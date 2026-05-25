@extends('layouts.admin')

@section('title', 'Invoices')
@section('page_title', 'Invoices')

@section('content')
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <form method="get" class="d-flex gap-2">
            <select name="status" class="form-select rounded-3" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (config('academy.invoice_statuses', []) as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </form>
        <div class="d-flex gap-2">
            <a href="{{ route('erp.fees.index') }}" class="btn btn-outline-secondary rounded-pill">Fee tracking</a>
            <a href="{{ route('erp.invoices.create') }}" class="btn btn-admin-primary text-white">Smart payment entry</a>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Student</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->student->name ?? '—' }}</td>
                            <td>{{ number_format($invoice->amount, 2) }}</td>
                            <td>{{ number_format($invoice->amount_paid, 2) }}</td>
                            <td>{{ number_format($invoice->balanceDue(), 2) }}</td>
                            <td>
                                <span class="badge rounded-pill {{ match ($invoice->status) {
                                    'paid' => 'bg-success',
                                    'overdue' => 'bg-danger',
                                    'partial' => 'bg-info',
                                    default => 'bg-warning text-dark',
                                } }}">{{ $invoice->statusLabel() }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('erp.invoices.show', $invoice) }}"
                                    class="btn btn-sm btn-outline-primary rounded-pill">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted text-center py-4">No invoices.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $invoices->links() }}</div>
@endsection
