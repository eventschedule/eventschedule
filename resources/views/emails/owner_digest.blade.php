@php
    // Account voice: one digest spans every schedule the owner runs, so no single schedule leads.
    $theme = \App\Utils\EmailTheme::account();
@endphp
<x-email.layout :theme="$theme" :title="__('messages.owner_digest_heading')" :preheader="__('messages.owner_digest_intro')">
<x-email.heading>{{ __('messages.owner_digest_heading') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $user->firstName() }},</x-email.text>
<x-email.text>{{ __('messages.owner_digest_intro') }}</x-email.text>

@foreach ($sections as $section)
<x-email.section />
<x-email.text variant="lead"><strong><x-email.link :href="$section['url']">{{ $section['name'] }}</x-email.link></strong></x-email.text>
<x-email.details>
@foreach (['views', 'followers', 'subscribers', 'tickets', 'rsvps'] as $metric)
@if ($section[$metric] > 0 || $metric === 'views')
<x-email.item :label="__('messages.owner_digest_'.$metric)">{{ number_format($section[$metric]) }}</x-email.item>
@endif
@endforeach
</x-email.details>
@if (! empty($section['upcoming']))
<x-email.text variant="small" :gap="8"><strong>{{ __('messages.owner_digest_coming_up') }}</strong></x-email.text>
<x-email.list :items="array_map(fn ($occurrence) => $occurrence['date'].' - '.$occurrence['name'], $section['upcoming'])" />
@endif
@endforeach

@if ($more > 0)
<x-email.section />
<x-email.text variant="small">{{ trans_choice('messages.owner_digest_more', $more, ['count' => $more]) }}</x-email.text>
@endif

<x-email.button :href="$dashboardUrl">{{ __('messages.owner_digest_cta') }}</x-email.button>

<x-slot:footer>
{{-- One sentence: owner_digest_why ends "... or" and the link finishes it. --}}
<x-email.footer>{{ __('messages.owner_digest_why') }} <x-email.link :href="$unsubscribeUrl" muted>{{ __('messages.unsubscribe') }}</x-email.link></x-email.footer>
</x-slot:footer>
</x-email.layout>
