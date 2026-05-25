@extends('layouts.admin')
@section('title', 'Bulk attendance')
@section('page_title', 'Bulk attendance')
@section('content')
    @if (session('success'))<div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>@endif
    <form method="get" class="mb-3 d-flex gap-2 align-items-center">
        <label class="small text-muted mb-0">Date</label>
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control rounded-3" style="max-width:180px" onchange="this.form.submit()">
        <a href="{{ route('erp.attendance.index', ['date' => $date->format('Y-m-d')]) }}" class="btn btn-outline-secondary rounded-pill">Daily view</a>
    </form>
    <form method="post" action="{{ route('erp.attendance.bulk.store') }}">
        @csrf
        <input type="hidden" name="attendance_date" value="{{ $date->format('Y-m-d') }}">
        <div class="panel-card"><div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead><tr><th>Student</th><th>Present</th><th>Late</th><th>Absent</th><th>Clear</th></tr></thead>
                <tbody>
                    @foreach ($students as $st)
                        @php $cur = $records->get($st->id)?->status; @endphp
                        <tr>
                            <td>{{ $st->name }}</td>
                            <td><input type="radio" name="statuses[{{ $st->id }}]" value="present" @checked($cur === 'present')></td>
                            <td><input type="radio" name="statuses[{{ $st->id }}]" value="late" @checked($cur === 'late')></td>
                            <td><input type="radio" name="statuses[{{ $st->id }}]" value="absent" @checked($cur === 'absent')></td>
                            <td><input type="radio" name="statuses[{{ $st->id }}]" value="" @checked(! $cur)></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>
        <button class="btn btn-admin-primary text-white mt-3 rounded-pill px-4">Save all</button>
    </form>
@endsection
