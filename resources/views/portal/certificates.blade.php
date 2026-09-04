@extends('layouts.student-portal')

@section('title', 'Certificates — Student portal')
@section('page_title', 'Certificates')
@section('page_subtitle', 'Belt, event, and normal certificates')

@section('content')
    @if ($students->isEmpty())
        <div class="alert alert-warning border-0 rounded-4">No student linked to your account.</div>
    @else
        @include('portal._student-switcher')

        @if ($student)
            <div class="row g-3">
                @include('partials.student-portal-certificates', [
                    'beltPromotions' => $beltPromotions,
                    'beltCertificates' => $beltCertificates,
                    'eventCertificates' => $eventCertificates,
                    'studentCertificates' => $studentCertificates,
                ])
            </div>

            @if ($beltPromotions->isEmpty()
                && $beltCertificates->isEmpty()
                && $eventCertificates->isEmpty()
                && $studentCertificates->isEmpty())
                <div class="panel-card">
                    <div class="panel-body p-4 text-center text-muted">
                        No certificates have been attached yet.
                    </div>
                </div>
            @endif
        @endif
    @endif
@endsection
