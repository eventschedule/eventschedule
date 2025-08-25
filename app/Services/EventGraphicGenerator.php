<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Event;
use App\Services\designs\ModernDesign;
use App\Services\designs\MinimalDesign;
use Illuminate\Support\Collection;

class EventGraphicGenerator
{
    protected Role $role;
    protected Collection $events;
    protected $im;
    protected array $c = [];
    
    // Grid configuration
    protected const GRID_COLS = 3;
    protected const GRID_ROWS = 3;
    protected const MAX_EVENTS = 9;
    protected const FLYER_WIDTH = 400;
    protected const FLYER_HEIGHT = 480; // Reduced from 600 to 480 (20% shorter)
    protected const MARGIN = 30; // Balanced spacing between 20 and 40
    
    // Language and layout
    protected string $lang;
    protected bool $rtl;
    
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
        
        // Calculate total dimensions
        $totalWidth = (self::FLYER_WIDTH * self::GRID_COLS) + (self::MARGIN * (self::GRID_COLS + 1));
        $totalHeight = (self::FLYER_HEIGHT * self::GRID_ROWS) + (self::MARGIN * (self::GRID_ROWS + 1));
        
        // Create image
        $this->im = imagecreatetruecolor($totalWidth, $totalHeight);
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
        // Check if we have events to display
        if ($this->events->isEmpty()) {
            return $this->generateNoEventsMessage();
        }
        
        // Apply background based on role's style
        $this->applyBackground();
        
        // Generate individual event flyers
        $this->generateEventFlyers();
        
        // Output the image
        ob_start();
        imagepng($this->im);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        return $imageData;
    }
    
    protected function generateNoEventsMessage(): string
    {
        // Create a simple image with "No upcoming events" message
        $width = 800;
        $height = 400;
        $im = imagecreatetruecolor($width, $height);
        
        if (!$im) {
            throw new \RuntimeException('Failed to create image resource for no events message');
        }
        
        // Set background
        $bgColor = imagecolorallocate($im, 248, 249, 250);
        imagefill($im, 0, 0, $bgColor);
        
        // Add text
        $text = 'No upcoming events';
        $fontSize = 24;
        $font = $this->getFontPath('Montserrat-Bold.ttf', '/System/Library/Fonts/Helvetica.ttc');
        $color = imagecolorallocate($im, 108, 117, 125);
        
        // Center text
        $bbox = imagettfbbox($fontSize, 0, $font, $text);
        $textWidth = $bbox[2] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[7];
        $x = ($width - $textWidth) / 2;
        $y = ($height + $textHeight) / 2;
        
        imagettftext($im, $fontSize, 0, $x, $y, $color, $font, $text);
        
        // Output
        ob_start();
        imagepng($im);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($im);
        return $imageData;
    }
    
    public function getWidth(): int
    {
        return (self::FLYER_WIDTH * self::GRID_COLS) + (self::MARGIN * (self::GRID_COLS + 1));
    }
    
    public function getHeight(): int
    {
        return (self::FLYER_HEIGHT * self::GRID_ROWS) + (self::MARGIN * (self::GRID_ROWS + 1));
    }
    
    public function getEventCount(): int
    {
        return $this->events->count();
    }
    
    public function getGridInfo(): array
    {
        return [
            'cols' => self::GRID_COLS,
            'rows' => self::GRID_ROWS,
            'max_events' => self::MAX_EVENTS,
            'flyer_width' => self::FLYER_WIDTH,
            'flyer_height' => self::FLYER_HEIGHT,
            'margin' => self::MARGIN,
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
        ];
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
        \Log::info("Applying background for role: " . $this->role->id . " - background type: " . $this->role->background);
        
        switch ($this->role->background) {
            case 'gradient':
                \Log::info("Applying gradient background with colors: " . ($this->role->background_colors ?? 'none'));
                $this->applyGradientBackground();
                break;
            case 'solid':
                \Log::info("Applying solid background with color: " . ($this->role->background_color ?? 'none'));
                $this->applySolidBackground();
                break;
            case 'image':
                \Log::info("Applying image background: " . ($this->role->background_image ?? 'none'));
                $this->applyImageBackground();
                break;
            default:
                \Log::info("Applying default background (no role background configured)");
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
        
        // Try to load background image
        if ($this->role->background_image) {
            $imagePath = public_path('images/backgrounds/' . $this->role->background_image . '.png');
            if (file_exists($imagePath)) {
                $bgImage = imagecreatefrompng($imagePath);
                if ($bgImage) {
                    // Resize and apply background image
                    $this->applyResizedBackground($bgImage);
                    imagedestroy($bgImage);
                }
            }
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
        
        imagecopyresampled($this->im, $bgImage, $x, $y, 0, 0, $newWidth, $newHeight, $bgWidth, $bgHeight);
    }
    
    protected function generateEventFlyers(): void
    {
        \Log::info("Generating event flyers for " . $this->events->count() . " events");
        
        $eventIndex = 0;
        
        for ($row = 0; $row < self::GRID_ROWS && $eventIndex < $this->events->count(); $row++) {
            for ($col = 0; $col < self::GRID_COLS && $eventIndex < $this->events->count(); $col++) {
                $event = $this->events[$eventIndex];
                \Log::info("Processing event {$eventIndex}: ID={$event->id}, Name={$event->name}, Image={$event->flyerImageUrl}");
                $this->generateSingleFlyer($event, $row, $col);
                $eventIndex++;
            }
        }
    }
    
    protected function generateSingleFlyer(Event $event, int $row, int $col): void
    {
        $x = self::MARGIN + ($col * (self::FLYER_WIDTH + self::MARGIN));
        $y = self::MARGIN + ($row * (self::FLYER_HEIGHT + self::MARGIN));
        
        // Get the event flyer image
        $this->addEventFlyerImage($event, $x, $y);
    }
    
    protected function addEventFlyerImage(Event $event, int $x, int $y): void
    {
        // Debug: Check all possible image-related properties
        \Log::info("=== DEBUG EVENT {$event->id} ===");
        \Log::info("Event name: " . $event->name);
        \Log::info("flyerImageUrl: " . ($event->flyerImageUrl ?? 'NULL'));
        \Log::info("flyer_image_url: " . ($event->flyer_image_url ?? 'NULL'));
        \Log::info("getImageUrl(): " . ($event->getImageUrl() ?? 'NULL'));
        
        // Check if the property exists and has a value
        if (property_exists($event, 'flyerImageUrl')) {
            \Log::info("flyerImageUrl property exists");
        } else {
            \Log::info("flyerImageUrl property does NOT exist");
        }
        
        if (property_exists($event, 'flyer_image_url')) {
            \Log::info("flyer_image_url property exists");
        } else {
            \Log::info("flyer_image_url property does NOT exist");
        }
        
        // Try different ways to get the image URL
        $imageUrl = null;
        
        // Method 1: Direct property access
        if (isset($event->flyerImageUrl) && !empty($event->flyerImageUrl)) {
            $imageUrl = $event->flyerImageUrl;
            \Log::info("Using flyerImageUrl: " . $imageUrl);
        }
        // Method 2: Check flyer_image_url property
        elseif (isset($event->flyer_image_url) && !empty($event->flyer_image_url)) {
            $imageUrl = $event->flyer_image_url;
            \Log::info("Using flyer_image_url: " . $imageUrl);
        }
        // Method 3: Fallback to getImageUrl method
        else {
            $imageUrl = $event->getImageUrl();
            \Log::info("Using getImageUrl(): " . $imageUrl);
        }
        
        \Log::info("Final imageUrl: " . ($imageUrl ?? 'NULL'));
        
        if ($imageUrl && $this->isValidImageUrl($imageUrl)) {
            \Log::info("Image URL is valid, attempting to load...");
            // Try to load and display the event image
            $this->loadAndDisplayEventImage($imageUrl, $x, $y);
        } else {
            // Fallback to a placeholder background if no image
            \Log::info("Creating placeholder for event {$event->id} - invalid image URL: " . $imageUrl);
            $this->createPlaceholderBackground($x, $y);
        }
        
        \Log::info("=== END DEBUG EVENT {$event->id} ===");
    }
    
    protected function loadAndDisplayEventImage(string $imageUrl, int $x, int $y): void
    {
        \Log::info("=== LOADING IMAGE ===");
        \Log::info("Image URL: " . $imageUrl);
        
        try {
            $sourceImage = null;
            
            // Handle both local and remote images
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Remote image
                \Log::info("Loading remote image: " . $imageUrl);
                
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
                
                $imageData = file_get_contents($imageUrl, false, $context);
                if ($imageData === false) {
                    \Log::info("file_get_contents failed, trying cURL as fallback");
                    
                    // Fallback to cURL if file_get_contents fails
                    $imageData = $this->fetchImageWithCurl($imageUrl);
                    
                    if ($imageData === false) {
                        \Log::info("Failed to fetch remote image with both methods: " . $imageUrl);
                        $this->createPlaceholderBackground($x, $y);
                        return;
                    }
                }
                \Log::info("Remote image data size: " . strlen($imageData) . " bytes");
                $sourceImage = imagecreatefromstring($imageData);
            } else {
                // Local image - try different path variations
                \Log::info("Loading local image: " . $imageUrl);
                $possiblePaths = [
                    public_path($imageUrl),
                    public_path('storage/' . $imageUrl),
                    public_path('images/' . $imageUrl),
                    $imageUrl // Try as absolute path
                ];
                
                \Log::info("Trying possible paths:");
                foreach ($possiblePaths as $path) {
                    \Log::info("  - " . $path . " (exists: " . (file_exists($path) ? 'YES' : 'NO') . ")");
                }
                
                $sourceImage = null;
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        \Log::info("Found local image at: " . $path);
                        $fileSize = filesize($path);
                        \Log::info("File size: " . $fileSize . " bytes");
                        
                        $imageData = file_get_contents($path);
                        $sourceImage = imagecreatefromstring($imageData);
                        
                        if ($sourceImage) {
                            break;
                        }
                    }
                }
                
                if (!$sourceImage) {
                    \Log::info("Failed to load local image from any path: " . $imageUrl);
                    $this->createPlaceholderBackground($x, $y);
                    return;
                }
            }
            
            if ($sourceImage === false) {
                \Log::info("Failed to create image resource from: " . $imageUrl);
                $this->createPlaceholderBackground($x, $y);
                return;
            }
            
            \Log::info("Successfully created image resource");
            \Log::info("Source image dimensions: " . imagesx($sourceImage) . "x" . imagesy($sourceImage));
            
            // Resize and display the image
            $this->resizeAndDisplayImage($sourceImage, $x, $y);
            
            // Clean up
            imagedestroy($sourceImage);
            \Log::info("Image displayed successfully");
            
        } catch (\Exception $e) {
            \Log::info("Exception loading image: " . $e->getMessage());
            \Log::info("Stack trace: " . $e->getTraceAsString());
            // Fallback to placeholder on any error
            $this->createPlaceholderBackground($x, $y);
        }
        
        \Log::info("=== END LOADING IMAGE ===");
    }
    
    protected function resizeAndDisplayImage($sourceImage, int $x, int $y): void
    {
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
        // No background or border - let the image fill the entire flyer area
        
        // Calculate how to fit the image within the flyer boundaries while maintaining aspect ratio
        $sourceAspectRatio = $sourceWidth / $sourceHeight;
        $flyerAspectRatio = self::FLYER_WIDTH / self::FLYER_HEIGHT;
        
        if ($sourceAspectRatio > $flyerAspectRatio) {
            // Image is wider than flyer - fit to height, crop left/right
            $newHeight = self::FLYER_HEIGHT;
            $newWidth = (int)($newHeight * $sourceAspectRatio);
            $offsetX = (int)((self::FLYER_WIDTH - $newWidth) / 2);
            $offsetY = 0;
        } else {
            // Image is taller than flyer - fit to width, crop top/bottom
            $newWidth = self::FLYER_WIDTH;
            $newHeight = (int)($newWidth / $sourceAspectRatio);
            $offsetX = 0;
            $offsetY = (int)((self::FLYER_HEIGHT - $newHeight) / 2);
        }
        
        // Ensure the image stays within the flyer boundaries by clipping
        $finalX = $x + $offsetX;
        $finalY = $y + $offsetY;
        
        // Clip the image to fit within the flyer area
        if ($finalX < $x) {
            $finalX = $x;
        }
        if ($finalY < $y) {
            $finalY = $y;
        }
        
        // For wide images, we need to calculate the source crop area to maintain aspect ratio
        if ($sourceAspectRatio > $flyerAspectRatio) {
            // Calculate how much of the source image to show (crop left/right)
            $sourceCropWidth = (int)($sourceHeight * $flyerAspectRatio);
            $sourceCropX = (int)(($sourceWidth - $sourceCropWidth) / 2);
            
            // Copy and resize the source image, cropping the sides
            imagecopyresampled(
                $this->im, $sourceImage,
                $x, $y, $sourceCropX, 0,
                self::FLYER_WIDTH, self::FLYER_HEIGHT,
                $sourceCropWidth, $sourceHeight
            );
        } else {
            // For tall images, crop top/bottom
            $sourceCropHeight = (int)($sourceWidth / $flyerAspectRatio);
            $sourceCropY = (int)(($sourceHeight - $sourceCropHeight) / 2);
            
            // Copy and resize the source image, cropping top/bottom
            imagecopyresampled(
                $this->im, $sourceImage,
                $x, $y, 0, $sourceCropY,
                self::FLYER_WIDTH, self::FLYER_HEIGHT,
                $sourceWidth, $sourceCropHeight
            );
        }
    }
    
    protected function createPlaceholderBackground(int $x, int $y): void
    {
        // Create a more visually appealing placeholder using the role's accent color
        $bgColor = $this->c['white'];
        $borderColor = $this->c['accent'];
        
        // Fill background
        imagefilledrectangle($this->im, $x, $y, $x + self::FLYER_WIDTH, $y + self::FLYER_HEIGHT, $bgColor);
        
        // Add accent border
        imagerectangle($this->im, $x, $y, $x + self::FLYER_WIDTH, $y + self::FLYER_HEIGHT, $borderColor);
        
        // Add subtle shadow effect
        $shadowColor = imagecolorallocatealpha($this->im, 0, 0, 0, 40);
        imagefilledrectangle($this->im, $x + 2, $y + 2, $x + self::FLYER_WIDTH + 2, $y + self::FLYER_HEIGHT + 2, $shadowColor);
        
        // Add a subtle accent color overlay in the top section
        $accentOverlay = imagecolorallocatealpha($this->im, 
            imagecolorsforindex($this->im, $this->c['accent'])['red'],
            imagecolorsforindex($this->im, $this->c['accent'])['green'],
            imagecolorsforindex($this->im, $this->c['accent'])['blue'],
            30 // Very subtle
        );
        
        // Add accent bar at top
        imagefilledrectangle($this->im, $x, $y, $x + self::FLYER_WIDTH, $y + 20, $accentOverlay);
        
        // Add some decorative elements
        $this->addPlaceholderDecorations($x, $y);
    }
    
    protected function addPlaceholderDecorations(int $x, int $y): void
    {
        $accentColor = $this->c['accent'];
        
        // Add a subtle pattern of small dots
        $dotColor = imagecolorallocatealpha($this->im, 
            imagecolorsforindex($this->im, $accentColor)['red'],
            imagecolorsforindex($this->im, $accentColor)['green'],
            imagecolorsforindex($this->im, $accentColor)['blue'],
            60 // Semi-transparent
        );
        
        // Create a subtle dot pattern
        for ($i = 0; $i < 8; $i++) {
            for ($j = 0; $j < 12; $j++) {
                $dotX = $x + 30 + ($i * 45);
                $dotY = $y + 60 + ($j * 45);
                if ($dotX < $x + self::FLYER_WIDTH - 30 && $dotY < $y + self::FLYER_HEIGHT - 30) {
                    imagefilledellipse($this->im, $dotX, $dotY, 3, 3, $dotColor);
                }
            }
        }
    }
    
    protected function isValidImageUrl(string $url): bool
    {
        \Log::info("=== VALIDATING IMAGE URL ===");
        \Log::info("URL to validate: " . $url);
        
        if (empty($url)) {
            \Log::info("Empty image URL provided");
            return false;
        }
        
        // Check if it's a valid URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            \Log::info("Valid remote URL: " . $url);
            return true;
        }
        
        // Check if it's a local path - try different variations
        $possiblePaths = [
            public_path($url),
            public_path('storage/' . $url),
            public_path('images/' . $url),
            $url // Try as absolute path
        ];
        
        \Log::info("Checking local paths:");
        foreach ($possiblePaths as $path) {
            $exists = file_exists($path);
            \Log::info("  - " . $path . " (exists: " . ($exists ? 'YES' : 'NO') . ")");
            if ($exists) {
                \Log::info("    File size: " . filesize($path) . " bytes");
                \Log::info("    File permissions: " . substr(sprintf('%o', fileperms($path)), -4));
            }
        }
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                \Log::info("Valid local image found at: " . $path);
                return true;
            }
        }
        
        \Log::info("No valid image found for URL: " . $url);
        return false;
    }
    
    protected function fetchImageWithCurl(string $url): string|false
    {
        if (!function_exists('curl_init')) {
            \Log::info("cURL not available, cannot fetch image");
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
            \Log::info("cURL error: " . $error);
            return false;
        }
        
        if ($httpCode !== 200) {
            \Log::info("HTTP error: " . $httpCode);
            return false;
        }
        
        if ($imageData === false || empty($imageData)) {
            \Log::info("cURL returned empty data");
            return false;
        }
        
        \Log::info("Successfully fetched image with cURL, size: " . strlen($imageData) . " bytes");
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
}