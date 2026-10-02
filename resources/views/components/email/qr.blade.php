{{--
    A QR code on a white tile that stays white in dark mode, so it still scans. src is usually
    $message->embedData(...), called in the VIEW: a component cannot see $message.
--}}
@aware(['theme' => null])
@props(['src', 'alt' => '', 'caption' => null, 'size' => 180])
@php($theme ??= \App\Utils\EmailTheme::account())
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="center" style="padding: 0 0 24px; text-align: center;">
<table role="presentation" align="center" cellpadding="0" cellspacing="0" border="0">
<tr>
<td bgcolor="#ffffff" style="padding: 12px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
<img src="{{ $src }}" width="{{ (int) $size }}" height="{{ (int) $size }}" alt="{{ $alt }}" style="display: block; width: {{ (int) $size }}px; height: {{ (int) $size }}px; border: 0;">
</td>
</tr>
</table>
@if (filled($caption))
<p class="es-ink-3" style="margin: 10px 0 0; font-size: 13px; line-height: 20px; color: #64748b;">{{ $caption }}</p>
@endif
</td>
</tr>
</table>
