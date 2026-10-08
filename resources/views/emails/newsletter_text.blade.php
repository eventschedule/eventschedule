{{-- The plain-text part. Every block that says something in the HTML part says it here: the
     quote, the poll, the sponsors and the video were silently dropped, and an event had no venue
     or price. mb_strwidth, not strlen: a Hebrew subject was underlined twice its length. Unescaped on
     purpose: this part is never read as HTML, and escaped it said "What&#039;s on". --}}
@php
    $line = fn (string $text, string $char) => str_repeat($char, max(3, mb_strwidth($text)));
    $href = fn ($url) => \App\Utils\UrlUtils::safeActionHref($url);
@endphp
{!! $newsletter->subject !!}
{!! $line($newsletter->subject, '=') !!}

@foreach ($blocks as $block)
@php
    $type = $block['type'] ?? '';
    $data = $block['data'] ?? [];
@endphp
@if ($type === 'heading' && filled($data['text'] ?? null))

{!! $data['text'] !!}
{!! $line($data['text'], '-') !!}

@elseif ($type === 'text' && filled($data['content'] ?? null))
{!! strip_tags($data['content']) !!}

@elseif ($type === 'image')
@foreach (isset($data['url']) ? [['url' => $data['url'], 'alt' => $data['alt'] ?? '']] : ($data['images'] ?? []) as $image)
@if (filled($image['url'] ?? null))
[{!! filled($image['alt'] ?? null) ? $image['alt'] : __('messages.image') !!}]{!! filled($image['caption'] ?? null) ? ' '.$image['caption'] : '' !!}{!! $href($image['link'] ?? null) ? ' '.$href($image['link']) : '' !!}
@endif
@endforeach

@elseif ($type === 'events' && collect($data['resolvedEvents'] ?? [])->isNotEmpty())
@foreach ($data['resolvedEvents'] as $e)
* {!! $e['name'] !!}
  {!! implode(' | ', array_filter([$e['date'], $e['time'], $e['venue'], $e['repeat'], $e['price']])) !!}
  {!! $e['url'] !!}

@endforeach
@if (filled($data['scheduleUrl'] ?? null))
{!! __('messages.announcement_view_schedule') !!}: {!! $data['scheduleUrl'] !!}

@endif
@elseif ($type === 'button' && filled($data['text'] ?? null))
{!! $data['text'] !!}{!! $href($data['url'] ?? null) ? ': '.$href($data['url']) : '' !!}

@elseif ($type === 'quote' && filled($data['text'] ?? null))
"{!! $data['text'] !!}"
@if (filled($data['author'] ?? null))
  {!! implode(', ', array_filter([$data['author'], $data['title'] ?? null])) !!}
@endif

@elseif ($type === 'offer' && (filled($data['title'] ?? null) || filled($data['salePrice'] ?? null)))
{!! implode("\n", array_filter([$data['title'] ?? null, $data['description'] ?? null])) !!}
@if (filled($data['originalPrice'] ?? null) && filled($data['salePrice'] ?? null))
{!! $data['originalPrice'] !!} -> {!! $data['salePrice'] !!}
@elseif (filled($data['salePrice'] ?? null))
{!! $data['salePrice'] !!}
@endif
@if (filled($data['couponCode'] ?? null))
{!! __('messages.coupon_code_label') !!}: {!! $data['couponCode'] !!}
@endif
@if (filled($data['buttonText'] ?? null))
{!! $data['buttonText'] !!}{!! $href($data['buttonUrl'] ?? null) ? ': '.$href($data['buttonUrl']) : '' !!}
@endif

@elseif ($type === 'video' && filled($data['videoId'] ?? null))
{!! __('messages.play_video') !!}: {!! $data['url'] !!}

@elseif ($type === 'poll' && ! empty($data['resolvedPoll']))
{!! $data['resolvedPoll']['question'] !!}
@foreach ($data['resolvedPoll']['options'] as $option)
  ( ) {!! $option !!}
@endforeach
{!! __('messages.vote_now') !!}: {!! $data['resolvedPoll']['eventUrl'] !!}

@elseif ($type === 'sponsors' && ! empty($data['resolvedSponsors']))
{!! filled($data['sponsorTitle'] ?? null) ? $data['sponsorTitle'].': ' : '' !!}{!! collect($data['resolvedSponsors'])->pluck('display_name')->filter()->implode(', ') !!}

@elseif ($type === 'divider')
---

@elseif ($type === 'social_links')
@foreach ($data['links'] ?? [] as $link)
@if (\App\Utils\UrlUtils::safeHref($link['url'] ?? null) && filled($link['platform'] ?? null))
{!! ucfirst($link['platform']) !!}: {!! \App\Utils\UrlUtils::safeHref($link['url']) !!}
@endif
@endforeach

@endif
@endforeach
--
{!! filled($style['footerText'] ?? null) ? $style['footerText'] : ($role?->name ?? config('app.name')) !!}
@if ($role)
{!! __('messages.newsletter_why_receiving', ['schedule' => $role->name]) !!}
@endif
@if (! empty($manageUrl))
{!! __('messages.subscription_manage_account') !!}: {!! $manageUrl !!}
@endif
{!! __('messages.unsubscribe') !!}: {!! $unsubscribeUrl !!}
