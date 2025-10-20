<?php

if (!function_exists('csp_nonce')) {
    /**
     * Get the CSP nonce for the current request
     */
    function csp_nonce(): string
    {
        return \App\Helpers\SecurityHelper::cspNonce();
    }
}

if (!function_exists('nonce_attr')) {
    /**
     * Generate nonce attribute for script tags
     */
    function nonce_attr(): string
    {
        return \App\Helpers\SecurityHelper::nonceAttr();
    }
}

if (!function_exists('get_translated_categories')) {
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

if (!function_exists('is_valid_language_code')) {
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

if (!function_exists('is_browser_testing')) {
    /**
     * Determine if the application is currently being exercised by browser tests.
     */
    function is_browser_testing(): bool
    {
        if (config('app.browser_testing')) {
            return true;
        }

        try {
            $request = request();
        } catch (\Throwable $e) {
            $request = null;
        }

        if ($request instanceof \Illuminate\Http\Request) {
            $cookie = $request->cookies->get('browser_testing');

            if (filter_var($cookie, FILTER_VALIDATE_BOOLEAN) || $cookie === '1') {
                return true;
            }
        }

        $flagPath = storage_path('framework/browser-testing.flag');

        return is_string($flagPath) && is_file($flagPath);
    }
}

if (!function_exists('is_hosted_or_admin')) {
    /**
     * Check if the current user is hosted or an admin
     */
    function is_hosted_or_admin(): bool
    {
        if (config('app.hosted') || config('app.is_testing') || is_browser_testing()) {
            return true;
        }

        return auth()->user() && auth()->user()->isAdmin();
    }
}

if (!function_exists('is_mobile')) {
    /**
     * Check if the current user is on a mobile device
     */
    function is_mobile(): bool
    {
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent'));
    }
}

if (!function_exists('is_rtl')) {
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

if (!function_exists('app_public_url')) {
    /**
     * Resolve the public application URL from settings when available.
     */
    function app_public_url(): string
    {
        static $cachedUrl = null;

        if ($cachedUrl !== null) {
            return $cachedUrl;
        }

        $url = config('app.url');

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $generalSettings = \App\Models\Setting::forGroup('general');

                if (!empty($generalSettings['public_url'])) {
                    $url = $generalSettings['public_url'];
                }
            }
        } catch (\Throwable $exception) {
            // Ignore any issues while resolving the URL and fall back to the config value.
        }

        if (empty($url)) {
            $url = url('/');
        }

        return $cachedUrl = rtrim($url, '/');
    }
}
if (!function_exists('storage_asset_url')) {
    /**
     * Generate a public URL for a file stored on the configured filesystem disk.
     */
    function storage_asset_url(?string $path): string
    {
        if (! is_string($path) || trim($path) === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = ltrim(substr($normalized, strlen('storage/')), '/');
        }

        $disk = config('filesystems.default');
        $diskToUse = in_array($disk, ['local', 'public'], true) ? 'public' : $disk;

        try {
            if (config()->has("filesystems.disks.{$diskToUse}")) {
                return \Illuminate\Support\Facades\Storage::disk($diskToUse)->url($normalized);
            }
        } catch (\Throwable $exception) {
            // Fall through to the manual URL construction below.
        }

        return url('/storage/' . $normalized);
    }
}

if (!function_exists('vite_manifest')) {
    /**
     * Load the built Vite manifest once per request.
     *
     * @return array<string, mixed>
     */
    function vite_manifest(): array
    {
        static $manifest = null;

        if ($manifest !== null) {
            return $manifest;
        }

        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath)) {
            return $manifest = [];
        }

        try {
            $decoded = json_decode(file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            report($exception);

            return $manifest = [];
        }

        return $manifest = is_array($decoded) ? $decoded : [];
    }
}

if (!function_exists('vite_entry_paths')) {
    /**
     * Resolve URLs for a given Vite entry.
     *
     * @return array{js: array<int, string>, css: array<int, string>}
     */
    function vite_entry_paths(string $entry): array
    {
        $manifest = vite_manifest();
        $paths = ['js' => [], 'css' => []];

        if (! array_key_exists($entry, $manifest) || ! is_array($manifest[$entry])) {
            return $paths;
        }

        $asset = $manifest[$entry];
        $baseUrl = rtrim(url('/build'), '/');

        if (! empty($asset['file']) && is_string($asset['file'])) {
            $file = ltrim($asset['file'], '/');
            $url = $baseUrl . '/' . $file;

            if (str_ends_with($file, '.css')) {
                $paths['css'][] = $url;
            } else {
                $paths['js'][] = $url;
            }
        }

        if (! empty($asset['css']) && is_array($asset['css'])) {
            foreach ($asset['css'] as $css) {
                if (! is_string($css) || $css === '') {
                    continue;
                }

                $paths['css'][] = $baseUrl . '/' . ltrim($css, '/');
            }
        }

        return $paths;
    }
}

if (!function_exists('vite_assets')) {
    /**
     * Resolve grouped Vite asset URLs for the provided entries.
     *
     * @param  array<int, string>  $entries
     * @return array{js: array<int, string>, css: array<int, string>}
     */
    function vite_assets(array $entries): array
    {
        $grouped = ['js' => [], 'css' => []];

        foreach ($entries as $entry) {
            if (! is_string($entry) || $entry === '') {
                continue;
            }

            $paths = vite_entry_paths($entry);

            $grouped['js'] = array_merge($grouped['js'], $paths['js']);
            $grouped['css'] = array_merge($grouped['css'], $paths['css']);
        }

        $grouped['js'] = array_values(array_unique($grouped['js']));
        $grouped['css'] = array_values(array_unique($grouped['css']));

        return $grouped;
    }
}
