{{--
    The mail's one primary action (or a secondary one).

    Bulletproof without the border trick (an 18px side border trips EmailSideStripeTest) and
    without VML (a roundrect needs a fixed width, which twelve languages will not fit): the cell
    carries the colour and Outlook's padding, the link carries everyone else's. Full width on a
    phone. With no href it renders disabled, for test sends, with an optional note beneath.
--}}
@aware(['theme' => null])
@props(['href' => null, 'variant' => 'primary', 'note' => null, 'align' => 'center'])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $disabled = blank($href);
    [$bg, $fg, $border] = match (true) {
        $disabled => ['#e2e8f0', '#475569', '#e2e8f0'],
        $variant === 'secondary' => ['#ffffff', $theme->accentInk, '#cbd5e1'],
        default => [$theme->accent, $theme->onAccent, $theme->accentBorder],
    };
    $cellAlign = $align === 'center' ? 'center' : $theme->start;
    $label = 'display: inline-block; padding: 14px 28px; font-family: '.\App\Utils\EmailTheme::FONT.'; font-size: 16px; line-height: 20px; font-weight: 600; color: '.$fg.'; text-decoration: none; border-radius: 10px; mso-padding-alt: 0;';
@endphp
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="{{ $cellAlign }}" style="padding: 8px 0 24px; text-align: {{ $cellAlign }};">
<table role="presentation" dir="{{ $theme->dir }}" @if ($cellAlign === 'center') align="center" @endif cellpadding="0" cellspacing="0" border="0" class="es-btn-wrap" style="{{ $cellAlign === 'center' ? 'margin: 0 auto;' : '' }}">
<tr>
<td align="center" bgcolor="{{ $bg }}" class="{{ $disabled ? 'es-btn-off' : ($variant === 'secondary' ? 'es-btn-ghost' : 'es-btn') }}" style="background-color: {{ $bg }}; border: 1px solid {{ $border }}; border-radius: 10px; mso-padding-alt: 14px 28px; text-align: center;">
@if ($disabled)
<span style="{{ $label }}">{{ $slot }}</span>
@else
<a href="{{ $href }}" target="_blank" rel="noopener" class="es-btn-a{{ $variant === 'secondary' ? ' es-link' : '' }}" style="{{ $label }}">{{ $slot }}</a>
@endif
</td>
</tr>
</table>
@if (filled($note))
<p class="es-ink-3" style="margin: 10px 0 0; font-size: 13px; line-height: 20px; color: #64748b; text-align: {{ $cellAlign }};">{{ $note }}</p>
@endif
</td>
</tr>
</table>
