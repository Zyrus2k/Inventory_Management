@extends('layouts.app')
@section('title', $user->exists ? 'Edit User' : 'Add User')

@section('content')
@php $self = $user->exists && $user->id === auth()->id(); @endphp
<div class="card" style="max-width: 760px;"><div class="card-body">
    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="row g-3">
        @csrf
        @if($user->exists) @method('PUT') @endif
        <x-input name="name" label="Full name" :value="$user->name" required />
        <x-input name="email" label="Email" type="email" :value="$user->email" required />
        <x-select name="role" label="Role" :options="['admin' => 'Administrator', 'staff' => 'Staff']" :value="$user->role" required :disabled="$self" placeholder="-- Select role --" />
        @if($self)<input type="hidden" name="role" value="{{ $user->role }}">@endif
        @if($user->exists)
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active)) @disabled($self)>
                    <label for="is_active" class="form-check-label">Account active</label>
                </div>
            </div>
        @endif
        <x-input name="password" label="{{ $user->exists ? 'New password (leave blank to keep current)' : 'Password' }}" type="password" :required="! $user->exists" autocomplete="new-password" />
        <x-input name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" />
        <div class="form-text">Minimum 8 characters, with letters and numbers.</div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save</button>
            <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
