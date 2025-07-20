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
        
        if (substr($magicBytes, 0, 4) === "GIF8") {
            return 'gif';
        }
        
        if (substr($magicBytes, 0, 4) === "RIFF" && substr($imageData, 8, 4) === "WEBP") {
            return 'webp';
        }
        
        if (substr($magicBytes, 0, 2) === "BM") {
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
        $filename = 'event_image.' . $extension;
        
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
        $tempFile = tempnam(sys_get_temp_dir(), 'event_' . uniqid() . '_');
        file_put_contents($tempFile, $imageData);
        
        // Determine file extension based on detected format
        $format = self::detectImageFormat($imageData, $imageUrl);
        $extension = self::getImageExtension($format);
        $filename = strtolower($filenamePrefix . \Illuminate\Support\Str::random(32) . '.' . $extension);
        
        $file = new \Illuminate\Http\UploadedFile($tempFile, $filenamePrefix . '.' . $extension);
        $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

        return $filename;
    }

    /**
     * Validate uploaded file for security
     */
    public static function validateUploadedFile($file): void
    {
        // Check if file upload was successful
        if (!$file->isValid()) {
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
            'image/webp'
        ];
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Invalid file type. Only images are allowed.');
        }
        
        // Additional security check using ImageUtils format detection
        $imageData = file_get_contents($file->getRealPath());
        $imageUrl = $file->getClientOriginalName();
        $detectedFormat = self::detectImageFormat($imageData, $imageUrl);
        
        $allowedFormats = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        if (!in_array($detectedFormat, $allowedFormats)) {
            throw new \Exception('Invalid file format detected.');
        }
    }
} 