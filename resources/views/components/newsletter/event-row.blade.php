{{-- One event as a row of the list layout: the date as a tile (tinted in Modern and Bold, bare
     type elsewhere), the name as the link, one quiet line under it, and an arrow at the end that
     says the row goes somewhere. Reads at 390px: the old row gave 120px to a date string and 60px
     to a button and left the name what remained. e is one row from NewsletterService::eventRow(). --}}
@props(['nl', 'e', 'first' => false])
@php
    $filled = in_array($nl->design, ['modern', 'bold'], true);
    $side = $nl->design === 'compact' ? 44 : 52;
    $rule = $first ? '' : 'border-top: 1px solid '.$nl->rule.';';
    $pad = $nl->design === 'compact' ? 9 : 13;
@endphp
<tr>
@if ($e['day'])
<td width="{{ $side }}" valign="top" style="width: {{ $side }}px; vertical-align: top; padding: {{ $pad }}px 0; {{ $rule }}">
<table role="presentation" width="{{ $side }}" cellpadding="0" cellspacing="0" border="0" @if ($filled) bgcolor="{{ $nl->accentTint }}" @endif style="width: {{ $side }}px;{{ $filled ? ' background-color: '.$nl->accentTint.'; border-radius: '.max(0, $nl->cardRadius - 4).'px;' : '' }}">
<tr>
<td align="center" style="padding: {{ $filled ? 7 : 2 }}px 0 0; text-align: center; {{ $nl->labelType(null, $nl->serif ? 10 : 11) }}">{{ $e['month'] }}</td>
</tr>
<tr>
<td align="center" style="padding: 0 0 {{ $filled ? 7 : 0 }}px; text-align: center; {{ $nl->numType($nl->design === 'compact' ? 19 : 22, $nl->design === 'compact' ? 22 : 26) }}">{{ $e['day'] }}</td>
</tr>
</table>
</td>
@endif
<td valign="top" @if (! $e['day']) colspan="2" @endif style="vertical-align: top; padding: {{ $pad }}px 0; padding-{{ $nl->start }}: {{ $e['day'] ? 16 : 0 }}px; {{ $rule }} text-align: {{ $nl->start }};">
<p dir="auto" style="margin: 0;"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" style="{{ $nl->type(['compact' => 15, 'bold' => 19][$nl->design] ?? 17, ['compact' => 20, 'bold' => 25][$nl->design] ?? 24, $nl->ink, $nl->serif ? 400 : ($nl->design === 'bold' ? 800 : 700)) }} text-decoration: none;">{{ $e['name'] }}</a></p>
<p style="margin: 3px 0 0; {{ $nl->smallType($nl->ink3) }}">{!! $nl->eventDetail($e) !!}</p>
</td>
<td width="28" valign="middle" align="{{ $nl->end }}" style="width: 28px; vertical-align: middle; padding: {{ $pad }}px 0; {{ $rule }} text-align: {{ $nl->end }};"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" aria-label="{{ $e['cta'] }}" style="{{ $nl->type(18, 24, $nl->accentInk, 700) }} text-decoration: none;">{!! $nl->arrow !!}</a></td>
</tr>
