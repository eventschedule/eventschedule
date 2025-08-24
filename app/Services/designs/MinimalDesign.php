<?php

namespace App\Services\designs;

use App\Services\AbstractEventDesign;
use Carbon\Carbon;
use GdImage;

class MinimalDesign extends AbstractEventDesign
{
    // Canvas
    private const WIDTH = 600;
    private const HEIGHT = 800;

    // Colors
    private const COLOR_BLACK     = [0, 0, 0];
    private const COLOR_WHITE     = [255, 255, 255];
    private const COLOR_GREY      = [128, 128, 128];

    // Layout
    private const MARGIN = 40;
    private const EVENTS_TOP = 150;
    private const ROW_H = 60;

    // Track temporary files for cleanup
    private array $tempFiles = [];

    public function getWidth(): int
    {
        return self::WIDTH;
    }

    public function getHeight(): int
    {
        return self::HEIGHT;
    }

    public function __destruct()
    {
        // Clean up temporary files
        $this->cleanupTempFiles();
    }

    public function generate(): string
    {
        $this->minimalBackground();
        $this->minimalHeader();
        $this->minimalEventsList();
        $this->minimalFooter();

        ob_start(); 
        imagepng($this->im); 
        $out = ob_get_clean();
        return $out;
    }

    // ---------- colors/bg ----------
    protected function allocateColors(): void
    {
        foreach ([
            'black'=>self::COLOR_BLACK,'white'=>self::COLOR_WHITE,'grey'=>self::COLOR_GREY
        ] as $k=>$rgb) $this->c[$k]=imagecolorallocate($this->im,$rgb[0],$rgb[1],$rgb[2]);
    }

    private function minimalBackground(): void
    {
        // White background
        imagefill($this->im, 0, 0, $this->c['white']);
    }

    // ---------- header ----------
    private function minimalHeader(): void
    {
        $company = $this->sanitize($this->role->translatedName() ?: 'YOUR COMPANY NAME');
        
        if ($this->rtl) {
            $w = $this->textW($company, 16, $this->fontRegular());
            $this->drawEmojiText($this->vis($company), 16, self::WIDTH - self::MARGIN - $w, 60, $this->c['black'], $this->fontRegular(), true);
        } else {
            $this->drawEmojiText($company, 16, self::MARGIN, 60, $this->c['black'], $this->fontRegular());
        }

        // Draw profile logo in top corner
        $this->drawProfileLogo();

        [$bold, $script] = $this->i18n[$this->lang];
        
        if ($this->rtl) {
            $right = self::WIDTH - self::MARGIN;
            $wBold = $this->textW($bold, 24, $this->fontBold());
            $this->drawEmojiText($this->vis($bold), 24, $right - $wBold, 100, $this->c['black'], $this->fontBold(), true);
        } else {
            $this->drawEmojiText($bold, 24, self::MARGIN, 100, $this->c['black'], $this->fontBold());
        }
    }

    // ---------- profile logo ----------
    private function drawProfileLogo(): void
    {
        // Check if role has a profile image
        if (!$this->role->profile_image_url) {
            return;
        }

        // Logo dimensions and positioning - make it larger
        $logoSize = 70; // Increased from 50
        $logoMargin = 15;
        
        // Position based on RTL setting
        if ($this->rtl) {
            // RTL: top-left corner
            $logoX = $logoMargin;
        } else {
            // LTR: top-right corner
            $logoX = self::WIDTH - $logoMargin - $logoSize;
        }
        
        $logoY = $logoMargin;

        // Try to load the profile image
        $profileImagePath = $this->getProfileImagePath();
        if (!$profileImagePath || !file_exists($profileImagePath)) {
            return;
        }

        // Try different methods to load the image
        $profileImg = $this->loadImage($profileImagePath);
        if ($profileImg === false) {
            return;
        }

        // Get original dimensions
        $origW = imagesx($profileImg);
        $origH = imagesy($profileImg);

        // Create a rounded rectangle logo instead of circular
        $this->drawRoundedLogo($profileImg, $logoX, $logoY, $logoSize, $origW, $origH);

        // Clean up
        imagedestroy($profileImg);
    }

    private function getProfileImagePath(): ?string
    {
        // Get the raw profile_image_url value from the database, not the accessor
        $profileUrl = $this->role->getAttributes()['profile_image_url'] ?? null;
        if (!$profileUrl) {
            return null;
        }

        // Handle different storage configurations
        if (config('filesystems.default') == 'local') {
            return storage_path('app/public/' . $profileUrl);
        } else {
            // For hosted environments, try to get local path or download the image
            $localPath = $this->downloadRemoteImage($profileUrl);
            return $localPath;
        }
    }

    private function downloadRemoteImage(string $url): ?string
    {
        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'profile_logo_');
        if ($tempFile === false) {
            return null;
        }

        // Track the temporary file for cleanup
        $this->tempFiles[] = $tempFile;

        // Download the image
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'EventSchedule/1.0',
        ]);

        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($imageData === false || $httpCode !== 200) {
            unlink($tempFile);
            // Remove from tracking array
            $this->tempFiles = array_filter($this->tempFiles, fn($f) => $f !== $tempFile);
            return null;
        }

        // Validate that it's actually an image
        if (getimagesizefromstring($imageData) === false) {
            unlink($tempFile);
            // Remove from tracking array
            $this->tempFiles = array_filter($this->tempFiles, fn($f) => $f !== $tempFile);
            return null;
        }

        // Write to temporary file
        if (file_put_contents($tempFile, $imageData) === false) {
            unlink($tempFile);
            // Remove from tracking array
            $this->tempFiles = array_filter($this->tempFiles, fn($f) => $f !== $tempFile);
            return null;
        }

        return $tempFile;
    }

    private function loadImage(string $path): GdImage|false
    {
        // Get file extension to determine image type
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'png':
                return imagecreatefrompng($path);
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'gif':
                return imagecreatefromgif($path);
            case 'webp':
                if (function_exists('imagecreatefromwebp')) {
                    return imagecreatefromwebp($path);
                }
                break;
        }
        
        // Fallback: try to detect format from file content
        $imageData = file_get_contents($path);
        if ($imageData === false) {
            return false;
        }
        
        // Try to create image from string
        $img = imagecreatefromstring($imageData);
        if ($img !== false) {
            return $img;
        }
        
        return false;
    }

    private function drawRoundedLogo(GdImage $sourceImg, int $x, int $y, int $size, int $origW, int $origH): void
    {
        // Create a rounded rectangle mask
        $mask = imagecreatetruecolor($size, $size);
        imagealphablending($mask, false);
        imagesavealpha($mask, true);
        
        // Fill with transparent background
        $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $transparent);
        
        // Draw white rounded rectangle with corner radius
        $white = imagecolorallocate($mask, 255, 255, 255);
        $cornerRadius = 10; // Rounded corner radius
        
        // Fill the main rectangle
        imagefilledrectangle($mask, $cornerRadius, 0, $size - $cornerRadius - 1, $size - 1, $white);
        imagefilledrectangle($mask, 0, $cornerRadius, $size - 1, $size - $cornerRadius - 1, $white);
        
        // Fill the corners with circles
        imagefilledellipse($mask, $cornerRadius, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $size - $cornerRadius - 1, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $cornerRadius, $size - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $size - $cornerRadius - 1, $size - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, $white);
        
        // Create destination image with transparency
        $dest = imagecreatetruecolor($size, $size);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        
        // Fill with transparent background
        $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $transparent);
        
        // Copy and resize source image
        imagecopyresampled($dest, $sourceImg, 0, 0, 0, 0, $size, $size, $origW, $origH);
        
        // Apply rounded rectangle mask
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                $alpha = imagecolorsforindex($mask, imagecolorat($mask, $i, $j));
                if ($alpha['red'] == 0) { // Outside rounded rectangle
                    $color = imagecolorallocatealpha($dest, 0, 0, 0, 127);
                    imagesetpixel($dest, $i, $j, $color);
                }
            }
        }
        
        // Copy to main canvas
        imagecopy($this->im, $dest, $x, $y, 0, 0, $size, $size);
        
        // Clean up
        imagedestroy($mask);
        imagedestroy($dest);
    }

    // ---------- events ----------
    private function minimalEventsList(): void
    {
        $n = $this->events->count(); 
        if (!$n) return;
        
        $y = self::EVENTS_TOP;
        
        foreach ($this->events as $event) {
            $title = $this->sanitize($event->translatedName() ?: $event->name);
            $desc = $this->sanitize($event->translatedDescription() ?: '');
            
            $dt = Carbon::parse($event->starts_at);
            $date = $dt->format('M d, Y');
            
            // Date
            if ($this->rtl) {
                $w = $this->textW($date, 12, $this->fontRegular());
                $this->drawEmojiText($date, 12, self::WIDTH - self::MARGIN - $w, $y, $this->c['grey'], $this->fontRegular(), true);
            } else {
                $this->drawEmojiText($date, 12, self::MARGIN, $y, $this->c['grey'], $this->fontRegular());
            }
            
            // Title
            $maxW = self::WIDTH - 2 * self::MARGIN;
            $this->clampLines($this->vis($title), 14, $this->fontBold(), $this->c['black'], self::MARGIN, $y + 20, $maxW, 2, $this->rtl);
            
            // Description
            if ($desc) {
                $this->clampLines($this->vis($desc), 11, $this->fontRegular(), $this->c['grey'], self::MARGIN, $y + 45, $maxW, 1, $this->rtl);
                $y += self::ROW_H + 10;
            } else {
                $y += self::ROW_H;
            }
        }
    }

    // ---------- footer ----------
    private function minimalFooter(): void
    {
        $text = $this->i18n[$this->lang][2];
        $this->center($this->vis($text), 10, $this->fontRegular(), 'grey', self::WIDTH / 2, self::HEIGHT - 30);
    }

    // ---------- draw helpers ----------
    private function center(string $text, int $size, string $font, string $colorKey, float $cx, float $cy): void
    {
        $b = imagettfbbox($size, 0, $font, $text);
        $w = abs($b[2] - $b[0]); 
        $h = abs($b[7] - $b[1]);
        $x = (int)round($cx - $w / 2); 
        $y = (int)round($cy + $h / 2);
        $this->drawEmojiText($text, $size, $x, $y, $this->c[$colorKey], $font, $this->rtl);
    }

    private function cleanupTempFiles(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];
    }
}
