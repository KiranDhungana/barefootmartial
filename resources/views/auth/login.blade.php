@extends('layouts.public')

@section('title', __('Log in').' — Barefoot Martial Arts Academy')

@section('content')
    <section class="py-5 py-lg-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-7 col-xl-5">
                    @if (Session::has('errormsg'))
                        <div class="alert alert-danger border-0 rounded-4 shadow-sm alert-dismissible fade show mb-4"
                            role="alert">
                            {{ Session::get('errormsg') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="{{ __('Close') }}"></button>
                        </div>
                    @endif

                    <div class="card-soft p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset('images/logo.png') }}" alt=""
                                style="height:52px;width:auto;object-fit:contain;" class="mb-3">
                            <h1 class="h4 fw-bold mb-1" style="letter-spacing:-0.02em;">{{ __('Welcome back') }}</h1>
                            <p class="text-muted small mb-0">{{ __('Sign in to your member account') }}</p>
                        </div>

                        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">{{ __('Email Address') }}</label>
                                <input id="email" type="email"
                                    class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                    placeholder="you@example.com">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">{{ __('Password') }}</label>
                                <input id="password" type="password"
                                    class="form-control form-control-lg rounded-3 @error('password') is-invalid @enderror"
                                    name="password" required autocomplete="current-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-4 form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                    {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="remember">{{ __('Remember Me') }}</label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-bf-primary btn-lg rounded-pill py-3">
                                    {{ __('Login') }}
                                </button>
                                @if (Route::has('password.request'))
                                    <a class="btn btn-link btn-sm text-muted" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <p class="text-center small text-muted mt-4 mb-0">
                        <a href="{{ url('/') }}" class="text-decoration-none">{{ __('← Back to website') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
