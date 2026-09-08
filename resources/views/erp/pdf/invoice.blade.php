@php
    use App\Support\PdfHelper;

    $org = config('academy.org');
    $student = $invoice->student;
    $logoPath = PdfHelper::logoPath();
    $qrPath = PdfHelper::publicImagePath($org['qr_path'] ?? 'images/qr_final.jpeg');
    $total = (float) $invoice->totalAmount();
    $amountWords = PdfHelper::amountInWords($total);
    $paidMethod = optional($invoice->payments->sortByDesc('id')->first())->payment_method;
    $methods = [
        'cash' => 'Cash',
        'esewa' => 'eSewa',
        'bank' => 'Fonepay / Bank Transfer',
        'card' => 'Card',
        'other' => 'Other',
    ];
    $rows = $invoice->lineItems;
    $padRows = max(0, 5 - $rows->count());
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 18px 20px 22px 20px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .red { color: #d82027; }
        .bg-red { background: #d82027; color: #fff; }
        .muted { color: #555; }
        .bold { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }

        table { border-collapse: collapse; }
        .w-100 { width: 100%; }

        .header td { vertical-align: top; }
        .logo { width: 78px; height: auto; }
        .brand-title {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1px;
            line-height: 1;
            margin: 2px 0 0 0;
            color: #111;
        }
        .brand-sub {
            font-size: 13px;
            font-weight: bold;
            color: #d82027;
            letter-spacing: 0.5px;
            margin: 2px 0 4px 0;
        }
        .brand-tag {
            font-size: 8px;
            letter-spacing: 0.6px;
            color: #222;
            text-transform: uppercase;
        }
        .invoice-badge {
            background: #111;
            color: #fff;
            padding: 8px 14px 8px 18px;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            text-align: right;
            border-left: 6px solid #d82027;
        }
        .contact-line {
            font-size: 9px;
            color: #222;
            line-height: 1.55;
            margin-top: 6px;
        }
        .contact-line .label { color: #d82027; font-weight: bold; }

        .meta-wrap { margin-top: 14px; }
        .invoice-to-title {
            color: #d82027;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 6px;
        }
        .invoice-to-row {
            margin-bottom: 5px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            font-size: 10px;
        }
        .invoice-to-row .k { color: #444; display: inline-block; width: 58px; }
        .meta-box {
            border: 1px solid #cfcfcf;
            background: #f7f7f7;
            padding: 8px 10px;
        }
        .meta-box table td {
            padding: 3px 0;
            font-size: 10px;
        }
        .meta-box .k { color: #333; width: 88px; }

        .items { margin-top: 14px; width: 100%; }
        .items th {
            background: #d82027;
            color: #fff;
            font-size: 9px;
            letter-spacing: 0.4px;
            padding: 7px 6px;
            text-align: left;
            border: 1px solid #d82027;
        }
        .items td {
            border: 1px solid #d0d0d0;
            padding: 7px 6px;
            vertical-align: top;
            height: 22px;
        }
        .items .sn { width: 28px; text-align: center; }
        .items .qty { width: 42px; text-align: center; }
        .items .rate, .items .amt { width: 90px; text-align: right; }
        .totals td { border: 1px solid #d0d0d0; padding: 6px 8px; }
        .totals .label { text-align: right; font-weight: bold; width: 70%; background: #fafafa; }
        .totals .total-row td {
            background: #d82027;
            color: #fff;
            font-weight: bold;
            border-color: #d82027;
        }
        .words {
            margin-top: 8px;
            font-size: 9.5px;
        }
        .words .k { color: #d82027; font-weight: bold; }

        .bottom { margin-top: 14px; }
        .pay-title { color: #d82027; font-weight: bold; font-size: 11px; margin-bottom: 6px; }
        .check {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #333;
            margin-right: 4px;
            text-align: center;
            font-size: 8px;
            line-height: 10px;
        }
        .check.on { background: #d82027; color: #fff; border-color: #d82027; }
        .note-title { color: #d82027; font-weight: bold; margin-top: 10px; margin-bottom: 3px; }
        .note-list { margin: 0; padding-left: 14px; color: #444; font-size: 8.5px; line-height: 1.45; }

        .qr-box { text-align: center; }
        .qr-img { width: 110px; height: 110px; }
        .qr-name { font-size: 8px; font-weight: bold; color: #0b3a66; margin-top: 4px; text-transform: uppercase; }
        .qr-meta { font-size: 7.5px; color: #666; line-height: 1.35; }

        .sign-wrap { text-align: center; margin-top: 28px; }
        .sign-line {
            border-top: 1px solid #333;
            width: 150px;
            margin: 0 auto 4px auto;
            padding-top: 2px;
        }
        .sign-name { font-size: 10px; font-style: italic; }
        .sign-label { font-size: 8px; color: #555; }

        .footer {
            margin-top: 16px;
            background: #111;
            color: #fff;
            padding: 8px 10px;
            font-size: 8px;
        }
        .footer td { color: #fff; vertical-align: middle; }
        .footer .tag { font-style: italic; letter-spacing: 0.8px; text-transform: uppercase; }
    </style>
</head>
<body>
    <table class="w-100 header">
        <tr>
            <td style="width: 90px;">
                @if ($logoPath)
                    <img class="logo" src="{{ $logoPath }}" alt="Logo">
                @endif
            </td>
            <td>
                <div class="brand-title">{{ $org['brand_line1'] ?? 'BAREFOOT' }}</div>
                <div class="brand-sub">{{ $org['brand_line2'] ?? 'MARTIAL ARTS ACADEMY' }}</div>
                <div class="brand-tag">{{ $org['tagline'] ?? 'DISCIPLINE • FOCUS • STRENGTH • RESPECT' }}</div>
            </td>
            <td style="width: 210px;" class="text-right">
                <div class="invoice-badge">INVOICE</div>
                <div class="contact-line text-right">
                    {{ $org['address'] ?? '' }}<br>
                    {{ $org['phone'] ?? '' }}<br>
                    {{ $org['website'] ?? '' }}<br>
                    PAN No. {{ $org['pan'] ?? '—' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="w-100 meta-wrap">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 12px;">
                <div class="invoice-to-title">Invoice To:</div>
                <div class="invoice-to-row"><span class="k">Name</span> {{ $student->name }}</div>
                <div class="invoice-to-row"><span class="k">Code</span> {{ $student->student_code }}</div>
                <div class="invoice-to-row"><span class="k">Address</span> {{ $student->address ?: ($student->branch->name ?? '—') }}</div>
                <div class="invoice-to-row"><span class="k">Contact</span> {{ $student->phone ?: ($student->parent_contact ?: '—') }}</div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <div class="meta-box">
                    <table class="w-100">
                        <tr>
                            <td class="k">Invoice No.</td>
                            <td class="bold">: {{ $invoice->invoice_number }}</td>
                        </tr>
                        <tr>
                            <td class="k">Date (AD)</td>
                            <td>: {{ optional($invoice->created_at)->format('Y-m-d') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="k">Due Date</td>
                            <td>: {{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="k">Status</td>
                            <td>: {{ $invoice->statusLabel() }}</td>
                        </tr>
                        @if ($invoice->billing_period)
                            <tr>
                                <td class="k">Period</td>
                                <td>: {{ str_replace('monthly:', '', $invoice->billing_period) }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="sn">SN</th>
                <th>DESCRIPTION</th>
                <th class="qty">QTY</th>
                <th class="rate">RATE (NPR)</th>
                <th class="amt">AMOUNT (NPR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $line)
                <tr>
                    <td class="sn">{{ $i + 1 }}</td>
                    <td>
                        {{ $line->description }}
                        @if ($line->size)
                            <span class="muted">({{ $line->size }})</span>
                        @endif
                    </td>
                    <td class="qty">{{ $line->quantity }}</td>
                    <td class="rate">{{ number_format((float) $line->unit_price, 2) }}</td>
                    <td class="amt">{{ number_format((float) $line->line_total, 2) }}</td>
                </tr>
            @endforeach
            @for ($i = 0; $i < $padRows; $i++)
                <tr>
                    <td class="sn">&nbsp;</td>
                    <td></td>
                    <td class="qty"></td>
                    <td class="rate"></td>
                    <td class="amt"></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="w-100" style="margin-top:0;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-top: 8px;">
                <div class="words">
                    <span class="k">Amount in Words:</span>
                    {{ $amountWords }}
                </div>
            </td>
            <td style="width: 45%;">
                <table class="w-100 totals">
                    <tr>
                        <td class="label">SUBTOTAL</td>
                        <td class="text-right">Rs. {{ number_format((float) $invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">DISCOUNT</td>
                        <td class="text-right">Rs. {{ number_format((float) $invoice->discount_amount, 2) }}</td>
                    </tr>
                    @if ($invoice->late_fee_amount > 0)
                        <tr>
                            <td class="label">LATE FEE</td>
                            <td class="text-right">Rs. {{ number_format((float) $invoice->late_fee_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td class="label" style="background:#d82027;color:#fff;">TOTAL AMOUNT</td>
                        <td class="text-right">Rs. {{ number_format($total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">PAID</td>
                        <td class="text-right">Rs. {{ number_format((float) $invoice->amount_paid, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">BALANCE DUE</td>
                        <td class="text-right bold">Rs. {{ number_format((float) $invoice->balanceDue(), 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="w-100 bottom">
        <tr>
            <td style="width: 42%; vertical-align: top; padding-right: 10px;">
                <div class="pay-title">Payment Method</div>
                @foreach ($methods as $key => $label)
                    <div style="margin-bottom: 4px;">
                        <span class="check {{ $paidMethod === $key || ($key === 'bank' && in_array($paidMethod, ['bank', 'fonepay'], true)) ? 'on' : '' }}">
                            {{ $paidMethod === $key || ($key === 'bank' && in_array($paidMethod, ['bank', 'fonepay'], true)) ? '✓' : '' }}
                        </span>
                        {{ $label }}
                    </div>
                @endforeach
                <div class="note-title">Note:</div>
                <ul class="note-list">
                    <li>Please pay by the due date to avoid late fees.</li>
                    <li>For payment queries, contact the academy office.</li>
                </ul>
            </td>
            <td style="width: 30%; vertical-align: top;" class="qr-box">
                @if ($qrPath)
                    <div class="muted" style="font-size:8px;margin-bottom:3px;">We Accept · Fonepay</div>
                    <img class="qr-img" src="{{ $qrPath }}" alt="Fonepay QR">
                    <div class="qr-name">{{ $org['legal_name'] ?? 'BAREFOOT MARTIAL ARTS ACADEMY' }}</div>
                    <div class="qr-meta">
                        Terminal: {{ $org['fonepay_terminal'] ?? '—' }}<br>
                        Address: {{ $org['fonepay_address'] ?? 'PARASI' }}
                    </div>
                @endif
            </td>
            <td style="width: 28%; vertical-align: bottom;">
                <div class="sign-wrap">
                    <div class="sign-name">{{ $org['authorized_signatory'] ?? '' }}</div>
                    <div class="sign-line"></div>
                    <div class="sign-label">Authorized Signature</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="w-100 footer">
        <tr>
            <td style="width: 30%;">{{ $org['website'] ?? '' }}</td>
            <td class="text-center tag" style="width: 40%;">{{ $org['footer_tagline'] ?? 'TRAIN TODAY, LEAD TOMORROW.' }}</td>
            <td class="text-right" style="width: 30%;">{{ $org['phone'] ?? '' }}</td>
        </tr>
    </table>
</body>
</html>
