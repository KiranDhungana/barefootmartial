@php
    $dashboardActive = request()->routeIs('admin-home', 'user.update');
    $erpDash = request()->routeIs('erp.dashboard');
    $erpStudents = request()->routeIs('erp.students.*');
    $erpImport = request()->routeIs('erp.students.import*');
    $erpAttendance = request()->routeIs('erp.attendance.*');
    $erpInvoices = request()->routeIs('erp.invoices.*');
    $erpFees = request()->routeIs('erp.fees.*');
    $erpInventory = request()->routeIs('erp.inventory.*');
    $erpTrainers = request()->routeIs('erp.trainers.*');
    $erpSalary = request()->routeIs('erp.salary.*');
    $erpReports = request()->routeIs('erp.reports.*');
    $erpHq = request()->routeIs('erp.hq.*');
    $erpBelts = request()->routeIs('erp.belts.*');
    $erpExpenses = request()->routeIs('erp.expenses.*');
    $erpBranchReports = request()->routeIs('erp.branch-reports.*');
    $erpAudit = request()->routeIs('erp.audit.*');
    $erpUsers = request()->routeIs('erp.users.*');
    $erpBranches = request()->routeIs('erp.branches.*');
    $erpSchedules = request()->routeIs('erp.schedules.*');
    $erpEvents = request()->routeIs('erp.events.*');
    $erpOnlineReg = request()->routeIs('erp.online-registrations.*');
    $erpCompliance = request()->routeIs('erp.compliance.*');
    $erpNotifications = request()->routeIs('erp.notifications.*');
    $erpInvReport = request()->routeIs('erp.inventory.report*');
    $addPlayerActive = request()->routeIs('register_user');
    $addNoticeActive = request()->routeIs('add_notice');
    $delNoticeActive = request()->routeIs('del_notice');
    $user = auth()->user();
    $isSuperAdmin = $user && $user->isSuperAdmin();
    $canFinance = $user && $user->canManageFinance();
    $canImport = $user && $user->canImportStudents();
    $canAudit = $user && $user->canViewAuditLogs();
@endphp
<nav class="admin-sidebar-nav flex-column gap-1" aria-label="Admin navigation">
    @if ($user && $user->canAccessErp())
        <div class="text-uppercase small text-white-50 px-2 mb-1 mt-1" style="font-size:0.65rem;letter-spacing:0.08em;">Academy ERP</div>
        @if ($isSuperAdmin)
            <a class="admin-nav-link {{ $erpHq ? 'active' : '' }}" href="{{ route('erp.hq.dashboard') }}">
                <i class="fa-solid fa-building"></i>
                <span>Head office</span>
            </a>
        @endif
        <a class="admin-nav-link {{ $erpDash && ! $erpHq ? 'active' : '' }}" href="{{ route('erp.dashboard') }}">
            <i class="fa-solid fa-chart-line"></i>
            <span>{{ $isSuperAdmin ? 'Branch console' : 'Console' }}</span>
        </a>
        <a class="admin-nav-link {{ $erpStudents && ! $erpImport ? 'active' : '' }}" href="{{ route('erp.students.index') }}">
            <i class="fa-solid fa-user-graduate"></i>
            <span>Students</span>
        </a>
        @if ($canImport)
            <a class="admin-nav-link {{ $erpImport ? 'active' : '' }}" href="{{ route('erp.students.import') }}">
                <i class="fa-solid fa-file-import"></i>
                <span>Import students</span>
            </a>
        @endif
        <a class="admin-nav-link {{ $erpAttendance ? 'active' : '' }}" href="{{ route('erp.attendance.index') }}">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Attendance</span>
        </a>
        <a class="admin-nav-link" href="{{ route('erp.attendance.bulk') }}">
            <i class="fa-solid fa-list-check"></i>
            <span>Bulk attendance</span>
        </a>
        <a class="admin-nav-link {{ $erpBranches ? 'active' : '' }}" href="{{ route('erp.branches.index') }}">
            <i class="fa-solid fa-location-dot"></i>
            <span>Branches</span>
        </a>
        <a class="admin-nav-link {{ $erpSchedules ? 'active' : '' }}" href="{{ route('erp.schedules.index') }}">
            <i class="fa-solid fa-clock"></i>
            <span>Schedules</span>
        </a>
        <a class="admin-nav-link {{ $erpEvents ? 'active' : '' }}" href="{{ route('erp.events.index') }}">
            <i class="fa-solid fa-trophy"></i>
            <span>Events</span>
        </a>
        <a class="admin-nav-link {{ $erpOnlineReg ? 'active' : '' }}" href="{{ route('erp.online-registrations.index') }}">
            <i class="fa-solid fa-globe"></i>
            <span>Online signups</span>
        </a>
        <a class="admin-nav-link {{ $erpBelts ? 'active' : '' }}" href="{{ route('erp.belts.index') }}">
            <i class="fa-solid fa-medal"></i>
            <span>Belt promotions</span>
        </a>
        @if ($canFinance)
            <a class="admin-nav-link {{ $erpInvoices ? 'active' : '' }}" href="{{ route('erp.invoices.index') }}">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Invoices</span>
            </a>
            <a class="admin-nav-link {{ $erpFees ? 'active' : '' }}" href="{{ route('erp.fees.index') }}">
                <i class="fa-solid fa-coins"></i>
                <span>Fee tracking</span>
            </a>
            <a class="admin-nav-link {{ $erpInventory ? 'active' : '' }}" href="{{ route('erp.inventory.index') }}">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Inventory</span>
            </a>
            <a class="admin-nav-link {{ $erpInvReport ? 'active' : '' }}" href="{{ route('erp.inventory.report') }}">
                <i class="fa-solid fa-shirt"></i>
                <span>Uniform sales</span>
            </a>
            <a class="admin-nav-link {{ $erpNotifications ? 'active' : '' }}" href="{{ route('erp.notifications.index') }}">
                <i class="fa-solid fa-bell"></i>
                <span>Notifications</span>
            </a>
            <a class="admin-nav-link {{ $erpBranchReports ? 'active' : '' }}" href="{{ route('erp.branch-reports.index') }}">
                <i class="fa-solid fa-chart-column"></i>
                <span>Branch reports</span>
            </a>
            <a class="admin-nav-link {{ $erpExpenses ? 'active' : '' }}" href="{{ route('erp.expenses.index') }}">
                <i class="fa-solid fa-receipt"></i>
                <span>Expenses</span>
            </a>
            <a class="admin-nav-link {{ $erpReports ? 'active' : '' }}" href="{{ route('erp.reports.index') }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Summary reports</span>
            </a>
        @endif
        @if ($canAudit)
            <a class="admin-nav-link {{ $erpAudit ? 'active' : '' }}" href="{{ route('erp.audit.index') }}">
                <i class="fa-solid fa-clipboard-list"></i>
                <span>Audit log</span>
            </a>
        @endif
        @if ($isSuperAdmin || $canFinance)
            <a class="admin-nav-link {{ $erpCompliance ? 'active' : '' }}" href="{{ route('erp.compliance.index') }}">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Compliance</span>
            </a>
        @endif
        @if ($isSuperAdmin)
            <a class="admin-nav-link {{ $erpUsers ? 'active' : '' }}" href="{{ route('erp.users.index') }}">
                <i class="fa-solid fa-users-gear"></i>
                <span>ERP users</span>
            </a>
            <a class="admin-nav-link {{ $erpTrainers ? 'active' : '' }}" href="{{ route('erp.trainers.index') }}">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Trainers</span>
            </a>
            <a class="admin-nav-link {{ $erpSalary ? 'active' : '' }}" href="{{ route('erp.salary.index') }}">
                <i class="fa-solid fa-money-check-dollar"></i>
                <span>Salary</span>
            </a>
        @endif
        <div class="border-bottom border-secondary border-opacity-25 my-2"></div>
    @endif

    @if ($isSuperAdmin)
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
