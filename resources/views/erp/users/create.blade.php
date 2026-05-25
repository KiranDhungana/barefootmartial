@extends('layouts.admin')

@section('title', 'Add ERP user')
@section('page_title', 'Add ERP user')

@section('content')
    <div class="panel-card">
        <div class="panel-body p-4 mx-auto" style="max-width:520px">
            <form method="post" action="{{ route('erp.users.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control rounded-3" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" id="erp-role" class="form-select rounded-3" required>
                        @foreach ($roles as $r)
                            <option value="{{ $r }}" @selected(old('role') === $r)>{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3" id="branch-field">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select rounded-3">
                        <option value="">—</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }} ({{ $b->code }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-admin-primary text-white">Create user</button>
                <a href="{{ route('erp.users.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const roleEl = document.getElementById('erp-role');
        const branchField = document.getElementById('branch-field');
        function toggleBranch() {
            const r = roleEl.value;
            const needs = ['branch_admin', 'accountant', 'coach'].includes(r);
            branchField.style.display = needs ? 'block' : 'none';
        }
        roleEl.addEventListener('change', toggleBranch);
        toggleBranch();
    </script>
@endpush
