@extends('layouts.admin')

@section('title', 'ERP users')
@section('page_title', 'ERP users & roles')
@section('page_subtitle', 'Super admin only')

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif
    <div class="mb-3">
        <a href="{{ route('erp.users.create') }}" class="btn btn-admin-primary text-white">Add ERP user</a>
    </div>
    <div class="panel-card">
        <div class="panel-body table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branch</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->roleLabel() }}</td>
                            <td>{{ $u->branch->name ?? 'All branches' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center py-4">No ERP users.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $users->links() }}
@endsection
