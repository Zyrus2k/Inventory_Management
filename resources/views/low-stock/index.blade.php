@extends('layouts.app')
@section('title', 'Low-Stock Alerts')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-sm-6"><div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle me-1"></i><strong>{{ $lowCount }}</strong> product(s) at or below the minimum threshold</div></div>
    <div class="col-sm-6"><div class="alert alert-danger mb-0"><i class="bi bi-x-octagon me-1"></i><strong>{{ $outCount }}</strong> product(s) out of stock</div></div>
</div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Product</th><th>SKU</th><th>Category</th><th>Supplier</th><th class="text-end">Stock</th><th class="text-end">Minimum</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse($products as $p)
        <tr>
            <td><a href="{{ route('products.show', $p) }}" class="text-decoration-none fw-medium">{{ $p->name }}</a></td>
            <td>{{ $p->sku }}</td><td>{{ $p->category->name }}</td><td>{{ $p->supplier->name ?? '—' }}</td>
            <td class="text-end">{{ $p->quantity }} {{ $p->unit }}</td><td class="text-end">{{ $p->min_stock }}</td>
            <td>@include('partials.status', ['product' => $p])</td>
            <td class="text-end"><a href="{{ route('stock.in', ['product' => $p->id]) }}" class="btn btn-sm btn-success">Restock</a></td>
        </tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted py-4">No low-stock products. 🎉</td></tr>
    @endforelse
    </tbody>
</table></div>
<div class="card-footer bg-white">{{ $products->links() }}</div></div>
@endsection
