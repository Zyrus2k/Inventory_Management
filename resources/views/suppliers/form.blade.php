@extends('layouts.app')
@section('title', $supplier->exists ? 'Edit Supplier' : 'Add Supplier')

@section('content')
<div class="card"><div class="card-body">
    <form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="row g-3">
        @csrf
        @if($supplier->exists) @method('PUT') @endif
        <x-input name="name" label="Supplier name" :value="$supplier->name" required maxlength="255" />
        <x-input name="contact_person" label="Contact person" :value="$supplier->contact_person" maxlength="255" />
        <x-input name="phone" label="Phone number" :value="$supplier->phone" maxlength="50" />
        <x-input name="email" label="Email address" type="email" :value="$supplier->email" maxlength="255" />
        <x-textarea name="address" label="Business address" :value="$supplier->address" rows="2" />
        <x-textarea name="notes" label="Other details / notes" :value="$supplier->notes" rows="2" />
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
