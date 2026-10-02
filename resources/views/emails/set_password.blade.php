@php($theme = \App\Utils\EmailTheme::account())
<x-email.layout :theme="$theme" :title="__('messages.set_password_subject')" :preheader="__('messages.set_password_body', ['email' => $email])">
<x-email.heading>{{ __('messages.set_password_heading') }}</x-email.heading>

{{-- bdi: the address is LTR inside RTL prose in ar/he, and without isolation it reorders
     around the surrounding punctuation. --}}
<x-email.text>{!! __('messages.set_password_body', ['email' => '<bdi dir="ltr">'.e($email).'</bdi>']) !!}</x-email.text>

<x-email.button :href="$resetUrl">{{ __('messages.set_password_button') }}</x-email.button>

{{-- The plain URL as well as the button, as subscription_confirmation.blade.php does: a
     client that strips or fails to render the anchor otherwise leaves a dead email. --}}
<x-email.url :href="$resetUrl" />

<x-email.text variant="muted">{{ __('messages.set_password_expires', ['minutes' => $expiresInMinutes]) }} {{ __('messages.set_password_ignore') }}</x-email.text>
</x-email.layout>
