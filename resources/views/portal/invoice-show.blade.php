@extends('layouts.student-portal')

@section('title', $invoice->invoice_number.' — Student portal')
@section('page_title', $invoice->invoice_number)
@section('page_subtitle', $student?->name ?? 'Invoice')

@section('content')
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="{{ route('portal.fees', ['student_id' => $student?->id]) }}"
            class="btn btn-outline-secondary rounded-pill btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to invoices
        </a>
        <a href="{{ route('portal.invoices.pdf', $invoice) }}"
            class="btn btn-admin-primary text-white rounded-pill btn-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="label">Status</div>
                <div class="value fs-5">{{ $invoice->statusLabel() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="label">Total</div>
                <div class="value fs-5">{{ number_format($invoice->totalAmount(), 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="label">Balance due</div>
                <div class="value fs-5 {{ $invoice->balanceDue() > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($invoice->balanceDue(), 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card mb-3">
        <div class="panel-heading">Invoice details</div>
        <div class="panel-body p-4 small">
            <div class="row g-2">
                <div class="col-md-6"><strong>Due date:</strong> {{ optional($invoice->due_date)->format('M j, Y') ?? '—' }}</div>
                <div class="col-md-6"><strong>Issued:</strong> {{ optional($invoice->created_at)->format('M j, Y') ?? '—' }}</div>
                @if ($invoice->billing_period)
                    <div class="col-md-6"><strong>Billing period:</strong> {{ $invoice->billing_period }}</div>
                @endif
                @if ($invoice->notes)
                    <div class="col-12"><strong>Notes:</strong> {{ $invoice->notes }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="panel-card mb-3">
        <div class="panel-heading">Line items</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th class="text-end">Unit</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoice->lineItems as $line)
                        <tr>
                            <td>
                                {{ $line->description }}
                                @if ($line->size)
                                    <span class="text-muted small">({{ $line->size }})</span>
                                @endif
                            </td>
                            <td>{{ $line->quantity }}</td>
                            <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted">No line items.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end">Subtotal</td>
                        <td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if ($invoice->discount_amount > 0)
                        <tr>
                            <td colspan="3" class="text-end text-muted">Discount</td>
                            <td class="text-end text-muted">−{{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->late_fee_amount > 0)
                        <tr>
                            <td colspan="3" class="text-end text-muted">Late fee</td>
                            <td class="text-end">{{ number_format($invoice->late_fee_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="fw-semibold">
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-end">{{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end">Paid</td>
                        <td class="text-end text-success">{{ number_format($invoice->amount_paid, 2) }}</td>
                    </tr>
                    <tr class="fw-semibold">
                        <td colspan="3" class="text-end">Balance due</td>
                        <td class="text-end text-danger">{{ number_format($invoice->balanceDue(), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-heading">Payments on this invoice</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->receipt_number }}</td>
                            <td>{{ optional($payment->paid_at)->format('M j, Y') ?? '—' }}</td>
                            <td>{{ ucfirst($payment->payment_method) }}</td>
                            <td class="text-end text-success">{{ number_format($payment->amount, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('portal.invoices.receipt', [$invoice, $payment]) }}"
                                    class="btn btn-sm btn-outline-secondary rounded-pill">Receipt PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">No payments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
