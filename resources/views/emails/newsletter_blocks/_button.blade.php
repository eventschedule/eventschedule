@php
    $text = $block['data']['text'] ?? '';
    $url = $block['data']['url'] ?? '#';
    $align = $block['data']['align'] ?? 'center';
@endphp
@if ($text)
<tr>
    <td align="{{ $align }}" style="padding: 20px 30px;">
        <a href="{{ $url }}" style="display: inline-block; padding: 12px 24px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: bold; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 6px;' : '' }} font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }}</a>
    </td>
</tr>
@endif
