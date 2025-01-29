<x-mail::message>
# {{ __('messages.hello') }}!

{{ $subject }}

<x-mail::button :url="$event->getGuestUrl()">
{{ __('messages.view_event') }}
</x-mail::button>

{{ __('messages.claim_email_line1') }}

<x-mail::button :url="route('sign_up', ['email' => base64_encode($venue->email)])">
{{ __('messages.sign_up') }}
</x-mail::button>

{!! __('messages.claim_email_line2', ['click_here' => '<a href="' . route('role.show_unsubscribe', ['email' => base64_encode($venue->email)]) . '">' . __('messages.click_here') . '</a>']) !!}

Thanks,<br>
{{ config('app.name') }}

</x-mail::message>