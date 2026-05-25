@extends('layouts.admin')

@section('title', 'Fee tracking')
@section('page_title', 'Fee tracking')

@section('content')
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <ul class="nav nav-pills gap-1">
            <li class="nav-item">
                <a class="nav-link rounded-pill {{ $tab === 'due' ? 'active' : '' }}"
                    href="{{ route('erp.fees.index', ['tab' => 'due']) }}">Due ({{ $counts['due'] }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill {{ $tab === 'overdue' ? 'active' : '' }}"
                    href="{{ route('erp.fees.index', ['tab' => 'overdue']) }}">Overdue ({{ $counts['overdue'] }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill {{ $tab === 'partial' ? 'active' : '' }}"
                    href="{{ route('erp.fees.index', ['tab' => 'partial']) }}">Partial ({{ $counts['partial'] }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill {{ $tab === 'paid' ? 'active' : '' }}"
                    href="{{ route('erp.fees.index', ['tab' => 'paid']) }}">Paid ({{ $counts['paid'] }})</a>
            </li>
        </ul>
        <a href="{{ route('erp.fees.reminders') }}" class="btn btn-outline-success rounded-pill">
            <i class="fa-brands fa-whatsapp me-1"></i> Reminders
        </a>
    </div>

    @if ($studentsPendingFee->isNotEmpty())
        <div class="alert alert-warning border-0 rounded-4 mb-3">
            <strong>Students marked “pending fee”:</strong>
            {{ $studentsPendingFee->pluck('name')->join(', ') }}
        </div>
    @endif

    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Student</th>
                        <th>Branch</th>
                        <th>Total</th>
                        <th>Balance</th>
                        <th>Due date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->student->name }}</td>
                            <td>{{ $invoice->student->branch->name ?? '—' }}</td>
                            <td>{{ number_format($invoice->amount, 2) }}</td>
                            <td class="fw-semibold">{{ number_format($invoice->balanceDue(), 2) }}</td>
                            <td>{{ optional($invoice->due_date)->format('M j, Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('erp.invoices.show', $invoice) }}"
                                    class="btn btn-sm btn-outline-primary rounded-pill">Collect</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted text-center py-4">No records in this tab.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $invoices->links() }}</div>
@endsection
