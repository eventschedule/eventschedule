{{-- A poll from an upcoming event. Each answer is a link to where it is cast, and says so with an
     arrow: they used to look like buttons and do nothing. No radio marks and no Vote button, which
     would promise a choose-then-send that mail cannot do. --}}
@php
    $poll = $block['data']['resolvedPoll'] ?? null;
    $compact = $nl->design === 'compact';
@endphp
@if ($poll)
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap }}px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $nl->panel }}" style="background-color: {{ $nl->panel }}; border: 1px solid {{ $nl->rule }}; border-radius: {{ $nl->cardRadius }}px;">
<tr>
<td style="padding: {{ $compact ? '14px 16px 8px' : '24px 24px 16px' }}; text-align: {{ $nl->start }};">
@if (! empty($poll['eventName']))
<p dir="auto" style="margin: 0 0 6px; {{ $nl->labelType() }}">{{ $poll['eventName'] }}</p>
@endif
<p dir="auto" style="margin: 0 0 {{ $compact ? 10 : 16 }}px; {{ $nl->type($compact ? 16 : 21, $compact ? 22 : 28, $nl->ink, $nl->serif ? 400 : 700) }}">{{ $poll['question'] }}</p>
@foreach ($poll['options'] as $option)
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 8px;">
<tr>
<td bgcolor="{{ $nl->sheet }}" style="background-color: {{ $nl->sheet }}; border: 1px solid {{ $nl->ruleStrong }}; border-radius: {{ $nl->radius }}px;">
{{-- The link is inside each cell: Outlook does not follow one wrapped round a table. --}}
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="text-align: {{ $nl->start }};"><a href="{{ $poll['eventUrl'] }}" target="_blank" rel="noopener" dir="auto" style="display: block; padding: {{ $compact ? 8 : 12 }}px 0; padding-{{ $nl->start }}: {{ $compact ? 12 : 16 }}px; {{ $nl->type($nl->body, $nl->bodyLine - 4, $nl->ink, $nl->serif ? 400 : 600) }} text-decoration: none; word-break: break-word;">{{ $option }}</a></td>
<td width="{{ 24 + ($compact ? 12 : 16) }}" align="{{ $nl->end }}" style="width: {{ 24 + ($compact ? 12 : 16) }}px; text-align: {{ $nl->end }};"><a href="{{ $poll['eventUrl'] }}" target="_blank" rel="noopener" aria-hidden="true" tabindex="-1" style="display: block; padding: {{ $compact ? 8 : 12 }}px 0; padding-{{ $nl->end }}: {{ $compact ? 12 : 16 }}px; {{ $nl->type($nl->body, $nl->bodyLine - 4, $nl->accentInk, 700) }} text-decoration: none;">{!! $nl->arrow !!}</a></td>
</tr>
</table>
</td>
</tr>
</table>
@endforeach
</td>
</tr>
</table>
</td>
</tr>
@endif
