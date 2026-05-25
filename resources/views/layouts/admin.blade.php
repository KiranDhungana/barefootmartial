<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Barefoot Martial Arts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/47b6cf0509.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --admin-sidebar-bg: #0c1222;
            --admin-sidebar-border: rgba(255, 255, 255, 0.06);
            --admin-accent: #3b82f6;
            --admin-accent-soft: rgba(59, 130, 246, 0.15);
            --admin-text-muted: #94a3b8;
            --admin-main-bg: #f1f5f9;
            --admin-card-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        body.admin-body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: var(--admin-main-bg);
            min-height: 100vh;
        }

        .admin-app {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 260px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--admin-sidebar-bg) 0%, #111827 100%);
            color: #e2e8f0;
            border-right: 1px solid var(--admin-sidebar-border);
            display: flex;
            flex-direction: column;
            padding: 1.25rem 1rem 1.5rem;
        }

        .admin-sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.35rem 0.5rem 1.25rem;
            border-bottom: 1px solid var(--admin-sidebar-border);
            margin-bottom: 1rem;
        }

        .admin-sidebar-brand img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            padding: 4px;
        }

        .admin-sidebar-brand span {
            font-weight: 600;
            font-size: 0.95rem;
            line-height: 1.25;
            color: #f8fafc;
        }

        .admin-sidebar-brand small {
            display: block;
            font-size: 0.7rem;
            font-weight: 500;
            color: var(--admin-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .admin-sidebar-nav .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.65rem 0.85rem;
            border-radius: 10px;
            color: var(--admin-text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.92rem;
            transition: background 0.15s, color 0.15s;
        }

        .admin-sidebar-nav .admin-nav-link i {
            width: 1.25rem;
            text-align: center;
            opacity: 0.9;
        }

        .admin-sidebar-nav .admin-nav-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #f1f5f9;
        }

        .admin-sidebar-nav .admin-nav-link.active {
            background: var(--admin-accent-soft);
            color: #93c5fd;
            box-shadow: inset 2px 0 0 var(--admin-accent);
        }

        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .admin-topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04);
        }

        .admin-menu-toggle {
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 10px;
            padding: 0.45rem 0.65rem;
            color: #334155;
        }

        .admin-page-heading {
            flex: 1;
            min-width: 0;
        }

        .admin-page-heading h1 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.02em;
        }

        .admin-page-heading p {
            margin: 0.15rem 0 0;
            font-size: 0.875rem;
            color: #64748b;
        }

        .admin-content {
            padding: 1.25rem 1.25rem 2rem;
            flex: 1;
        }

        @media (min-width: 992px) {
            .admin-menu-toggle {
                display: none;
            }
        }

        .admin-flash .alert {
            border: none;
            border-radius: 12px;
            box-shadow: var(--admin-card-shadow);
        }

        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
            border: 1px solid #e2e8f0;
            box-shadow: var(--admin-card-shadow);
            height: 100%;
        }

        .stat-card .label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0.25rem;
            letter-spacing: -0.03em;
        }

        .stat-card .icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }

        .panel-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--admin-card-shadow);
            overflow: hidden;
        }

        .panel-card .panel-heading {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            color: #0f172a;
            font-size: 1rem;
        }

        .panel-card .panel-body {
            padding: 0;
        }

        .admin-table {
            margin: 0;
        }

        .admin-table thead th {
            background: #f8fafc;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1rem;
            white-space: nowrap;
        }

        .admin-table tbody td,
        .admin-table tbody th {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        .admin-table tbody tr:hover {
            background: #fafbfc;
        }

        .action-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn-admin-primary {
            background: var(--admin-accent);
            border-color: var(--admin-accent);
            border-radius: 10px;
            font-weight: 600;
            padding: 0.45rem 1rem;
        }

        .btn-admin-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        .offcanvas.admin-offcanvas {
            background: linear-gradient(180deg, var(--admin-sidebar-bg) 0%, #111827 100%);
            color: #e2e8f0;
        }

        .offcanvas.admin-offcanvas .btn-close {
            filter: invert(1);
            opacity: 0.7;
        }

        @media (max-width: 991.98px) {
            .admin-content {
                padding: 1rem 0.85rem 1.5rem;
            }

            .admin-page-heading h1 {
                font-size: 1.15rem;
            }

            .stat-card .value {
                font-size: 1.35rem;
            }

            .panel-card .panel-heading {
                padding: 0.85rem 1rem;
                font-size: 0.95rem;
            }

            .admin-table thead th,
            .admin-table tbody td {
                padding: 0.6rem 0.65rem;
                font-size: 0.88rem;
            }

            .nav-tabs .nav-link {
                font-size: 0.85rem;
                padding: 0.5rem 0.75rem;
            }

            .btn.rounded-pill {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 575.98px) {
            .admin-topbar {
                padding: 0.65rem 0.85rem;
            }

            .action-btns .btn {
                width: 100%;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="admin-body">
    <div class="admin-app">
        <aside class="admin-sidebar d-none d-lg-flex">
            <div class="admin-sidebar-brand">
                <img src="/images/logo.png" alt="Barefoot Martial Arts">
                <div>
                    <span>Barefoot</span>
                    <small>Admin</small>
                </div>
            </div>
            @include('partials.admin-sidebar-nav')
        </aside>

        <div class="offcanvas offcanvas-start admin-offcanvas d-lg-none" tabindex="-1" id="adminSidebar"
            aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
                <h5 class="offcanvas-title text-white" id="adminSidebarLabel">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column">
                @include('partials.admin-sidebar-nav')
            </div>
        </div>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="admin-menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="admin-page-heading">
                    <h1>@yield('page_title', 'Dashboard')</h1>
                    @hasSection('page_subtitle')
                        <p>@yield('page_subtitle')</p>
                    @endif
                </div>
                <div class="dropdown">
                    <button class="btn btn-light border rounded-pill px-3 d-flex align-items-center gap-2" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-circle-user text-secondary fs-5"></i>
                        <span class="d-none d-sm-inline fw-semibold text-dark">{{ Auth::user()->name }}</span>
                        <i class="fa-solid fa-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                        <li>
                            <a class="dropdown-item rounded-2" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>
                        </li>
                    </ul>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </header>

            <main class="admin-content">
                <div class="admin-flash mb-3">
                    @if (Session::has('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (Session::has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ Session::get('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (Session::has('msg'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('msg') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (Session::has('updatedmsg'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('updatedmsg') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (Session::has('deletedmsg'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('deletedmsg') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (Session::has('notice_message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('notice_message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (Session::has('delete_message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ Session::get('delete_message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    @stack('scripts')
</body>

</html>
