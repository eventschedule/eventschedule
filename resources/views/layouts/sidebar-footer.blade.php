{{-- Sidebar footer actions: Help, Contact, About and the theme switcher, pinned to the bottom
     of the rail. Ported from the Flutter app's SidebarFooterActions and the React app's
     HelpSidebarIcons, both of which pin the same row to the bottom-left corner.

     Included by layouts/app-admin.blade.php as a SIBLING of the scrolling nav column rather
     than inside it, so it stays on screen no matter how many schedules the user has. That
     placement is also what lets the theme popover open upward over the nav list: it is outside
     the scroll container, so nothing clips it.

     This renders twice per page (mobile drawer + desktop rail), so there are no ids anywhere -
     every handler is scoped to the closest .sidebar-footer-actions. --}}
@php
    // Declared once: the popover's mode buttons and the strip's trigger glyph render the same
    // three icons.
    $themeModeIcons = [
        'light' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
        'dark' => 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z',
        'system' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    ];
@endphp

{{-- The inline padding-bottom clears the iPhone home indicator on the mobile drawer, which
     reaches the viewport bottom - the same guard Flutter's SidebarFooterActions gets from
     SafeArea(top: false), and the pattern the guest portal's bottom bars already use.
     env(safe-area-inset-bottom) is 0 on desktop, so the rail is unaffected. --}}
<div class="sidebar-footer-actions relative shrink-0 border-t border-white/[0.06] px-6 pt-3"
    style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">

    {{-- Theme popover. inset-x-6, not inset-x-0: an absolutely positioned child resolves its
         offsets against the padding BOX, so inset-x-0 would sit flush with the rail edges
         instead of lining up with the strip below.

         No aria-label on the group: the two radiogroups inside carry their own, and each is
         preceded by a visible heading saying the same thing. --}}
    <div class="js-theme-popover theme-popover-panel absolute inset-x-6 bottom-full mb-2 hidden rounded-xl p-2 shadow-lg"
        role="group">

        <div class="px-1 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ __('messages.appearance') }}</div>

        {{-- Mode: Light / Dark / System --}}
        <div class="theme-switcher-container flex gap-1 rounded-lg p-1.5 w-full" role="radiogroup" aria-label="{{ __('messages.appearance') }}">
            @foreach ($themeModeIcons as $mode => $iconPath)
                @if (! $loop->first)
                    <div class="theme-separator w-px self-stretch my-1.5 bg-white/[0.08] transition-opacity duration-200"></div>
                @endif
                <button
                    type="button"
                    data-theme="{{ $mode }}"
                    class="theme-btn js-theme-mode-btn flex-1 rounded-md px-2 py-1.5 text-sm font-medium text-gray-400 hover:text-white hover:scale-105 active:scale-95 transition-all duration-200"
                    aria-label="{{ __('messages.theme_' . $mode) }}"
                    title="{{ __('messages.theme_' . $mode) }}"
                    role="radio"
                    aria-checked="false">
                    <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}" />
                    </svg>
                </button>
            @endforeach
        </div>

        <div class="px-1 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{ __('messages.palette') }}</div>

        {{-- Palette. Only the three variants for the brightness currently in effect are shown -
             the port of activeBrightness() in the Flutter app's theme_tile.dart. Under System
             that follows the OS, so the JS re-runs this on the matchMedia change event too. --}}
        <div class="theme-switcher-container flex gap-1 rounded-lg p-1.5 w-full" role="radiogroup" aria-label="{{ __('messages.palette') }}">
            @foreach (config('app.ap_palettes') as [$variant, $brightness, $swatchBg, $swatchSurface])
                <button
                    type="button"
                    data-variant="{{ $variant }}"
                    data-brightness="{{ $brightness }}"
                    class="theme-btn js-theme-variant-btn flex-1 rounded-md px-1.5 py-1.5 hover:scale-105 active:scale-95 transition-all duration-200 hidden"
                    aria-label="{{ __('messages.variant_' . $variant) }}"
                    title="{{ __('messages.variant_' . $variant) }}"
                    role="radio"
                    aria-checked="false">
                    <span class="theme-swatch" style="--sw-bg: {{ $swatchBg }}; --sw-surface: {{ $swatchSurface }}"></span>
                    <span class="mt-1 block truncate text-[10px] font-medium leading-tight">{{ __('messages.variant_' . $variant) }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- The action strip. Reuses .theme-switcher-container / .theme-btn so it reads as the same
         grouped control as the pickers it now hides, and the icon-over-caption shape is the one
         the palette swatches above already use.

         None of these carry an aria-label: each has a visible caption, so the caption IS the
         accessible name and the two can never drift. An aria-label would override it - which is
         how the theme button ended up named "Appearance" while reading "Theme", failing WCAG
         2.5.3 for anyone driving the UI by voice. `title` is only on Contact, the one control
         whose tooltip says more than its caption. --}}
    <div class="theme-switcher-container flex gap-1 rounded-lg p-1.5 w-full">

        {{-- User guide. Keeps the js-help-link hook so the delegated anchor-map driver in
             layouts/navigation.blade.php keeps retargeting it per section and tab. --}}
        <a href="{{ \App\Utils\HelpUtils::getDocUrl() }}" target="_blank" rel="noopener noreferrer"
            class="theme-btn js-help-link flex-1 rounded-md px-1.5 py-1.5 transition-all duration-200">
            <svg class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="mt-1 block truncate text-[10px] font-medium leading-tight">{{ __('messages.help') }}</span>
        </a>

        {{-- Contact. Hosted opens the in-app support chat (a chat bubble, because that is what
             it does); selfhost has no chat service, so it opens mail instead - and says so with
             an envelope. Was hidden behind a hover on the Help row until now.

             The caption is the short `contact` key, not `contact_us`: the slot is ~45px wide at
             this font size, so "Contact Us" and most of its translations would clip. The tooltip
             carries the full string. --}}
        @if (config('app.hosted'))
        <button type="button"
            class="theme-btn js-support-chat-sidebar-btn flex-1 rounded-md px-1.5 py-1.5 transition-all duration-200"
            title="{{ __('messages.contact_us') }}">
            <svg class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span class="mt-1 block truncate text-[10px] font-medium leading-tight">{{ __('messages.contact') }}</span>
            <span class="js-support-chat-sidebar-badge absolute top-0.5 end-0.5 inline-flex items-center justify-center min-w-[1rem] h-4 px-1 text-[10px] font-bold text-white bg-red-500 rounded-full" style="display: none;"></span>
        </button>
        @else
        <a href="mailto:{{ config('app.support_email') }}?subject=Feedback"
            class="theme-btn flex-1 rounded-md px-1.5 py-1.5 transition-all duration-200"
            title="{{ __('messages.contact_us') }}">
            <svg class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span class="mt-1 block truncate text-[10px] font-medium leading-tight">{{ __('messages.contact') }}</span>
        </a>
        @endif

        {{-- About. Drives components/modal.blade.php through its open-modal window event. --}}
        <button type="button"
            class="theme-btn js-about-btn flex-1 rounded-md px-1.5 py-1.5 transition-all duration-200">
            <svg class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="mt-1 block truncate text-[10px] font-medium leading-tight">{{ __('messages.about') }}</span>
        </button>

        {{-- Theme. All three glyphs ship inline with the inactive two hidden, so the icon can
             track the active mode (as the Flutter footer's does) without building SVG in JS.
             haspopup="dialog", not "true": "true" resolves to `menu`, and what opens is a pair
             of radiogroups. --}}
        <button type="button"
            class="theme-btn js-theme-popover-btn flex-1 rounded-md px-1.5 py-1.5 transition-all duration-200"
            aria-haspopup="dialog" aria-expanded="false">
            @foreach ($themeModeIcons as $glyph => $iconPath)
                <svg class="js-theme-glyph h-5 w-5 mx-auto {{ $glyph === 'system' ? '' : 'hidden' }}" data-theme-glyph="{{ $glyph }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}" />
                </svg>
            @endforeach
            <span class="mt-1 block truncate text-[10px] font-medium leading-tight">{{ __('messages.theme') }}</span>
        </button>
    </div>
</div>

@once
<style {!! nonce_attr() !!}>
    .theme-switcher-container {
        background: linear-gradient(to bottom, rgb(var(--ap-rail-hover)),
                    color-mix(in srgb, rgb(var(--ap-rail-hover)) 88%, #000)) !important;
        border: 1px solid rgba(255, 255, 255, 0.06);
    }
    .theme-popover-panel {
        background: rgb(var(--ap-rail-deep));
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .theme-btn { position: relative; }
    .theme-btn.active {
        background: rgb(var(--ap-rail-deep)) !important;
        color: #ffffff !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    .theme-btn.active:hover {
        background: color-mix(in srgb, rgb(var(--ap-rail-deep)) 94%, #fff) !important;
        color: #ffffff !important;
    }
    .theme-btn:not(.active) {
        background-color: transparent !important;
        color: rgb(var(--ap-rail-ink-2)) !important;
    }
    .theme-btn:not(.active):hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
    }
    /* These are the only route to Help, Contact and About now, so they need a focus ring a
       keyboard user can actually see against the rail. outline rather than box-shadow: the
       .active state already owns box-shadow for its pressed inset. */
    .theme-btn:focus-visible {
        outline: 2px solid var(--brand-blue);
        outline-offset: 2px;
    }
    /* The popover sits on --ap-rail-deep, which IS .theme-btn.active's background - so an
       active pill inside it would read as a hole rather than a selection. Lift it one step. */
    .theme-popover-panel .theme-btn.active {
        background: color-mix(in srgb, rgb(var(--ap-rail-deep)) 88%, #fff) !important;
    }
    /* Two-tone chip: page ground over card surface, so each palette reads at a glance. */
    .theme-swatch {
        display: block;
        width: 100%;
        height: 14px;
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: linear-gradient(135deg, var(--sw-bg) 0%, var(--sw-bg) 50%,
                    var(--sw-surface) 50%, var(--sw-surface) 100%);
    }
</style>

<script {!! nonce_attr() !!}>
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

        // The strip's theme trigger shows the active mode's glyph. querySelectorAll, not
        // querySelector: there are two triggers on the page (drawer + desktop rail).
        document.querySelectorAll('.js-theme-glyph').forEach(function(glyph) {
            glyph.classList.toggle('hidden', glyph.getAttribute('data-theme-glyph') !== mode);
        });

        // Show only the palettes for the brightness actually rendering, and mark
        // the selected one. Falls back to reading the dark class if the head
        // script has not defined the helpers yet.
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

        // Hide separators touching the active button.
        document.querySelectorAll('.theme-separator').forEach(function(sep) {
            var prev = sep.previousElementSibling;
            var next = sep.nextElementSibling;
            sep.style.opacity = (prev && prev.classList.contains('active')) ||
                                (next && next.classList.contains('active')) ? '0' : '1';
        });

        syncRovingTabindex();
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

    // Closes every open theme popover. `except` is the trigger being toggled (so a click on an
    // already-open trigger is not closed twice); `restoreFocus` pulls focus back to the trigger,
    // which Escape wants and an outside click does not.
    function closeThemePopovers(except, restoreFocus) {
        document.querySelectorAll('.js-theme-popover-btn[aria-expanded="true"]').forEach(function(btn) {
            if (btn === except) {
                return;
            }
            var wrap = btn.closest('.sidebar-footer-actions');
            var popover = wrap ? wrap.querySelector('.js-theme-popover') : null;
            // Opening moves focus INTO the popover, so hiding it (display:none) would drop the
            // keyboard user back on <body> with their place in the page lost. Pull focus to the
            // trigger whenever it was inside, whatever the caller asked for.
            var heldFocus = !!(popover && popover.contains(document.activeElement));
            if (popover) {
                popover.classList.add('hidden');
            }
            btn.setAttribute('aria-expanded', 'false');
            btn.classList.remove('active');
            if (restoreFocus || heldFocus) {
                btn.focus();
            }
        });
    }

    // Delegated, and bound once. The footer renders twice (desktop and mobile sidebars), and
    // pickers can also render later in the page body - the profile Appearance tab does - so
    // direct per-button listeners would both double-bind here and miss anything parsed after
    // this script.
    if (!window.__themePickerBound) {
        window.__themePickerBound = true;
        document.addEventListener('click', function(e) {
            var popoverBtn = e.target.closest('.js-theme-popover-btn');
            if (popoverBtn) {
                var wrap = popoverBtn.closest('.sidebar-footer-actions');
                var popover = wrap ? wrap.querySelector('.js-theme-popover') : null;
                if (!popover) {
                    return;
                }
                var willOpen = popover.classList.contains('hidden');
                closeThemePopovers(popoverBtn, false);
                popover.classList.toggle('hidden', !willOpen);
                popoverBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                popoverBtn.classList.toggle('active', willOpen);
                if (willOpen) {
                    // Land a keyboard user inside the popover rather than leaving them on the
                    // trigger with the controls unreachable without a fresh Tab run.
                    var first = popover.querySelector('.js-theme-mode-btn.active')
                        || popover.querySelector('.js-theme-mode-btn');
                    if (first) {
                        first.focus();
                    }
                }
                return;
            }

            // Picking a mode or a palette deliberately leaves the popover OPEN. The Flutter
            // menu closes on select, but it only carries the three modes; this one also
            // carries the palette row, which re-populates the moment the brightness flips -
            // so closing would force a second trip for the likeliest next click. The page
            // repaints live behind the popover either way.
            var modeBtn = e.target.closest('.js-theme-mode-btn');
            if (modeBtn) {
                setTheme(modeBtn.getAttribute('data-theme'));
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
                return;
            }

            if (e.target.closest('.js-about-btn')) {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'about-app' }));
                return;
            }

            if (e.target.closest('.js-support-chat-sidebar-btn')) {
                window.dispatchEvent(new CustomEvent('show-support-chat'));
                return;
            }

            // Anything else outside an open popover dismisses it.
            if (!e.target.closest('.js-theme-popover')) {
                closeThemePopovers(null, false);
            }
        });

        // Arrow-key navigation inside the mode and palette radiogroups. Declaring role="radio"
        // promises this: a radiogroup is one tab stop, and the arrows move within it. Selection
        // follows focus, which is the standard pattern and doubles as a live preview here.
        // Delegated on document, so it also covers the pickers on the profile Appearance tab.
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

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') {
                return;
            }
            if (!document.querySelector('.js-theme-popover-btn[aria-expanded="true"]')) {
                return;
            }
            // The mobile drawer binds its own Escape-to-close on document while it is open.
            // This listener is registered at parse time and therefore runs first, so stopping
            // immediately keeps one Escape from dismissing the popover AND the drawer under it.
            e.stopImmediatePropagation();
            e.preventDefault();
            closeThemePopovers(null, true);
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

    // Paint the buttons now, then sync again once the document is complete. The second pass is
    // not optional: the render-once guard around this block means it runs at the FIRST
    // sidebar's position - the desktop rail and the profile Appearance tab have not been
    // parsed yet, and without it their palette row stays entirely hidden.
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
