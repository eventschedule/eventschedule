{{--
    The logo that opens a newsletter, as its masthead. A wide logo usually carries the name already,
    so it stands alone; a square one gets the schedule's name with it. A hairline round the picture,
    so a pale logo does not vanish into a pale page. Outlook for Windows cannot show the WebP
    derivative, so it is given the name alone.

    Classic's is a nameplate: the name in spaced capitals, the month the mail goes out, and the
    double rule. Compact's is one line.
--}}
@props(['nl', 'role'])
@php
    $size = $role->imageSourceDimensions();
    $ratio = $size ? $size[0] / max(1, $size[1]) : 1;
    $wide = $ratio > 1.6;
    $logo = $role->getProfileImageUrl(480);
    $box = ['modern' => 72, 'classic' => 72, 'minimal' => 48, 'bold' => 88, 'compact' => 36][$nl->design];
    [$w, $h] = $wide ? [min(220, (int) round($box * 0.8 * $ratio)), null] : ($ratio >= 1 ? [$box, (int) round($box / $ratio)] : [(int) round($box * $ratio), $box]);
    $h ??= (int) round($w / $ratio);
    // No size on record (an upload from before sizes were kept, which is most of them): the
    // picture keeps its own shape inside a box, as the x-email sender row lets it. Printing a
    // width and a height for a ratio that was only assumed squeezed a wide logo into a square.
    $sized = $size
        ? 'width="'.$w.'" height="'.$h.'" style="display: block; width: '.$w.'px; height: '.$h.'px;'
        : 'style="display: block; width: auto; height: auto; max-width: '.min(220, $box * 3).'px; max-height: '.$box.'px;';
    $inline = $nl->design === 'compact';
    $logoRadius = $nl->radius === 0 ? 0 : ($wide ? min(8, $nl->radius) : (int) round($box / 4.5));
    $edge = $nl->design === 'bold' ? ' border: 3px solid '.$nl->accent.';' : ($wide ? '' : ' border: 1px solid '.$nl->rule.';');
    $nameStyle = match ($nl->design) {
        'classic' => $nl->type(14, 20, $nl->ink, 400).$nl->tracking(0.24).' text-transform: uppercase;',
        'bold' => $nl->type(15, 20, $nl->ink, 800).$nl->tracking(0.12).' text-transform: uppercase;',
        'compact' => $nl->type(14, 20, $nl->ink, 700),
        'minimal' => $nl->type(15, 20, $nl->ink, 600),
        // Modern's masthead stands on the ground, not on the sheet.
        default => $nl->type(17, 24, $nl->groundInk, 700),
    };
    $homeUrl = $role->getGuestUrl(true);
    $pad = match ($nl->design) {
        'modern' => '0 8px 22px',
        'classic' => '36px '.$nl->gutter.'px 0',
        'minimal' => '32px '.$nl->gutter.'px 22px',
        default => '36px '.$nl->gutter.'px 28px',
    };
@endphp
@if ($inline)
<tr>
<td class="nl-g nl-mast" style="padding: 14px {{ $nl->gutter }}px 12px; text-align: {{ $nl->start }};">
<table role="presentation" dir="{{ $nl->dir }}" cellpadding="0" cellspacing="0" border="0">
<tr>
<!--[if !mso]><!-->
<td valign="middle" style="vertical-align: middle;"><a href="{{ $homeUrl }}" style="text-decoration: none;"><img src="{{ $logo }}" alt="{{ $wide ? $role->name : '' }}" {!! $sized !!} border-radius: {{ $logoRadius }}px;{{ $edge }}"></a></td>
<!--<![endif]-->
@if (! $wide)
<td valign="middle" style="vertical-align: middle; padding-{{ $nl->start }}: 10px;"><a href="{{ $homeUrl }}" dir="auto" style="{{ $nameStyle }} text-decoration: none;">{{ $role->name }}</a></td>
@endif
<!--[if mso]>@if ($wide)<td style="{{ $nameStyle }}">{{ $role->name }}</td>@endif<![endif]-->
</tr>
</table>
</td>
</tr>
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="border-top: 1px solid {{ $nl->rule }}; height: {{ $nl->top }}px; line-height: {{ $nl->top }}px; font-size: 0;">&nbsp;</td>
</tr>
</table>
</td>
</tr>
@else
<tr>
<td align="center" class="nl-mast" style="padding: {{ $pad }}; text-align: center;">
<!--[if !mso]><!-->
<a href="{{ $homeUrl }}" style="text-decoration: none;"><img src="{{ $logo }}" alt="{{ $wide ? $role->name : '' }}" {!! $sized !!} margin: 0 auto; border-radius: {{ $logoRadius }}px;{{ $edge }}"></a>
<!--<![endif]-->
@if (! $wide)
<p dir="auto" style="margin: 12px 0 0; text-align: center;"><a href="{{ $homeUrl }}" style="{{ $nameStyle }} text-decoration: none;">{{ $role->name }}</a></p>
@endif
<!--[if mso]>@if ($wide)<p style="margin: 0; text-align: center; {{ $nameStyle }}">{{ $role->name }}</p>@endif<![endif]-->
@if ($nl->design === 'classic')
<p style="margin: 6px 0 0; text-align: center; {{ $nl->type(15, 22, $nl->ink3) }}{{ $nl->italic() }}">{{ \Carbon\Carbon::now($role->timezone ?: config('app.timezone'))->translatedFormat('F Y') }}</p>
@endif
</td>
</tr>
@if (in_array($nl->design, ['classic', 'minimal'], true))
<tr>
<td class="nl-g" style="padding: {{ $nl->design === 'classic' ? 18 : 0 }}px {{ $nl->gutter }}px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@if ($nl->design === 'classic')
<tr>
<td style="border-top: 1px solid {{ $nl->ruleDouble }}; border-bottom: 1px solid {{ $nl->ruleDouble }}; height: 3px; line-height: 3px; font-size: 0;">&nbsp;</td>
</tr>
<tr>
<td style="height: {{ $nl->top }}px; line-height: {{ $nl->top }}px; font-size: 0;">&nbsp;</td>
</tr>
@else
<tr>
<td style="border-top: 1px solid {{ $nl->rule }}; height: {{ $nl->top }}px; line-height: {{ $nl->top }}px; font-size: 0;">&nbsp;</td>
</tr>
@endif
</table>
</td>
</tr>
@endif
@endif
