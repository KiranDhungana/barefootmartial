<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1 {
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h1>Invoice {{ $invoice->invoice_number }}</h1>
    <p><strong>Barefoot Martial Arts</strong></p>
    <p><strong>Bill to:</strong> {{ $invoice->student->name }}<br>
        ID: {{ $invoice->student->student_code }}<br>
        @if ($invoice->student->phone)
            Phone: {{ $invoice->student->phone }}<br>
        @endif
    </p>
    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Academy fee — {{ $invoice->invoice_number }}</td>
            <td>{{ number_format($invoice->amount, 2) }}</td>
        </tr>
    </table>
    <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
    @if ($invoice->due_date)
        <p><strong>Due date:</strong> {{ $invoice->due_date->format('M j, Y') }}</p>
    @endif
    @if ($invoice->notes)
        <p><strong>Notes:</strong> {{ $invoice->notes }}</p>
    @endif
</body>

</html>
