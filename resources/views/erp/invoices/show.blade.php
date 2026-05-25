@extends('layouts.admin')

@section('title', $invoice->invoice_number)
@section('page_title', $invoice->invoice_number)
@section('page_subtitle', $invoice->student->name)

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="panel-card">
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
                            @foreach ($invoice->lineItems as $line)
                                <tr>
                                    <td>{{ $line->description }}@if ($line->size)
                                            <span class="text-muted small">({{ $line->size }})</span>
                                        @endif
                                    </td>
                                    <td>{{ $line->quantity }}</td>
                                    <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                                </tr>
                            @endforeach
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

            <div class="panel-card mt-3">
                <div class="panel-heading">Payments</div>
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
                            @forelse ($invoice->payments as $pay)
                                <tr>
                                    <td>{{ $pay->receipt_number }}</td>
                                    <td>{{ $pay->paid_at->format('M j, Y') }}</td>
                                    <td>{{ ucfirst($pay->payment_method) }}</td>
                                    <td class="text-end">{{ number_format($pay->amount, 2) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('erp.invoices.receipt', [$invoice, $pay]) }}"
                                            class="btn btn-sm btn-outline-secondary rounded-pill">Receipt PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted text-center py-3">No payments yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($invoice->balanceDue() > 0)
                <div class="panel-card mt-3">
                    <div class="panel-heading">Record payment</div>
                    <div class="panel-body p-4">
                        <form method="post" action="{{ route('erp.invoices.payments.store', $invoice) }}"
                            class="row g-2 align-items-end">
                            @csrf
                            <div class="col-md-3">
                                <label class="form-label small">Amount</label>
                                <input type="number" step="0.01" name="amount" class="form-control rounded-3" required
                                    max="{{ $invoice->balanceDue() }}" value="{{ $invoice->balanceDue() }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Method</label>
                                <select name="payment_method" class="form-select rounded-3">
                                    @foreach ($paymentMethods as $m)
                                        <option value="{{ $m }}">{{ ucfirst($m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Notes</label>
                                <input type="text" name="notes" class="form-control rounded-3">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success w-100 rounded-pill">Pay</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="panel-card">
                <div class="panel-heading">Summary</div>
                <div class="panel-body p-4">
                    <p><strong>Student:</strong> {{ $invoice->student->name }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge rounded-pill {{ match ($invoice->status) {
                            'paid' => 'bg-success',
                            'overdue' => 'bg-danger',
                            'partial' => 'bg-info',
                            default => 'bg-warning text-dark',
                        } }}">{{ $invoice->statusLabel() }}</span>
                    </p>
                    <p><strong>Due:</strong> {{ optional($invoice->due_date)->format('M j, Y') ?? '—' }}</p>
                    @if ($invoice->is_scholarship_waiver)
                        <p class="small text-success mb-0"><i class="fa-solid fa-graduation-cap me-1"></i> Scholarship waiver applied</p>
                    @endif
                </div>
            </div>
            <div class="panel-card mt-3">
                <div class="panel-heading">Actions</div>
                <div class="panel-body p-4 d-grid gap-2">
                    <a href="{{ route('erp.invoices.pdf', $invoice) }}" class="btn btn-outline-primary rounded-pill">Invoice PDF</a>
                    @if ($invoice->balanceDue() > 0)
                        <a href="{{ route('erp.invoices.payment-slip', $invoice) }}" class="btn btn-outline-secondary rounded-pill">Payment slip PDF</a>
                        <form method="post" action="{{ route('erp.invoices.paid', $invoice) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 rounded-pill">Pay balance in full</button>
                        </form>
                        <form method="post" action="{{ route('erp.invoices.late-fee', $invoice) }}" class="d-flex gap-2">
                            @csrf
                            <input type="number" step="0.01" name="late_fee" class="form-control form-control-sm rounded-3"
                                placeholder="Late fee" value="{{ config('academy.default_late_fee', 0) }}">
                            <button type="submit" class="btn btn-outline-warning rounded-pill text-nowrap">+ Late fee</button>
                        </form>
                    @endif
                    <a href="{{ route('erp.invoices.index') }}" class="btn btn-link">Back</a>
                </div>
            </div>
        </div>
    </div>
@endsection
