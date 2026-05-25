@extends('layouts.admin')
@section('title', 'New event')
@section('page_title', 'New event')
@section('content')
    <form method="post" action="{{ route('erp.events.store') }}" class="panel-card p-4">@csrf @include('erp.events._form')<button class="btn btn-admin-primary text-white mt-3">Create</button></form>
@endsection
