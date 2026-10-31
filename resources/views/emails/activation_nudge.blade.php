@php
    // The platform writing to the owner about one of their schedules: the schedule up top, so an
    // owner of several can tell which one this is about.
    $theme = \App\Utils\EmailTheme::owner($role);
    $params = $params ?? ['schedule' => $role->name, 'days' => (int) config('app.trial_days', 7)];
    $body = __($bodyKey ?? 'messages.activation_nudge_body_'.$nudgeKey, $params);
@endphp
<x-email.layout :theme="$theme" :title="__('messages.activation_nudge_subject_'.$nudgeKey, $params)" :preheader="$body">
<x-email.heading>{{ __('messages.activation_nudge_heading_'.$nudgeKey) }}</x-email.heading>

<x-email.text>{{ $greeting }}</x-email.text>
<x-email.text>{{ $body }}</x-email.text>
@if (! empty($shareUrl))
<x-email.url :href="$shareUrl" :intro="__('messages.activation_nudge_share_link')" />
@endif
@if ($importUrl)
<x-email.text>{{ __('messages.activation_nudge_import_'.$nudgeKey) }}</x-email.text>
@endif

<x-email.button :href="$ctaUrl">{{ __('messages.activation_nudge_cta_'.$nudgeKey) }}</x-email.button>
@if ($importUrl)
<x-email.button :href="$importUrl" variant="secondary">{{ __('messages.activation_nudge_import_cta_'.$nudgeKey) }}</x-email.button>
@endif

<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
</x-slot:footer>
</x-email.layout>
