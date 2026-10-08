@php
    $line = $block['data']['style'] ?? 'solid';
    $line = in_array($line, ['solid', 'dashed', 'dotted'], true) ? $line : 'solid';
    $border = match ($nl->design) {
        'classic' => '1px solid '.$nl->ruleDouble,
        'bold' => '2px '.$line.' '.$nl->accentMark,
        'compact' => '1px '.($line === 'solid' ? 'dotted' : $line).' '.$nl->ruleStrong,
        default => '1px '.$line.' '.$nl->rule,
    };
@endphp
<tr>
<td class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap }}px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="border-top: {{ $border }};{{ $nl->design === 'classic' ? ' border-bottom: '.$border.'; height: 3px; line-height: 3px;' : ' height: 1px; line-height: 1px;' }} font-size: 0;">&nbsp;</td>
</tr>
</table>
</td>
</tr>
