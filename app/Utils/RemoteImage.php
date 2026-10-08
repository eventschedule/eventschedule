<?php

namespace App\Utils;

/**
 * A picture at somebody else's address, fetched and checked before anything is done with it.
 *
 * Fetched through UrlUtils::safeFetch(), so the address is one this server may ask (public,
 * http or https, every redirect hop checked again and pinned to the address that was checked)
 * and the body is cut off at the fetch's own cap. Then it has to BE a picture of a kind the app
 * stores, read from its bytes: the address's file extension and the Content-Type a server states
 * are both whatever the other side likes to say.
 *
 * Used by the link import's preview and by a flyer given to the API as an address.
 */
class RemoteImage
{
    public const UNREACHABLE = 'unreachable';

    public const TOO_LARGE = 'too_large';

    public const NOT_AN_IMAGE = 'not_an_image';

    private const TYPES = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];

    /**
     * @return array{contents: string, extension: string}|array{reason: string}
     */
    public static function read(string $url, int $seconds, int $maxBytes): array
    {
        $contents = UrlUtils::safeFetch($url, $seconds);

        if (! is_string($contents) || $contents === '') {
            return ['reason' => self::UNREACHABLE];
        }

        if (strlen($contents) > $maxBytes) {
            return ['reason' => self::TOO_LARGE];
        }

        $info = @getimagesizefromstring($contents);
        $extension = self::TYPES[$info[2] ?? 0] ?? null;

        if (! $extension) {
            return ['reason' => self::NOT_AN_IMAGE];
        }

        return ['contents' => $contents, 'extension' => $extension];
    }
}
