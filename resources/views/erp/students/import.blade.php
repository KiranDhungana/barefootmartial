@extends('layouts.admin')

@section('title', 'Import students')
@section('page_title', 'Existing student import')
@section('page_subtitle', 'Bulk CSV or single manual entry')

@section('content')
    @if (session('success'))
        <div class="alert alert-success border-0 rounded-4">{{ session('success') }}</div>
    @endif
    @if ($errors->has('import'))
        <div class="alert alert-danger border-0 rounded-4">{{ $errors->first('import') }}</div>
    @endif
    @if (session('import_errors'))
        <div class="alert alert-warning border-0 rounded-4">
            <strong>Some rows failed:</strong>
            <ul class="mb-0 small">
                @foreach (session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="panel-card">
                <div class="panel-heading">Upload CSV / Excel (save as CSV)</div>
                <div class="panel-body p-4">
                    <p class="small text-muted">Columns: name, belt, branch (code or name), joining_date, fee_status,
                        uniform_status, parent_name, parent_contact, phone, address, status</p>
                    <a href="{{ route('erp.students.import.template') }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3">
                        Download template
                    </a>
                    <form method="post" action="{{ route('erp.students.import.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="csv_file" accept=".csv,.txt" class="form-control rounded-3 mb-3" required>
                        <button type="submit" class="btn btn-admin-primary text-white">Import file</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-card">
                <div class="panel-heading">Add one existing student manually</div>
                <div class="panel-body p-4">
                    <form method="post" action="{{ route('erp.students.import.manual') }}">
                        @csrf
                        <div class="mb-2">
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Full name *" required
                                value="{{ old('name') }}">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="text" name="belt_rank" class="form-control rounded-3" placeholder="Belt"
                                    value="{{ old('belt_rank') }}">
                            </div>
                            <div class="col-6">
                                <input type="text" name="branch" class="form-control rounded-3" placeholder="Branch code"
                                    value="{{ old('branch') }}">
                            </div>
                        </div>
                        <div class="mb-2">
                            <input type="date" name="join_date" class="form-control rounded-3" value="{{ old('join_date') }}">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="text" name="parent_name" class="form-control rounded-3" placeholder="Parent name"
                                    value="{{ old('parent_name') }}">
                            </div>
                            <div class="col-6">
                                <input type="text" name="parent_contact" class="form-control rounded-3" placeholder="Parent contact"
                                    value="{{ old('parent_contact') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="phone" class="form-control rounded-3" placeholder="Phone"
                                value="{{ old('phone') }}">
                        </div>
                        <button type="submit" class="btn btn-outline-primary rounded-pill">Import student</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
