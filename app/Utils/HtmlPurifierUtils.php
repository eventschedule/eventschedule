<?php

namespace App\Utils;

use HTMLPurifier_Config;

class HtmlPurifierUtils
{
    /**
     * The folder each definition type is cached in under the cache path. These are the
     * three the Serializer creates for itself.
     */
    private const TYPES = ['HTML', 'CSS', 'URI'];

    /**
     * Every purifier starts here, never from HTMLPurifier_Config::createDefault() alone.
     *
     * The default caches its definitions inside its own folder in vendor/, which a
     * selfhost install does not give the web user (#131). On a cache miss the Serializer
     * raises a warning for a folder it cannot write, Laravel turns that into an
     * ErrorException, and saving an event was a 500. So the cache lives in storage/,
     * and when even that cannot be written it is turned off: purifying still works and
     * only rebuilds the definition each time.
     *
     * A type folder is checked too, because a cron job or an artisan command run as
     * root can create one the web user cannot write to, and the next release's config
     * would be refused there. A folder the process owns is chmodded back by HTMLPurifier
     * itself; the warning only comes from one that belongs to somebody else.
     */
    public static function config(?string $cacheDir = null): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();
        $dir = rtrim($cacheDir ?? storage_path('framework/cache/htmlpurifier'), '/') ?: '/';

        if (self::canCacheIn($dir)) {
            $config->set('Cache.SerializerPath', $dir);
        } else {
            $config->set('Cache.DefinitionImpl', null);
        }

        return $config;
    }

    private static function canCacheIn(string $dir): bool
    {
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (! is_dir($dir) || ! is_writable($dir)) {
            return false;
        }

        foreach (self::TYPES as $type) {
            $typeDir = $dir.'/'.$type;

            if (is_dir($typeDir) && ! is_writable($typeDir)) {
                return false;
            }
        }

        return true;
    }
}
