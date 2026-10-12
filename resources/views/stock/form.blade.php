@extends('layouts.app')
@section('title', $type === 'in' ? 'Stock In' : 'Stock Out')

@section('content')
<div class="card" style="max-width: 820px;"><div class="card-body">
    <p class="text-muted">
        @if($type === 'in') Record newly received inventory. The product's stock will increase.
        @else Record products released, sold, consumed or removed. The product's stock will decrease. @endif
    </p>
    <form method="POST" action="{{ $type === 'in' ? route('stock.in.store') : route('stock.out.store') }}" class="row g-3">
        @csrf
        <x-select name="product_id" label="Product" :options="$products" :value="$selected" required col="col-12" />
        <x-input name="quantity" label="Quantity" type="number" min="1" step="1" required col="col-md-4" />
        <x-input name="transaction_date" label="Transaction date" type="date" :value="now()->toDateString()" max="{{ now()->toDateString() }}" required col="col-md-4" />
        @if($type === 'in')
            <x-select name="supplier_id" label="Supplier" :options="$suppliers" placeholder="-- None --" col="col-md-4" />
            <x-input name="invoice_no" label="Purchase reference / invoice no." maxlength="100" />
        @else
            <x-select name="reason" label="Reason" :options="$reasons" required col="col-md-4" />
        @endif
        <x-textarea name="notes" label="Notes" rows="2" />
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-{{ $type === 'in' ? 'success' : 'danger' }}"><i class="bi bi-check2-circle me-1"></i>Record {{ $type === 'in' ? 'stock-in' : 'stock-out' }}</button>
            <a href="{{ route('transactions.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
