@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);
    $headingLine = trans_choice('messages.announcement_heading', $events->count(), ['count' => $events->count()]);
@endphp
<x-email.layout :theme="$theme" :title="trans_choice('messages.announcement_subject', $events->count(), ['schedule' => $role->name, 'count' => $events->count()])" :preheader="$headingLine.' · '.$events->pluck('name')->take(3)->implode(', ')">
<x-email.heading :eyebrow="$headingLine" auto>{{ $role->name }}</x-email.heading>

<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 16px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach ($events as $event)
    {{-- getStartDateTime() with no timezone override, so the date renders in the SCHEDULE's
         timezone. An occurrence falls on a given day because of where it happens, not because
         of where the reader is sitting. --}}
    @php
        $start = $event->starts_at ? $event->getStartDateTime(null, true) : null;
        $rule = $loop->first ? '' : ' border-top: 1px solid #e2e8f0;';
    @endphp
<tr>
@if ($start)
<td class="es-rule" width="52" valign="top" style="width: 52px; vertical-align: top; padding: 14px 0;{{ $rule }}">
<table role="presentation" width="52" cellpadding="0" cellspacing="0" border="0" class="es-tint" bgcolor="{{ $theme->accentTint }}" style="width: 52px; background-color: {{ $theme->accentTint }}; border-radius: 10px;">
<tr>
<td align="center" class="es-accent-ink" style="padding: 7px 0 0; text-align: center; font-size: 11px; line-height: 14px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: {{ $theme->accentInk }};">{{ $start->translatedFormat('M') }}</td>
</tr>
<tr>
<td align="center" class="es-ink" style="padding: 0 0 7px; text-align: center; font-size: 20px; line-height: 26px; font-weight: 700; color: #0f172a;">{{ $start->format('j') }}</td>
</tr>
</table>
</td>
@endif
<td class="es-rule" valign="top" @if (! $start) colspan="2" @endif style="vertical-align: top; padding: 14px 0;{{ $start ? ' padding-'.$theme->start.': 14px;' : '' }}{{ $rule }} text-align: {{ $theme->start }};">
<a href="{{ $event->getGuestUrl($role->subdomain, null, true) }}" target="_blank" rel="noopener" class="es-ink" dir="auto" style="font-size: 17px; line-height: 24px; font-weight: 600; color: #0f172a; text-decoration: none;">{{ $event->name }}</a>
<p class="es-ink-3" style="margin: 4px 0 0; font-size: 14px; line-height: 20px; color: #64748b;">
@if ($start)
{{ $event->is_multi_day ? $event->getDateRangeDisplay() : $start->translatedFormat('l, F j, Y') }}
@endif
@if ($event->venue && $event->venue->name)
@if ($start) &middot; @endif<span dir="auto">{{ $event->venue->name }}</span>
@endif
</p>
</td>
</tr>
@endforeach
</table>
</td>
</tr>
</table>

<x-email.button :href="$role->getGuestUrl(true)">{{ __('messages.announcement_view_schedule') }}</x-email.button>

<x-slot:footer>
{{-- messages.subscription_why_receiving shipped translated into all 13 languages and was rendered
     nowhere. It is the line that turns "who is this?" into an unsubscribe rather than a spam
     complaint.

     "Manage your account" is the one durable way back to the account this subscription created.
     The offer on /sub/done is one-shot, so without this nothing ever mentions it again.
     Deliberately "Manage your account" and not "Set a password": one footer reaches recipients
     with a passwordless account, a real account and no account, and the page is what can tell
     them apart. --}}
<x-email.footer :links="[[__('messages.subscription_manage_account'), $manageUrl ?? null], [__('messages.unsubscribe'), $unsubscribeUrl]]">{{ __('messages.subscription_why_receiving', ['schedule' => $role->name]) }}</x-email.footer>
</x-slot:footer>
</x-email.layout>
