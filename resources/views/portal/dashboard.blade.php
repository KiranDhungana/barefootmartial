@extends('layouts.student-portal')

@section('title', 'Overview — Student portal')
@section('page_title', 'Overview')
@section('page_subtitle', 'Your academy summary')

@section('content')
    @if ($students->isEmpty())
        <div class="alert alert-warning border-0 rounded-4">
            No student record is linked to your account yet. Contact your branch office to connect your login.
        </div>
    @else
        @include('portal._student-switcher')

        @if ($student)
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="label">Attendance</div>
                                <div class="value">{{ $attendanceSummary ? $attendanceSummary->percent.'%' : '—' }}</div>
                                <p class="small text-muted mb-0 mt-1">This month</p>
                            </div>
                            <div class="icon-wrap bg-primary bg-opacity-10 text-primary">
                                <i class="fa-solid fa-clipboard-user"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="label">Still to pay</div>
                                <div class="value {{ $feeSummary['total_remaining'] > 0 ? 'text-danger' : '' }}">
                                    {{ number_format($feeSummary['total_remaining'], 2) }}
                                </div>
                                <p class="small text-muted mb-0 mt-1">{{ $openInvoices }} open bill(s)</p>
                            </div>
                            <div class="icon-wrap bg-warning bg-opacity-10 text-warning">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="label">Certificates</div>
                                <div class="value">{{ $certificateCount }}</div>
                                <p class="small text-muted mb-0 mt-1">On file</p>
                            </div>
                            <div class="icon-wrap bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-certificate"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="panel-card h-100">
                        <div class="panel-heading">Profile snapshot</div>
                        <div class="panel-body p-4 small">
                            <div class="d-flex gap-3 align-items-start mb-3">
                                @if ($student->photoUrl())
                                    <img src="{{ $student->photoUrl() }}"
                                        class="rounded-3"
                                        style="width:72px;height:72px;object-fit:cover"
                                        alt="{{ $student->name }}">
                                @else
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center text-muted"
                                        style="width:72px;height:72px;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="mb-1 fw-semibold">{{ $student->name }}</p>
                                    <p class="mb-0 text-muted">{{ $student->student_code }}</p>
                                </div>
                            </div>
                            <p class="mb-1"><strong>Branch:</strong> {{ $student->branch->name ?? '—' }}</p>
                            <p class="mb-1"><strong>Belt:</strong> {{ $student->belt_rank ?? '—' }}</p>
                            <p class="mb-3"><strong>Status:</strong> {{ $student->statusLabel() }}</p>
                            <a href="{{ route('portal.profile', request()->only('student_id')) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill">Full profile</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel-card h-100">
                        <div class="panel-heading">Recent invoices</div>
                        <div class="panel-body p-4">
                            <ul class="mb-3 list-unstyled">
                                @forelse ($recentInvoices as $inv)
                                    <li class="mb-2 d-flex justify-content-between gap-2">
                                        <span>
                                            <a href="{{ route('portal.invoices.show', [$inv, 'student_id' => $student->id]) }}">
                                                {{ $inv->invoice_number }}
                                            </a>
                                            <span class="text-muted small">· {{ number_format($inv->totalAmount(), 2) }}</span>
                                        </span>
                                        <span class="badge rounded-pill {{ $inv->status === 'paid' ? 'bg-success' : ($inv->status === 'overdue' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                            {{ $inv->statusLabel() }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="text-muted">No invoices yet.</li>
                                @endforelse
                            </ul>
                            <a href="{{ route('portal.fees', request()->only('student_id')) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill">All invoices</a>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="panel-card">
                        <div class="panel-heading">Latest notices</div>
                        <div class="panel-body p-4">
                            <ul class="mb-3">
                                @forelse ($recentNotices as $n)
                                    <li class="mb-2"><strong>{{ $n->title }}</strong> — {{ \Illuminate\Support\Str::limit($n->description, 80) }}</li>
                                @empty
                                    <li class="text-muted">No notices.</li>
                                @endforelse
                            </ul>
                            <a href="{{ route('portal.notices', request()->only('student_id')) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill">All notices</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
