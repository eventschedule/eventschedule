{{--
    One numbered step of a short how-to: a tinted number, a title, and the step's own copy in the
    slot (text, a tip, a command). The number sits on the mail's own starting side in RTL.
--}}
@aware(['theme' => null])
@props(['number', 'title', 'last' => false])
@php($theme ??= \App\Utils\EmailTheme::account())
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="32" valign="top" style="width: 32px; vertical-align: top; padding: 0 0 {{ $last ? 8 : 20 }}px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="es-tint" width="30" height="30" align="center" valign="middle" bgcolor="{{ $theme->accentTint }}" style="width: 30px; height: 30px; border-radius: 15px; background-color: {{ $theme->accentTint }}; text-align: center;">
<span class="es-accent-ink" style="font-size: 14px; line-height: 30px; font-weight: 700; color: {{ $theme->accentInk }};">{{ $number }}</span>
</td>
</tr>
</table>
</td>
<td valign="top" style="vertical-align: top; padding: 0 0 {{ $last ? 8 : 20 }}px; padding-{{ $theme->start }}: 14px; text-align: {{ $theme->start }};">
<p class="es-ink" style="margin: 4px 0 4px; font-size: 16px; line-height: 22px; font-weight: 700; color: #0f172a;">{{ $title }}</p>
<div class="es-ink-2" style="font-size: 15px; line-height: 24px; color: #334155;">{{ $slot }}</div>
</td>
</tr>
</table>
