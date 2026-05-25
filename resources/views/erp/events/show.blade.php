@extends('layouts.admin')
@section('title', $event->title)
@section('page_title', $event->title)
@section('content')
    @if (session('success'))<div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>@endif
    <a href="{{ route('erp.events.edit', $event) }}" class="btn btn-outline-primary rounded-pill mb-3">Edit event</a>
    <div class="row g-3">
        <div class="col-lg-5">
            <form method="post" action="{{ route('erp.events.register', $event) }}" class="panel-card p-4">
                @csrf
                <h6>Register student</h6>
                <select name="student_id" class="form-select rounded-3 mb-2" required>
                    @foreach ($students as $st)
                        <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->student_code }})</option>
                    @endforeach
                </select>
                <input name="category" class="form-control rounded-3 mb-2" placeholder="Category (optional)">
                <button class="btn btn-admin-primary text-white w-100">Register</button>
            </form>
        </div>
        <div class="col-lg-7">
            <div class="panel-card"><div class="panel-heading">Registrations</div><div class="panel-body table-responsive">
                <table class="table admin-table mb-0">
                    <thead><tr><th>Student</th><th>Category</th><th>Fee</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($event->registrations as $r)
                            <tr><td>{{ $r->student->name }}</td><td>{{ $r->category ?? '—' }}</td><td>{{ number_format($r->fee_amount, 2) }}</td><td>{{ $r->status }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">None yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    </div>
@endsection
