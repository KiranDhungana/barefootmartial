@extends('layouts.admin')
@section('title', 'Inventory sales')
@section('page_title', 'Uniform & inventory sales')
@section('content')
    <form method="get" class="d-flex gap-2 mb-3">
        <input type="number" name="year" value="{{ $year }}" class="form-control rounded-3" style="width:100px">
        <input type="number" name="month" value="{{ $month }}" min="1" max="12" class="form-control rounded-3" style="width:80px">
        <button class="btn btn-outline-primary rounded-pill">Filter</button>
        <a href="{{ route('erp.inventory.report.export', ['year' => $year, 'month' => $month]) }}" class="btn btn-outline-secondary rounded-pill">Export CSV</a>
    </form>
    <p>Total: <strong>{{ number_format($total, 2) }}</strong></p>
    <div class="panel-card"><div class="panel-body table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th class="text-end">Revenue</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr><td>{{ $row->description }}</td><td>{{ $row->fee_type }}</td><td>{{ $row->qty }}</td><td class="text-end">{{ number_format($row->revenue, 2) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No sales this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
@endsection
