<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notices — Barefoot Martial Arts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Notices</h1>
        <p><a href="/">Home</a></p>
        <ul class="list-group">
            @forelse ($notices as $n)
                <li class="list-group-item">
                    <strong>{{ $n->title }}</strong>
                    <div class="small text-muted">{{ $n->description }}</div>
                </li>
            @empty
                <li class="list-group-item text-muted">No notices.</li>
            @endforelse
        </ul>
    </div>
</body>

</html>
