@extends('layouts.admin')

@section('title', 'Two-factor authentication')
@section('page_title', 'Two-factor authentication')
@section('page_subtitle', 'Secure your account')

@section('content')
    @if ($enabled)
        <div class="alert alert-success rounded-3">Two-factor authentication is enabled.</div>
        <form method="post" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('Disable 2FA?');">
            @csrf
            <button type="submit" class="btn btn-outline-danger rounded-pill">Disable</button>
        </form>
    @else
        <div class="panel-card mb-3">
            <div class="panel-heading">Setup</div>
            <div class="panel-body p-4">
                @if ($pendingSecret && $qrSvg)
                    <p class="small text-muted">Scan with Google Authenticator or similar, then enter the code.</p>
                    <div class="mb-3">{!! $qrSvg !!}</div>
                    <form method="post" action="{{ route('two-factor.confirm') }}" class="mx-auto"
                        style="max-width:320px">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">6-digit code</label>
                            <input type="text" name="code" class="form-control rounded-3 @error('code') is-invalid @enderror"
                                required inputmode="numeric">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-admin-primary text-white w-100 rounded-3">Confirm &
                            enable</button>
                    </form>
                @else
                    <form method="post" action="{{ route('two-factor.generate') }}">
                        @csrf
                        <button type="submit" class="btn btn-admin-primary text-white rounded-pill">Generate pairing
                            secret</button>
                    </form>
                @endif
            </div>
        </div>
    @endif
@endsection
