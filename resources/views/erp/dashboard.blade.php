@extends('layouts.admin')

@section('title', 'Academy console')
@section('page_title', 'Academy console')
@section('page_subtitle', 'Overview')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="label">Students</div>
                        <div class="value">{{ $totalStudents }}</div>
                    </div>
                    <div class="icon-wrap bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>
        @if ($showFinance)
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="label">Fees collected (month)</div>
                            <div class="value">{{ number_format($collectedMonth, 2) }}</div>
                        </div>
                        <div class="icon-wrap bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="label">Pending invoices</div>
                            <div class="value">{{ $pendingInvoices }}</div>
                        </div>
                        <div class="icon-wrap bg-warning bg-opacity-10 text-warning">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="label">Pending balance</div>
                            <div class="value">{{ number_format($pendingFeesAmount, 2) }}</div>
                        </div>
                        <div class="icon-wrap bg-danger bg-opacity-10 text-danger">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="label">Role</div>
                            <div class="value fs-5 mt-1">Staff</div>
                        </div>
                        <div class="icon-wrap bg-secondary bg-opacity-10 text-secondary">
                            <i class="fa-solid fa-id-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="panel-card">
                <div class="panel-heading">Attendance check-ins (this month)</div>
                <div class="panel-body p-4">
                    @if (count($attendanceChartLabels))
                        <canvas id="attendanceChart" height="120"></canvas>
                    @else
                        <p class="text-muted mb-0">No attendance recorded yet this month.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-heading">Shortcuts</div>
                <div class="panel-body p-4 d-grid gap-2">
                    <a href="{{ route('erp.students.create') }}" class="btn btn-admin-primary text-white">Add student</a>
                    <a href="{{ route('erp.attendance.index') }}" class="btn btn-outline-primary rounded-pill">Daily attendance</a>
                    @if ($showFinance)
                        <a href="{{ route('erp.invoices.create') }}" class="btn btn-outline-primary rounded-pill">New invoice</a>
                        <a href="{{ route('erp.reports.index') }}" class="btn btn-outline-secondary rounded-pill">Reports</a>
                    @endif
                    <a href="{{ route('two-factor.setup') }}" class="btn btn-outline-secondary rounded-pill">Two-factor auth</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if (count($attendanceChartLabels))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            const ctx = document.getElementById('attendanceChart');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($attendanceChartLabels),
                    datasets: [{
                        label: 'Check-ins',
                        data: @json($attendanceChartCounts),
                        backgroundColor: 'rgba(59, 130, 246, 0.45)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endpush
