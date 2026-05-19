@extends('layouts.admin')

@section('title', $invoice->invoice_number)
@section('page_title', $invoice->invoice_number)
@section('page_subtitle', $invoice->student->name)

@section('content')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-heading">Details</div>
                <div class="panel-body p-4">
                    <p><strong>Student:</strong> {{ $invoice->student->name }} ({{ $invoice->student->student_code }})</p>
                    <p><strong>Amount:</strong> {{ number_format($invoice->amount, 2) }}</p>
                    <p><strong>Due:</strong> {{ optional($invoice->due_date)->format('M j, Y') ?? '—' }}</p>
                    <p><strong>Status:</strong> <span
                            class="badge {{ $invoice->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($invoice->status) }}</span>
                    </p>
                    @if ($invoice->paid_at)
                        <p><strong>Paid at:</strong> {{ $invoice->paid_at->format('M j, Y H:i') }}</p>
                    @endif
                    @if ($invoice->notes)
                        <p class="mb-0"><strong>Notes:</strong> {{ $invoice->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card">
                <div class="panel-heading">Actions</div>
                <div class="panel-body p-4 d-grid gap-2">
                    <a href="{{ route('erp.invoices.pdf', $invoice) }}" class="btn btn-outline-primary rounded-pill">Download
                        PDF</a>
                    @if ($invoice->status !== 'paid')
                        <form method="post" action="{{ route('erp.invoices.paid', $invoice) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 rounded-pill">Mark paid</button>
                        </form>
                    @else
                        <form method="post" action="{{ route('erp.invoices.pending', $invoice) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100 rounded-pill">Mark pending</button>
                        </form>
                    @endif
                    <a href="{{ route('erp.invoices.index') }}" class="btn btn-link">Back to list</a>
                </div>
            </div>
        </div>
    </div>
@endsection
