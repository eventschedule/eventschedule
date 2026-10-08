{{--
    One action in a newsletter. solid is the accent fill; outline is Classic's; link is Minimal's
    text link with an arrow. Bulletproof the way the transactional mail's button is: the cell
    carries the colour and Outlook's padding, the link carries everyone else's. fluid makes it the
    width of a phone. With no href it still reads, unlinked: a block's link is JSON the builder
    posts, and the block drops one that is not safe before it gets here.

    A component and not an include on purpose: an include inherits its parent's variables, and
    several blocks have an align, a size or a width of their own.
--}}
@props(['nl', 'label', 'href' => null, 'variant' => 'solid', 'size' => 'md', 'align' => 'center', 'fluid' => false])
@php
    $align = in_array($align, ['left', 'center', 'right'], true) ? $align : 'center';
    [$fs, $lh, $py, $px] = ['sm' => [14, 18, 9, 18], 'lg' => [17, 22, 17, 36]][$size] ?? [16, 20, 13, 28];
    $fill = $variant === 'solid';
    $labelStyle = 'display: inline-block; padding: '.$py.'px '.$px.'px; '.$nl->type($fs, $lh, $fill ? $nl->onAccent : $nl->accentInk, 700).' text-decoration: none; border-radius: '.$nl->radius.'px; mso-padding-alt: 0;';
@endphp
@if ($variant === 'link')
<a @if ($href) href="{{ $href }}" target="_blank" rel="noopener" @endif style="{{ $nl->type($fs, $lh + 4, $nl->accentInk, 600) }} text-decoration: underline;">{{ $label }}&nbsp;{!! $nl->arrow !!}</a>
@else
{{-- The side is said twice, as an attribute for Outlook and as margins for everyone else, and
     for every side: with nothing said, "left" was wherever the mail's direction starts. --}}
<table role="presentation" dir="{{ $nl->dir }}" align="{{ $align }}" cellpadding="0" cellspacing="0" border="0" @if ($fluid) class="nl-btn-wrap" @endif style="margin: 0 {{ $align === 'right' ? '0' : 'auto' }} 0 {{ $align === 'left' ? '0' : 'auto' }};">
<tr>
<td align="center" bgcolor="{{ $fill ? $nl->accent : $nl->sheet }}" style="background-color: {{ $fill ? $nl->accent : 'transparent' }}; border: {{ $fill ? '1px solid '.$nl->accentEdge : '2px solid '.$nl->accentInk }}; border-radius: {{ $nl->radius }}px; mso-padding-alt: {{ $py }}px {{ $px }}px; text-align: center;">
<a @if ($href) href="{{ $href }}" target="_blank" rel="noopener" @endif class="nl-btn-a" style="{{ $labelStyle }}">{{ $label }}</a>
</td>
</tr>
</table>
@endif
