@extends('layouts.admin')

@section('title', 'Trainers')
@section('page_title', 'Trainers')
@section('page_subtitle', 'Staff payroll profiles')

@section('content')
    <div class="mb-3 text-end">
        <a href="{{ route('erp.trainers.create') }}" class="btn btn-admin-primary text-white">Add trainer</a>
    </div>
    <div class="panel-card">
        <div class="panel-heading">Directory</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Salary mode</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trainers as $t)
                        <tr>
                            <td>{{ $t->name }}</td>
                            <td>{{ $t->role_title ?? '—' }}</td>
                            <td>{{ ucfirst($t->salary_mode) }}</td>
                            <td class="text-end">
                                <a href="{{ route('erp.trainers.edit', $t) }}"
                                    class="btn btn-sm btn-outline-primary rounded-pill">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No trainers yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $trainers->links() }}</div>
@endsection
