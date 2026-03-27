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
  @php
      $tz = $role->timezone ?? 'UTC';
      $s = $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->setTimezone($tz) : null;
      $timeFormat = ($role?->use_24_hour_time ?? false) ? 'H:i' : 'g:i A';
      if ($s && $event->is_multi_day) {
          $e = $s->copy()->addHours($event->duration);
          if ($s->year !== $e->year) {
              $dateStr = $s->format('M j, Y') . ' - ' . $e->format('M j, Y');
          } elseif ($s->month !== $e->month) {
              $dateStr = $s->format('M j') . ' - ' . $e->format('M j, Y');
          } else {
              $dateStr = $s->format('M j') . ' - ' . $e->format('j, Y');
          }
          $dateStr .= ' - ' . $s->format($timeFormat);
      } elseif ($s) {
          $dateStr = $s->format('M j, Y - ' . $timeFormat);
      } else {
          $dateStr = '';
      }
  @endphp
{{ $dateStr }}
  {{ $event->getGuestUrl() }}

@endforeach
@elseif ($blockType === 'button' && !empty($block['data']['text']))
{{ $block['data']['text'] }}: {{ $block['data']['url'] ?? '' }}

@elseif ($blockType === 'offer')
@if (!empty($block['data']['title']))
{{ $block['data']['title'] }}
@endif
@if (!empty($block['data']['description']))
{{ $block['data']['description'] }}
@endif
@if (!empty($block['data']['originalPrice']) && !empty($block['data']['salePrice']))
{{ $block['data']['originalPrice'] }} -> {{ $block['data']['salePrice'] }}
@elseif (!empty($block['data']['salePrice']))
{{ $block['data']['salePrice'] }}
@endif
@if (!empty($block['data']['couponCode']))
{{ __('messages.coupon_code_label') }}: {{ $block['data']['couponCode'] }}
@endif
@if (!empty($block['data']['buttonText']))
{{ $block['data']['buttonText'] }}: {{ $block['data']['buttonUrl'] ?? '' }}
@endif

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
{{ !empty($style['footerText']) ? $style['footerText'] : ($role?->name ?? config('app.name')) }}
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
