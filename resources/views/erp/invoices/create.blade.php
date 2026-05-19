@extends('layouts.admin')

@section('title', 'New invoice')
@section('page_title', 'New invoice')
@section('page_subtitle', 'Fee billing')

@section('content')
    <div class="panel-card">
        <div class="panel-heading">Invoice</div>
        <div class="panel-body p-4">
            @if ($students->isEmpty())
                <p class="text-muted mb-0">Add at least one student before creating invoices.</p>
                <a href="{{ route('erp.students.create') }}" class="btn btn-outline-primary rounded-pill mt-3">Add student</a>
            @else
                <form method="post" action="{{ route('erp.invoices.store') }}" class="mx-auto" style="max-width:520px">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Student <span class="text-danger">*</span></label>
                        <select name="student_id" required class="form-select rounded-3">
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id', $studentId) == $s->id)>{{ $s->student_code }}
                                    — {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control rounded-3"
                            value="{{ old('amount') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due date</label>
                        <input type="date" name="due_date" class="form-control rounded-3" value="{{ old('due_date') }}">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control rounded-3">{{ old('notes') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-admin-primary text-white">Create invoice</button>
                    <a href="{{ route('erp.invoices.index') }}" class="btn btn-link">Cancel</a>
                </form>
            @endif
        </div>
    </div>
@endsection
