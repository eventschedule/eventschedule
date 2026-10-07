{{--
    A picture of the admin portal, in the theme the reader is in.

    The frame is a link to the full-size image. A 1280px screenshot drawn in a 52rem column is
    readable; drawn across a phone it is a thumbnail, and until this was a link there was nothing
    a reader could do about that. The link opens the picture on its own, where a phone can pinch
    and a desktop sees every pixel.

    `id` is a FILE name under public/images/docs (four files: light and dark, png and webp), not
    an anchor. App\Console\Commands\GenerateDocScreenshots makes them, and
    DocsManifestTest::test_every_referenced_doc_screenshot_exists fails the build on a missing one.

    width/height are read from the file so the page does not jump as each picture arrives: the
    pictures are lazy, and the longer guide pages hold ten of them.
--}}
@props(['id', 'alt', 'loading' => 'lazy', 'caption' => null])

@php
    $themes = [];
    foreach (['' => 'dark:hidden', '-dark' => 'hidden dark:block'] as $suffix => $show) {
        $file = "images/docs/{$id}{$suffix}";
        $size = is_file(public_path($file.'.png')) ? @getimagesize(public_path($file.'.png')) : false;
        $themes[] = [
            'show' => $show,
            'png' => url($file.'.png'),
            'webp' => url($file.'.webp'),
            'width' => $size ? $size[0] : null,
            'height' => $size ? $size[1] : null,
        ];
    }
@endphp

<figure class="doc-shot">
    @foreach ($themes as $shot)
        {{-- The wrapper carries the theme switch: .doc-shot-frame sets its own display, and a
             display utility on the same element would be left to source order. --}}
        <div class="{{ $shot['show'] }}">
            <a href="{{ $shot['png'] }}" target="_blank" rel="noopener" class="doc-shot-frame doc-unstyled" aria-label="{{ $alt }} (opens the full-size image)">
                <picture>
                    <source srcset="{{ $shot['webp'] }}" type="image/webp">
                    <img src="{{ $shot['png'] }}" alt="{{ $alt }}" class="doc-shot-img" loading="{{ $loading }}" decoding="async"
                         @if ($shot['width']) width="{{ $shot['width'] }}" height="{{ $shot['height'] }}" @endif>
                </picture>
                <span class="doc-shot-zoom" aria-hidden="true">Open full size</span>
            </a>
        </div>
    @endforeach
    @if ($caption)
        <figcaption>{{ $caption }}</figcaption>
    @endif
</figure>
