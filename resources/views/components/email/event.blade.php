{{--
    When and where, the block a guest opens the mail to find: a date tile, the date and time, the
    venue and its address, "View map", and "Add to calendar" for Google, Apple (.ics) and Outlook.

    Dates are the SCHEDULE's clock (getStartDateTime() with no override): an occurrence falls on a
    given day because of where it happens, not where the reader is. date is the occurrence (a
    sale's event_date) for a recurring event. time-line replaces the computed time when the mail
    has its own (an appointment's guest-zone line). ics overrides the .ics link (appointments have
    their own route), and details the notes the calendar entry carries (default: the event page,
    which an appointment should replace with its manage link). calendar and map turn the link rows
    off, as a cancelled event wants.
--}}
@aware(['theme' => null])
@props(['event', 'date' => null, 'role' => null, 'calendar' => true, 'map' => true, 'time' => true, 'timeLine' => null, 'ics' => null, 'details' => null])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $start = $event->starts_at ? $event->getStartDateTime($date, true) : null;
    $use24 = (bool) ($role?->use_24_hour_time);

    $dateLine = $start ? ($event->is_multi_day ? $event->getDateRangeDisplay($date) : $start->translatedFormat('l, F j, Y')) : null;
    $timeLine ??= ($time && $start) ? $event->getStartEndTime($date, $use24) : null;

    $venue = $event->venue;
    $venueName = $venue ? trim((string) $venue->name) : '';
    $address = $venue ? trim((string) $venue->bestAddress()) : '';
    $mapUrl = $map && $address !== '' ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($address) : null;

    $calendarLinks = [];
    if ($calendar && $start) {
        // The entry's notes link back to the event page rather than carry the description, which
        // has no length cap and would ride in both URLs (see Event::getGoogleCalendarUrl()). A
        // link that cannot be built drops out and is reported: it must never stop the mail.
        $guestUrl = null;
        $appleUrl = $ics;
        if (! $ics) {
            try {
                $subdomain = $role?->subdomain ?? false;
                $guestUrl = $event->getGuestUrl($subdomain, $date, true) ?: null;
                $appleUrl = $event->getAppleCalendarUrl($date, $subdomain, true) ?: null;
            } catch (\Illuminate\Routing\Exceptions\UrlGenerationException $e) {
                report($e);
            }
        }
        $notes = $details ?? $guestUrl ?? '';
        $calendarLinks['Google'] = $event->getGoogleCalendarUrl($date, $notes);
        if ($appleUrl) {
            $calendarLinks['Apple'] = $appleUrl;
        }
        $calendarLinks['Outlook'] = $event->getMicrosoftCalendarUrl($date, $notes);
    }

    $link = 'color: '.$theme->accentInk.'; text-decoration: underline;';
@endphp
@if ($dateLine || $venueName !== '' || $address !== '')
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 24px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-panel" bgcolor="#f8fafc" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
<tr>
<td style="padding: 20px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
@if ($start)
<td width="64" valign="top" style="width: 64px; vertical-align: top;">
<table role="presentation" width="64" cellpadding="0" cellspacing="0" border="0" class="es-tint" bgcolor="{{ $theme->accentTint }}" style="width: 64px; background-color: {{ $theme->accentTint }}; border-radius: 12px;">
<tr>
<td align="center" class="es-accent-ink" style="padding: 9px 0 0; text-align: center; font-size: 12px; line-height: 16px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: {{ $theme->accentInk }};">{{ $start->translatedFormat('M') }}</td>
</tr>
<tr>
<td align="center" class="es-ink" style="padding: 0 0 9px; text-align: center; font-size: 26px; line-height: 32px; font-weight: 700; color: #0f172a;">{{ $start->format('j') }}</td>
</tr>
</table>
</td>
@endif
<td valign="top" style="vertical-align: top; text-align: {{ $theme->start }};{{ $start ? ' padding-'.$theme->start.': 16px;' : '' }}">
@if ($dateLine)
<p class="es-ink" style="margin: 0; font-size: 16px; line-height: 24px; font-weight: 600; color: #0f172a;">{{ $dateLine }}</p>
@endif
@if ($timeLine)
<p class="es-ink-2" dir="auto" style="margin: 2px 0 0; font-size: 15px; line-height: 22px; color: #334155;">{{ $timeLine }}</p>
@endif
@if ($venueName !== '')
<p class="es-ink" dir="auto" style="margin: 10px 0 0; font-size: 15px; line-height: 22px; font-weight: 600; color: #0f172a;">{{ $venueName }}</p>
@endif
@if ($address !== '' && $address !== $venueName)
<p class="es-ink-3" dir="auto" style="margin: 0; font-size: 14px; line-height: 20px; color: #64748b;">{{ $address }}</p>
@endif
@if ($mapUrl)
<p style="margin: 6px 0 0; font-size: 14px; line-height: 20px;"><a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="es-link" style="{{ $link }} font-weight: 600;">{{ __('messages.view_map') }}</a></p>
@endif
</td>
</tr>
</table>
@if ($calendarLinks)
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="height: 16px; line-height: 16px; font-size: 0;">&nbsp;</td>
</tr>
<tr>
<td class="es-rule es-ink-3" style="padding: 14px 0 0; border-top: 1px solid #e2e8f0; font-size: 14px; line-height: 20px; color: #64748b; text-align: {{ $theme->start }};">
{{ __('messages.add_to_calendar') }}:&nbsp;
@foreach ($calendarLinks as $label => $url)
<a href="{{ $url }}" target="_blank" rel="noopener" class="es-link" style="{{ $link }} font-weight: 600;">{{ $label }}</a>@if (! $loop->last)<span class="es-ink-3" style="color: #94a3b8;">&nbsp;&middot;&nbsp;</span>@endif
@endforeach
</td>
</tr>
</table>
@endif
</td>
</tr>
</table>
</td>
</tr>
</table>
@endif
