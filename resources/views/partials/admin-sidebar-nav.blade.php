@php
    $dashboardActive = request()->routeIs('admin-home', 'user.update');
    $erpDash = request()->routeIs('erp.dashboard');
    $erpStudents = request()->routeIs('erp.students.*');
    $erpAttendance = request()->routeIs('erp.attendance.*');
    $erpInvoices = request()->routeIs('erp.invoices.*');
    $erpTrainers = request()->routeIs('erp.trainers.*');
    $erpSalary = request()->routeIs('erp.salary.*');
    $erpReports = request()->routeIs('erp.reports.*');
    $addPlayerActive = request()->routeIs('register_user');
    $addNoticeActive = request()->routeIs('add_notice');
    $delNoticeActive = request()->routeIs('del_notice');
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp
<nav class="admin-sidebar-nav flex-column gap-1" aria-label="Admin navigation">
    @if (auth()->check() && auth()->user()->canAccessErp())
        <div class="text-uppercase small text-white-50 px-2 mb-1 mt-1" style="font-size:0.65rem;letter-spacing:0.08em;">Academy ERP</div>
        <a class="admin-nav-link {{ $erpDash ? 'active' : '' }}" href="{{ route('erp.dashboard') }}">
            <i class="fa-solid fa-chart-line"></i>
            <span>Console</span>
        </a>
        <a class="admin-nav-link {{ $erpStudents ? 'active' : '' }}" href="{{ route('erp.students.index') }}">
            <i class="fa-solid fa-user-graduate"></i>
            <span>Students</span>
        </a>
        <a class="admin-nav-link {{ $erpAttendance ? 'active' : '' }}" href="{{ route('erp.attendance.index') }}">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Attendance</span>
        </a>
        @if ($isAdmin)
            <a class="admin-nav-link {{ $erpInvoices ? 'active' : '' }}" href="{{ route('erp.invoices.index') }}">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Invoices</span>
            </a>
            <a class="admin-nav-link {{ $erpTrainers ? 'active' : '' }}" href="{{ route('erp.trainers.index') }}">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Trainers</span>
            </a>
            <a class="admin-nav-link {{ $erpSalary ? 'active' : '' }}" href="{{ route('erp.salary.index') }}">
                <i class="fa-solid fa-money-check-dollar"></i>
                <span>Salary</span>
            </a>
            <a class="admin-nav-link {{ $erpReports ? 'active' : '' }}" href="{{ route('erp.reports.index') }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Reports</span>
            </a>
        @endif
        <div class="border-bottom border-secondary border-opacity-25 my-2"></div>
    @endif

    @if ($isAdmin)
        <div class="text-uppercase small text-white-50 px-2 mb-1" style="font-size:0.65rem;letter-spacing:0.08em;">Legacy admin</div>
        <a class="admin-nav-link {{ $dashboardActive ? 'active' : '' }}" href="{{ route('admin-home') }}">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>
        <a class="admin-nav-link {{ $addPlayerActive ? 'active' : '' }}" href="{{ route('register_user') }}">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add player</span>
        </a>
        <a class="admin-nav-link {{ $addNoticeActive ? 'active' : '' }}" href="{{ route('add_notice') }}">
            <i class="fa-solid fa-bullhorn"></i>
            <span>Add notice</span>
        </a>
        <a class="admin-nav-link {{ $delNoticeActive ? 'active' : '' }}" href="{{ route('del_notice') }}">
            <i class="fa-solid fa-trash-can"></i>
            <span>Delete notice</span>
        </a>
    @endif
</nav>
