{{--
    The next event, large: the lead of Modern, Bold and Classic. A wide flyer runs across the top;
    any other picture sits beside the text; with none, the date stands there as a large tile
    (Classic, which has no tiles, is type alone). Pictures are the 960px derivative, which is
    WebP, so Outlook for Windows is shown the card without one.

    The two columns fall into one by themselves: each is an inline block with a max-width, so a
    client that throws the style block away still stacks them on a phone. Classic keeps its
    picture at the END of the card, as its other entries do, and still stacks it ABOVE the text:
    the picture comes first in the markup and the row is laid out in the opposite direction.

    e is one row from NewsletterService::eventRow().
--}}
@props(['nl', 'e'])
@php
    $classic = $nl->design === 'classic';
    $bold = $nl->design === 'bold';
    $inner = $nl->inner - 2;
    $ratio = $e['ratio'] ?: 1.33;
    $across = $e['imageLarge'] && $ratio >= 1.6 && ! $classic;
    $beside = $e['imageLarge'] && ! $across;
    $tile = ! $e['imageLarge'] && $e['day'] && ! $classic;
    $pad = $classic ? 22 : 16;
    $iw = $ih = 0;
    if ($across) {
        [$iw, $ih] = [$inner, (int) round($inner / $ratio)];
    } elseif ($beside) {
        $col = $classic ? 184 : 216;
        [$iw, $ih] = [$col, (int) round($col / $ratio)];
        if ($ih > 288) {
            [$iw, $ih] = [(int) round(288 * $ratio), 288];
        }
    } elseif ($tile) {
        $iw = 112;
    }
    $corner = max(0, $nl->cardRadius - 1);
    // With the date on a tile, the line above the name carries the weekday and the time.
    $when = $nl->when($tile && ! $e['multiDay'] ? $e['weekday'] : $e['date'], $e['time']);
    $titleType = match (true) {
        $classic => $nl->type(27, 34, $nl->ink, 400),
        $bold => $nl->type(27, 32, $nl->ink, 800).' letter-spacing: -0.02em;',
        default => $nl->type(24, 30, $nl->ink, 700).' letter-spacing: -0.02em;',
    };
    // Classic: lay the row out against the mail's direction, so the picture (first in the markup)
    // lands at the end of the card.
    $rowDir = $classic && $beside ? ($nl->dir === 'rtl' ? 'ltr' : 'rtl') : $nl->dir;
    $textPad = $classic ? $nl->end : $nl->start;
@endphp
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px 12px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $nl->panel }}" style="background-color: {{ $nl->panel }}; border: 1px solid {{ $nl->rule }}; border-radius: {{ $nl->cardRadius }}px;">
@if ($across)
<!--[if !mso]><!-->
<tr>
<td style="padding: 0; font-size: 0; line-height: 0;">
<a href="{{ $e['url'] }}" target="_blank" rel="noopener"><img src="{{ $e['imageLarge'] }}" width="{{ $iw }}" height="{{ $ih }}" alt="" style="display: block; width: 100%; max-width: {{ $iw }}px; height: auto; border: 0; border-radius: {{ $corner }}px {{ $corner }}px 0 0;"></a>
</td>
</tr>
<!--<![endif]-->
@endif
<tr>
<td dir="{{ $rowDir }}" style="padding: {{ $across ? '20px 22px 22px' : $pad.'px' }}; text-align: {{ $rowDir === 'rtl' ? 'right' : 'left' }}; font-size: 0;">
@if ($tile)
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td width="{{ $iw }}" valign="top" style="width: {{ $iw }}px; vertical-align: top;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $nl->accentTint }}" style="background-color: {{ $nl->accentTint }}; border-radius: {{ max(0, $nl->cardRadius - 6) }}px;">
<tr>
<td align="center" style="padding: 18px 0 0; text-align: center; {{ $nl->labelType(null, 13) }}">{{ $e['month'] }}</td>
</tr>
<tr>
<td align="center" style="padding: 0 0 18px; text-align: center; {{ $nl->numType(44, 48) }}">{{ $e['day'] }}</td>
</tr>
</table>
</td>
<td valign="top" style="vertical-align: top; text-align: {{ $nl->start }}; padding-{{ $nl->start }}: 20px;">
<x-newsletter.event-lead-text :nl="$nl" :e="$e" :when="$when" :title-type="$titleType" :nudge="true" />
</td>
</tr>
</table>
@else
@if ($beside)
<!--[if !mso]><!-->
<div dir="{{ $nl->dir }}" class="nl-stack{{ $ratio >= 1.2 ? ' nl-lead-img' : '' }}" style="display: inline-block; width: 100%; max-width: {{ $iw }}px; vertical-align: top;">
<a href="{{ $e['url'] }}" target="_blank" rel="noopener"><img src="{{ $e['imageLarge'] }}" width="{{ $iw }}" height="{{ $ih }}" alt="" style="display: block; width: 100%; max-width: {{ $iw }}px; height: auto; border: 0; border-radius: {{ $classic ? $nl->cardRadius : max(0, $nl->cardRadius - 6) }}px;"></a>
</div>
<!--<![endif]-->
@endif
<div dir="{{ $nl->dir }}" class="{{ $beside ? 'nl-stack' : '' }}" style="display: inline-block; width: 100%; max-width: {{ $beside ? $inner - 2 * $pad - $iw : $inner }}px; vertical-align: top;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td class="{{ $beside ? 'nl-stack-pad' : '' }}" style="text-align: {{ $nl->start }};{{ $beside ? ' padding-'.$textPad.': 20px;' : '' }}">
<x-newsletter.event-lead-text :nl="$nl" :e="$e" :when="$when" :title-type="$titleType" :nudge="$beside" />
</td>
</tr>
</table>
</div>
@endif
</td>
</tr>
</table>
</td>
</tr>
