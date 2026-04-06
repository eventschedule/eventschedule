@php
    $template = $template ?? 'modern';
    $align = $block['data']['align'] ?? 'center';
    $width = $block['data']['width'] ?? '100%';
    $layout = $block['data']['layout'] ?? 'column';

    // Normalize: convert old format to images array
    if (isset($block['data']['url'])) {
        $images = [['url' => $block['data']['url'], 'alt' => $block['data']['alt'] ?? '', 'caption' => '', 'link' => '']];
    } else {
        $images = $block['data']['images'] ?? [];
    }

    // Filter to only images with URLs
    $images = array_values(array_filter($images, fn($img) => !empty($img['url'])));

    // Validate width
    if (!preg_match('/^\d+(px|%)?$/', $width)) {
        $width = '100%';
    }

    // Template-specific image style
    $imgExtra = '';
    if ($template === 'classic') $imgExtra = 'border: 1px solid #ddd;';
    elseif ($template === 'bold') $imgExtra = 'border-radius: 8px;';
@endphp
@if (count($images) === 0)
    {{-- Nothing to render --}}
@elseif (count($images) === 1 || $layout === 'column')
    {{-- Single image or column layout: stack vertically --}}
    @foreach ($images as $img)
    <tr>
        <td align="{{ $align }}" style="padding: 10px 30px;">
            @if (!empty($img['link']))<a href="{{ $img['link'] }}" style="text-decoration: none; color: inherit;">@endif
            <img src="{{ $img['url'] }}" alt="{{ $img['alt'] ?? '' }}" width="600" style="max-width: 600px; width: {{ $width }}; height: auto; display: block; {{ $imgExtra }}" />
            @if (!empty($img['link']))</a>@endif
            @if (!empty($img['caption']))
            <p style="margin: 6px 0 0 0; font-size: 13px; color: {{ $style['textColor'] }}cc; font-family: '{{ $style['fontFamily'] }}', sans-serif; text-align: {{ $align }};">{{ $img['caption'] }}</p>
            @endif
        </td>
    </tr>
    @endforeach
@elseif ($layout === 'row')
    {{-- Row layout: all images side by side --}}
    @php
        $colCount = count($images);
        $tdWidth = floor(100 / $colCount);
        $pixelWidth = floor(540 / $colCount);
    @endphp
    <tr>
        <td style="padding: 10px 30px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    @foreach ($images as $img)
                    <td width="{{ $tdWidth }}%" valign="top" style="padding: 0 {{ $loop->last ? '0' : '5' }}px 0 {{ $loop->first ? '0' : '5' }}px;">
                        @if (!empty($img['link']))<a href="{{ $img['link'] }}" style="text-decoration: none; color: inherit;">@endif
                        <img src="{{ $img['url'] }}" alt="{{ $img['alt'] ?? '' }}" width="{{ $pixelWidth }}" style="width: 100%; max-width: {{ $pixelWidth }}px; height: auto; display: block; {{ $imgExtra }}" />
                        @if (!empty($img['link']))</a>@endif
                        @if (!empty($img['caption']))
                        <p style="margin: 6px 0 0 0; font-size: 13px; color: {{ $style['textColor'] }}cc; font-family: '{{ $style['fontFamily'] }}', sans-serif; text-align: center;">{{ $img['caption'] }}</p>
                        @endif
                    </td>
                    @endforeach
                </tr>
            </table>
        </td>
    </tr>
@elseif ($layout === 'grid')
    {{-- Grid layout: 2 per row --}}
    @php $pixelWidth = floor(540 / 2); @endphp
    @foreach (array_chunk($images, 2) as $row)
    <tr>
        <td style="padding: 5px 30px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    @foreach ($row as $img)
                    <td width="50%" valign="top" style="padding: 0 {{ $loop->last ? '0' : '5' }}px 0 {{ $loop->first ? '0' : '5' }}px;">
                        @if (!empty($img['link']))<a href="{{ $img['link'] }}" style="text-decoration: none; color: inherit;">@endif
                        <img src="{{ $img['url'] }}" alt="{{ $img['alt'] ?? '' }}" width="{{ $pixelWidth }}" style="width: 100%; max-width: {{ $pixelWidth }}px; height: auto; display: block; {{ $imgExtra }}" />
                        @if (!empty($img['link']))</a>@endif
                        @if (!empty($img['caption']))
                        <p style="margin: 6px 0 0 0; font-size: 13px; color: {{ $style['textColor'] }}cc; font-family: '{{ $style['fontFamily'] }}', sans-serif; text-align: center;">{{ $img['caption'] }}</p>
                        @endif
                    </td>
                    @endforeach
                    @if (count($row) === 1)
                    <td width="50%"></td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>
    @endforeach
@endif
