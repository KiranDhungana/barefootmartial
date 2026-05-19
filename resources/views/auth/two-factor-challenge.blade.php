<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-factor authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container" style="max-width:400px">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-4">
                <h1 class="h5 mb-3">Authenticator code</h1>
                <p class="small text-muted">Enter the 6-digit code from your app.</p>
                <form method="post" action="{{ route('two-factor.verify') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="10"
                            class="form-control rounded-3 @error('code') is-invalid @enderror" autofocus required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-3">Continue</button>
                </form>
                <a href="{{ route('login') }}" class="d-block text-center small mt-3">Back to login</a>
            </div>
        </div>
    </div>
</body>

</html>
