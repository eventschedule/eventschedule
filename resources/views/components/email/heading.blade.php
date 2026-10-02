{{--
    The one <h1> of the mail, with an optional eyebrow above it and a subtitle below.

    Event mail headlines the event and moves the mail's own title ("Ticket purchase confirmation",
    "Event cancelled") into the eyebrow. A state is told by the eyebrow's colour (tone), never by an
    icon: glyphs render differently in every client and font. Pass auto for user content, so a
    Hebrew event name in an English mail lays itself out correctly. An eyebrow that is a whole
    sentence (some mails have no shorter title) reads in sentence case: spaced capitals only suit a
    label of a few words.
--}}
@aware(['theme' => null])
@props(['eyebrow' => null, 'tone' => null, 'subtitle' => null, 'auto' => false])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $eyebrowColour = $tone ? $theme->tone($tone)['ink'] : $theme->accentInk;
    $eyebrowClass = $tone ? 'es-ink-'.\App\Utils\EmailTheme::toneName($tone) : 'es-accent-ink';
    $eyebrowCase = mb_strlen((string) $eyebrow) > 36
        ? 'font-size: 14px; line-height: 20px;'
        : 'font-size: 13px; line-height: 18px; letter-spacing: 0.06em; text-transform: uppercase;';
@endphp
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 20px; text-align: {{ $theme->start }};">
@if (filled($eyebrow))
<p class="{{ $eyebrowClass }}" style="margin: 0 0 10px; {{ $eyebrowCase }} font-weight: 600; color: {{ $eyebrowColour }};">{{ $eyebrow }}</p>
@endif
<h1 class="es-ink es-h1" @if ($auto) dir="auto" @endif style="margin: 0; font-size: 28px; line-height: 34px; font-weight: 700; letter-spacing: -0.02em; color: #0f172a; font-family: {{ \App\Utils\EmailTheme::FONT }};">{{ $slot }}</h1>
@if (filled($subtitle))
<p class="es-ink-3" style="margin: 8px 0 0; font-size: 16px; line-height: 24px; color: #64748b;">{{ $subtitle }}</p>
@endif
</td>
</tr>
</table>
