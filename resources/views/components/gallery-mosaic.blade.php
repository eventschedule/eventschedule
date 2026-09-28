@props([
    // The committed GalleryImage rows, in order.
    'images',
    // 'event': sized by the event column (a 2:1 box). 'schedule': fixed row heights, because the
    // schedule panel widens to the calendar's full width.
    'variant' => 'event',
    // Whose photos these are, for the alt text of a photo with no caption.
    'name' => '',
    'accentColor' => '#4E81FA',
    // The first photo is the page's largest image (the gallery took the flyer's place).
    'priority' => false,
])
{{--
    An organizer's gallery on a guest page: a swipeable strip on a phone, and one large photo with
    up to four beside it from sm up. One list serves both, switched by CSS alone, so no photo is in
    the page twice and a photo past the fifth, hidden on the wider layout, is never fetched there
    (hidden lazy images are not). Each photo is a link to its original, which the shared viewer
    (partials/lightbox) opens in place; the fifth, when there are more, opens every photo as a grid
    from sm up.

    The caller writes the id="gp-gallery" wrapper, since that id is the documented custom-CSS hook
    and the page views are where GuestSectionIdTest looks for it. Captions are the owner's text:
    they appear only in alt attributes here and as bound data in the viewer.
--}}
@php
    $images = collect($images)->values();
    $count = $images->count();
    $shown = min($count, 5);
    $more = max(0, $count - 5);
    $lightboxItems = $images->map(fn ($image) => $image->toLightboxArray())->all();
@endphp
@if ($count > 0)
@once
<style {!! nonce_attr() !!}>
    .es-mosaic-list { display: flex; gap: 0.5rem; height: 14rem; overflow-x: auto; overscroll-behavior-x: contain; scroll-snap-type: x mandatory; scrollbar-width: none; margin-inline: -1.5rem; padding-inline: 1.5rem; scroll-padding-inline: 1.5rem; }
    .es-mosaic-list::-webkit-scrollbar { display: none; }
    .es-mosaic-item { position: relative; flex: 0 0 auto; height: 100%; aspect-ratio: var(--es-ratio, 1); max-width: calc(100vw - 4rem); scroll-snap-align: start; border-radius: 0.75rem; overflow: hidden; }
    .es-mosaic--n1 .es-mosaic-item { width: 100%; max-width: none; aspect-ratio: auto; }
    .es-mosaic-more { display: none; }
    @media (min-width: 640px) {
        .es-mosaic-list { display: grid; gap: 0.5rem; height: auto; overflow: visible; margin-inline: 0; padding-inline: 0; grid-template-columns: repeat(4, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(0, 1fr)); }
        .es-mosaic--event .es-mosaic-list { aspect-ratio: 2 / 1; }
        .es-mosaic--schedule .es-mosaic-list { height: clamp(16rem, 30vw, 28rem); }
        .es-mosaic-item { height: auto; max-width: none; aspect-ratio: auto; border-radius: 0; }
        .es-mosaic-item:nth-child(n+6) { display: none; }
        .es-mosaic-list { border-radius: 0.75rem; overflow: hidden; }
        .es-mosaic--n1 .es-mosaic-list { grid-template-columns: 1fr; grid-template-rows: 1fr; }
        .es-mosaic--event.es-mosaic--n1 .es-mosaic-list { aspect-ratio: 16 / 9; }
        .es-mosaic--n2 .es-mosaic-list { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr; }
        .es-mosaic--n3 .es-mosaic-list { grid-template-columns: 1fr 1fr; }
        .es-mosaic--n3 .es-mosaic-item:first-child { grid-row: span 2; }
        .es-mosaic--n4 .es-mosaic-list { grid-template-columns: 1fr 1fr; }
        .es-mosaic--n5 .es-mosaic-item:first-child { grid-column: span 2; grid-row: span 2; }
        .es-mosaic-more { display: flex; }
    }
    .es-mosaic-img { width: 100%; height: 100%; object-fit: cover; }
    @media (prefers-reduced-motion: no-preference) {
        .es-mosaic-img { transition: transform 500ms cubic-bezier(0.2, 0, 0, 1); }
        .es-mosaic-link:hover .es-mosaic-img { transform: scale(1.04); }
    }
    .es-mosaic-link:focus-visible { outline: 3px solid var(--es-accent, #4E81FA); outline-offset: -3px; }
</style>
@endonce
<div class="es-mosaic es-mosaic--{{ $variant }} es-mosaic--n{{ $shown }}" style="--es-accent: {{ $accentColor }};">
    <ul role="list" class="es-mosaic-list">
        @foreach ($images as $i => $image)
        @php
            $ratio = ($image->width && $image->height) ? max(0.6, min(1.8, $image->width / $image->height)) : 1.333;
            $srcset = $image->srcset();
            // Half the mosaic's width for the large photo, for both photos of two, all four of four
            // and the pair beside the large one of three; a quarter only for the small tiles of
            // five or more.
            $isHero = $i === 0 && $shown !== 2 && $shown !== 4;
            $isHalf = $isHero || $shown === 2 || $shown === 4 || ($shown === 3 && $i < 3) || $shown === 1;
            $alt = $image->caption ?: __('messages.gallery_photo_alt', ['name' => $name, 'n' => $i + 1]);
            $opensGrid = $i === 4 && $more > 0;
        @endphp
        <li class="es-mosaic-item" style="--es-ratio: {{ round($ratio, 3) }};@if ($image->color) background-color: {{ $image->color }};@endif">
            <a href="{{ $image->url() }}" class="es-mosaic-link block h-full w-full" data-lightbox-set="gallery" data-lightbox-index="{{ $i }}" @if ($opensGrid) data-lightbox-grid-from="640" @endif>
                <img src="{{ $image->thumbUrl($isHalf ? 960 : 480) }}"
                     @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $shown === 1 ? '(min-width: 640px) 100vw, 90vw' : ($isHalf ? '(min-width: 640px) 50vw, 70vw' : '(min-width: 640px) 25vw, 70vw') }}" @endif
                     @if ($image->width && $image->height) width="{{ $image->width }}" height="{{ $image->height }}" @endif
                     alt="{{ $alt }}"
                     class="es-mosaic-img"
                     @if ($image->height > $image->width) style="object-position: 50% 25%;" @endif
                     @if ($i === 0 && $priority) fetchpriority="high" @else loading="lazy" @endif
                     decoding="async"/>
                @if ($opensGrid)
                {{-- Shown from sm up only, where this tile opens every photo; on a phone it is simply the
                     fifth photo in the strip, and says so. --}}
                <span class="es-mosaic-more pointer-events-none absolute inset-0 items-center justify-center bg-black/45 text-lg font-semibold text-white"><span aria-hidden="true">+{{ $more }}</span><span class="sr-only">{{ __('messages.gallery_see_all', ['count' => $count]) }}</span></span>
                @endif
            </a>
        </li>
        @endforeach
    </ul>
</div>
<script {!! nonce_attr() !!}>(window.EsLightboxSets = window.EsLightboxSets || {}).gallery = @json($lightboxItems, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);</script>
@endif
