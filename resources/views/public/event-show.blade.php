@extends('layouts.public')

@section('title', $event->title.' — Barefoot Martial Arts')

@section('content')
    <section class="py-5">
        <div class="container" style="max-width:720px">
            <p class="mb-3"><a href="{{ route('public.events') }}" class="text-decoration-none">&larr; All events</a></p>

            <h1 class="section-heading mb-2">{{ $event->title }}</h1>
            <div class="text-muted mb-4">
                @if ($event->event_date)
                    <div><strong>Date:</strong> {{ $event->event_date->format('F j, Y') }}</div>
                @endif
                @if ($event->registration_deadline)
                    <div><strong>Register by:</strong> {{ $event->registration_deadline->format('F j, Y') }}</div>
                @endif
                @if ($event->branch)
                    <div><strong>Branch:</strong> {{ $event->branch->name }}</div>
                @endif
                @if ($event->fee_amount > 0)
                    <div><strong>Fee:</strong> Rs. {{ number_format($event->fee_amount, 2) }}</div>
                @endif
            </div>

            @if ($event->description)
                <div class="card-soft p-4 mb-4">
                    <p class="mb-0" style="white-space:pre-line">{{ $event->description }}</p>
                </div>
            @endif

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

            @if ($event->isOpenForRegistration())
                <div class="card-soft p-4">
                    <h2 class="h5 mb-3">Register for this event</h2>
                    <form method="post" action="{{ route('public.events.register', $event) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Full name <span class="text-danger">*</span></label>
                            <input type="text" name="registrant_name" class="form-control rounded-3"
                                value="{{ old('registrant_name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control rounded-3"
                                value="{{ old('phone') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control rounded-3"
                                value="{{ old('email') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Membership ID (optional)</label>
                            <input type="text" name="student_code" class="form-control rounded-3"
                                value="{{ old('student_code') }}" placeholder="e.g. BFN-2026-0001">
                            <div class="form-text">If you are already a student, enter your ID to link your profile.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category / weight division</label>
                            <input type="text" name="category" class="form-control rounded-3"
                                value="{{ old('category') }}" placeholder="Optional">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-bf-primary w-100">Submit registration</button>
                    </form>
                </div>
            @else
                <div class="alert alert-secondary border-0 rounded-4">
                    Registration is closed for this event.
                </div>
            @endif
        </div>
    </section>
@endsection
