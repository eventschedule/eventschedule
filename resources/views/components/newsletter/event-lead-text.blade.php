{{-- The words of the lead card: when, the name, the detail line, and its one button, which is the
     width of the card on a phone. Classic's is outlined, as Classic's buttons are. --}}
@props(['nl', 'e', 'when', 'titleType', 'nudge' => false])
@php
    $meta = $nl->eventMeta($e);
@endphp
@if ($when !== '')
<p style="margin: {{ $nudge ? 2 : 0 }}px 0 8px; {{ $nl->labelType(null, 13) }}">{!! $when !!}</p>
@endif
<p dir="auto" style="margin: 0; word-break: break-word;"><a href="{{ $e['url'] }}" target="_blank" rel="noopener" class="nl-lead-title" style="{{ $titleType }} text-decoration: none;">{{ $e['name'] }}</a></p>
@if ($meta !== '')
<p style="margin: 8px 0 0; {{ $nl->smallType($nl->ink3) }}">{!! $meta !!}</p>
@endif
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 18px 0 2px; text-align: {{ $nl->start }};">
<x-newsletter.button :nl="$nl" :label="$e['cta']" :href="$e['url']" :align="$nl->start" :fluid="true" :variant="$nl->design === 'classic' ? 'outline' : 'solid'" />
</td>
</tr>
</table>
