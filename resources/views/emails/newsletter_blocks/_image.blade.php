@php
    $url = $block['data']['url'] ?? '';
    $alt = $block['data']['alt'] ?? '';
    $width = $block['data']['width'] ?? '100%';
    $align = $block['data']['align'] ?? 'center';
    $template = $template ?? 'modern';
@endphp
@if ($url)
@if ($template === 'classic')
<tr>
    <td align="{{ $align }}" style="padding: 10px 30px;">
        <img src="{{ $url }}" alt="{{ $alt }}" style="max-width: 600px; width: {{ $width }}; height: auto; display: block; border: 1px solid #ddd;" />
    </td>
</tr>
@elseif ($template === 'bold')
<tr>
    <td align="{{ $align }}" style="padding: 10px 30px;">
        <img src="{{ $url }}" alt="{{ $alt }}" style="max-width: 600px; width: {{ $width }}; height: auto; display: block; border-radius: 8px;" />
    </td>
</tr>
@else
<tr>
    <td align="{{ $align }}" style="padding: 10px 30px;">
        <img src="{{ $url }}" alt="{{ $alt }}" style="max-width: 600px; width: {{ $width }}; height: auto; display: block;" />
    </td>
</tr>
@endif
@endif
