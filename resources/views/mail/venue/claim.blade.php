@php
    $theme = \App\Utils\EmailTheme::account();
    $unsubscribe = '<a href="'.e(route('role.show_unsubscribe', ['email' => base64_encode($venue->email)])).'" class="es-foot" style="color: #475569; text-decoration: underline;">'.e(__('messages.click_here')).'</a>';
@endphp
<x-email.layout :theme="$theme" :title="$subject" :preheader="$event->name.' · '.$event->localStartsAt(true, null, false, null, app()->getLocale())">
<x-email.heading :eyebrow="$subject" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }},</x-email.text>

<x-email.details panel>
<x-email.item :label="__('messages.date')" wide>{{ $event->localStartsAt(true, null, false, null, app()->getLocale()) }}</x-email.item>
@if ($schedulerName)
<x-email.item :label="__('messages.schedule')" wide>{{ $schedulerName }}</x-email.item>
@endif
</x-email.details>

<x-email.button :href="$event->getGuestUrl()">{{ __('messages.view_event') }}</x-email.button>

<x-email.section />
<x-email.text>{{ __('messages.claim_email_line1') }}</x-email.text>
@if ($venue->getClaimUrl())
<x-email.button :href="$venue->getClaimUrl()" variant="secondary">{{ __('messages.view_schedule') }}</x-email.button>
@else
<x-email.button :href="route('sign_up', ['email' => base64_encode($venue->email)])" variant="secondary">{{ __('messages.sign_up') }}</x-email.button>
@endif

<x-slot:footer>
<x-email.footer>{!! __('messages.claim_email_line2', ['click_here' => $unsubscribe]) !!}<br>{{ __('messages.thanks') }},<br>{{ config('app.name') }}</x-email.footer>
</x-slot:footer>
</x-email.layout>
