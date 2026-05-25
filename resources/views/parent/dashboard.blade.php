@extends('layouts.parent')

@section('title', 'Parent portal')
@section('page_title', 'My children')

@section('content')
    @if ($children->isEmpty())
        <div class="alert alert-warning border-0 rounded-4">
            No students linked to your account. Contact your branch to connect your login.
        </div>
    @else
        <form method="get" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
            <label class="small text-muted mb-0">View child:</label>
            <select name="student_id" class="form-select rounded-3" style="max-width:280px" onchange="this.form.submit()">
                @foreach ($children as $c)
                    <option value="{{ $c->id }}" @selected($student && $student->id === $c->id)>{{ $c->name }} ({{ $c->student_code }})</option>
                @endforeach
            </select>
        </form>

        @if ($student)
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="panel-card">
                        <div class="panel-heading">Profile</div>
                        <div class="panel-body p-4 small">
                            <p class="mb-1"><strong>Branch:</strong> {{ $student->branch->name ?? '—' }}</p>
                            <p class="mb-1"><strong>Belt:</strong> {{ $student->belt_rank ?? '—' }}</p>
                            <p class="mb-1"><strong>Status:</strong> {{ $student->statusLabel() }}</p>
                            <p class="mb-0"><strong>Coach:</strong> {{ $student->coach_name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel-card">
                        <div class="panel-heading">Attendance this month</div>
                        <div class="panel-body p-4">
                            @if ($attendanceSummary)
                                <p class="mb-0 fs-4 fw-bold">{{ $attendanceSummary->percent }}%</p>
                                <p class="text-muted small mb-0">{{ $attendanceSummary->present_days }} present, {{ $attendanceSummary->late_days }} late this month</p>
                            @else
                                <p class="text-muted mb-0">No attendance recorded yet this month.</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="panel-card">
                        <div class="panel-heading">Fee invoices</div>
                        <div class="panel-body table-responsive">
                            <table class="table admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Due</th>
                                        <th>Status</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoices as $inv)
                                        <tr>
                                            <td>{{ $inv->invoice_number }}</td>
                                            <td>{{ optional($inv->due_date)->format('M j, Y') ?? '—' }}</td>
                                            <td>{{ ucfirst($inv->status) }}</td>
                                            <td class="text-end">{{ number_format($inv->balanceDue(), 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted">No invoices on file.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="panel-card">
                        <div class="panel-heading">Notices</div>
                        <div class="panel-body p-4">
                            <ul class="mb-0">
                                @forelse ($notices as $n)
                                    <li class="mb-2"><strong>{{ $n->title }}</strong> — {{ $n->description }}</li>
                                @empty
                                    <li class="text-muted">No notices.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
