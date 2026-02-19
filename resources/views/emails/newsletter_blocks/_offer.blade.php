@php
    $title = $block['data']['title'] ?? '';
    $description = $block['data']['description'] ?? '';
    $originalPrice = $block['data']['originalPrice'] ?? '';
    $salePrice = $block['data']['salePrice'] ?? '';
    $couponCode = $block['data']['couponCode'] ?? '';
    $buttonText = $block['data']['buttonText'] ?? '';
    $buttonUrl = $block['data']['buttonUrl'] ?? '#';
    $align = $block['data']['align'] ?? 'center';
    $template = $template ?? 'modern';
@endphp
@if ($title || $salePrice)
<tr>
    <td align="{{ $align }}" style="padding: 20px 30px;">
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto; border: 1px solid {{ $style['accentColor'] }}33; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 8px;' : '' }} overflow: hidden; max-width: 480px; width: 100%;">
            @if ($title)
            <tr>
                <td style="padding: 20px 24px 8px; font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 20px; font-weight: bold; color: {{ $style['textColor'] }}; text-align: {{ $align }};">
                    {{ $title }}
                </td>
            </tr>
            @endif
            @if ($description)
            <tr>
                <td style="padding: 4px 24px 12px; font-family: '{{ $style['fontFamily'] }}', sans-serif; font-size: 14px; color: {{ $style['textColor'] }}cc; text-align: {{ $align }};">
                    {{ $description }}
                </td>
            </tr>
            @endif
            @if ($originalPrice || $salePrice)
            <tr>
                <td style="padding: 8px 24px; text-align: {{ $align }}; font-family: '{{ $style['fontFamily'] }}', sans-serif;">
                    @if ($originalPrice)
                    <span style="font-size: 16px; color: {{ $style['textColor'] }}80; text-decoration: line-through;">{{ $originalPrice }}</span>
                    <span style="display: inline-block; width: 8px;"></span>
                    @endif
                    @if ($salePrice)
                    <span style="font-size: 24px; font-weight: bold; color: {{ $style['accentColor'] }};">{{ $salePrice }}</span>
                    @endif
                </td>
            </tr>
            @endif
            @if ($couponCode)
            <tr>
                <td style="padding: 12px 24px; text-align: {{ $align }};">
                    <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                        <tr>
                            <td style="padding: 8px 20px; border: 2px dashed {{ $style['accentColor'] }}; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 6px;' : '' }} font-family: 'Courier New', Courier, monospace; font-size: 18px; font-weight: bold; color: {{ $style['accentColor'] }}; letter-spacing: 2px; text-align: center;">
                                {{ $couponCode }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endif
            @if ($buttonText)
            <tr>
                <td align="{{ $align }}" style="padding: 16px 24px 20px;">
                    <a href="{{ $buttonUrl }}" style="display: inline-block; padding: 12px 24px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: bold; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 6px;' : '' }} font-family: '{{ $style['fontFamily'] }}', sans-serif;">{{ $buttonText }}</a>
                </td>
            </tr>
            @endif
        </table>
    </td>
</tr>
@endif
