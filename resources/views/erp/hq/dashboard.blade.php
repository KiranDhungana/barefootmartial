@extends('layouts.admin')

@section('title', 'Head office')
@section('page_title', 'Head office dashboard')
@section('page_subtitle', 'All branches')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Branches</div>
                <div class="value">{{ $totalBranches }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Students</div>
                <div class="value">{{ $totalStudents }}</div>
                <div class="small text-muted">{{ $officialStudents }} official · {{ $pendingRegistration }} pending</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Fees collected (month)</div>
                <div class="value">{{ number_format($collectedMonth, 0) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Outstanding / overdue</div>
                <div class="value fs-6">{{ number_format($pendingFees, 0) }}</div>
                <div class="small text-danger">{{ $overdueCount }} overdue invoices</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">New today</div>
                <div class="value">{{ $newToday }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Inactive (14+ days)</div>
                <div class="value">{{ $inactiveCount }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Active / inactive status</div>
                <div class="value fs-6">{{ $activeStudents }} / {{ $inactiveStudents }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Uniform sales (month)</div>
                <div class="value fs-6">{{ number_format($uniformSalesMonth, 0) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Belt exams (month)</div>
                <div class="value">{{ $beltExamsMonth }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-heading">Branch rankings (this month)</div>
                <div class="panel-body table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Score</th>
                                <th>Revenue</th>
                                <th>Growth</th>
                                <th>Attendance</th>
                                <th>Compliance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rankings as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $row['branch']->name }}</td>
                                    <td>{{ $row['score'] }}</td>
                                    <td>{{ number_format($row['metrics']['revenue'], 0) }}</td>
                                    <td>{{ $row['metrics']['student_growth'] }}</td>
                                    <td>{{ $row['metrics']['avg_attendance'] }}%</td>
                                    <td>{{ $row['metrics']['compliance'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card mb-3">
                <div class="panel-heading">Students by branch</div>
                <div class="panel-body p-3">
                    @foreach ($branchGrowth as $b)
                        <div class="d-flex justify-content-between small py-1">
                            <span>{{ $b->name }}</span>
                            <strong>{{ $b->students_count }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
            @if (count($attendanceChartLabels))
                <div class="panel-card">
                    <div class="panel-heading">Org attendance (month)</div>
                    <div class="panel-body p-3">
                        <canvas id="hqAttendanceChart" height="140"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="panel-card mt-3">
        <div class="panel-heading">Official registrations (last 6 months)</div>
        <div class="panel-body p-3">
            <canvas id="hqGrowthChart" height="120"></canvas>
        </div>
    </div>

    <div class="mt-3 d-flex flex-wrap gap-2">
        <a href="{{ route('erp.branch-reports.index') }}" class="btn btn-outline-primary rounded-pill">Branch reports</a>
        <a href="{{ route('erp.compliance.index') }}" class="btn btn-outline-primary rounded-pill">Compliance</a>
        <a href="{{ route('erp.expenses.index') }}" class="btn btn-outline-secondary rounded-pill">Expenses</a>
        <a href="{{ route('erp.belts.index') }}" class="btn btn-outline-secondary rounded-pill">Belt promotions</a>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @if (count($attendanceChartLabels))
        <script>
            new Chart(document.getElementById('hqAttendanceChart'), {
                type: 'bar',
                data: {
                    labels: @json($attendanceChartLabels),
                    datasets: [{
                        label: 'Check-ins',
                        data: @json($attendanceChartCounts),
                        backgroundColor: 'rgba(59, 130, 246, 0.45)'
                    }]
                },
                options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
            });
        </script>
    @endif
    <script>
        new Chart(document.getElementById('hqGrowthChart'), {
            type: 'line',
            data: {
                labels: @json($growthLabels),
                datasets: [{
                    label: 'New official members',
                    data: @json($growthCounts),
                    borderColor: 'rgb(34, 197, 94)',
                    tension: 0.2
                }]
            },
            options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    </script>
@endpush
