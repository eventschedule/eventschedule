@php
    $url = $block['data']['url'] ?? '';
    $alt = $block['data']['alt'] ?? '';
    $width = $block['data']['width'] ?? '100%';
    $align = $block['data']['align'] ?? 'center';
@endphp
@if ($url)
<tr>
    <td align="{{ $align }}" style="padding: 10px 30px;">
        <img src="{{ $url }}" alt="{{ $alt }}" style="max-width: 600px; width: {{ $width }}; height: auto; display: block;" />
    </td>
</tr>
@endif
