{{--
    The inside of a guest page's #gp-gallery card: its heading, a "See all" link that opens every
    photo as a grid (shown past five photos, where the mosaic stops, and on a phone whenever there
    is more than one, since the strip hides the rest off to the side), and the mosaic itself.

    Expects: $galleryImages, $galleryVariant ('event' | 'schedule'), $galleryName, $galleryLabel,
    $galleryPriority, $accentColor.
--}}
@php $galleryCount = $galleryImages->count(); @endphp
<div class="mb-4 flex items-baseline justify-between gap-4">
    {{-- es-, not gp-: gp- ids are the documented custom-CSS hooks, and this is the heading the
         section's aria-labelledby points at. v-pre because the label is the owner's text. --}}
    <h2 id="es-gallery-title" v-pre class="{{ $galleryVariant === 'event' ? 'text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400' : 'text-lg font-semibold text-gray-900 dark:text-gray-100' }}">
        {{ $galleryLabel }}
    </h2>
    @if ($galleryCount > 1)
    <a href="{{ $galleryImages->first()->url() }}" data-lightbox-set="gallery" data-lightbox-grid
       class="{{ $galleryCount > 5 ? '' : 'sm:hidden' }} shrink-0 text-sm font-medium text-gray-600 dark:text-gray-300 underline-offset-4 hover:underline focus:outline-none focus-visible:underline"
       style="text-decoration-color: {{ $accentColor }};">
        {{ __('messages.gallery_see_all', ['count' => $galleryCount]) }}
    </a>
    @endif
</div>
<x-gallery-mosaic :images="$galleryImages" :variant="$galleryVariant" :name="$galleryName" :accent-color="$accentColor" :priority="$galleryPriority" />
