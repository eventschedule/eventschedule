{{--
    A heading. Each design has ONE title treatment, for h1, a plain heading for h2 and a small label
    for h3, in that order of weight in all five: while every level was an accent slab (Modern,
    Bold) or every level a grey label (Minimal), a mail had three titles or none.
--}}
@php
    // A block saved before a setting existed has no key for it.
    $level = $block['data']['level'] ?? 'h1';
    $level = in_array($level, ['h1', 'h2', 'h3'], true) ? $level : 'h1';
    $align = $block['data']['align'] ?? 'center';
    $align = in_array($align, ['left', 'center', 'right'], true) ? $align : 'center';
    // Compact has one axis, its leading edge, and its headings have always kept to it whatever
    // the block's own alignment says.
    $align = $nl->design === 'compact' ? $nl->start : $align;
    $text = trim((string) ($block['data']['text'] ?? ''));
    $band = $level === 'h1' && in_array($nl->design, ['modern', 'bold'], true);
    $barMargin = $align === 'center' ? '0 auto' : ($align === 'right' ? '0 0 0 auto' : '0');
    $tight = (int) round($nl->gap * 0.45);
@endphp
@if ($text !== '')
@if ($band)
@php
    $bold = $nl->design === 'bold';
    $corner = ($first && $nl->sheetRadius > 0) ? 'border-radius: '.($nl->sheetRadius - 1).'px '.($nl->sheetRadius - 1).'px 0 0;' : '';
@endphp
<tr>
<td class="nl-g nl-band{{ $corner ? ' nl-round' : '' }}" bgcolor="{{ $nl->accent }}" style="background-color: {{ $nl->accent }}; padding: {{ $bold ? 44 : 38 }}px {{ $nl->gutter }}px {{ $bold ? 40 : 34 }}px; text-align: {{ $align }}; {{ $corner }}">
<h1 class="nl-h1" dir="auto" style="margin: 0; {{ $nl->type($bold ? 36 : 30, $bold ? 40 : 36, $nl->onAccent, $bold ? 800 : 700) }} letter-spacing: -0.02em; text-wrap: balance;">{{ $text }}</h1>
</td>
</tr>
@if (! $beforeBleed)
<tr>
<td style="height: {{ $nl->top }}px; line-height: {{ $nl->top }}px; font-size: 0;">&nbsp;</td>
</tr>
@endif
@else
<tr>
<td class="nl-g" style="padding: {{ $level === 'h1' ? 0 : 4 }}px {{ $nl->gutter }}px {{ $level === 'h1' ? $nl->gap : $tight }}px; text-align: {{ $align }};">
@switch($nl->design.'-'.$level)
@case('modern-h2')
@case('bold-h2')
<h2 dir="auto" style="margin: 0; {{ $nl->type($nl->design === 'bold' ? 27 : 22, $nl->design === 'bold' ? 32 : 30, $nl->ink, $nl->design === 'bold' ? 800 : 700) }} letter-spacing: -0.01em;">{{ $text }}</h2>
@if ($nl->design === 'bold')
<div style="margin: 10px {{ $align === 'center' ? 'auto' : '0' }} 0 {{ $align === 'left' ? '0' : 'auto' }}; width: 44px; height: 4px; line-height: 4px; font-size: 0; background-color: {{ $nl->accent }};">&nbsp;</div>
@endif
@break
@case('classic-h1')
<h1 class="nl-h1" dir="auto" style="margin: 0 0 14px; {{ $nl->type(34, 42, $nl->accentInk, 400) }} letter-spacing: -0.01em; text-wrap: balance;">{{ $text }}</h1>
<div style="margin: {{ $barMargin }}; width: 56px; height: 2px; line-height: 2px; font-size: 0; background-color: {{ $nl->accentInk }};">&nbsp;</div>
@break
@case('classic-h2')
<h2 dir="auto" style="margin: 0; padding: 0 0 8px; border-bottom: 1px solid {{ $nl->rule }}; {{ $nl->type(23, 30, $nl->ink, 400) }}">{{ $text }}</h2>
@break
@case('minimal-h1')
<h1 class="nl-h1" dir="auto" style="margin: 0; {{ $nl->type(32, 38, $nl->ink, 600) }} letter-spacing: -0.02em; text-wrap: balance;">{{ $text }}</h1>
@break
@case('minimal-h2')
<h2 dir="auto" style="margin: 0; padding: 20px 0 0; border-top: 1px solid {{ $nl->rule }}; {{ $nl->type(21, 27, $nl->ink, 600) }} letter-spacing: -0.01em;">{{ $text }}</h2>
@break
@case('minimal-h3')
<h3 dir="auto" style="margin: 0; {{ $nl->labelType($nl->ink3) }}">{{ $text }}</h3>
@break
@case('compact-h1')
<h1 dir="auto" style="margin: 0; {{ $nl->type(21, 27, $nl->ink, 700) }}">{{ $text }}</h1>
@break
@case('compact-h2')
<h2 dir="auto" style="margin: 0; padding: 0 0 6px; border-bottom: 1px solid {{ $nl->rule }}; {{ $nl->type(16, 22, $nl->ink, 700) }}">{{ $text }}</h2>
@break
@default
<{{ $level }} dir="auto" style="margin: 0; {{ $nl->labelType(null, 13) }}">{{ $text }}</{{ $level }}>
@endswitch
</td>
</tr>
@endif
@endif
