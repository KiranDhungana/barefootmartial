@extends('layouts.admin')
@section('title', 'Events')
@section('page_title', 'Events & tournaments')
@section('content')
    @if (session('success'))<div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>@endif
    <a href="{{ route('erp.events.create') }}" class="btn btn-admin-primary text-white mb-3">New event</a>
    <div class="panel-card"><div class="panel-body table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Title</th><th>Date</th><th>Branch</th><th>Registered</th><th></th></tr></thead>
            <tbody>
                @foreach ($events as $e)
                    <tr>
                        <td>{{ $e->title }}</td>
                        <td>{{ optional($e->event_date)->format('M j, Y') ?? '—' }}</td>
                        <td>{{ $e->branch->name ?? 'All' }}</td>
                        <td>{{ $e->registrations->count() }}</td>
                        <td class="text-end"><a href="{{ route('erp.events.show', $e) }}" class="btn btn-sm btn-outline-primary rounded-pill">Manage</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
@endsection
