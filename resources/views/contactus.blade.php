@extends('layouts.public')

@section('title', 'Contact — Barefoot Martial Arts Academy')

@section('content')
    <section class="py-5">
        <div class="container py-lg-2">
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8 text-center">
                    <h1 class="section-heading d-inline-block">Get in touch</h1>
                    <p class="text-muted mt-2 mb-0">Send us a message — we will get back to you as soon as we can.</p>
                </div>
            </div>

            @if (Session::has('sentmsg'))
                <div class="alert alert-success border-0 rounded-4 shadow-sm col-lg-10 mx-auto mb-4" role="alert">
                    {{ Session::get('sentmsg') }}
                </div>
            @endif

            <div class="row g-4 justify-content-center align-items-stretch">
                <div class="col-lg-6">
                    <div class="card-soft p-4 p-lg-5 h-100">
                        <form action="{{ route('send_mail') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                <input name="name" type="text"
                                    class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" placeholder="Your name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input name="email" type="email"
                                    class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" placeholder="you@example.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                                <input name="phone" type="tel"
                                    class="form-control form-control-lg rounded-3 @error('phone') is-invalid @enderror"
                                    value="{{ old('phone') }}" placeholder="+977 …" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                                <input name="subject" type="text"
                                    class="form-control form-control-lg rounded-3 @error('subject') is-invalid @enderror"
                                    value="{{ old('subject') }}" placeholder="How can we help?" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                                <textarea name="message" rows="4"
                                    class="form-control rounded-3 @error('message') is-invalid @enderror"
                                    placeholder="Your message" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-bf-primary btn-lg w-100 rounded-pill">Send message</button>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card-soft p-4 p-lg-5 h-100 d-flex flex-column">
                        <h2 class="h5 fw-bold mb-4">Contact details</h2>
                        <div class="mb-4 text-center">
                            <img src="{{ asset('images/mail.png') }}" class="img-fluid" style="max-height:120px"
                                alt="">
                        </div>
                        <div class="contact-rows flex-grow-1">
                            <div class="d-flex gap-3 mb-3">
                                <span class="text-primary"><i class="fa-solid fa-phone fa-lg"></i></span>
                                <div>
                                    <div class="small text-muted text-uppercase fw-semibold">Phone</div>
                                    <a href="tel:+9779847445948" class="text-dark text-decoration-none fs-5">+977-9847445948</a>
                                </div>
                            </div>
                            <div class="d-flex gap-3 mb-3">
                                <span class="text-primary"><i class="fa-solid fa-envelope fa-lg"></i></span>
                                <div>
                                    <div class="small text-muted text-uppercase fw-semibold">Email</div>
                                    <a href="mailto:barefootmartialarts@gmail.com"
                                        class="text-dark text-decoration-none">barefootmartialarts@gmail.com</a>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <span class="text-primary"><i class="fa-solid fa-location-dot fa-lg"></i></span>
                                <div>
                                    <div class="small text-muted text-uppercase fw-semibold">Address</div>
                                    <span>Ramgram-05, Parasi, Nawalparasi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
