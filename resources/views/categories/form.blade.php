@extends('layouts.app')
@section('title', $category->exists ? 'Edit Category' : 'Add Category')

@section('content')
<div class="card" style="max-width: 640px;"><div class="card-body">
    <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="row g-3">
        @csrf
        @if($category->exists) @method('PUT') @endif
        <x-input name="name" label="Category name" :value="$category->name" required col="col-12" maxlength="255" />
        <x-textarea name="description" label="Description" :value="$category->description" />
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button>
            <a href="{{ route('categories.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
