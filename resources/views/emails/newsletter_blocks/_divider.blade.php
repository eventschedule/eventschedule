@php
    $dividerStyle = $block['data']['style'] ?? 'solid';
    $template = $template ?? 'modern';
@endphp
@if ($template === 'classic')
<tr>
    <td style="padding: 10px 30px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top: 3px double #ccc; font-size: 0; line-height: 0;" height="1">&nbsp;</td></tr></table>
    </td>
</tr>
@elseif ($template === 'minimal')
<tr>
    <td style="padding: 10px 30px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top: 1px solid #eee; font-size: 0; line-height: 0;" height="1">&nbsp;</td></tr></table>
    </td>
</tr>
@elseif ($template === 'bold')
<tr>
    <td style="padding: 10px 30px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top: 2px solid {{ $style['accentColor'] }}; font-size: 0; line-height: 0;" height="1">&nbsp;</td></tr></table>
    </td>
</tr>
@elseif ($template === 'compact')
<tr>
    <td style="padding: 6px 30px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top: 1px dotted #ccc; font-size: 0; line-height: 0;" height="1">&nbsp;</td></tr></table>
    </td>
</tr>
@else
{{-- Modern (default) --}}
<tr>
    <td style="padding: 10px 30px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top: 1px {{ $dividerStyle }} #ddd; font-size: 0; line-height: 0;" height="1">&nbsp;</td></tr></table>
    </td>
</tr>
@endif
