@props(['sponsors', 'title', 'maxWidth' => '200rem', 'background' => null])

@if (!empty($sponsors))
@php
    // Panel background: null keeps the default translucent card, 'transparent' lets the
    // schedule's own background show through, a hex value paints a custom colour.
    $isTransparentPanel = $background === 'transparent';
    $customPanelColor = (is_string($background) && preg_match('/^#[0-9a-fA-F]{6}$/', $background)) ? $background : null;
    $panelClasses = ($isTransparentPanel || $customPanelColor) ? '' : 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm';
    $panelTextColor = $customPanelColor ? \App\Utils\ColorUtils::getContrastColor($customPanelColor) : null;

    // Denser layout once the list outgrows the original 12-logo cap, otherwise a long
    // partner list turns into a dozen rows of mostly empty space.
    $sponsorCount = count($sponsors);
    $isDense = $sponsorCount > 12;
    if ($sponsorCount === 1) {
        $gridColumns = 'grid-cols-1';
    } elseif ($isDense) {
        $gridColumns = 'grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6';
    } else {
        $gridColumns = 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4';
    }
    $gridGap = $isDense ? 'gap-4 sm:gap-6' : 'gap-8';
    $tileHeight = $isDense ? 'h-20' : 'h-24';
    $logoStyles = $isDense ? 'max-height: 3.5rem; max-width: 120px;' : 'max-height: 5rem; max-width: 160px;';
@endphp

@once
<style {!! nonce_attr() !!}>
    /* The banner is revealed as one block once every logo has loaded, so visitors never
       watch it fill in one tile at a time. Width transitions stay off until then so the
       view-toggle animation can't fire while the panel is still being laid out. */
    [data-sponsor-grid] { transition: opacity 0.3s ease-in-out, max-width 0.3s ease-in-out; }
    [data-sponsor-grid].es-sponsors-pending { opacity: 0; transition: none !important; }
</style>
<noscript><style>[data-sponsor-grid].es-sponsors-pending { opacity: 1 !important; }</style></noscript>
@endonce

<div
    class="w-full {{ $panelClasses }} sm:rounded-2xl px-6 py-6 mx-auto es-sponsors-pending"
    data-sponsor-grid
    data-view-width
    style="max-width: {{ $maxWidth }};{{ $customPanelColor ? ' background-color: '.$customPanelColor.';' : '' }}"
>
    <h3 class="text-lg font-semibold {{ $panelTextColor ? '' : 'text-gray-900 dark:text-gray-100' }} text-center mb-6"
        @if ($panelTextColor) style="color: {{ $panelTextColor }}" @endif>
        {{ $title }}
    </h3>
    <div class="grid {{ $gridColumns }} {{ $gridGap }} place-content-center">
        @foreach ($sponsors as $sponsor)
            <div class="flex flex-col items-center text-center">
                @if (!empty($sponsor['url']))
                    <a href="{{ $sponsor['url'] }}" target="_blank" rel="noopener noreferrer nofollow" class="flex flex-col items-center text-center w-full hover:opacity-80 transition-opacity">
                @endif
                <div class="{{ $tileHeight }} w-full flex items-end justify-center pb-2 overflow-hidden">
                    @if (!empty($sponsor['logo_url']))
                        <img src="{{ $sponsor['logo_url'] }}"
                            alt="{{ $sponsor['display_name'] }}"
                            style="{{ $logoStyles }}"
                            class="object-contain"
                            loading="eager"
                            decoding="async" />
                    @endif
                </div>
                @if (!empty($sponsor['display_name']))
                    <span class="text-xs {{ $panelTextColor ? 'opacity-70' : 'text-gray-600 dark:text-gray-400' }} truncate max-w-full"
                        @if ($panelTextColor) style="color: {{ $panelTextColor }}" @endif>{{ $sponsor['display_name'] }}</span>
                @endif
                @if (!empty($sponsor['tier']))
                    <span class="inline-block text-xs px-1.5 py-0.5 rounded mt-0.5
                        {{ $sponsor['tier'] === 'gold' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : '' }}
                        {{ $sponsor['tier'] === 'silver' ? 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' : '' }}
                        {{ $sponsor['tier'] === 'bronze' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : '' }}
                    ">{{ __('messages.' . $sponsor['tier']) }}</span>
                @endif
                @if (!empty($sponsor['url']))
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>

@once
<script {!! nonce_attr() !!}>
(function () {
    var init = function () {
        document.querySelectorAll('[data-sponsor-grid].es-sponsors-pending').forEach(function (grid) {
            var images = Array.prototype.slice.call(grid.querySelectorAll('img'));
            var revealed = false;
            var reveal = function () {
                if (revealed) return;
                revealed = true;
                grid.classList.remove('es-sponsors-pending');
            };

            var remaining = images.length;
            if (!remaining) {
                reveal();
                return;
            }

            var tick = function () {
                if (--remaining <= 0) reveal();
            };

            images.forEach(function (image) {
                if (image.complete) {
                    tick();
                    return;
                }
                image.addEventListener('load', tick, { once: true });
                image.addEventListener('error', tick, { once: true });
            });

            // A slow or broken logo must never leave the banner permanently hidden.
            setTimeout(reveal, 2500);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endonce
@endif
