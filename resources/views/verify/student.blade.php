@extends('layouts.public')

@section('title', 'Member verification')

@section('content')
    <section class="py-5">
        <div class="container" style="max-width:560px">
            @if (session('error'))
                <div class="alert alert-danger border-0 rounded-4">{{ session('error') }}</div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 text-center">
                    @if ($student->photo_path)
                        <img src="{{ asset('storage/'.$student->photo_path) }}" class="rounded-circle mb-3"
                            style="width:120px;height:120px;object-fit:cover" alt="">
                    @endif
                    <h1 class="h4 mb-1">{{ $student->name }}</h1>
                    <p class="text-muted mb-3">{{ $student->student_code }}</p>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                        @if ($student->isOfficial())
                            <span class="badge bg-success rounded-pill">Official member</span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill">Pending registration</span>
                        @endif
                        <span class="badge bg-primary rounded-pill">{{ $student->statusLabel() }}</span>
                    </div>

                    <ul class="list-unstyled text-start small mb-4">
                        <li><strong>Branch:</strong> {{ $student->branch->name ?? '—' }}</li>
                        <li><strong>Belt:</strong> {{ $student->belt_rank ?? '—' }}
                            @if ($nextBelt)
                                <span class="text-muted">(next: {{ $nextBelt }})</span>
                            @endif
                        </li>
                        <li><strong>Coach:</strong> {{ $student->coach_name ?? '—' }}</li>
                        <li><strong>Joined:</strong> {{ optional($student->join_date)->format('M j, Y') ?? '—' }}</li>
                    </ul>

                    @if ($canCheckIn && $student->isOfficial() && ! in_array($student->status, ['inactive', 'suspended']))
                        <a href="{{ $checkInUrl }}" class="btn btn-success rounded-pill w-100 mb-2">Staff: mark attendance today</a>
                    @endif

                    <p class="text-muted small mb-0">Barefoot Martial Arts — verified {{ now()->format('M j, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection
