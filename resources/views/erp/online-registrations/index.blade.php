@extends('layouts.admin')
@section('title', 'Online registrations')
@section('page_title', 'Online registration requests')
@section('page_subtitle', 'Review and approve student signups from the public website')

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info border-0 rounded-4 mb-3">
        Requests from the home page arrive as <strong>pending</strong>. Approve by creating a student record, or reject
        if the signup should not proceed.
    </div>

    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label class="form-label small text-muted mb-1">Status</label>
            <select name="status" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach (['pending', 'contacted', 'enrolled', 'rejected'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Parent</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registrations as $r)
                        <tr>
                            <td>{{ $r->created_at->format('M j, Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $r->student_name }}</div>
                                @if ($r->message)
                                    <div class="small text-muted">{{ Str::limit($r->message, 80) }}</div>
                                @endif
                            </td>
                            <td>{{ $r->parent_name ?? '—' }}</td>
                            <td>{{ $r->phone }}</td>
                            <td>{{ $r->email ?? '—' }}</td>
                            <td>{{ $r->branch->name ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $r->status === 'pending' ? 'warning text-dark' : ($r->status === 'rejected' ? 'secondary' : ($r->status === 'enrolled' ? 'success' : 'info')) }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($r->student_id)
                                    <a href="{{ route('erp.students.show', $r->student_id) }}" class="btn btn-sm btn-outline-secondary rounded-pill">View student</a>
                                @else
                                    <form method="post" action="{{ route('erp.online-registrations.convert', $r) }}" class="d-inline"
                                        onsubmit="return confirm('Approve this registration and create a pending student record?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill">Approve</button>
                                    </form>
                                    <form method="post" action="{{ route('erp.online-registrations.status', $r) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"
                                            onclick="return confirm('Reject this registration request?');">Reject</button>
                                    </form>
                                @endif
                                <form method="post" action="{{ route('erp.online-registrations.status', $r) }}" class="d-inline ms-1">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto rounded-pill"
                                        onchange="this.form.submit()">
                                        @foreach (['pending', 'contacted', 'enrolled', 'rejected'] as $s)
                                            <option value="{{ $s }}" @selected($r->status === $s)>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted text-center py-4">No online registration requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $registrations->links() }}
        </div>
    </div>
@endsection
