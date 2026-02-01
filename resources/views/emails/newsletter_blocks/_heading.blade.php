@php
    $level = $block['data']['level'] ?? 'h1';
    $align = $block['data']['align'] ?? 'center';
    $text = $block['data']['text'] ?? '';
    $fontSize = match($level) { 'h1' => '22px', 'h2' => '18px', 'h3' => '16px', default => '22px' };
@endphp
@if ($text)
<tr>
    <td style="background-color: {{ $style['accentColor'] }}; padding: 24px 30px; {{ $style['buttonRadius'] === 'rounded' ? 'border-radius: 8px;' : '' }} text-align: {{ $align }};">
        <{{ $level }} style="margin: 0; font-size: {{ $fontSize }}; color: #ffffff; font-family: {{ $style['fontFamily'] }}, sans-serif;">{{ $text }}</{{ $level }}>
    </td>
</tr>
@endif
