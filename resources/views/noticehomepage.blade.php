@extends('layouts.public')

@section('title', 'Notices — Barefoot Martial Arts Academy')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="section-heading d-inline-block">Latest notices</h1>
                <p class="text-muted mt-2 mb-0">Updates from the academy.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    @forelse ($notice as $item)
                        <a href="{{ url('notice-home/'.$item->id) }}" class="text-decoration-none text-reset">
                            <article class="card-soft mb-4 overflow-hidden transition-hover">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <img src="{{ asset('storage/images/notice.jpg') }}"
                                            class="img-fluid h-100 object-fit-cover w-100"
                                            style="min-height: 200px;" alt="">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="p-4">
                                            <h2 class="h5 fw-bold text-primary mb-2">{{ $item->title }}</h2>
                                            <p class="text-muted mb-2">{{ Str::limit($item->description, 220) }}</p>
                                            <p class="small text-secondary mb-0">
                                                <i class="fa-regular fa-calendar me-1"></i>
                                                {{ $item->updated_at->toFormattedDateString() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </a>
                    @empty
                        <p class="text-center text-muted py-5">No notices published yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .transition-hover {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        a:hover .transition-hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.1);
        }
    </style>
@endpush
