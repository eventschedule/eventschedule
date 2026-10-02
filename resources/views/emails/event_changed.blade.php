@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $label = 'margin: 0 0 6px; font-size: 12px; line-height: 16px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b;';
    $was = 'margin: 0; font-size: 14px; line-height: 22px; color: #64748b;';
    $now = 'margin: 0; font-size: 15px; line-height: 24px; color: #0f172a;';
    $plain = 'margin: 0; font-size: 15px; line-height: 24px; color: #334155;';

    // The date and the time range each isolated on its own: inside an RTL line the bidi algorithm
    // otherwise pulls the numbers into the month's run and reorders the range into
    // "PM - 11:00 PM EDT 8:00". The date follows its own script; the time always reads left to right.
    $when = function (?array $parts, ?string $joined, ?string $tz) {
        $parts ??= ['date' => (string) $joined, 'time' => null];
        $html = '<bdi>'.e($parts['date']).'</bdi>';
        $tail = trim(($parts['time'] ?? '').' '.$tz);

        return new \Illuminate\Support\HtmlString($tail === '' ? $html : $html.($parts['time'] !== null ? ', ' : ' ').'<bdi dir="ltr">'.e($tail).'</bdi>');
    };
@endphp
<x-email.layout :theme="$theme" :title="__('messages.event_changed_heading')" :preheader="__('messages.event_changed_body', ['event' => $event->name])">
<x-email.heading :eyebrow="__('messages.event_changed_heading')" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }}@if (! empty($recipientName)), {{ $recipientName }}@endif,</x-email.text>
<x-email.text>{{ __('messages.event_changed_body', ['event' => $event->name]) }}</x-email.text>

@if (! empty($note))
<x-email.quote :label="__('messages.organizer_note')" :text="$note" />
@endif

{{-- No when/where card: the organizer's preview (EventController::notifyPreview) renders this from
     an unsaved clone whose venue is still the OLD one, so a card would contradict the "Now" line
     below. The new date and venue are both stated here. --}}
<x-email.panel>
<p class="es-ink-3" style="{{ $label }}">{{ __('messages.event_changed_whats_changed') }}</p>

@if (isset($display['date']))
<p class="es-ink" style="margin: 10px 0 4px; font-size: 15px; line-height: 22px; font-weight: 600; color: #0f172a;">{{ __('messages.event_changed_date_label') }}</p>
@if (! empty($display['date']['old']))
<p class="es-ink-3" style="{{ $was }}">{{ __('messages.event_changed_previously') }}: <s>{{ $when($display['date']['old_parts'] ?? null, $display['date']['old'], $display['date']['old_tz']) }}</s></p>
@endif
<p class="es-ink" style="{{ $now }}">{{ __('messages.event_changed_now') }}: <strong>{{ $when($display['date']['new_parts'] ?? null, $display['date']['new'], $display['date']['new_tz']) }}</strong></p>
@if (! empty($display['date']['delta']))
<p class="es-accent-ink" style="margin: 2px 0 0; font-size: 13px; line-height: 20px; font-weight: 600; color: {{ $theme->accentInk }};">{{ $display['date']['delta'] }}</p>
@endif
@endif

@if (isset($display['location']))
@php($loc = $display['location'])
<p class="es-ink" style="margin: {{ isset($display['date']) ? '16px' : '10px' }} 0 4px; font-size: 15px; line-height: 22px; font-weight: 600; color: #0f172a;">{{ __('messages.event_changed_location_label') }}</p>
@if ($loc['variant'] === 'moved_online')
<p class="es-ink-2" style="{{ $plain }}">{{ __('messages.event_changed_moved_online') }}</p>
@elseif ($loc['variant'] === 'moved_in_person')
<p class="es-ink-2" style="{{ $plain }}">{{ __('messages.event_changed_moved_in_person', ['venue' => $loc['new_venue']]) }}</p>
@elseif ($loc['variant'] === 'online_updated')
<p class="es-ink-2" style="{{ $plain }}">{{ __('messages.event_changed_online_updated') }}</p>
@else
<p class="es-ink-2" style="{{ $plain }}">{{ __('messages.event_changed_venue') }}</p>
@if (! empty($loc['old_venue']))
<p class="es-ink-3" style="margin: 6px 0 0; font-size: 14px; line-height: 22px; color: #64748b;">{{ __('messages.event_changed_previously') }}: <s dir="auto">{{ $loc['old_venue'] }}</s></p>
@endif
@if (! empty($loc['new_venue']))
<p class="es-ink" style="{{ $now }}">{{ __('messages.event_changed_now') }}: <strong dir="auto">{{ $loc['new_venue'] }}</strong></p>
@endif
@endif
@endif
<div style="height: 16px; line-height: 16px; font-size: 0;">&nbsp;</div>
</x-email.panel>

<x-email.button :href="$eventUrl">{{ __('messages.event_changed_cta') }}</x-email.button>

@if (! empty($icalUrl))
<x-email.text variant="small" align="center" :gap="4"><x-email.link :href="$icalUrl">{{ __('messages.update_your_calendar') }}</x-email.link></x-email.text>
<x-email.text variant="muted" align="center">{{ __('messages.update_your_calendar_note') }}</x-email.text>
@endif
</x-email.layout>
