@php($theme = \App\Utils\EmailTheme::guest($role ?? null))
<x-email.layout :theme="$theme" :title="__('messages.subscription_confirm_subject', ['schedule' => $role->name])" :preheader="__('messages.subscription_confirm_body', ['schedule' => $role->name])">
{{-- The schedule's name leads, as it does in event_announcement.blade.php. For somebody who typed
     their address into a venue page twenty minutes ago, the name is the recognition trigger and
     the difference between a confirm and a spam report; "One more tap" on its own is not. --}}
<x-email.heading :eyebrow="__('messages.subscription_confirm_heading')" auto>{{ $role->name }}</x-email.heading>

<x-email.text>{{ __('messages.subscription_confirm_body', ['schedule' => $role->name]) }}</x-email.text>
<x-email.text>{{ __('messages.subscription_confirm_cadence') }}</x-email.text>

{{-- Said here as well as on the confirm page, because plenty of people never open that page's fine
     print. The confirm PAGE is the authority, though: this renders when the mail is built, and the
     schedule can be claimed between then and the click. --}}
@if ($role->willCreateAccountOnConfirm())
<x-email.text>{{ __('messages.subscribe_account_note') }}</x-email.text>
@endif

<x-email.button :href="$confirmUrl">{{ __('messages.subscription_confirm_button') }}</x-email.button>

{{-- The plain URL as well as the button. The text part has carried it all along; a client that
     strips or fails to render the anchor otherwise leaves the recipient with a dead email and no
     way to confirm. --}}
<x-email.url :href="$confirmUrl" />

<x-email.text variant="muted" :gap="0">{{ __('messages.subscription_confirm_ignore') }}</x-email.text>

<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
</x-slot:footer>
</x-email.layout>
