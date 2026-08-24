@extends('layouts.public')

@section('title', 'Barefoot Martial Arts Academy')

@section('content')
    @php
        $slides = $slides ?? collect();
        $galleryPreview = $galleryPreview ?? collect();
        $fallbackSlides = [
            [
                'url' => asset('images/ban3.jpg'),
                'title' => 'Barefoot Martial Arts Academy',
                'subtitle' => 'Build fitness, focus, and confidence — from fundamentals to advanced practice.',
                'cta_label' => 'Register as student',
                'cta_url' => route('public.register'),
                'eyebrow' => 'Train with purpose',
            ],
            [
                'url' => asset('images/ban3.jpg'),
                'title' => 'A community that lifts each other',
                'subtitle' => 'Taekwondo, boxing, Tang Soo Do and more — programmes for every age and level.',
                'cta_label' => 'About us',
                'cta_url' => url('about-us'),
                'eyebrow' => null,
            ],
        ];
    @endphp
    <div class="hero-wrap">
        <div id="carouselExampleIndicators" class="carousel slide hero-carousel" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @forelse ($slides as $i => $slide)
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $i }}"
                        class="{{ $i === 0 ? 'active' : '' }}" @if ($i === 0) aria-current="true" @endif
                        aria-label="Slide {{ $i + 1 }}"></button>
                @empty
                    @foreach ($fallbackSlides as $i => $slide)
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $i }}"
                            class="{{ $i === 0 ? 'active' : '' }}" @if ($i === 0) aria-current="true" @endif
                            aria-label="Slide {{ $i + 1 }}"></button>
                    @endforeach
                @endforelse
            </div>
            <div class="carousel-inner">
                @forelse ($slides as $i => $slide)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <img src="{{ $slide->url }}" class="d-block w-100"
                            alt="{{ $slide->title ?: 'Barefoot Martial Arts' }}">
                        <div class="carousel-caption text-start">
                            <div class="caption-inner">
                                @if ($slide->title || $slide->subtitle)
                                    <h2 class="hero-title text-white mb-3">
                                        {{ $slide->title ?: 'Barefoot Martial Arts Academy' }}</h2>
                                    @if ($slide->subtitle)
                                        <p class="hero-lead text-white mb-4">{{ $slide->subtitle }}</p>
                                    @endif
                                    @if ($slide->cta_label && $slide->cta_url)
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ $slide->cta_url }}"
                                                class="btn btn-bf-primary btn-lg">{{ $slide->cta_label }}</a>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    @foreach ($fallbackSlides as $i => $slide)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            <img src="{{ $slide['url'] }}" class="d-block w-100" alt="{{ $slide['title'] }}">
                            <div class="carousel-caption text-start">
                                <div class="caption-inner">
                                    @if ($slide['eyebrow'])
                                        <p class="text-uppercase small fw-semibold text-white-50 mb-2 mb-lg-3 letter-spacing-1">
                                            {{ $slide['eyebrow'] }}</p>
                                    @endif
                                    <h2 class="hero-title text-white mb-3">{{ $slide['title'] }}</h2>
                                    <p class="hero-lead text-white mb-4">{{ $slide['subtitle'] }}</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ $slide['cta_url'] }}"
                                            class="btn btn-bf-primary btn-lg">{{ $slide['cta_label'] }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-6">
                    <h2 class="section-heading">Introduction</h2>
                    <div class="section-lead text-muted">
                        <p class="mb-3">
                            Welcome to Barefoot Martial Arts Academy — a space dedicated to self-defence skills, mental
                            focus, and physical fitness. We aim to grow a community of students who love martial arts and
                            strive to improve every day.
                        </p>
                        <p class="mb-3">
                            Training here is more than exercise: it cultivates respect, perseverance, and discipline
                            for life. Our instructors support learners of all ages with attentive, personalised guidance.<span
                                id="dots">...</span>
                            <span id="more" class="d-none">
                                Our goal is a safe, welcoming place where students grow as individuals and as teammates.
                                Programmes include Taekwondo, boxing, Tang Soo Do, and more — so everyone can find the right
                                path.
                                Whether you are starting out or sharpening advanced technique, we invite you to train with
                                us.
                            </span>
                        </p>
                        <button type="button" onclick="toggleRead('dots','more','myBtn')" id="myBtn"
                            class="btn btn-bf-outline btn-sm">Read more</button>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card-soft overflow-hidden p-2">
                        <img src="{{ asset('images/1.png') }}" class="img-fluid rounded-3 w-100"
                            alt="Barefoot Martial Arts">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white border-top border-bottom">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h2 class="section-heading text-center d-block mx-auto">Message from our coach</h2>
                    <div class="section-lead text-muted mt-4">
                        <p class="mb-3">
                            Greetings to all prospective students and martial arts enthusiasts! As head coach, I am
                            delighted to welcome you. We are a community passionate about sharing knowledge with everyone
                            who wants to learn.
                        </p>
                        <p class="mb-3">
                            Martial arts is a way of life — strength, flexibility, coordination, and confidence.<span
                                id="dots2">...</span>
                            <span id="more2" class="d-none">
                                Our instructors are committed to your progress with personalised attention. We foster an
                                inclusive environment where everyone can thrive.
                                We offer Taekwondo, boxing, Tang Soo Do, and more — try different styles and discover what
                                resonates with you.
                                Thank you for considering Barefoot Martial Arts Academy. We look forward to seeing you on
                                the mat.
                                <br><br>
                                <span class="fw-bold text-primary fs-5">Manish Gupta</span><br>
                                <span class="small text-muted">Head Coach, Barefoot Martial Arts Academy</span>
                            </span>
                        </p>
                        <button type="button" onclick="toggleRead('dots2','more2','myBtn2')" id="myBtn2"
                            class="btn btn-bf-outline btn-sm">Read more</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="gallery-section py-5">
        <div class="container">
            <h2 class="section-heading text-center d-block mx-auto mb-2">Explore us</h2>
            <p class="text-center text-muted mb-0">Tap an image to enlarge.</p>
        </div>
        <div class="lightbox" aria-hidden="true">
            <div class="wrapper">
                <header>
                    <div class="d-flex align-items-center gap-2 text-muted small fw-semibold">
                        <i class="fa-solid fa-camera"></i>
                        <span>Preview</span>
                    </div>
                    <button type="button" class="btn btn-link p-0 close-icon border-0 text-dark" aria-label="Close"><i
                            class="fa-solid fa-xmark fa-lg"></i></button>
                </header>
                <div class="preview-img">
                    <img src="" alt="Gallery preview" class="img-fluid">
                </div>
            </div>
        </div>
        <section class="gallery">
            <ul class="images">
                @forelse ($galleryPreview as $item)
                    <li class="img"><img src="{{ $item->url }}" alt="{{ $item->title ?: 'Training gallery' }}"></li>
                @empty
                    @foreach (['tk1', 'tk2', 'tk3', 'tk4', 'tk5', 'tk6'] as $img)
                        <li class="img"><img src="{{ asset('images/'.$img.'.jpg') }}" alt="Training gallery"></li>
                    @endforeach
                @endforelse
            </ul>
        </section>
        @if ($galleryPreview->isNotEmpty())
            <div class="container text-center mt-4">
                <a href="{{ route('public.gallery') }}" class="btn btn-bf-outline">View full gallery</a>
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        function toggleRead(dotsId, moreId, btnId) {
            var dots = document.getElementById(dotsId);
            var moreText = document.getElementById(moreId);
            var btn = document.getElementById(btnId);
            if (!dots || !moreText || !btn) return;
            var hidden = moreText.classList.contains('d-none');
            if (hidden) {
                moreText.classList.remove('d-none');
                dots.style.display = 'none';
                btn.textContent = 'Read less';
            } else {
                moreText.classList.add('d-none');
                dots.style.display = 'inline';
                btn.textContent = 'Read more';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var allImages = document.querySelectorAll('.gallery-section .images .img');
            var lightbox = document.querySelector('.gallery-section .lightbox');
            if (!lightbox) return;
            var closeBtn = lightbox.querySelector('.close-icon');
            var previewImg = lightbox.querySelector('.preview-img img');

            allImages.forEach(function(li) {
                li.addEventListener('click', function() {
                    var src = li.querySelector('img').getAttribute('src');
                    previewImg.src = src;
                    lightbox.classList.add('show');
                    document.body.style.overflow = 'hidden';
                });
            });
            closeBtn.addEventListener('click', function() {
                lightbox.classList.remove('show');
                document.body.style.overflow = 'auto';
            });
            lightbox.addEventListener('click', function(e) {
                if (e.target === lightbox) {
                    lightbox.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            });
        });
    </script>
@endpush
