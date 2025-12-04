<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('messages.booking_confirmation') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #007cba;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            padding: 30px;
            background: #f9f9f9;
        }

        .button {
            background: #28a745;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ __('messages.booking_confirmed') }}</h1>
    </div>

    <div class="content">
        <p>{{ __('messages.hello_name', ['name' => $sale->name]) }},</p>

        <p>{{ __('messages.booking_success_message', ['event' => $sale->event->name]) }}</p>

        <table>
            <tr>
                <th>{{ __('messages.event') }}</th>
                <td>{{ $sale->event->name }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.date') }}</th>
                <td>{{ $sale->event_date }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.total_amount') }}</th>
                <td>{{ number_format($sale->payment_amount, 2) }} €</td>
            </tr>
            <tr>
                <th>{{ __('messages.number_of_tickets') }}</th>
                <td>{{ $sale->saleTickets->sum('quantity') }}</td>
            </tr>
        </table>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" class="button">
                👉 {{ __('messages.view_tickets_qr') }}
            </a>
        </p>

        <p>{{ __('messages.thank_you_message') }}</p>
    </div>

    <div class="footer">
        <p>{{ __('messages.footer_contact') }}</p>
    </div>
</body>

</html>