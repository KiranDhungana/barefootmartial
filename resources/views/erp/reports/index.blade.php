@extends('layouts.admin')

@section('title', 'Reports')
@section('page_title', 'Reports')
@section('page_subtitle', 'Fees summary')

@section('content')
    <form method="get" class="row g-2 align-items-end mb-4">
        <div class="col-auto">
            <label class="form-label small text-muted">Year</label>
            <input type="number" name="year" class="form-control rounded-3" value="{{ $year }}" min="2000" max="2100">
        </div>
        <div class="col-auto">
            <label class="form-label small text-muted">Month</label>
            <input type="number" name="month" class="form-control rounded-3" value="{{ $month }}" min="1" max="12">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary rounded-3" type="submit">Apply</button>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Total students</div>
                <div class="value">{{ $totalStudents }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Official (central)</div>
                <div class="value">{{ $officialStudents }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Pending registration</div>
                <div class="value">{{ $pendingRegistration }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Fees collected (period)</div>
                <div class="value">{{ number_format($feesCollected, 2) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Pending fees (all open)</div>
                <div class="value">{{ number_format($pendingFees, 2) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Open invoices</div>
                <div class="value">{{ $pendingCount }}</div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('erp.reports.export', request()->only('year', 'month')) }}"
            class="btn btn-outline-primary rounded-pill">Export CSV</a>
        <a href="{{ route('erp.reports.pdf', request()->only('year', 'month')) }}"
            class="btn btn-outline-secondary rounded-pill">Export PDF</a>
    </div>
@endsection
