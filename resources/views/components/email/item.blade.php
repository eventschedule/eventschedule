{{--
    One label-above-value pair inside <x-email.details>. wide spans both columns, for long
    values. ltr is for emails, phones and URLs, which read left to right in every language but
    still start on the mail's own side.
--}}
@aware(['theme' => null])
@props(['label', 'wide' => false, 'ltr' => false, 'caption' => null])
@php($theme ??= \App\Utils\EmailTheme::account())
<div class="es-col" style="display: inline-block; width: {{ $wide ? '100%' : '50%' }}; vertical-align: top; font-size: 16px; line-height: 24px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 20px; padding-{{ $theme->end }}: 16px; text-align: {{ $theme->start }};">
<p class="es-ink-3" style="margin: 0 0 4px; font-size: 12px; line-height: 16px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b;">{{ $label }}</p>
<p class="es-ink" @if ($ltr) dir="ltr" @endif style="margin: 0; font-size: 16px; line-height: 24px; font-weight: 600; color: #0f172a; text-align: {{ $theme->start }}; word-break: break-word;">{{ $slot }}</p>
@if (filled($caption))
<p class="es-ink-3" style="margin: 2px 0 0; font-size: 14px; line-height: 20px; color: #64748b;">{{ $caption }}</p>
@endif
</td>
</tr>
</table>
</div>
