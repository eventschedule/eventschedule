{{--
    A tinted panel for a state worth stopping on: a failed payment, a cancellation, a warning, a
    success. Tinted background and a 1px border all round, never a side stripe (CLAUDE.md). The
    slot is inline text; the optional title reads in the tone's own colour.
--}}
@aware(['theme' => null])
@props(['tone' => 'info', 'title' => null, 'gap' => 24])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $tone = \App\Utils\EmailTheme::toneName($tone);
    $c = $theme->tone($tone);
@endphp
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 {{ (int) $gap }}px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" class="es-tone-{{ $tone }}" bgcolor="{{ $c['bg'] }}" style="background-color: {{ $c['bg'] }}; border: 1px solid {{ $c['border'] }}; border-radius: 12px;">
<tr>
<td class="es-ink-2" style="padding: 16px 20px; font-size: 15px; line-height: 24px; color: #334155; text-align: {{ $theme->start }};">
@if (filled($title))
<p class="es-ink-{{ $tone }}" style="margin: 0 0 4px; font-size: 15px; line-height: 22px; font-weight: 600; color: {{ $c['ink'] }};">{{ $title }}</p>
@endif
{{ $slot }}
</td>
</tr>
</table>
</td>
</tr>
</table>
