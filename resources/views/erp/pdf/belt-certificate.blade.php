<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $promotion->certificate_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; text-align: center; padding: 40px; }
        h1 { font-size: 28px; margin-bottom: 8px; }
        h2 { font-size: 18px; color: #444; font-weight: normal; }
        .belt { font-size: 22px; margin: 24px 0; }
    </style>
</head>

<body>
    @include('partials.pdf-header')
    <h2 style="font-weight:normal;color:#444;">Belt promotion certificate</h2>
    <p class="belt">This certifies that</p>
    <p style="font-size:24px;font-weight:bold">{{ $student->name }}</p>
    <p>Member ID: {{ $student->student_code }}</p>
    <p class="belt">
        @if ($promotion->from_belt)
            {{ $promotion->from_belt }} →
        @endif
        <strong>{{ $promotion->to_belt }}</strong>
    </p>
    <p>Date: {{ $promotion->promoted_at->format('F j, Y') }}</p>
    <p>Certificate: {{ $promotion->certificate_number }}</p>
    @if ($promotion->exam_passed)
        <p><em>Exam passed</em></p>
    @endif
</body>

</html>
