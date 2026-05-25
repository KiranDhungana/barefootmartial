@extends('layouts.admin')

@section('title', 'Belt — '.$student->name)
@section('page_title', 'Belt promotion')
@section('page_subtitle', $student->student_code)

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="panel-card">
                <div class="panel-heading">Record promotion</div>
                <div class="panel-body p-4">
                    <p class="mb-3">Current belt: <strong>{{ $student->belt_rank ?? 'None' }}</strong></p>
                    <form method="post" action="{{ route('erp.belts.promote.store', $student) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">New belt</label>
                            <select name="to_belt" class="form-select rounded-3" required>
                                @foreach ($beltRanks as $rank)
                                    <option value="{{ $rank }}" @selected($rank === $nextBelt)>{{ $rank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Promotion date</label>
                            <input type="date" name="promoted_at" class="form-control rounded-3"
                                value="{{ old('promoted_at', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="exam_passed" value="1" class="form-check-input" checked id="exam">
                            <label class="form-check-label" for="exam">Exam passed</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-admin-primary text-white">Promote & generate certificate</button>
                        <a href="{{ route('erp.belts.index') }}" class="btn btn-link">Back</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="panel-card">
                <div class="panel-heading">Belt history</div>
                <div class="panel-body table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Certificate</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($history as $h)
                                <tr>
                                    <td>{{ $h->promoted_at->format('M j, Y') }}</td>
                                    <td>{{ $h->from_belt ?? '—' }}</td>
                                    <td>{{ $h->to_belt }}</td>
                                    <td>{{ $h->certificate_number }}</td>
                                    <td class="text-end">
                                        @if ($h->certificate_number)
                                            <a href="{{ route('erp.belts.certificate', [$student, $h->id]) }}"
                                                class="btn btn-sm btn-outline-secondary rounded-pill">PDF</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted text-center py-3">No promotions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
