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

if (! function_exists('get_translated_categories')) {
    /**
     * Returns an [id => translated name] map of categories.
     * With a Role, returns that schedule's effective enabled list
     * (custom categories included). Without, returns the 12 system defaults.
     */
    function get_translated_categories(?\App\Models\Role $role = null, ?string $locale = null): array
    {
        if ($role) {
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

if (! function_exists('is_mobile')) {
    /**
     * Check if the current user is on a mobile device
     */
    function is_mobile(): bool
    {
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent'));
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

if (! function_exists('content_dir')) {
    /**
     * Base direction ('rtl'|'ltr') for schedule-authored content.
     *
     * Uses the schedule's language (viewer-independent, via isContentRtl) so that
     * mixed Latin/Hebrew text keeps the schedule's intended base direction, matching
     * the WhatsApp export. Unlike is_rtl()/rtl_class(), it does not depend on the
     * viewer's translate state. Pass $showingEnglish = true to force LTR when a real
     * English value is the one actually being displayed.
     *
     * @param  object|null  $role  The schedule whose language governs the content
     * @param  bool  $showingEnglish  True when a genuine English value is shown
     */
    function content_dir(?object $role, bool $showingEnglish = false): string
    {
        if ($showingEnglish) {
            return 'ltr';
        }

        return ($role && method_exists($role, 'isContentRtl') && $role->isContentRtl()) ? 'rtl' : 'ltr';
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
