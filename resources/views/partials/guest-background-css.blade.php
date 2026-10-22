{{-- The page's background, drawn on a layer of its own (partials/guest-background) rather than on
     `body`. Printed inside the guest layout's <style>, before the owner's custom CSS.

     Why a layer: a background on `body` with `cover` is sized to the whole PAGE where it scrolls
     with it (the calendar), so a 3000px calendar enlarged a built-in picture 2.4x, 4.9x on a
     retina screen, and iPhones ignore `background-attachment: fixed` and do the same everywhere.
     A fixed layer is always the size of the window. Longhand offsets rather than `inset`, which
     iOS before 14.5 drops; 100lvh so the cover is not fitted again each time the address bar
     slides away.

     Expects $bg (the schedule whose background this is), $bgAurora (App\Utils\Aurora or null) and
     $showMobileBackground, from layouts/app-guest. --}}
        .gp-bg { position: fixed; top: 0; left: 0; right: 0; height: 100vh; height: 100lvh; z-index: -1; pointer-events: none; background-repeat: no-repeat; background-position: center; background-size: cover; }
        @media print { .gp-bg { display: none; } }
        @media (forced-colors: active) { .gp-bg { display: none; } }
        @if (request()->graphic)
        {{-- A graphic is captured whole, page by page: a fixed layer would be drawn again on every
             screen of it. Here the layer runs the length of the page instead. --}}
        body { position: relative; }
        .gp-bg { position: absolute; bottom: 0; height: auto; }
        .gp-bg .gp-aurora { container-type: normal; --aurora-u: 1vmax; }
        @endif
        @php
            $bgOverlay = request()->graphic ? 'linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)), ' : '';
        @endphp
        @if ($bg->background == 'gradient')
            .gp-bg { background-image: linear-gradient({{ $bg->background_rotation }}deg, {{ $bg->background_colors }}); }
            @php $bgBase = \App\Utils\Aurora::hex(explode(',', (string) $bg->background_colors)[0]); @endphp
            @if ($bgBase)
            {{-- The page under the layer, where iOS shows it past the end of a scroll. --}}
            body { background-color: {{ $bgBase }} !important; }
            @endif
        @elseif ($bg->background == 'image')
            @if (! $showMobileBackground)
            {{-- Below md the schedule page shows its picture behind the header only
                 (role/show-guest), so there is nothing to lay over the whole window. --}}
            @media (max-width: 767.98px) { .gp-bg { display: none; } }
            @endif
            @if ($bg->background_image)
                .gp-bg {
                    background-image: {!! $bgOverlay !!}url("{{ css_url(asset('images/backgrounds/' . $bg->background_image . '.webp')) }}");
                    background-image: {!! $bgOverlay !!}image-set(
                        url("{{ css_url(asset('images/backgrounds/' . $bg->background_image . '.webp')) }}") type("image/webp"),
                        url("{{ css_url(asset('images/backgrounds/' . $bg->background_image . '.png')) }}") type("image/png")
                    );
                }
            @else
                @php
                    // The desktop picture, and a wider one for a retina screen or a window past
                    // 1920 when the pipeline made one: the 3840 key holds the 1920 file again for
                    // an original no wider than that (ImageUtils::PAGE_VARIANT_WIDTHS). Never the
                    // original, which can be a 2.5MB PNG. An animated upload is its original.
                    $bgDesktop = $bg->backgroundImageUrl(1920, pageWidth: true);
                    $bgWide = $bg->imageVariantUrl(3840, 'background', true);
                    $bgWide = ($bgWide && $bgWide !== $bgDesktop) ? $bgWide : null;
                @endphp
                @if ($showMobileBackground)
                .gp-bg { background-image: {!! $bgOverlay !!}url("{{ css_url($bg->backgroundImageUrl(960, pageWidth: true)) }}"); }
                @endif
                @media (min-width: 768px) {
                    .gp-bg { background-image: {!! $bgOverlay !!}url("{{ css_url($bgDesktop) }}"); }
                }
                @if ($bgWide)
                {{-- The wider picture from a laptop up, where a retina screen shows the
                     difference; a tablet keeps the 1920 one. --}}
                @media (min-width: 1280px) {
                    .gp-bg {
                        background-image: {!! $bgOverlay !!}-webkit-image-set(url("{{ css_url($bgDesktop) }}") 1x, url("{{ css_url($bgWide) }}") 2x);
                        background-image: {!! $bgOverlay !!}image-set(url("{{ css_url($bgDesktop) }}") 1x, url("{{ css_url($bgWide) }}") 2x);
                    }
                }
                @media (min-width: 1921px) {
                    .gp-bg { background-image: {!! $bgOverlay !!}url("{{ css_url($bgWide) }}"); }
                }
                @endif
            @endif
        @elseif ($bgAurora)
            {!! $bgAurora->css('.gp-bg') !!}
            {!! $bgAurora->keyframes() !!}
            {{-- The page under the layer, where iOS shows it past the end of a scroll. Not where
                 the owner's CSS styles the page background: a background on html would stop
                 body's reaching the canvas, and body's own would then cover the layer. --}}
            @if (! $role->customCssStylesPageBackground())
            body { background-color: {{ $bgAurora->ground('light') }} !important; }
            :where(html.dark) body { background-color: {{ $bgAurora->ground('dark') }} !important; }
            @else
            {{-- Where html carries the owner's background, body's own grey would be painted over
                 the layer: see-through, so the owner's colour shows only where Aurora does not. --}}
            body { background-color: transparent !important; }
            @endif
            {{-- The body's ink is one grey in both modes (above). On Aurora's dark ground, text
                 drawn straight on the page takes white, as the lights were held for. On screen
                 only (paper is white), and as light as a body rule, so an owner's own colour,
                 printed later, still wins. --}}
            @media screen {
                :where(html.dark) body { color: #ffffff !important; }
            }
            @if ($bgAurora->moves() && ! request()->embed && ! request()->graphic)
            {{-- While the lights move, the frosted panels over them would be blurred again on
                 every frame, for nothing a visitor can see through 95% of panel. Kept for the
                 still page (the visitor's setting, the accessibility widget's two modes), and
                 isolate keeps the stacking each blur used to make. --}}
            @media (prefers-reduced-motion: no-preference) {
                html:not(.es-a11y-reduce-motion):not(.es-a11y-high-contrast) .gp-bg ~ * :is(.gk-panel, .gk-past-label, .gk-head, .gk-headbar, .calendar-panel-border, .backdrop-blur-sm, .backdrop-blur),
                html:not(.es-a11y-reduce-motion):not(.es-a11y-high-contrast) .gp-bg ~ :is(.backdrop-blur-sm, .backdrop-blur) {
                    -webkit-backdrop-filter: none !important;
                    backdrop-filter: none !important;
                    isolation: isolate;
                }
            }
            @endif
        @endif
