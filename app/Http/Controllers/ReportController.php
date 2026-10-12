<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public const TYPES = [
        'current' => 'Current Inventory',
        'stock-in' => 'Stock-In Report',
        'stock-out' => 'Stock-Out Report',
        'low-stock' => 'Low-Stock Report',
        'out-of-stock' => 'Out-of-Stock Report',
        'movement' => 'Inventory Movement Summary',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'type' => ['nullable', 'in:'.implode(',', array_keys(self::TYPES))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $type = $request->input('type', 'current');
        $from = $request->input('date_from');
        $to = $request->input('date_to');
        $report = $this->build($type, $from, $to);

        if ($request->query('export') === 'csv') {
            return $this->csv($type, $report);
        }

        return view('reports.index', [
            'types' => self::TYPES, 'type' => $type, 'from' => $from, 'to' => $to, 'report' => $report,
        ]);
    }

    private function build(string $type, ?string $from, ?string $to): array
    {
        $money = fn ($v) => number_format((float) $v, 2);
        $dateScope = fn ($q) => $q
            ->when($from, fn ($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('transaction_date', '<=', $to));

        switch ($type) {
            case 'stock-in':
            case 'stock-out':
                $dir = $type === 'stock-in' ? 'in' : 'out';
                $tx = $dateScope(StockTransaction::with(['product', 'user', 'supplier'])->where('type', $dir))
                    ->orderBy('transaction_date')->orderBy('id')->get();

                return [
                    'title' => self::TYPES[$type],
                    'headings' => ['Reference', 'Date', 'Product', 'SKU', 'Qty', $dir === 'in' ? 'Supplier' : 'Reason', 'Invoice/Notes', 'User'],
                    'rows' => $tx->map(fn ($t) => [
                        $t->reference, $t->transaction_date->format('Y-m-d'), $t->product->name, $t->product->sku,
                        $t->quantity, $dir === 'in' ? ($t->supplier->name ?? '—') : ($t->reason ?? '—'),
                        $t->invoice_no ?: ($t->notes ?: '—'), $t->user->name,
                    ])->all(),
                    'summary' => 'Transactions: '.$tx->count().' | Total quantity: '.number_format($tx->sum('quantity')),
                ];

            case 'low-stock':
            case 'out-of-stock':
                $products = Product::with(['category', 'supplier'])
                    ->{$type === 'low-stock' ? 'low' : 'out'}()->orderBy('name')->get();

                return [
                    'title' => self::TYPES[$type].' (as of now)',
                    'headings' => ['Product', 'SKU', 'Category', 'Supplier', 'Stock', 'Minimum', 'Unit'],
                    'rows' => $products->map(fn ($p) => [
                        $p->name, $p->sku, $p->category->name, $p->supplier->name ?? '—', $p->quantity, $p->min_stock, $p->unit,
                    ])->all(),
                    'summary' => 'Products: '.$products->count(),
                ];

            case 'movement':
                $agg = $dateScope(StockTransaction::query())
                    ->select('product_id',
                        DB::raw("SUM(CASE WHEN type = 'in' THEN quantity ELSE 0 END) as total_in"),
                        DB::raw("SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END) as total_out"))
                    ->groupBy('product_id')->get()->keyBy('product_id');
                $products = Product::orderBy('name')->get();

                return [
                    'title' => self::TYPES[$type],
                    'headings' => ['Product', 'SKU', 'Total In', 'Total Out', 'Net Change', 'Current Stock'],
                    'rows' => $products->map(function ($p) use ($agg) {
                        $in = (int) ($agg[$p->id]->total_in ?? 0);
                        $out = (int) ($agg[$p->id]->total_out ?? 0);

                        return [$p->name, $p->sku, $in, $out, ($in - $out > 0 ? '+' : '').($in - $out), $p->quantity];
                    })->all(),
                    'summary' => 'Total in: '.number_format($agg->sum('total_in')).' | Total out: '.number_format($agg->sum('total_out')),
                ];

            default: // current
                $products = Product::with(['category'])->orderBy('name')->get();

                return [
                    'title' => self::TYPES['current'].' (as of now)',
                    'headings' => ['Product', 'SKU', 'Category', 'Stock', 'Unit', 'Min', 'Purchase Price', 'Stock Value'],
                    'rows' => $products->map(fn ($p) => [
                        $p->name, $p->sku, $p->category->name, $p->quantity, $p->unit, $p->min_stock,
                        $money($p->purchase_price), $money($p->quantity * $p->purchase_price),
                    ])->all(),
                    'summary' => 'Products: '.$products->count().' | Total units: '.number_format($products->sum('quantity'))
                        .' | Total value: '.$money($products->sum(fn ($p) => $p->quantity * $p->purchase_price)),
                ];
        }
    }

    private function csv(string $type, array $report): StreamedResponse
    {
        $name = "{$type}-".now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $report['headings']);
            foreach ($report['rows'] as $row) {
                // Prevent spreadsheet formula injection
                fputcsv($out, array_map(fn ($c) => is_string($c) && preg_match('/^[=+\-@]/', $c) && ! is_numeric($c) ? "'".$c : $c, $row));
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }
}
