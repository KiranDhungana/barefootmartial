@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Players and quick actions')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="label">Players</div>
                        <div class="value">{{ $playerCount ?? 0 }}</div>
                    </div>
                    <div class="icon-wrap bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="label">Notices</div>
                        <div class="value">{{ $noticeCount ?? 0 }}</div>
                    </div>
                    <div class="icon-wrap bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="label">Accounts (total)</div>
                        <div class="value">{{ $len ?? 0 }}</div>
                    </div>
                    <div class="icon-wrap bg-secondary bg-opacity-10 text-secondary">
                        <i class="fa-solid fa-id-badge"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="label">Shortcuts</div>
                        <div class="mt-2 d-flex flex-wrap gap-2">
                            <a href="{{ route('erp.dashboard') }}" class="btn btn-sm btn-primary text-white">
                                <i class="fa-solid fa-building-columns me-1"></i> Academy ERP
                            </a>
                            <a href="{{ route('register_user') }}" class="btn btn-sm btn-admin-primary text-white">
                                <i class="fa-solid fa-user-plus me-1"></i> Add player
                            </a>
                            <a href="{{ route('add_notice') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                Notice
                            </a>
                        </div>
                    </div>
                    <div class="icon-wrap bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-heading d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span>Player roster</span>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('erp.dashboard') }}" class="btn btn-sm btn-primary text-white">
                    <i class="fa-solid fa-building-columns me-1"></i> Academy ERP
                </a>
                <a href="{{ route('register_user') }}" class="btn btn-sm btn-admin-primary text-white">
                    <i class="fa-solid fa-user-plus me-1"></i> Add players
                </a>
                <a href="{{ route('add_notice') }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="fa-solid fa-plus me-1"></i> Add notice
                </a>
                <a href="{{ route('del_notice') }}" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="fa-solid fa-trash me-1"></i> Delete notice
                </a>
            </div>
        </div>
        <div class="panel-body table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">S.No</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Address</th>
                        <th scope="col">Phone</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($data as $item)
                        @if ($item->name == 'admin')
                            @continue
                        @endif
                        <tr>
                            <th scope="row" class="text-muted fw-semibold">{{ $i }}</th>
                            <td class="fw-medium text-dark">{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->address }}</td>
                            <td>{{ $item->phone }}</td>
                            <td class="text-end">
                                <div class="action-btns justify-content-end">
                                    <form action="/admin-del/{{ $item->id }}" method="post" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Delete</button>
                                    </form>
                                    <form action="/admin-update/{{ $item->id }}" method="get" class="d-inline">
                                        <button type="submit" class="btn btn-sm btn-admin-primary text-white rounded-pill">Update</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @php
                            $i = $i + 1;
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
