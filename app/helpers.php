<?php

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

if (! function_exists('get_translated_categories')) {
    /**
     * Get translated category names
     */
    function get_translated_categories(): array
    {
        $categories = config('app.event_categories', []);
        $translatedCategories = [];

        foreach ($categories as $id => $englishName) {
            // Convert category name to translation key format
            // First replace " & " with "_&_", then replace remaining spaces with "_"
            $key = strtolower($englishName);
            $key = str_replace(' & ', '_&_', $key);
            $key = str_replace(' ', '_', $key);
            $translatedCategories[$id] = __("messages.{$key}");
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

        $supportedLanguages = config('app.supported_languages', ['en']);

        return in_array($languageCode, $supportedLanguages, true);
    }
}

if (! function_exists('is_hosted_or_admin')) {
    /**
     * Check if the current instance is the nexus or user is an admin
     */
    function is_hosted_or_admin(): bool
    {
        if (config('app.is_nexus') || config('app.is_testing')) {
            return true;
        }

        return auth()->user() && auth()->user()->isAdmin();
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
        if (session()->has('translate')) {
            return false;
        }

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

if (! function_exists('blog_url')) {
    /**
     * Generate a URL for blog pages
     * Returns /blog path for testing and selfhosted instances
     * Returns blog.eventschedule.com for hosted production
     */
    function blog_url(string $path = ''): string
    {
        if (config('app.is_testing') || ! config('app.hosted')) {
            return url('/blog'.$path);
        }

        return 'https://blog.eventschedule.com'.$path;
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

        return $role->subdomain === \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN;
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
