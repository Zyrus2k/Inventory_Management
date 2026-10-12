@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="row g-3">
    <div class="col-lg-5"><div class="card h-100"><div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h2 class="h5 mb-0">{{ $product->name }}</h2>@include('partials.status', ['product' => $product])
        </div>
        <table class="table table-sm mb-3">
            <tr><th class="text-muted fw-normal">SKU</th><td>{{ $product->sku }}</td></tr>
            <tr><th class="text-muted fw-normal">Barcode</th><td>{{ $product->barcode ?: '—' }}</td></tr>
            <tr><th class="text-muted fw-normal">Category</th><td>{{ $product->category->name }}</td></tr>
            <tr><th class="text-muted fw-normal">Supplier</th><td>@if($product->supplier)<a href="{{ route('suppliers.show', $product->supplier) }}">{{ $product->supplier->name }}</a>@else — @endif</td></tr>
            <tr><th class="text-muted fw-normal">Purchase price</th><td>₱{{ number_format($product->purchase_price, 2) }}</td></tr>
            <tr><th class="text-muted fw-normal">Selling price</th><td>₱{{ number_format($product->selling_price, 2) }}</td></tr>
            <tr><th class="text-muted fw-normal">Current stock</th><td class="fw-semibold">{{ $product->quantity }} {{ $product->unit }}</td></tr>
            <tr><th class="text-muted fw-normal">Minimum threshold</th><td>{{ $product->min_stock }}</td></tr>
            <tr><th class="text-muted fw-normal">Created</th><td>{{ $product->created_at->format('M d, Y H:i') }}</td></tr>
            <tr><th class="text-muted fw-normal">Last updated</th><td>{{ $product->updated_at->format('M d, Y H:i') }}</td></tr>
        </table>
        @if($product->description)<p class="text-muted">{{ $product->description }}</p>@endif
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('stock.in', ['product' => $product->id]) }}" class="btn btn-success btn-sm"><i class="bi bi-box-arrow-in-down me-1"></i>Stock in</a>
            <a href="{{ route('stock.out', ['product' => $product->id]) }}" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-up me-1"></i>Stock out</a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
                <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="Delete product “{{ $product->name }}”?">
                    @csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Delete</button>
                </form>
            @endif
            <a href="{{ route('products.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
    </div></div></div>

    <div class="col-lg-7"><div class="card h-100">
        <div class="card-header bg-white"><strong>Recent movements</strong></div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Ref</th><th>Type</th><th class="text-end">Qty</th><th class="text-end">Before → After</th><th>User</th></tr></thead>
            <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t->transaction_date->format('M d, Y') }}</td><td class="small">{{ $t->reference }}</td>
                    <td><span class="badge text-bg-{{ $t->type === 'in' ? 'success' : 'danger' }}">{{ strtoupper($t->type) }}</span></td>
                    <td class="text-end">{{ $t->quantity }}</td>
                    <td class="text-end">{{ $t->previous_quantity }} → {{ $t->new_quantity }}</td><td>{{ $t->user->name }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No movements yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div></div>
</div>
@endsection
