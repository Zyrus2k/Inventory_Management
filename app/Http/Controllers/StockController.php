<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockController extends Controller
{
    public const OUT_REASONS = ['Sale', 'Internal use / consumption', 'Damaged', 'Expired', 'Lost / missing', 'Returned to supplier', 'Other'];

    /** $type ('in' | 'out') comes from the route default. */
    public function create(Request $request, string $type): View
    {
        $products = Product::orderBy('name')->get()
            ->mapWithKeys(fn ($p) => [$p->id => "{$p->name} ({$p->sku}) — stock: {$p->quantity} {$p->unit}"]);

        return view('stock.form', [
            'type' => $type,
            'products' => $products,
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'reasons' => array_combine(self::OUT_REASONS, self::OUT_REASONS),
            'selected' => $request->query('product'),
        ]);
    }

    public function store(Request $request, StockService $stock, string $type): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'invoice_no' => ['nullable', 'string', 'max:100'],
            'reason' => [$type === 'out' ? 'required' : 'nullable', 'string', Rule::in(self::OUT_REASONS)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $tx = $stock->record($data['product_id'], $type, (int) $data['quantity'], $data, $request->user()->id);

        return redirect()->route('transactions.index')
            ->with('success', "Transaction {$tx->reference} recorded. New stock: {$tx->new_quantity}.");
    }
}
