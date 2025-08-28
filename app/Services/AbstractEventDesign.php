<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Event;
use Illuminate\Support\Collection;

abstract class AbstractEventDesign
{
    protected Role $role;
    protected Collection $events;
    protected $im;
    protected array $c = [];
    
    // Common configuration
    protected const MAX_EVENTS = 9;
    protected const MARGIN = 18;
    protected const CORNER_RADIUS = 10;
    
    // QR Code configuration
    protected const QR_CODE_SIZE = 70;
    protected const QR_CODE_PADDING = 10;
    protected const QR_CODE_MARGIN = 2;
    
    // Language and layout
    protected string $lang;
    protected bool $rtl;
    
    // Design-specific dimensions
    protected int $totalWidth;
    protected int $totalHeight;
    
    public function __construct(Role $role, Collection $events)
    {
        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required to generate event graphics');
        }
        
        $this->role = $role;
        $this->events = $events->take(self::MAX_EVENTS)->values();
        
        $this->lang = in_array(strtolower($role->language_code), ['ar','de','en','es','fr','he','it','nl','pt'], true)
            ? strtolower($role->language_code) : 'en';
        
        $this->rtl = in_array($this->lang, ['ar','he'], true);
        
        // Calculate dimensions based on design type
        $this->calculateDimensions();
        
        // Create image
        $this->im = imagecreatetruecolor($this->totalWidth, $this->totalHeight);
        if (!$this->im) {
            throw new \RuntimeException('Failed to create image resource');
        }
        
        imagealphablending($this->im, true);
        imagesavealpha($this->im, true);
        
        $this->allocateColors();
    }
    
    public function __destruct()
    {
        if ($this->im) {
            imagedestroy($this->im);
        }
    }
    
    public function generate(): string
    {
        // Apply background based on role's style
        $this->applyBackground();
        
        // Generate event layout based on design type
        $this->generateEventLayout();
        
        // Output the image
        ob_start();
        
        // Ensure PNG transparency is preserved
        imagepng($this->im, null, 9, PNG_ALL_FILTERS);
        
        $imageData = ob_get_contents();
        ob_end_clean();
        
        return $imageData;
    }
    
    // Abstract methods that must be implemented by design classes
    abstract protected function calculateDimensions(): void;
    abstract protected function generateEventLayout(): void;
    
    // Common getter methods
    public function getWidth(): int
    {
        return $this->totalWidth;
    }
    
    public function getHeight(): int
    {
        return $this->totalHeight;
    }
    
    public function getEventCount(): int
    {
        return $this->events->count();
    }
    
    protected function allocateColors(): void
    {
        $this->c = [
            'white' => imagecolorallocate($this->im, 255, 255, 255),
            'black' => imagecolorallocate($this->im, 0, 0, 0),
            'gray' => imagecolorallocate($this->im, 128, 128, 128),
            'lightGray' => imagecolorallocate($this->im, 200, 200, 200),
            'darkGray' => imagecolorallocate($this->im, 64, 64, 64),
            'accent' => $this->hexToColor($this->role->accent_color ?? '#000000'),
            'font' => $this->hexToColor($this->role->font_color ?? '#000000'),
        ];
    }
    
    protected function applyBackground(): void
    {
        switch ($this->role->background) {
            case 'gradient':
                $this->applyGradientBackground();
                break;
            case 'solid':
                $this->applySolidBackground();
                break;
            case 'image':
                $this->applyImageBackground();
                break;
            default:
                // Default to a subtle gradient
                $this->applyDefaultBackground();
                break;
        }
    }
    
    protected function applyGradientBackground(): void
    {
        $colors = explode(',', $this->role->background_colors ?? '#f0f0f0,#e0e0e0');
        
        // Validate and sanitize colors
        $color1 = $this->validateHexColor($colors[0] ?? '#f0f0f0');
        $color2 = $this->validateHexColor($colors[1] ?? $colors[0] ?? '#e0e0e0');
        
        $color1Resource = $this->hexToColor($color1);
        $color2Resource = $this->hexToColor($color2);
        
        $rotation = $this->role->background_rotation ?? 0;
        
        // Simple gradient implementation
        for ($y = 0; $y < imagesy($this->im); $y++) {
            $ratio = $y / imagesy($this->im);
            $r = (int)((1 - $ratio) * imagecolorsforindex($this->im, $color1Resource)['red'] + $ratio * imagecolorsforindex($this->im, $color2Resource)['red']);
            $g = (int)((1 - $ratio) * imagecolorsforindex($this->im, $color1Resource)['green'] + $ratio * imagecolorsforindex($this->im, $color2Resource)['green']);
            $b = (int)((1 - $ratio) * imagecolorsforindex($this->im, $color1Resource)['blue'] + $ratio * imagecolorsforindex($this->im, $color2Resource)['blue']);
            $color = imagecolorallocate($this->im, $r, $g, $b);
            
            if ($color !== false) {
                imageline($this->im, 0, $y, imagesx($this->im), $y, $color);
            }
        }
    }
    
    protected function applySolidBackground(): void
    {
        $bgColor = $this->hexToColor($this->role->background_color ?? '#ffffff');
        imagefill($this->im, 0, 0, $bgColor);
    }
    
    protected function applyImageBackground(): void
    {
        $bgColor = $this->hexToColor('#f0f0f0'); // Fallback color
        imagefill($this->im, 0, 0, $bgColor);
        
        // Try to load background image - handle both local and custom URLs
        if ($this->role->background_image) {
            // First try local background image from backgrounds folder
            $imagePath = public_path('images/backgrounds/' . $this->role->background_image . '.png');
            if (file_exists($imagePath)) {
                $bgImage = imagecreatefrompng($imagePath);
                if ($bgImage) {
                    // Resize and apply background image
                    $this->applyResizedBackground($bgImage);
                    imagedestroy($bgImage);
                    return;
                }
            }
        }
        
        // If no local image or it failed to load, try custom background image URL
        if ($this->role->background_image_url) {
            $this->applyCustomBackgroundImage();
        }
    }
    
    protected function applyDefaultBackground(): void
    {
        $bgColor = $this->hexToColor('#f8f9fa');
        imagefill($this->im, 0, 0, $bgColor);
    }
    
    protected function applyResizedBackground($bgImage): void
    {
        $bgWidth = imagesx($bgImage);
        $bgHeight = imagesy($bgImage);
        $targetWidth = imagesx($this->im);
        $targetHeight = imagesy($this->im);
        
        // Scale to cover the entire area
        $scale = max($targetWidth / $bgWidth, $targetHeight / $bgHeight);
        $newWidth = (int)($bgWidth * $scale);
        $newHeight = (int)($bgHeight * $scale);
        
        // Center the image
        $x = (int)(($targetWidth - $newWidth) / 2);
        $y = (int)(($targetHeight - $newHeight) / 2);
        
        // Apply 50% alpha transparency to the background image
        // First, create a temporary image with alpha blending
        $tempImage = imagecreatetruecolor($newWidth, $newHeight);
        if ($tempImage) {
            // Enable alpha blending
            imagealphablending($tempImage, false);
            imagesavealpha($tempImage, true);
            
            // Create a transparent background
            $transparent = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
            imagefill($tempImage, 0, 0, $transparent);
            
            // Copy the resized background image to temp image
            imagecopyresampled($tempImage, $bgImage, 0, 0, 0, 0, $newWidth, $newHeight, $bgWidth, $bgHeight);
            
            // Apply 50% transparency by blending with the main image
            // We'll use imagecopymerge with 50% opacity
            imagecopymerge($this->im, $tempImage, $x, $y, 0, 0, $newWidth, $newHeight, 50);
            
            // Clean up temp image
            imagedestroy($tempImage);
        } else {
            // Fallback to original method if temp image creation fails
            imagecopyresampled($this->im, $bgImage, $x, $y, 0, 0, $newWidth, $newHeight, $bgWidth, $bgHeight);
        }
    }
    
    protected function applyCustomBackgroundImage(): void
    {
        try {
            $imageData = null;
            
            // Handle both local and remote images
            if (filter_var($this->role->background_image_url, FILTER_VALIDATE_URL)) {
                // Remote image
                
                // Disable SSL verification for local development
                $context = null;
                if (app()->environment('local') || config('app.disable_ssl_verification', false)) {
                    $context = stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                        'http' => [
                            'timeout' => 30,
                        ]
                    ]);
                }
                
                $imageData = file_get_contents($this->role->background_image_url, false, $context);
                if ($imageData === false) {
                    // Fallback to cURL if file_get_contents fails
                    $imageData = $this->fetchImageWithCurl($this->role->background_image_url);
                    
                    if ($imageData === false) {
                        return;
                    }
                }
            } else {
                // Local file path
                $imagePath = $this->role->background_image_url;
                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);
                } else {
                    return;
                }
            }
            
            if ($imageData) {
                $bgImage = imagecreatefromstring($imageData);
                if ($bgImage) {
                    // Resize and apply background image
                    $this->applyResizedBackground($bgImage);
                    imagedestroy($bgImage);
                }
            }
        } catch (\Exception $e) {
            // Error applying custom background image
        }
    }
    
    protected function isValidImageUrl(string $url): bool
    {
        if (empty($url)) {
            return false;
        }
        
        // Check if it's a valid URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }
        
        // Check if it's a local path - try different variations
        $possiblePaths = [
            public_path($url),
            public_path('storage/' . $url),
            public_path('images/' . $url),
            $url // Try as absolute path
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function fetchImageWithCurl(string $url): string|false
    {
        if (!function_exists('curl_init')) {
            return false;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; EventGraphicGenerator/1.0)');
        
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return false;
        }
        
        if ($httpCode !== 200) {
            return false;
        }
        
        if ($imageData === false || empty($imageData)) {
            return false;
        }
        
        return $imageData;
    }
    
    protected function hexToColor(string $hex): int
    {
        $hex = ltrim($hex, '#');
        
        // Validate hex color format
        if (!preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            // Return black as fallback for invalid colors
            return $this->c['black'];
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $color = imagecolorallocate($this->im, $r, $g, $b);
        
        // If color allocation fails, return black as fallback
        if ($color === false) {
            return $this->c['black'];
        }
        
        return $color;
    }

    protected function validateHexColor(string $color): string
    {
        $color = trim($color);
        
        // If it's already a valid hex color, return it
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return $color;
        }
        
        // If it's a valid hex color without #, add it
        if (preg_match('/^[0-9A-Fa-f]{6}$/', $color)) {
            return '#' . $color;
        }
        
        // Return a default color if invalid
        return '#f0f0f0';
    }
    
    protected function getCornerRadius(): int
    {
        return self::CORNER_RADIUS;
    }
    
    protected function getQRCodeSize(): int
    {
        return self::QR_CODE_SIZE;
    }
    
    protected function getQRCodePadding(): int
    {
        return self::QR_CODE_PADDING;
    }
    
    protected function getQRCodeMargin(): int
    {
        return self::QR_CODE_MARGIN;
    }
}