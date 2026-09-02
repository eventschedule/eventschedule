<?php

namespace App\Utils;

use Illuminate\Support\Facades\Storage;

class ImageUtils
{
    /**
     * Detect image format from image data or URL
     */
    public static function detectImageFormat(string $imageData, string $imageUrl): string
    {
        // First try to detect from image data using magic bytes
        $magicBytes = substr($imageData, 0, 4);

        if (substr($magicBytes, 0, 2) === "\xFF\xD8") {
            return 'jpeg';
        }

        if (substr($magicBytes, 0, 4) === "\x89PNG") {
            return 'png';
        }

        if (substr($magicBytes, 0, 4) === 'GIF8') {
            return 'gif';
        }

        if (substr($magicBytes, 0, 4) === 'RIFF' && substr($imageData, 8, 4) === 'WEBP') {
            return 'webp';
        }

        if (substr($magicBytes, 0, 2) === 'BM') {
            return 'bmp';
        }

        // If magic bytes don't work, try to detect from URL extension
        $urlParts = parse_url($imageUrl);
        if (isset($urlParts['path'])) {
            $extension = strtolower(pathinfo($urlParts['path'], PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                return $extension === 'jpg' ? 'jpeg' : $extension;
            }
        }

        // Default to JPEG if we can't determine
        return 'jpeg';
    }

    /**
     * Get file extension for image format
     */
    public static function getImageExtension(string $format): string
    {
        $extensions = [
            'jpeg' => 'jpg',
            'jpg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            'bmp' => 'bmp',
        ];

        return $extensions[$format] ?? 'jpg';
    }

    /**
     * Get MIME type for image format
     */
    public static function getImageMimeType(string $format): string
    {
        $mimeTypes = [
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
        ];

        return $mimeTypes[$format] ?? 'image/jpeg';
    }

    /**
     * Create UploadedFile from image data
     *
     * Note: The caller is responsible for cleaning up the temporary file after use.
     * Use cleanupUploadedFile() method after the file has been processed.
     */
    public static function createUploadedFileFromImageData(string $imageData, string $imageUrl, string $tempPrefix = 'event_image_'): \Illuminate\Http\UploadedFile
    {
        // Create a temporary file with the image data
        $tempFile = tempnam(sys_get_temp_dir(), $tempPrefix);
        file_put_contents($tempFile, $imageData);

        // Detect format and determine file properties
        $format = self::detectImageFormat($imageData, $imageUrl);
        $extension = self::getImageExtension($format);
        $mimeType = self::getImageMimeType($format);
        $filename = 'event_image.'.$extension;

        // Create UploadedFile object
        return new \Illuminate\Http\UploadedFile(
            $tempFile,
            $filename,
            $mimeType,
            null,
            false // Not in test mode so getRealPath() works
        );
    }

    /**
     * Public URL for a filename produced by saveImageData().
     *
     * Where a stored image is served from depends on the configured disk, so a
     * hardcoded '/storage/...' silently 404s on any deployment using object storage -
     * which includes the hosted site. This mirrors the resolution in
     * Event::getFlyerImageUrlAttribute() and Role::getProfileImageUrlAttribute();
     * those predate this helper and still carry their own copies.
     */
    public static function storedUrl(?string $value): string
    {
        if (! $value) {
            return '';
        }

        // Demo seed images ship in the repo rather than in storage.
        if (str_starts_with($value, 'demo_')) {
            return url('/images/demo/'.$value);
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        }

        if (in_array(config('filesystems.default'), ['local', 'public'])) {
            return url('/storage/'.$value);
        }

        // Already an absolute URL from some other source.
        return $value;
    }

    /**
     * The default derivative width: the homepage poster wall, the mobile strip, and the `src`
     * attribute of every card that also offers a srcset.
     *
     * The wall declares 96x128 (mobile strip) and 208x277 (desktop wall) slots, so 480px covers
     * both at up to 2x DPR while staying one file.
     */
    public const VARIANT_WIDTH = 480;

    /**
     * Every width the pipeline generates, smallest first.
     *
     * 480 covers the wall (above). 960 exists for the CARD surfaces, which are far larger than
     * a wall slot: the homepage rail's poster card is 320 CSS px (72vw on a phone), /browse's
     * grid cards run 287 to 669 CSS px and the for-talent rail's run 292 to ~480. At 2x DPR
     * those want 580 to 1340 device pixels, so 480 would be an upscale of 1.2x to 2.8x -
     * visibly softer than the original it replaced. Those three sites render a `srcset` of both
     * widths and let the browser choose; everything else keeps asking for 480.
     *
     * Widths are part of the derivative FILENAME, so this list is additive: adding one leaves
     * every file already generated valid, and `images:backfill-variants` fills in the new key.
     */
    public const VARIANT_WIDTHS = [480, 960];

    public const VARIANT_QUALITY = 80;

    /**
     * The complete `reason` vocabulary of generateStoredVariants(), split by whether trying
     * again could ever produce a different answer.
     *
     * That split is the contract between the helper, the job and the backfill command:
     *
     *  - DETERMINISTIC: the same bytes (or the same absence of them) will decode the same way on
     *    the tenth attempt. The outcome is RECORDED as a skip, and nothing revisits the row until
     *    `--retry-skipped` asks it to.
     *  - TRANSIENT: the disk answered badly. Flysystem swallows the exception on any disk that
     *    does not set `throw` - do_spaces does not - so an S3 blip arrives here as a null stream
     *    or a false write, indistinguishable from a real answer except for the return value.
     *    Recording those as skips is how an afternoon of uploads ends up permanently
     *    thumbnail-less, so the job throws instead (the queue retries, the column stays null) and
     *    the backfill re-attempts them without being asked.
     */
    public const VARIANT_DETERMINISTIC_REASONS = ['demo', 'external', 'missing', 'unreadable', 'too_large', 'unsupported', 'failed'];

    public const VARIANT_TRANSIENT_REASONS = ['read_failed', 'write_failed'];

    public static function isTransientVariantReason(?string $reason): bool
    {
        return $reason !== null && in_array($reason, self::VARIANT_TRANSIENT_REASONS, true);
    }

    /**
     * Refuse to decode anything bigger than this.
     *
     * GD allocates 4 bytes per pixel for a truecolor canvas, so 12MP is ~48MB for the source
     * alone before the destination canvas, and the hosted PHP workers are capped at 128MB.
     * Only the web upload path resizes originals (EventRepo caps them at 2000px); the API,
     * guest submit, WhatsApp and import paths all store whatever arrived, so this guard is
     * the only thing standing between a 10MB camera JPEG and an OOM in the queue worker.
     * getimagesize() reads the header only, so the check costs nothing.
     */
    public const VARIANT_MAX_PIXELS = 12_000_000;

    /**
     * Deterministic name for a derivative of a stored image.
     *
     * `flyer_abc123.png` at 480px becomes `flyer_abc123_w480.webp`. Deterministic on purpose:
     * regenerating overwrites rather than orphaning, and a caller holding only the original's
     * name can still address the derivative.
     */
    public static function variantFilename(string $storedName, int $width = self::VARIANT_WIDTH): string
    {
        $dir = pathinfo($storedName, PATHINFO_DIRNAME);
        $name = pathinfo($storedName, PATHINFO_FILENAME).'_w'.$width.'.webp';

        return ($dir && $dir !== '.' && $dir !== DIRECTORY_SEPARATOR) ? $dir.'/'.$name : $name;
    }

    /**
     * The disk path a stored image filename lives at.
     *
     * Mirrors the storeAs() rule every write path uses
     * (`config('filesystems.default') == 'local' ? '/public' : '/'`), so a derivative written
     * here is served by exactly the same `/storage/...` or CDN URL as its original.
     */
    public static function storagePathFor(string $filename): string
    {
        return config('filesystems.default') == 'local' ? 'public/'.$filename : $filename;
    }

    /**
     * Public URL for a derivative filename recorded in an `image_variants` column.
     *
     * Delegates to storedUrl() deliberately: the derivative sits on the same disk in the same
     * directory as its original, so it has to resolve through the identical branch or a
     * selfhost install would serve `/storage/...` for the flyer and a CDN host for its thumbnail.
     */
    public static function variantUrl(?string $variantFilename): string
    {
        return self::storedUrl($variantFilename);
    }

    /**
     * Apply a JPEG's EXIF Orientation tag to an already-decoded GD image.
     *
     * GD ignores the tag: imagecreatefromjpeg() hands back the pixels exactly as stored, and
     * every re-encode below drops the tag entirely (WebP carries no EXIF at all). So a phone
     * photo that a browser renders upright - because a browser DOES honour the tag - comes out
     * of the resizer permanently on its side. That is the whole bug: upright on the event page,
     * rotated on the wall, the rail and /browse.
     *
     * The caller MUST take the return value: the rotate cases build a new resource and destroy
     * the one passed in.
     */
    public static function applyExifOrientation(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data') || ! function_exists('imagerotate')) {
            return $image;
        }

        // exif_read_data() warns and returns false for anything that is not a JPEG or TIFF, so
        // ask the header first rather than suppressing our way through every PNG the app resizes.
        $info = @getimagesize($path);
        if (($info[2] ?? null) !== IMAGETYPE_JPEG) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 0) : 0;

        // 1 is upright and anything outside 2-8 is not a value the tag can hold.
        if ($orientation < 2 || $orientation > 8) {
            return $image;
        }

        // Four of the eight orientations are mirrored as well as turned. imageflip() is in place.
        if ($orientation === 2 || $orientation === 7) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        } elseif ($orientation === 4 || $orientation === 5) {
            imageflip($image, IMG_FLIP_VERTICAL);
        }

        // imagerotate() turns COUNTER-clockwise, so the clockwise quarter turn an Orientation 6
        // photo needs is -90 (i.e. 270).
        $angle = match ($orientation) {
            3 => 180,
            5, 6, 7 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    /**
     * Write WebP derivatives of a stored image next to the original, on the same disk.
     *
     * Model-agnostic on purpose: it takes the raw stored filename (what
     * `events.flyer_image_url` holds), not a model, so `roles.profile_image_url` can reuse it
     * unchanged. GD cannot read from S3, so the original is streamed to a temp file first and
     * both temp files are removed in a `finally`.
     *
     * Every requested width shares ONE decode: the expensive half of this is pulling a
     * multi-megabyte original off object storage and handing it to GD, and doing that twice to
     * produce two thumbnails of the same file would double the job's cost for nothing.
     *
     * Never upscales: a source narrower than the target is re-encoded at its own width, which
     * is still a large win (the wall's originals are multi-megabyte PNGs). The returned
     * filename always names the REQUESTED width so the recorded key stays predictable.
     *
     * @param  int[]  $widths
     * @return array<int, array{ok: bool, filename: ?string, reason: ?string}> keyed by width;
     *                                                                         see VARIANT_DETERMINISTIC_REASONS and VARIANT_TRANSIENT_REASONS
     */
    public static function generateStoredVariants(string $storedName, array $widths = self::VARIANT_WIDTHS): array
    {
        $widths = array_values(array_unique(array_map('intval', $widths)));

        $skipAll = fn (string $reason) => array_fill_keys($widths, ['ok' => false, 'filename' => null, 'reason' => $reason]);

        if (! $storedName) {
            return $skipAll('missing');
        }

        // Demo seed flyers ship in the repo as small WebPs already; there is nothing to gain
        // and no storage disk to write to.
        if (str_starts_with($storedName, 'demo_')) {
            return $skipAll('demo');
        }

        // A few legacy rows hold a full URL rather than a stored filename.
        if (str_starts_with($storedName, 'http')) {
            return $skipAll('external');
        }

        // Before any decoding, and before any I/O: a GD built without WebP support has no
        // imagewebp(), and calling it is a fatal Error. On the `sync` queue (the selfhost
        // default) that Error propagates out of the Event save that dispatched the job and turns
        // a successful flyer upload into a 500.
        if (! function_exists('imagewebp')) {
            return $skipAll('unsupported');
        }

        $sourcePath = self::storagePathFor($storedName);

        if (! Storage::exists($sourcePath)) {
            return $skipAll('missing');
        }

        $tempIn = tempnam(sys_get_temp_dir(), 'variant_src_');
        $tempOut = tempnam(sys_get_temp_dir(), 'variant_out_');

        $sourceImage = null;

        try {
            $read = Storage::readStream($sourcePath);
            if (! $read) {
                // The file exists (checked above), so this is the disk failing to hand it over -
                // worth retrying, unlike a genuinely missing original.
                return $skipAll('read_failed');
            }

            $write = fopen($tempIn, 'w');
            if (! $write) {
                fclose($read);

                return $skipAll('failed');
            }

            stream_copy_to_stream($read, $write);
            fclose($write);
            fclose($read);

            // Header-only read, BEFORE any GD allocation. Everything below this line depends
            // on it having passed.
            $info = @getimagesize($tempIn);
            if ($info === false) {
                return $skipAll('unreadable');
            }

            [$srcWidth, $srcHeight] = $info;
            $mimeType = $info['mime'] ?? null;

            if ($srcWidth < 1 || $srcHeight < 1) {
                return $skipAll('unreadable');
            }

            if (($srcWidth * $srcHeight) > self::VARIANT_MAX_PIXELS) {
                return $skipAll('too_large');
            }

            $sourceImage = match ($mimeType) {
                'image/jpeg' => @imagecreatefromjpeg($tempIn),
                'image/png' => @imagecreatefrompng($tempIn),
                'image/gif' => @imagecreatefromgif($tempIn),
                'image/webp' => @imagecreatefromwebp($tempIn),
                default => null,
            };

            if (! $sourceImage) {
                return $skipAll('unreadable');
            }

            $sourceImage = self::applyExifOrientation($sourceImage, $tempIn);

            // Re-read from the resource rather than reusing the header values: orientations 5-8
            // turn the image a quarter circle, which SWAPS width and height, and scaling against
            // the pre-rotation numbers would squash the derivative.
            $srcWidth = imagesx($sourceImage);
            $srcHeight = imagesy($sourceImage);

            $results = [];

            foreach ($widths as $width) {
                $results[$width] = self::writeStoredVariant($sourceImage, $storedName, $width, $srcWidth, $srcHeight, $tempOut);
            }

            return $results;
        } finally {
            if ($sourceImage) {
                imagedestroy($sourceImage);
            }
            if (file_exists($tempIn)) {
                @unlink($tempIn);
            }
            if (file_exists($tempOut)) {
                @unlink($tempOut);
            }
        }
    }

    /**
     * One width of generateStoredVariants(): resample the decoded source and put it on the disk.
     *
     * @return array{ok: bool, filename: ?string, reason: ?string}
     */
    private static function writeStoredVariant(\GdImage $sourceImage, string $storedName, int $width, int $srcWidth, int $srcHeight, string $tempOut): array
    {
        $skip = fn (string $reason) => ['ok' => false, 'filename' => null, 'reason' => $reason];

        $destWidth = min($width, $srcWidth);
        $destHeight = max(1, (int) round($srcHeight * ($destWidth / $srcWidth)));

        $destImage = imagecreatetruecolor($destWidth, $destHeight);
        if (! $destImage) {
            return $skip('failed');
        }

        try {
            // WebP carries alpha, and a flyer with a transparent background would otherwise
            // resample onto black.
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
            imagefill($destImage, 0, 0, $transparent);

            imagecopyresampled(
                $destImage, $sourceImage,
                0, 0, 0, 0,
                $destWidth, $destHeight,
                $srcWidth, $srcHeight
            );

            if (! imagewebp($destImage, $tempOut, self::VARIANT_QUALITY)) {
                return $skip('failed');
            }

            $destName = self::variantFilename($storedName, $width);
            $stream = fopen($tempOut, 'r');
            if (! $stream) {
                return $skip('failed');
            }

            // Explicitly public: the do_spaces disk defaults to public visibility, but the
            // local/public disks and any future S3 bucket must not be left to a default.
            $stored = Storage::put(self::storagePathFor($destName), $stream, 'public');

            if (is_resource($stream)) {
                fclose($stream);
            }

            if (! $stored) {
                // Flysystem returns false rather than throwing on a disk without `throw`, so an
                // S3 outage looks exactly like this. Transient: worth another attempt.
                return $skip('write_failed');
            }

            return ['ok' => true, 'filename' => $destName, 'reason' => null];
        } finally {
            imagedestroy($destImage);
        }
    }

    /**
     * Write a single WebP derivative. Thin wrapper over generateStoredVariants().
     *
     * @return array{ok: bool, filename: ?string, reason: ?string}
     */
    public static function generateStoredVariant(string $storedName, int $width = self::VARIANT_WIDTH): array
    {
        return self::generateStoredVariants($storedName, [$width])[$width];
    }

    /**
     * Remove every derivative of a stored image.
     *
     * Called when the original is replaced or its row is deleted. Derivative names are DERIVED
     * from the original's, so once that filename is gone nothing can address them again and they
     * would sit on Spaces forever - a replaced flyer stranded one file per width, every time.
     *
     * Walks VARIANT_WIDTHS rather than whatever the row recorded: the saving hook nulls
     * `image_variants` before this could read it, and a run that failed halfway may have written
     * a file it never got to record.
     *
     * Never throws. This is cleanup beside a save that has already been decided, and a disk that
     * cannot delete a stale thumbnail must not fail the user's edit.
     */
    public static function deleteStoredVariants(string $storedName): void
    {
        // Demo flyers ship in the repo and legacy http values are not ours; neither has
        // derivatives, and storagePathFor() would name something that is not theirs.
        if (! $storedName || str_starts_with($storedName, 'demo_') || str_starts_with($storedName, 'http')) {
            return;
        }

        foreach (self::VARIANT_WIDTHS as $width) {
            try {
                Storage::delete(self::storagePathFor(self::variantFilename($storedName, $width)));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * Save image data to storage with proper format
     */
    public static function saveImageData(string $imageData, string $imageUrl, string $filenamePrefix = 'flyer_'): string
    {
        // Create a temporary file with the image data
        $tempFile = tempnam(sys_get_temp_dir(), 'event_'.uniqid().'_');
        file_put_contents($tempFile, $imageData);

        try {
            // Determine file extension based on detected format
            $format = self::detectImageFormat($imageData, $imageUrl);
            $extension = self::getImageExtension($format);
            $filename = strtolower($filenamePrefix.\Illuminate\Support\Str::random(32).'.'.$extension);

            $file = new \Illuminate\Http\UploadedFile($tempFile, $filenamePrefix.'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            return $filename;
        } finally {
            // Clean up temporary file
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Validate uploaded file for security
     */
    public static function validateUploadedFile($file): void
    {
        // Check if file upload was successful
        if (! $file->isValid()) {
            throw new \Exception('File upload failed');
        }

        // Check file size (5MB limit)
        if ($file->getSize() > 5242880) {
            throw new \Exception('File too large. Maximum size is 5MB.');
        }

        // Use ImageUtils to validate MIME type and format
        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ];

        if (! in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Invalid file type. Only images are allowed.');
        }

        // Additional security check using ImageUtils format detection
        $imageData = file_get_contents($file->getRealPath());
        $imageUrl = $file->getClientOriginalName();
        $detectedFormat = self::detectImageFormat($imageData, $imageUrl);

        $allowedFormats = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        if (! in_array($detectedFormat, $allowedFormats)) {
            throw new \Exception('Invalid file format detected.');
        }
    }

    /**
     * Generate a thumbnail from a source image
     *
     * @param  string  $sourcePath  Path to source image
     * @param  string  $destPath  Path to save thumbnail
     * @param  int  $width  Target width
     * @param  int  $height  Target height
     * @param  int  $quality  JPEG quality (0-100)
     * @return bool True on success, false on failure
     */
    public static function generateThumbnail(string $sourcePath, string $destPath, int $width, int $height, int $quality = 80): bool
    {
        if (! file_exists($sourcePath)) {
            return false;
        }

        // Get source image info
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return false;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Create source image resource based on type
        switch ($mimeType) {
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if ($sourceImage === false) {
            return false;
        }

        // Create destination image
        $destImage = imagecreatetruecolor($width, $height);
        if ($destImage === false) {
            imagedestroy($sourceImage);

            return false;
        }

        // Preserve transparency for the resampling process
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);

        // Resize/resample the image
        $result = imagecopyresampled(
            $destImage,
            $sourceImage,
            0, 0, 0, 0,
            $width, $height,
            $sourceWidth, $sourceHeight
        );

        if (! $result) {
            imagedestroy($sourceImage);
            imagedestroy($destImage);

            return false;
        }

        // Ensure destination directory exists
        $destDir = dirname($destPath);
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Save as JPEG
        $result = imagejpeg($destImage, $destPath, $quality);

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($destImage);

        return $result;
    }

    /**
     * Generate a WebP version of a source image
     *
     * @param  string  $sourcePath  Path to source image
     * @param  string  $destPath  Path to save WebP file
     * @param  int  $quality  WebP quality (0-100)
     * @return bool True on success, false on failure
     */
    public static function generateWebP(string $sourcePath, string $destPath, int $quality = 80): bool
    {
        if (! file_exists($sourcePath)) {
            return false;
        }

        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            return false;
        }

        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            default:
                return false;
        }

        if ($sourceImage === false) {
            return false;
        }

        // Preserve alpha transparency for PNGs
        if ($mimeType === 'image/png') {
            imagealphablending($sourceImage, true);
            imagesavealpha($sourceImage, true);
        }

        // Ensure destination directory exists
        $destDir = dirname($destPath);
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $result = imagewebp($sourceImage, $destPath, $quality);

        imagedestroy($sourceImage);

        return $result;
    }

    /**
     * Resize an image in place so its longest side is at most $maxDim pixels.
     *
     * No-op if the image is already within the limit. Preserves aspect ratio,
     * transparency, and original format. Returns false if the image is too
     * large to safely decode within current memory limits.
     */
    public static function resizeImageToMax(string $path, int $maxDim = 2000, int $quality = 85): bool
    {
        if (! file_exists($path)) {
            return false;
        }

        $info = @getimagesize($path);
        if ($info === false) {
            return false;
        }

        [$srcWidth, $srcHeight] = $info;
        $mimeType = $info['mime'] ?? null;

        if ($srcWidth <= $maxDim && $srcHeight <= $maxDim) {
            return true;
        }

        // Refuse pathologically large images rather than risk an OOM during
        // decode. ~16MP * 4 bytes = 64MB; with framework overhead this is the
        // safe ceiling on a 128MB PHP_FPM process.
        if (($srcWidth * $srcHeight) > 16_000_000) {
            return false;
        }

        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = @imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($path);
                break;
            case 'image/gif':
                $sourceImage = @imagecreatefromgif($path);
                break;
            case 'image/webp':
                $sourceImage = @imagecreatefromwebp($path);
                break;
            default:
                return false;
        }

        if (! $sourceImage) {
            return false;
        }

        // This path re-encodes, and every encoder below drops the EXIF block, so a phone photo
        // would lose the only record of which way up it is. Applied here rather than at the top
        // because the early return above keeps the original bytes (and their tag) untouched.
        $sourceImage = self::applyExifOrientation($sourceImage, $path);

        // Orientations 5-8 swap width and height, so the destination has to be measured from the
        // ROTATED image, not from the header read before the decode.
        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);

        $scale = $maxDim / max($srcWidth, $srcHeight);
        $dstWidth = (int) round($srcWidth * $scale);
        $dstHeight = (int) round($srcHeight * $scale);

        $destImage = imagecreatetruecolor($dstWidth, $dstHeight);
        if (! $destImage) {
            imagedestroy($sourceImage);

            return false;
        }

        if (in_array($mimeType, ['image/png', 'image/gif', 'image/webp'], true)) {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
            imagefill($destImage, 0, 0, $transparent);
        }

        imagecopyresampled(
            $destImage, $sourceImage,
            0, 0, 0, 0,
            $dstWidth, $dstHeight,
            $srcWidth, $srcHeight
        );

        $ok = match ($mimeType) {
            'image/jpeg' => imagejpeg($destImage, $path, $quality),
            'image/png' => imagepng($destImage, $path, 6),
            'image/gif' => imagegif($destImage, $path),
            'image/webp' => imagewebp($destImage, $path, $quality),
            default => false,
        };

        imagedestroy($sourceImage);
        imagedestroy($destImage);

        return $ok;
    }

    /**
     * Clean up temporary file from an UploadedFile object
     *
     * @param  \Illuminate\Http\UploadedFile  $file  The uploaded file to clean up
     */
    public static function cleanupUploadedFile(\Illuminate\Http\UploadedFile $file): void
    {
        $path = $file->getRealPath();
        if ($path && file_exists($path)) {
            @unlink($path);
        }
    }
}
