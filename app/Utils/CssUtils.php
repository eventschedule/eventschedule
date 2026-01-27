<?php

namespace App\Utils;

class CssUtils
{
    /**
     * Sanitize CSS by removing dangerous patterns.
     *
     * Note: HTMLPurifier was removed because it only supports CSS 2.1 properties
     * and throws errors for modern properties like border-radius, display, flex, grid, etc.
     *
     * This is safe because only authenticated schedule owners can edit custom_css
     * for their own schedules - this is NOT untrusted user-generated content.
     */
    public static function sanitizeCss(?string $css): string
    {
        $css = trim($css ?? '');
        if (empty($css)) {
            return '';
        }

        // Remove dangerous patterns - this is sufficient since only
        // authenticated schedule owners can edit their own custom_css
        return self::removeDangerousCss($css);
    }

    /**
     * Remove dangerous CSS patterns that could execute JavaScript or load external resources.
     *
     * Blocks:
     * - expression() - IE JavaScript execution
     * - javascript: - JavaScript URLs
     * - @import - External stylesheet inclusion
     * - behavior, binding, -moz-binding - Browser-specific code execution
     * - url() with http/https/data URIs - External resource loading and data exfiltration
     * - @font-face - External font loading (can be used for data exfiltration)
     * - @charset - Character set manipulation
     */
    private static function removeDangerousCss(string $css): string
    {
        $patterns = [
            '/expression\s*\(/i',
            '/javascript\s*:/i',
            '/@import/i',
            '/(behavior|binding|-moz-binding)\s*:/i',
            // Block url() with external protocols or data URIs
            '/url\s*\(\s*["\']?\s*(https?:|data:|\/\/)/i',
            // Block @font-face entirely (external font loading)
            '/@font-face\s*\{[^}]*\}/is',
            // Block @charset manipulation
            '/@charset/i',
        ];

        return preg_replace($patterns, '', $css);
    }
}
