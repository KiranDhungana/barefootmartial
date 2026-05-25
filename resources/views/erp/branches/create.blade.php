@extends('layouts.admin')
@section('title', 'Add branch')
@section('page_title', 'Add branch')
@section('content')
    <form method="post" action="{{ route('erp.branches.store') }}" class="panel-card p-4">
        @csrf
        @include('erp.branches._form')
        <button class="btn btn-admin-primary text-white mt-3">Save</button>
    </form>
@endsection
