@php
    use App\Support\PdfHelper;
    $logoPath = PdfHelper::logoPath();
@endphp
@if ($logoPath)
    <img src="{{ $logoPath }}" alt="Barefoot Martial Arts" style="max-height:56px;margin-bottom:8px;">
@endif
<h1 style="margin:0;font-size:18px;">Barefoot Martial Arts</h1>
