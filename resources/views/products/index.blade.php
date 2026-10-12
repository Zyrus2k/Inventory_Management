@extends('layouts.app')
@section('title', 'Products')

@section('content')
<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4"><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, SKU or barcode"></div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All categories</option>
                    @foreach($categories as $id => $name)<option value="{{ $id }}" @selected(request('category') == $id)>{{ $name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All stock statuses</option>
                    <option value="ok" @selected(request('status') === 'ok')>In stock</option>
                    <option value="low" @selected(request('status') === 'low')>Low stock</option>
                    <option value="out" @selected(request('status') === 'out')>Out of stock</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
                <a href="{{ route('products.index') }}" class="btn btn-light" title="Reset"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        @if(auth()->user()->isAdmin())
            <a href="{{ route('products.create') }}" class="btn btn-success mb-3"><i class="bi bi-plus-lg me-1"></i>Add product</a>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr>
                    <th>Product</th><th>SKU</th><th>Category</th><th class="text-end">Price</th><th class="text-end">Stock</th><th>Status</th><th class="text-end">Actions</th>
                </tr></thead>
                <tbody>
                @forelse($products as $p)
                    <tr>
                        <td><a href="{{ route('products.show', $p) }}" class="text-decoration-none fw-medium">{{ $p->name }}</a></td>
                        <td>{{ $p->sku }}</td>
                        <td>{{ $p->category->name }}</td>
                        <td class="text-end">₱{{ number_format($p->selling_price, 2) }}</td>
                        <td class="text-end">{{ $p->quantity }} <span class="text-muted small">{{ $p->unit }}</span></td>
                        <td>@include('partials.status', ['product' => $p])</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('products.destroy', $p) }}" class="d-inline" data-confirm="Delete product “{{ $p->name }}”? This cannot be undone.">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No products found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $products->links() }}
    </div>
</div>
@endsection
