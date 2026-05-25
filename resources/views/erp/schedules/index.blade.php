@extends('layouts.admin')
@section('title', 'Schedules')
@section('page_title', 'Class schedules')
@section('content')
    <div class="d-flex flex-wrap gap-2 mb-3 justify-content-between">
        <form method="get" class="d-flex gap-2">
            <select name="branch_id" class="form-select rounded-3" onchange="this.form.submit()">
                <option value="">All branches</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('erp.schedules.create') }}" class="btn btn-admin-primary text-white">Add class</a>
    </div>
    <div class="panel-card"><div class="panel-body table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Branch</th><th>Class</th><th>Day</th><th>Time</th><th></th></tr></thead>
            <tbody>
                @foreach ($schedules as $s)
                    <tr>
                        <td>{{ $s->branch->name }}</td>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->dayLabel() }}</td>
                        <td>{{ $s->start_time }}</td>
                        <td class="text-end">
                            <a href="{{ route('erp.schedules.edit', $s) }}" class="btn btn-sm btn-outline-primary rounded-pill">Edit</a>
                            <form method="post" action="{{ route('erp.schedules.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger rounded-pill">Del</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
@endsection
