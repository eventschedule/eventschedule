{{ $newsletter->subject }}
{{ str_repeat('=', strlen($newsletter->subject)) }}

{{ !empty($style['footerText']) ? $style['footerText'] : $role->name }}

@foreach ($blocks as $block)
@php $blockType = $block['type'] ?? ''; @endphp
@if ($blockType === 'heading' && !empty($block['data']['text']))
{{ $block['data']['text'] }}
{{ str_repeat('-', strlen($block['data']['text'])) }}

@elseif ($blockType === 'text' && !empty($block['data']['content']))
{{ $block['data']['content'] }}

@elseif ($blockType === 'events' && !empty($block['data']['resolvedEvents']))
@foreach ($block['data']['resolvedEvents'] as $event)
{{ $event->name }}
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
---
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
