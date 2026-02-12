<?php

namespace App\Services\designs;

use App\Models\Event;
use App\Services\AbstractEventDesign;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class GridDesign extends AbstractEventDesign
{
    // Grid-specific configuration
    protected const FLYER_WIDTH = 400;

    protected const FLYER_HEIGHT = 480;

    // Text configuration for grid flyers
    protected const TITLE_FONT_SIZE = 16;

    protected const DATE_FONT_SIZE = 14;

    protected const TEXT_PADDING = 15;

    protected const TEXT_BOTTOM_MARGIN = 80; // Space for QR code

    // Date bar configuration
    protected const DATE_BAR_HEIGHT = 32;

    protected const DATE_OVERLAY_HEIGHT = 36;

    // Grid configuration - now dynamic
    protected int $gridCols;

    protected int $gridRows;

    protected function calculateDimensions(): void
    {
        // Calculate dynamic grid dimensions based on event count
        $this->calculateGridDimensions();

        // Calculate total dimensions based on dynamic grid
        $this->totalWidth = (self::FLYER_WIDTH * $this->gridCols) + (self::MARGIN * ($this->gridCols + 1));

        // Calculate flyer cell height (includes date bar if position is "above")
        $flyerCellHeight = self::FLYER_HEIGHT;
        if ($this->getOption('date_position') === 'above') {
            $flyerCellHeight += self::DATE_BAR_HEIGHT;
        }

        $this->totalHeight = ($flyerCellHeight * $this->gridRows) + (self::MARGIN * ($this->gridRows + 1));
    }

    protected function generateEventLayout(): void
    {
        $eventIndex = 0;

        for ($row = 0; $row < $this->gridRows && $eventIndex < $this->events->count(); $row++) {
            for ($col = 0; $col < $this->gridCols && $eventIndex < $this->events->count(); $col++) {
                $event = $this->events[$eventIndex];

                // For RTL languages, position flyers from right to left
                $displayCol = $this->rtl ? ($this->gridCols - 1 - $col) : $col;

                $this->generateSingleFlyer($event, $row, $displayCol);
                $eventIndex++;
            }
        }
    }

    protected function generateSingleFlyer(Event $event, int $row, int $col): void
    {
        $datePosition = $this->getOption('date_position');

        // Calculate flyer cell height (includes date bar if position is "above")
        $flyerCellHeight = self::FLYER_HEIGHT;
        if ($datePosition === 'above') {
            $flyerCellHeight += self::DATE_BAR_HEIGHT;
        }

        $x = self::MARGIN + ($col * (self::FLYER_WIDTH + self::MARGIN));
        $y = self::MARGIN + $this->headerHeight + ($row * ($flyerCellHeight + self::MARGIN));

        // If date is above, add date bar first and offset the flyer
        $flyerY = $y;
        if ($datePosition === 'above') {
            $this->addDateAbove($event, $x, $y);
            $flyerY = $y + self::DATE_BAR_HEIGHT;
        }

        // Get the event flyer image
        $this->addEventFlyerImage($event, $x, $flyerY);

        // Add date overlay on top of flyer if requested
        if ($datePosition === 'overlay') {
            $this->addDateOverlay($event, $x, $flyerY);
        }

        // Add QR code to the bottom left corner of the flyer
        $this->addEventQRCode($event, $x, $flyerY);
    }

    /**
     * Add event text overlay to the flyer
     *
     * @deprecated Text overlay removed - flyers are shown without text
     */
    protected function addEventTextOverlay(Event $event, int $x, int $y): void
    {
        // Text overlay functionality removed - just show flyers
        return;

        // Create a semi-transparent background for text readability
        $this->addTextBackground($x, $y);

        // Event title
        $title = $event->name ?? 'Untitled Event';
        $titleX = $x + self::TEXT_PADDING;
        $titleY = $y + self::FLYER_HEIGHT - self::TEXT_BOTTOM_MARGIN + 10;

        // Use multiline text if title is too long
        $maxTitleWidth = self::FLYER_WIDTH - (self::TEXT_PADDING * 2);
        $this->addMultilineText($title, $titleX, $titleY, self::TITLE_FONT_SIZE, $this->c['white'], 'bold', $maxTitleWidth);

        // Event date
        if ($event->start_date) {
            $dateText = $this->formatEventDate($event);
            $dateX = $titleX;
            $dateY = $titleY + 25;

            $this->addText($dateText, $dateX, $dateY, self::DATE_FONT_SIZE, $this->c['lightGray'], 'regular');
        }
    }

    /**
     * Add a semi-transparent background for text readability
     *
     * @deprecated Text background removed - flyers are shown without text
     */
    protected function addTextBackground(int $x, int $y): void
    {
        // Text background functionality removed
        return;

        $bgHeight = 60;
        $bgY = $y + self::FLYER_HEIGHT - self::TEXT_BOTTOM_MARGIN;

        // Create semi-transparent black background
        $bgColor = imagecolorallocatealpha($this->im, 0, 0, 0, 100);
        imagefilledrectangle($this->im, $x, $bgY, $x + self::FLYER_WIDTH, $bgY + $bgHeight, $bgColor);
    }

    /**
     * Format event date for display with localized month names
     */
    protected function formatEventDate(Event $event): string
    {
        try {
            Carbon::setLocale($this->lang);
            $startDate = Carbon::parse($event->start_date);

            if ($event->end_date) {
                $endDate = Carbon::parse($event->end_date);

                if ($startDate->isSameDay($endDate)) {
                    // Same day event
                    return $startDate->translatedFormat('M j, Y');
                } else {
                    // Multi-day event
                    return $startDate->translatedFormat('M j').' - '.$endDate->translatedFormat('M j, Y');
                }
            } else {
                // Single date event
                return $startDate->translatedFormat('M j, Y');
            }
        } catch (\Exception $e) {
            return 'Date TBD';
        }
    }

    /**
     * Add date overlay on top of the flyer image
     * Semi-transparent dark background with white text
     */
    protected function addDateOverlay(Event $event, int $x, int $y): void
    {
        $text = $this->getOverlayText($event);

        // Create semi-transparent dark background at top of flyer
        $bgColor = imagecolorallocatealpha($this->im, 0, 0, 0, 50); // ~60% opacity
        imagefilledrectangle(
            $this->im,
            $x,
            $y,
            $x + self::FLYER_WIDTH,
            $y + self::DATE_OVERLAY_HEIGHT,
            $bgColor
        );

        // Calculate text position (centered)
        // Use getSmartFontPath for consistent font selection with addText()
        $fontPath = $this->getSmartFontPath($text, 'bold');
        $fontSize = self::DATE_FONT_SIZE;
        $textWidth = $this->getTextWidth($text, $fontSize, $fontPath);
        $leftOffset = $this->getTextLeftOffset($text, $fontSize, $fontPath);
        $textX = $x + (self::FLYER_WIDTH - $textWidth) / 2 - $leftOffset;
        $textY = $y + 10; // Padding from top

        // Add white text
        $this->addText($text, (int) $textX, $textY, $fontSize, $this->c['white'], 'bold');
    }

    /**
     * Add date bar above the flyer (separate from flyer image)
     * Solid dark background with white text
     */
    protected function addDateAbove(Event $event, int $x, int $y): void
    {
        $text = $this->getOverlayText($event);

        // Create solid dark background
        $bgColor = imagecolorallocate($this->im, 51, 51, 51); // #333333
        imagefilledrectangle(
            $this->im,
            $x,
            $y,
            $x + self::FLYER_WIDTH,
            $y + self::DATE_BAR_HEIGHT,
            $bgColor
        );

        // Calculate text position (centered)
        // Use getSmartFontPath for consistent font selection with addText()
        $fontPath = $this->getSmartFontPath($text, 'bold');
        $fontSize = self::DATE_FONT_SIZE;
        $textWidth = $this->getTextWidth($text, $fontSize, $fontPath);
        $leftOffset = $this->getTextLeftOffset($text, $fontSize, $fontPath);
        $textX = $x + (self::FLYER_WIDTH - $textWidth) / 2 - $leftOffset;
        $textY = $y + 8; // Padding from top

        // Add white text
        $this->addText($text, (int) $textX, $textY, $fontSize, $this->c['white'], 'bold');
    }

    /**
     * Get the overlay text for an event, parsing variables if a template is provided
     */
    protected function getOverlayText(Event $event): string
    {
        $template = $this->getOption('overlay_text');

        // If no custom template, use default date format
        if (empty($template)) {
            return $this->formatEventDate($event);
        }

        return $this->parseOverlayText($template, $event);
    }

    /**
     * Parse overlay text template with event variables
     */
    protected function parseOverlayText(string $template, Event $event): string
    {
        try {
            Carbon::setLocale($this->lang);
            $startDate = Carbon::parse($event->start_date);
            $endDate = $event->end_date ? Carbon::parse($event->end_date) : null;

            // Determine time format based on role's 24h setting
            $timeFormat = $this->role->use_24_hour_time ? 'H:i' : 'g:i A';

            $replacements = [
                // Date variables
                '{date_mdy}' => $startDate->format('n/j'),
                '{date_dmy}' => $startDate->format('j/n'),
                '{date_full_mdy}' => $startDate->format('m/d/Y'),
                '{date_full_dmy}' => $startDate->format('d/m/Y'),
                '{day_name}' => $startDate->translatedFormat('l'),
                '{day_short}' => $startDate->translatedFormat('D'),
                '{month}' => $startDate->format('n'),
                '{month_name}' => $startDate->translatedFormat('F'),
                '{month_short}' => $startDate->translatedFormat('M'),
                '{day}' => $startDate->format('j'),
                '{year}' => $startDate->format('Y'),
                '{time}' => $startDate->format($timeFormat),
                '{end_time}' => $endDate ? $endDate->format($timeFormat) : '',

                // Event variables
                '{event_name}' => $event->translatedName() ?? $event->name ?? '',
                '{short_description}' => $event->translatedShortDescription() ?? '',
                '{description}' => strip_tags($event->translatedDescription() ?? ''),

                // Venue variables
                '{venue}' => $event->venue ? ($event->venue->translatedName() ?? '') : '',
                '{city}' => $event->venue ? ($event->venue->translatedCity() ?? '') : '',
            ];

            return str_replace(array_keys($replacements), array_values($replacements), $template);
        } catch (\Exception $e) {
            return $template;
        }
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
            'size' => self::QR_CODE_SIZE,
        ];
    }

    /**
     * Generate and add a QR code with a guaranteed consistent size.
     */
    protected function addEventQRCode(Event $event, int $x, int $y): void
    {
        try {
            // Generate the event URL for the QR code
            $eventUrl = $event->getGuestUrl($this->role->subdomain);
            if ($this->directRegistration && $event->registration_url) {
                // Insert trailing slash before query string if present
                if (str_contains($eventUrl, '?')) {
                    $eventUrl = str_replace('?', '/?', $eventUrl);
                } else {
                    $eventUrl .= '/';
                }
            }

            // Create QR code without a fixed size; the library will determine the optimal size.
            // We will resize it to a consistent dimension during the placement step.
            $qrCode = QrCode::create($eventUrl)
                ->setMargin(self::QR_CODE_MARGIN); // Keep margin for a quiet zone

            // Create PNG writer and generate QR code image data
            $writer = new PngWriter;
            $result = $writer->write($qrCode);
            $qrCodeImageData = $result->getString();

            // Create an image resource from the generated QR code data
            $qrCodeImage = imagecreatefromstring($qrCodeImageData);
            if (! $qrCodeImage) {
                return;
            }

            // Get the actual dimensions of the generated QR code
            $actualQRWidth = imagesx($qrCodeImage);
            $actualQRHeight = imagesy($qrCodeImage);

            // Calculate the standardized position and final size for the QR code
            $position = $this->calculateQRCodePosition($x, $y);
            $qrX = $position['x'];
            $qrY = $position['y'];
            $finalQRSize = $position['size']; // This is our target constant size (e.g., 80px)

            // Copy and resample the generated QR code directly onto the main canvas.
            // This ensures every QR code is rendered at the exact same final size, regardless
            // of the size generated by the library.
            imagecopyresampled(
                $this->im,          // Destination canvas
                $qrCodeImage,       // Source QR code image
                $qrX,               // Destination X coordinate
                $qrY,               // Destination Y coordinate
                0,                  // Source X coordinate
                0,                  // Source Y coordinate
                $finalQRSize,       // Destination width (our standard size)
                $finalQRSize,       // Destination height (our standard size)
                $actualQRWidth,     // Source width (original generated size)
                $actualQRHeight     // Source height (original generated size)
            );

            // Clean up the temporary QR code image resource
            imagedestroy($qrCodeImage);

        } catch (\Exception $e) {
            // Continue without QR code if there's an error
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
                        ],
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
                    public_path('storage/'.$imageUrl),
                    public_path('images/'.$imageUrl),
                    $imageUrl, // Try as absolute path
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

                if (! $sourceImage) {
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
            $newWidth = (int) ($newHeight * $sourceAspectRatio);
            $offsetX = (int) ((self::FLYER_WIDTH - $newWidth) / 2);
            $offsetY = 0;
        } else {
            // Image is taller than flyer - fit to width, crop top/bottom
            $newWidth = self::FLYER_WIDTH;
            $newHeight = (int) ($newWidth / $sourceAspectRatio);
            $offsetX = 0;
            $offsetY = (int) ((self::FLYER_HEIGHT - $newHeight) / 2);
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
        if (! $tempImage) {
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
            $sourceCropWidth = (int) ($sourceHeight * $flyerAspectRatio);
            $sourceCropX = (int) (($sourceWidth - $sourceCropWidth) / 2);

            imagecopyresampled(
                $tempImage, $sourceImage,
                0, 0, $sourceCropX, 0,
                self::FLYER_WIDTH, self::FLYER_HEIGHT,
                $sourceCropWidth, $sourceHeight
            );
        } else {
            // For tall images, crop top/bottom
            $sourceCropHeight = (int) ($sourceWidth / $flyerAspectRatio);
            $sourceCropY = (int) (($sourceHeight - $sourceCropHeight) / 2);

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
        if (! $mask) {
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
            $sourceCropWidth = (int) ($sourceHeight * $flyerAspectRatio);
            $sourceCropX = (int) (($sourceWidth - $sourceCropWidth) / 2);

            // Copy and resize the source image, cropping the sides
            imagecopyresampled(
                $this->im, $sourceImage,
                $x, $y, $sourceCropX, 0,
                self::FLYER_WIDTH, self::FLYER_HEIGHT,
                $sourceCropWidth, $sourceHeight
            );
        } else {
            // For tall images, crop top/bottom
            $sourceCropHeight = (int) ($sourceWidth / $flyerAspectRatio);
            $sourceCropY = (int) (($sourceHeight - $sourceCropHeight) / 2);

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
        if (! $tempImage) {
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

    /**
     * Calculate optimal grid dimensions based on event count
     * Creates layouts that are visually balanced and have good aspect ratios
     */
    protected function calculateGridDimensions(): void
    {
        $eventCount = $this->events->count();

        if ($eventCount === 0) {
            $this->gridCols = 1;
            $this->gridRows = 1;

            return;
        }

        if ($eventCount === 1) {
            $this->gridCols = 1;
            $this->gridRows = 1;

            return;
        }

        if ($eventCount === 2) {
            $this->gridCols = 2;
            $this->gridRows = 1;

            return;
        }

        if ($eventCount === 3) {
            $this->gridCols = 3;
            $this->gridRows = 1;

            return;
        }

        if ($eventCount === 4) {
            $this->gridCols = 2;
            $this->gridRows = 2;

            return;
        }

        if ($eventCount === 5) {
            // 3x2 layout with one empty space - looks better than 2x3
            $this->gridCols = 3;
            $this->gridRows = 2;

            return;
        }

        if ($eventCount === 6) {
            $this->gridCols = 3;
            $this->gridRows = 2;

            return;
        }

        if ($eventCount === 7) {
            // 3x3 layout with two empty spaces - still looks balanced
            $this->gridCols = 3;
            $this->gridRows = 3;

            return;
        }

        if ($eventCount === 8) {
            // 3x3 layout with one empty space
            $this->gridCols = 3;
            $this->gridRows = 3;

            return;
        }

        // For 9 events, use 3x3 grid
        $this->gridCols = 3;
        $this->gridRows = 3;
    }

    // Public getter methods for grid information
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

    public function getCurrentGridLayout(): array
    {
        return [
            'cols' => $this->gridCols,
            'rows' => $this->gridRows,
            'event_count' => $this->events->count(),
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
            'aspect_ratio' => round($this->getWidth() / $this->getHeight(), 2),
        ];
    }

    public function getActualQRCodeSize(): int
    {
        $maxQRSize = min(self::FLYER_WIDTH - (self::QR_CODE_PADDING * 2), self::FLYER_HEIGHT - (self::QR_CODE_PADDING * 2));

        return min(self::QR_CODE_SIZE, $maxQRSize);
    }

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
            'padding' => self::QR_CODE_PADDING,
        ];
    }

    public function getQRCodePositionForFlyer(int $row, int $col): array
    {
        $x = self::MARGIN + ($col * (self::FLYER_WIDTH + self::MARGIN));
        $y = self::MARGIN + ($row * (self::FLYER_HEIGHT + self::MARGIN));

        return $this->calculateQRCodePosition($x, $y);
    }

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
            'description' => 'QR codes are positioned in the bottom left corner of each flyer with consistent padding and sizing',
        ];
    }
}
