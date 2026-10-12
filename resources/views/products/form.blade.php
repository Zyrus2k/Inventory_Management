@extends('layouts.app')
@section('title', $product->exists ? 'Edit Product' : 'Add Product')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" class="row g-3">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <x-input name="name" label="Product name" :value="$product->name" required maxlength="255" />
        <x-input name="sku" label="SKU / product code" :value="$product->sku" required maxlength="50" />
        <x-input name="barcode" label="Barcode" :value="$product->barcode" maxlength="64" />
        <x-select name="category_id" label="Category" :options="$categories" :value="$product->category_id" required />
        <x-select name="supplier_id" label="Supplier" :options="$suppliers" :value="$product->supplier_id" placeholder="-- None --" />
        <x-input name="unit" label="Unit of measurement" :value="$product->unit" required maxlength="20" list="units" placeholder="pcs, box, kg…" />
        <datalist id="units"><option>pcs</option><option>box</option><option>pack</option><option>set</option><option>kg</option><option>liter</option><option>ream</option></datalist>
        <x-input name="purchase_price" label="Purchase price (₱)" type="number" step="0.01" min="0" :value="$product->purchase_price" required col="col-md-3" />
        <x-input name="selling_price" label="Selling price (₱)" type="number" step="0.01" min="0" :value="$product->selling_price" required col="col-md-3" />
        <x-input name="min_stock" label="Minimum stock threshold" type="number" min="0" step="1" :value="$product->min_stock ?? 0" required col="col-md-3" />
        @unless($product->exists)
            <x-input name="opening_stock" label="Opening stock (optional)" type="number" min="0" step="1" col="col-md-3" />
        @else
            <div class="col-md-3"><label class="form-label">Current stock</label>
                <input class="form-control" value="{{ $product->quantity }}" disabled>
                <div class="form-text">Changed only via Stock In / Stock Out.</div></div>
        @endunless
        <x-textarea name="description" label="Description" :value="$product->description" />

        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button>
            <a href="{{ route('products.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
