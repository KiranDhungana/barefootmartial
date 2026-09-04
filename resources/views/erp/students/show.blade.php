@extends('layouts.admin')

@section('title', $student->name)
@section('page_title', $student->name)
@section('page_subtitle', $student->student_code)

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 rounded-4">{{ session('error') }}</div>
    @endif

    @if ($student->isPendingRegistration())
        <div class="alert alert-warning border-0 rounded-4">
            <strong>Pending official registration.</strong> This student is not yet official in the central system.
            @if (count($missingOfficial) > 0)
                <ul class="mb-0 mt-2 small">
                    @foreach ($missingOfficial as $field)
                        <li>Missing: {{ str_replace('_', ' ', $field) }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @else
        <div class="alert alert-success border-0 rounded-4 py-2">
            <i class="fa-solid fa-circle-check me-1"></i>
            Official member since {{ optional($student->registered_at)->format('M j, Y') ?? '—' }}
            @if ($student->registeredByUser)
                (by {{ $student->registeredByUser->name }})
            @endif
        </div>
    @endif

    <ul class="nav nav-tabs mb-3 flex-nowrap overflow-auto">
        @foreach (['profile' => 'Profile', 'attendance' => 'Attendance', 'fees' => 'Fees', 'uniforms' => 'Uniforms', 'certificates' => 'Certificates'] as $key => $label)
            @if (in_array($key, ['fees', 'uniforms']) && ! $canViewFinance)
                @continue
            @endif
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === $key ? 'active' : '' }}"
                    href="{{ route('erp.students.show', [$student, 'tab' => $key]) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    @if ($activeTab === 'profile')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-heading">Profile</div>
                <div class="panel-body p-4">
                    <div class="d-flex flex-wrap gap-3 align-items-start">
                        @if ($student->photoUrl())
                            <img src="{{ $student->photoUrl() }}" class="rounded-3"
                                style="width:120px;height:120px;object-fit:cover" alt="">
                        @endif
                        <div class="small">
                            <p class="mb-1"><strong>Branch:</strong> {{ $student->branch->name ?? '—' }}</p>
                            <p class="mb-1"><strong>Status:</strong> {{ $student->statusLabel() }}</p>
                            <p class="mb-1"><strong>Belt:</strong> {{ $student->belt_rank ?? '—' }}</p>
                            <p class="mb-1"><strong>Coach:</strong> {{ $student->coach_name ?? '—' }}</p>
                            <p class="mb-1"><strong>Batch:</strong> {{ $student->batch_timing ?? '—' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $student->phone ?? '—' }}</p>
                            <p class="mb-1"><strong>DOB:</strong> {{ optional($student->dob)->format('M j, Y') ?? '—' }}</p>
                            <p class="mb-1"><strong>Gender:</strong> {{ $student->gender ? ucfirst($student->gender) : '—' }}</p>
                            <p class="mb-1"><strong>Blood:</strong> {{ $student->blood_group ?? '—' }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $student->address ?? '—' }}</p>
                            <p class="mb-0"><strong>Join date:</strong>
                                {{ optional($student->join_date)->format('M j, Y') ?? '—' }}</p>
                            <p class="mb-0 mt-1"><strong>Monthly fee:</strong>
                                Rs. {{ number_format((float) ($student->monthly_fee > 0 ? $student->monthly_fee : config('academy.default_monthly_fee', 0)), 2) }}
                                <span class="text-muted">(auto from join date)</span>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <p class="small mb-1"><strong>Parent:</strong> {{ $student->parent_name ?? '—' }}
                        @if ($student->parent_contact)
                            ({{ $student->parent_contact }})
                        @endif
                    </p>
                    <p class="small mb-0"><strong>Emergency:</strong> {{ $student->emergency_contact ?? '—' }}</p>
                    @if ($student->notes)
                        <hr>
                        <p class="text-muted small mb-0">{{ $student->notes }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card">
                <div class="panel-heading">Actions</div>
                <div class="panel-body p-4 d-grid gap-2">
                    @if ($canMarkOfficial)
                        <form method="post" action="{{ route('erp.students.mark-official', $student) }}">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 rounded-pill"
                                onclick="return confirm('Mark this student as officially registered?');">
                                <i class="fa-solid fa-stamp me-1"></i> Mark official registration
                            </button>
                        </form>
                    @endif
                    @if ($canViewFinance && $student->isOfficial())
                        <a href="{{ route('erp.invoices.create', ['student_id' => $student->id]) }}"
                            class="btn btn-admin-primary text-white rounded-pill">Bill fees / admission</a>
                    @endif
                    @if ($student->isOfficial())
                        <a href="{{ route('erp.belts.promote', $student) }}" class="btn btn-outline-primary rounded-pill">
                            Belt @if ($beltEligible && $nextBelt)
                                (eligible → {{ $nextBelt }})
                            @else
                                history
                            @endif
                        </a>
                    @endif
                    <a href="{{ route('erp.students.edit', $student) }}" class="btn btn-outline-primary rounded-pill">Edit</a>
                    @if ($student->isOfficial())
                        <a href="{{ route('erp.students.id-card', $student) }}" class="btn btn-outline-secondary rounded-pill">Download ID
                            card (PDF)</a>
                    @else
                        <button type="button" class="btn btn-outline-secondary rounded-pill" disabled>ID card (official only)</button>
                    @endif
                    @if ($student->phone || $student->parent_contact)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                            class="btn btn-success rounded-pill">
                            <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp reminder
                        </a>
                    @endif
                    <a href="{{ route('erp.parents.create', $student) }}" class="btn btn-outline-secondary rounded-pill">Create parent login</a>
                    <form action="{{ route('erp.students.destroy', $student) }}" method="post"
                        onsubmit="return confirm('Delete this student and related records?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">Delete student</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card mt-3">
    @endif

    @if ($activeTab === 'attendance')
        <div class="panel-card">
            <div class="panel-heading">Attendance</div>
            <div class="panel-body p-4">
                @if ($monthPct)
                    <p class="mb-3">This month: <strong>{{ $monthPct->percent }}%</strong>
                        ({{ $monthPct->present_days }} present, {{ $monthPct->late_days }} late)</p>
                @endif
                <div class="table-responsive">
                    <table class="table admin-table mb-0 small">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendanceRecords as $a)
                                <tr>
                                    <td>{{ $a->attendance_date->format('M j, Y') }}</td>
                                    <td>{{ ucfirst($a->status) }}</td>
                                    <td>{{ $a->source ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if ($activeTab === 'fees' && $canViewFinance)
        <div class="panel-card">
            <div class="panel-heading">Invoices & fees</div>
            <div class="panel-body table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Due</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            <tr>
                                <td>{{ $inv->invoice_number }}</td>
                                <td>{{ optional($inv->due_date)->format('M j, Y') ?? '—' }}</td>
                                <td>{{ number_format($inv->amount, 2) }}</td>
                                <td>{{ number_format($inv->balanceDue(), 2) }}</td>
                                <td>{{ ucfirst($inv->status) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('erp.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary rounded-pill">Open</a>
                                    @if ($inv->balanceDue() > 0)
                                        <a href="{{ route('erp.invoices.payment-slip', $inv) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Receipt</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted text-center py-3">No invoices.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($activeTab === 'uniforms' && $canViewFinance)
        <div class="panel-card">
            <div class="panel-heading">Uniform & equipment purchases</div>
            <div class="panel-body table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($uniformLines as $line)
                            <tr>
                                <td>{{ $line->invoice->invoice_number ?? '—' }}</td>
                                <td>{{ $line->description }}</td>
                                <td>{{ $line->quantity }}</td>
                                <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted text-center py-3">No uniform or inventory lines yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <p class="small text-muted">Uniform status on profile: {{ $student->uniform_status ?? '—' }}</p>
    @endif

    @if ($activeTab === 'certificates')
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="panel-card">
                    <div class="panel-heading">Attach certificate</div>
                    <div class="panel-body p-4">
                        <form method="post" action="{{ route('erp.students.certificates.store', $student) }}"
                            enctype="multipart/form-data" id="attach-certificate-form">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3" required
                                    value="{{ old('title') }}"
                                    placeholder="e.g. School leaving certificate">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="certificate_type" id="certificate_type" class="form-select rounded-3" required>
                                    @foreach (\App\Models\StudentCertificate::attachTypeOptions() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('certificate_type', 'general') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3 d-none" id="event-select-wrap">
                                <label class="form-label">Event <span class="text-danger">*</span></label>
                                <select name="event_id" id="event_id" class="form-select rounded-3">
                                    <option value="">Select event…</option>
                                    @foreach ($eventsForCertificates as $event)
                                        <option value="{{ $event->id }}" @selected((string) old('event_id') === (string) $event->id)>
                                            {{ $event->title }}
                                            @if ($event->event_date)
                                                ({{ $event->event_date->format('M j, Y') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @if ($eventsForCertificates->isEmpty())
                                    <div class="form-text text-warning">No events found. Create an event first under ERP → Events.</div>
                                @else
                                    <div class="form-text">If the student is not registered yet, they will be registered when you attach the certificate.</div>
                                @endif
                            </div>
                            <div class="mb-3 d-none" id="cert-number-wrap">
                                <label class="form-label">Certificate number</label>
                                <input type="text" name="certificate_number" class="form-control rounded-3"
                                    value="{{ old('certificate_number') }}" placeholder="Auto-generated if empty">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File <span class="text-danger">*</span></label>
                                <input type="file" name="file" accept=".pdf,image/*" class="form-control rounded-3" required>
                                <div class="form-text">PDF or image — max 10 MB (Cloudinary)</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Issued on</label>
                                <input type="date" name="issued_on" class="form-control rounded-3"
                                    value="{{ old('issued_on') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" rows="2" class="form-control rounded-3">{{ old('notes') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-admin-primary text-white w-100">
                                <i class="fa-solid fa-paperclip me-1"></i> Attach certificate
                            </button>
                        </form>
                        @push('scripts')
                            <script>
                                (function () {
                                    const typeSelect = document.getElementById('certificate_type');
                                    const eventWrap = document.getElementById('event-select-wrap');
                                    const eventSelect = document.getElementById('event_id');
                                    const certNumberWrap = document.getElementById('cert-number-wrap');
                                    if (!typeSelect || !eventWrap || !eventSelect) return;

                                    function sync() {
                                        const isEvent = typeSelect.value === 'event';
                                        eventWrap.classList.toggle('d-none', !isEvent);
                                        certNumberWrap?.classList.toggle('d-none', !isEvent);
                                        eventSelect.required = isEvent;
                                        if (!isEvent) eventSelect.value = '';
                                    }

                                    typeSelect.addEventListener('change', sync);
                                    sync();
                                })();
                            </script>
                        @endpush
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="panel-card mb-3">
                    <div class="panel-heading">Event certificates</div>
                    <div class="panel-body p-3">
                        @forelse ($student->eventCertificates as $reg)
                            <div class="border rounded-4 p-3 mb-3">
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-3 text-center">
                                        @if ($reg->certificateIsImage())
                                            <a href="{{ $reg->certificate_url }}" target="_blank" rel="noopener">
                                                <img src="{{ $reg->certificate_url }}" alt="Certificate"
                                                    class="img-fluid rounded-3" style="max-height:100px;object-fit:cover">
                                            </a>
                                        @else
                                            <a href="{{ $reg->certificate_url }}" target="_blank" rel="noopener"
                                                class="btn btn-outline-secondary rounded-pill">
                                                <i class="fa-solid fa-file-pdf me-1"></i> Open PDF
                                            </a>
                                        @endif
                                    </div>
                                    <div class="col-md-9">
                                        <div class="fw-semibold">{{ $reg->certificate_title ?: $reg->event?->title }}</div>
                                        <div class="small text-muted mb-1">
                                            Event: {{ $reg->event?->title ?? '—' }}
                                            @if ($reg->event?->event_date)
                                                · {{ $reg->event->event_date->format('M j, Y') }}
                                            @endif
                                        </div>
                                        @if ($reg->certificate_number)
                                            <div class="small">Certificate no: {{ $reg->certificate_number }}</div>
                                        @endif
                                        @if ($reg->certificate_issued_on)
                                            <div class="small text-muted">Issued {{ $reg->certificate_issued_on->format('M j, Y') }}</div>
                                        @endif
                                        <a href="{{ route('erp.events.show', $reg->event_id) }}"
                                            class="btn btn-sm btn-outline-primary rounded-pill mt-2">Manage on event</a>
                                        <a href="{{ $reg->certificate_url }}" target="_blank" rel="noopener"
                                            class="btn btn-sm btn-outline-secondary rounded-pill mt-2">View</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0">
                                No event certificates yet. Attach one here by choosing type <strong>Event certificate</strong>,
                                or from <strong>ERP → Events → Registrations</strong>.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div class="panel-card mb-3">
                    <div class="panel-heading">Attached certificates</div>
                    <div class="panel-body p-3">
                        @forelse ($student->certificates as $cert)
                            <div class="border rounded-4 p-3 mb-3">
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-3 text-center">
                                        @if ($cert->isImage())
                                            <a href="{{ $cert->file_url }}" target="_blank" rel="noopener">
                                                <img src="{{ $cert->file_url }}" alt="{{ $cert->title }}"
                                                    class="img-fluid rounded-3" style="max-height:100px;object-fit:cover">
                                            </a>
                                        @else
                                            <a href="{{ $cert->file_url }}" target="_blank" rel="noopener"
                                                class="btn btn-outline-secondary rounded-pill">
                                                <i class="fa-solid fa-file-pdf me-1"></i> Open PDF
                                            </a>
                                        @endif
                                    </div>
                                    <div class="col-md-9">
                                        <form method="post"
                                            action="{{ route('erp.students.certificates.update', [$student, $cert]) }}"
                                            enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small">Title</label>
                                                    <input type="text" name="title" class="form-control form-control-sm rounded-3"
                                                        value="{{ $cert->title }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Type</label>
                                                    <select name="certificate_type" class="form-select form-select-sm rounded-3" required>
                                                        @foreach (\App\Models\StudentCertificate::typeOptions() as $value => $label)
                                                            <option value="{{ $value }}" @selected(($cert->certificate_type ?: 'general') === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Issued on</label>
                                                    <input type="date" name="issued_on"
                                                        class="form-control form-control-sm rounded-3"
                                                        value="{{ optional($cert->issued_on)->format('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Notes</label>
                                                    <input type="text" name="notes" class="form-control form-control-sm rounded-3"
                                                        value="{{ $cert->notes }}">
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label small">Replace file (optional)</label>
                                                    <input type="file" name="file" accept=".pdf,image/*"
                                                        class="form-control form-control-sm rounded-3">
                                                </div>
                                                <div class="col-md-4 d-flex align-items-end gap-2">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-primary rounded-pill">Save</button>
                                                    <a href="{{ $cert->file_url }}" target="_blank" rel="noopener"
                                                        class="btn btn-sm btn-outline-secondary rounded-pill">View</a>
                                                </div>
                                            </div>
                                            <p class="small text-muted mb-0 mt-2">
                                                {{ $cert->original_filename ?: 'File' }}
                                                @if ($cert->uploader)
                                                    · uploaded by {{ $cert->uploader->name }}
                                                @endif
                                                · {{ $cert->created_at->format('M j, Y') }}
                                            </p>
                                        </form>
                                        <form method="post"
                                            action="{{ route('erp.students.certificates.destroy', [$student, $cert]) }}"
                                            class="mt-2"
                                            onsubmit="return confirm('Remove this certificate?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 mb-0">No certificates attached yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="panel-card">
                    <div class="panel-heading">Belt certificates</div>
                    <div class="panel-body table-responsive">
                        <table class="table admin-table mb-0 small">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Belt</th>
                                    <th>Certificate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($student->beltPromotions as $h)
                                    <tr>
                                        <td>{{ $h->promoted_at->format('M j, Y') }}</td>
                                        <td>{{ $h->from_belt ? $h->from_belt.' → ' : '' }}{{ $h->to_belt }}</td>
                                        <td>
                                            @if ($h->certificate_number)
                                                <a href="{{ route('erp.belts.certificate', [$student, $h->id]) }}">{{ $h->certificate_number }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted">No belt promotions yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($activeTab === 'profile')
    <div class="panel-card mt-3">
        <div class="panel-heading">QR verification</div>
        <div class="panel-body p-4">
            <p class="small text-muted mb-2">Public verify link (belt & status). Staff scan URL marks attendance when logged in.</p>
            <p class="small mb-1"><strong>Verify:</strong></p>
            <code class="d-block p-2 bg-light rounded mb-2 small break-all">{{ $student->verifyUrl() }}</code>
            <p class="small mb-1"><strong>Staff check-in:</strong></p>
            <code class="d-block p-2 bg-light rounded mb-0 small break-all">{{ $student->qrScanUrl() }}</code>
        </div>
    </div>
    @endif
@endsection
