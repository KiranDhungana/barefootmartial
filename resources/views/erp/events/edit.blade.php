@extends('layouts.admin')
@section('title', 'Edit event')
@section('page_title', 'Edit event')
@section('content')
    <form method="post" action="{{ route('erp.events.update', $event) }}" class="panel-card p-4">@csrf @method('PUT') @include('erp.events._form', ['event' => $event])<button class="btn btn-admin-primary text-white mt-3">Save</button></form>
    <form method="post" action="{{ route('erp.events.destroy', $event) }}" class="mt-3" onsubmit="return confirm('Delete this event and all its registrations?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger rounded-pill">Delete event</button>
    </form>
@endsection
