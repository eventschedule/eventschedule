{{-- The schedule's banner: edge to edge where the design has an edge (a sheet, or Bold's full
     page), and in line with the text in Minimal and Compact. The shell leaves this block out when
     the schedule has no banner. --}}
@php
    $inset = in_array($nl->design, ['minimal', 'compact'], true);
    $corner = $inset ? $nl->cardRadius.'px' : (($first && $nl->sheetRadius > 0) ? ($nl->sheetRadius - 1).'px '.($nl->sheetRadius - 1).'px 0 0' : '0');
    $w = $inset ? $nl->inner : 600;
@endphp
<tr>
<td @if ($inset) class="nl-g" @endif style="padding: {{ $inset ? '0 '.$nl->gutter.'px '.$nl->gap.'px' : '0' }}; font-size: 0; line-height: 0;">
<img src="{{ $role->header_image_url }}" width="{{ $w }}" alt="{{ $role->name }}" class="{{ ! $inset && $corner !== '0' ? 'nl-round' : '' }}" style="display: block; width: 100%; max-width: {{ $w }}px; height: auto; border: 0; border-radius: {{ $corner }};">
</td>
</tr>
@if (! $inset && ! $beforeBleed)
<tr>
<td style="height: {{ $nl->top }}px; line-height: {{ $nl->top }}px; font-size: 0;">&nbsp;</td>
</tr>
@endif
