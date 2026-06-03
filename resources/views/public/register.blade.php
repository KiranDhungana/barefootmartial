@extends('layouts.public')

@section('title', 'Student registration — Barefoot Martial Arts')

@section('content')
    <section class="py-5">
        <div class="container" style="max-width: 640px;">
            <h1 class="section-heading mb-2">Student registration</h1>
            <p class="text-muted mb-4">
                Submit your details below. Your request stays <strong>pending</strong> until a super admin reviews and
                approves it. We will contact you after approval.
            </p>

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

            <form method="post" action="{{ route('public.register.store') }}" class="card-soft p-4">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Preferred branch</label>
                    <select name="branch_id" class="form-select rounded-3">
                        <option value="">Any / not sure</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Student name <span class="text-danger">*</span></label>
                    <input type="text" name="student_name" class="form-control rounded-3 @error('student_name') is-invalid @enderror"
                        value="{{ old('student_name') }}" required>
                    @error('student_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Parent / guardian name</label>
                    <input type="text" name="parent_name" class="form-control rounded-3"
                        value="{{ old('parent_name') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control rounded-3 @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                        value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control rounded-3" rows="3"
                        placeholder="Age, programme interest, preferred schedule, etc.">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="btn btn-bf-primary w-100">Submit registration request</button>
            </form>
        </div>
    </section>
@endsection
