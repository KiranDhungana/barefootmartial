@extends('layouts.admin')

@section('title', 'Edit trainer')
@section('page_title', 'Edit trainer')

@section('content')
    <div class="panel-card">
        <div class="panel-heading">{{ $trainer->name }}</div>
        <div class="panel-body p-4">
            <form method="post" action="{{ route('erp.trainers.update', $trainer) }}" class="mx-auto"
                style="max-width:560px">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control rounded-3" required
                        value="{{ old('name', $trainer->name) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control rounded-3"
                        value="{{ old('phone', $trainer->phone) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control rounded-3"
                        value="{{ old('email', $trainer->email) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role title</label>
                    <input type="text" name="role_title" class="form-control rounded-3"
                        value="{{ old('role_title', $trainer->role_title) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Salary mode <span class="text-danger">*</span></label>
                    <select name="salary_mode" class="form-select rounded-3">
                        <option value="fixed" @selected(old('salary_mode', $trainer->salary_mode) === 'fixed')>Fixed monthly
                        </option>
                        <option value="attendance" @selected(old('salary_mode', $trainer->salary_mode) === 'attendance')>Per
                            weekday × rate</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monthly amount (fixed)</label>
                    <input type="number" step="0.01" min="0" name="monthly_amount" class="form-control rounded-3"
                        value="{{ old('monthly_amount', $trainer->monthly_amount) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Per day amount (attendance mode)</label>
                    <input type="number" step="0.01" min="0" name="per_day_amount" class="form-control rounded-3"
                        value="{{ old('per_day_amount', $trainer->per_day_amount) }}">
                </div>
                <div class="mb-4">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control rounded-3">{{ old('notes', $trainer->notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-admin-primary text-white">Update</button>
                <a href="{{ route('erp.trainers.index') }}" class="btn btn-link">Back</a>
            </form>
            <form method="post" action="{{ route('erp.trainers.destroy', $trainer) }}" class="mt-4"
                onsubmit="return confirm('Delete this trainer?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Delete trainer</button>
            </form>
        </div>
    </div>
@endsection
