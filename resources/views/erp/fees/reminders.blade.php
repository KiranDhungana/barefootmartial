@extends('layouts.admin')

@section('title', 'Fee reminders')
@section('page_title', 'Fee reminders')

@section('content')
    <p class="text-muted mb-3">Send reminders via WhatsApp (opens on your phone). Automated SMS/email can be added in a later phase.</p>
    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Invoice</th>
                        <th>Balance</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $row)
                        <tr>
                            <td>{{ $row['student']->name }}</td>
                            <td>{{ $row['invoice']->invoice_number }}</td>
                            <td>{{ number_format($row['invoice']->balanceDue(), 2) }}</td>
                            <td class="text-end">
                                @if ($row['whatsapp_url'])
                                    <a href="{{ $row['whatsapp_url'] }}" target="_blank" rel="noopener"
                                        class="btn btn-sm btn-success rounded-pill">WhatsApp</a>
                                @else
                                    <span class="text-muted small">No phone</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
