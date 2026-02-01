@php
    $height = $block['data']['height'] ?? 20;
@endphp
<tr>
    <td style="height: {{ (int)$height }}px; line-height: {{ (int)$height }}px; font-size: 1px;">&nbsp;</td>
</tr>
