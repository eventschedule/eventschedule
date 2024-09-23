<x-mail::message>
# {{ __('messages.hello') }}!

{{ $subject }}

<x-mail::button :url="route('event.view_guest', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)])">
{{ __('messages.view_event') }}
</x-mail::button>

{{ __('messages.claim_email_line1') }}

<x-mail::button :url="route('sign_up', ['email' => $venue->email])">
{{ __('messages.sign_up') }}
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}

</x-mail::message>