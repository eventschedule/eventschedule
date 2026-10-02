@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $emailSettings = $role ? $role->getEmailSettings() : [];
    $supportEmail = ! empty($emailSettings['from_address']) ? $emailSettings['from_address'] : ($event->user?->email ?? config('mail.from.address'));
@endphp
<x-email.layout :theme="$theme" :title="__('messages.feedback_request_subject')" :preheader="__('messages.feedback_rate_event')">
<x-email.heading :eyebrow="__('messages.feedback_how_was')" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $sale->name }},</x-email.text>
<x-email.text>{{ __('messages.feedback_rate_event') }}</x-email.text>

{{-- The event has happened: nothing to add to a calendar or travel to. --}}
<x-email.event :event="$event" :date="$sale->event_date" :role="$role ?? null" :calendar="false" :map="false" />

@if (empty($sale->id))
<x-email.button :note="__('messages.test_email_note')">{{ __('messages.feedback_submit') }}</x-email.button>
@else
<x-email.button :href="$feedbackUrl">{{ __('messages.feedback_submit') }}</x-email.button>
@endif

@if ($event->isFanContentEnabled() && $event->getGuestUrl())
    @php
        $types = [];
        if ($event->isFanPhotosEnabled()) $types[] = mb_strtolower(__('messages.fan_photos_enabled'));
        if ($event->isFanVideosEnabled()) $types[] = mb_strtolower(__('messages.fan_videos_enabled'));
        if ($event->isFanCommentsEnabled()) $types[] = mb_strtolower(__('messages.fan_comments_enabled'));
    @endphp
<x-email.callout tone="info" :title="__('messages.feedback_share_content')">
{{ __('messages.feedback_share_content_description', ['types' => implode(', ', $types)]) }}
<br><x-email.link :href="$event->getGuestUrl($role?->subdomain, null, true).'#gp-fan-content'">{{ __('messages.feedback_share_content_link') }}</x-email.link>
</x-email.callout>
@endif

<x-slot:footer>
<x-email.footer>{{ __('messages.event_support_contact') }}: <x-email.link :href="'mailto:'.$supportEmail" muted>{{ $supportEmail }}</x-email.link></x-email.footer>
</x-slot:footer>
</x-email.layout>
