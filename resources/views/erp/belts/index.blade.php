@extends('layouts.admin')

@section('title', 'Belt promotions')
@section('page_title', 'Belt promotions')
@section('page_subtitle')
    {{ $eligible->count() }} eligible for exam
@endsection

@section('content')
    <div class="alert alert-info border-0 rounded-4 mb-3">
        Exam eligibility uses {{ config('academy.belt_months_between_exams', 3) }}+ months since last promotion or joining date.
    </div>

    <div class="panel-card mb-3">
        <div class="panel-heading text-success">Suggested for promotion</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Current</th>
                        <th>Next</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($eligible as $row)
                        <tr>
                            <td>{{ $row->student->name }} <span class="text-muted small">{{ $row->student->student_code }}</span></td>
                            <td>{{ $row->student->belt_rank ?? '—' }}</td>
                            <td>{{ $row->next_belt }}</td>
                            <td class="text-end">
                                <a href="{{ route('erp.belts.promote', $row->student) }}"
                                    class="btn btn-sm btn-success rounded-pill">Promote</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center py-3">No eligible students right now.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-heading">All official students</div>
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Belt</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row->student->name }}</td>
                            <td>{{ $row->student->belt_rank ?? '—' }}</td>
                            <td class="small {{ $row->eligible ? 'text-success' : 'text-muted' }}">{{ $row->reason }}</td>
                            <td class="text-end">
                                <a href="{{ route('erp.belts.promote', $row->student) }}"
                                    class="btn btn-sm btn-outline-primary rounded-pill">History / promote</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
