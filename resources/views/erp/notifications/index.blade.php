@extends('layouts.admin')
@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('content')
    @if (session('success'))<div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger border-0 rounded-4">{{ session('error') }}</div>@endif
    <div class="row g-3">
        <div class="col-lg-5">
            <form method="post" action="{{ route('erp.notifications.send') }}" class="panel-card p-4">
                @csrf
                <h6>Send message</h6>
                <select name="student_id" class="form-select rounded-3 mb-2" required>
                    @foreach (\App\Support\BranchScope::students()->orderBy('name')->get() as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
                <select name="channel" class="form-select rounded-3 mb-2"><option value="email">Email</option><option value="sms">SMS (log)</option><option value="whatsapp">WhatsApp (log)</option></select>
                <input name="subject" class="form-control rounded-3 mb-2" placeholder="Subject (email)">
                <textarea name="body" class="form-control rounded-3 mb-2" rows="4" required></textarea>
                <button class="btn btn-admin-primary text-white w-100">Send / log</button>
            </form>
        </div>
        <div class="col-lg-7">
            <div class="panel-card mb-3"><div class="panel-heading">Overdue reminders</div><div class="panel-body">
                @foreach ($overdue->take(10) as $inv)
                    <form method="post" action="{{ route('erp.notifications.invoice', $inv) }}" class="d-flex justify-content-between align-items-center border-bottom py-2 small">
                        @csrf
                        <span>{{ $inv->student->name }} — {{ $inv->invoice_number }}</span>
                        <button class="btn btn-sm btn-outline-primary rounded-pill">Send reminder</button>
                    </form>
                @endforeach
            </div></div>
            <div class="panel-card"><div class="panel-heading">Recent log</div><div class="panel-body table-responsive" style="max-height:320px">
                <table class="table admin-table mb-0 small">
                    <thead><tr><th>When</th><th>Channel</th><th>To</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr><td>{{ $log->created_at->format('M j H:i') }}</td><td>{{ $log->channel }}</td><td>{{ Str::limit($log->recipient, 24) }}</td><td>{{ $log->status }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
@endsection
