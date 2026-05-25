@extends('layouts.admin')

@section('title', 'Edit student')
@section('page_title', 'Edit student')
@section('page_subtitle', $student->student_code)

@section('content')
    <div class="panel-card">
        <div class="panel-heading">Student details</div>
        <div class="panel-body p-4">
            <form method="post" action="{{ route('erp.students.update', $student) }}" enctype="multipart/form-data"
                class="mx-auto" style="max-width:720px">
                @csrf
                @method('PUT')
                @include('erp.students._form', ['student' => $student])
                <button type="submit" class="btn btn-admin-primary text-white">Update</button>
                <a href="{{ route('erp.students.show', $student) }}" class="btn btn-link">Back</a>
            </form>
        </div>
    </div>
@endsection
