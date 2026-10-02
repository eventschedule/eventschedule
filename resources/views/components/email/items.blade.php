{{--
    Line items: what was bought, with an optional total.

    rows is a list of ['name' => ..., 'caption' => ..., 'url' => ..., 'value' => ...]; caption
    carries seat labels or a pass's terms, url a link the item comes with (an add-on's page), value
    a quantity or an amount. Amounts use tabular figures so a column of prices lines up.
--}}
@aware(['theme' => null])
@props(['rows' => [], 'total' => null, 'totalLabel' => null])
@php($theme ??= \App\Utils\EmailTheme::account())
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
<td style="padding: 0 0 24px;">
<table role="presentation" dir="{{ $theme->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach ($rows as $row)
@php($rule = $loop->first ? '' : ' border-top: 1px solid #e2e8f0;')
<tr>
<td class="es-rule" valign="top" style="padding: 12px 0;{{ $rule }} text-align: {{ $theme->start }}; vertical-align: top;">
<p class="es-ink" dir="auto" style="margin: 0; font-size: 16px; line-height: 24px; font-weight: 600; color: #0f172a; word-break: break-word;">{{ $row['name'] ?? '' }}</p>
@if (filled($row['caption'] ?? null))
<p class="es-ink-3" style="margin: 2px 0 0; font-size: 14px; line-height: 20px; color: #64748b;">{{ $row['caption'] }}</p>
@endif
@if (filled($row['url'] ?? null))
<p style="margin: 2px 0 0; font-size: 14px; line-height: 20px; word-break: break-all;"><a href="{{ $row['url'] }}" target="_blank" rel="noopener" dir="ltr" class="es-link" style="color: {{ $theme->accentInk }}; text-decoration: underline;">{{ $row['url'] }}</a></p>
@endif
</td>
<td class="es-rule es-ink-2" valign="top" style="padding: 12px 0; padding-{{ $theme->start }}: 16px;{{ $rule }} text-align: {{ $theme->end }}; vertical-align: top; white-space: nowrap; font-size: 16px; line-height: 24px; color: #334155; font-variant-numeric: tabular-nums;">{{ $row['value'] ?? '' }}</td>
</tr>
@endforeach
@if (filled($total))
<tr>
<td class="es-rule es-ink" style="padding: 14px 0 0; border-top: 1px solid #cbd5e1; text-align: {{ $theme->start }}; font-size: 16px; line-height: 24px; font-weight: 700; color: #0f172a;">{{ $totalLabel }}</td>
<td class="es-rule es-ink" style="padding: 14px 0 0; padding-{{ $theme->start }}: 16px; border-top: 1px solid #cbd5e1; text-align: {{ $theme->end }}; white-space: nowrap; font-size: 18px; line-height: 24px; font-weight: 700; color: #0f172a; font-variant-numeric: tabular-nums;">{{ $total }}</td>
</tr>
@endif
</table>
</td>
</tr>
</table>
