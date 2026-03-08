{{ __('messages.backup_import_email_subject') }}

{{ __('messages.backup_import_email_intro') }}

@foreach ($report as $schedule)
{{ $schedule['name'] ?? 'Unknown' }}
@if (! empty($schedule['error']))
{{ $schedule['error'] }}
@else
@foreach (['schedules', 'sub_schedules', 'events', 'tickets', 'sales', 'promo_codes', 'newsletters'] as $entity)
@if (isset($schedule[$entity]))
{{ __('messages.backup_entity_' . $entity) }}: {{ $schedule[$entity]['success'] ?? 0 }} {{ __('messages.backup_created') }}, {{ $schedule[$entity]['failed'] ?? 0 }} {{ __('messages.backup_failed') }}
@endif
@endforeach
@php
    $failures = [];
    foreach (['schedules', 'sub_schedules', 'events', 'tickets', 'sales', 'promo_codes', 'newsletters'] as $entity) {
        if (! empty($schedule[$entity]['failures'])) {
            $failures = array_merge($failures, $schedule[$entity]['failures']);
        }
    }
@endphp
@if (! empty($failures))

{{ __('messages.backup_failures') }}:
@foreach ($failures as $failure)
- {{ $failure }}
@endforeach
@endif
@endif

@endforeach
