{{-- A hairline that opens a new section of the card, with an optional small-caps label. --}}
@aware(['theme' => null])
@props(['label' => null])
@php($theme ??= \App\Utils\EmailTheme::account())
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="es-rule" style="padding: 24px 0 {{ filled($label) ? 12 : 0 }}px; border-top: 1px solid #e3e6ec; text-align: {{ $theme->start }};">
@if (filled($label))
<p class="es-ink-3" style="margin: 0; font-size: 12px; line-height: 16px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #6a7183;">{{ $label }}</p>
@endif
</td>
</tr>
</table>
