@php
    $level = $block['data']['level'] ?? 'h1';
    $align = $block['data']['align'] ?? 'center';
    $text = $block['data']['text'] ?? '';
    $fontSize = match($level) { 'h1' => '22px', 'h2' => '18px', 'h3' => '16px', default => '22px' };
    $template = $template ?? 'modern';
@endphp
@if ($text)
@if ($template === 'classic')
<tr>
    <td style="padding: 24px 30px {{ $align === 'center' ? '20px' : '20px' }} 30px; text-align: {{ $align }};">
        <{{ $level }} style="margin: 0 0 8px 0; font-size: {{ $fontSize }}; color: {{ $style['accentColor'] }}; font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }}</{{ $level }}>
        <div style="border-bottom: 2px solid {{ $style['accentColor'] }}; width: 60px; {{ $align === 'center' ? 'margin: 0 auto;' : ($align === 'right' ? 'margin: 0 0 0 auto;' : 'margin: 0;') }}"></div>
    </td>
</tr>
@elseif ($template === 'minimal')
<tr>
    <td style="padding: 14px 30px; text-align: {{ $align }};">
        <{{ $level }} style="margin: 0; font-size: 13px; color: #888; font-family: {{ $style['fontFamily'] }}, sans-serif; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;">{{ $text }}</{{ $level }}>
    </td>
</tr>
@elseif ($template === 'bold')
@php
    $boldFontSize = match($level) { 'h1' => '26px', 'h2' => '22px', 'h3' => '18px', default => '26px' };
@endphp
<tr>
    <td style="background-color: {{ $style['accentColor'] }}; padding: 30px; text-align: {{ $align }};">
        <{{ $level }} style="margin: 0; font-size: {{ $boldFontSize }}; color: #ffffff; font-family: {{ $style['fontFamily'] }}, sans-serif; font-weight: 800;">{{ $text }}</{{ $level }}>
    </td>
</tr>
@elseif ($template === 'compact')
<tr>
    <td style="padding: 8px 30px 8px 30px; text-align: left;">
        <div style="border-left: 4px solid {{ $style['accentColor'] }}; padding-left: 12px;">
            <{{ $level }} style="margin: 0; font-size: 15px; color: {{ $style['textColor'] }}; font-family: {{ $style['fontFamily'] }}, sans-serif; font-weight: 700;">{{ $text }}</{{ $level }}>
        </div>
    </td>
</tr>
@else
{{-- Modern (default) --}}
<tr>
    <td style="background-color: {{ $style['accentColor'] }}; padding: 24px 30px; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 8px;' : '' }} text-align: {{ $align }};">
        <{{ $level }} style="margin: 0; font-size: {{ $fontSize }}; color: #ffffff; font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }}</{{ $level }}>
    </td>
</tr>
@endif
@endif
