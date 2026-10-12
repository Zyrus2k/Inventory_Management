<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'quantity' => (int) Product::sum('quantity'),
            'low' => Product::low()->count(),
            'out' => Product::out()->count(),
            'value' => (float) Product::selectRaw('COALESCE(SUM(quantity * purchase_price), 0) as v')->value('v'),
        ];

        $lowProducts = Product::needsRestock()->with('category')->orderBy('quantity')->limit(8)->get();
        $recent = StockTransaction::with(['product', 'user'])->latest('id')->limit(8)->get();

        // Stock-in vs stock-out for the last 7 days
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $rows = StockTransaction::selectRaw('transaction_date as d, type, SUM(quantity) as total')
            ->whereDate('transaction_date', '>=', $days->first())
            ->groupBy('d', 'type')->get();

        $sum = fn (string $type) => $days->map(
            fn ($d) => (int) ($rows->first(fn ($r) => (string) $r->d === $d && $r->type === $type)?->total ?? 0)
        )->values();

        $chart = [
            'labels' => $days->map(fn ($d) => date('M j', strtotime($d)))->values(),
            'in' => $sum('in'),
            'out' => $sum('out'),
        ];

        $byCategory = Category::withSum('products', 'quantity')->orderBy('name')->get();
        $categoryChart = [
            'labels' => $byCategory->pluck('name'),
            'data' => $byCategory->map(fn ($c) => (int) $c->products_sum_quantity),
        ];

        return view('dashboard', compact('stats', 'lowProducts', 'recent', 'chart', 'categoryChart'));
    }
}
