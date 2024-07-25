<?php

namespace App\Utils;

use League\CommonMark\CommonMarkConverter;
use HTMLPurifier;
use HTMLPurifier_Config;

class MarkdownUtils
{
    public static function convertToHtml($markdown)
    {
        $markdown = strip_tags($markdown);

        $converter = new CommonMarkConverter();
        $html = $converter->convertToHtml($markdown);
        
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        
        return $purifier->purify($html);
    }
}