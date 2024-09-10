<x-mail::message>
# {{ __('messages.hello') }}!

{{ str_replace([':venue', ':role'], [$venue->name, $role->name], __('messages.claim_your_venue'))}}

<x-mail::button :url="route('event.view_guest', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)])">
{{ __('messages.view_event') }}
</x-mail::button>

{!! str_replace(':click_here', '<a href="' . $unsubscribe_url . '">' . __('messages.click_here') . '</a>', __('messages.claim_email_line2')) !!}

Thanks,<br>
{{ config('app.name') }}

</x-mail::message>