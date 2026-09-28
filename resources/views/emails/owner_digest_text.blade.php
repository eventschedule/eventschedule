{{ __('messages.owner_digest_heading') }}

{{ __('messages.hello') }} {{ $user->firstName() }},

{{ __('messages.owner_digest_intro') }}

@foreach ($sections as $section)
{{ $section['name'] }} - {{ $section['url'] }}
@foreach (['views', 'followers', 'subscribers', 'tickets', 'rsvps'] as $metric)
@if ($section[$metric] > 0 || $metric === 'views')
{{ __('messages.owner_digest_'.$metric) }}: {{ number_format($section[$metric]) }}
@endif
@endforeach
@if (! empty($section['upcoming']))
{{ __('messages.owner_digest_coming_up') }}
@foreach ($section['upcoming'] as $occurrence)
- {{ $occurrence['date'] }} - {{ $occurrence['name'] }}
@endforeach
@endif

@endforeach
@if ($more > 0)
{{ trans_choice('messages.owner_digest_more', $more, ['count' => $more]) }}

@endif
{{ __('messages.owner_digest_cta') }}: {{ $dashboardUrl }}

{{ __('messages.owner_digest_why') }}
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
