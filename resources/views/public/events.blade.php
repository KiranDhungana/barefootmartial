@extends('layouts.public')

@section('title', 'Events — Barefoot Martial Arts Academy')

@section('content')
    <section class="py-5">
        <div class="container" style="max-width:860px">
            <h1 class="section-heading mb-2">Events & tournaments</h1>
            <p class="text-muted mb-4">Browse upcoming events and register online.</p>

            @forelse ($events as $e)
                <div class="card-soft p-4 mb-3">
                    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start">
                        <div>
                            <h2 class="h5 mb-1">{{ $e->title }}</h2>
                            @if ($e->event_date)
                                <p class="small text-muted mb-1">
                                    <i class="fa-regular fa-calendar me-1"></i>{{ $e->event_date->format('M j, Y') }}
                                </p>
                            @endif
                            @if ($e->branch)
                                <p class="small mb-1">Branch: {{ $e->branch->name }}</p>
                            @endif
                            @if ($e->fee_amount > 0)
                                <p class="small mb-1">Fee: Rs. {{ number_format($e->fee_amount, 2) }}</p>
                            @endif
                            @if ($e->description)
                                <p class="mb-0 text-muted">{{ \Illuminate\Support\Str::limit($e->description, 180) }}</p>
                            @endif
                        </div>
                        <div class="text-end">
                            @if ($e->isOpenForRegistration())
                                <span class="badge rounded-pill bg-success mb-2">Open</span>
                            @else
                                <span class="badge rounded-pill bg-secondary mb-2">Closed</span>
                            @endif
                            <div>
                                <a href="{{ route('public.events.show', $e) }}" class="btn btn-bf-primary btn-sm">
                                    {{ $e->isOpenForRegistration() ? 'Register' : 'View' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted">No published events at the moment.</p>
            @endforelse
        </div>
    </section>
@endsection
