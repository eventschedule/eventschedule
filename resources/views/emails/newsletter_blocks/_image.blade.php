@php
    $align = $block['data']['align'] ?? 'center';
    $align = in_array($align, ['left', 'center', 'right'], true) ? $align : 'center';
    $width = $block['data']['width'] ?? '100%';
    $layout = $block['data']['layout'] ?? 'column';
    $inner = $nl->inner;

    // Normalize: convert old format to images array
    if (isset($block['data']['url'])) {
        $images = [['url' => $block['data']['url'], 'alt' => $block['data']['alt'] ?? '', 'caption' => '', 'link' => '']];
    } else {
        $images = $block['data']['images'] ?? [];
    }

    $images = array_values(array_filter($images, fn ($img) => ! empty($img['url'])));

    // A link is client-built JSON that nothing validates as one, shown in the builder's preview
    // too: linked only through safeActionHref(), and without a safe href the image shows unlinked.
    $images = array_map(fn ($img) => array_merge($img, ['href' => \App\Utils\UrlUtils::safeActionHref($img['link'] ?? null)]), $images);

    if (! preg_match('/^\d+(px|%)?$/', $width)) {
        $width = '100%';
    }

    // The attribute is what Outlook sizes by, so it is the column's width and never more.
    $attr = str_ends_with($width, '%') ? (int) round($inner * min(100, (int) $width) / 100) : min($inner, (int) $width);
    $caption = 'margin: 8px 0 0; '.$nl->smallType();
    $imgStyle = 'height: auto; display: block; border: 0; border-radius: '.$nl->cardRadius.'px;';
    $margin = $align === 'center' ? 'margin: 0 auto;' : ($align === 'right' ? 'margin: 0 0 0 auto;' : '');
    $perRow = $layout === 'grid' ? 2 : max(1, count($images));
@endphp
@if (count($images) === 0)
@elseif (count($images) === 1 || $layout === 'column')
@foreach ($images as $img)
<tr>
<td align="{{ $align }}" class="nl-g" style="padding: 0 {{ $nl->gutter }}px {{ $loop->last ? $nl->gap : 14 }}px; text-align: {{ $align }};">
@if ($img['href'])<a href="{{ $img['href'] }}" target="_blank" rel="noopener" style="text-decoration: none;">@endif
<img src="{{ $img['url'] }}" alt="{{ $img['alt'] ?? '' }}" width="{{ $attr }}" style="width: {{ $width }}; max-width: 100%; {{ $imgStyle }} {{ $margin }}">
@if ($img['href'])</a>@endif
@if (! empty($img['caption']))
<p dir="auto" style="{{ $caption }} text-align: {{ $align }};">{{ $img['caption'] }}</p>
@endif
</td>
</tr>
@endforeach
@else
{{-- Side by side. Every cell is the same share of the row with the same 5px each side, and the
     row itself is pulled 5px wider each side, so the pictures are equal at any width and their
     outer edges still meet the gutter. Captions are a row of their own, so three pictures of
     different heights have their captions on one line. --}}
@php
    $cell = (int) floor(($inner + 10) / $perRow) - 10;
    $share = round(100 / $perRow, 2);
@endphp
@foreach (array_chunk($images, $perRow) as $row)
@php
    $hasCaptions = (bool) array_filter(array_column($row, 'caption'));
@endphp
<tr>
<td class="nl-g5" style="padding: 0 {{ $nl->gutter - 5 }}px {{ $loop->last ? $nl->gap : 10 }}px;">
<table role="presentation" dir="{{ $nl->dir }}" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
@foreach ($row as $img)
<td width="{{ $share }}%" valign="top" style="width: {{ $share }}%; vertical-align: top; padding: 0 5px;">
@if ($img['href'])<a href="{{ $img['href'] }}" target="_blank" rel="noopener" style="text-decoration: none;">@endif
<img src="{{ $img['url'] }}" alt="{{ $img['alt'] ?? '' }}" width="{{ $cell }}" style="width: 100%; max-width: {{ $cell }}px; {{ $imgStyle }}">
@if ($img['href'])</a>@endif
</td>
@endforeach
@for ($i = count($row); $i < $perRow; $i++)
<td width="{{ $share }}%" style="width: {{ $share }}%; padding: 0 5px;">&nbsp;</td>
@endfor
</tr>
@if ($hasCaptions)
<tr>
@foreach ($row as $img)
<td valign="top" style="vertical-align: top; padding: 0 5px;"><p dir="auto" style="{{ $caption }} text-align: center;">{{ $img['caption'] ?? '' }}</p></td>
@endforeach
@for ($i = count($row); $i < $perRow; $i++)
<td></td>
@endfor
</tr>
@endif
</table>
</td>
</tr>
@endforeach
@endif
