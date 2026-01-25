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
        // AllowTricky enables layout properties (display, position, etc.) needed for legitimate
        // schedule customization. This is safe because only authenticated schedule owners can
        // edit custom_css, and it only applies to their own schedule page.
        $config->set('CSS.AllowTricky', true);
        // Whitelist allowed CSS properties - dangerous patterns are blocked by removeDangerousCss()
        $config->set('CSS.AllowedProperties', [
            // Typography
            'color', 'background-color', 'background', 'background-image', 'background-position', 'background-repeat', 'background-size',
            'font-family', 'font-size', 'font-weight', 'font-style',
            'text-align', 'text-decoration', 'line-height', 'letter-spacing', 'text-transform',
            // Spacing
            'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
            'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
            // Borders
            'border', 'border-color', 'border-width', 'border-style', 'border-radius',
            'border-top', 'border-right', 'border-bottom', 'border-left',
            // Sizing
            'width', 'max-width', 'min-width', 'height', 'max-height', 'min-height',
            // Layout (AllowTricky enables these)
            'display', 'position', 'top', 'right', 'bottom', 'left', 'z-index',
            'flex', 'flex-direction', 'flex-wrap', 'justify-content', 'align-items', 'align-content', 'gap',
            'grid', 'grid-template-columns', 'grid-template-rows', 'grid-gap',
            'overflow', 'overflow-x', 'overflow-y', 'visibility', 'opacity',
            'box-shadow', 'transform', 'transition',
        ]);

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
