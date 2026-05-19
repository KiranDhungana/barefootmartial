@extends('layouts.admin')

@section('title', 'Add player')
@section('page_title', 'Add player')
@section('page_subtitle', 'Register a new player account')

@section('content')
    <div class="panel-card">
        <div class="panel-heading">Player details</div>
        <div class="panel-body p-4">
            <div class="mx-auto" style="max-width: 640px;">
                <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input name="name" type="text" class="form-control rounded-3 @error('name') is-invalid @enderror"
                            value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input name="email" type="email" class="form-control rounded-3 @error('email') is-invalid @enderror"
                            value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input name="password" type="password"
                            class="form-control rounded-3 @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <input name="address" type="text" class="form-control rounded-3 @error('address') is-invalid @enderror"
                            value="{{ old('address') }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Age <span class="text-danger">*</span></label>
                        <input name="age" type="number" class="form-control rounded-3 @error('age') is-invalid @enderror"
                            value="{{ old('age') }}">
                        @error('age')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input name="phone" type="tel" class="form-control rounded-3 @error('phone') is-invalid @enderror"
                            value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rank <span class="text-danger">*</span></label>
                        <input name="rank" type="number" min="0" step="1"
                            class="form-control rounded-3 @error('rank') is-invalid @enderror"
                            value="{{ old('rank') }}">
                        @error('rank')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea rows="5" name="description"
                            class="form-control rounded-3 @error('description') is-invalid @enderror"
                            placeholder="Notes about the player">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Profile image <span class="text-danger">*</span></label>
                        <input class="form-control rounded-3 @error('image') is-invalid @enderror" name="image" type="file">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Documents</label>
                        <input multiple class="form-control rounded-3" name="file[]" type="file">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Account type <span class="text-danger">*</span></label>
                        <select name="role" class="form-select rounded-3 @error('role') is-invalid @enderror" required>
                            <option value="player" @selected(old('role', 'player') === 'player')>Player (member)</option>
                            <option value="staff" @selected(old('role') === 'staff')>Staff (academy console)</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Staff accounts can use Students &amp; Attendance; administrators keep full access.</div>
                    </div>
                    <button type="submit" class="btn btn-admin-primary text-white px-4 rounded-pill">Submit</button>
                </form>
            </div>
        </div>
    </div>
@endsection
