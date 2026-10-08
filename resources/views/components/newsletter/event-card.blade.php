{{--
    One event as a card (Modern, Bold, Classic). The picture is an object INSIDE the card, at its
    own shape, never a strip cropped to the card's height: mail cannot crop, and a flyer that
    stopped halfway down a taller card looked broken. Every picture starts on the card's leading
    edge in one column, so the names line up; an event with no picture gets its date as a tile in
    the same place. Classic's picture is at the end, and Classic has no tiles.

    e is one row from NewsletterService::eventRow().
--}}
@props(['nl', 'e', 'last' => false])
@php
    $classic = $nl->design === 'classic';
    $bold = $nl->design === 'bold';
    $ratio = $e['ratio'] ?: 1.33;
    [$tw, $th] = [112, (int) round(112 / $ratio)];
    if ($th > 150) {
        [$tw, $th] = [(int) round(150 * $ratio), 150];
    }
    $tile = ! $e['image'] && $e['day'] && ! $classic;
    // With the date on a tile, the line above the name carries the weekday and the time.
    $when = $nl->when($tile && ! $e['multiDay'] ? $e['weekday'] : $e['date'], $e['time']);
    $meta = $nl->eventMeta($e);
    $title = match (true) {
        $classic => $nl->type(21, 27, $nl->ink, 400),
        $bold => $nl->type(20, 26, $nl->ink, 800),
        default => $nl->type(18, 24, $nl->ink, 700),
    };
@endphp
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $last ? 8 : 12 }}px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $nl->panel }}" style="background-color: {{ $nl->panel }}; border: 1px solid {{ $nl->rule }}; border-radius: {{ $nl->cardRadius }}px;">
<tr>
@if (! $classic && $e['image'])
<!--[if !mso]><!-->
<td class="nl-thumb" width="112" valign="top" style="width: 112px; vertical-align: top; text-align: {{ $nl->start }}; padding: 16px 0; padding-{{ $nl->start }}: 16px;">
<a href="{{ $e['url'] }}" target="_blank" rel="noopener"><img src="{{ $e['image'] }}" width="{{ $tw }}" height="{{ $th }}" alt="" style="display: block; width: {{ $tw }}px; max-width: 100%; height: auto; border: 0; border-radius: {{ max(0, $nl->cardRadius - 6) }}px;"></a>
</td>
<!--<![endif]-->
@elseif ($tile)
<td class="nl-thumb" width="112" valign="top" style="width: 112px; vertical-align: top; padding: 16px 0; padding-{{ $nl->start }}: 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $nl->accentTint }}" style="background-color: {{ $nl->accentTint }}; border-radius: {{ max(0, $nl->cardRadius - 6) }}px;">
<tr>
<td align="center" style="padding: 14px 0 0; text-align: center; {{ $nl->labelType(null, 12) }}">{{ $e['month'] }}</td>
</tr>
<tr>
<td align="center" style="padding: 0 0 14px; text-align: center; {{ $nl->numType(34, 38) }}">{{ $e['day'] }}</td>
</tr>
</table>
</td>
@endif
<td valign="top" style="vertical-align: top; padding: {{ $classic ? '18px 20px' : '16px 18px' }}; text-align: {{ $nl->start }};">
@if ($when !== '')
<p style="margin: 0 0 5px; {{ $nl->labelType() }}">{!! $when !!}</p>
@endif
<p dir="auto" style="margin: 0;"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" style="{{ $title }} text-decoration: none;">{{ $e['name'] }}</a></p>
@if ($meta !== '')
<p style="margin: 5px 0 0; {{ $nl->smallType($nl->ink3) }}">{!! $meta !!}</p>
@endif
@if ($bold)
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 12px 0 0;">
{{-- Bold fills the button that sells and outlines the one that only opens the page, so seven
     cards are not seven identical buttons. --}}
<x-newsletter.button :nl="$nl" :label="$e['cta']" :href="$e['url']" :align="$nl->start" :variant="$e['buy'] ? 'solid' : 'outline'" size="sm" />
</td>
</tr>
</table>
@else
{{-- Padded, so it is a target a thumb can hit, not a 13px word. --}}
<p style="margin: 2px 0 0;"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" style="display: inline-block; padding: 8px 0 2px; {{ $nl->type(14, 20, $nl->accentInk, 700) }} text-decoration: {{ $classic ? 'underline' : 'none' }};">{{ $e['cta'] }}&nbsp;{!! $nl->arrow !!}</a></p>
@endif
</td>
@if ($classic && $e['image'])
<!--[if !mso]><!-->
<td class="nl-thumb" width="112" valign="top" align="{{ $nl->end }}" style="width: 112px; vertical-align: top; text-align: {{ $nl->end }}; padding: 18px 0; padding-{{ $nl->end }}: 20px;">
<a href="{{ $e['url'] }}" target="_blank" rel="noopener"><img src="{{ $e['image'] }}" width="{{ $tw }}" height="{{ $th }}" alt="" style="display: block; margin-{{ $nl->start }}: auto; width: {{ $tw }}px; max-width: 100%; height: auto; border: 0; border-radius: {{ $nl->cardRadius }}px;"></a>
</td>
<!--<![endif]-->
@endif
</tr>
</table>
</td>
</tr>
