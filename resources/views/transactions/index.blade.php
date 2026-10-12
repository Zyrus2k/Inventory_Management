@extends('layouts.app')
@section('title', 'Inventory Transaction History')

@section('content')
<div class="card"><div class="card-body">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3"><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, invoice, product, SKU"></div>
        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">All types</option>
                <option value="in" @selected(request('type') === 'in')>Stock-In</option>
                <option value="out" @selected(request('type') === 'out')>Stock-Out</option>
            </select>
        </div>
        <div class="col-md-2"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="From"></div>
        <div class="col-md-2"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="To"></div>
        <div class="col-md-2">
            <select name="sort" class="form-select" title="Sort by">
                <option value="transaction_date" @selected(request('sort', 'transaction_date') === 'transaction_date')>Sort: Date</option>
                <option value="quantity" @selected(request('sort') === 'quantity')>Sort: Quantity</option>
                <option value="reference" @selected(request('sort') === 'reference')>Sort: Reference</option>
            </select>
        </div>
        <div class="col-md-1">
            <select name="dir" class="form-select" title="Direction">
                <option value="desc" @selected(request('dir', 'desc') === 'desc')>↓</option>
                <option value="asc" @selected(request('dir') === 'asc')>↑</option>
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
            <a href="{{ route('transactions.index') }}" class="btn btn-light">Reset</a>
            <a href="{{ route('stock.in') }}" class="btn btn-success ms-auto"><i class="bi bi-box-arrow-in-down me-1"></i>Stock in</a>
            <a href="{{ route('stock.out') }}" class="btn btn-danger"><i class="bi bi-box-arrow-up me-1"></i>Stock out</a>
        </div>
    </form>

    <div class="table-responsive"><table class="table table-hover align-middle table-sm">
        <thead class="table-light"><tr>
            <th>Reference</th><th>Date</th><th>Product</th><th>Type</th><th class="text-end">Qty</th>
            <th class="text-end">Previous</th><th class="text-end">Updated</th><th>User</th><th>Notes / reason</th>
        </tr></thead>
        <tbody>
        @forelse($transactions as $t)
            <tr>
                <td class="small text-nowrap">{{ $t->reference }}</td>
                <td class="text-nowrap">{{ $t->transaction_date->format('M d, Y') }}</td>
                <td><a href="{{ route('products.show', $t->product) }}" class="text-decoration-none">{{ $t->product->name }}</a></td>
                <td><span class="badge text-bg-{{ $t->type === 'in' ? 'success' : 'danger' }}">{{ $t->type === 'in' ? 'Stock-In' : 'Stock-Out' }}</span></td>
                <td class="text-end">{{ $t->type === 'in' ? '+' : '−' }}{{ $t->quantity }}</td>
                <td class="text-end">{{ $t->previous_quantity }}</td><td class="text-end fw-semibold">{{ $t->new_quantity }}</td>
                <td>{{ $t->user->name }}</td>
                <td class="small text-muted">{{ collect([$t->reason, $t->invoice_no ? 'Inv: '.$t->invoice_no : null, $t->supplier?->name, $t->notes])->filter()->implode(' · ') ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No transactions found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $transactions->links() }}
</div></div>
@endsection
