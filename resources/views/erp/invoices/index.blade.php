@extends('layouts.admin')

@section('title', 'Invoices')
@section('page_title', 'Invoices')
@section('page_subtitle', 'Fees')

@section('content')
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <form method="get" class="d-flex gap-2 align-items-center">
            <select name="status" class="form-select rounded-3" style="width:auto" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="paid" @selected(request('status') === 'paid')>Paid</option>
            </select>
        </form>
        <a href="{{ route('erp.invoices.create') }}" class="btn btn-admin-primary text-white">New invoice</a>
    </div>

    <div class="panel-card">
        <div class="panel-heading">Fee invoices</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="fw-semibold">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->student->name }}</td>
                            <td>{{ number_format($invoice->amount, 2) }}</td>
                            <td>{{ optional($invoice->due_date)->format('M j, Y') ?? '—' }}</td>
                            <td><span
                                    class="badge rounded-pill {{ $invoice->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($invoice->status) }}</span>
                            </td>
                            <td class="text-end"><a href="{{ route('erp.invoices.show', $invoice) }}"
                                    class="btn btn-sm btn-outline-primary rounded-pill">View</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted py-4 text-center">No invoices.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $invoices->links() }}</div>
@endsection
