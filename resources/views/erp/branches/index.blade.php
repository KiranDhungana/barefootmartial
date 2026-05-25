@extends('layouts.admin')
@section('title', 'Branches')
@section('page_title', 'Branches')
@section('content')
    @if (session('success'))<div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>@endif
    <div class="d-flex justify-content-between mb-3">
        <span></span>
        @if (auth()->user()->isSuperAdmin())
            <a href="{{ route('erp.branches.create') }}" class="btn btn-admin-primary text-white">Add branch</a>
        @endif
    </div>
    <div class="panel-card"><div class="panel-body table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Name</th><th>Code</th><th>Students</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @foreach ($branches as $b)
                    <tr>
                        <td>{{ $b->name }}</td>
                        <td>{{ $b->code }}</td>
                        <td>{{ $b->students_count }}</td>
                        <td>{{ $b->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-end">
                            <a href="{{ route('erp.branches.show', $b) }}" class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                            @if (auth()->user()->isSuperAdmin())
                                <a href="{{ route('erp.branches.edit', $b) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Edit</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
@endsection
