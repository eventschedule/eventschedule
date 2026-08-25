<?php

if (! function_exists('webp_path')) {
    /**
     * Convert an image path to its WebP equivalent
     * e.g. 'images/headers/Arena.png' -> 'images/headers/Arena.webp'
     */
    function webp_path(string $path): string
    {
        return preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $path);
    }
}

if (! function_exists('csp_nonce')) {
    /**
     * Get the CSP nonce for the current request
     */
    function csp_nonce(): string
    {
        return \App\Helpers\SecurityHelper::cspNonce();
    }
}

if (! function_exists('nonce_attr')) {
    /**
     * Generate nonce attribute for script tags
     */
    function nonce_attr(): string
    {
        return \App\Helpers\SecurityHelper::nonceAttr();
    }
}

if (! function_exists('inject_csp_nonce')) {
    /**
     * Add the current request's CSP nonce to any <script> tag that lacks one.
     *
     * Operator-provided header/footer snippets (e.g. Google Tag Manager) contain
     * inline <script> tags with no nonce; under our nonce-based CSP those would be
     * blocked. Injecting the nonce lets them execute. This is only ever applied to
     * trusted super-admin (operator) input, never to schedule-owner content.
     */
    function inject_csp_nonce(?string $html): string
    {
        $html = trim($html ?? '');
        if ($html === '') {
            return '';
        }

        $nonce = csp_nonce();

        return preg_replace('/<script\b(?![^>]*\bnonce=)/i', '<script nonce="'.$nonce.'"', $html);
    }
}

if (! function_exists('consent_required')) {
    /**
     * Whether this install has anything a visitor must consent to.
     *
     * Drives the cookie banner, and by extension the 30-day UTM attribution cookies in
     * CaptureUtmParameters, which are written only once the banner has been accepted. An
     * install with no third party configured and no COOKIE_CONSENT_BANNER shows no banner
     * and sets no non-essential cookie at all: attribution falls back to the session, which
     * rides the strictly-necessary session cookie and needs no consent.
     *
     * Every check here is env-only on purpose. This runs on every page render, including
     * pages that must not touch the database, so do not reach for AdsService::setting()
     * or the settings table.
     */
    function consent_required(): bool
    {
        return (bool) config('services.google.analytics')
            || (bool) config('ads.enabled')
            || (bool) config('stay22.enabled')
            || (bool) config('app.cookie_consent_banner');
    }
}

if (! function_exists('get_translated_categories')) {
    /**
     * Returns an [id => translated name] map of categories.
     * With a Role, returns that schedule's effective enabled list
     * (custom categories included). Without, returns the 12 system defaults.
     */
    function get_translated_categories(?\App\Models\Role $role = null, ?string $locale = null): array
    {
        if ($role) {
            // With no explicit locale, surface the target-language category names while the guest is
            // viewing the translation (mirrors how name/description use showing_translation()).
            if ($locale === null && showing_translation($role)) {
                $locale = $role->translation_language_code ?: 'en';
            }
            $out = [];
            foreach ($role->getEventCategories($locale) as $entry) {
                $out[$entry['id']] = $entry['name'];
            }

            return $out;
        }

        $categories = config('app.event_categories', []);
        $translatedCategories = [];

        foreach ($categories as $id => $englishName) {
            // Convert category name to translation key format
            // First replace " & " with "_&_", then replace remaining spaces with "_"
            $key = strtolower($englishName);
            $key = str_replace(' & ', '_&_', $key);
            $key = str_replace(' ', '_', $key);
            $translatedCategories[$id] = $locale ? __("messages.{$key}", [], $locale) : __("messages.{$key}");
        }

        return $translatedCategories;
    }
}

if (! function_exists('is_valid_language_code')) {
    /**
     * Check if a language code is supported by the application
     */
    function is_valid_language_code(?string $languageCode): bool
    {
        if (empty($languageCode)) {
            return false;
        }

        $supportedLanguages = config('app.supported_languages', ['en' => 'english']);

        return array_key_exists($languageCode, $supportedLanguages);
    }
}

if (! function_exists('detect_content_language')) {
    /**
     * Best-effort detection of the dominant NON-Latin script in event content, returned as a
     * supported language code ('he', 'ar', 'ru') or null. Deliberately conservative: it only returns
     * a code when a non-Latin script is at least as prevalent as Latin letters, so it can OVERRIDE an
     * account/UI language without ever clobbering correct Latin-script content. It decides on the
     * first argument that contains letters (pass the event name first, description second), so a long
     * Latin description can't dilute a short non-Latin name. Ranges mirror AbstractEventDesign.
     */
    function detect_content_language(?string ...$parts): ?string
    {
        foreach ($parts as $text) {
            $text = is_string($text) ? trim($text) : '';
            if ($text === '') {
                continue;
            }

            $candidates = [
                'he' => preg_match_all('/[\x{0590}-\x{05FF}\x{FB1D}-\x{FB4F}]/u', $text),
                'ar' => preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text),
                'ru' => preg_match_all('/[\x{0400}-\x{04FF}]/u', $text),
            ];
            $latin = preg_match_all('/[A-Za-z]/', $text);

            arsort($candidates);
            $code = array_key_first($candidates);
            $count = $candidates[$code];

            // A letter-less part (emoji / numbers only) tells us nothing - try the next one.
            if ($count === 0 && $latin === 0) {
                continue;
            }

            // Confident override only when a non-Latin script is at least as prevalent as Latin.
            return ($count > 0 && $count >= $latin && is_valid_language_code($code)) ? $code : null;
        }

        return null;
    }
}

if (! function_exists('showing_translation')) {
    /**
     * Whether the current request is displaying a schedule's TRANSLATED content (stored in the
     * `_en` columns) rather than the authored language. The guest controllers set the `translate`
     * session flag when a visitor switches to the schedule's target language - that flag is the
     * authoritative signal for the guest flow. As a fallback for the same request, a passed model
     * that exposes its own target (a Role, via `translation_language_code`) also matches an
     * explicit `?lang=<target>`. Models without that column (Event/EventPart/Group) resolve to
     * the default `'en'` here and rely on the session flag, which is always set for real renders.
     */
    function showing_translation($role = null): bool
    {
        if (session()->has('translate')) {
            return true;
        }

        return $role && request()->lang == ($role->translation_language_code ?: 'en');
    }
}

if (! function_exists('is_mobile')) {
    /**
     * Check if the current user is on a mobile device
     */
    function is_mobile(): bool
    {
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent'));
    }
}

if (! function_exists('requested_event_layout')) {
    /**
     * The event layout asked for by ?layout= on the current request, or null when the
     * parameter is absent or unrecognised (unrecognised falls through to the schedule's
     * own setting rather than erroring). "grid" and "month" are undocumented aliases for
     * "calendar" so a guessed value still works; the docs only name calendar and list,
     * because ?layout=grid means something different on the event-graphic routes.
     */
    function requested_event_layout(): ?string
    {
        $requested = request()->query('layout');

        if (! is_string($requested)) {
            return null;
        }

        return match (strtolower(trim($requested))) {
            'calendar', 'grid', 'month' => 'calendar',
            'list' => 'list',
            default => null,
        };
    }
}

if (! function_exists('is_rtl')) {
    /**
     * Check if the current user is on a rtl language
     */
    function is_rtl(): bool
    {
        $locale = app()->getLocale();

        return in_array($locale, ['ar', 'he']);
    }
}

if (! function_exists('rtl_class')) {
    /**
     * Return RTL or LTR class based on role's RTL setting
     * In admin context, uses the authenticated user's language instead
     *
     * @param  object|null  $role  The role object (or null)
     * @param  string  $rtlClass  The class to return for RTL
     * @param  string  $ltrClass  The class to return for LTR (default empty)
     * @param  bool  $useAdminContext  When true, use authenticated user's language instead of role's
     */
    function rtl_class(?object $role, string $rtlClass, string $ltrClass = '', bool $useAdminContext = false): string
    {
        // In admin context, use ONLY the authenticated user's language preference
        if ($useAdminContext && auth()->check()) {
            return auth()->user()->isRtl() ? $rtlClass : $ltrClass;
        }

        if ($role && method_exists($role, 'isRtl') && $role->isRtl()) {
            return $rtlClass;
        }

        return $ltrClass;
    }
}

if (! function_exists('dir_script_pattern')) {
    /**
     * PCRE character-class body for one side of the bidi split ('rtl' or 'ltr').
     *
     * Kept in one place so the counter (detect_content_dir) and the presence test
     * (has_rtl_text) can never drift apart.
     */
    function dir_script_pattern(string $side): string
    {
        return $side === 'rtl'
            ? '\p{Hebrew}\p{Arabic}\p{Syriac}\p{Thaana}'
            : '\p{Latin}\p{Greek}\p{Cyrillic}';
    }
}

if (! function_exists('strip_dir_noise')) {
    /**
     * Drop the parts of a string that say nothing about its direction: HTML tags and URLs.
     *
     * Accepts either markdown or rendered HTML. Without this, a `<strong>` tag name and an
     * href's Latin characters drag the count toward 'ltr'.
     *
     * The `?? $text` is load-bearing: a /u pattern returns NULL on malformed UTF-8, and one bad
     * byte from an iCal or scraper import reaches this on every event name and description. The
     * original string is handed back instead, so the callers' own /u matches fail the same way
     * and they degrade to "no opinion" rather than throwing a TypeError out of a guest page.
     */
    function strip_dir_noise(?string $text): string
    {
        $text = html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_replace('~\b(?:https?://|www\.)\S+~iu', '', $text) ?? $text;
    }
}

if (! function_exists('detect_content_dir')) {
    /**
     * Base direction ('rtl'|'ltr') of a piece of text, or null when it has nothing to go on.
     *
     * Whichever script has more strong directional characters wins, with the first strong
     * character breaking an exact tie. That beats first-strong (`dir="auto"`) detection for
     * real content: "DJ Mike presents: <hebrew>" is Hebrew text that first-strong would call
     * LTR. Same policy as detectDir() in resources/js/editor-helpers.js, tie-break included -
     * though not the same alphabet: the JS side counts Latin only (no Greek or Cyrillic), spans
     * whole Unicode blocks rather than script properties, and does not strip tags or URLs.
     *
     * This answers "which script is this text mostly written in", which is not always the
     * same question as "which way should this element read". Callers that already know the
     * authoring language - content_dir() and content_dir_for_language() - only consult it
     * where that language leaves genuine doubt; see resolve_content_dir().
     */
    function detect_content_dir(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $text = strip_dir_noise($text);

        $rtl = preg_match_all('/['.dir_script_pattern('rtl').']/u', $text);
        $ltr = preg_match_all('/['.dir_script_pattern('ltr').']/u', $text);

        if (! $rtl && ! $ltr) {
            return null;
        }

        // Exact tie: the first strong character decides, matching detectDir(). The `> 0` and the
        // `?? ''` are belt and braces - the early return above already guarantees a strong
        // character exists, but that guarantee lives in a different if.
        if ($rtl === $ltr && $rtl > 0) {
            preg_match('/['.dir_script_pattern('rtl').dir_script_pattern('ltr').']/u', $text, $first);

            return preg_match('/['.dir_script_pattern('rtl').']/u', $first[0] ?? '') ? 'rtl' : 'ltr';
        }

        return $rtl > $ltr ? 'rtl' : 'ltr';
    }
}

if (! function_exists('has_rtl_text')) {
    /**
     * True when $text contains at least one strong right-to-left letter.
     *
     * Deliberately presence, not majority. An RTL language routinely embeds Latin script -
     * band names, venue names, hashtags - while LTR text essentially never embeds Hebrew or
     * Arabic. So inside a known-RTL context "there is Hebrew here" settles the question,
     * while counting the two scripts symmetrically does not: a Hebrew band title spends more
     * letters on the Latin name than on the Hebrew words around it, so detect_content_dir()
     * called it LTR and the browser pushed its punctuation to the wrong end of the line.
     *
     * \p{L} is what makes "letter" true rather than aspirational. Those script blocks also hold
     * characters that are not strong R - Arabic-Indic digits (bidi class AN), Hebrew niqqud and
     * Arabic harakat (NSM), and punctuation like the Arabic comma and percent sign. Matching any
     * of them would let a single stray mark inside an otherwise-Latin string flip a whole title.
     * detect_content_dir() does not draw that distinction; that is a pre-existing quirk of the
     * counter, left alone deliberately so its pinned tests keep their meaning.
     */
    function has_rtl_text(?string $text): bool
    {
        if ($text === null || trim($text) === '') {
            return false;
        }

        return (bool) preg_match('/(?=['.dir_script_pattern('rtl').'])\p{L}/u', strip_dir_noise($text));
    }
}

if (! function_exists('resolve_content_dir')) {
    /**
     * The shared policy behind content_dir() and content_dir_for_language().
     *
     * The known authoring language leads and the text only overrides it where the text is
     * trustworthy, which is asymmetric on purpose:
     *
     *  - Known-RTL: the presence of any RTL letter settles it, because Latin inside RTL text
     *    is ordinary and proves nothing. Text with Latin but no RTL letters reads LTR; text with
     *    NO strong characters at all ("2026", "12:00 - 18:00", an emoji) has nothing to say and
     *    falls through to the language, which is what an empty string does too. Answering 'ltr'
     *    for those was a regression - a blank field stayed RTL while a field holding "2026"
     *    flipped.
     *  - Known-LTR: an aggregated event may genuinely be in the other language (a Hebrew
     *    event surfaced on an English curator's page), so the majority rule still overrides.
     *
     * Do not "simplify" this into a symmetric rule. Consulting the character counts ahead of
     * the known language is exactly what made Hebrew titles containing a Latin band name
     * render backwards.
     */
    function resolve_content_dir(?string $text, string $fallback): string
    {
        if (trim((string) $text) === '') {
            return $fallback;
        }

        if ($fallback === 'rtl') {
            return has_rtl_text($text) ? 'rtl' : (detect_content_dir($text) ?: $fallback);
        }

        return detect_content_dir($text) ?: $fallback;
    }
}

if (! function_exists('content_dir')) {
    /**
     * Base direction ('rtl'|'ltr') for schedule content.
     *
     * The language fallback is the schedule's own: for authored content its authoring
     * language (viewer-independent, via isContentRtl) so mixed Latin/Hebrew text keeps the
     * schedule's intended base direction, matching the WhatsApp export; for the translated
     * (`_en`) value its TARGET language, so an RTL translation renders correctly. Defaults
     * to 'en' (=> 'ltr'), reproducing the original behavior.
     *
     * When $content is given, resolve_content_dir() decides how much say it gets.
     *
     * @param  object|null  $role  The schedule whose language governs the content
     * @param  bool  $showingTranslation  True when the translated (`_en`) value is shown
     * @param  string|null  $content  The text being rendered, when it is available
     */
    function content_dir(?object $role, bool $showingTranslation = false, ?string $content = null): string
    {
        if ($showingTranslation) {
            $target = ($role && ! empty($role->translation_language_code)) ? $role->translation_language_code : 'en';
            $fallback = in_array($target, ['ar', 'he']) ? 'rtl' : 'ltr';
        } else {
            $fallback = ($role && method_exists($role, 'isContentRtl') && $role->isContentRtl()) ? 'rtl' : 'ltr';
        }

        return resolve_content_dir($content, $fallback);
    }
}

if (! function_exists('content_dir_for_language')) {
    /**
     * Base direction for a string whose language is already known.
     *
     * The content_dir() variant above infers the language from the schedule plus a
     * "showing translation" boolean, which is wrong for an aggregated event whose language
     * pair differs from the viewing schedule's - that is how Hebrew event names ended up
     * tagged 'ltr' on a curator's English view. Once the resolver has picked a string it
     * also knows which language it picked, so pass both and let resolve_content_dir() weigh
     * them.
     *
     * $lang MUST be the language the text was authored in or selected for - never the viewer's
     * UI locale. The known-RTL branch trusts it enough that a single RTL letter settles the
     * direction, so passing app()->getLocale() here makes a Hebrew reader's English document
     * render RTL. A caller that only knows the viewer should use detect_content_dir() directly.
     */
    function content_dir_for_language(?string $text, ?string $lang): string
    {
        return resolve_content_dir($text, in_array($lang, ['ar', 'he'], true) ? 'rtl' : 'ltr');
    }
}

if (! function_exists('platform_currency')) {
    /**
     * The currency code this installation quotes its own prices in.
     */
    function platform_currency(): string
    {
        return \App\Utils\PlatformCurrency::code();
    }
}

if (! function_exists('plan_price')) {
    /**
     * One of OUR prices, rendered in the installation's currency: "$9", "R9", "9 CHF".
     *
     * Use this for every plan amount, platform fee and free-tier zero the app quotes for
     * itself, so an operator sets the currency once instead of editing Blade files. Do NOT
     * use it for money that belongs to a row - a ticket sale renders the currency it was
     * taken in, via MoneyUtils::format($amount, $event->ticket_currency_code).
     */
    function plan_price($amount): string
    {
        return \App\Utils\PlatformCurrency::format($amount);
    }
}

if (! function_exists('marketing_url')) {
    /**
     * Generate a URL for marketing pages
     * Returns configured marketing URL for white-labeled instances
     * Returns eventschedule.com for nexus, local URLs for testing
     */
    function marketing_url(string $path = '/'): string
    {
        if (config('app.is_testing')) {
            return url($path);
        }

        $baseUrl = config('app.marketing_url', 'https://eventschedule.com');

        return $baseUrl.($path === '/' ? '' : $path);
    }
}

if (! function_exists('policy_url')) {
    /**
     * The URL of a legal document (privacy | terms | cookies).
     *
     * An operator can replace any of them from /admin/legal, either by pointing
     * at a policy hosted elsewhere or by writing one in the app (issue #116).
     * Resolution order: external URL -> in-app document -> the built-in page.
     *
     * $fallbackPath is the marketing path to use when nothing is overridden.
     * It is explicit because the call sites are not consistent about the terms
     * document: most link /terms-of-service, while the selfhost branches of the
     * consent checkboxes link /self-hosting-terms-of-service. Passing it keeps
     * each site's existing behaviour exactly.
     */
    function policy_url(string $type, ?string $fallbackPath = null): string
    {
        // Resolved against one index read rather than by calling this helper
        // again for the cookie fallback below, which doubled the cost of the
        // cookie banner on every install that has not written one.
        $index = \App\Models\LegalDocument::index();

        $resolve = function (string $for) use ($index): ?string {
            $document = $index[$for] ?? null;

            if ($document && $document['url']) {
                return $document['url'];
            }

            if (! $document || ! $document['has_content']) {
                return null;
            }

            // NOT url(): that builds against the host the request arrived on, and the
            // legal routes are not registered on every host an install serves.
            //
            //  - On a nexus they carry a domain (Route::domain(_base_domain())), so a
            //    guest on tenant.example.com would be handed a URL that its own /{slug}
            //    catch-all answers instead, and one on app.example.com a hard 404.
            //    route() reads the domain off the route, so it lands on the marketing
            //    host - and unlike marketing_url() it cannot be pointed at an operator's
            //    external site, which would send consent links away from the document
            //    they just wrote.
            //  - Everywhere else the route is domain-less, and the tenant subdomain group
            //    is registered ~1500 lines earlier in routes/web.php, so on a tenant host
            //    /{slug} still wins. app_url() is the one host that group excludes
            //    ('^(?!www|app).*'). On a plain selfhost it collapses to url($path),
            //    which is why path-based installs keep working exactly as before.
            //
            // A literal PATH, never a route() result - app_url() prepends the root, and an
            // absolute path doubles a selfhost's front-controller base path.
            return config('app.is_nexus')
                ? route(\App\Models\LegalDocument::ROUTES[$for])
                : app_url(\App\Models\LegalDocument::PATHS[$for]);
        };

        if ($resolved = $resolve($type)) {
            return $resolved;
        }

        // Nothing written for the cookie policy: the cookie disclosure is part
        // of the privacy policy, which is where the banner has always pointed.
        if ($fallbackPath === null && $type === \App\Models\LegalDocument::COOKIES) {
            return $resolve(\App\Models\LegalDocument::PRIVACY)
                ?? marketing_url(\App\Models\LegalDocument::PATHS[\App\Models\LegalDocument::PRIVACY]);
        }

        return marketing_url($fallbackPath ?? \App\Models\LegalDocument::PATHS[$type]);
    }
}

if (! function_exists('marketing_domain')) {
    /**
     * Get the marketing domain for display (without protocol)
     */
    function marketing_domain(): string
    {
        $url = config('app.marketing_url', 'https://eventschedule.com');

        return preg_replace('#^https?://(www\.)?#', '', $url);
    }
}

if (! function_exists('_base_domain')) {
    /**
     * Extract the base domain from APP_URL by stripping known subdomain prefixes.
     * e.g. "https://app.eventschedule.com" -> "eventschedule.com"
     * e.g. "https://eventschedule.com" -> "eventschedule.com"
     */
    function _base_domain(): string
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';

        // Strip known subdomain prefixes
        return preg_replace('/^(app|www|blog|demo)\./', '', $host);
    }
}

if (! function_exists('blog_url')) {
    /**
     * Generate a URL for blog pages
     * Returns /blog path for testing and selfhosted instances
     * Returns blog.{domain} for hosted production
     */
    function blog_url(string $path = ''): string
    {
        if (config('app.is_testing') || ! config('app.hosted')) {
            return url('/blog'.$path);
        }

        return 'https://blog.'._base_domain().$path;
    }
}

if (! function_exists('demo_url')) {
    /**
     * Generate the URL for the demo schedule
     * Returns local URL for testing, subdomain URL for hosted production
     */
    function demo_url(): string
    {
        if (config('app.is_testing')) {
            return url('/demo');
        }

        return 'https://demo.'._base_domain();
    }
}

if (! function_exists('app_url')) {
    /**
     * Generate a URL for app pages (login, sign up, etc.)
     * Returns app.{domain} for hosted production
     * Returns local URL for testing and selfhosted instances
     */
    function app_url(string $path = '/'): string
    {
        if (config('app.is_testing') || config('app.env') === 'local' || ! config('app.hosted')) {
            return url($path);
        }

        return 'https://app.'._base_domain().$path;
    }
}

if (! function_exists('sitemap_url')) {
    /**
     * The sitemap that applies to the host the current request arrived on.
     *
     * A custom domain gets its own. The global sitemap may not carry a third-party host's URLs -
     * Google reports every one as "URL not allowed" and discards it - so pointing a custom domain
     * at the global sitemap advertises one containing none of that host's pages. Every other host
     * keeps the global sitemap, which on tenant subdomains is also the cross-submission grant that
     * keeps their URLs legal inside it.
     *
     * Not custom_domain_url(): that rewrites the subdomain and app. hosts only, so the apex URL
     * this builds would pass through it unchanged.
     */
    function sitemap_url(): string
    {
        $customDomainHost = request()->attributes->get('custom_domain_host');
        $base = $customDomainHost ? 'https://'.$customDomainHost : config('app.url');

        // Relative form, so the path stays tied to the route definition. It ignores the route's
        // domain constraint, which is what makes it usable from a custom-domain request.
        return $base.route('sitemap', [], false);
    }
}

if (! function_exists('canonical_url')) {
    /**
     * Build an absolute URL whose base path comes from APP_URL rather than from the
     * incoming request.
     *
     * `route()` and `url()` resolve against $request->root(), which includes the base path
     * the front controller happens to be served under. An install whose document root is the
     * project folder answers on both `/` and `/public/`, so the same route can produce two
     * different absolute URLs. That is harmless for a redirect but not for a value we bake
     * into a QR code, which must be stable for the life of the ticket.
     *
     * The scheme and host still come from Laravel's own resolver so custom-domain branding
     * survives. Do NOT read them off the request: AppServiceProvider forces the https scheme
     * outside local, and a proxy that terminates TLS leaves the request looking like plain
     * http, which would bake http:// into a printed ticket. Under a queue worker there is no
     * real request; SetRequestForConsole synthesizes one from APP_URL.
     *
     * Pass the relative form, e.g. canonical_url(route('ticket.view', [...], false)).
     */
    function canonical_url(string $path): string
    {
        $root = \Illuminate\Support\Facades\URL::formatRoot(\Illuminate\Support\Facades\URL::formatScheme());
        $origin = preg_replace('#^(https?://[^/]+).*$#', '$1', $root);

        // A schemeless APP_URL ("host/public") makes parse_url() report the whole string as the
        // path; concatenating that would corrupt the host. Only an absolute path is a base path.
        $base = parse_url(config('app.url'), PHP_URL_PATH) ?: '';
        $base = str_starts_with($base, '/') ? rtrim($base, '/') : '';

        return $origin.$base.'/'.ltrim($path, '/');
    }
}

if (! function_exists('redirect_with_pending_action')) {
    /**
     * Store pending action data in session and redirect.
     * On custom domains, also bridges the data via cache so it survives the
     * cross-domain redirect to app.eventschedule.com for sign-up/login.
     */
    function redirect_with_pending_action(string $url, array $sessionData): \Illuminate\Http\RedirectResponse
    {
        foreach ($sessionData as $key => $value) {
            session([$key => $value]);
        }

        if (request()->attributes->get('custom_domain_host')) {
            $token = \Illuminate\Support\Str::random(40);
            \Illuminate\Support\Facades\Cache::put('pending_action:'.$token, $sessionData, now()->addHour());
            $url .= (str_contains($url, '?') ? '&' : '?').'pa='.$token;
        }

        return redirect($url);
    }
}

if (! function_exists('restore_pending_action')) {
    /**
     * Restore pending action data from cache into the current session.
     * Called on sign-up/login pages to recover data that was stored on a custom domain.
     */
    function restore_pending_action(): void
    {
        $token = request()->query('pa');
        if (! $token || ! is_string($token)) {
            return;
        }

        $data = \Illuminate\Support\Facades\Cache::pull('pending_action:'.$token);
        if (! is_array($data)) {
            return;
        }

        foreach ($data as $key => $value) {
            session([$key => $value]);
        }
    }
}

if (! function_exists('signup_intent_from_session')) {
    /**
     * Classify why the visitor is creating an account, based on the pending-action
     * session markers captured before they were sent to sign up.
     */
    function signup_intent_from_session(): string
    {
        if (session()->has('pending_follow')) {
            return 'follow';
        }

        if (session()->has('pending_request')) {
            return 'request';
        }

        if (session()->has('pending_fan_content')) {
            return 'fan';
        }

        // Someone accepting a schedule handover is signing up to run somebody else's
        // schedule, exactly like an invited team member - so reuse 'team' rather than
        // minting an intent the funnel reporting has never seen.
        if (session()->has('pending_transfer')) {
            return 'team';
        }

        if (session()->has('sms_token')) {
            return 'claim';
        }

        return 'organizer';
    }
}

if (! function_exists('post_signup_redirect_url')) {
    /**
     * Destination right after account creation. Attendee flows (follow/request/
     * fan content) and users who already hold schedules (sms claims, upgraded
     * stubs with memberships) keep the normal home flow; organizer signups go
     * to the schedule-type chooser, or straight to the create form when they
     * picked a type on the marketing site. When passed to redirect()->intended()
     * this still runs (and consumes the type) even if a stored URL wins - that
     * is intentional session cleanup.
     */
    function post_signup_redirect_url(\App\Models\User $user): string
    {
        // roles() (any pivot level) is intentionally broader than the member()
        // check used by home()/gettingStarted(): claimRolesByPhone() preserves
        // the stub's original level, so a claim can land the user as a follower
        // rather than an owner. Either way they already have a schedule tie and
        // should go home, not to the "create your first schedule" chooser.
        if (session()->has('pending_follow')
            || session()->has('pending_request')
            || session()->has('pending_fan_content')
            // Not yet a member of anything - the schedule only becomes theirs once they
            // accept - so this marker is what keeps them out of the "create your first
            // schedule" chooser. home() consumes it and returns them to the offer.
            || session()->has('pending_transfer')
            || $user->roles()->exists()) {
            return route('home', absolute: false);
        }

        $type = session()->pull('signup_role_type');

        if ($type && in_array($type, ['talent', 'venue', 'curator'], true)) {
            return route('new', ['type' => $type], false);
        }

        return route('getting-started', absolute: false);
    }
}

if (! function_exists('payment_gateways')) {
    /**
     * The payment gateway registry, for blades that need to ask what a gateway can do rather than
     * naming it. See App\Services\Payments\PaymentGatewayManager.
     */
    function payment_gateways(): \App\Services\Payments\PaymentGatewayManager
    {
        return app(\App\Services\Payments\PaymentGatewayManager::class);
    }
}

if (! function_exists('is_demo_mode')) {
    /**
     * Check if the current session is in demo mode
     * Demo mode restricts certain settings and features
     */
    function is_demo_mode(): bool
    {
        // Demo mode is only available in hosted or testing mode
        if (! config('app.hosted') && ! config('app.is_testing')) {
            return false;
        }

        // Must be authenticated
        if (! auth()->check()) {
            return false;
        }

        // Check if the user is the demo user
        return auth()->user()->email === \App\Services\DemoService::DEMO_EMAIL;
    }
}

if (! function_exists('can_self_update')) {
    /**
     * Whether the self-updater UI/route is available to the given user.
     *
     * Disabled on nexus (eventschedule.com deploys via git/CI) and in testing.
     * On a multi-tenant self-hosted SaaS (hosted) it is operator-only (admin),
     * so a tenant can't trigger a global update. On a plain selfhost it is
     * available to any authenticated user.
     */
    function can_self_update(?\App\Models\User $user = null): bool
    {
        if (config('app.is_nexus') || config('app.is_testing')) {
            return false;
        }

        if (! config('app.hosted')) {
            return true; // plain selfhost - any authenticated user
        }

        return (bool) ($user ?? auth()->user())?->isAdmin(); // self-hosted SaaS - admin only
    }
}

if (! function_exists('is_demo_role')) {
    /**
     * Check if a given role is the demo role
     * Used to block certain operations (like sending emails) for the demo account
     */
    function is_demo_role(?\App\Models\Role $role): bool
    {
        if (! $role) {
            return false;
        }

        // Demo role check is only relevant in hosted or testing mode
        if (! config('app.hosted') && ! config('app.is_testing')) {
            return false;
        }

        // Deliberately reads the relation rather than caching a resolved demo user id. Eloquent
        // already memoizes $role->user per model instance, so the hot path (one schedule per guest
        // page) costs one query either way, and every id-caching variant tried here went stale the
        // moment a demo user was created after the first lookup.
        return $role->subdomain === \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN
            || ($role->user && $role->user->email === \App\Services\DemoService::DEMO_EMAIL);
    }
}

if (! function_exists('accent_contrast_color')) {
    /**
     * Get contrasting text color (black or white) for an accent color background
     */
    function accent_contrast_color(?string $accentColor): string
    {
        $color = $accentColor ?? '#4E81FA';

        return \App\Utils\ColorUtils::getContrastColor($color);
    }
}

if (! function_exists('get_use_24_hour_time')) {
    /**
     * Get the effective 24-hour time preference.
     * If logged-in user has an explicit preference, use it;
     * otherwise fall back to the role's setting.
     */
    function get_use_24_hour_time(?object $role): bool
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->use_24_hour_time !== null) {
                return (bool) $user->use_24_hour_time;
            }
        }

        return $role && $role->use_24_hour_time ? true : false;
    }
}

if (! function_exists('detect_24_hour_time')) {
    /**
     * Auto-detect 24-hour time preference based on timezone.
     * Europe/Asia/Africa timezones return true.
     * America timezones return false.
     * Unknown returns null.
     */
    function detect_24_hour_time(?string $timezone, ?string $locale): ?bool
    {
        if ($timezone) {
            $prefix = explode('/', $timezone)[0] ?? '';
            if (in_array($prefix, ['Europe', 'Asia', 'Africa'])) {
                return true;
            }
            if ($prefix === 'America') {
                return false;
            }
        }

        return null;
    }
}

if (! function_exists('custom_domain_url')) {
    /**
     * Rewrite a URL to use the custom domain if the current request is via one.
     * Used for URLs passed to external services (e.g. Stripe) that bypass the middleware.
     */
    function custom_domain_url(string $url): string
    {
        $customDomainHost = request()->attributes->get('custom_domain_host');
        if (! $customDomainHost) {
            return $url;
        }
        $subdomain = request()->attributes->get('custom_domain_subdomain');
        $baseDomain = _base_domain();
        $url = str_replace("https://{$subdomain}.{$baseDomain}", "https://{$customDomainHost}", $url);
        $url = str_replace("https://app.{$baseDomain}", "https://{$customDomainHost}", $url);

        return $url;
    }
}

if (! function_exists('get_sub_audience_blog')) {
    /**
     * Get a blog post for a sub-audience by slug
     * Returns null if no matching blog post exists
     *
     * @param  string  $slug  The blog post slug (e.g., 'for-solo-artists')
     */
    function get_sub_audience_blog(string $slug): ?\App\Models\BlogPost
    {
        // Cache for 5 minutes to avoid repeated queries
        return \Illuminate\Support\Facades\Cache::remember(
            'sub_audience_blog_'.$slug,
            300,
            function () use ($slug) {
                return \App\Models\BlogPost::published()
                    ->where('slug', $slug)
                    ->first();
            }
        );
    }
}

if (! function_exists('get_sub_audience_info')) {
    /**
     * Get sub-audience info for a blog post by slug
     * Returns info about the parent audience and sub-audience if the slug matches
     *
     * @param  string  $slug  The blog post slug (e.g., 'for-solo-artists')
     * @return object|null Returns object with parent_page, parent_title, sub_audience_name, icon_color, or null if not found
     */
    function get_sub_audience_info(string $slug): ?object
    {
        $subAudiences = config('sub_audiences', []);

        foreach ($subAudiences as $audienceKey => $audience) {
            foreach ($audience['sub_audiences'] as $subKey => $subAudience) {
                if ($subAudience['slug'] === $slug) {
                    return (object) [
                        'parent_page' => $audience['page'],
                        'parent_title' => $audience['title'],
                        'sub_audience_name' => $subAudience['name'],
                        'icon_color' => $subAudience['icon_color'],
                    ];
                }
            }
        }

        return null;
    }
}

if (! function_exists('public_registration_enabled')) {
    /**
     * Whether anyone may create an account on this install.
     *
     * Hosted (and self-hosted SaaS) installs are always open. A plain selfhost is
     * single-user by default - the first account is the instance admin - and only
     * accepts further sign-ups when the operator sets ALLOW_REGISTRATION=true.
     * Every account-creation path (sign-up form, API register, the guest-submit
     * account step, Google OAuth) gates on this.
     */
    function public_registration_enabled(): bool
    {
        return (bool) config('app.hosted') || (bool) config('app.allow_registration');
    }
}

if (! function_exists('selfhost_needs_setup')) {
    /**
     * Whether a selfhosted install still needs the first-run setup wizard.
     *
     * Returns true when selfhost is not yet configured: APP_URL is blank, or APP_URL is
     * set but the database has no `users` table (migrations never ran / were wiped). The
     * setup wizard is the sign-up page, which keys off this so a failed or partial setup
     * stays recoverable instead of locking the user out. Always false in hosted/testing
     * mode. Result is memoized per worker process so the schema check runs at most once.
     */
    function selfhost_needs_setup(): bool
    {
        if (config('app.hosted') || config('app.is_testing')) {
            return false;
        }

        if (empty(config('app.url'))) {
            return true; // fresh install, no DB query needed
        }

        // APP_URL is set, which only happens after a successful migrate, so only treat
        // this as "needs setup" when we can CONFIRM the schema is gone.
        static $needsSetup = null;
        if ($needsSetup === null) {
            try {
                $needsSetup = ! \Illuminate\Support\Facades\Schema::hasTable('users');
            } catch (\Throwable $e) {
                // Connection error with APP_URL set means a transient DB outage on an
                // already-configured install, not a fresh install. Return false so a DB
                // blip does not redirect all traffic to (and expose) the setup wizard;
                // the normal error surfaces instead. Blanking APP_URL still recovers the
                // wizard for a genuine reconfigure.
                $needsSetup = false;
            }
        }

        return $needsSetup;
    }
}
