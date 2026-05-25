@extends('layouts.admin')
@section('title', 'Edit schedule')
@section('page_title', 'Edit schedule')
@section('content')
    <form method="post" action="{{ route('erp.schedules.update', $schedule) }}" class="panel-card p-4">@csrf @method('PUT') @include('erp.schedules._form', ['schedule' => $schedule])<button class="btn btn-admin-primary text-white mt-3">Update</button></form>
@endsection
