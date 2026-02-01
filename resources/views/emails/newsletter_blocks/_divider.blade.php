@php
    $dividerStyle = $block['data']['style'] ?? 'solid';
    $template = $template ?? 'modern';
@endphp
@if ($template === 'classic')
<tr>
    <td style="padding: 10px 30px;">
        <hr style="border: none; border-top: 3px double #ccc; margin: 0;" />
    </td>
</tr>
@elseif ($template === 'minimal')
<tr>
    <td style="padding: 10px 30px;">
        <hr style="border: none; border-top: 1px solid #eee; margin: 0;" />
    </td>
</tr>
@elseif ($template === 'bold')
<tr>
    <td style="padding: 10px 30px;">
        <hr style="border: none; border-top: 2px solid {{ $style['accentColor'] }}; margin: 0;" />
    </td>
</tr>
@elseif ($template === 'compact')
<tr>
    <td style="padding: 6px 30px;">
        <hr style="border: none; border-top: 1px dotted #ccc; margin: 0;" />
    </td>
</tr>
@else
{{-- Modern (default) --}}
<tr>
    <td style="padding: 10px 30px;">
        <hr style="border: none; border-top: 1px {{ $dividerStyle }} #ddd; margin: 0;" />
    </td>
</tr>
@endif
