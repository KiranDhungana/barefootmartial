@php
    $isHome = request()->is('/');
    $isNotice = request()->routeIs('notice_home') || request()->routeIs('notice_main');
    $isContact = request()->routeIs('contact');
    $isAbout = request()->is('about-us');
    $isGallery = request()->is('gallary');
@endphp

<nav class="navbar navbar-expand-lg navbar-dark site-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand py-2" href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Barefoot Martial Arts Academy">
        </a>
        <button class="navbar-toggler rounded-pill px-3" type="button" data-bs-toggle="collapse"
            data-bs-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="siteNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 py-2 py-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ $isHome ? 'active' : '' }}" href="{{ url('/') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $isNotice ? 'active' : '' }}" href="{{ route('notice_home') }}">Notices</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $isAbout ? 'active' : '' }}" href="{{ url('about-us') }}">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $isGallery ? 'active' : '' }}" href="{{ url('gallary') }}">Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $isContact ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                </li>
                <li class="nav-item ms-lg-2">
                    @auth
                        <a class="btn btn-nav-cta" href="{{ url('home') }}">My dashboard</a>
                    @else
                        @if (Route::has('login'))
                            <a class="btn btn-nav-cta" href="{{ route('login') }}">Log in</a>
                        @endif
                    @endauth
                </li>
            </ul>
        </div>
    </div>
</nav>
