{{-- One event as type alone: Minimal's entry (rules between, a text link) and Compact's (a tight
     panel with the date down its side and an arrow at its end). No pictures in either: that is
     what these two are. e is one row from NewsletterService::eventRow(). --}}
@props(['nl', 'e', 'first' => false])
@php
    $when = $nl->when($e['date'], $e['time']);
    $meta = $nl->eventMeta($e);
@endphp
@if ($nl->design === 'compact')
<tr>
<td style="padding: 0 0 6px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $nl->panel }}" style="background-color: {{ $nl->panel }}; border: 1px solid {{ $nl->rule }}; border-radius: {{ $nl->cardRadius }}px;">
<tr>
@if ($e['day'])
<td width="46" valign="top" align="center" style="width: 46px; vertical-align: top; padding: 10px 0; padding-{{ $nl->start }}: 12px; text-align: center;">
<p style="margin: 0; {{ $nl->labelType(null, 12) }}">{{ $e['month'] }}</p>
<p style="margin: 0; {{ $nl->numType(20, 22) }}">{{ $e['day'] }}</p>
</td>
@endif
<td valign="top" style="vertical-align: top; padding: 10px 14px; text-align: {{ $nl->start }};">
<p dir="auto" style="margin: 0;"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" style="{{ $nl->type(15, 20, $nl->ink, 700) }} text-decoration: none;">{{ $e['name'] }}</a></p>
<p style="margin: 2px 0 0; {{ $nl->smallType($nl->ink3) }}">{!! $nl->eventDetail($e) !!}</p>
</td>
<td width="30" valign="middle" align="{{ $nl->end }}" style="width: 30px; vertical-align: middle; padding-{{ $nl->end }}: 12px; text-align: {{ $nl->end }};"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" aria-label="{{ $e['cta'] }}" style="{{ $nl->type(18, 24, $nl->accentInk, 700) }} text-decoration: none;">{!! $nl->arrow !!}</a></td>
</tr>
</table>
</td>
</tr>
@else
<tr>
<td style="padding: 18px 0 12px; {{ $first ? '' : 'border-top: 1px solid '.$nl->rule.';' }} text-align: {{ $nl->start }};">
@if ($when !== '')
<p style="margin: 0 0 4px; {{ $nl->labelType($nl->ink3) }}">{!! $when !!}</p>
@endif
<p dir="auto" style="margin: 0;"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" style="{{ $nl->type(21, 27, $nl->ink, 600) }} letter-spacing: -0.01em; text-decoration: none;">{{ $e['name'] }}</a></p>
@if ($meta !== '')
<p style="margin: 4px 0 0; {{ $nl->smallType($nl->ink3) }}">{!! $meta !!}</p>
@endif
<p style="margin: 2px 0 0;"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" style="display: inline-block; padding: 8px 0 6px; {{ $nl->type(15, 20, $nl->accentInk, 600) }} text-decoration: underline;">{{ $e['cta'] }}&nbsp;{!! $nl->arrow !!}</a></p>
</td>
</tr>
@endif
