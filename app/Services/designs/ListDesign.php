<?php

namespace App\Services\designs;

use App\Services\AbstractEventDesign;
use App\Models\Event;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Carbon\Carbon;

class ListDesign extends AbstractEventDesign
{
    // List-specific configuration
    protected const FLYER_WIDTH = 200;
    protected const FLYER_HEIGHT = 120;
    protected const ITEM_HEIGHT = 140;
    protected const ITEM_PADDING = 20;
    protected const TEXT_LEFT_MARGIN = 240; // Space for flyer + padding
    
    // Font configuration
    protected const TITLE_FONT_SIZE = 5;
    protected const VENUE_FONT_SIZE = 4;
    protected const DATE_FONT_SIZE = 4;
    protected const LINE_HEIGHT = 25;
    
    protected function calculateDimensions(): void
    {
        $eventCount = $this->events->count();
        
        if ($eventCount === 0) {
            $this->totalWidth = 800;
            $this->totalHeight = 200;
            return;
        }
        
        // Calculate width: flyer width + text area + margins
        $this->totalWidth = self::TEXT_LEFT_MARGIN + 500 + (self::MARGIN * 2);
        
        // Calculate height: each item height + margins
        $this->totalHeight = ($eventCount * self::ITEM_HEIGHT) + (self::MARGIN * 2);
    }
    
    protected function generateEventLayout(): void
    {
        $y = self::MARGIN;
        
        foreach ($this->events as $event) {
            $this->generateEventListItem($event, $y);
            $y += self::ITEM_HEIGHT;
        }
    }
    
    protected function generateEventListItem(Event $event, int $y): void
    {
        $x = self::MARGIN;
        
        // Add the event flyer image on the left
        $this->addEventFlyerImage($event, $x, $y);
        
        // Add event details on the right
        $this->addEventDetails($event, $x + self::TEXT_LEFT_MARGIN, $y);
        
        // Add QR code to the right side
        $this->addEventQRCode($event, $x + $this->totalWidth - self::QR_CODE_SIZE - self::MARGIN, $y + self::ITEM_HEIGHT - self::QR_CODE_SIZE - 10);
        
        // Add separator line between items (except for the last one)
        if ($y + self::ITEM_HEIGHT < $this->totalHeight - self::MARGIN) {
            $this->addSeparatorLine($y + self::ITEM_HEIGHT);
        }
    }
    
    protected function addEventFlyerImage(Event $event, int $x, int $y): void
    {
        $imageUrl = $event->flyer_image_url;
                
        if ($imageUrl && $this->isValidImageUrl($imageUrl)) {
            // Try to load and display the event image
            $this->loadAndDisplayEventImage($imageUrl, $x, $y);
        } else {
            // Fallback to a placeholder background if no image
            $this->createPlaceholderBackground($x, $y);
        }
    }
    
    protected function loadAndDisplayEventImage(string $imageUrl, int $x, int $y): void
    {
        try {
            $sourceImage = null;
            
            // Handle both local and remote images
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
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
                
                $imageData = file_get_contents($imageUrl, false, $context);
                if ($imageData === false) {
                    // Fallback to cURL if file_get_contents fails
                    $imageData = $this->fetchImageWithCurl($imageUrl);
                    
                    if ($imageData === false) {
                        $this->createPlaceholderBackground($x, $y);
                        return;
                    }
                }

                $sourceImage = imagecreatefromstring($imageData);
            } else {
                // Local image - try different path variations
                $possiblePaths = [
                    public_path($imageUrl),
                    public_path('storage/' . $imageUrl),
                    public_path('images/' . $imageUrl),
                    $imageUrl // Try as absolute path
                ];
                
                $sourceImage = null;
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $imageData = file_get_contents($path);
                        $sourceImage = imagecreatefromstring($imageData);
                        
                        if ($sourceImage) {
                            break;
                        }
                    }
                }
                
                if (!$sourceImage) {
                    $this->createPlaceholderBackground($x, $y);
                    return;
                }
            }
            
            if ($sourceImage === false) {
                $this->createPlaceholderBackground($x, $y);
                return;
            }
            
            // Resize and display the image
            $this->resizeAndDisplayImage($sourceImage, $x, $y);
            
            // Clean up
            imagedestroy($sourceImage);

        } catch (\Exception $e) {
            // Fallback to placeholder on any error
            $this->createPlaceholderBackground($x, $y);
        }
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
        
        // Create a mask for rounded corners
        $mask = imagecreatetruecolor($width, $height);
        if (!$mask) {
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
        
        // Apply the mask to the image
        $this->applyMaskToImage($image, $mask);
        
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
        
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
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
                }
            }
        }
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
        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 6; $j++) {
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
        for ($i = 0; $i < 4; $i++) {
            for ($j = 0; $j < 6; $j++) {
                $dotX = 30 + ($i * 45);
                $dotY = 60 + ($j * 45);
                if ($dotX < self::FLYER_WIDTH - 30 && $dotY < self::FLYER_HEIGHT - 30) {
                    imagefilledellipse($image, $dotX, $dotY, 3, 3, $dotColor);
                }
            }
        }
    }
    
    protected function addEventDetails(Event $event, int $x, int $y): void
    {
        $currentY = $y + 20;
        
        // Event title
        $title = $event->name ?? 'Untitled Event';
        $this->addText($title, $x, $currentY, self::TITLE_FONT_SIZE, $this->c['font']);
        $currentY += self::LINE_HEIGHT;
        
        // Venue
        if ($event->venue) {
            $venue = $event->venue;
            $this->addText($venue, $x, $currentY, self::VENUE_FONT_SIZE, $this->c['gray']);
            $currentY += self::LINE_HEIGHT;
        }
        
        // Date and time
        if ($event->start_date) {
            $dateTime = $this->formatEventDateTime($event);
            $this->addText($dateTime, $x, $currentY, self::DATE_FONT_SIZE, $this->c['darkGray']);
        }
    }
    
    protected function addText(string $text, int $x, int $y, int $fontSize, int $color): void
    {
        // Simple text rendering using GD's built-in fonts
        imagestring($this->im, $fontSize, $x, $y, $text, $color);
    }
    
    protected function formatEventDateTime(Event $event): string
    {
        try {
            $startDate = Carbon::parse($event->start_date);
            
            if ($event->end_date) {
                $endDate = Carbon::parse($event->end_date);
                
                if ($startDate->isSameDay($endDate)) {
                    // Same day event
                    return $startDate->format('M j, Y') . ' at ' . $startDate->format('g:i A') . ' - ' . $endDate->format('g:i A');
                } else {
                    // Multi-day event
                    return $startDate->format('M j, Y') . ' - ' . $endDate->format('M j, Y');
                }
            } else {
                // Single date event
                return $startDate->format('M j, Y') . ' at ' . $startDate->format('g:i A');
            }
        } catch (\Exception $e) {
            return 'Date TBD';
        }
    }
    
    protected function addSeparatorLine(int $y): void
    {
        $lineColor = $this->c['lightGray'];
        $lineY = $y - 1;
        
        imageline($this->im, self::MARGIN, $lineY, $this->totalWidth - self::MARGIN, $lineY, $lineColor);
    }
    
    /**
     * Generate and add a QR code for the event
     */
    protected function addEventQRCode(Event $event, int $x, int $y): void
    {
        try {
            // Generate the event URL for the QR code
            $eventUrl = $event->registration_url ?: $event->getGuestUrl($this->role->subdomain);

            // Create QR code
            $qrCode = QrCode::create($eventUrl)
                ->setMargin(self::QR_CODE_MARGIN);

            // Create PNG writer and generate QR code image data
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrCodeImageData = $result->getString();

            // Create an image resource from the generated QR code data
            $qrCodeImage = imagecreatefromstring($qrCodeImageData);
            if (!$qrCodeImage) {
                return;
            }

            // Get the actual dimensions of the generated QR code
            $actualQRWidth = imagesx($qrCodeImage);
            $actualQRHeight = imagesy($qrCodeImage);

            // Copy and resize the QR code to the specified position
            imagecopyresampled(
                $this->im,          // Destination canvas
                $qrCodeImage,       // Source QR code image
                $x,                 // Destination X coordinate
                $y,                 // Destination Y coordinate
                0,                  // Source X coordinate
                0,                  // Source Y coordinate
                self::QR_CODE_SIZE, // Destination width
                self::QR_CODE_SIZE, // Destination height
                $actualQRWidth,     // Source width
                $actualQRHeight     // Source height
            );

            // Clean up the temporary QR code image resource
            imagedestroy($qrCodeImage);

        } catch (\Exception $e) {
            // Continue without QR code if there's an error
        }
    }
    
    // Public getter methods for list information
    public function getListInfo(): array
    {
        return [
            'flyer_width' => self::FLYER_WIDTH,
            'flyer_height' => self::FLYER_HEIGHT,
            'item_height' => self::ITEM_HEIGHT,
            'item_padding' => self::ITEM_PADDING,
            'text_left_margin' => self::TEXT_LEFT_MARGIN,
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
            'event_count' => $this->events->count(),
            'margin' => self::MARGIN,
            'corner_radius' => self::CORNER_RADIUS,
        ];
    }
    
    public function getCurrentListLayout(): array
    {
        return [
            'event_count' => $this->events->count(),
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
            'aspect_ratio' => round($this->getWidth() / $this->getHeight(), 2),
            'item_height' => self::ITEM_HEIGHT,
            'flyer_dimensions' => [
                'width' => self::FLYER_WIDTH,
                'height' => self::FLYER_HEIGHT,
            ]
        ];
    }
}
