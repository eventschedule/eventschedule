@php
    $height = max(0, min(200, (int) ($block['data']['height'] ?? 20)));
@endphp
<tr>
<td style="height: {{ $height }}px; line-height: {{ $height }}px; font-size: 1px;">&nbsp;</td>
</tr>
