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

if (!function_exists('is_hosted_or_admin')) {
    /**
     * Check if the current user is hosted or an admin
     */
    function is_hosted_or_admin(): bool
    {
        if (config('app.hosted')) {
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