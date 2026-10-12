@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="card mb-3 no-print"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Report</label>
            <select name="type" class="form-select">
                @foreach($types as $k => $label)<option value="{{ $k }}" @selected($type === $k)>{{ $label }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">From</label><input type="date" name="date_from" value="{{ $from }}" class="form-control @error('date_from') is-invalid @enderror"></div>
        <div class="col-md-2"><label class="form-label">To</label><input type="date" name="date_to" value="{{ $to }}" class="form-control @error('date_to') is-invalid @enderror">
            @error('date_to')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-5 d-flex flex-wrap gap-2">
            <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Generate</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
            <a class="btn btn-outline-success" href="{{ route('reports.index', array_filter(['type' => $type, 'date_from' => $from, 'date_to' => $to, 'export' => 'csv'])) }}"><i class="bi bi-filetype-csv me-1"></i>Export CSV</a>
        </div>
    </form>
    <div class="form-text">Date range applies to transaction-based reports (stock-in, stock-out, movement). Current, low-stock and out-of-stock reports show live stock levels.</div>
</div></div>

<div class="card"><div class="card-body">
    <h2 class="h5 mb-0">{{ $report['title'] }}</h2>
    <div class="text-muted small mb-2">
        Generated {{ now()->format('M d, Y H:i') }}
        @if($from || $to) · Period: {{ $from ?: 'beginning' }} to {{ $to ?: 'today' }} @endif
    </div>
    <div class="fw-medium mb-3">{{ $report['summary'] }}</div>
    <div class="table-responsive"><table class="table table-sm table-striped align-middle">
        <thead class="table-light"><tr>@foreach($report['headings'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
        @forelse($report['rows'] as $row)
            <tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
        @empty
            <tr><td colspan="{{ count($report['headings']) }}" class="text-center text-muted py-4">No records for the selected filters.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div></div>
@endsection
