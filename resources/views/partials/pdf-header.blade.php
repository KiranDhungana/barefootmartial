@php
    use App\Support\PdfHelper;
    use App\Support\AcademyOrg;
    $logoPath = PdfHelper::logoPath();
    $org = AcademyOrg::get();
@endphp
@if ($logoPath)
    <img src="{{ $logoPath }}" alt="{{ $org['legal_name'] ?? 'Academy' }}" style="max-height:56px;margin-bottom:8px;">
@endif
<h1 style="margin:0;font-size:18px;">{{ $org['legal_name'] ?? 'Barefoot Martial Arts' }}</h1>
@if (! empty($org['address']))
    <p style="margin:4px 0 0;font-size:11px;color:#444;">{{ $org['address'] }}</p>
@endif
@if (! empty($org['phone']) || ! empty($org['website']))
    <p style="margin:2px 0 0;font-size:10px;color:#555;">
        {{ $org['phone'] ?? '' }}
        @if (! empty($org['phone']) && ! empty($org['website'])) · @endif
        {{ $org['website'] ?? '' }}
    </p>
@endif
