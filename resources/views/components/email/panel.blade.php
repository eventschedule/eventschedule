{{--
    A raised panel inside the card, for the one block that should stand out. Use sparingly: one
    card with hairline sections reads cleaner than boxes inside boxes.
--}}
@aware(['theme' => null])
@props(['align' => null])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $textAlign = $align === 'center' ? 'center' : $theme->start;
@endphp
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 24px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-panel" bgcolor="#f8fafc" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
<tr>
<td class="es-ink-2" style="padding: 20px 24px 4px; font-size: 15px; line-height: 24px; color: #334155; text-align: {{ $textAlign }};">
{{ $slot }}
</td>
</tr>
</table>
</td>
</tr>
</table>
