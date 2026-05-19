<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Report {{ $periodLabel }}</title>
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
            padding: 8px;
        }

        th {
            text-align: left;
            background: #f3f4f6;
        }
    </style>
</head>

<body>
    <h1>Academy report — {{ $periodLabel }}</h1>
    <p>Barefoot Martial Arts</p>
    <table>
        <tr>
            <th>Metric</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Total students</td>
            <td>{{ $totalStudents }}</td>
        </tr>
        <tr>
            <td>Fees collected (month)</td>
            <td>{{ number_format($feesCollected, 2) }}</td>
        </tr>
        <tr>
            <td>Pending fees (all open)</td>
            <td>{{ number_format($pendingFees, 2) }}</td>
        </tr>
        <tr>
            <td>Open invoice count</td>
            <td>{{ $pendingCount }}</td>
        </tr>
    </table>
</body>

</html>
