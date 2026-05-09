<?php

namespace App\Utils;

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
