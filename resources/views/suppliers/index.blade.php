@extends('layouts.app')
@section('title', 'Suppliers')

@section('content')
<div class="card"><div class="card-body">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5"><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name, contact, email or phone"></div>
        <div class="col-md-3">
            <select name="filter" class="form-select">
                <option value="">All suppliers</option>
                <option value="with" @selected(request('filter') === 'with')>With products</option>
                <option value="without" @selected(request('filter') === 'without')>Without products</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-light"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('suppliers.create') }}" class="btn btn-success mb-3"><i class="bi bi-plus-lg me-1"></i>Add supplier</a>
    @endif
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead class="table-light"><tr><th>Supplier</th><th>Contact person</th><th>Phone</th><th>Email</th><th class="text-end">Products</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($suppliers as $s)
            <tr>
                <td><a href="{{ route('suppliers.show', $s) }}" class="text-decoration-none fw-medium">{{ $s->name }}</a></td>
                <td>{{ $s->contact_person ?: '—' }}</td><td>{{ $s->phone ?: '—' }}</td><td>{{ $s->email ?: '—' }}</td>
                <td class="text-end">{{ $s->products_count }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('suppliers.show', $s) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('suppliers.destroy', $s) }}" class="d-inline" data-confirm="Delete supplier “{{ $s->name }}”?">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No suppliers found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $suppliers->links() }}
</div></div>
@endsection
