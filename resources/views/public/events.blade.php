<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Events — Barefoot Martial Arts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Events & tournaments</h1>
        <p><a href="/">Home</a></p>
        @forelse ($events as $e)
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5>{{ $e->title }}</h5>
                    @if ($e->event_date)
                        <p class="small text-muted mb-1">Date: {{ $e->event_date->format('M j, Y') }}</p>
                    @endif
                    @if ($e->branch)
                        <p class="small mb-1">Branch: {{ $e->branch->name }}</p>
                    @endif
                    @if ($e->fee_amount > 0)
                        <p class="small mb-1">Fee: {{ number_format($e->fee_amount, 2) }}</p>
                    @endif
                    <p class="mb-0">{{ $e->description }}</p>
                </div>
            </div>
        @empty
            <p class="text-muted">No published events at the moment.</p>
        @endforelse
    </div>
</body>

</html>
