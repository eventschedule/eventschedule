{{ $newsletter->subject }}
{{ str_repeat('=', strlen($newsletter->subject)) }}

@foreach ($blocks as $block)
@php $blockType = $block['type'] ?? ''; @endphp
@if ($blockType === 'heading' && !empty($block['data']['text']))

{{ $block['data']['text'] }}
{{ str_repeat('-', strlen($block['data']['text'])) }}

@elseif ($blockType === 'text' && !empty($block['data']['content']))
{{ strip_tags($block['data']['content']) }}

@elseif ($blockType === 'image' && !empty($block['data']['url']))
[{{ !empty($block['data']['alt']) ? $block['data']['alt'] : __('messages.image') }}]

@elseif ($blockType === 'events' && !empty($block['data']['resolvedEvents']))
@foreach ($block['data']['resolvedEvents'] as $event)
* {{ $event->name }}
  {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format($role->use_24_hour_time ? 'M j, Y - H:i' : 'M j, Y - g:i A') : '' }}
  {{ $role->getGuestUrl() }}

@endforeach
@elseif ($blockType === 'button' && !empty($block['data']['text']))
{{ $block['data']['text'] }}: {{ $block['data']['url'] ?? '' }}

@elseif ($blockType === 'divider')
---

@elseif ($blockType === 'social_links' && !empty($block['data']['links']))
@foreach ($block['data']['links'] as $link)
@if (!empty($link['url']) && !empty($link['platform']))
{{ ucfirst($link['platform']) }}: {{ $link['url'] }}
@endif
@endforeach

@endif
@endforeach
--
{{ !empty($style['footerText']) ? $style['footerText'] : $role->name }}
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
