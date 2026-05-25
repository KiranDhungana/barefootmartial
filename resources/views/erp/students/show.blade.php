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
                        @if ($student->photo_path)
                            <img src="{{ asset('storage/'.$student->photo_path) }}" class="rounded-3"
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
                                        <a href="{{ route('erp.invoices.payment-slip', $inv) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Slip</a>
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
                                <td colspan="3" class="text-muted">No promotions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
