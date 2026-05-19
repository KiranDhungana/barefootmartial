@extends('layouts.app')

@section('title', 'My dashboard — Barefoot Martial Arts')

@section('content')
    @php
        $certs = [];
        if (!empty(Auth::user()->path)) {
            $decoded = json_decode(Auth::user()->path, true);
            $certs = is_array($decoded) ? $decoded : [];
        }
    @endphp

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1" style="letter-spacing:-0.02em;">Your profile</h1>
                    <p class="text-muted small mb-0">Member dashboard</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary rounded-pill dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li>
                            <a class="dropdown-item rounded-2" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Log out') }}
                            </a>
                        </li>
                    </ul>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>

            <div class="card-soft overflow-hidden">
                <div class="row g-0">
                    <div class="col-md-4 bg-light d-flex align-items-center justify-content-center p-4 p-lg-5">
                        @if (Auth::user()->image)
                            <img src="{{ asset('storage/images/' . Auth::user()->image) }}"
                                class="rounded-circle shadow"
                                style="width: 180px; height: 180px; object-fit: cover;" alt="Profile photo">
                        @else
                            <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-muted"
                                style="width: 180px; height: 180px;">
                                <i class="fa-solid fa-user fa-3x"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <div class="p-4 p-lg-5">
                            <dl class="row mb-0 small">
                                <dt class="col-sm-4 text-muted py-2">Name</dt>
                                <dd class="col-sm-8 py-2 mb-0 fw-semibold">{{ Auth::user()->name }}</dd>
                                <dt class="col-sm-4 text-muted py-2">Description</dt>
                                <dd class="col-sm-8 py-2 mb-0">{{ Auth::user()->description ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted py-2">Age</dt>
                                <dd class="col-sm-8 py-2 mb-0">{{ Auth::user()->age ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted py-2">Address</dt>
                                <dd class="col-sm-8 py-2 mb-0">{{ Auth::user()->address ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted py-2">Phone</dt>
                                <dd class="col-sm-8 py-2 mb-0">{{ Auth::user()->phone ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted py-2">Rank</dt>
                                <dd class="col-sm-8 py-2 mb-0">{{ Auth::user()->rank ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($certs))
                <div class="card-soft p-4 p-lg-5 mt-4">
                    <h2 class="h6 fw-bold text-uppercase text-muted letter-spacing mb-3">Certificates</h2>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($certs as $i => $item)
                            <a href="{{ asset('storage/images/' . $item) }}" target="_blank" rel="noopener"
                                class="btn btn-outline-primary rounded-pill btn-sm">
                                <i class="fa-solid fa-file-arrow-down me-1"></i> Certificate {{ $i + 1 }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
