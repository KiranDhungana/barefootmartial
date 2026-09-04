@php
    $overview = request()->routeIs('portal.dashboard');
    $profile = request()->routeIs('portal.profile');
    $attendance = request()->routeIs('portal.attendance');
    $fees = request()->routeIs('portal.fees', 'portal.invoices.*');
    $certificates = request()->routeIs('portal.certificates', 'portal.belts.*');
    $notices = request()->routeIs('portal.notices');
    $qs = request()->only('student_id');
@endphp
<nav class="admin-sidebar-nav flex-column gap-1 flex-grow-1" aria-label="Student portal">
    <div class="text-uppercase small text-white-50 px-2 mb-1 mt-1" style="font-size:0.65rem;letter-spacing:0.08em;">My portal</div>

    <a class="admin-nav-link {{ $overview ? 'active' : '' }}" href="{{ route('portal.dashboard', $qs) }}">
        <i class="fa-solid fa-house"></i> Overview
    </a>
    <a class="admin-nav-link {{ $profile ? 'active' : '' }}" href="{{ route('portal.profile', $qs) }}">
        <i class="fa-solid fa-user"></i> Profile
    </a>
    <a class="admin-nav-link {{ $attendance ? 'active' : '' }}" href="{{ route('portal.attendance', $qs) }}">
        <i class="fa-solid fa-clipboard-user"></i> Attendance
    </a>
    <a class="admin-nav-link {{ $fees ? 'active' : '' }}" href="{{ route('portal.fees', $qs) }}">
        <i class="fa-solid fa-receipt"></i> Invoices & fees
    </a>
    <a class="admin-nav-link {{ $certificates ? 'active' : '' }}" href="{{ route('portal.certificates', $qs) }}">
        <i class="fa-solid fa-certificate"></i> Certificates
    </a>
    <a class="admin-nav-link {{ $notices ? 'active' : '' }}" href="{{ route('portal.notices', $qs) }}">
        <i class="fa-solid fa-bullhorn"></i> Notices
    </a>

    <div class="mt-auto pt-3">
        <a class="admin-nav-link" href="{{ route('public.home') }}">
            <i class="fa-solid fa-globe"></i> Public website
        </a>
    </div>
</nav>
