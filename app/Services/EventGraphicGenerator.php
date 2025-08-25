<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Event;
use App\Services\designs\ModernDesign;
use App\Services\designs\MinimalDesign;
use Illuminate\Support\Collection;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class EventGraphicGenerator
{
    protected Role $role;
    protected Collection $events;
    protected $im;
    protected array $c = [];
    
    // Grid configuration - now dynamic
    protected int $gridCols;
    protected int $gridRows;
    protected const MAX_EVENTS = 9;
    protected const FLYER_WIDTH = 400;
    protected const FLYER_HEIGHT = 480;
    protected const MARGIN = 30;
    protected const CORNER_RADIUS = 8;
    
    // QR Code configuration
    protected const QR_CODE_SIZE = 80;
    protected const QR_CODE_PADDING = 20;
    protected const QR_CODE_MARGIN = 2;
    
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
        
        // Calculate dynamic grid dimensions based on event count
        $this->calculateGridDimensions();
        
        $this->lang = in_array(strtolower($role->language_code), ['ar','de','en','es','fr','he','it','nl','pt'], true)
            ? strtolower($role->language_code) : 'en';
        
        $this->rtl = in_array($this->lang, ['ar','he'], true);
        
        // Calculate total dimensions based on dynamic grid
        $totalWidth = (self::FLYER_WIDTH * $this->gridCols) + (self::MARGIN * ($this->gridCols + 1));
        $totalHeight = (self::FLYER_HEIGHT * $this->gridRows) + (self::MARGIN * ($this->gridRows + 1));
        
        \Log::info("Final image dimensions: {$totalWidth}x{$totalHeight} (grid: {$this->gridCols}x{$this->gridRows})");
        
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
        // Apply background based on role's style
        $this->applyBackground();
        
        // Generate individual event flyers
        $this->generateEventFlyers();
        
        \Log::info("All flyers generated, preparing PNG output...");
        
        // Output the image
        ob_start();
        
        // Ensure PNG transparency is preserved
        imagepng($this->im, null, 9, PNG_ALL_FILTERS);
        
        $imageData = ob_get_contents();
        ob_end_clean();
        
        \Log::info("PNG output generated, size: " . strlen($imageData) . " bytes");
        
        return $imageData;
    }
    
    public function getWidth(): int
    {
        return (self::FLYER_WIDTH * $this->gridCols) + (self::MARGIN * ($this->gridCols + 1));
    }
    
    public function getHeight(): int
    {
        return (self::FLYER_HEIGHT * $this->gridRows) + (self::MARGIN * ($this->gridRows + 1));
    }
    
    public function getEventCount(): int
    {
        return $this->events->count();
    }
    
    public function getGridInfo(): array
    {
        return [
            'cols' => $this->gridCols,
            'rows' => $this->gridRows,
            'max_events' => self::MAX_EVENTS,
            'flyer_width' => self::FLYER_WIDTH,
            'flyer_height' => self::FLYER_HEIGHT,
            'margin' => self::MARGIN,
            'corner_radius' => self::CORNER_RADIUS,
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
            'qr_code_size' => self::QR_CODE_SIZE,
            'qr_code_padding' => self::QR_CODE_PADDING,
            'qr_code_margin' => self::QR_CODE_MARGIN,
            'qr_code_actual_size' => $this->getActualQRCodeSize(),
            'qr_code_validation' => $this->validateQRCodeFit(),
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
        \Log::info("Applying custom background image from URL: " . $this->role->background_image_url);
        
        try {
            $imageData = null;
            
            // Handle both local and remote images
            if (filter_var($this->role->background_image_url, FILTER_VALIDATE_URL)) {
                // Remote image
                \Log::info("Loading remote background image: " . $this->role->background_image_url);
                
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
                    \Log::info("file_get_contents failed, trying cURL as fallback");
                    
                    // Fallback to cURL if file_get_contents fails
                    $imageData = $this->fetchImageWithCurl($this->role->background_image_url);
                    
                    if ($imageData === false) {
                        \Log::info("Failed to fetch remote background image with both methods: " . $this->role->background_image_url);
                        return;
                    }
                }
            } else {
                // Local file path
                $imagePath = $this->role->background_image_url;
                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);
                } else {
                    \Log::info("Local background image file not found: " . $imagePath);
                    return;
                }
            }
            
            if ($imageData) {
                $bgImage = imagecreatefromstring($imageData);
                if ($bgImage) {
                    // Resize and apply background image
                    $this->applyResizedBackground($bgImage);
                    imagedestroy($bgImage);
                    \Log::info("Successfully applied custom background image");
                } else {
                    \Log::info("Failed to create image resource from custom background image data");
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error applying custom background image: " . $e->getMessage());
        }
    }
    
    protected function generateEventFlyers(): void
    {
        \Log::info("Generating event flyers for " . $this->events->count() . " events");
        
        $eventIndex = 0;
        
        for ($row = 0; $row < $this->gridRows && $eventIndex < $this->events->count(); $row++) {
            for ($col = 0; $col < $this->gridCols && $eventIndex < $this->events->count(); $col++) {
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
        
        // Add QR code to the bottom left corner of the flyer
        $this->addEventQRCode($event, $x, $y);
    }
    
    /**
     * Calculate standardized QR code position for a flyer
     * This ensures all QR codes are positioned exactly the same way
     */
    protected function calculateQRCodePosition(int $x, int $y): array
    {
        // Standard position: bottom left corner with consistent padding
        $qrX = $x + self::QR_CODE_PADDING;
        $qrY = $y + self::FLYER_HEIGHT - self::QR_CODE_SIZE - self::QR_CODE_PADDING;
        
        // Ensure QR code doesn't go outside flyer boundaries
        if ($qrX + self::QR_CODE_SIZE > $x + self::FLYER_WIDTH) {
            $qrX = $x + self::FLYER_WIDTH - self::QR_CODE_SIZE - self::QR_CODE_PADDING;
        }
        if ($qrY + self::QR_CODE_SIZE > $y + self::FLYER_HEIGHT) {
            $qrY = $y + self::FLYER_HEIGHT - self::QR_CODE_SIZE - self::QR_CODE_PADDING;
        }
        if ($qrX < $x) {
            $qrX = $x + self::QR_CODE_PADDING;
        }
        if ($qrY < $y) {
            $qrY = $y + self::QR_CODE_PADDING;
        }
        
        return [
            'x' => $qrX,
            'y' => $qrY,
            'size' => self::QR_CODE_SIZE
        ];
    }
    
    /**
     * Generate and add a QR code to the bottom left corner of a flyer
     */
    protected function addEventQRCode(Event $event, int $x, int $y): void
    {
        try {
            // Generate the event URL for the QR code
            $eventUrl = $event->registration_url ?: $event->getGuestUrl($this->role->subdomain);
            
            \Log::info("Generating QR code for event {$event->id} with URL: {$eventUrl}");
            
            // Create QR code with consistent size
            $qrCode = QrCode::create($eventUrl)
                ->setSize(self::QR_CODE_SIZE)
                ->setMargin(self::QR_CODE_MARGIN);
            
            // Create PNG writer and generate QR code image
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrCodeImageData = $result->getString();
            
            // Create image resource from QR code data
            $qrCodeImage = imagecreatefromstring($qrCodeImageData);
            if (!$qrCodeImage) {
                \Log::warning("Failed to create QR code image resource for event {$event->id}");
                return;
            }
            
            // Get actual QR code dimensions to ensure consistency
            $actualQRWidth = imagesx($qrCodeImage);
            $actualQRHeight = imagesy($qrCodeImage);
            
            \Log::info("QR code dimensions for event {$event->id}: {$actualQRWidth}x{$actualQRHeight}");
            
            // Ensure QR code size is within flyer boundaries
            $maxQRSize = min(self::FLYER_WIDTH - (self::QR_CODE_PADDING * 2), self::FLYER_HEIGHT - (self::QR_CODE_PADDING * 2));
            $qrSize = min(self::QR_CODE_SIZE, $maxQRSize);
            
            // If the generated QR code is a different size than expected, resize it to ensure consistency
            if ($actualQRWidth !== $qrSize || $actualQRHeight !== $qrSize) {
                \Log::info("Resizing QR code from {$actualQRWidth}x{$actualQRHeight} to {$qrSize}x{$qrSize} for consistency");
                
                // Create a new image with the exact size we want
                $resizedQRImage = imagecreatetruecolor($qrSize, $qrSize);
                if ($resizedQRImage) {
                    // Enable alpha blending for the resized image
                    imagealphablending($resizedQRImage, false);
                    imagesavealpha($resizedQRImage, true);
                    
                    // Create transparent background
                    $transparent = imagecolorallocatealpha($resizedQRImage, 0, 0, 0, 127);
                    imagefill($resizedQRImage, 0, 0, $transparent);
                    
                    // Copy and resize the original QR code to the new image
                    imagecopyresampled(
                        $resizedQRImage, $qrCodeImage,
                        0, 0, 0, 0,
                        $qrSize, $qrSize,
                        $actualQRWidth, $actualQRHeight
                    );
                    
                    // Clean up original QR code image
                    imagedestroy($qrCodeImage);
                    
                    // Use the resized image
                    $qrCodeImage = $resizedQRImage;
                }
            }
            
            // Calculate standardized QR code position
            $position = $this->calculateQRCodePosition($x, $y);
            $qrX = $position['x'];
            $qrY = $position['y'];
            $qrSize = $position['size'];
            
            \Log::info("QR code final position for event {$event->id}: ({$qrX}, {$qrY}) with size {$qrSize}");
            
            // Copy QR code to the main image with consistent size
            imagecopy(
                $this->im, 
                $qrCodeImage, 
                $qrX, 
                $qrY, 
                0, 
                0, 
                $qrSize, 
                $qrSize
            );
            
            // Clean up QR code image resource
            imagedestroy($qrCodeImage);
            
            \Log::info("QR code added successfully to event {$event->id} at position ({$qrX}, {$qrY}) with size {$qrSize}");
            
        } catch (\Exception $e) {
            \Log::error("Error generating QR code for event {$event->id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            // Continue without QR code if there's an error
        }
    }
    
    protected function addEventFlyerImage(Event $event, int $x, int $y): void
    {
        // Debug: Check all possible image-related properties
        \Log::info("=== DEBUG EVENT {$event->id} ===");
        \Log::info("Event name: " . $event->name);

        $imageUrl = $event->flyer_image_url;
        \Log::info("Image URL: " . $imageUrl);
                
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
        
        // Create a temporary image with the resized event image
        $tempImage = imagecreatetruecolor(self::FLYER_WIDTH, self::FLYER_HEIGHT);
        if (!$tempImage) {
            // Fallback to original method if temp image creation fails
            $this->applyImageWithoutRoundedCorners($sourceImage, $x, $y, $sourceAspectRatio, $flyerAspectRatio);
            return;
        }
        
        // Enable alpha blending for the temp image
        imagealphablending($tempImage, false);
        imagesavealpha($tempImage, true);
        
        // Create transparent background
        $transparent = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
        imagefill($tempImage, 0, 0, $transparent);
        
        // Copy and resize the source image to the temp image
        if ($sourceAspectRatio > $flyerAspectRatio) {
            // Calculate how much of the source image to show (crop left/right)
            $sourceCropWidth = (int)($sourceHeight * $flyerAspectRatio);
            $sourceCropX = (int)(($sourceWidth - $sourceCropWidth) / 2);
            
            imagecopyresampled(
                $tempImage, $sourceImage,
                0, 0, $sourceCropX, 0,
                self::FLYER_WIDTH, self::FLYER_HEIGHT,
                $sourceCropWidth, $sourceHeight
            );
        } else {
            // For tall images, crop top/bottom
            $sourceCropHeight = (int)($sourceWidth / $flyerAspectRatio);
            $sourceCropY = (int)(($sourceHeight - $sourceCropHeight) / 2);
            
            imagecopyresampled(
                $tempImage, $sourceImage,
                0, 0, 0, $sourceCropY,
                self::FLYER_WIDTH, self::FLYER_HEIGHT,
                $sourceWidth, $sourceCropHeight
            );
        }
        
        // Apply rounded corners mask
        $this->applyRoundedCorners($tempImage, self::FLYER_WIDTH, self::FLYER_HEIGHT);
        
        // Copy the rounded image to the main canvas
        imagecopy($this->im, $tempImage, $x, $y, 0, 0, self::FLYER_WIDTH, self::FLYER_HEIGHT);
        
        // Clean up temp image
        imagedestroy($tempImage);
    }
    
    /**
     * Apply rounded corners to an image using alpha blending
     */
    protected function applyRoundedCorners($image, int $width, int $height): void
    {
        $cornerRadius = $this->getCornerRadius();
        
        \Log::info("Applying rounded corners with radius: {$cornerRadius} to image {$width}x{$height}");
        
        // Create a mask for rounded corners
        $mask = imagecreatetruecolor($width, $height);
        if (!$mask) {
            \Log::error("Failed to create mask for rounded corners");
            return;
        }
        
        // Enable alpha blending for the mask
        imagealphablending($mask, false);
        imagesavealpha($mask, true);
        
        // Create transparent background
        $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $transparent);
        
        // Create white (opaque) color for the mask
        $white = imagecolorallocate($mask, 255, 255, 255);
        
        // Fill the main rectangle area
        imagefilledrectangle($mask, $cornerRadius, 0, $width - $cornerRadius - 1, $height - 1, $white);
        imagefilledrectangle($mask, 0, $cornerRadius, $width - 1, $height - $cornerRadius - 1, $white);
        
        // Fill the corner areas with circles
        // Top-left corner
        imagefilledellipse($mask, $cornerRadius, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, $white);
        
        // Top-right corner
        imagefilledellipse($mask, $width - $cornerRadius - 1, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, $white);
        
        // Bottom-left corner
        imagefilledellipse($mask, $cornerRadius, $height - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, $white);
        
        // Bottom-right corner
        imagefilledellipse($mask, $width - $cornerRadius - 1, $height - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, $white);
        
        \Log::info("Mask created successfully, applying to image...");
        
        // Apply the mask to the image
        $this->applyMaskToImage($image, $mask);
        
        \Log::info("Rounded corners applied successfully");
        
        // Clean up mask
        imagedestroy($mask);
    }
    
    /**
     * Apply a mask to an image to create transparency
     */
    protected function applyMaskToImage($image, $mask): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        
        \Log::info("Applying mask to image {$width}x{$height}");
        
        $transparentPixels = 0;
        $totalPixels = 0;
        
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $totalPixels++;
                $maskColor = imagecolorat($mask, $x, $y);
                $imageColor = imagecolorat($image, $x, $y);
                
                // Get RGB values from mask (white = 255,255,255, black = 0,0,0)
                $maskR = ($maskColor >> 16) & 0xFF;
                $maskG = ($maskColor >> 8) & 0xFF;
                $maskB = $maskColor & 0xFF;
                
                // If mask is black (transparent area), make image transparent
                if ($maskR < 128 && $maskG < 128 && $maskB < 128) {
                    // Get RGB values from image
                    $r = ($imageColor >> 16) & 0xFF;
                    $g = ($imageColor >> 8) & 0xFF;
                    $b = $imageColor & 0xFF;
                    
                    // Create new color with full transparency
                    $newColor = imagecolorallocatealpha($image, $r, $g, $b, 127);
                    imagesetpixel($image, $x, $y, $newColor);
                    $transparentPixels++;
                }
            }
        }
        
        \Log::info("Mask applied: {$transparentPixels} pixels made transparent out of {$totalPixels} total pixels");
    }
    
    /**
     * Fallback method for applying image without rounded corners
     */
    protected function applyImageWithoutRoundedCorners($sourceImage, int $x, int $y, float $sourceAspectRatio, float $flyerAspectRatio): void
    {
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
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
        // Create a temporary image for the placeholder with rounded corners
        $tempImage = imagecreatetruecolor(self::FLYER_WIDTH, self::FLYER_HEIGHT);
        if (!$tempImage) {
            // Fallback to original method if temp image creation fails
            $this->createPlaceholderBackgroundWithoutRoundedCorners($x, $y);
            return;
        }
        
        // Enable alpha blending for the temp image
        imagealphablending($tempImage, false);
        imagesavealpha($tempImage, true);
        
        // Create transparent background
        $transparent = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
        imagefill($tempImage, 0, 0, $transparent);
        
        // Create a more visually appealing placeholder using the role's accent color
        $bgColor = $this->c['white'];
        $borderColor = $this->c['accent'];
        
        // Fill background
        imagefilledrectangle($tempImage, 0, 0, self::FLYER_WIDTH, self::FLYER_HEIGHT, $bgColor);
        
        // Add accent border
        imagerectangle($tempImage, 0, 0, self::FLYER_WIDTH, self::FLYER_HEIGHT, $borderColor);
        
        // Add subtle shadow effect
        $shadowColor = imagecolorallocatealpha($tempImage, 0, 0, 0, 40);
        imagefilledrectangle($tempImage, 2, 2, self::FLYER_WIDTH + 2, self::FLYER_HEIGHT + 2, $shadowColor);
        
        // Add a subtle accent color overlay in the top section
        $accentOverlay = imagecolorallocatealpha($tempImage, 
            imagecolorsforindex($tempImage, $this->c['accent'])['red'],
            imagecolorsforindex($tempImage, $this->c['accent'])['green'],
            imagecolorsforindex($tempImage, $this->c['accent'])['blue'],
            30 // Very subtle
        );
        
        // Add accent bar at top
        imagefilledrectangle($tempImage, 0, 0, self::FLYER_WIDTH, 20, $accentOverlay);
        
        // Add some decorative elements
        $this->addPlaceholderDecorationsToImage($tempImage);
        
        // Apply rounded corners mask
        $this->applyRoundedCorners($tempImage, self::FLYER_WIDTH, self::FLYER_HEIGHT);
        
        // Copy the rounded placeholder to the main canvas
        imagecopy($this->im, $tempImage, $x, $y, 0, 0, self::FLYER_WIDTH, self::FLYER_HEIGHT);
        
        // Clean up temp image
        imagedestroy($tempImage);
    }
    
    /**
     * Fallback method for creating placeholder without rounded corners
     */
    protected function createPlaceholderBackgroundWithoutRoundedCorners(int $x, int $y): void
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
    
    /**
     * Add placeholder decorations to a temporary image
     */
    protected function addPlaceholderDecorationsToImage($image): void
    {
        $accentColor = $this->c['accent'];
        
        // Add a subtle pattern of small dots
        $dotColor = imagecolorallocatealpha($image, 
            imagecolorsforindex($image, $accentColor)['red'],
            imagecolorsforindex($image, $accentColor)['green'],
            imagecolorsforindex($image, $accentColor)['blue'],
            60 // Semi-transparent
        );
        
        // Create a subtle dot pattern
        for ($i = 0; $i < 8; $i++) {
            for ($j = 0; $j < 12; $j++) {
                $dotX = 30 + ($i * 45);
                $dotY = 60 + ($j * 45);
                if ($dotX < self::FLYER_WIDTH - 30 && $dotY < self::FLYER_HEIGHT - 30) {
                    imagefilledellipse($image, $dotX, $dotY, 3, 3, $dotColor);
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

    /**
     * Calculate optimal grid dimensions based on event count
     * Creates layouts that are visually balanced and have good aspect ratios
     */
    protected function calculateGridDimensions(): void
    {
        $eventCount = $this->events->count();
        
        \Log::info("Calculating grid dimensions for {$eventCount} events");
        
        if ($eventCount === 0) {
            $this->gridCols = 1;
            $this->gridRows = 1;
            \Log::info("Grid layout: 1x1 (no events)");
            return;
        }
        
        if ($eventCount === 1) {
            $this->gridCols = 1;
            $this->gridRows = 1;
            \Log::info("Grid layout: 1x1 (single event)");
            return;
        }
        
        if ($eventCount === 2) {
            $this->gridCols = 2;
            $this->gridRows = 1;
            \Log::info("Grid layout: 2x1 (two events)");
            return;
        }
        
        if ($eventCount === 3) {
            $this->gridCols = 3;
            $this->gridRows = 1;
            \Log::info("Grid layout: 3x1 (three events)");
            return;
        }
        
        if ($eventCount === 4) {
            $this->gridCols = 2;
            $this->gridRows = 2;
            \Log::info("Grid layout: 2x2 (four events)");
            return;
        }
        
        if ($eventCount === 5) {
            // 3x2 layout with one empty space - looks better than 2x3
            $this->gridCols = 3;
            $this->gridRows = 2;
            \Log::info("Grid layout: 3x2 (five events)");
            return;
        }
        
        if ($eventCount === 6) {
            $this->gridCols = 3;
            $this->gridRows = 2;
            \Log::info("Grid layout: 3x2 (six events)");
            return;
        }
        
        if ($eventCount === 7) {
            // 3x3 layout with two empty spaces - still looks balanced
            $this->gridCols = 3;
            $this->gridRows = 3;
            \Log::info("Grid layout: 3x3 (seven events)");
            return;
        }
        
        if ($eventCount === 8) {
            // 3x3 layout with one empty space
            $this->gridCols = 3;
            $this->gridRows = 3;
            \Log::info("Grid layout: 3x3 (eight events)");
            return;
        }
        
        // For 9 events, use 3x3 grid
        $this->gridCols = 3;
        $this->gridRows = 3;
        \Log::info("Grid layout: 3x3 (nine events)");
    }
    
    /**
     * Get current grid layout information
     */
    public function getCurrentGridLayout(): array
    {
        return [
            'cols' => $this->gridCols,
            'rows' => $this->gridRows,
            'event_count' => $this->events->count(),
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
            'aspect_ratio' => round($this->getWidth() / $this->getHeight(), 2)
        ];
    }
    
    /**
     * Get the current corner radius for rounded flyers
     */
    public function getCornerRadius(): int
    {
        return self::CORNER_RADIUS;
    }
    
    /**
     * Set a custom corner radius (useful for testing different values)
     */
    public function setCornerRadius(int $radius): void
    {
        // Note: This would require making CORNER_RADIUS non-const or using a different approach
        // For now, we'll keep it as a constant, but this method can be used if needed
        // You could add a protected property to override the constant value
        \Log::info("Corner radius change requested to: {$radius} (current: " . self::CORNER_RADIUS . ")");
    }
    
    /**
     * Get QR code configuration information
     */
    public function getQRCodeInfo(): array
    {
        return [
            'size' => self::QR_CODE_SIZE,
            'padding' => self::QR_CODE_PADDING,
            'margin' => self::QR_CODE_MARGIN,
            'position' => 'bottom_left',
            'description' => 'QR codes are positioned in the bottom left corner of each flyer with padding'
        ];
    }
    
    /**
     * Get the current QR code size
     */
    public function getQRCodeSize(): int
    {
        return self::QR_CODE_SIZE;
    }
    
    /**
     * Get the current QR code padding
     */
    public function getQRCodePadding(): int
    {
        return self::QR_CODE_PADDING;
    }
    
    /**
     * Get the actual QR code size being used (may be smaller than constant if constrained by flyer size)
     */
    public function getActualQRCodeSize(): int
    {
        $maxQRSize = min(self::FLYER_WIDTH - (self::QR_CODE_PADDING * 2), self::FLYER_HEIGHT - (self::QR_CODE_PADDING * 2));
        return min(self::QR_CODE_SIZE, $maxQRSize);
    }
    
    /**
     * Validate that QR codes will fit within flyer boundaries
     */
    public function validateQRCodeFit(): array
    {
        $maxQRSize = min(self::FLYER_WIDTH - (self::QR_CODE_PADDING * 2), self::FLYER_HEIGHT - (self::QR_CODE_PADDING * 2));
        $actualSize = min(self::QR_CODE_SIZE, $maxQRSize);
        $fits = $actualSize >= self::QR_CODE_SIZE;
        
        return [
            'requested_size' => self::QR_CODE_SIZE,
            'max_available_size' => $maxQRSize,
            'actual_size' => $actualSize,
            'fits_within_bounds' => $fits,
            'flyer_width' => self::FLYER_WIDTH,
            'flyer_height' => self::FLYER_HEIGHT,
            'padding' => self::QR_CODE_PADDING
        ];
    }
    
    /**
     * Get QR code positioning information for a specific flyer
     */
    public function getQRCodePositionForFlyer(int $row, int $col): array
    {
        $x = self::MARGIN + ($col * (self::FLYER_WIDTH + self::MARGIN));
        $y = self::MARGIN + ($row * (self::FLYER_HEIGHT + self::MARGIN));
        
        return $this->calculateQRCodePosition($x, $y);
    }
    
    /**
     * Get detailed QR code configuration and positioning information
     */
    public function getDetailedQRCodeInfo(): array
    {
        $validation = $this->validateQRCodeFit();
        $samplePosition = $this->getQRCodePositionForFlyer(0, 0);
        
        return [
            'constants' => [
                'size' => self::QR_CODE_SIZE,
                'padding' => self::QR_CODE_PADDING,
                'margin' => self::QR_CODE_MARGIN,
            ],
            'validation' => $validation,
            'sample_position' => $samplePosition,
            'flyer_dimensions' => [
                'width' => self::FLYER_WIDTH,
                'height' => self::FLYER_HEIGHT,
            ],
            'description' => 'QR codes are positioned in the bottom left corner of each flyer with consistent padding and sizing'
        ];
    }
}