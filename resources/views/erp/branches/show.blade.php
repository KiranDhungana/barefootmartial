@extends('layouts.admin')
@section('title', $branch->name)
@section('page_title', $branch->name)
@section('content')
    <p class="text-muted">{{ $branch->address }} · {{ $branch->phone }} · {{ $branch->email }}</p>
    <a href="{{ route('erp.schedules.index', ['branch_id' => $branch->id]) }}" class="btn btn-outline-primary rounded-pill mb-3">Class schedules</a>
    <div class="panel-card"><div class="panel-heading">Weekly schedule</div><div class="panel-body table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Class</th><th>Day</th><th>Time</th><th>Coach</th></tr></thead>
            <tbody>
                @forelse ($branch->schedules as $s)
                    <tr><td>{{ $s->name }}</td><td>{{ $s->dayLabel() }}</td><td>{{ $s->start_time }}@if($s->end_time) – {{ $s->end_time }}@endif</td><td>{{ $s->coach_name ?? '—' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No schedules.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
@endsection
