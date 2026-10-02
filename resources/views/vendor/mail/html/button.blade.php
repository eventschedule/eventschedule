{{-- Bulletproof without the 18px side-border trick: the cell carries the colour and Outlook's padding. --}}
@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
@php($bg = match ($color) { 'success', 'green' => '#15803d', 'error', 'red' => '#b91c1c', default => '#3d6fe8' })
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table class="button-table" align="{{ $align }}" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center" bgcolor="{{ $bg }}" style="border-radius: 10px; background-color: {{ $bg }}; mso-padding-alt: 14px 28px;">
<a href="{{ $url }}" class="button" target="_blank" rel="noopener" style="background-color: {{ $bg }};">{{ $slot }}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
