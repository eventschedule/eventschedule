{{--
    The small print under the card: why the reader got this, who to ask, and the links to manage
    or stop it. The slot is the context line(s); links is a list of [label, url] pairs, and a pair
    with no url is skipped. Goes in the layout's footer slot.
--}}
@aware(['theme' => null])
@props(['links' => []])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $links = array_values(array_filter($links, fn ($l) => filled($l[1] ?? null)));
@endphp
@if ($slot->hasActualContent())
<p class="es-foot" style="margin: 0 0 8px; font-size: 13px; line-height: 20px; color: #475569;">{{ $slot }}</p>
@endif
@if ($links)
<p class="es-foot" style="margin: 0; font-size: 13px; line-height: 20px; color: #475569;">
@foreach ($links as [$label, $url])
<a href="{{ $url }}" target="_blank" rel="noopener" class="es-foot" style="color: #475569; text-decoration: underline;">{{ $label }}</a>@if (! $loop->last)<span style="color: #94a3b8;">&nbsp;&nbsp;&middot;&nbsp;&nbsp;</span>@endif
@endforeach
</p>
@endif
