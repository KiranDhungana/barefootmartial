@extends('layouts.admin')

@section('title', 'Edit student')
@section('page_title', 'Edit student')
@section('page_subtitle', $student->student_code)

@section('content')
    <div class="panel-card">
        <div class="panel-heading">Student details</div>
        <div class="panel-body p-4">
            <form method="post" action="{{ route('erp.students.update', $student) }}" enctype="multipart/form-data"
                class="mx-auto" style="max-width:640px">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select rounded-3">
                        <option value="">—</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id', $student->branch_id) == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Full name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $student->name) }}"
                        required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control rounded-3"
                        value="{{ old('phone', $student->phone) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-control rounded-3">{{ old('address', $student->address) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Join date</label>
                    <input type="date" name="join_date" class="form-control rounded-3"
                        value="{{ old('join_date', optional($student->join_date)->format('Y-m-d')) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Photo</label>
                    @if ($student->photo_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$student->photo_path) }}" alt="" class="rounded-3"
                                style="max-height:120px">
                        </div>
                    @endif
                    <input type="file" name="photo" accept="image/*" class="form-control rounded-3">
                </div>
                <div class="mb-4">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control rounded-3">{{ old('notes', $student->notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-admin-primary text-white">Update</button>
                <a href="{{ route('erp.students.show', $student) }}" class="btn btn-link">Back</a>
            </form>
        </div>
    </div>
@endsection
