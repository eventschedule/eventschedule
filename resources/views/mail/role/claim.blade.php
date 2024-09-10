<x-mail::message>
# {{ __('messages.hello') }}!

{{ str_replace([':venue', ':role'], [$venue->name, $role->name], __('messages.claim_your_role'))}}

<x-mail::button :url="route('event.view_guest', ['subdomain' => $venue->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)])">
{{ __('messages.view_event') }}
</x-mail::button>

{{ __('messages.claim_email_line2') }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
