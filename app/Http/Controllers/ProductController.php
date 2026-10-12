<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with(['category', 'supplier'])
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%")))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->status, fn ($q, $s) => match ($s) {
                'ok' => $q->inStock(),
                'low' => $q->low(),
                'out' => $q->out(),
                default => $q,
            })
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'supplier']);
        $transactions = $product->transactions()->with('user')->latest('id')->limit(10)->get();

        return view('products.show', compact('product', 'transactions'));
    }

    public function create(): View
    {
        return view('products.form', $this->formData(new Product(['unit' => 'pcs'])));
    }

    public function store(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate($this->rules() + [
            'opening_stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);
        $opening = (int) ($data['opening_stock'] ?? 0);
        unset($data['opening_stock']);

        DB::transaction(function () use ($data, $opening, $stock) {
            $product = Product::create($data + ['quantity' => 0]);

            if ($opening > 0) {
                $stock->record($product->id, 'in', $opening, [
                    'supplier_id' => $product->supplier_id,
                    'notes' => 'Opening stock',
                ], auth()->id());
            }
        });

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', $this->formData($product));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        // Quantity is intentionally NOT editable here; it only changes via stock transactions.
        $product->update($request->validate($this->rules($product)));

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->transactions()->exists()) {
            return back()->with('error', 'This product has transaction history and cannot be deleted.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    private function rules(?Product $product = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product?->id)],
            'barcode' => ['nullable', 'string', 'max:64', Rule::unique('products', 'barcode')->ignore($product?->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'min_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'unit' => ['required', 'string', 'max:20'],
        ];
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
        ];
    }
}
