@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $cards = [
        ['Products', number_format($stats['products']), 'bi-box', 'primary', route('products.index')],
        ['Categories', number_format($stats['categories']), 'bi-tags', 'info', auth()->user()->isAdmin() ? route('categories.index') : null],
        ['Total stock units', number_format($stats['quantity']), 'bi-stack', 'success', null],
        ['Low stock', number_format($stats['low']), 'bi-exclamation-triangle', 'warning', route('low-stock')],
        ['Out of stock', number_format($stats['out']), 'bi-x-octagon', 'danger', route('low-stock')],
        ['Stock value (cost)', '₱'.number_format($stats['value'], 2), 'bi-cash-stack', 'secondary', null],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach($cards as [$label, $value, $icon, $color, $link])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}"><i class="bi {{ $icon }}"></i></div>
                <div class="min-w-0">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fw-semibold fs-5 text-truncate">
                        @if($link)<a href="{{ $link }}" class="text-decoration-none text-reset stretched-link">{{ $value }}</a>@else{{ $value }}@endif
                    </div>
                </div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8"><div class="card h-100"><div class="card-body">
        <h2 class="h6">Stock movement — last 7 days</h2>
        <canvas id="movementChart" height="110"></canvas>
    </div></div></div>
    <div class="col-lg-4"><div class="card h-100"><div class="card-body">
        <h2 class="h6">Stock units by category</h2>
        <canvas id="categoryChart" height="220"></canvas>
    </div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-5"><div class="card h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-exclamation-triangle text-warning me-1"></i>Low / out-of-stock products</strong>
            <a href="{{ route('low-stock') }}" class="small">View all</a>
        </div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <tbody>
            @forelse($lowProducts as $p)
                <tr>
                    <td><a href="{{ route('products.show', $p) }}" class="text-decoration-none">{{ $p->name }}</a><div class="small text-muted">{{ $p->sku }}</div></td>
                    <td class="text-end">{{ $p->quantity }} / {{ $p->min_stock }}</td>
                    <td class="text-end">@include('partials.status', ['product' => $p])</td>
                </tr>
            @empty
                <tr><td class="text-center text-muted py-4">All products are sufficiently stocked 🎉</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div></div>

    <div class="col-lg-7"><div class="card h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-clock-history me-1"></i>Recent transactions</strong>
            <a href="{{ route('transactions.index') }}" class="small">View all</a>
        </div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Ref</th><th>Product</th><th>Type</th><th class="text-end">Qty</th><th>Date</th></tr></thead>
            <tbody>
            @forelse($recent as $t)
                <tr>
                    <td class="small">{{ $t->reference }}</td>
                    <td>{{ $t->product->name }}</td>
                    <td><span class="badge text-bg-{{ $t->type === 'in' ? 'success' : 'danger' }}">{{ $t->type === 'in' ? 'Stock-In' : 'Stock-Out' }}</span></td>
                    <td class="text-end">{{ $t->type === 'in' ? '+' : '−' }}{{ $t->quantity }}</td>
                    <td class="small">{{ $t->transaction_date->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No transactions yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const chart = @json($chart);
    new Chart(document.getElementById('movementChart'), {
        type: 'bar',
        data: { labels: chart.labels, datasets: [
            { label: 'Stock-In',  data: chart.in,  backgroundColor: '#198754' },
            { label: 'Stock-Out', data: chart.out, backgroundColor: '#dc3545' },
        ]},
        options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    const cat = @json($categoryChart);
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: { labels: cat.labels, datasets: [{ data: cat.data,
            backgroundColor: ['#2563eb','#0ea5e9','#16a34a','#f59e0b','#ef4444','#8b5cf6','#64748b'] }] },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
</script>
@endpush
