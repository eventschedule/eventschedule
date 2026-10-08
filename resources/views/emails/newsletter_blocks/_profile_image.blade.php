{{-- The schedule's picture in the flow of the mail. One that OPENS the mail is the masthead instead
     (the newsletter masthead component), which the shell renders. --}}
@php
    $size = $role->imageSourceDimensions();
    $ratio = $size ? $size[0] / max(1, $size[1]) : 1;
    $w = $ratio > 1.6 ? 200 : ($nl->design === 'compact' ? 72 : 120);
    $h = (int) round($w / $ratio);
@endphp
<tr>
<td align="center" class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap }}px; text-align: center;">
<img src="{{ $role->getProfileImageUrl(480) }}" width="{{ $w }}" height="{{ $h }}" alt="{{ $role->name }}" style="display: block; margin: 0 auto; width: {{ $w }}px; height: {{ $h }}px; border: 0; border-radius: {{ $nl->cardRadius }}px;">
</td>
</tr>
