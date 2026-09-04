@extends('layouts.student-portal')

@section('title', 'Attendance — Student portal')
@section('page_title', 'Attendance')
@section('page_subtitle', 'This month')

@section('content')
    @if ($students->isEmpty())
        <div class="alert alert-warning border-0 rounded-4">No student linked to your account.</div>
    @else
        @include('portal._student-switcher')

        @if ($student)
            <div class="panel-card">
                <div class="panel-heading">Attendance — {{ now()->format('F Y') }}</div>
                <div class="panel-body p-4">
                    @if ($attendanceSummary)
                        <p class="fs-2 fw-bold mb-1">{{ $attendanceSummary->percent }}%</p>
                        <p class="text-muted mb-0">
                            {{ $attendanceSummary->present_days }} present,
                            {{ $attendanceSummary->late_days }} late this month
                        </p>
                    @else
                        <p class="text-muted mb-0">No attendance recorded yet this month.</p>
                    @endif
                </div>
            </div>
        @endif
    @endif
@endsection
