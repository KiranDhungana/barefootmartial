@extends('layouts.admin')

@section('title', 'Add student')
@section('page_title', 'Add student')
@section('page_subtitle', 'Enrollment')

@section('content')
    <div class="panel-card">
        <div class="panel-heading">Student details</div>
        <div class="panel-body p-4">
            <form method="post" action="{{ route('erp.students.store') }}" enctype="multipart/form-data"
                class="mx-auto" style="max-width:640px">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select rounded-3 @error('branch_id') is-invalid @enderror">
                        <option value="">—</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Full name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control rounded-3 @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" rows="2" class="form-control rounded-3 @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Join date</label>
                    <input type="date" name="join_date"
                        class="form-control rounded-3 @error('join_date') is-invalid @enderror"
                        value="{{ old('join_date') }}">
                    @error('join_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Photo</label>
                    <input type="file" name="photo" accept="image/*"
                        class="form-control rounded-3 @error('photo') is-invalid @enderror">
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control rounded-3">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-admin-primary text-white">Save student</button>
                <a href="{{ route('erp.students.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
