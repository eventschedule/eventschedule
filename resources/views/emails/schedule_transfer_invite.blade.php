@php($theme = \App\Utils\EmailTheme::owner($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.schedule_transfer_invite_subject', ['name' => $role?->name])" :preheader="__('messages.schedule_transfer_invite_intro', ['user' => $fromUser?->name, 'name' => $role?->name])">
<x-email.heading>{{ __('messages.schedule_transfer_invite_heading') }}</x-email.heading>

<x-email.text>{{ __('messages.schedule_transfer_invite_intro', ['user' => $fromUser?->name, 'name' => $role?->name]) }}</x-email.text>

<x-email.details panel>
<x-email.item :label="__('messages.schedule')" :caption="$role?->getGuestUrl(true)" wide>{{ $role?->name }}</x-email.item>
</x-email.details>

<x-email.text>{{ __('messages.schedule_transfer_invite_what_moves') }}</x-email.text>

<x-email.button :href="$acceptUrl">{{ __('messages.schedule_transfer_review') }}</x-email.button>

<x-email.text variant="small">{{ __('messages.schedule_transfer_invite_sign_in', ['email' => $transfer->to_email]) }}</x-email.text>
<x-email.text variant="small">{{ __('messages.schedule_transfer_invite_expires', ['date' => $transfer->expires_at?->translatedFormat('M j, Y')]) }}</x-email.text>
<x-email.text variant="small">{{ __('messages.schedule_transfer_invite_ignore') }}</x-email.text>
</x-email.layout>
