<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Branch report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>

<body>
    <h1>Branch report — {{ $branch->name ?? 'Branch' }}</h1>
    <p>Barefoot Martial Arts</p>

    <h2>Daily ({{ $daily['date'] }})</h2>
    <table>
        <tr><th>Metric</th><th>Amount</th></tr>
        <tr><td>Fees collected</td><td>{{ number_format($daily['fees_collected'], 2) }}</td></tr>
        <tr><td>Uniform / gear</td><td>{{ number_format($daily['uniform_sales'], 2) }}</td></tr>
        <tr><td>Expenses</td><td>{{ number_format($daily['expenses'], 2) }}</td></tr>
        <tr><td>Net</td><td>{{ number_format($daily['net'], 2) }}</td></tr>
    </table>

    <h2>Monthly ({{ $monthly['year'] }}-{{ str_pad((string) $monthly['month'], 2, '0', STR_PAD_LEFT) }})</h2>
    <table>
        <tr><th>Metric</th><th>Amount</th></tr>
        <tr><td>Revenue</td><td>{{ number_format($monthly['revenue'], 2) }}</td></tr>
        <tr><td>Expenses</td><td>{{ number_format($monthly['expenses'], 2) }}</td></tr>
        <tr><td>Profit</td><td>{{ number_format($monthly['profit'], 2) }}</td></tr>
        <tr><td>Pending fees</td><td>{{ number_format($monthly['pending_fees'], 2) }}</td></tr>
        <tr><td>New students</td><td>{{ $monthly['student_growth'] }}</td></tr>
    </table>
</body>

</html>
