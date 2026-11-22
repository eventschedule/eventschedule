@props(['sponsors', 'title', 'maxWidth' => '200rem', 'background' => null])

@if (!empty($sponsors))
@php
    // The owner's panel colour is the colour of each logo's CHIP, not of a block across the
    // page: null is a soft white, 'transparent' sets the logos straight on the page, a hex
    // paints the chips. A light chip lets a logo's own white box melt into it (multiply).
    $isTransparentPanel = $background === 'transparent';
    $customPanelColor = (is_string($background) && preg_match('/^#[0-9a-fA-F]{6}$/', $background)) ? $background : null;
    // Kept neutral: the owner's colour gives the chips their brightness, never their hue. A cream
    // or a pink chip clashed with the page and with the logos on it; a soft grey with a breath of
    // the page's own ink-blue sits under any logo.
    if ($customPanelColor) {
        [, , $chipLightness] = \App\Utils\ColorUtils::toHsl($customPanelColor);
        $customPanelColor = \App\Utils\ColorUtils::fromHsl(0.56, 0.1, $chipLightness > 0.5 ? max(0.9, min($chipLightness, 0.95)) : min(max($chipLightness, 0.16), 0.24));
    }
    $chipInk = $customPanelColor ? \App\Utils\ColorUtils::getContrastColor($customPanelColor) : null;
    $isDarkChip = $chipInk === '#ffffff';

    // Gold leads, in a row of its own and larger; then silver, bronze and the rest, each in
    // the owner's own order. A sponsor with neither a logo nor a name has nothing to show.
    $tierRank = ['gold' => 0, 'silver' => 1, 'bronze' => 2];
    $items = [];
    foreach (array_values($sponsors) as $i => $sponsor) {
        $name = trim((string) ($sponsor['display_name'] ?? ''));
        $logo = (string) ($sponsor['logo_url'] ?? '');
        if ($name === '' && $logo === '') {
            continue;
        }
        $tier = in_array($sponsor['tier'] ?? null, ['gold', 'silver', 'bronze'], true) ? $sponsor['tier'] : null;
        // The url is client-built JSON that nothing validates: linked only through safeHref(),
        // and a sponsor without a safe link shows unlinked.
        $href = \App\Utils\UrlUtils::safeHref($sponsor['url'] ?? null);
        $host = $href ? preg_replace('/^www\./i', '', (string) parse_url($href, PHP_URL_HOST)) : '';
        $items[] = ['name' => $name, 'logo' => $logo, 'tier' => $tier, 'href' => $href, 'alt' => $name !== '' ? $name : $host, 'rank' => $tierRank[$tier] ?? 3, 'i' => $i];
    }
    usort($items, fn ($a, $b) => [$a['rank'], $a['i']] <=> [$b['rank'], $b['i']]);
    $lead = array_values(array_filter($items, fn ($s) => $s['tier'] === 'gold'));
    $rest = array_values(array_filter($items, fn ($s) => $s['tier'] !== 'gold'));
    // Every sponsor gold is no distinction: one list.
    if (! $rest) {
        [$lead, $rest] = [[], $lead];
    }
    // Names under the chips for every sponsor or for none, so the rows keep one rhythm. A
    // name not shown is still the logo's alt text and its tooltip.
    $logoItems = array_filter($items, fn ($s) => $s['logo'] !== '');
    $withNames = $logoItems && count(array_filter($logoItems, fn ($s) => $s['name'] !== '')) === count($logoItems);

    $rootClasses = 'es-sp es-sponsors-pending'
        .($isTransparentPanel ? ' es-sp-bare' : '')
        .($isDarkChip ? ' es-sp-dark' : '')
        .($withNames ? ' es-sp-named' : '');
    $rootStyle = 'max-width: '.$maxWidth.';'.($customPanelColor ? ' --sp-chip: '.$customPanelColor.'; --sp-chip-ink: '.$chipInk.';' : '');
@endphp

@once
<style {!! nonce_attr() !!}>
    {{-- The section is revealed as one block once every logo has loaded (and been sized), so
         visitors never watch it fill in one logo at a time. Width transitions stay off until
         then so the view-toggle animation can't fire while it is still being laid out. The look
         itself is in partials/guest-stage-styles ("Sponsors on the sheet"). --}}
    [data-sponsor-grid] { transition: opacity 0.3s ease-in-out, max-width 0.3s ease-in-out; }
    [data-sponsor-grid].es-sponsors-pending { opacity: 0; transition: none !important; }
</style>
<noscript><style>[data-sponsor-grid].es-sponsors-pending { opacity: 1 !important; }</style></noscript>
@endonce

<section
    id="gp-sponsors"
    class="{{ $rootClasses }}"
    data-sponsor-grid
    data-view-width
    style="{{ $rootStyle }}"
>
    <div class="es-sp-head">
        <h3 class="es-sp-title">{{ $title }}</h3>
        <button type="button" class="es-sp-toggle" data-sp-toggle hidden aria-expanded="false"
            data-more="{{ __('messages.show_more') }}" data-less="{{ __('messages.show_less') }}">{{ __('messages.show_more') }}</button>
    </div>
    @foreach (array_filter([$lead, $rest]) as $group)
        @php $isLead = $loop->first && $lead; @endphp
        <div class="es-sp-view {{ $isLead ? 'es-sp-view-lead' : '' }}" @unless ($isLead) data-sp-view @endunless>
            <div class="es-sp-track" @unless ($isLead) data-sp-track @endunless>
                <ul class="es-sp-list" role="list" @unless ($isLead) data-sp-list @endunless>
                    @foreach ($group as $sponsor)
                        <li class="es-sp-item {{ $sponsor['tier'] ? 'es-sp-'.$sponsor['tier'] : '' }}">
                            @if ($sponsor['href'])
                                <a href="{{ $sponsor['href'] }}" target="_blank" rel="noopener noreferrer nofollow" class="es-sp-link" @if ($sponsor['name'] !== '') title="{{ $sponsor['name'] }}" @endif>
                            @else
                                <span class="es-sp-link" @if ($sponsor['name'] !== '') title="{{ $sponsor['name'] }}" @endif>
                            @endif
                                <span class="es-sp-chip">
                                    @if ($sponsor['logo'] !== '')
                                        <img src="{{ $sponsor['logo'] }}" alt="{{ $sponsor['alt'] }}" class="es-sp-logo" loading="eager" decoding="async" />
                                    @else
                                        <span class="es-sp-word"><bdi>{{ $sponsor['name'] }}</bdi></span>
                                    @endif
                                </span>
                                @if ($withNames && $sponsor['logo'] !== '')
                                    <span class="es-sp-name" aria-hidden="true"><bdi>{{ $sponsor['name'] }}</bdi></span>
                                @endif
                                @if ($sponsor['tier'])
                                    <span class="es-sp-sr">{{ __('messages.'.$sponsor['tier']) }}</span>
                                @endif
                            @if ($sponsor['href'])
                                </a>
                            @else
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</section>

@once
<script {!! nonce_attr() !!}>
(function () {
    var motion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
    var stillOnly = function () { return !!(motion && motion.matches); };

    // One optical size for every shape: a square logo fills the chip's height, a wide wordmark
    // is drawn lower and longer, so a row reads as even rather than as boxes and slivers.
    var sizeLogo = function (img) {
        var w = img.naturalWidth, h = img.naturalHeight;
        if (!w || !h) return;
        var s = Math.max(0.5, Math.min(1, Math.pow(w / h, -0.45)));
        img.style.setProperty('--sp-s', s.toFixed(3));
    };

    var rowsOf = function (list) {
        var tops = [];
        Array.prototype.forEach.call(list.children, function (li) {
            var t = li.offsetTop;
            if (tops.indexOf(t) === -1) tops.push(t);
        });
        return tops.sort(function (a, b) { return a - b; });
    };

    // The rest of the logos, laid out for the room they have: all of them when they fit one
    // row; otherwise one row drifting slowly (paused under the pointer or on focus), or, for a
    // visitor who asked for less motion, the first two rows. "Show more" opens every one.
    var layout = function (root) {
        var view = root.querySelector('[data-sp-view]');
        var track = root.querySelector('[data-sp-track]');
        var list = root.querySelector('[data-sp-list]');
        var toggle = root.querySelector('[data-sp-toggle]');
        if (!view || !track || !list || !toggle) return;
        var clone = track.querySelector('[data-sp-clone]');
        if (clone) clone.remove();
        root.classList.remove('es-sp-run', 'es-sp-clamped');
        root.style.removeProperty('--sp-clamp');
        var open = root.classList.contains('es-sp-open');
        var rows = rowsOf(list);
        toggle.hidden = rows.length <= 1 && !open;
        if (open || rows.length <= 1) return;

        if (!stillOnly()) {
            clone = list.cloneNode(true);
            clone.setAttribute('aria-hidden', 'true');
            clone.setAttribute('data-sp-clone', '');
            clone.removeAttribute('data-sp-list');
            clone.querySelectorAll('a').forEach(function (a) { a.setAttribute('tabindex', '-1'); });
            clone.querySelectorAll('img').forEach(function (img) { img.setAttribute('alt', ''); });
            track.appendChild(clone);
            root.classList.add('es-sp-run');
            // About 32px a second: slow enough to read each logo as it passes.
            root.style.setProperty('--sp-dur', Math.max(20, Math.round(list.offsetWidth / 32)) + 's');
        } else if (rows.length > 2) {
            root.style.setProperty('--sp-clamp', (rows[2] - rows[0] - 1) + 'px');
            root.classList.add('es-sp-clamped');
        } else {
            toggle.hidden = true;
        }
    };

    var setOpen = function (root, open) {
        var toggle = root.querySelector('[data-sp-toggle]');
        root.classList.toggle('es-sp-open', open);
        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.textContent = open ? toggle.getAttribute('data-less') : toggle.getAttribute('data-more');
        }
        layout(root);
    };

    var init = function () {
        document.querySelectorAll('[data-sponsor-grid].es-sponsors-pending').forEach(function (grid) {
            var images = Array.prototype.slice.call(grid.querySelectorAll('img'));
            var revealed = false;
            var queued = false;
            var relayout = function () {
                if (queued) return;
                queued = true;
                requestAnimationFrame(function () { queued = false; layout(grid); });
            };
            var reveal = function () {
                if (revealed) return;
                revealed = true;
                layout(grid);
                grid.classList.remove('es-sponsors-pending');
            };

            var toggle = grid.querySelector('[data-sp-toggle]');
            if (toggle) toggle.addEventListener('click', function () { setOpen(grid, !grid.classList.contains('es-sp-open')); });
            // A keyboard visitor reaching a logo that is drifting or folded away gets them all,
            // standing still, so the one in focus is always in view.
            grid.addEventListener('focusin', function (e) {
                if (e.target.closest('[data-sp-list]') && (grid.classList.contains('es-sp-run') || grid.classList.contains('es-sp-clamped'))) setOpen(grid, true);
            });
            if (motion && motion.addEventListener) motion.addEventListener('change', relayout);
            var lastWidth = 0;
            if (window.ResizeObserver) {
                new ResizeObserver(function (entries) {
                    var w = Math.round(entries[0].contentRect.width);
                    if (w !== lastWidth) { lastWidth = w; if (revealed) relayout(); }
                }).observe(grid);
            }

            var remaining = images.length;
            if (!remaining) {
                reveal();
                return;
            }

            var settle = function (image) {
                sizeLogo(image);
                if (revealed) { relayout(); return; }
                if (--remaining <= 0) reveal();
            };

            images.forEach(function (image) {
                if (image.complete) {
                    settle(image);
                    return;
                }
                image.addEventListener('load', function () { settle(image); }, { once: true });
                image.addEventListener('error', function () { settle(image); }, { once: true });
            });

            // A slow or broken logo must never leave the section permanently hidden.
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
