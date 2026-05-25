@extends('layouts.admin')

@section('title', 'Add student')
@section('page_title', 'Add student')
@section('page_subtitle', 'Central registration — pending until marked official')

@section('content')
    <div class="alert alert-info border-0 rounded-4 mb-3">
        <strong>Central registration rule:</strong> New students are saved as <em>pending</em>. Attendance, billing, and ID
        cards require <strong>official</strong> registration after required fields are complete.
    </div>
    <div class="panel-card">
        <div class="panel-heading">Student details</div>
        <div class="panel-body p-4">
            <form method="post" action="{{ route('erp.students.store') }}" enctype="multipart/form-data"
                class="mx-auto" style="max-width:720px">
                @csrf
                @include('erp.students._form', ['student' => null])
                <button type="submit" class="btn btn-admin-primary text-white">Save student</button>
                <a href="{{ route('erp.students.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
@endsection
