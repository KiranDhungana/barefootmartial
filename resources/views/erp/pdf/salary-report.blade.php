<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Salary {{ $period }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
        }

        th {
            background: #f3f4f6;
        }
    </style>
</head>

<body>
    <h1>Trainer salary — {{ $period }}</h1>
    <p>Barefoot Martial Arts</p>
    <table>
        <thead>
            <tr>
                <th>Trainer</th>
                <th>Amount</th>
                <th>Days</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $rec)
                <tr>
                    <td>{{ $rec->trainer->name }}</td>
                    <td>{{ number_format($rec->amount, 2) }}</td>
                    <td>{{ $rec->attendance_days ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
