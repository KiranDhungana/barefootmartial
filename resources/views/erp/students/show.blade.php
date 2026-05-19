@extends('layouts.admin')

@section('title', $student->name)
@section('page_title', $student->name)
@section('page_subtitle', $student->student_code)

@section('content')
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
                        <div>
                            <p class="mb-1"><strong>Branch:</strong> {{ $student->branch->name ?? '—' }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $student->phone ?? '—' }}</p>
                            <p class="mb-1"><strong>Address:</strong> {{ $student->address ?? '—' }}</p>
                            <p class="mb-0"><strong>Join date:</strong>
                                {{ optional($student->join_date)->format('M j, Y') ?? '—' }}</p>
                        </div>
                    </div>
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
                    <a href="{{ route('erp.students.edit', $student) }}" class="btn btn-outline-primary rounded-pill">Edit</a>
                    <a href="{{ route('erp.students.id-card', $student) }}" class="btn btn-outline-secondary rounded-pill">Download ID
                        card (PDF)</a>
                    @if ($student->phone)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener"
                            class="btn btn-success rounded-pill">
                            <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp reminder
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary rounded-pill" disabled>Add phone for WhatsApp</button>
                    @endif
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

    @if ($canViewFinance)
        <div class="panel-card mt-3">
            <div class="panel-heading">Recent invoices</div>
            <div class="panel-body table-responsive">
                <table class="table admin-table mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($student->invoices as $inv)
                            <tr>
                                <td>{{ $inv->invoice_number }}</td>
                                <td>{{ number_format($inv->amount, 2) }}</td>
                                <td><span
                                        class="badge rounded-pill {{ $inv->status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($inv->status) }}</span>
                                </td>
                                <td class="text-end"><a href="{{ route('erp.invoices.show', $inv) }}"
                                        class="btn btn-sm btn-outline-primary rounded-pill">Open</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted text-center py-3">No invoices.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="panel-card mt-3">
        <div class="panel-heading">Attendance QR (staff)</div>
        <div class="panel-body p-4">
            <p class="small text-muted mb-2">Scanning this URL while logged in marks attendance for today.</p>
            <code class="d-block p-2 bg-light rounded mb-2 small break-all">{{ $student->qrScanUrl() }}</code>
            <p class="small mb-0"><strong>Message template (fees):</strong></p>
            <pre class="small bg-light p-2 rounded">{{ $feeReminderMessage }}</pre>
        </div>
    </div>
@endsection
