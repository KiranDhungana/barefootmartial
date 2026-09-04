@extends('layouts.admin')
@section('title', $event->title)
@section('page_title', $event->title)
@section('page_subtitle', 'Registrations & event certificates')

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('erp.events.edit', $event) }}" class="btn btn-outline-primary rounded-pill">Edit event</a>
        @if ($event->is_published)
            <a href="{{ route('public.events.show', $event) }}" target="_blank" class="btn btn-outline-secondary rounded-pill">
                Public page
            </a>
        @endif
        <form method="post" action="{{ route('erp.events.destroy', $event) }}" class="d-inline"
            onsubmit="return confirm('Delete this event and all its registrations?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger rounded-pill">Delete event</button>
        </form>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <div class="panel-card p-4">
                <h6 class="mb-3">Register student (admin)</h6>
                <form method="post" action="{{ route('erp.events.register', $event) }}">
                    @csrf
                    <select name="student_id" class="form-select rounded-3 mb-2" required>
                        @foreach ($students as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->student_code }})</option>
                        @endforeach
                    </select>
                    <input name="category" class="form-control rounded-3 mb-2" placeholder="Category (optional)">
                    <button class="btn btn-admin-primary text-white w-100">Register</button>
                </form>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="panel-card p-4 small">
                <p class="mb-1"><strong>Date:</strong> {{ optional($event->event_date)->format('M j, Y') ?? '—' }}</p>
                <p class="mb-1"><strong>Deadline:</strong> {{ optional($event->registration_deadline)->format('M j, Y') ?? '—' }}</p>
                <p class="mb-1"><strong>Branch:</strong> {{ $event->branch->name ?? '—' }}</p>
                <p class="mb-1"><strong>Fee:</strong> Rs. {{ number_format($event->fee_amount, 2) }}</p>
                <p class="mb-0"><strong>Published:</strong> {{ $event->is_published ? 'Yes' : 'No' }}
                    · <strong>Registrations:</strong> {{ $event->registrations->count() }}</p>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-heading">Registrations</div>
        <div class="panel-body p-3">
            @forelse ($event->registrations as $r)
                <div class="border rounded-4 p-3 mb-3">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <div class="fw-semibold">{{ $r->displayName() }}</div>
                            <div class="small text-muted">
                                @if ($r->student)
                                    {{ $r->student->student_code }}
                                    @if ($r->student->phone) · {{ $r->student->phone }} @endif
                                @else
                                    Guest
                                    @if ($r->phone) · {{ $r->phone }} @endif
                                    @if ($r->email) · {{ $r->email }} @endif
                                @endif
                            </div>
                            <div class="small mt-1">
                                Category: {{ $r->category ?? '—' }}
                                · Fee: {{ number_format($r->fee_amount, 2) }}
                            </div>
                            <form method="post"
                                action="{{ route('erp.events.registrations.status', [$event, $r]) }}"
                                class="mt-2 d-flex gap-2 align-items-center">
                                @csrf
                                <select name="status" class="form-select form-select-sm rounded-pill" style="max-width:160px"
                                    onchange="this.form.submit()">
                                    @foreach (['registered', 'confirmed', 'completed', 'cancelled'] as $st)
                                        <option value="{{ $st }}" @selected($r->status === $st)>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @if ($r->notes)
                                <p class="small text-muted mb-0 mt-2">{{ $r->notes }}</p>
                            @endif
                        </div>
                        <div class="col-lg-7">
                            @if ($r->hasCertificate() && $r->certificate_url)
                                <div class="d-flex flex-wrap gap-3 align-items-start mb-2">
                                    @if ($r->certificateIsImage())
                                        <a href="{{ $r->certificate_url }}" target="_blank" rel="noopener">
                                            <img src="{{ $r->certificate_url }}" alt="Certificate"
                                                class="rounded-3" style="max-height:80px;object-fit:cover">
                                        </a>
                                    @endif
                                    <div class="small">
                                        <div class="fw-semibold">{{ $r->certificate_title ?: 'Certificate' }}</div>
                                        @if ($r->certificate_number)
                                            <div>No: {{ $r->certificate_number }}</div>
                                        @endif
                                        @if ($r->certificate_issued_on)
                                            <div>Issued: {{ $r->certificate_issued_on->format('M j, Y') }}</div>
                                        @endif
                                        <a href="{{ $r->certificateDownloadUrl() }}" target="_blank" rel="noopener"
                                            class="btn btn-sm btn-outline-secondary rounded-pill mt-1">Download PDF</a>
                                        <form method="post"
                                            action="{{ route('erp.events.registrations.certificate.destroy', [$event, $r]) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Remove this certificate?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill mt-1">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            @elseif ($r->certificate_number)
                                <p class="small mb-2">Certificate no: {{ $r->certificate_number }} (no file yet)</p>
                            @endif

                            <form method="post"
                                action="{{ route('erp.events.registrations.certificate', [$event, $r]) }}"
                                enctype="multipart/form-data" class="row g-2 align-items-end">
                                @csrf
                                <div class="col-md-5">
                                    <label class="form-label small mb-0">{{ $r->certificate_url ? 'Replace certificate' : 'Attach certificate' }}</label>
                                    <input type="file" name="certificate" accept=".pdf,image/*"
                                        class="form-control form-control-sm rounded-3" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-0">Title</label>
                                    <input type="text" name="certificate_title"
                                        class="form-control form-control-sm rounded-3"
                                        value="{{ $r->certificate_title }}" placeholder="Optional">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-0">Issued</label>
                                    <input type="date" name="certificate_issued_on"
                                        class="form-control form-control-sm rounded-3"
                                        value="{{ optional($r->certificate_issued_on)->format('Y-m-d') ?? now()->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-admin-primary text-white w-100 rounded-pill">
                                        Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted text-center py-4 mb-0">No registrations yet. Share the public event page or register a student here.</p>
            @endforelse
        </div>
    </div>
@endsection
