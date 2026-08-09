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
@php($variants = $variants ?? false)
<script {!! nonce_attr() !!}>
    (function() {
        var KEY = 'theme', KEY_LIGHT = 'themeLight', KEY_DARK = 'themeDark';
        var VARIANTS = @json($variants);
        var LIGHT = ['sand', 'mist', 'paper'];
        var DARK = ['espresso', 'midnight', 'carbon'];
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

        // ?dark=true pins dark mode, for embeds on a third-party page.
        var forced = false;
        try {
            forced = new URLSearchParams(window.location.search).get('dark') === 'true';
        } catch (e) {}

        apply(forced ? 'dark' : mode());

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
