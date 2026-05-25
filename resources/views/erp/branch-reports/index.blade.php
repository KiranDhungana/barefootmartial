@extends('layouts.admin')

@section('title', 'Branch reports')
@section('page_title', 'Branch collection reports')

@section('content')
    <form method="get" class="row g-2 align-items-end mb-4">
        @if ($branches->count() > 1)
            <div class="col-auto">
                <label class="form-label small">Branch</label>
                <select name="branch_id" class="form-select rounded-3">
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-auto">
            <label class="form-label small">Tab</label>
            <select name="tab" class="form-select rounded-3">
                <option value="daily" @selected($tab === 'daily')>Daily</option>
                <option value="monthly" @selected($tab === 'monthly')>Monthly</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small">Date</label>
            <input type="date" name="date" class="form-control rounded-3" value="{{ $date }}">
        </div>
        <div class="col-auto">
            <label class="form-label small">Year</label>
            <input type="number" name="year" class="form-control rounded-3" value="{{ $year }}" min="2020">
        </div>
        <div class="col-auto">
            <label class="form-label small">Month</label>
            <input type="number" name="month" class="form-control rounded-3" value="{{ $month }}" min="1" max="12">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-primary rounded-3">Apply</button>
        </div>
        <div class="col-auto">
            <a href="{{ route('erp.branch-reports.pdf', request()->only('branch_id', 'year', 'month', 'date')) }}"
                class="btn btn-outline-secondary rounded-pill">Export PDF</a>
        </div>
    </form>

    @if ($tab === 'daily')
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="label">Fees collected</div>
                    <div class="value fs-5">{{ number_format($daily['fees_collected'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="label">Uniform / gear sales</div>
                    <div class="value fs-5">{{ number_format($daily['uniform_sales'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="label">Expenses</div>
                    <div class="value fs-5">{{ number_format($daily['expenses'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="label">Net (day)</div>
                    <div class="value fs-5">{{ number_format($daily['net'], 2) }}</div>
                </div>
            </div>
        </div>
        <p class="text-muted">New students on {{ $daily['date'] }}: <strong>{{ $daily['new_students'] }}</strong></p>
    @else
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="label">Revenue (month)</div>
                    <div class="value fs-5">{{ number_format($monthly['revenue'], 2) }}</div>
                    <div class="small text-muted">Fees {{ number_format($monthly['fees_collected'], 2) }} + uniform
                        {{ number_format($monthly['uniform_sales'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="label">Expenses</div>
                    <div class="value fs-5">{{ number_format($monthly['expenses'], 2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="label">Profit</div>
                    <div class="value fs-5">{{ number_format($monthly['profit'], 2) }}</div>
                    <div class="small text-muted">Pending fees: {{ number_format($monthly['pending_fees'], 2) }}</div>
                </div>
            </div>
        </div>
        <p class="text-muted">New students this month: <strong>{{ $monthly['student_growth'] }}</strong></p>
    @endif

    @if ($rankings->isNotEmpty())
        <div class="panel-card mt-4">
            <div class="panel-heading">Branch rankings (HQ)</div>
            <div class="panel-body table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Branch</th>
                            <th>Score</th>
                            <th>Revenue</th>
                            <th>Growth</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rankings as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row['branch']->name }}</td>
                                <td>{{ $row['score'] }}</td>
                                <td>{{ number_format($row['metrics']['revenue'], 0) }}</td>
                                <td>{{ $row['metrics']['student_growth'] }}</td>
                                <td>{{ $row['metrics']['avg_attendance'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
