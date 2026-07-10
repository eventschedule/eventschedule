<?php

namespace App\Utils;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Single point of contact with endroid/qr-code.
 *
 * The library changes its construction API between major versions, so every caller goes
 * through here and no other file imports Endroid directly.
 */
class QrCodeUtils
{
    /**
     * Render $data as a PNG and return the raw image bytes.
     */
    public static function png(string $data, int $size = 200, int $margin = 10): string
    {
        $qrCode = new QrCode(data: $data, size: $size, margin: $margin);

        return (new PngWriter)->write($qrCode)->getString();
    }

    /**
     * Render $data as a PNG base64 data URI, for embedding directly in an <img src>.
     */
    public static function dataUri(string $data, int $size = 200, int $margin = 10): string
    {
        return 'data:image/png;base64,'.base64_encode(self::png($data, $size, $margin));
    }
}
