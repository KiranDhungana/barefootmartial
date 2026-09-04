@extends('layouts.student-portal')

@section('title', 'Profile — Student portal')
@section('page_title', 'Profile')
@section('page_subtitle', 'Student details')

@section('content')
    @if ($students->isEmpty())
        <div class="alert alert-warning border-0 rounded-4">No student linked to your account.</div>
    @else
        @include('portal._student-switcher')

        @if ($student)
            <div class="panel-card">
                <div class="panel-heading">{{ $student->name }}</div>
                <div class="panel-body p-4">
                    <div class="d-flex flex-wrap gap-4 align-items-start mb-4">
                        @if ($student->photoUrl())
                            <img src="{{ $student->photoUrl() }}"
                                class="rounded-3 shadow-sm"
                                style="width:140px;height:140px;object-fit:cover"
                                alt="{{ $student->name }}">
                        @else
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted"
                                style="width:140px;height:140px;">
                                <i class="fa-solid fa-user fa-3x"></i>
                            </div>
                        @endif
                        <div class="pt-1">
                            <h2 class="h4 fw-bold mb-1">{{ $student->name }}</h2>
                            <p class="text-muted small mb-1">{{ $student->student_code }}</p>
                            <p class="mb-0">
                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">
                                    {{ $student->belt_rank ?? 'No belt set' }}
                                </span>
                                <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary ms-1">
                                    {{ $student->statusLabel() }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <dl class="row small mb-0">
                                <dt class="col-sm-4 text-muted">Student code</dt>
                                <dd class="col-sm-8">{{ $student->student_code }}</dd>
                                <dt class="col-sm-4 text-muted">Branch</dt>
                                <dd class="col-sm-8">{{ $student->branch->name ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted">Belt / rank</dt>
                                <dd class="col-sm-8">{{ $student->belt_rank ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted">Status</dt>
                                <dd class="col-sm-8">{{ $student->statusLabel() }}</dd>
                                <dt class="col-sm-4 text-muted">Coach</dt>
                                <dd class="col-sm-8">{{ $student->coach_name ?? '—' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row small mb-0">
                                <dt class="col-sm-4 text-muted">Phone</dt>
                                <dd class="col-sm-8">{{ $student->phone ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted">Parent</dt>
                                <dd class="col-sm-8">{{ $student->parent_name ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted">Parent contact</dt>
                                <dd class="col-sm-8">{{ $student->parent_contact ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted">Joined</dt>
                                <dd class="col-sm-8">{{ optional($student->join_date)->format('M j, Y') ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted">Address</dt>
                                <dd class="col-sm-8">{{ $student->address ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
