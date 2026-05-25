@extends('layouts.admin')

@section('title', 'Students')
@section('page_title', 'Students')
@section('page_subtitle', 'Directory')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <form method="get" class="d-flex flex-wrap gap-2 flex-grow-1">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control rounded-3"
                style="max-width:220px" placeholder="Search…">
            <select name="registration_status" class="form-select rounded-3" style="max-width:160px">
                <option value="">All registration</option>
                <option value="official" @selected(request('registration_status') === 'official')>Official</option>
                <option value="pending" @selected(request('registration_status') === 'pending')>Pending</option>
            </select>
            <select name="status" class="form-select rounded-3" style="max-width:140px">
                <option value="">All status</option>
                @foreach (config('academy.student_statuses', []) as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary rounded-3" type="submit">Filter</button>
        </form>
        <div class="d-flex gap-2">
            @if (auth()->user()->canImportStudents())
                <a href="{{ route('erp.students.import') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fa-solid fa-file-import me-1"></i> Import
                </a>
            @endif
            <a href="{{ route('erp.students.create') }}" class="btn btn-admin-primary text-white">
                <i class="fa-solid fa-plus me-1"></i> Add student
            </a>
        </div>
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
                        <th>Belt</th>
                        <th>Registration</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td class="fw-semibold">{{ $student->student_code }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->branch->name ?? '—' }}</td>
                            <td>{{ $student->belt_rank ?? '—' }}</td>
                            <td>
                                @if ($student->isOfficial())
                                    <span class="badge bg-success rounded-pill">Official</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                @endif
                            </td>
                            <td>{{ $student->statusLabel() }}</td>
                            <td class="text-end">
                                <a href="{{ route('erp.students.show', $student) }}"
                                    class="btn btn-sm btn-outline-primary rounded-pill">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted py-4 text-center">No students yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $students->links() }}</div>
@endsection
