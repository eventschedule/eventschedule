<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
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

    // Header image configuration
    protected const HEADER_PADDING = 20;

    protected const MAX_HEADER_HEIGHT = 200;

    protected int $headerHeight = 0;

    protected $headerImageResource = null;

    // Language and layout
    protected string $lang;

    protected bool $rtl;

    // Font configuration
    protected array $fonts = [];

    protected const DEFAULT_FONT_SIZE = 16;

    protected const DEFAULT_LINE_HEIGHT = 1.4;

    // Design-specific dimensions
    protected int $totalWidth;

    protected int $totalHeight;

    // Direct registration option
    protected bool $directRegistration;

    // Options array for customization
    protected array $options = [];

    public function __construct(Role $role, Collection $events, bool $directRegistration = false, array $options = [])
    {
        // Check if GD extension is available
        if (! extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required to generate event graphics');
        }

        $this->role = $role;
        $this->events = $events->values();
        $this->directRegistration = $directRegistration;
        $this->options = $options;

        // Language code only affects RTL layout direction, not font selection
        // Fonts are automatically selected based on text content
        $this->lang = in_array(strtolower($role->language_code), ['ar', 'de', 'en', 'es', 'fr', 'he', 'it', 'nl', 'pt'], true)
            ? strtolower($role->language_code) : 'en';

        // RTL layout is determined by role language (Hebrew/Arabic = RTL)
        $this->rtl = in_array($this->lang, ['ar', 'he'], true);

        // Initialize fonts
        $this->initializeFonts();

        // Calculate dimensions based on design type
        $this->calculateDimensions();

        // Prepare header image (uses totalWidth, adds to totalHeight)
        $this->prepareHeaderImage();

        // Create image
        $this->im = imagecreatetruecolor($this->totalWidth, $this->totalHeight);
        if (! $this->im) {
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
        if ($this->headerImageResource) {
            imagedestroy($this->headerImageResource);
        }
    }

    public function generate(): string
    {
        // Apply background based on role's style
        $this->applyBackground();

        // Render header image after background
        $this->renderHeaderImage();

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

    /**
     * Get an option value with optional default
     */
    protected function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }

    /**
     * Prepare header image - fetch and measure before canvas creation
     * This adds headerHeight to totalHeight after calculateDimensions()
     */
    protected function prepareHeaderImage(): void
    {
        $headerUrl = $this->getOption('header_image_url');
        if (empty($headerUrl)) {
            return;
        }

        // Build full URL/path for the image
        if (! filter_var($headerUrl, FILTER_VALIDATE_URL)) {
            if (config('filesystems.default') == 'local') {
                $headerUrl = url('/storage/'.$headerUrl);
            } else {
                $headerUrl = \Storage::url($headerUrl);
            }
        }

        // Fetch and measure
        $imageData = $this->fetchImageWithCurl($headerUrl);
        if (! $imageData) {
            return;
        }

        $this->headerImageResource = imagecreatefromstring($imageData);
        if (! $this->headerImageResource) {
            return;
        }

        // Calculate rendered height (full width, maintain aspect ratio, cap at max)
        $srcWidth = imagesx($this->headerImageResource);
        $srcHeight = imagesy($this->headerImageResource);

        $targetWidth = $this->totalWidth - (self::MARGIN * 2);
        $scaleFactor = $targetWidth / $srcWidth;
        $targetHeight = (int) ($srcHeight * $scaleFactor);

        // Store the header height (including padding below header)
        $this->headerHeight = min($targetHeight, self::MAX_HEADER_HEIGHT) + self::HEADER_PADDING;

        // Add header height to total canvas height
        $this->totalHeight += $this->headerHeight;
    }

    /**
     * Render header image onto the canvas
     */
    protected function renderHeaderImage(): void
    {
        if (! $this->headerImageResource) {
            return;
        }

        $srcWidth = imagesx($this->headerImageResource);
        $srcHeight = imagesy($this->headerImageResource);

        $targetWidth = $this->totalWidth - (self::MARGIN * 2);
        $scaleFactor = $targetWidth / $srcWidth;
        $targetHeight = min((int) ($srcHeight * $scaleFactor), self::MAX_HEADER_HEIGHT);

        // Position at top with margin
        $x = self::MARGIN;
        $y = self::MARGIN;

        imagecopyresampled(
            $this->im, $this->headerImageResource,
            $x, $y, 0, 0,
            $targetWidth, $targetHeight,
            $srcWidth, $srcHeight
        );

        imagedestroy($this->headerImageResource);
        $this->headerImageResource = null;
    }

    /**
     * Initialize TTF fonts for different languages
     */
    protected function initializeFonts(): void
    {
        // Use absolute paths to avoid Laravel helper function issues
        $possiblePaths = [
            __DIR__.'/../../resources/fonts',
            dirname(__DIR__, 2).'/resources/fonts',
            dirname(dirname(__DIR__)).'/resources/fonts',
        ];

        $fontsPath = null;
        foreach ($possiblePaths as $path) {
            if (is_dir($path) && file_exists($path.'/NotoSans-Regular.ttf')) {
                $fontsPath = $path;
                break;
            }
        }

        if (! $fontsPath) {
            // Final fallback: use current directory relative path
            $fontsPath = __DIR__.'/../../resources/fonts';
        }

        // Default fonts for English and other languages
        $this->fonts['en'] = [
            'regular' => $fontsPath.'/NotoSans-Regular.ttf',
            'bold' => $fontsPath.'/NotoSans-Bold.ttf',
        ];

        // Hebrew fonts
        $this->fonts['he'] = [
            'regular' => $fontsPath.'/NotoSansHebrew-Regular.ttf',
            'bold' => $fontsPath.'/NotoSansHebrew-Bold.ttf',
        ];

        // Arabic fonts
        $this->fonts['ar'] = [
            'regular' => $fontsPath.'/NotoSansArabic-Regular.ttf',
            'bold' => $fontsPath.'/NotoSansArabic-Bold.ttf',
        ];

        // Verify font files exist and are readable
        foreach ($this->fonts as $lang => $fontSet) {
            foreach ($fontSet as $weight => $path) {
                if (! file_exists($path) || ! is_readable($path)) {
                    // Fallback to default fonts if specific language fonts don't exist
                    if (isset($this->fonts['en'][$weight])) {
                        $this->fonts[$lang][$weight] = $this->fonts['en']['regular'];
                    }
                }

                // Debug: Log font status
                error_log("Font {$lang} {$weight}: {$path} - exists: ".(file_exists($path) ? 'yes' : 'no').', readable: '.(is_readable($path) ? 'yes' : 'no'));
            }
        }
    }

    /**
     * Get the appropriate font path for the current language and weight
     *
     * @deprecated Use getFontPathForLanguage() instead - this method is kept for backward compatibility
     */
    protected function getFontPath(string $weight = 'regular'): string
    {
        // For backward compatibility, use English fonts as default
        return $this->fonts['en'][$weight] ?? $this->fonts['en']['regular'];
    }

    /**
     * Smart font selection that handles mixed content automatically
     * For mixed content with apostrophes, we need to use the mixed content handler
     * rather than switching the entire text to English fonts.
     */
    protected function getSmartFontPath(string $text, string $weight = 'regular'): string
    {
        // If text contains only English characters, use English font
        if (! $this->containsRTLCharacters($text)) {
            return $this->fonts['en'][$weight] ?? $this->fonts['en']['regular'];
        }

        // If text contains only RTL characters, use role language font
        if (! $this->containsLTRCharacters($text)) {
            return $this->getFontPath($weight);
        }

        // For mixed content (including text with apostrophes), prefer the role language font as base
        // The mixed content handler will use appropriate fonts for each segment
        return $this->getFontPath($weight);
    }

    /**
     * Check if text contains apostrophes or other punctuation that should use English font
     * This is important because Hebrew and Arabic fonts often lack proper apostrophe characters,
     * so we switch to English fonts for better rendering of these characters.
     */
    protected function containsApostrophesOrPunctuation(string $text): bool
    {
        // Check for common apostrophes, quotes, and punctuation that are better rendered in English fonts
        return preg_match('/[\'`´"\x{2032}\x{2033}\x{2034}\x{2035}\x{2036}\x{2037}\x{2039}\x{203A}]/u', $text);
    }

    /**
     * Get optimal Y position for apostrophes to align with Hebrew text
     */
    protected function getApostropheYPosition(int $baseY, int $fontSize, string $fontPath): int
    {
        if (! function_exists('imagettfbbox') || ! file_exists($fontPath)) {
            // For GD fonts, use a small offset
            return $baseY + 2;
        }

        // Get the bounding box for the apostrophe to determine its height
        $bbox = imagettfbbox($fontSize, 0, $fontPath, "'");
        if ($bbox === false) {
            return $baseY + 2;
        }

        // Calculate the height of the apostrophe
        $apostropheHeight = $bbox[1] - $bbox[7];

        // Position the apostrophe to align with the x-height of Hebrew text
        // Hebrew fonts typically have a larger x-height, so we need to adjust
        $adjustedY = $baseY + (int) ($fontSize * 0.15); // 15% of font size downward

        return $adjustedY;
    }

    /**
     * Check if text contains LTR characters (English, etc.)
     */
    protected function containsLTRCharacters(string $text): bool
    {
        return preg_match('/[a-zA-Z]/', $text);
    }

    /**
     * Replace problematic characters that don't render well in certain fonts
     */
    protected function sanitizeText(string $text): string
    {
        // Replace em dashes (—) with regular dashes (-)
        $text = str_replace(['—', '–', '−'], '-', $text);

        return $text;
    }

    /**
     * Add text with TTF font support and RTL handling
     */
    protected function addText(string $text, int $x, int $y, int $fontSize, int $color, string $weight = 'regular', ?bool $isRtl = null): void
    {
        if (empty($text)) {
            return;
        }

        // Sanitize text to replace problematic characters
        $text = $this->sanitizeText($text);

        // Determine RTL based on actual text content first, not role language
        // This ensures English text like "Jan 28, 2026" is never treated as RTL
        // even when the role is Hebrew/Arabic
        if ($isRtl === null) {
            $isRtl = $this->isRTLCharacter($text[0] ?? '');
        }

        // Check if text contains mixed content (Hebrew/Arabic + English) or apostrophes
        if ($this->containsMixedContent($text) || $this->containsApostrophesOrPunctuation($text)) {
            $this->addMixedContentText($text, $x, $y, $fontSize, $color, $weight, $isRtl);

            return;
        }

        // Use smart font selection for single-language text
        $fontPath = $this->getSmartFontPath($text, $weight);

        // If TTF fonts are not available, fall back to GD built-in fonts
        if (! file_exists($fontPath) || ! is_readable($fontPath) || ! function_exists('imagettftext')) {
            $this->addTextWithGDFonts($text, $x, $y, $fontSize, $color, $isRtl);

            return;
        }

        // Use TTF fonts for better quality and language support
        $this->addTextWithTTF($text, $x, $y, $fontSize, $color, $fontPath, $isRtl);
    }

    /**
     * Add text using TTF fonts with proper RTL support
     */
    protected function addTextWithTTF(string $text, int $x, int $y, int $fontSize, int $color, string $fontPath, bool $isRtl): void
    {
        // Get text dimensions
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        if ($bbox === false) {
            // Fallback to GD fonts if TTF fails
            $this->addTextWithGDFonts($text, $x, $y, $fontSize, $color, $isRtl);

            return;
        }

        $textWidth = $bbox[4] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[7];

        // Adjust position for RTL text
        if ($isRtl) {
            $x = $x - $textWidth;
        }

        // Add text with TTF font
        $result = imagettftext($this->im, $fontSize, 0, $x, $y + $textHeight, $color, $fontPath, $text);

        if ($result === false) {
            // Fallback to GD fonts if TTF rendering fails
            $this->addTextWithGDFonts($text, $x, $y, $fontSize, $color, $isRtl);

            return;
        }
    }

    /**
     * Add text using GD built-in fonts (fallback)
     */
    protected function addTextWithGDFonts(string $text, int $x, int $y, int $fontSize, int $color, bool $isRtl): void
    {
        // Convert font size to GD font constant
        $gdFont = $this->getGDFontConstant($fontSize);

        // Get text dimensions
        $textWidth = imagefontwidth($gdFont) * strlen($text);
        $textHeight = imagefontheight($gdFont);

        // Adjust position for RTL text
        if ($isRtl) {
            $x = $x - $textWidth;
        }

        // Add text with GD font
        imagestring($this->im, $gdFont, $x, $y, $text, $color);
    }

    /**
     * Convert font size to GD font constant
     */
    protected function getGDFontConstant(int $fontSize): int
    {
        // Map font sizes to GD font constants
        if ($fontSize <= 8) {
            return 1;
        }
        if ($fontSize <= 12) {
            return 2;
        }
        if ($fontSize <= 16) {
            return 3;
        }
        if ($fontSize <= 20) {
            return 4;
        }

        return 5; // Largest GD font
    }

    /**
     * Add multiline text with proper line breaks and RTL support
     */
    protected function addMultilineText(string $text, int $x, int $y, int $fontSize, int $color, string $weight = 'regular', int $maxWidth = 0, ?float $lineHeight = null): void
    {
        if (empty($text)) {
            return;
        }

        // Sanitize text to replace problematic characters
        $text = $this->sanitizeText($text);

        $lineHeight = $lineHeight ?? self::DEFAULT_LINE_HEIGHT;
        $currentY = $y;

        // Split text into lines
        $lines = $this->splitTextIntoLines($text, $maxWidth, $fontSize, $weight);

        foreach ($lines as $line) {
            $this->addText($line, $x, $currentY, $fontSize, $color, $weight);
            $currentY += $fontSize * $lineHeight;
        }
    }

    /**
     * Split text into lines based on width constraints
     */
    protected function splitTextIntoLines(string $text, int $maxWidth, int $fontSize, string $weight): array
    {
        if ($maxWidth <= 0) {
            return [$text];
        }

        $fontPath = $this->getFontPath($weight);
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine.($currentLine ? ' ' : '').$word;

            if ($this->getTextWidth($testLine, $fontSize, $fontPath) <= $maxWidth) {
                $currentLine = $testLine;
            } else {
                if ($currentLine) {
                    $lines[] = trim($currentLine);
                }
                $currentLine = $word;
            }
        }

        if ($currentLine) {
            $lines[] = trim($currentLine);
        }

        return $lines;
    }

    /**
     * Get text width for a given font and size
     */
    protected function getTextWidth(string $text, int $fontSize, string $fontPath): int
    {
        if (function_exists('imagettfbbox') && file_exists($fontPath)) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            if ($bbox !== false) {
                return $bbox[4] - $bbox[0];
            }
        }

        // Fallback to GD fonts
        $gdFont = $this->getGDFontConstant($fontSize);

        return imagefontwidth($gdFont) * strlen($text);
    }

    /**
     * Get the left offset of text bounding box (bbox[0])
     * This offset needs to be subtracted when centering text
     */
    protected function getTextLeftOffset(string $text, int $fontSize, string $fontPath): int
    {
        if (function_exists('imagettfbbox') && file_exists($fontPath)) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            if ($bbox !== false) {
                return $bbox[0];
            }
        }

        return 0;
    }

    /**
     * Get text height for a given font and size
     */
    protected function getTextHeight(string $text, int $fontSize, string $fontPath): int
    {
        if (function_exists('imagettfbbox') && file_exists($fontPath)) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            if ($bbox !== false) {
                return $bbox[1] - $bbox[7];
            }
        }

        // Fallback to GD fonts
        $gdFont = $this->getGDFontConstant($fontSize);

        return imagefontheight($gdFont);
    }

    /**
     * Check if TTF fonts are available
     */
    protected function isTTFAvailable(): bool
    {
        return function_exists('imagettfbbox') && function_exists('imagettftext');
    }

    /**
     * Test if a specific font file is working correctly
     */
    protected function testFontFile(string $fontPath): bool
    {
        if (! file_exists($fontPath) || ! is_readable($fontPath)) {
            return false;
        }

        if (! function_exists('imagettfbbox')) {
            return false;
        }

        // Test with a simple character
        $bbox = imagettfbbox(12, 0, $fontPath, 'A');

        return $bbox !== false;
    }

    /**
     * Get font debugging information
     */
    public function getFontDebugInfo(): array
    {
        $info = [
            'language' => $this->lang,
            'rtl' => $this->rtl,
            'ttf_available' => $this->isTTFAvailable(),
            'fonts' => [],
        ];

        foreach ($this->fonts as $lang => $fontSet) {
            $info['fonts'][$lang] = [];
            foreach ($fontSet as $weight => $path) {
                $info['fonts'][$lang][$weight] = [
                    'path' => $path,
                    'exists' => file_exists($path),
                    'readable' => is_readable($path),
                    'working' => $this->testFontFile($path),
                ];
            }
        }

        return $info;
    }

    /**
     * Debug method to show how text would be segmented
     */
    public function debugTextSegmentation(string $text): array
    {
        $segments = $this->splitTextByLanguageWithApostrophes($text);
        $debug = [
            'original_text' => $text,
            'segments' => [],
            'total_segments' => count($segments),
        ];

        foreach ($segments as $i => $segment) {
            $debug['segments'][] = [
                'index' => $i,
                'text' => $segment['text'],
                'language' => $segment['language'],
                'rtl' => $segment['rtl'],
                'font_path' => $this->getFontPathForLanguage($segment['language'], 'regular'),
                'contains_apostrophes' => $this->containsApostrophesOrPunctuation($segment['text']),
            ];
        }

        return $debug;
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
            $r = (int) ((1 - $ratio) * imagecolorsforindex($this->im, $color1Resource)['red'] + $ratio * imagecolorsforindex($this->im, $color2Resource)['red']);
            $g = (int) ((1 - $ratio) * imagecolorsforindex($this->im, $color1Resource)['green'] + $ratio * imagecolorsforindex($this->im, $color2Resource)['green']);
            $b = (int) ((1 - $ratio) * imagecolorsforindex($this->im, $color1Resource)['blue'] + $ratio * imagecolorsforindex($this->im, $color2Resource)['blue']);
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
            $imagePath = public_path('images/backgrounds/'.$this->role->background_image.'.png');
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
        $newWidth = (int) ($bgWidth * $scale);
        $newHeight = (int) ($bgHeight * $scale);

        // Center the image (these can be negative for cropping)
        $x = (int) (($targetWidth - $newWidth) / 2);
        $y = (int) (($targetHeight - $newHeight) / 2);

        // Apply 50% alpha transparency to the background image
        // Create temp image at CANVAS SIZE (not scaled background size) to avoid memory issues
        $tempImage = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($tempImage) {
            // Enable alpha blending
            imagealphablending($tempImage, false);
            imagesavealpha($tempImage, true);

            // Create a transparent background
            $transparent = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
            imagefill($tempImage, 0, 0, $transparent);

            // Calculate source region to sample from the background image
            // We need to figure out which portion of the source maps to the visible canvas
            if ($x < 0) {
                // Background is wider than canvas - crop horizontally
                $srcX = (int) ((-$x) / $scale);
                $srcWidth = (int) ($targetWidth / $scale);
                $dstX = 0;
                $dstWidth = $targetWidth;
            } else {
                // Background fits within canvas width
                $srcX = 0;
                $srcWidth = $bgWidth;
                $dstX = $x;
                $dstWidth = $newWidth;
            }

            if ($y < 0) {
                // Background is taller than canvas - crop vertically
                $srcY = (int) ((-$y) / $scale);
                $srcHeight = (int) ($targetHeight / $scale);
                $dstY = 0;
                $dstHeight = $targetHeight;
            } else {
                // Background fits within canvas height
                $srcY = 0;
                $srcHeight = $bgHeight;
                $dstY = $y;
                $dstHeight = $newHeight;
            }

            // Resample directly to temp image at canvas size
            imagecopyresampled(
                $tempImage, $bgImage,
                $dstX, $dstY,
                $srcX, $srcY,
                $dstWidth, $dstHeight,
                $srcWidth, $srcHeight
            );

            // Apply 50% transparency by blending with the main image
            imagecopymerge($this->im, $tempImage, 0, 0, 0, 0, $targetWidth, $targetHeight, 50);

            // Clean up temp image
            imagedestroy($tempImage);
        } else {
            // Fallback: render directly without transparency effect
            imagecopyresampled(
                $this->im, $bgImage,
                $x, $y, 0, 0,
                $newWidth, $newHeight,
                $bgWidth, $bgHeight
            );
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
                        ],
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
            public_path('storage/'.$url),
            public_path('images/'.$url),
            $url, // Try as absolute path
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
        if (! function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        // Enable SSL verification to prevent MITM attacks
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
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
        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
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
            return '#'.$color;
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

    /**
     * Center text horizontally within a given width
     */
    protected function addCenteredText(string $text, int $centerX, int $y, int $fontSize, int $color, string $weight = 'regular', int $maxWidth = 0): void
    {
        if (empty($text)) {
            return;
        }

        // Sanitize text to replace problematic characters
        $text = $this->sanitizeText($text);

        // Use getSmartFontPath for consistent font selection with addText()
        $fontPath = $this->getSmartFontPath($text, $weight);
        $textWidth = $this->getTextWidth($text, $fontSize, $fontPath);
        $leftOffset = $this->getTextLeftOffset($text, $fontSize, $fontPath);

        // If maxWidth is specified, use multiline text
        if ($maxWidth > 0) {
            $lines = $this->splitTextIntoLines($text, $maxWidth, $fontSize, $weight);
            $totalHeight = count($lines) * $fontSize * self::DEFAULT_LINE_HEIGHT;
            $startY = $y - ($totalHeight / 2);

            foreach ($lines as $line) {
                $lineFontPath = $this->getSmartFontPath($line, $weight);
                $lineWidth = $this->getTextWidth($line, $fontSize, $lineFontPath);
                $lineLeftOffset = $this->getTextLeftOffset($line, $fontSize, $lineFontPath);
                $lineX = $centerX - ($lineWidth / 2) - $lineLeftOffset;
                $this->addText($line, $lineX, $startY, $fontSize, $color, $weight);
                $startY += $fontSize * self::DEFAULT_LINE_HEIGHT;
            }
        } else {
            // Single line centered text
            $x = $centerX - ($textWidth / 2) - $leftOffset;
            $this->addText($text, $x, $y, $fontSize, $color, $weight);
        }
    }

    /**
     * Add text with automatic wrapping and RTL support
     */
    protected function addWrappedText(string $text, int $x, int $y, int $fontSize, int $color, int $maxWidth, string $weight = 'regular', string $alignment = 'left'): void
    {
        if (empty($text)) {
            return;
        }

        // Sanitize text to replace problematic characters
        $text = $this->sanitizeText($text);

        $lines = $this->splitTextIntoLines($text, $maxWidth, $fontSize, $weight);
        $currentY = $y;

        foreach ($lines as $line) {
            $lineX = $x;

            // Handle alignment
            if ($alignment === 'center') {
                $lineWidth = $this->getTextWidth($line, $fontSize, $this->getFontPath($weight));
                $lineX = $x + ($maxWidth - $lineWidth) / 2;
            } elseif ($alignment === 'right' || $this->rtl) {
                $lineWidth = $this->getTextWidth($line, $fontSize, $this->getFontPath($weight));
                $lineX = $x + $maxWidth - $lineWidth;
            }

            $this->addText($line, $lineX, $currentY, $fontSize, $color, $weight);
            $currentY += $fontSize * self::DEFAULT_LINE_HEIGHT;
        }
    }

    /**
     * Get the best font size for a given text and width constraint
     */
    protected function getOptimalFontSize(string $text, int $maxWidth, int $maxFontSize = 24, int $minFontSize = 8, string $weight = 'regular'): int
    {
        $fontPath = $this->getFontPath($weight);

        for ($size = $maxFontSize; $size >= $minFontSize; $size--) {
            $textWidth = $this->getTextWidth($text, $size, $fontPath);
            if ($textWidth <= $maxWidth) {
                return $size;
            }
        }

        return $minFontSize;
    }

    /**
     * Add text with a drop shadow effect
     */
    protected function addTextWithShadow(string $text, int $x, int $y, int $fontSize, int $textColor, int $shadowColor, string $weight = 'regular', int $shadowOffset = 2): void
    {
        // Sanitize text to replace problematic characters
        $text = $this->sanitizeText($text);

        // Add shadow first
        $this->addText($text, $x + $shadowOffset, $y + $shadowOffset, $fontSize, $shadowColor, $weight);

        // Add main text on top
        $this->addText($text, $x, $y, $fontSize, $textColor, $weight);
    }

    /**
     * Add text with a background for better readability
     */
    protected function addTextWithBackground(string $text, int $x, int $y, int $fontSize, int $textColor, int $bgColor, string $weight = 'regular', int $padding = 5): void
    {
        // Sanitize text to replace problematic characters
        $text = $this->sanitizeText($text);

        $fontPath = $this->getFontPath($weight);
        $textWidth = $this->getTextWidth($text, $fontSize, $fontPath);
        $textHeight = $this->getTextHeight($text, $fontSize, $fontPath);

        // Draw background rectangle
        $bgX = $x - $padding;
        $bgY = $y - $textHeight - $padding;
        $bgWidth = $textWidth + ($padding * 2);
        $bgHeight = $textHeight + ($padding * 2);

        imagefilledrectangle($this->im, $bgX, $bgY, $bgX + $bgWidth, $bgY + $bgHeight, $bgColor);

        // Add text on top
        $this->addText($text, $x, $y, $fontSize, $textColor, $weight);
    }

    /**
     * Check if text contains RTL characters
     */
    protected function containsRTLCharacters(string $text): bool
    {
        // Check for Hebrew, Arabic, and other RTL characters
        return preg_match('/[\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    }

    /**
     * Get text direction for mixed content
     */
    protected function getTextDirection(string $text): string
    {
        if ($this->containsRTLCharacters($text)) {
            return 'rtl';
        }

        return 'ltr';
    }

    /**
     * Reverse text for RTL display
     */
    protected function reverseText(string $text): string
    {
        // Split by words and reverse order for RTL
        $words = explode(' ', $text);

        return implode(' ', array_reverse($words));
    }

    /**
     * Check if text contains mixed content (Hebrew/Arabic + English)
     */
    protected function containsMixedContent(string $text): bool
    {
        $hasRTL = $this->containsRTLCharacters($text);
        $hasLTR = preg_match('/[a-zA-Z]/', $text);

        return $hasRTL && $hasLTR;
    }

    /**
     * Add text with mixed content using appropriate fonts for each part
     */
    protected function addMixedContentText(string $text, int $x, int $y, int $fontSize, int $color, string $weight, bool $isRtl): void
    {
        // Sanitize text to replace problematic characters
        $text = $this->sanitizeText($text);

        // For now, let's use a simpler approach that ensures Hebrew fonts work
        // Split text into segments by language using enhanced segmentation with apostrophe handling
        $segments = $this->splitTextByLanguageWithApostrophes($text);

        // Calculate total width for proper positioning
        $totalWidth = $this->calculateMixedTextWidth($segments, $fontSize, $color, $weight);

        // Determine starting position based on first character's RTL status
        $firstCharRTL = $this->isRTLCharacter($text[0] ?? '');
        if ($firstCharRTL) {
            $currentX = $x - $totalWidth;
        } else {
            $currentX = $x;
        }

        foreach ($segments as $segment) {
            $segmentText = $segment['text'];
            $segmentLang = $segment['language'];
            $segmentRTL = $segment['rtl'];

            // Force Hebrew text to use Hebrew fonts, English text to use English fonts
            if ($segmentLang === 'he' || $segmentLang === 'ar') {
                $fontPath = $this->getFontPathForLanguage($segmentLang, $weight);
                $adjustedY = $y;
            } elseif ($this->containsApostrophesOrPunctuation($segmentText)) {
                // For apostrophes, use English font with much larger downward adjustment
                $fontPath = $this->fonts['en'][$weight] ?? $this->fonts['en']['regular'];
                $adjustedY = $y + 15; // Much larger downward adjustment to align with Hebrew text
            } else {
                $fontPath = $this->fonts['en'][$weight] ?? $this->fonts['en']['regular'];
                $adjustedY = $y;
            }

            // Calculate segment width
            $segmentWidth = $this->getTextWidth($segmentText, $fontSize, $fontPath);

            // Debug: Log which font is being used
            error_log("Segment: '{$segmentText}' using font: {$fontPath}, lang: {$segmentLang}");

            if (file_exists($fontPath) && is_readable($fontPath) && function_exists('imagettftext')) {
                // Use TTF font for this segment
                $this->addTextWithTTF($segmentText, $currentX, $adjustedY, $fontSize, $color, $fontPath, $segmentRTL);
            } else {
                // Fallback to GD fonts
                $this->addTextWithGDFonts($segmentText, $currentX, $adjustedY, $fontSize, $color, $segmentRTL);
            }

            // Move to next position
            $currentX += $segmentWidth;
        }
    }

    /**
     * Calculate total width of mixed content text
     */
    protected function calculateMixedTextWidth(array $segments, int $fontSize, int $color, string $weight): int
    {
        $totalWidth = 0;

        foreach ($segments as $segment) {
            $fontPath = $this->getFontPathForLanguage($segment['language'], $weight);
            $segmentWidth = $this->getTextWidth($segment['text'], $fontSize, $fontPath);
            $totalWidth += $segmentWidth;
        }

        return $totalWidth;
    }

    /**
     * Split text into segments by language
     */
    protected function splitTextByLanguage(string $text): array
    {
        $segments = [];
        $currentSegment = '';
        $currentLang = null;
        $currentRTL = null;

        $chars = mb_str_split($text);

        foreach ($chars as $char) {
            $charLang = $this->getCharacterLanguage($char);
            $charRTL = $this->isRTLCharacter($char);

            // If language changes, save current segment and start new one
            if ($currentLang !== $charLang) {
                if (! empty($currentSegment)) {
                    $segments[] = [
                        'text' => $currentSegment,
                        'language' => $currentLang,
                        'rtl' => $currentRTL,
                    ];
                }

                $currentSegment = $char;
                $currentLang = $charLang;
                $currentRTL = $charRTL;
            } else {
                $currentSegment .= $char;
            }
        }

        // Add the last segment
        if (! empty($currentSegment)) {
            $segments[] = [
                'text' => $currentSegment,
                'language' => $currentLang,
                'rtl' => $currentRTL,
            ];
        }

        return $segments;
    }

    /**
     * Enhanced text segmentation that handles common mixed content patterns
     */
    protected function splitTextByLanguageEnhanced(string $text): array
    {
        // Handle common patterns first
        $patterns = [
            // Hebrew + English pattern: "אירוע מוזיקה Evening Music"
            '/([\x{0590}-\x{05FF}\s]+)([a-zA-Z\s]+)/u' => ['he', 'en'],
            // Arabic + English pattern: "مهرجان الموسيقى Music Festival"
            '/([\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}\s]+)([a-zA-Z\s]+)/u' => ['ar', 'en'],
            // English + Hebrew pattern: "Music Event אירוע מוזיקה"
            '/([a-zA-Z\s]+)([\x{0590}-\x{05FF}\s]+)/u' => ['en', 'he'],
            // English + Arabic pattern: "Music Festival مهرجان الموسيقى"
            '/([a-zA-Z\s]+)([\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}\s]+)/u' => ['en', 'ar'],
        ];

        foreach ($patterns as $pattern => $languages) {
            if (preg_match($pattern, $text, $matches)) {
                $segments = [];

                // Add first language segment
                if (! empty(trim($matches[1]))) {
                    $segments[] = [
                        'text' => trim($matches[1]),
                        'language' => $languages[0],
                        'rtl' => in_array($languages[0], ['he', 'ar']),
                    ];
                }

                // Add second language segment
                if (! empty(trim($matches[2]))) {
                    $segments[] = [
                        'text' => trim($matches[2]),
                        'language' => $languages[1],
                        'rtl' => in_array($languages[1], ['he', 'ar']),
                    ];
                }

                return $segments;
            }
        }

        // If no patterns match, fall back to character-by-character segmentation
        return $this->splitTextByLanguage($text);
    }

    /**
     * Enhanced text segmentation that handles apostrophes and punctuation better
     */
    protected function splitTextByLanguageWithApostrophes(string $text): array
    {
        // First, try to split by language patterns
        $segments = $this->splitTextByLanguageEnhanced($text);

        // If we have segments, check if any contain apostrophes and need further splitting
        if (count($segments) > 1) {
            $finalSegments = [];
            foreach ($segments as $segment) {
                if ($this->containsApostrophesOrPunctuation($segment['text'])) {
                    // Split this segment further to isolate apostrophes
                    $subSegments = $this->splitSegmentByApostrophes($segment);
                    $finalSegments = array_merge($finalSegments, $subSegments);
                } else {
                    $finalSegments[] = $segment;
                }
            }

            return $finalSegments;
        }

        // If no language patterns found, try to split by apostrophes directly
        if ($this->containsApostrophesOrPunctuation($text)) {
            return $this->splitSegmentByApostrophes([
                'text' => $text,
                'language' => $this->getTextLanguage($text),
                'rtl' => $this->isRTLCharacter($text[0] ?? ''),
            ]);
        }

        return $segments;
    }

    /**
     * Split a text segment by apostrophes and punctuation
     */
    protected function splitSegmentByApostrophes(array $segment): array
    {
        $text = $segment['text'];
        $baseLang = $segment['language'];
        $baseRTL = $segment['rtl'];

        // Split by apostrophes and punctuation
        $parts = preg_split('/([\'`´"\x{2032}\x{2033}\x{2034}\x{2035}\x{2036}\x{2037}\x{2039}\x{203A}])/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $segments = [];
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            if ($this->containsApostrophesOrPunctuation($part)) {
                // This is an apostrophe/punctuation - use English font
                $segments[] = [
                    'text' => $part,
                    'language' => 'en',
                    'rtl' => false,
                ];
            } else {
                // This is regular text - use original language font
                $segments[] = [
                    'text' => $part,
                    'language' => $baseLang,
                    'rtl' => $baseRTL,
                ];
            }
        }

        return $segments;
    }

    /**
     * Get the language of a character
     */
    protected function getCharacterLanguage(string $char): string
    {
        if ($this->isHebrewCharacter($char)) {
            return 'he';
        } elseif ($this->isArabicCharacter($char)) {
            return 'ar';
        } else {
            return 'en';
        }
    }

    /**
     * Check if character is Hebrew
     */
    protected function isHebrewCharacter(string $char): bool
    {
        return preg_match('/[\x{0590}-\x{05FF}]/u', $char);
    }

    /**
     * Check if character is Arabic
     */
    protected function isArabicCharacter(string $char): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $char);
    }

    /**
     * Check if character is RTL
     */
    protected function isRTLCharacter(string $char): bool
    {
        return $this->isHebrewCharacter($char) || $this->isArabicCharacter($char);
    }

    /**
     * Get font path for a specific language
     */
    protected function getFontPathForLanguage(string $lang, string $weight): string
    {
        if (isset($this->fonts[$lang][$weight])) {
            return $this->fonts[$lang][$weight];
        }

        // Fallback to regular weight
        if (isset($this->fonts[$lang]['regular'])) {
            return $this->fonts[$lang]['regular'];
        }

        // Fallback to English fonts
        if (isset($this->fonts['en'][$weight])) {
            return $this->fonts['en'][$weight];
        }

        // Final fallback
        return $this->fonts['en']['regular'];
    }

    /**
     * Get baseline offset for a font to ensure proper alignment
     */
    protected function getBaselineOffset(int $fontSize, string $fontPath): int
    {
        if (! function_exists('imagettfbbox') || ! file_exists($fontPath)) {
            // For GD fonts, use a standard baseline
            return 0;
        }

        // Get the bounding box for a test character to determine baseline
        $bbox = imagettfbbox($fontSize, 0, $fontPath, 'Ag');
        if ($bbox === false) {
            return 0;
        }

        // Calculate the baseline offset
        // The baseline is where the bottom of most letters should align
        // We use the height of the font to determine proper positioning
        $fontHeight = $bbox[1] - $bbox[7]; // Total height of the font

        // For better alignment, we'll use a standard baseline calculation
        // Most fonts have their baseline at about 80% of the font height from the top
        $baselineOffset = (int) ($fontHeight * 0.2); // 20% from the top

        return $baselineOffset;
    }

    /**
     * Get the language of the text content (not the role language)
     */
    protected function getTextLanguage(string $text): string
    {
        if ($this->containsHebrewCharacters($text)) {
            return 'he';
        } elseif ($this->containsArabicCharacters($text)) {
            return 'ar';
        } else {
            return 'en';
        }
    }

    /**
     * Check if text contains Hebrew characters
     */
    protected function containsHebrewCharacters(string $text): bool
    {
        return preg_match('/[\x{0590}-\x{05FF}]/u', $text);
    }

    /**
     * Check if text contains Arabic characters
     */
    protected function containsArabicCharacters(string $text): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    }
}
