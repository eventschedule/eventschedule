{{--
    Someone's own words: an organizer's note, a gift message, a support message. Pass the raw
    text as text (escaped, line breaks kept); the slot is for markup that is already safe. label
    sits above in small caps ("Note from the organizer"); the author sits on its own line beneath,
    never after a dash.
--}}
@aware(['theme' => null])
@props(['text' => null, 'cite' => null, 'label' => null])
@php($theme ??= \App\Utils\EmailTheme::account())
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 24px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-panel" bgcolor="#f8fafc" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
<tr>
<td style="padding: 16px 20px; text-align: {{ $theme->start }};">
@if (filled($label))
<p class="es-ink-3" style="margin: 0 0 6px; font-size: 12px; line-height: 16px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b;">{{ $label }}</p>
@endif
<div class="es-ink" dir="auto" style="margin: 0; font-size: 16px; line-height: 26px; color: #0f172a; word-break: break-word;">@if (filled($text)){!! nl2br(e($text)) !!}@else{{ $slot }}@endif</div>
@if (filled($cite))
<p class="es-ink-3" style="margin: 8px 0 0; font-size: 14px; line-height: 20px; color: #64748b;">{{ $cite }}</p>
@endif
</td>
</tr>
</table>
</td>
</tr>
</table>
