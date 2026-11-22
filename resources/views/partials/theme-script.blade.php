{{--
    Pre-paint theme script. Included from layouts/app.blade.php, layouts/auth.blade.php
    and layouts/marketing.blade.php. It must run in <head>, before first paint, or the
    page flashes the wrong palette.

    $variants (default false) turns on the six-palette layer:
      false - toggles only the `dark` class, exactly as this has always worked.
      true  - additionally stamps data-theme="<variant>" on <html>, which is what
              the --ap-* blocks in resources/css/app.css key off.

    Variants are admin portal + auth ONLY, and the flag is deliberately passed in
    per inner layout rather than decided here: layouts/app.blade.php is also the
    shell for the guest portal, because app-guest.blade.php opens with
    <x-app-layout> exactly like app-admin.blade.php does. A guest page must stay
    on the :root/.dark fallback so the six palettes can never fight a schedule
    owner's configured colours.
--}}
@php
    $variants = $variants ?? false;
    // Built here rather than inlined into @json below: a multi-line array literal inside the
    // directive truncates. These are the allow-list setThemeVariant() validates against, so a
    // palette present in config but missing here would render a swatch that silently refuses
    // to apply.
    $lightVariants = [];
    $darkVariants = [];
    foreach (config('app.ap_palettes') as [$paletteName, $paletteBrightness]) {
        if ($paletteBrightness === 'dark') {
            $darkVariants[] = $paletteName;
        } else {
            $lightVariants[] = $paletteName;
        }
    }
@endphp
<script {!! nonce_attr() !!}>
    (function() {
        var KEY = 'theme', KEY_LIGHT = 'themeLight', KEY_DARK = 'themeDark';
        var VARIANTS = @json($variants);
        var LIGHT = @json($lightVariants);
        var DARK = @json($darkVariants);
        var DEFAULT_LIGHT = 'mist', DEFAULT_DARK = 'midnight';

        function get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } }
        function set(k, v) { try { localStorage.setItem(k, v); } catch (e) {} }

        function systemBrightness() {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        function mode() { return get(KEY) || 'system'; }

        // The brightness actually rendering: an explicit light/dark, or the OS
        // preference under 'system'. Drives which palette row the picker shows.
        function brightness(m) {
            m = m || mode();
            return m === 'system' ? systemBrightness() : m;
        }

        function variantFor(b) {
            var allowed = b === 'dark' ? DARK : LIGHT;
            var stored = get(b === 'dark' ? KEY_DARK : KEY_LIGHT);
            if (allowed.indexOf(stored) !== -1) return stored;
            return b === 'dark' ? DEFAULT_DARK : DEFAULT_LIGHT;
        }

        function apply(m) {
            var html = document.documentElement;
            var b = brightness(m);
            if (b === 'dark') { html.classList.add('dark'); } else { html.classList.remove('dark'); }
            if (VARIANTS) { html.setAttribute('data-theme', variantFor(b)); }
        }

        // ?dark=true pins dark mode and ?dark=false pins light, for embeds on a third-party
        // page: the owner knows their website's colours, the visitor's OS preference does not.
        // Without the light pin, a visitor in dark mode got a dark widget on a white site.
        var forced = null;
        try {
            var darkParam = new URLSearchParams(window.location.search).get('dark');
            forced = darkParam === 'true' ? 'dark' : (darkParam === 'false' ? 'light' : null);
        } catch (e) {}
        {{-- The guest pages are in the mode their schedule's look asks for, whatever the visitor's
             setting ($forceMode, from the guest layout); a pin above still wins. --}}
        var FORCE_MODE = @json(in_array($forceMode ?? null, ['dark', 'light'], true) ? $forceMode : null);
        {{-- The first guest page a visitor opens decides light or dark for the rest of the visit, so
             moving between schedules (each with its own palette) never flips the page between the
             two (es_gp_mode, a cookie that lasts until the browser closes; on the platform's own
             domain it is set there, so it carries across schedules' subdomains). A schedule then
             shows its own palette when it is of that brightness, and its automatic one otherwise.
             A cookie cannot cross to another domain (a schedule's own, or the platform's from one),
             so a link that leaves for one carries the mode in its fragment (#es_mode=dark), which
             the page it opens takes, stores and removes from the address (never sent to a server). --}}
        if (!forced && FORCE_MODE) {
            var carryDomain = @json($carryDomain ?? null);
            var carryHosts = @json(array_values($carryHosts ?? []));
            var underBase = function (h) { return !!carryDomain && (h === carryDomain || h.slice(-(carryDomain.length + 1)) === '.' + carryDomain); };
            var writeMode = function (m) {
                try { document.cookie = 'es_gp_mode=' + m + '; path=/; SameSite=Lax' + (underBase(window.location.hostname) ? '; domain=' + carryDomain : '') + (window.location.protocol === 'https:' ? '; Secure' : ''); } catch (e) {}
            };
            var carried = null;
            try {
                var hm = /^#es_mode=(light|dark)(?:&(.*))?$/.exec(window.location.hash);
                if (hm) {
                    carried = hm[1];
                    writeMode(carried);
                    history.replaceState(history.state, '', window.location.pathname + window.location.search + (hm[2] ? '#' + hm[2] : ''));
                    if (hm[2]) {
                        document.addEventListener('DOMContentLoaded', function () { try { var t = document.getElementById(decodeURIComponent(hm[2])); if (t) { t.scrollIntoView(); } } catch (e) {} });
                    }
                }
            } catch (e) {}
            {{-- A palette the owner picked is their page's mode whatever the visit carried (an owner
                 who looked at their page in dark, then picked a light palette, kept seeing dark until
                 the browser closed), and it is what the rest of the visit carries from here. --}}
            if (@json((bool) ($modeChosen ?? false))) {
                carried = FORCE_MODE;
                writeMode(carried);
            }
            if (!carried) {
                try { var cm = document.cookie.match(/(?:^|;\s*)es_gp_mode=(light|dark)(?:;|$)/); carried = cm ? cm[1] : null; } catch (e) {}
            }
            if (!carried) {
                carried = FORCE_MODE;
                writeMode(carried);
            }
            forced = carried;
            {{-- Only a link to another of this platform's guest pages, and only when it leaves
                 the domain the cookie covers. --}}
            var decorate = function (e) {
                var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
                if (!a) { return; }
                try {
                    var u = new URL(a.href, window.location.href);
                    var here = window.location.hostname, there = u.hostname;
                    if (there === here || (underBase(here) && underBase(there))) { return; }
                    if (!underBase(there) && carryHosts.indexOf(there) === -1) { return; }
                    if (/^#?es_mode=/.test(u.hash)) { return; }
                    var rest = u.hash ? u.hash.slice(1) : '';
                    u.hash = 'es_mode=' + carried + (rest ? '&' + rest : '');
                    a.href = u.toString();
                } catch (err) {}
            };
            document.addEventListener('click', decorate, true);
            document.addEventListener('auxclick', decorate, true);
            document.addEventListener('contextmenu', decorate, true);
        }

        apply(forced || mode());

        if (!forced) {
            var mq = window.matchMedia('(prefers-color-scheme: dark)');
            var onChange = function() {
                if (mode() !== 'system') return;
                apply('system');
                if (typeof window.updateThemeButtons === 'function') window.updateThemeButtons();
            };
            if (mq.addEventListener) { mq.addEventListener('change', onChange); }
            else if (mq.addListener) { mq.addListener(onChange); } // iOS Safari < 14
        }

        // Re-apply from whatever is in storage right now, WITHOUT writing to it. The cross-tab
        // `storage` listener in layouts/navigation needs this: it only had setTheme(), which
        // writes and would echo the event back, so it settled for repainting the buttons - and
        // the other tab then highlighted a palette while still rendering the old one.
        window.applyTheme = function() { apply(mode()); };

        window.getCurrentTheme = function() { return mode(); };
        window.getThemeBrightness = function() { return brightness(); };
        window.getThemeVariant = function(b) { return variantFor(b || brightness()); };

        window.setTheme = function(m) {
            set(KEY, m);
            apply(m);
            if (typeof window.updateThemeButtons === 'function') window.updateThemeButtons();
        };

        window.setThemeVariant = function(b, v) {
            var allowed = b === 'dark' ? DARK : LIGHT;
            if (allowed.indexOf(v) === -1) return;
            set(b === 'dark' ? KEY_DARK : KEY_LIGHT, v);
            apply(mode());
            if (typeof window.updateThemeButtons === 'function') window.updateThemeButtons();
        };

        // The picker markup renders later in the body; nudge it once it exists.
        requestAnimationFrame(function() {
            if (typeof window.updateThemeButtons === 'function') window.updateThemeButtons();
        });
    })();
</script>
