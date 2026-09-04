@extends('layouts.student-portal')

@section('title', 'Fees — Student portal')
@section('page_title', 'Invoices & fees')
@section('page_subtitle', 'Bills, invoice details, and payment history')

@section('content')
    @if ($students->isEmpty())
        <div class="alert alert-warning border-0 rounded-4">No student linked to your account.</div>
    @else
        @include('portal._student-switcher')

        @if ($student)
            <div class="row g-3">
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
                        <div class="panel-heading">Invoices</div>
                        <div class="panel-body table-responsive">
                            <table class="table admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Invoice no.</th>
                                        <th>Due date</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Remaining</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($invoices as $inv)
                                        <tr>
                                            <td class="fw-semibold">{{ $inv->invoice_number }}</td>
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
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('portal.invoices.show', [$inv, 'student_id' => $student->id]) }}"
                                                    class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                                                <a href="{{ route('portal.invoices.pdf', $inv) }}"
                                                    class="btn btn-sm btn-outline-secondary rounded-pill">PDF</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-muted">No invoices on file yet.</td>
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
                                        <th>Invoice no.</th>
                                        <th>Receipt</th>
                                        <th>Method</th>
                                        <th class="text-end">Amount paid</th>
                                        <th></th>
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
                                            <td class="text-end">
                                                @if ($payment->invoice)
                                                    <a href="{{ route('portal.invoices.receipt', [$payment->invoice, $payment]) }}"
                                                        class="btn btn-sm btn-outline-secondary rounded-pill">Receipt PDF</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted">No payments recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
