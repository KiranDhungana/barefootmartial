@extends('layouts.student-portal')

@section('title', 'Notices — Student portal')
@section('page_title', 'Notices')
@section('page_subtitle', 'Academy announcements')

@section('content')
    @include('portal._student-switcher')

    <div class="panel-card">
        <div class="panel-heading">All notices</div>
        <div class="panel-body p-4">
            <ul class="mb-0 list-unstyled">
                @forelse ($notices as $n)
                    <li class="mb-3 pb-3 border-bottom">
                        <div class="fw-semibold">{{ $n->title }}</div>
                        <p class="mb-0 text-muted">{{ $n->description }}</p>
                    </li>
                @empty
                    <li class="text-muted">No notices.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
