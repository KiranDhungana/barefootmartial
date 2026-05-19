@extends('layouts.public')

@section('title', ($notice->title ?? 'Notice').' — Barefoot Martial Arts Academy')

@section('content')
    @php
        $files = [];
        if (!empty($notice->path)) {
            $decoded = json_decode($notice->path, true);
            if (is_array($decoded)) {
                $files = $decoded;
            }
        }
        $len = count($files);
    @endphp

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb small mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('notice_home') }}">Notices</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($notice->title, 40) }}
                            </li>
                        </ol>
                    </nav>

                    <div class="card-soft p-4 p-lg-5 mb-4">
                        <h1 class="h3 fw-bold mb-3" style="letter-spacing:-0.02em;">{{ $notice->title }}</h1>
                        <p class="text-muted mb-0 lh-lg">{{ $notice->description }}</p>
                    </div>

                    @if ($len > 0)
                        <div class="card-soft p-4 p-lg-5">
                            <h2 class="h6 fw-bold text-uppercase text-muted letter-spacing mb-3">Attachments</h2>
                            <ul class="list-unstyled mb-0">
                                @for ($i = 0; $i < $len; $i++)
                                    <li class="mb-2">
                                        <a href="{{ asset('storage/files/'.$files[$i]) }}" target="_blank" rel="noopener"
                                            class="btn btn-outline-primary btn-sm rounded-pill">
                                            <i class="fa-solid fa-paperclip me-1"></i> Download {{ $i + 1 }}
                                        </a>
                                    </li>
                                @endfor
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
