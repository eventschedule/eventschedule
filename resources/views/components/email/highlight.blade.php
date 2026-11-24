{{--
    One large value the reader came for: a sign-up code, a gift card code, a sale's total.

    The value is a prop, never a slot, and prints as ONE text node: a copy (the reader's, or a
    client's "Copy code" button) must yield exactly the value, with no stray whitespace or
    per-character markup. Pass ltr for codes, which read left to right in every language. label sits
    above in small caps ("Gift card code"); tone="neutral" sets it on the plain raised panel, for a
    second highlight that should not compete with the first. large sets the value bigger (a sign-up
    code is the whole mail); group=3 draws it as 123 456, a space made by padding on adjacent spans,
    not a character, so a copy still yields the six digits and nothing else.
--}}
@aware(['theme' => null])
@props(['value', 'caption' => null, 'label' => null, 'mono' => false, 'ltr' => false, 'tone' => 'accent', 'large' => false, 'group' => null])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $font = $mono ? \App\Utils\EmailTheme::MONO : \App\Utils\EmailTheme::FONT;
    $tracking = $mono ? '0.18em' : ($large ? '0.12em' : '-0.01em');
    [$valueSize, $valueLine] = $large ? [44, 52] : [32, 40];
    // The groups are joined in PHP with no whitespace between the spans: Blade's own line breaks
    // would become a space a copy picks up.
    $groups = ($group && (int) $group > 0) ? str_split((string) $value, (int) $group) : null;
    $valueHtml = $groups
        ? implode('', array_map(fn ($part, $i) => '<span'.($i > 0 ? ' style="padding-left: 0.4em;"' : '').'>'.e($part).'</span>', $groups, array_keys($groups)))
        : e((string) $value);
    $neutral = $tone === 'neutral';
    $surface = $neutral
        ? 'class="es-panel" bgcolor="#f8fafc" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;"'
        : 'class="es-tint" bgcolor="'.e($theme->accentTint).'" style="background-color: '.e($theme->accentTint).'; border-radius: 12px;"';
@endphp
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 24px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" {!! $surface !!}>
<tr>
<td align="center" style="padding: 24px 20px; text-align: center;">
@if (filled($label))
<p class="es-ink-3" style="margin: 0 0 6px; font-size: 12px; line-height: 16px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b;">{{ $label }}</p>
@endif
<p class="es-ink" @if ($ltr) dir="ltr" @endif style="margin: 0; font-family: {{ $font }}; font-size: {{ $valueSize }}px; line-height: {{ $valueLine }}px; font-weight: 700; letter-spacing: {{ $tracking }}; color: #0f172a; font-variant-numeric: tabular-nums; word-break: break-all;">{!! $valueHtml !!}</p>
@if (filled($caption))
<p class="es-ink-2" style="margin: 6px 0 0; font-size: 14px; line-height: 20px; color: #475569;">{{ $caption }}</p>
@endif
</td>
</tr>
</table>
</td>
</tr>
</table>
