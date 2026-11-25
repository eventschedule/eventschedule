{{-- A small monospace block (an SMTP error, a command): always left to right, wraps anywhere. --}}
@aware(['theme' => null])
@props(['value', 'gap' => 24])
@php($theme ??= \App\Utils\EmailTheme::account())
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 {{ (int) $gap }}px;">
<table role="presentation" dir="ltr" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-panel" bgcolor="#f8f9fb" style="background-color: #f8f9fb; border: 1px solid #e3e6ec; border-radius: 10px;">
<tr>
<td class="es-ink" dir="ltr" style="padding: 12px 16px; font-family: {{ \App\Utils\EmailTheme::MONO }}; font-size: 13px; line-height: 20px; color: #14161a; text-align: left; word-break: break-all; white-space: pre-wrap;">{{ $value }}</td>
</tr>
</table>
</td>
</tr>
</table>
