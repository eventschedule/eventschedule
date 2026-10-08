{{-- The schedule's picture in the flow of the mail. One that OPENS the mail is the masthead instead
     (the newsletter masthead component), which the shell renders. With no size on record the
     picture keeps its own shape. It is the WebP derivative, so Outlook for Windows is not sent it. --}}
@php
    $size = $role->imageSourceDimensions();
    $ratio = $size ? $size[0] / max(1, $size[1]) : 1;
    $box = $nl->design === 'compact' ? 72 : 120;
    $w = $ratio > 1.6 ? 200 : ($ratio >= 1 ? $box : (int) round($box * $ratio));
    $h = (int) round($w / $ratio);
    $sized = $size
        ? 'width="'.$w.'" height="'.$h.'" style="width: '.$w.'px; height: '.$h.'px;'
        : 'style="width: auto; height: auto; max-width: 200px; max-height: '.$box.'px;';
@endphp
<!--[if !mso]><!-->
<tr>
<td align="center" class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap }}px; text-align: center;">
<img src="{{ $role->getProfileImageUrl(480) }}" alt="{{ $role->name }}" {!! $sized !!} display: block; margin: 0 auto; border: 0; border-radius: {{ $nl->cardRadius }}px;">
</td>
</tr>
<!--<![endif]-->
