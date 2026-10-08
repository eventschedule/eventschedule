@php
    $text = $block['data']['text'] ?? '';
    // Client-built JSON that nothing validates as a link, shown in the builder's preview too:
    // linked only through safeActionHref(), and without a safe href the button still reads,
    // unlinked.
    $url = \App\Utils\UrlUtils::safeActionHref($block['data']['url'] ?? null);
    $align = $block['data']['align'] ?? 'center';
    $align = in_array($align, ['left', 'center', 'right'], true) ? $align : 'center';
@endphp
@if ($text)
<tr>
<td align="{{ $align }}" class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $nl->gap }}px; text-align: {{ $align }};">
<x-newsletter.button :nl="$nl" :label="$text" :href="$url" :align="$align" :fluid="true"
    :variant="['classic' => 'outline', 'minimal' => 'link'][$nl->design] ?? 'solid'"
    :size="['bold' => 'lg', 'compact' => 'sm'][$nl->design] ?? 'md'" />
</td>
</tr>
@endif
