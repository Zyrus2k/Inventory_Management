@extends('layouts.app')
@section('title', 'User Management')

@section('content')
<div class="card"><div class="card-body">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5"><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name or email"></div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">All roles</option>
                <option value="admin" @selected(request('role') === 'admin')>Administrator</option>
                <option value="staff" @selected(request('role') === 'staff')>Staff</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary"><i class="bi bi-search"></i></button><a href="{{ route('users.index') }}" class="btn btn-light"><i class="bi bi-x-lg"></i></a></div>
    </form>
    <a href="{{ route('users.create') }}" class="btn btn-success mb-3"><i class="bi bi-person-plus me-1"></i>Add user</a>
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td class="fw-medium">{{ $u->name }} @if($u->id === auth()->id())<span class="badge text-bg-light">You</span>@endif</td>
                <td>{{ $u->email }}</td>
                <td><span class="badge text-bg-{{ $u->role === 'admin' ? 'primary' : 'secondary' }}">{{ ucfirst($u->role) }}</span></td>
                <td><span class="badge text-bg-{{ $u->is_active ? 'success' : 'dark' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="small">{{ $u->created_at->format('M d, Y') }}</td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary" title="Edit / reset password"><i class="bi bi-pencil"></i></a>
                    @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('users.toggle', $u) }}" class="d-inline" data-confirm="{{ $u->is_active ? 'Deactivate' : 'Activate' }} the account of {{ $u->name }}?">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-{{ $u->is_active ? 'warning' : 'success' }}" title="{{ $u->is_active ? 'Deactivate' : 'Activate' }}"><i class="bi bi-{{ $u->is_active ? 'person-dash' : 'person-check' }}"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    {{ $users->links() }}
</div></div>
@endsection
