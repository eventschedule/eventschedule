{{-- The appearance (Light / Dark / System) and palette pickers, as one control used in both
     places the user can change them: the sidebar footer's theme popover and the Appearance tab
     in profile settings. Those were two independent implementations - icon-only vs text-only,
     pressed-inset vs brand-blue outline, two different swatch shapes - so no amount of
     recolouring could have made them read as the same control. Here they share geometry,
     content shape and interaction; only the colour tone differs.

     tone      rail    | the sidebar popover. The rail is dark in EVERY palette (see the
                         --ap-rail* note in resources/css/app.css), so it cannot use the
                         gray ramp the rest of the AP does.
               surface | anywhere on a normal AP page. This is the house segmented control,
                         the same shape as the appointments filter tabs.

     headings  compact | 11px uppercase section labels, for the popover where space is tight.
               full    | an <h3> plus its help paragraph, for a settings page.

     Nothing here posts: the theme lives in localStorage, per device. --}}

@props([
    'tone' => 'surface',
    'headings' => 'full',
])

@php
    $compact = $headings === 'compact';
    // Shared with the sidebar footer's popover trigger - see config/app.php.
    $modeIcons = config('app.ap_theme_mode_icons');
@endphp

<div class="tp tp-{{ $tone }} {{ $compact ? 'space-y-2' : 'space-y-6' }}">

    {{-- Mode. No aria-label and no title on the buttons: each carries a visible .tp-label, so
         that label IS the accessible name and the two can never drift. An aria-label would
         override it, which is how this button once ended up named "Appearance" while reading
         "Theme" - a WCAG 2.5.3 failure for anyone driving the UI by voice. --}}
    <div>
        @if ($compact)
            <div class="tp-heading">{{ __('messages.appearance') }}</div>
        @else
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.theme') }}</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.appearance_help') }}</p>
        @endif

        <div class="tp-group {{ $compact ? 'mt-1' : 'mt-3' }}" role="radiogroup" aria-label="{{ __('messages.appearance') }}">
            @foreach ($modeIcons as $mode => $iconPath)
                <button type="button" class="tp-item js-theme-mode-btn" data-theme="{{ $mode }}"
                    role="radio" aria-checked="false">
                    <svg class="tp-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}" />
                    </svg>
                    <span class="tp-label">{{ __('messages.theme_' . $mode) }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Palette. All six ship; the JS hides the three that do not belong to the brightness
         currently rendering, which under System follows the OS and can flip at any moment. --}}
    <div>
        @if ($compact)
            <div class="tp-heading">{{ __('messages.palette') }}</div>
        @else
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.palette') }}</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.palette_help') }}</p>
        @endif

        <div class="tp-group {{ $compact ? 'mt-1' : 'mt-3' }}" role="radiogroup" aria-label="{{ __('messages.palette') }}">
            @foreach (config('app.ap_palettes') as [$variant, $brightness, $swatchBg, $swatchSurface])
                <button type="button" class="tp-item js-theme-variant-btn hidden"
                    data-variant="{{ $variant }}" data-brightness="{{ $brightness }}"
                    role="radio" aria-checked="false">
                    <span class="tp-swatch" style="--sw-bg: {{ $swatchBg }}; --sw-surface: {{ $swatchSurface }}"></span>
                    <span class="tp-label">{{ __('messages.variant_' . $variant) }}</span>
                </button>
            @endforeach
        </div>
    </div>
</div>

@once
<script {!! nonce_attr() !!}>
    // The one driver for every picker on the page. It lives with the component rather than in
    // the sidebar footer, where it used to: the settings tab renders no JS of its own, so it
    // only ever worked because that unrelated partial happened to ship on the same pages.
    function updateThemeButtons() {
        var mode = 'system';
        if (window.getCurrentTheme) {
            mode = window.getCurrentTheme();
        } else {
            try { mode = localStorage.getItem('theme') || 'system'; } catch (e) {}
        }

        document.querySelectorAll('.js-theme-mode-btn').forEach(function(button) {
            var active = button.getAttribute('data-theme') === mode;
            button.classList.toggle('active', active);
            button.setAttribute('aria-checked', active ? 'true' : 'false');
        });

        // Show only the palettes for the brightness actually rendering, and mark the selected
        // one. Falls back to reading the dark class if the head script has not defined the
        // helpers yet.
        var brightness = window.getThemeBrightness
            ? window.getThemeBrightness()
            : (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        var current = window.getThemeVariant ? window.getThemeVariant(brightness) : null;

        document.querySelectorAll('.js-theme-variant-btn').forEach(function(button) {
            var forThisBrightness = button.getAttribute('data-brightness') === brightness;
            button.classList.toggle('hidden', !forThisBrightness);
            var active = forThisBrightness && button.getAttribute('data-variant') === current;
            button.classList.toggle('active', active);
            button.setAttribute('aria-checked', active ? 'true' : 'false');
        });

        syncRovingTabindex();

        // Anything outside the picker that mirrors the active mode - the sidebar footer's
        // popover trigger glyph - hangs off this instead of being inlined here, so the footer
        // keeps its own concern and this stays the single repaint entry point.
        window.dispatchEvent(new CustomEvent('theme-buttons-updated', { detail: { mode: mode } }));
    }

    // Every radio in a group but the checked one is taken out of the tab order, which is what
    // makes a radiogroup one tab stop with the arrow keys moving inside it. Re-run whenever the
    // selection changes, since the tabbable radio moves with it.
    function syncRovingTabindex() {
        document.querySelectorAll('[role="radiogroup"]').forEach(function(group) {
            var radios = groupRadios(group);
            if (!radios.length) {
                return;
            }
            var checked = radios.filter(function(r) {
                return r.getAttribute('aria-checked') === 'true';
            });
            var tabbable = checked.length ? checked[0] : radios[0];
            radios.forEach(function(radio) {
                radio.setAttribute('tabindex', radio === tabbable ? '0' : '-1');
            });
        });
    }

    // The palette group keeps the three off-brightness buttons in the DOM and hides them, so
    // "the radios in this group" is never just a querySelectorAll.
    function groupRadios(group) {
        return [].slice.call(group.querySelectorAll('[role="radio"]')).filter(function(radio) {
            return !radio.classList.contains('hidden');
        });
    }

    window.updateThemeButtons = updateThemeButtons;

    // Delegated, and bound once. This component renders up to three times per page (the footer
    // popover twice, plus the settings tab), and the settings copy is parsed after this script
    // runs, so direct per-button listeners would both double-bind and miss it.
    if (!window.__themePickerBound) {
        window.__themePickerBound = true;

        // Picking a mode or a palette deliberately leaves the popover OPEN. The Flutter menu
        // closes on select, but it only carries the three modes; this one also carries the
        // palette row, which re-populates the moment the brightness flips - so closing would
        // force a second trip for the likeliest next click. The page repaints live either way.
        document.addEventListener('click', function(e) {
            var modeBtn = e.target.closest('.js-theme-mode-btn');
            if (modeBtn && typeof window.setTheme === 'function') {
                window.setTheme(modeBtn.getAttribute('data-theme'));
                updateThemeButtons();
                return;
            }
            var variantBtn = e.target.closest('.js-theme-variant-btn');
            if (variantBtn && typeof window.setThemeVariant === 'function') {
                window.setThemeVariant(
                    variantBtn.getAttribute('data-brightness'),
                    variantBtn.getAttribute('data-variant')
                );
                updateThemeButtons();
            }
        });

        // Arrow-key navigation inside the mode and palette radiogroups. Declaring role="radio"
        // promises this: a radiogroup is one tab stop, and the arrows move within it. Selection
        // follows focus, which is the standard pattern and doubles as a live preview here.
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft' && e.key !== 'ArrowDown' &&
                e.key !== 'ArrowUp' && e.key !== 'Home' && e.key !== 'End') {
                return;
            }
            var radio = e.target.closest('[role="radio"]');
            var group = radio && radio.closest('[role="radiogroup"]');
            if (!group) {
                return;
            }
            var radios = groupRadios(group);
            var index = radios.indexOf(radio);
            if (index === -1) {
                return;
            }

            // Left/Right follow the visual order, which mirrors in Hebrew and Arabic. Up/Down
            // never mirror.
            var rtl = document.documentElement.getAttribute('dir') === 'rtl';
            var step = 0;
            if (e.key === 'ArrowDown') {
                step = 1;
            } else if (e.key === 'ArrowUp') {
                step = -1;
            } else if (e.key === 'ArrowRight') {
                step = rtl ? -1 : 1;
            } else if (e.key === 'ArrowLeft') {
                step = rtl ? 1 : -1;
            }

            var target;
            if (e.key === 'Home') {
                target = radios[0];
            } else if (e.key === 'End') {
                target = radios[radios.length - 1];
            } else {
                target = radios[(index + step + radios.length) % radios.length];
            }
            if (!target || target === radio) {
                return;
            }

            e.preventDefault();
            target.focus();
            // click(), not a copy of the selection logic: the delegated handler above already
            // owns the one path that writes the theme and repaints.
            target.click();
        });

        // Cross-tab sync: mode and either palette choice.
        window.addEventListener('storage', function(e) {
            if (e.key === 'theme' || e.key === 'themeLight' || e.key === 'themeDark') {
                // Repaint the PAGE first. updateThemeButtons() only reads state and toggles
                // button classes, so on its own it left the other tab highlighting the
                // newly-chosen palette while still rendering the old one - and because it
                // derives which palette row to show from the live `dark` class, it read the
                // stale brightness too. applyTheme() re-applies from storage without writing
                // back, which setTheme() would (echoing the event round again).
                if (typeof window.applyTheme === 'function') window.applyTheme();
                updateThemeButtons();
            }
        });
    }

    // Paint now, then sync again once the document is complete. The second pass is not
    // optional: the render-once guard around this block means it runs at the FIRST picker's
    // position - the desktop rail's copy and the settings tab have not been parsed yet, and
    // without it their palette row stays entirely hidden.
    //
    // (Do not name that Blade directive here. Blade compiles directives inside JS comments
    // just the same, and an unbalanced one 500s every admin page.)
    updateThemeButtons();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateThemeButtons);
    } else {
        requestAnimationFrame(updateThemeButtons);
    }
</script>
@endonce
