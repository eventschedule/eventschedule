{{--
    The schedule's events. Each row was resolved once per send by NewsletterService::eventRow()
    (name, link, picture, the date as the event's own schedule's clock reads it, venue, price), so
    this view and its components only print.

    cards: Modern, Bold and Classic open with the next event as a lead; Minimal and Compact are
    type alone. list: one tight row per event in every design.
--}}
@php
    $rows = collect($block['data']['resolvedEvents'] ?? [])->values();
    $layout = $block['data']['layout'] ?? ($style['eventLayout'] ?? 'cards');
    $layout = in_array($layout, ['cards', 'list'], true) ? $layout : 'cards';
    $scheduleUrl = $block['data']['scheduleUrl'] ?? null;
    $boxed = in_array($nl->design, ['modern', 'bold', 'classic'], true);
    $lead = $layout === 'cards' && $boxed ? $rows->first() : null;
    $rest = $lead ? $rows->slice(1)->values() : $rows;
@endphp
@if ($rows->isNotEmpty())
@if ($lead)
<x-newsletter.event-lead :nl="$nl" :e="$lead" />
@endif
@if ($layout === 'list')
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $scheduleUrl ? 8 : $nl->gap }}px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach ($rest as $e)
<x-newsletter.event-row :nl="$nl" :e="$e" :first="$loop->first" />
@endforeach
</table>
</td>
</tr>
@elseif ($boxed)
@foreach ($rest as $e)
<x-newsletter.event-card :nl="$nl" :e="$e" :last="$loop->last" />
@endforeach
@else
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $scheduleUrl ? 8 : $nl->gap }}px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach ($rest as $e)
<x-newsletter.event-entry :nl="$nl" :e="$e" :first="$loop->first" />
@endforeach
</table>
</td>
</tr>
@endif
@if ($scheduleUrl)
<tr>
<td align="{{ $nl->design === 'compact' ? $nl->start : 'center' }}" class="nl-g" style="padding: {{ $boxed && $layout === 'cards' ? 6 : 10 }}px {{ $nl->gutter }}px {{ $nl->gap }}px; text-align: {{ $nl->design === 'compact' ? $nl->start : 'center' }};">
<x-newsletter.button :nl="$nl" :label="__('messages.announcement_view_schedule')" :href="$scheduleUrl" variant="link" :size="$nl->design === 'compact' ? 'sm' : 'md'" />
</td>
</tr>
@endif
@endif
