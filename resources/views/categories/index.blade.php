@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="card"><div class="card-body">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-6"><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search categories"></div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-search"></i></button>
            <a href="{{ route('categories.index') }}" class="btn btn-light"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
    <a href="{{ route('categories.create') }}" class="btn btn-success mb-3"><i class="bi bi-plus-lg me-1"></i>Add category</a>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead class="table-light"><tr><th>Name</th><th>Description</th><th class="text-end">Products</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @forelse($categories as $c)
            <tr>
                <td class="fw-medium">{{ $c->name }}</td>
                <td class="text-muted">{{ \Illuminate\Support\Str::limit($c->description, 80) ?: '—' }}</td>
                <td class="text-end"><a href="{{ route('products.index', ['category' => $c->id]) }}">{{ $c->products_count }}</a></td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('categories.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('categories.destroy', $c) }}" class="d-inline" data-confirm="Delete category “{{ $c->name }}”?">
                        @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" @disabled($c->products_count > 0) title="{{ $c->products_count ? 'Has products' : 'Delete' }}"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">No categories found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    {{ $categories->links() }}
</div></div>
@endsection
