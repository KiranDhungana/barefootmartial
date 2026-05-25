@extends('layouts.admin')
@section('title', 'Online registrations')
@section('page_title', 'Online registration requests')
@section('content')
    @if (session('success'))<div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>@endif
    <div class="panel-card"><div class="panel-body table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Date</th><th>Student</th><th>Phone</th><th>Branch</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($registrations as $r)
                    <tr>
                        <td>{{ $r->created_at->format('M j, Y') }}</td>
                        <td>{{ $r->student_name }}</td>
                        <td>{{ $r->phone }}</td>
                        <td>{{ $r->branch->name ?? '—' }}</td>
                        <td>{{ $r->status }}</td>
                        <td class="text-end">
                            <form method="post" action="{{ route('erp.online-registrations.convert', $r) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success rounded-pill">Convert</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $registrations->links() }}
    </div></div>
@endsection
