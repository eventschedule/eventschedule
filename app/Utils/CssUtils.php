<?php

namespace App\Utils;

use HTMLPurifier;
use HTMLPurifier_Config;

class CssUtils
{
    public static function sanitizeCss(?string $css): string
    {
        $css = trim($css ?? '');
        if (empty($css)) {
            return '';
        }

        $css = self::removeDangerousCss($css);

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'span[style]');
        $config->set('CSS.AllowImportant', true);
        // This allows display: none;
        $config->set('CSS.AllowTricky', true);

        $purifier = new HTMLPurifier($config);

        if (strpos($css, '{') !== false) {
            return preg_replace_callback('/([^{]+)\{([^}]*)\}/s', function ($matches) use ($purifier) {
                $selector = trim($matches[1]);
                $props = self::purifyProperties($matches[2], $purifier);

                return "$selector { $props }";
            }, $css);
        }

        return self::purifyProperties($css, $purifier);
    }

    /**
     * Internal helper to clean a string of CSS properties
     */
    private static function purifyProperties(string $props, HTMLPurifier $purifier): string
    {
        $props = trim($props);
        if (empty($props)) {
            return '';
        }

        $html = '<span style="'.htmlspecialchars($props, ENT_QUOTES, 'UTF-8').'"></span>';
        $purifiedHtml = $purifier->purify($html);

        if (preg_match('/style=["\'](.*?)["\']/is', $purifiedHtml, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        }

        return '';
    }

    /**
     * Remove patterns HTMLPurifier might miss
     */
    private static function removeDangerousCss(string $css): string
    {
        $patterns = [
            '/expression\s*\(/i',
            '/javascript\s*:/i',
            '/@import/i',
            '/(behavior|binding|-moz-binding)\s*:/i',
        ];

        return preg_replace($patterns, '', $css);
    }
}
