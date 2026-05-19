@extends('layouts.public')

@section('title', 'About us — Barefoot Martial Arts Academy')

@section('content')
    <section class="py-5 border-bottom bg-white"
        style="background: linear-gradient(135deg, rgba(59,130,246,0.08) 0%, rgba(34,197,94,0.06) 100%);">
        <div class="container py-lg-4">
            <div class="row justify-content-center text-center">
                <div class="col-lg-9">
                    <p class="text-uppercase small fw-semibold text-primary mb-2">About us</p>
                    <h1 class="display-6 fw-bold mb-3" style="letter-spacing:-0.03em;">Barefoot Martial Arts Academy</h1>
                    <p class="lead text-muted mb-0">
                        A welcoming academy for learning martial arts — building self-defence skills, mental focus, and
                        physical fitness together.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <h2 class="section-heading text-center d-block mx-auto mb-5">Our coaches</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card-soft h-100 overflow-hidden">
                        <img src="{{ asset('images/gup.jpg') }}" class="w-100 object-fit-cover" style="height: 380px;"
                            alt="Manish Gupta">
                        <div class="p-4">
                            <h3 class="h5 fw-bold mb-1">Manish Gupta</h3>
                            <p class="small text-muted mb-0"><i class="fa-solid fa-envelope me-1"></i>gptmanish28@gmail.com
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5">
                    <div class="card-soft h-100 overflow-hidden">
                        <img src="{{ asset('images/gup2.jpg') }}" class="w-100 object-fit-cover" style="height: 380px;"
                            alt="Krishna Mohan Yadav">
                        <div class="p-4">
                            <h3 class="h5 fw-bold mb-1">Krishna Mohan Yadav</h3>
                            <p class="small text-muted mb-0"><i
                                    class="fa-solid fa-envelope me-1"></i>krishna.oskss@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
