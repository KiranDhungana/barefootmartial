@extends('layouts.admin')

@section('title', 'Salary')
@section('page_title', 'Trainer salary')
@section('page_subtitle', 'Generated rows')

@section('content')
    <div class="panel-card mb-3">
        <div class="panel-heading">Period & generation</div>
        <div class="panel-body p-4">
            <form method="get" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label small text-muted">Year</label>
                    <input type="number" name="year" class="form-control rounded-3" value="{{ $year }}" min="2000"
                        max="2100">
                </div>
                <div class="col-auto">
                    <label class="form-label small text-muted">Month</label>
                    <input type="number" name="month" class="form-control rounded-3" value="{{ $month }}" min="1"
                        max="12">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary rounded-3" type="submit">Load</button>
                </div>
            </form>
            <form method="post" action="{{ route('erp.salary.generate') }}" class="d-inline"
                onsubmit="return confirm('Generate or overwrite salary rows for this month?');">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="btn btn-admin-primary text-white">Generate month</button>
            </form>
            <a href="{{ route('erp.salary.pdf', ['year' => $year, 'month' => $month]) }}"
                class="btn btn-outline-secondary ms-2 rounded-pill">Export PDF</a>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-heading">Records</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Trainer</th>
                        <th>Amount</th>
                        <th>Days (meta)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $rec)
                        <tr>
                            <td>{{ $rec->trainer->name }}</td>
                            <td>{{ number_format($rec->amount, 2) }}</td>
                            <td>{{ $rec->attendance_days ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">No salary rows. Generate this period or add trainers.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
