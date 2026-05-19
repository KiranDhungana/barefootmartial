@extends('layouts.admin')

@section('title', 'Students')
@section('page_title', 'Students')
@section('page_subtitle', 'Directory')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <form method="get" class="d-flex gap-2 flex-grow-1" style="max-width:420px">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control rounded-3"
                placeholder="Search name, ID, phone…">
            <button class="btn btn-outline-secondary rounded-3" type="submit">Search</button>
        </form>
        <a href="{{ route('erp.students.create') }}" class="btn btn-admin-primary text-white">
            <i class="fa-solid fa-plus me-1"></i> Add student
        </a>
    </div>

    <div class="panel-card">
        <div class="panel-heading">All students</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Branch</th>
                        <th>Phone</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td class="fw-semibold">{{ $student->student_code }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->branch->name ?? '—' }}</td>
                            <td>{{ $student->phone ?? '—' }}</td>
                            <td>{{ optional($student->join_date)->format('M j, Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('erp.students.show', $student) }}"
                                    class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted py-4 text-center">No students yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $students->links() }}</div>
@endsection
