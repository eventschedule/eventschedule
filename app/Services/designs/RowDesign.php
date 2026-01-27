<?php

namespace App\Services\designs;

use App\Models\Event;
use App\Services\AbstractEventDesign;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class RowDesign extends AbstractEventDesign
{
    // Row-specific configuration
    protected const TARGET_HEIGHT = 400;

    protected const MIN_FLYER_WIDTH = 200;

    protected const MAX_FLYER_WIDTH = 600;

    protected const DEFAULT_ASPECT_RATIO = 5 / 6; // Default 5:6 aspect ratio if image fails

    // Cache for calculated flyer dimensions
    protected array $flyerDimensions = [];

    protected function calculateDimensions(): void
    {
        // Calculate dimensions for each flyer based on actual image aspect ratios
        $this->calculateFlyerDimensions();

        // Calculate total width: sum of all flyer widths + margins
        $totalFlyerWidth = 0;
        foreach ($this->flyerDimensions as $dims) {
            $totalFlyerWidth += $dims['width'];
        }

        $eventCount = $this->events->count();
        $this->totalWidth = $totalFlyerWidth + (self::MARGIN * ($eventCount + 1));
        $this->totalHeight = self::TARGET_HEIGHT + (self::MARGIN * 2);
    }

    /**
     * Calculate dimensions for each flyer based on actual image aspect ratios
     */
    protected function calculateFlyerDimensions(): void
    {
        $this->flyerDimensions = [];

        foreach ($this->events as $index => $event) {
            $imageUrl = $event->flyer_image_url;
            $aspectRatio = self::DEFAULT_ASPECT_RATIO;

            if ($imageUrl && $this->isValidImageUrl($imageUrl)) {
                $imageDims = $this->getImageDimensions($imageUrl);
                if ($imageDims) {
                    $aspectRatio = $imageDims['width'] / $imageDims['height'];
                }
            }

            // Calculate width based on target height and aspect ratio
            $calculatedWidth = (int) (self::TARGET_HEIGHT * $aspectRatio);

            // Clamp width to min/max bounds
            $finalWidth = max(self::MIN_FLYER_WIDTH, min(self::MAX_FLYER_WIDTH, $calculatedWidth));

            $this->flyerDimensions[$index] = [
                'width' => $finalWidth,
                'height' => self::TARGET_HEIGHT,
                'aspect_ratio' => $aspectRatio,
            ];
        }
    }

    /**
     * Get image dimensions from URL
     * Uses getimagesize() which fetches only image headers for efficiency
     */
    protected function getImageDimensions(string $url): ?array
    {
        try {
            // getimagesize doesn't support stream context directly for remote URLs
            // It will use the default stream context, so we just call it directly
            $info = @getimagesize($url);
            if ($info && isset($info[0]) && isset($info[1]) && $info[0] > 0 && $info[1] > 0) {
                return [
                    'width' => $info[0],
                    'height' => $info[1],
                ];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function generateEventLayout(): void
    {
        $currentX = self::MARGIN;

        foreach ($this->events as $index => $event) {
            $dims = $this->flyerDimensions[$index] ?? [
                'width' => (int) (self::TARGET_HEIGHT * self::DEFAULT_ASPECT_RATIO),
                'height' => self::TARGET_HEIGHT,
            ];

            $this->generateSingleFlyer($event, $currentX, self::MARGIN, $dims['width'], $dims['height']);

            $currentX += $dims['width'] + self::MARGIN;
        }
    }

    protected function generateSingleFlyer(Event $event, int $x, int $y, int $width, int $height): void
    {
        // Add the event flyer image
        $this->addEventFlyerImage($event, $x, $y, $width, $height);

        // Add QR code to the bottom left corner
        $this->addEventQRCode($event, $x, $y, $width, $height);
    }

    protected function addEventFlyerImage(Event $event, int $x, int $y, int $width, int $height): void
    {
        $imageUrl = $event->flyer_image_url;

        if ($imageUrl && $this->isValidImageUrl($imageUrl)) {
            $this->loadAndDisplayEventImage($imageUrl, $x, $y, $width, $height);
        } else {
            $this->createPlaceholderBackground($x, $y, $width, $height);
        }
    }

    protected function loadAndDisplayEventImage(string $imageUrl, int $x, int $y, int $width, int $height): void
    {
        try {
            $sourceImage = null;

            // Handle both local and remote images
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Remote image
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
                    $imageData = $this->fetchImageWithCurl($imageUrl);
                    if ($imageData === false) {
                        $this->createPlaceholderBackground($x, $y, $width, $height);

                        return;
                    }
                }

                $sourceImage = imagecreatefromstring($imageData);
            } else {
                // Local image
                $possiblePaths = [
                    public_path($imageUrl),
                    public_path('storage/'.$imageUrl),
                    public_path('images/'.$imageUrl),
                    $imageUrl,
                ];

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
                    $this->createPlaceholderBackground($x, $y, $width, $height);

                    return;
                }
            }

            if ($sourceImage === false) {
                $this->createPlaceholderBackground($x, $y, $width, $height);

                return;
            }

            // Resize and display the image
            $this->resizeAndDisplayImage($sourceImage, $x, $y, $width, $height);

            imagedestroy($sourceImage);

        } catch (\Exception $e) {
            $this->createPlaceholderBackground($x, $y, $width, $height);
        }
    }

    protected function resizeAndDisplayImage($sourceImage, int $x, int $y, int $width, int $height): void
    {
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        // Create temp image for the flyer
        $tempImage = imagecreatetruecolor($width, $height);
        if (! $tempImage) {
            $this->createPlaceholderBackground($x, $y, $width, $height);

            return;
        }

        // Enable alpha blending
        imagealphablending($tempImage, false);
        imagesavealpha($tempImage, true);

        // Create transparent background
        $transparent = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
        imagefill($tempImage, 0, 0, $transparent);

        // Calculate how to fit the image - cover the area while maintaining aspect ratio
        $sourceAspectRatio = $sourceWidth / $sourceHeight;
        $targetAspectRatio = $width / $height;

        if ($sourceAspectRatio > $targetAspectRatio) {
            // Source is wider - crop left/right
            $sourceCropWidth = (int) ($sourceHeight * $targetAspectRatio);
            $sourceCropX = (int) (($sourceWidth - $sourceCropWidth) / 2);

            imagecopyresampled(
                $tempImage, $sourceImage,
                0, 0, $sourceCropX, 0,
                $width, $height,
                $sourceCropWidth, $sourceHeight
            );
        } else {
            // Source is taller - crop top/bottom
            $sourceCropHeight = (int) ($sourceWidth / $targetAspectRatio);
            $sourceCropY = (int) (($sourceHeight - $sourceCropHeight) / 2);

            imagecopyresampled(
                $tempImage, $sourceImage,
                0, 0, 0, $sourceCropY,
                $width, $height,
                $sourceWidth, $sourceCropHeight
            );
        }

        // Apply rounded corners
        $this->applyRoundedCorners($tempImage, $width, $height);

        // Copy to main canvas
        imagecopy($this->im, $tempImage, $x, $y, 0, 0, $width, $height);

        imagedestroy($tempImage);
    }

    /**
     * Apply rounded corners to an image using alpha blending
     */
    protected function applyRoundedCorners($image, int $width, int $height): void
    {
        $cornerRadius = $this->getCornerRadius();

        $mask = imagecreatetruecolor($width, $height);
        if (! $mask) {
            return;
        }

        imagealphablending($mask, false);
        imagesavealpha($mask, true);

        $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $transparent);

        $white = imagecolorallocate($mask, 255, 255, 255);

        // Fill main rectangle
        imagefilledrectangle($mask, $cornerRadius, 0, $width - $cornerRadius - 1, $height - 1, $white);
        imagefilledrectangle($mask, 0, $cornerRadius, $width - 1, $height - $cornerRadius - 1, $white);

        // Fill corners
        imagefilledellipse($mask, $cornerRadius, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $width - $cornerRadius - 1, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $cornerRadius, $height - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $width - $cornerRadius - 1, $height - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, $white);

        $this->applyMaskToImage($image, $mask);

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

                $maskR = ($maskColor >> 16) & 0xFF;
                $maskG = ($maskColor >> 8) & 0xFF;
                $maskB = $maskColor & 0xFF;

                if ($maskR < 128 && $maskG < 128 && $maskB < 128) {
                    $r = ($imageColor >> 16) & 0xFF;
                    $g = ($imageColor >> 8) & 0xFF;
                    $b = $imageColor & 0xFF;

                    $newColor = imagecolorallocatealpha($image, $r, $g, $b, 127);
                    imagesetpixel($image, $x, $y, $newColor);
                }
            }
        }
    }

    protected function createPlaceholderBackground(int $x, int $y, int $width, int $height): void
    {
        $tempImage = imagecreatetruecolor($width, $height);
        if (! $tempImage) {
            return;
        }

        imagealphablending($tempImage, false);
        imagesavealpha($tempImage, true);

        $transparent = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
        imagefill($tempImage, 0, 0, $transparent);

        $bgColor = $this->c['white'];
        $borderColor = $this->c['accent'];

        imagefilledrectangle($tempImage, 0, 0, $width, $height, $bgColor);
        imagerectangle($tempImage, 0, 0, $width - 1, $height - 1, $borderColor);

        $this->applyRoundedCorners($tempImage, $width, $height);

        imagecopy($this->im, $tempImage, $x, $y, 0, 0, $width, $height);

        imagedestroy($tempImage);
    }

    /**
     * Generate and add a QR code for the event
     */
    protected function addEventQRCode(Event $event, int $x, int $y, int $width, int $height): void
    {
        try {
            $eventUrl = $event->getGuestUrl($this->role->subdomain);
            if ($this->directRegistration && $event->registration_url) {
                if (str_contains($eventUrl, '?')) {
                    $eventUrl = str_replace('?', '/?', $eventUrl);
                } else {
                    $eventUrl .= '/';
                }
            }

            $qrCode = QrCode::create($eventUrl)
                ->setMargin(self::QR_CODE_MARGIN);

            $writer = new PngWriter;
            $result = $writer->write($qrCode);
            $qrCodeImageData = $result->getString();

            $qrCodeImage = imagecreatefromstring($qrCodeImageData);
            if (! $qrCodeImage) {
                return;
            }

            $actualQRWidth = imagesx($qrCodeImage);
            $actualQRHeight = imagesy($qrCodeImage);

            // Position at bottom left
            $qrX = $x + self::QR_CODE_PADDING;
            $qrY = $y + $height - self::QR_CODE_SIZE - self::QR_CODE_PADDING;

            imagecopyresampled(
                $this->im,
                $qrCodeImage,
                $qrX,
                $qrY,
                0,
                0,
                self::QR_CODE_SIZE,
                self::QR_CODE_SIZE,
                $actualQRWidth,
                $actualQRHeight
            );

            imagedestroy($qrCodeImage);

        } catch (\Exception $e) {
            // Continue without QR code if there's an error
        }
    }

    // Public getter methods for row information
    public function getRowInfo(): array
    {
        return [
            'target_height' => self::TARGET_HEIGHT,
            'min_flyer_width' => self::MIN_FLYER_WIDTH,
            'max_flyer_width' => self::MAX_FLYER_WIDTH,
            'margin' => self::MARGIN,
            'corner_radius' => self::CORNER_RADIUS,
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
            'event_count' => $this->events->count(),
            'flyer_dimensions' => $this->flyerDimensions,
        ];
    }

    public function getCurrentRowLayout(): array
    {
        return [
            'event_count' => $this->events->count(),
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
            'aspect_ratio' => $this->getHeight() > 0 ? round($this->getWidth() / $this->getHeight(), 2) : 0,
            'flyer_dimensions' => $this->flyerDimensions,
        ];
    }
}
