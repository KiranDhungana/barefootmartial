@extends('layouts.admin')

@section('title', 'Fee reminders')
@section('page_title', 'Fee reminders')
@section('page_subtitle', 'One WhatsApp message per student with total outstanding balance')

@section('content')
    <p class="text-muted mb-3">
        Each row is one student. The message includes the <strong>combined balance</strong> across all open invoices,
        plus an itemized breakdown.
    </p>
    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Open invoices</th>
                        <th class="text-end">Total outstanding</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reminders as $row)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $row['student']->name }}</div>
                                <div class="small text-muted">{{ $row['student']->student_code }}</div>
                            </td>
                            <td>
                                <div class="small">
                                    @foreach ($row['summary']['itemized'] as $item)
                                        <div>
                                            {{ $item['invoice_number'] }} —
                                            Rs. {{ number_format($item['balance'], 2) }}
                                            @if ($item['due_date'])
                                                <span class="text-muted">(due {{ $item['due_date'] }})</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-end fw-semibold text-danger">
                                Rs. {{ number_format($row['summary']['total_outstanding'], 2) }}
                            </td>
                            <td class="text-end">
                                @if ($row['whatsapp_url'])
                                    <a href="{{ $row['whatsapp_url'] }}" target="_blank" rel="noopener"
                                        class="btn btn-sm btn-success rounded-pill">
                                        <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                                    </a>
                                @else
                                    <span class="text-muted small">No phone</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center py-4">No open balances to remind.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
