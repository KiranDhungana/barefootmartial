<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register — Barefoot Martial Arts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5" style="max-width:560px">
        <h1 class="mb-3">Online registration</h1>
        <p class="text-muted"><a href="/">Home</a> · <a href="{{ route('public.branches') }}">Branches</a></p>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form method="post" action="{{ route('public.register.store') }}" class="card shadow-sm p-4">
            @csrf
            <div class="mb-3">
                <label class="form-label">Preferred branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">Any / not sure</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Student name *</label>
                <input type="text" name="student_name" class="form-control" value="{{ old('student_name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Parent / guardian name</label>
                <input type="text" name="parent_name" class="form-control" value="{{ old('parent_name') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone *</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="3">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit request</button>
        </form>
    </div>
</body>

</html>
