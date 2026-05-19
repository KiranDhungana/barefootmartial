@extends('layouts.public')

@section('title', 'Gallery — Barefoot Martial Arts Academy')

@section('content')
    <section class="py-5">
        <div class="container text-center mb-4">
            <h1 class="section-heading">Gallery</h1>
            <p class="text-muted mb-0">Moments from training — tap any photo to enlarge.</p>
        </div>

        <div class="gallery-section">
            <div class="lightbox" aria-hidden="true">
                <div class="wrapper">
                    <header>
                        <div class="d-flex align-items-center gap-2 text-muted small fw-semibold">
                            <i class="fa-solid fa-camera"></i>
                            <span>Preview</span>
                        </div>
                        <button type="button" class="btn btn-link p-0 close-icon border-0 text-dark"
                            aria-label="Close"><i class="fa-solid fa-xmark fa-lg"></i></button>
                    </header>
                    <div class="preview-img">
                        <img src="" alt="Gallery preview" class="img-fluid rounded-3">
                    </div>
                </div>
            </div>
            <section class="gallery">
                <ul class="images">
                    @foreach (range(1, 12) as $n)
                        <li class="img"><img src="{{ asset('images/tk'.$n.'.jpg') }}" alt="Gallery photo {{ $n }}"></li>
                    @endforeach
                </ul>
            </section>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var root = document.querySelector('.gallery-section');
            if (!root) return;
            var allImages = root.querySelectorAll('.images .img');
            var lightbox = root.querySelector('.lightbox');
            var closeBtn = lightbox.querySelector('.close-icon');
            var previewImg = lightbox.querySelector('.preview-img img');

            allImages.forEach(function(li) {
                li.addEventListener('click', function() {
                    previewImg.src = li.querySelector('img').src;
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
