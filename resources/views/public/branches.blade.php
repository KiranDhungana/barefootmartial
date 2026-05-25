<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Branches — Barefoot Martial Arts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Our branches</h1>
        <p><a href="/">Home</a> · <a href="{{ route('public.register') }}">Register online</a></p>
        <div class="row g-3">
            @foreach ($branches as $b)
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $b->name }}</h5>
                            <p class="text-muted small mb-2">{{ $b->address ?? 'Address on request' }}</p>
                            @if ($b->phone)
                                <p class="mb-1 small">Phone: {{ $b->phone }}</p>
                            @endif
                            @if ($b->email)
                                <p class="mb-1 small">Email: {{ $b->email }}</p>
                            @endif
                            <p class="mb-0 small"><strong>{{ $b->official_students }}</strong> official members</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>
