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
                        <div class="panel-heading">Fee summary</div>
                        <div class="panel-body p-4">
                            <div class="row g-3 text-center">
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Total fees</p>
                                    <p class="fs-4 fw-bold mb-0">{{ number_format($feeSummary['total_billed'], 2) }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Paid so far</p>
                                    <p class="fs-4 fw-bold text-success mb-0">{{ number_format($feeSummary['total_paid'], 2) }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Still to pay</p>
                                    <p class="fs-4 fw-bold {{ $feeSummary['total_remaining'] > 0 ? 'text-danger' : 'text-success' }} mb-0">
                                        {{ number_format($feeSummary['total_remaining'], 2) }}
                                    </p>
                                </div>
                            </div>
                            @if ($feeSummary['total_remaining'] > 0)
                                <p class="text-muted small mb-0 mt-3 text-center">
                                    Please pay the remaining amount at your branch. The office will update payments here.
                                </p>
                            @elseif ($invoices->isNotEmpty())
                                <p class="text-success small mb-0 mt-3 text-center">
                                    <i class="fa-solid fa-circle-check me-1"></i> All fees are paid up to date.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="panel-card">
                        <div class="panel-heading">Bills (invoices)</div>
                        <div class="panel-body table-responsive">
                            <table class="table admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Bill no.</th>
                                        <th>Due date</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Remaining</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoices as $inv)
                                        <tr>
                                            <td>{{ $inv->invoice_number }}</td>
                                            <td>{{ optional($inv->due_date)->format('M j, Y') ?? '—' }}</td>
                                            <td class="text-end">{{ number_format($inv->totalAmount(), 2) }}</td>
                                            <td class="text-end text-success">{{ number_format($inv->amount_paid, 2) }}</td>
                                            <td class="text-end {{ $inv->balanceDue() > 0 ? 'text-danger fw-semibold' : '' }}">
                                                {{ number_format($inv->balanceDue(), 2) }}
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill {{ $inv->status === 'paid' ? 'bg-success' : ($inv->status === 'overdue' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                                    {{ $inv->statusLabel() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted">No bills on file yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="panel-card">
                        <div class="panel-heading">Payment history</div>
                        <div class="panel-body table-responsive">
                            <table class="table admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Date paid</th>
                                        <th>Bill no.</th>
                                        <th>Receipt</th>
                                        <th>Method</th>
                                        <th class="text-end">Amount paid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payments as $payment)
                                        <tr>
                                            <td>{{ optional($payment->paid_at)->format('M j, Y') ?? '—' }}</td>
                                            <td>{{ $payment->invoice->invoice_number ?? '—' }}</td>
                                            <td>{{ $payment->receipt_number }}</td>
                                            <td>{{ ucfirst($payment->payment_method) }}</td>
                                            <td class="text-end text-success fw-semibold">{{ number_format($payment->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-muted">No payments recorded yet.</td>
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
