@extends('layouts.admin')
@section('title', 'Edit event')
@section('page_title', 'Edit event')
@section('content')
    <form method="post" action="{{ route('erp.events.update', $event) }}" class="panel-card p-4">@csrf @method('PUT') @include('erp.events._form', ['event' => $event])<button class="btn btn-admin-primary text-white mt-3">Save</button></form>
@endsection
