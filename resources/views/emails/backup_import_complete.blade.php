<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.backup_import_email_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.backup_import_email_subject') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.backup_import_email_intro') }}</p>

        @foreach ($report as $schedule)
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h3 style="margin-top: 0; color: #4E81FA;">{{ $schedule['name'] ?? 'Unknown' }}</h3>

            @if (! empty($schedule['error']))
            <p style="color: #dc3545;">{{ $schedule['error'] }}</p>
            @else
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr style="border-bottom: 1px solid #eee;">
                    <th style="text-align: left; padding: 8px 0; color: #666;">{{ __('messages.backup_entity') }}</th>
                    <th style="text-align: right; padding: 8px 0; color: #666;">{{ __('messages.backup_created') }}</th>
                    <th style="text-align: right; padding: 8px 0; color: #666;">{{ __('messages.backup_failed') }}</th>
                </tr>
                @foreach (['schedules', 'sub_schedules', 'events', 'tickets', 'sales', 'promo_codes', 'newsletters'] as $entity)
                @if (isset($schedule[$entity]))
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 8px 0;">{{ __('messages.backup_entity_' . $entity) }}</td>
                    <td style="text-align: right; padding: 8px 0; color: #28a745;">{{ $schedule[$entity]['success'] ?? 0 }}</td>
                    <td style="text-align: right; padding: 8px 0; color: {{ ($schedule[$entity]['failed'] ?? 0) > 0 ? '#dc3545' : '#666' }};">{{ $schedule[$entity]['failed'] ?? 0 }}</td>
                </tr>
                @endif
                @endforeach
            </table>

            @php
                $failures = [];
                foreach (['schedules', 'sub_schedules', 'events', 'tickets', 'sales', 'promo_codes', 'newsletters'] as $entity) {
                    if (! empty($schedule[$entity]['failures'])) {
                        $failures = array_merge($failures, $schedule[$entity]['failures']);
                    }
                }
            @endphp

            @if (! empty($failures))
            <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border-radius: 4px; font-size: 13px;">
                <strong>{{ __('messages.backup_failures') }}:</strong>
                <ul style="margin: 5px 0 0; padding-left: 20px;">
                    @foreach (array_slice($failures, 0, 10) as $failure)
                    <li>{{ $failure }}</li>
                    @endforeach
                    @if (count($failures) > 10)
                    <li>... {{ __('messages.backup_and_more', ['count' => count($failures) - 10]) }}</li>
                    @endif
                </ul>
            </div>
            @endif

            @if (! empty($schedule['subdomain']))
            <p style="margin-top: 15px; margin-bottom: 0;">
                <a href="{{ config('app.hosted') ? 'https://' . $schedule['subdomain'] . '.' . _base_domain() . '/schedule' : url('/' . $schedule['subdomain'] . '/schedule') }}" style="color: #4E81FA;">{{ __('messages.backup_view_schedule') }}</a>
            </p>
            @endif
            @endif
        </div>
        @endforeach
    </div>
</body>
</html>
