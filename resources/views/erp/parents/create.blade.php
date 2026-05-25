@extends('layouts.admin')
@section('title', 'Parent account')
@section('page_title', 'Parent portal — '.$student->name)
@section('content')
    <form method="post" action="{{ route('erp.parents.store', $student) }}" class="panel-card p-4" style="max-width:480px">
        @csrf
        <p class="small text-muted">Creates a login linked to this student only.</p>
        <div class="mb-3"><label class="form-label">Parent name</label><input name="name" class="form-control rounded-3" value="{{ old('name', $student->parent_name) }}" required></div>
        <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control rounded-3" required minlength="8"></div>
        <button class="btn btn-admin-primary text-white">Create account</button>
        <a href="{{ route('erp.students.show', $student) }}" class="btn btn-link">Cancel</a>
    </form>
@endsection
