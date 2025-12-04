<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ __('messages.new_reservation') }}</title>
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
            background: #ffc107;
            color: #333;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            padding: 30px;
            background: #f9f9f9;
        }

        .button {
            background: #007cba;
            color: white;
            padding: 12px 24px;
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

        .highlight {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ __('messages.new_reservation') }}</h1>
    </div>

    ```
    <div class="content">
        <div class="highlight">
            <strong>{{ __('messages.new_reservation_received') }}</strong>
        </div>

        <table>
            <tr>
                <th>{{ __('messages.customer') }}</th>
                <td>{{ $sale->name }}<br><small>{{ $sale->email }}</small></td>
            </tr>
            <tr>
                <th>{{ __('messages.event') }}</th>
                <td>{{ $sale->event->name }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.date') }}</th>
                <td>{{ $sale->event_date }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.amount') }}</th>
                <td>{{ number_format($sale->payment_amount, 2) }} €</td>
            </tr>
            <tr>
                <th>{{ __('messages.tickets') }}</th>
                <td>
                    {{ $sale->saleTickets->sum('quantity') }}
                    {{ trans_choice('messages.ticket_singular_plural', $sale->saleTickets->sum('quantity')) }}
                </td>
            </tr>
            <tr>
                <th>{{ __('messages.status') }}</th>
                <td>{{ ucfirst($sale->status) }}</td>
            </tr>
        </table>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" class="button">
                👀 {{ __('messages.view_full_reservation') }}
            </a>
        </p>

        <p><strong>{{ __('messages.ticket_details') }}:</strong></p>
        <ul>
            @foreach($sale->saleTickets as $ticket)
            <li>{{ $ticket->ticket?->type ?? __('messages.standard') }} × {{ $ticket->quantity }}</li>
            @endforeach
        </ul>
    </div>

    <div class="footer">
        <p>{{ __('messages.auto_sent_message') }}</p>
        <p>{{ __('messages.copyright') }}</p>
    </div>
</body>

</html>