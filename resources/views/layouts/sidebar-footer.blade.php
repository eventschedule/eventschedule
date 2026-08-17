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
    // The trigger glyph below renders all three modes and hides two. Same source as the icons
    // above each mode in components/theme-picker.blade.php, so the two cannot drift.
    $themeModeIcons = config('app.ap_theme_mode_icons');
@endphp

{{-- No padding of its own: the band below is full-bleed and owns the rail's whole width, so
     every edge inset and the top border live on it, not here. All this element does is give
     the popover and the hover labels something to position against. --}}
<div class="sidebar-footer-actions relative shrink-0">

    {{-- Theme popover. inset-x-6, not inset-x-0: an absolutely positioned child resolves its
         offsets against the padding BOX, which here is the full rail width - so inset-x-0
         would sit flush with the rail edges. The band below deliberately does run edge to
         edge; this floats above it and should read as a panel, hence the 24px inset.

         No aria-label on the group: the two radiogroups inside carry their own, and each is
         preceded by a visible heading saying the same thing. --}}
    <div class="js-theme-popover theme-popover-panel absolute inset-x-6 bottom-full mb-2 hidden rounded-xl p-2 shadow-lg"
        role="group">
        {{-- Same component the Appearance tab in settings renders, in its rail tone. Only the
             palette row's "show just this brightness" behaviour is dynamic, and that lives in
             the component's driver, so nothing about it is popover-specific. --}}
        <x-theme-picker tone="rail" headings="compact" />
    </div>

    {{-- The action strip: a full-bleed band across the foot of the rail rather than a rounded
         card inset in its gutter, so it reads as the rail's footer instead of a widget parked
         in it. The picker rows inside the popover above stay inset and rounded (.tp-group); the
         band needs a top-only border and no radius, so it has its own class.

         Icon-only. Each caption moved into a .sidebar-tip that appears on hover and on keyboard
         focus, which is what buys the band its single-line height and lets Contact carry the
         full "Contact Us" string instead of the clipped short form.

         With the captions gone every control here NEEDS an aria-label - the SVGs are
         aria-hidden, so there is nothing else to name them. Keep each aria-label byte-identical
         to its .sidebar-tip text. That is the WCAG 2.5.3 (label in name) guard, and the reason
         the theme button once ended up named "Appearance" while reading "Theme", unusable for
         anyone driving the UI by voice.

         No `title` anywhere: .sidebar-tip replaces it, styled to the rail, instant rather than
         after the browser's ~1s delay, and visible on Tab. --}}
    <div class="sidebar-footer-bar flex w-full gap-1 px-2 pt-2"
        {{-- Clears the iPhone home indicator on the mobile drawer, which reaches the viewport
             bottom - the same guard Flutter's SidebarFooterActions gets from
             SafeArea(top: false), and the pattern the guest portal's bottom bars already use.
             It sits on the band, not the wrapper, so the tint runs all the way down.
             env(safe-area-inset-bottom) is 0 on desktop, so the rail is unaffected. --}}
        style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom));">

        {{-- User guide. Keeps the js-help-link hook so the delegated anchor-map driver in
             layouts/navigation.blade.php keeps retargeting it per section and tab. --}}
        <a href="{{ \App\Utils\HelpUtils::getDocUrl() }}" target="_blank" rel="noopener noreferrer"
            class="theme-btn js-help-link flex-1 rounded-lg py-2.5 transition-all duration-200"
            aria-label="{{ __('messages.help') }}">
            <svg class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="sidebar-tip absolute bottom-full left-1/2 z-10 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md px-2 py-1 text-xs font-medium" aria-hidden="true">{{ __('messages.help') }}</span>
        </a>

        {{-- Contact. Hosted opens the in-app support chat (a chat bubble, because that is what
             it does); selfhost has no chat service, so it opens mail instead - and says so with
             an envelope.

             `contact_us`, not the short `contact` key the caption used to carry: a tooltip has
             no width to clip against, so this and its translations finally fit whole. --}}
        @if (config('app.hosted'))
        <button type="button"
            class="theme-btn js-support-chat-sidebar-btn flex-1 rounded-lg py-2.5 transition-all duration-200"
            aria-label="{{ __('messages.contact_us') }}">
            <svg class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span class="sidebar-tip absolute bottom-full left-1/2 z-10 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md px-2 py-1 text-xs font-medium" aria-hidden="true">{{ __('messages.contact_us') }}</span>
            <span class="js-support-chat-sidebar-badge absolute top-0.5 end-0.5 inline-flex items-center justify-center min-w-[1rem] h-4 px-1 text-[10px] font-bold text-white bg-red-500 rounded-full" style="display: none;"></span>
        </button>
        @else
        <a href="mailto:{{ config('app.support_email') }}?subject=Feedback"
            class="theme-btn flex-1 rounded-lg py-2.5 transition-all duration-200"
            aria-label="{{ __('messages.contact_us') }}">
            <svg class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span class="sidebar-tip absolute bottom-full left-1/2 z-10 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md px-2 py-1 text-xs font-medium" aria-hidden="true">{{ __('messages.contact_us') }}</span>
        </a>
        @endif

        {{-- About. Drives components/modal.blade.php through its open-modal window event. --}}
        <button type="button"
            class="theme-btn js-about-btn flex-1 rounded-lg py-2.5 transition-all duration-200"
            aria-label="{{ __('messages.about') }}">
            <svg class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="sidebar-tip absolute bottom-full left-1/2 z-10 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md px-2 py-1 text-xs font-medium" aria-hidden="true">{{ __('messages.about') }}</span>
        </button>

        {{-- Theme. All three glyphs ship inline with the inactive two hidden, so the icon can
             track the active mode (as the Flutter footer's does) without building SVG in JS.
             haspopup="dialog", not "true": "true" resolves to `menu`, and what opens is a pair
             of radiogroups. --}}
        <button type="button"
            class="theme-btn js-theme-popover-btn flex-1 rounded-lg py-2.5 transition-all duration-200"
            aria-label="{{ __('messages.theme') }}" aria-haspopup="dialog" aria-expanded="false">
            @foreach ($themeModeIcons as $glyph => $iconPath)
                <svg class="js-theme-glyph h-5 w-5 mx-auto {{ $glyph === 'system' ? '' : 'hidden' }}" data-theme-glyph="{{ $glyph }}"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}" />
                </svg>
            @endforeach
            <span class="sidebar-tip absolute bottom-full left-1/2 z-10 mb-2 -translate-x-1/2 whitespace-nowrap rounded-md px-2 py-1 text-xs font-medium" aria-hidden="true">{{ __('messages.theme') }}</span>
        </button>
    </div>
</div>

@once
<style {!! nonce_attr() !!}>
    .theme-popover-panel {
        background: rgb(var(--ap-rail-deep));
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    /* Same gradient recipe as .tp-rail .tp-group, .dark-nav-hover and .schedule-badge, so the
       band tracks all six palettes. --ap-rail-hover sits one step lighter than the rail's own
       bottom (--ap-rail-deep, via .sidebar-gradient) - that step is what makes the band read
       as a band. Top border only: the other three edges are the rail's. */
    .sidebar-footer-bar {
        background: linear-gradient(to bottom, rgb(var(--ap-rail-hover)),
                    color-mix(in srgb, rgb(var(--ap-rail-hover)) 88%, #000));
        border-top: 1px solid rgba(255, 255, 255, 0.06);
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
    /* The band's captions, moved out of the layout. Built from --ap-rail-deep so it tracks
       every palette rather than needing a .dark twin, and the first real consumer of
       --ap-rail-ink, which app.css ports but nothing used until now.

       Lifted one step toward white, the same trick (and the same ratio) the popover uses on
       its active pill below, and for the same reason: the tip opens over the BOTTOM of the
       rail, which .sidebar-gradient paints in --ap-rail-deep. Left at the raw token it is
       the exact colour of its own backdrop and reads as a hole with a hairline around it. */
    .sidebar-tip {
        background: color-mix(in srgb, rgb(var(--ap-rail-deep)) 88%, #fff);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: rgb(var(--ap-rail-ink));
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
        opacity: 0;
        pointer-events: none;
        transition: opacity 150ms ease-out;
    }
    /* Hover is gated on a real pointer. A tap on a touch screen fires :hover and leaves it
       latched, so an ungated rule would strand the label on screen after every press. Focus
       is deliberately NOT gated - it is the only label a keyboard user gets. */
    @media (hover: hover) {
        .theme-btn:hover .sidebar-tip { opacity: 1; }
    }
    .theme-btn:focus-visible .sidebar-tip { opacity: 1; }
    /* The popover opens into exactly the strip of space the label wants. All three rules here
       compute to the same specificity, so this one wins by being last - keep it last. */
    .js-theme-popover-btn[aria-expanded="true"] .sidebar-tip { opacity: 0; }
</style>

<script {!! nonce_attr() !!}>
    // The popover trigger's glyph tracks the active mode. All three ship inline with two
    // hidden, so this is a class toggle rather than building SVG in JS. querySelectorAll, not
    // querySelector: there are two triggers on the page (drawer + desktop rail).
    //
    // components/theme-picker.blade.php owns the single repaint entry point and fires
    // theme-buttons-updated at the end of it. Riding that keeps this sidebar-only concern out
    // of the picker, which three surfaces now share.
    function syncThemeGlyphs(mode) {
        document.querySelectorAll('.js-theme-glyph').forEach(function(glyph) {
            glyph.classList.toggle('hidden', glyph.getAttribute('data-theme-glyph') !== mode);
        });
    }

    window.addEventListener('theme-buttons-updated', function(e) {
        syncThemeGlyphs(e.detail && e.detail.mode);
    });

    // The picker's first paint runs before that listener exists - its render-once block is
    // emitted at the popover above, which parses first - so seed the glyph here instead of
    // leaving it wrong until the picker's second pass.
    //
    // (Do not name that Blade directive in this comment. Blade compiles directives inside JS
    // comments just the same, and an unbalanced one 500s every admin page.)
    syncThemeGlyphs(window.getCurrentTheme ? window.getCurrentTheme() : 'system');

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

    // Delegated, and bound once. The footer renders twice (desktop and mobile sidebars), so
    // direct per-button listeners would double-bind. The picker's own handlers live in
    // components/theme-picker.blade.php behind their own guard.
    if (!window.__sidebarFooterBound) {
        window.__sidebarFooterBound = true;
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
    }
</script>
@endonce
