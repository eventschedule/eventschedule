{{-- An offer: a tinted card, the price, the code in a dashed box, one button. --}}
@php
    $title = $block['data']['title'] ?? '';
    $description = $block['data']['description'] ?? '';
    $originalPrice = $block['data']['originalPrice'] ?? '';
    $salePrice = $block['data']['salePrice'] ?? '';
    $couponCode = $block['data']['couponCode'] ?? '';
    $buttonText = $block['data']['buttonText'] ?? '';
    // Client-built JSON that nothing validates as a link, shown in the builder's preview too:
    // linked only through safeActionHref(), and without a safe href the button still reads,
    // unlinked.
    $buttonUrl = \App\Utils\UrlUtils::safeActionHref($block['data']['buttonUrl'] ?? null);
    $align = $block['data']['align'] ?? 'center';
    $align = in_array($align, ['left', 'center', 'right'], true) ? $align : 'center';
    $compact = $nl->design === 'compact';
    $flat = $nl->design === 'minimal';
@endphp
@if ($title || $salePrice)
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap }}px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" @if (! $flat) bgcolor="{{ $nl->accentTint }}" @endif style="{{ $flat ? 'border: 1px solid '.$nl->ruleStrong.';' : 'background-color: '.$nl->accentTint.';' }} border-radius: {{ $nl->cardRadius }}px;">
<tr>
<td align="{{ $align }}" style="padding: {{ $compact ? '16px 18px' : '30px 28px' }}; text-align: {{ $align }};">
@if ($title)
<p dir="auto" style="margin: 0; {{ $nl->type($compact ? 17 : 24, $compact ? 23 : 30, $nl->ink, $nl->design === 'bold' ? 800 : ($nl->serif ? 400 : 700)) }} letter-spacing: -0.01em;">{{ $title }}</p>
@endif
@if ($description)
<p dir="auto" style="margin: 6px 0 0; {{ $nl->bodyType() }}">{{ $description }}</p>
@endif
@if ($originalPrice || $salePrice)
<p style="margin: {{ $compact ? 10 : 16 }}px 0 0; font-family: {{ $nl->font }};">
@if ($originalPrice)
<span style="font-size: {{ $compact ? 14 : 18 }}px; line-height: {{ $compact ? 24 : 38 }}px; color: {{ $nl->ink3 }}; text-decoration: line-through;">{{ $originalPrice }}</span>&nbsp;&nbsp;
@endif
@if ($salePrice)
<span style="font-size: {{ $compact ? 22 : 34 }}px; line-height: {{ $compact ? 24 : 38 }}px; font-weight: 800; color: {{ $nl->accentInk }};">{{ $salePrice }}</span>
@endif
</p>
@endif
@if ($couponCode)
<table role="presentation" @if ($align !== 'left') align="{{ $align }}" @endif cellpadding="0" cellspacing="0" border="0" style="margin: {{ $compact ? 10 : 16 }}px {{ $align === 'center' ? 'auto' : '0' }} 0;">
<tr>
<td bgcolor="{{ $nl->sheet }}" dir="ltr" style="padding: {{ $compact ? '6px 14px' : '10px 22px' }}; background-color: {{ $nl->sheet }}; border: 2px dashed {{ $nl->accentInk }}; border-radius: {{ $nl->radius }}px; font-family: {{ \App\Utils\NewsletterTheme::MONO }}; font-size: {{ $compact ? 15 : 19 }}px; line-height: 24px; font-weight: 700; color: {{ $nl->ink }}; letter-spacing: 0.14em; text-align: center;">{{ $couponCode }}</td>
</tr>
</table>
@endif
@if ($buttonText)
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="{{ $align }}" style="padding: {{ $compact ? 12 : 22 }}px 0 0; text-align: {{ $align }};">
<x-newsletter.button :nl="$nl" :label="$buttonText" :href="$buttonUrl" :align="$align" :fluid="true" :variant="in_array($nl->design, ['classic', 'minimal'], true) ? 'outline' : 'solid'" :size="$compact ? 'sm' : 'md'" />
</td>
</tr>
</table>
@endif
</td>
</tr>
</table>
</td>
</tr>
@endif
