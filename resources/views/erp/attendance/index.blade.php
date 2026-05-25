@extends('layouts.admin')

@section('title', 'Attendance')
@section('page_title', 'Attendance')
@section('page_subtitle', 'Daily marking, monthly % & inactive alerts')

@section('content')
    @if ($inactiveStudents->isNotEmpty())
        <div class="alert alert-warning border-0 rounded-4 mb-3">
            <strong>Inactive (no check-in for {{ config('academy.attendance_inactive_days', 14) }}+ days):</strong>
            {{ $inactiveStudents->pluck('name')->take(8)->join(', ') }}
            @if ($inactiveStudents->count() > 8)
                … +{{ $inactiveStudents->count() - 8 }} more
            @endif
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('erp.attendance.bulk', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-admin-primary text-white rounded-pill">
            <i class="fa-solid fa-list-check me-1"></i> Bulk mark attendance
        </a>
    </div>

    <div class="panel-card mb-3">
        <div class="panel-heading">Day roster — {{ $date->format('l, M j, Y') }}</div>
        <div class="panel-body p-4">
            <form method="get" class="row g-2 align-items-end mb-4">
                <input type="hidden" name="summary_month" value="{{ $summaryMonth }}">
                <div class="col-auto">
                    <label class="form-label small text-muted">Date</label>
                    <input type="date" name="date" class="form-control rounded-3" value="{{ $date->format('Y-m-d') }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary rounded-3" type="submit">Load</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $s)
                            @php $r = $records->get($s->id); @endphp
                            <tr>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->student_code }}</td>
                                <td>{{ $r ? ucfirst($r->status) : '—' }}</td>
                                <td class="text-end">
                                    <form method="post" action="{{ route('erp.attendance.day') }}"
                                        class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                        @csrf
                                        <input type="hidden" name="attendance_date"
                                            value="{{ $date->format('Y-m-d') }}">
                                        <input type="hidden" name="student_id" value="{{ $s->id }}">
                                        <button type="submit" name="status" value="present"
                                            class="btn btn-sm btn-success rounded-pill">Present</button>
                                        <button type="submit" name="status" value="late"
                                            class="btn btn-sm btn-warning rounded-pill text-dark">Late</button>
                                        <button type="submit" name="status" value="absent"
                                            class="btn btn-sm btn-outline-secondary rounded-pill">Absent</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-heading">Monthly summary</div>
        <div class="panel-body p-4">
            <form method="get" class="row g-2 align-items-end mb-3">
                <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                <div class="col-auto">
                    <label class="form-label small text-muted">Month</label>
                    <input type="month" name="summary_month" class="form-control rounded-3"
                        value="{{ $summaryMonth }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary rounded-3" type="submit">Apply</button>
                </div>
            </form>
            <p class="small text-muted">Rate = (present + late) ÷ {{ $daysInMonth }} days. Low threshold:
                {{ config('academy.attendance_low_percent', 40) }}%.</p>
            <div class="table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Present</th>
                            <th>Late</th>
                            <th>Absent</th>
                            <th>Monthly %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summaryRows as $row)
                            @php
                                $low = $row->percent < config('academy.attendance_low_percent', 40);
                            @endphp
                            <tr class="{{ $low && $row->percent > 0 ? 'table-warning' : '' }}">
                                <td>{{ $row->student->name }}</td>
                                <td>{{ $row->present_days }}</td>
                                <td>{{ $row->late_days }}</td>
                                <td>{{ $row->absent_days }}</td>
                                <td class="fw-semibold">{{ $row->percent }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
