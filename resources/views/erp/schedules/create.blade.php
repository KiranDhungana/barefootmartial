@extends('layouts.admin')
@section('title', 'Add schedule')
@section('page_title', 'Add class schedule')
@section('content')
    <form method="post" action="{{ route('erp.schedules.store') }}" class="panel-card p-4">@csrf @include('erp.schedules._form')<button class="btn btn-admin-primary text-white mt-3">Save</button></form>
@endsection
