{{ __('messages.feedback_how_was') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.feedback_rate_event') }}

{{ $event->name }}

{{ __('messages.date') }}: {{ $event->getStartDateTime($sale->event_date, true)->translatedFormat('F j, Y') }}
{{ __('messages.time') }}: {{ $event->getStartEndTime($sale->event_date) }}

{{ __('messages.feedback_submit') }}: {{ $feedbackUrl }}

@php
    $emailSettings = $role ? $role->getEmailSettings() : [];
    $supportEmail = !empty($emailSettings['from_address']) ? $emailSettings['from_address'] : ($event->user?->email ?? config('mail.from.address'));
@endphp
{{ __('messages.event_support_contact') }}: {{ $supportEmail }}
