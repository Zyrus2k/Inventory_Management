<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class LowStockController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::needsRestock()->with(['category', 'supplier'])
            ->orderBy('quantity')->orderBy('name')->paginate(15);

        return view('low-stock.index', [
            'products' => $products,
            'lowCount' => Product::low()->count(),
            'outCount' => Product::out()->count(),
        ]);
    }
}
