@php
    $logoFile = config('academy.logo_path', 'images/logo.png');
    $logoFull = public_path($logoFile);
@endphp
@if (is_file($logoFull))
    <img src="{{ $logoFull }}" alt="Barefoot Martial Arts" style="max-height:56px;margin-bottom:8px;">
@endif
<h1 style="margin:0;font-size:18px;">Barefoot Martial Arts</h1>
