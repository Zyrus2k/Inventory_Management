<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .sidebar { --bs-offcanvas-width: 250px; background: #1f2937; color: #cbd5e1; }
        @media (min-width: 992px) { .sidebar { width: 250px; flex: 0 0 250px; min-height: 100vh; } }
        .sidebar .brand { color: #fff; font-weight: 600; font-size: 1.1rem; }
        .sidebar .nav-link { color: #cbd5e1; border-radius: .4rem; padding: .55rem .9rem; }
        .sidebar .nav-link:hover { background: #374151; color: #fff; }
        .sidebar .nav-link.active { background: #2563eb; color: #fff; }
        .sidebar .nav-heading { font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; padding: .9rem .9rem .3rem; }
        .main { min-width: 0; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .stat-icon { width: 46px; height: 46px; border-radius: .6rem; display: grid; place-items: center; font-size: 1.4rem; }
        @media print {
            .no-print, .sidebar, .topbar { display: none !important; }
            body { background: #fff; } .card { box-shadow: none; border: 0; }
        }
    </style>
    @stack('head')
</head>
<body>
@php $u = auth()->user(); $on = fn ($p) => request()->routeIs($p) ? 'active' : ''; @endphp
<div class="d-lg-flex">
    <aside class="offcanvas-lg offcanvas-start sidebar no-print" tabindex="-1" id="sidebar">
        <div class="offcanvas-header">
            <span class="brand"><i class="bi bi-box-seam me-2"></i>Inventory</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
        </div>
        <div class="offcanvas-body d-block p-3 pt-lg-4">
            <div class="brand mb-3 d-none d-lg-block px-2"><i class="bi bi-box-seam me-2"></i>Inventory System</div>
            <nav class="nav flex-column">
                <a class="nav-link {{ $on('dashboard') }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>

                <div class="nav-heading">Catalog</div>
                <a class="nav-link {{ $on('products.*') }}" href="{{ route('products.index') }}"><i class="bi bi-box me-2"></i>Products</a>
                @if($u->isAdmin())
                    <a class="nav-link {{ $on('categories.*') }}" href="{{ route('categories.index') }}"><i class="bi bi-tags me-2"></i>Categories</a>
                @endif
                <a class="nav-link {{ $on('suppliers.*') }}" href="{{ route('suppliers.index') }}"><i class="bi bi-truck me-2"></i>Suppliers</a>

                <div class="nav-heading">Stock</div>
                <a class="nav-link {{ $on('stock.in*') }}" href="{{ route('stock.in') }}"><i class="bi bi-box-arrow-in-down me-2"></i>Stock In</a>
                <a class="nav-link {{ $on('stock.out*') }}" href="{{ route('stock.out') }}"><i class="bi bi-box-arrow-up me-2"></i>Stock Out</a>
                <a class="nav-link {{ $on('transactions.*') }}" href="{{ route('transactions.index') }}"><i class="bi bi-clock-history me-2"></i>History</a>
                <a class="nav-link {{ $on('low-stock') }}" href="{{ route('low-stock') }}"><i class="bi bi-exclamation-triangle me-2"></i>Low Stock</a>

                @if($u->isAdmin())
                    <div class="nav-heading">Administration</div>
                    <a class="nav-link {{ $on('reports.*') }}" href="{{ route('reports.index') }}"><i class="bi bi-graph-up me-2"></i>Reports</a>
                    <a class="nav-link {{ $on('users.*') }}" href="{{ route('users.index') }}"><i class="bi bi-people me-2"></i>Users</a>
                @endif
            </nav>
        </div>
    </aside>

    <div class="flex-grow-1 main">
        <header class="topbar bg-white border-bottom px-3 py-2 d-flex align-items-center">
            <button class="btn btn-outline-secondary d-lg-none me-2" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Menu"><i class="bi bi-list"></i></button>
            <h1 class="h5 mb-0">@yield('title', 'Dashboard')</h1>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-sm-inline"><i class="bi bi-person-circle me-1"></i>{{ $u->name }}
                    <span class="badge text-bg-secondary">{{ ucfirst($u->role) }}</span></span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                </form>
            </div>
        </header>

        <main class="p-3 p-md-4">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</div>

{{-- Shared delete-confirmation modal: any <form data-confirm="message"> uses it --}}
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Please confirm</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="confirmMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmYes">Yes, continue</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        let pending = null;
        document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
            e.preventDefault(); pending = f;
            document.getElementById('confirmMessage').textContent = f.dataset.confirm;
            modal.show();
        }));
        document.getElementById('confirmYes').addEventListener('click', () => { if (pending) pending.submit(); });
    })();
</script>
@stack('scripts')
</body>
</html>
