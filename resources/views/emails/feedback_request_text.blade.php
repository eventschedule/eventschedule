{{ __('messages.feedback_how_was') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.feedback_rate_event') }}

{{ $event->name }}

{{ __('messages.date') }}: {{ $event->getStartDateTime($sale->event_date, true)->translatedFormat('F j, Y') }}
{{ __('messages.time') }}: {{ $event->getStartEndTime($sale->event_date) }}

{{ __('messages.feedback_submit') }}: {{ $feedbackUrl }}

@if($event->isFanContentEnabled() && $event->getGuestUrl())
@php
    $types = [];
    if ($event->isFanPhotosEnabled()) $types[] = mb_strtolower(__('messages.fan_photos_enabled'));
    if ($event->isFanVideosEnabled()) $types[] = mb_strtolower(__('messages.fan_videos_enabled'));
    if ($event->isFanCommentsEnabled()) $types[] = mb_strtolower(__('messages.fan_comments_enabled'));
@endphp
{{ __('messages.feedback_share_content') }}
{{ __('messages.feedback_share_content_description', ['types' => implode(', ', $types)]) }}
{{ __('messages.feedback_share_content_link') }}: {{ $event->getGuestUrl() }}#event-media-section
@endif

@php
    $emailSettings = $role ? $role->getEmailSettings() : [];
    $supportEmail = !empty($emailSettings['from_address']) ? $emailSettings['from_address'] : ($event->user?->email ?? config('mail.from.address'));
@endphp
{{ __('messages.event_support_contact') }}: {{ $supportEmail }}
