{{-- A bulleted list, as a table so the bullets sit on the correct side in RTL and in Outlook. --}}
@aware(['theme' => null])
@props(['items' => []])
@php($theme ??= \App\Utils\EmailTheme::account())
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 16px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach ($items as $item)
<tr>
<td width="20" valign="top" class="es-ink-3" style="width: 20px; vertical-align: top; font-size: 16px; line-height: 26px; color: #94a3b8;">&bull;</td>
<td valign="top" class="es-ink-2" style="vertical-align: top; padding: 0 0 4px; font-size: 16px; line-height: 26px; color: #334155; text-align: {{ $theme->start }};">{{ $item }}</td>
</tr>
@endforeach
</table>
</td>
</tr>
</table>
