@php
    $text = $block['data']['text'] ?? '';
    $url = $block['data']['url'] ?? '#';
    $align = $block['data']['align'] ?? 'center';
    $template = $template ?? 'modern';
@endphp
@if ($text)
@if ($template === 'classic')
<tr>
    <td align="{{ $align }}" style="padding: 20px 30px;">
        <a href="{{ $url }}" style="display: inline-block; padding: 12px 24px; background-color: transparent; color: {{ $style['accentColor'] }}; text-decoration: none; font-size: 15px; font-weight: bold; border: 2px solid {{ $style['accentColor'] }}; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 6px;' : '' }} font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }}</a>
    </td>
</tr>
@elseif ($template === 'minimal')
<tr>
    <td align="{{ $align }}" style="padding: 14px 30px;">
        <a href="{{ $url }}" style="color: {{ $style['accentColor'] }}; text-decoration: underline; font-size: 14px; font-weight: 600; font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }} &rarr;</a>
    </td>
</tr>
@elseif ($template === 'bold')
<tr>
    <td align="{{ $align }}" style="padding: 20px 30px;">
        <a href="{{ $url }}" style="display: inline-block; padding: 16px 32px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 17px; font-weight: bold; border-radius: 6px; font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }}</a>
    </td>
</tr>
@elseif ($template === 'compact')
<tr>
    <td align="{{ $align }}" style="padding: 10px 30px;">
        <a href="{{ $url }}" style="display: inline-block; padding: 8px 16px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: bold; font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }}</a>
    </td>
</tr>
@else
{{-- Modern (default) --}}
<tr>
    <td align="{{ $align }}" style="padding: 20px 30px;">
        <a href="{{ $url }}" style="display: inline-block; padding: 12px 24px; background-color: {{ $style['accentColor'] }}; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: bold; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 6px;' : '' }} font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }}</a>
    </td>
</tr>
@endif
@endif
