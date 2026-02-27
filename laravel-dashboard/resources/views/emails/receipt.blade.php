<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zora EVCE - Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 24px;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            background-color: #fafafa;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th, td {
            text-align: left;
            padding: 8px 0;
        }
        th {
            width: 45%;
            color: #555;
        }
        .footer {
            font-size: 12px;
            color: #999;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="col-lg-8 col-xl-6 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="" style="display:block; margin: 0 auto;width:40%">
            <hr class="divider" />
            <br>
        </div>
        <h1>Payment Receipt</h1>
        <p>Hello {{ $transaction->name ?? 'Customer' }},</p>
        <p>Thank you for your payment. Here are your transaction details:</p>

        <table>
            <tbody>
                <tr>
                    <th>Transaction ID</th>
                    <td>{{ $transaction->transactionId ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Order ID</th>
                    <td>{{ $transaction->midtrans_order_id ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Duration</th>
                    <td>{{ $transaction->duration.' Hour(s)' ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Amount Paid (Inc Tax)</th>
                    <td>Rp {{ number_format((float) ($transaction->total_price ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Station</th>
                    <td>{{ optional($transaction->station)->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Connector</th>
                    <td>{{ optional($transaction->connector)->connector_number ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Payment Time</th>
                    <td>
                        {{ $transaction->updated_at
                            ? $transaction->updated_at->copy()->timezone(config('app.timezone'))->format('d M Y H:i')
                            : '-' }}
                    </td>
                </tr>
            </tbody>
        </table>

        <p>If you have any questions, feel free to reply to this email.</p>
        <p>You can force stop the charging by visiting the link below:</p>
        <p><a href="{{ route('zora.stop') }}">Force Stop</a></p>
        <br>
        <p>You can see the remaining time for your charging session by visiting the link below:</p>
        <p>
            <a href="{{ route('zora.my.charging', $transaction->token) }}">
                See Remaining Time
            </a>
        </p>

        <p class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Zora EVCE') }}. All rights reserved.
        </p>
    </div>
</body>
</html>


