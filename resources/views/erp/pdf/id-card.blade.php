<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>ID {{ $student->student_code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 12px;
        }

        .card {
            border: 2px solid #1e3a5f;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            max-width: 320px;
        }

        .row {
            display: table;
            width: 100%;
        }

        .photo {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }

        h2 {
            margin: 0 0 4px 0;
            font-size: 16px;
            color: #1e3a5f;
        }

        .meta {
            font-size: 11px;
            color: #444;
        }

        .qr svg {
            width: 120px;
            height: 120px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="row">
            <div style="display:table-cell; vertical-align:top; width:100px;">
                @if ($student->photo_path && file_exists(public_path('storage/'.$student->photo_path)))
                    <img class="photo"
                        src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('storage/'.$student->photo_path))) }}"
                        alt="">
                @endif
            </div>
            <div style="display:table-cell; vertical-align:top; padding-left:8px;">
                <h2>{{ $student->name }}</h2>
                <div class="meta">ID: {{ $student->student_code }}</div>
                @if ($student->branch)
                    <div class="meta">{{ $student->branch->name }}</div>
                @endif
            </div>
        </div>
        <div style="margin-top:10px; text-align:center;" class="qr">
            {!! $qrSvg !!}
        </div>
        <p style="text-align:center; font-size:9px; color:#666; margin:6px 0 0 0;">Scan to open profile URL</p>
    </div>
</body>

</html>
