<?php

namespace App\Utils;

use League\CommonMark\CommonMarkConverter;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\File;

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
                'soft_break' => '<br>'
            ]
        ]);
        $html = $converter->convertToHtml($markdown);
        
        $config = HTMLPurifier_Config::createDefault();

        $cachePath = storage_path('app/htmlpurifier');

        if (! File::exists($cachePath)) {
            File::makeDirectory($cachePath, 0755, true);
        }

        $config->set('Cache.SerializerPath', $cachePath);
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }
}
