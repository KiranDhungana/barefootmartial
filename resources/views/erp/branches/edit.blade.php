@extends('layouts.admin')
@section('title', 'Edit branch')
@section('page_title', 'Edit '.$branch->name)
@section('content')
    <form method="post" action="{{ route('erp.branches.update', $branch) }}" class="panel-card p-4">
        @csrf @method('PUT')
        @include('erp.branches._form')
        <button class="btn btn-admin-primary text-white mt-3">Update</button>
    </form>
@endsection
