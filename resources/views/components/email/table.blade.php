{{--
    A real data table (a payment schedule, an overdue list), so it keeps its semantics: th with
    scope, no role="presentation". A cell is a string, or [text, tone] to colour it. align is one
    'start' or 'end' per column.
--}}
@aware(['theme' => null])
@props(['head' => [], 'rows' => [], 'align' => []])
@php
    $theme ??= \App\Utils\EmailTheme::account();
    $side = fn ($i) => ($align[$i] ?? 'start') === 'end' ? $theme->end : $theme->start;
@endphp
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 24px;">
<table dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse;">
@if ($head)
<tr>
@foreach ($head as $i => $label)
<th scope="col" class="es-rule es-ink-3" style="padding: 0 0 8px;{{ $i ? ' padding-'.$theme->start.': 12px;' : '' }} border-bottom: 1px solid #e2e8f0; text-align: {{ $side($i) }}; font-size: 12px; line-height: 16px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #64748b;">{{ $label }}</th>
@endforeach
</tr>
@endif
@foreach ($rows as $row)
<tr>
@foreach (array_values($row) as $i => $cell)
@php
    [$text, $tone] = is_array($cell) ? [$cell[0] ?? '', $cell[1] ?? null] : [$cell, null];
    $tone = $tone ? \App\Utils\EmailTheme::toneName($tone) : null;
    $colour = $tone ? $theme->tone($tone)['ink'] : '#334155';
@endphp
<td class="es-rule {{ $tone ? 'es-ink-'.$tone : 'es-ink-2' }}" style="padding: 10px 0;{{ $i ? ' padding-'.$theme->start.': 12px;' : '' }} border-bottom: 1px solid #e2e8f0; text-align: {{ $side($i) }}; font-size: 14px; line-height: 20px; color: {{ $colour }}; font-variant-numeric: tabular-nums;{{ $tone ? ' font-weight: 600;' : '' }}">{{ $text }}</td>
@endforeach
</tr>
@endforeach
</table>
</td>
</tr>
</table>
