<?php

namespace App\Utils;

use HTMLPurifier;
use HTMLPurifier_Config;
use League\CommonMark\CommonMarkConverter;

class MarkdownUtils
{
    public static function convertToHtml($markdown)
    {
        if (! $markdown) {
            return $markdown;
        }

        $markdown = strip_tags($markdown);

        $converter = new CommonMarkConverter([
            'renderer' => [
                'soft_break' => '<br>',
            ],
        ]);
        $html = $converter->convertToHtml($markdown);

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.TargetBlank', true);
        $config->set('HTML.Nofollow', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * Sanitize HTML content to prevent XSS attacks.
     * Allows common HTML tags while stripping dangerous content.
     */
    public static function sanitizeHtml(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $config = HTMLPurifier_Config::createDefault();
        // Allow common HTML elements for blog content
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,a[href|target|rel],ul,ol,li,h1,h2,h3,h4,h5,h6,blockquote,pre,code,img[src|alt|width|height],table,thead,tbody,tr,th,td,div,span[style],hr');
        $config->set('HTML.TargetBlank', true);
        // Force rel="noopener" on links
        $config->set('HTML.Nofollow', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        // Limit URI schemes
        $config->set('URI.AllowedSchemes', ['http', 'https', 'mailto']);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
