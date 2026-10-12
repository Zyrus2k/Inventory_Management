@extends('layouts.app')
@section('title', $supplier->name)

@section('content')
<div class="row g-3">
    <div class="col-lg-5"><div class="card h-100"><div class="card-body">
        <h2 class="h5">{{ $supplier->name }}</h2>
        <table class="table table-sm mb-3">
            <tr><th class="text-muted fw-normal">Contact person</th><td>{{ $supplier->contact_person ?: '—' }}</td></tr>
            <tr><th class="text-muted fw-normal">Phone</th><td>{{ $supplier->phone ?: '—' }}</td></tr>
            <tr><th class="text-muted fw-normal">Email</th><td>{{ $supplier->email ?: '—' }}</td></tr>
            <tr><th class="text-muted fw-normal">Address</th><td>{{ $supplier->address ?: '—' }}</td></tr>
            <tr><th class="text-muted fw-normal">Notes</th><td>{{ $supplier->notes ?: '—' }}</td></tr>
        </table>
        <div class="d-flex gap-2">
            @if(auth()->user()->isAdmin())<a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>@endif
            <a href="{{ route('suppliers.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
    </div></div></div>
    <div class="col-lg-7"><div class="card h-100">
        <div class="card-header bg-white"><strong>Products from this supplier</strong></div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Product</th><th>SKU</th><th class="text-end">Stock</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($supplier->products as $p)
                <tr><td><a href="{{ route('products.show', $p) }}">{{ $p->name }}</a></td><td>{{ $p->sku }}</td><td class="text-end">{{ $p->quantity }}</td><td>@include('partials.status', ['product' => $p])</td></tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No products linked.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div></div>
</div>
@endsection
