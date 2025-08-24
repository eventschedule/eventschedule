<?php

namespace App\Services\designs;

use App\Services\AbstractEventDesign;
use Carbon\Carbon;

class ModernDesign extends AbstractEventDesign
{
    private const WIDTH  = 800;
    private const HEIGHT = 1000;

    private const COLOR_CORAL   = [232, 111, 82];
    private const COLOR_INK     = [27, 27, 27];
    private const COLOR_CREAM   = [244, 232, 216];
    private const COLOR_GREY    = [92, 92, 92];
    private const COLOR_WHITE   = [255, 255, 255];
    private const COLOR_BORDER  = [205, 190, 170];
    private const COLOR_FOOTER  = [27, 27, 27];

    private const MARGIN     = 48;
    private const EVENTS_TOP = 230;
    private const ROW_H      = 85;

    private const DATE_W = 66;
    private const DATE_H = 66;
    private const DATE_R = 10;

    private const COL_GAP  = 36;
    private const TEXT_GAP = 18;

    public function getWidth(): int  { return self::WIDTH;  }
    public function getHeight(): int { return self::HEIGHT; }

    public function generate(): string
    {
        $this->fill(self::COLOR_CORAL);

        $this->header();
        $this->eventsGrid();
        $this->footer();

        ob_start();
        imagepng($this->im);
        return (string) ob_get_clean();
    }

    protected function allocateColors(): void
    {
        foreach ([
            'coral'  => self::COLOR_CORAL,
            'ink'    => self::COLOR_INK,
            'cream'  => self::COLOR_CREAM,
            'grey'   => self::COLOR_GREY,
            'white'  => self::COLOR_WHITE,
            'border' => self::COLOR_BORDER,
            'footer' => self::COLOR_FOOTER,
        ] as $k => $rgb) {
            $this->c[$k] = imagecolorallocate($this->im, $rgb[0], $rgb[1], $rgb[2]);
        }

        $this->c['shadow'] = imagecolorallocatealpha($this->im, 0, 0, 0, 100);
    }

    /**
     * Smart bold font selection for mixed content
     */
    private function fontExtraBold(): string
    {
        // For RTL languages, use smart font selection
        if ($this->rtl) {
            return $this->fontBold();
        }
        return $this->fontBold();
    }

    /**
     * Smart text rendering with automatic font selection
     */
    private function drawSmartBoldText(string $text, int $size, int $x, int $y, int $color, bool $rtl = false): void
    {
        $this->drawSmartText($text, $size, $x, $y, $color, $rtl);
    }

    /**
     * Smart clampLines with automatic font selection for mixed content
     */
    private function smartClampLines(string $text, int $size, int $color, int $x, int $y, int $maxW, int $maxLines, bool $rtl): int
    {
        $words = preg_split('/\s+/u', $text) ?: [];
        $line = '';
        $lines = [];
        
        foreach ($words as $w) {
            $try = $line === '' ? $w : "$line $w";
            if ($this->textW($try, $size, $this->fontBold()) <= $maxW) {
                $line = $try;
            } else {
                if ($line === '') {
                    $line = $this->truncate($w, $size, $this->fontBold(), $maxW, '', $rtl);
                }
                $lines[] = $line;
                $line = $w;
                if (count($lines) >= $maxLines) break;
            }
        }
        
        if ($line !== '' && count($lines) < $maxLines) {
            $lines[] = $line;
        }
        
        if (count($lines) === $maxLines) {
            $last = end($lines);
            $lines[count($lines) - 1] = $this->truncate($last, $size, $this->fontBold(), $maxW, '…', $rtl);
        }
        
        $dy = 0;
        foreach ($lines as $ln) {
            $w = $this->textW($ln, $size, $this->fontBold());
            $drawX = $rtl ? ($x + ($maxW - $w)) : $x;
            
            // Use smart text rendering for better font selection
            $this->drawSmartText($ln, $size, $drawX, $y + $dy, $color, $rtl);
            $dy += (int) ceil($size * 1.35);
        }
        
        return count($lines);
    }

    /**
     * Truncate text to fit within max width
     */
    private function truncate(string $text, int $size, string $font, int $maxW, string $ellipsis = '', bool $rtl = false): string
    {
        $lo = 0;
        $hi = mb_strlen($text);
        $best = '';
        
        while ($lo <= $hi) {
            $mid = intdiv($lo + $hi, 2);
            
            if ($this->rtl) {
                $sub = $ellipsis . mb_substr($text, -$mid);
            } else {
                $sub = mb_substr($text, 0, $mid) . $ellipsis;
            }
            
            if ($this->textW($sub, $size, $font) <= $maxW) {
                $best = $sub;
                $lo = $mid + 1;
            } else {
                $hi = $mid - 1;
            }
        }
        
        return $best ?: $ellipsis;
    }

    private function fill(array $rgb): void
    {
        $col = imagecolorallocate($this->im, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($this->im, 0, 0, self::WIDTH, self::HEIGHT, $col);
    }

    private function header(): void
    {
        $company = $this->sanitize($this->role->translatedName() ?: 'YOUR COMPANY NAME');

        if ($this->rtl) {
            $w = $this->textW($company, 12, $this->fontRegular());
            $this->drawEmojiText($this->vis($company), 12, self::WIDTH - self::MARGIN - $w, 44, $this->c['ink'], $this->fontRegular(), true);
        } else {
            $this->drawEmojiText(mb_strtoupper($company), 12, self::MARGIN, 44, $this->c['ink'], $this->fontRegular());
        }

        $this->drawProfileLogo();

        [$wordUpcoming, $wordEvents] = $this->i18n[$this->lang];

        $up = mb_strtoupper($wordUpcoming);
        $ev = mb_strtoupper($wordEvents);

        if ($this->rtl) {
            $right     = self::WIDTH - self::MARGIN;
            $upWidth   = $this->textW($up, 54, $this->fontBold());
            $evWidth   = $this->textW($ev, 96, $this->fontBold());
            $xUp       = $right - max($upWidth, $evWidth);
            $xEv       = $xUp;

            $this->drawEmojiText($this->vis($up), 54, $xUp, 112, $this->c['ink'], $this->fontBold(), true);
            $this->drawEmojiText($this->vis($ev), 96, $xEv, 190, $this->c['ink'], $this->fontBold(), true);
        } else {
            $x = self::MARGIN;
            $this->drawEmojiText($up, 54, $x, 112, $this->c['ink'], $this->fontBold());
            $this->drawEmojiText($ev, 96, $x, 190, $this->c['ink'], $this->fontBold());
        }
    }

    private function eventsGrid(): void
    {
        $n = $this->events->count();
        if (!$n) return;

        $leftCount = (int) ceil($n / 2);
        $colW      = (self::WIDTH - 2 * self::MARGIN - self::COL_GAP) / 2;

        $xL = self::MARGIN;
        $xR = self::MARGIN + $colW + self::COL_GAP;
        if ($this->rtl) { [$xL, $xR] = [$xR, $xL]; }

        $cols = [
            $this->events->slice(0, $leftCount)->values(),
            $this->events->slice($leftCount)->values(),
        ];

        foreach ([0, 1] as $ci) {
            $x0 = $ci === 0 ? $xL : $xR;
            $y  = self::EVENTS_TOP;

            foreach ($cols[$ci] as $event) {
                $pillX = $x0 + ($this->rtl ? ($colW - self::DATE_W) : 0);

                imagefilledrectangle(
                    $this->im,
                    $pillX + 3, $y + 4,
                    $pillX + 3 + self::DATE_W, $y + 4 + self::DATE_H,
                    $this->c['shadow']
                );

                $this->roundRect($pillX, $y, $pillX + self::DATE_W, $y + self::DATE_H, self::DATE_R, $this->c['cream']);
                imagerectangle($this->im, $pillX, $y, $pillX + self::DATE_W, $y + self::DATE_H, $this->c['border']);

                $dt  = Carbon::parse($event->starts_at);
                $day = $dt->format('d');
                $mon = $this->monthAbbr($dt);

                $this->center($day, 22, $this->fontBold(), 'ink', $pillX + self::DATE_W / 2, $y + 28);
                $this->center($mon, 12, $this->fontRegular(), 'ink', $pillX + self::DATE_W / 2, $y + 48);

                $left  = $this->rtl ? $x0 : ($x0 + self::DATE_W + self::TEXT_GAP);
                $right = $this->rtl ? ($pillX - self::TEXT_GAP) : ($x0 + $colW);

                $title = $this->sanitize($event->translatedName() ?: $event->name);
                $desc  = $this->sanitize($event->translatedDescription() ?: '');

                $this->smartClampLines($this->vis($this->maybeUpper($title)), 20, $this->c['ink'],  $left, $y + 26, $right - $left, 1, $this->rtl);
                $this->smartClampLines($this->vis($desc),                      11, $this->c['grey'], $left, $y + 48, $right - $left, 2, $this->rtl);

                $y += self::ROW_H;
            }
        }
    }

    private function footer(): void
    {
        $text = $this->i18n[$this->lang][2];
        $this->center($this->vis($text), 12, $this->fontRegular(), 'footer', self::WIDTH / 2, self::HEIGHT - 28);
    }

    // ---------- profile logo ----------
    protected function drawProfileLogo(): void
    {
        // Check if role has a profile image
        if (!$this->role->profile_image_url) {
            return;
        }

        // Logo dimensions and positioning - make it 50% larger
        $logoSize = 120; // Increased from 80 (50% larger)
        $logoMargin = 20;
        
        // Position based on RTL setting
        if ($this->rtl) {
            // RTL: top-left corner
            $logoX = $logoMargin;
        } else {
            // LTR: top-right corner
            $logoX = $this->getWidth() - $logoMargin - $logoSize;
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

        // Create a rounded rectangle logo
        $this->drawRoundedLogo($profileImg, $logoX, $logoY, $logoSize, $origW, $origH);

        // Clean up
        imagedestroy($profileImg);
    }

    private function center(string $text, int $size, string $font, string $colorKey, float $cx, float $cy): void
    {
        $b = imagettfbbox($size, 0, $font, $text);
        $w = abs($b[2] - $b[0]);
        $h = abs($b[7] - $b[1]);
        $x = (int) round($cx - $w / 2);
        $y = (int) round($cy + $h / 2);
        
        // Use smart text rendering for better font selection
        if ($font === $this->fontBold() || $font === $this->fontExtraBold()) {
            $this->drawSmartText($text, $size, $x, $y, $this->c[$colorKey], $this->rtl);
        } else {
            $this->drawEmojiText($text, $size, $x, $y, $this->c[$colorKey], $font, $this->rtl);
        }
    }

    private function roundRect(int $x1, int $y1, int $x2, int $y2, int $r, int $col): void
    {
        $r = max(0, min($r, (int) floor(min($x2 - $x1, $y2 - $y1) / 2)));
        imagefilledrectangle($this->im, $x1 + $r, $y1,     $x2 - $r, $y2,     $col);
        imagefilledrectangle($this->im, $x1,     $y1 + $r, $x2,     $y2 - $r, $col);
        imagefilledellipse($this->im, $x1 + $r, $y1 + $r, 2 * $r, 2 * $r, $col);
        imagefilledellipse($this->im, $x2 - $r, $y1 + $r, 2 * $r, 2 * $r, $col);
        imagefilledellipse($this->im, $x1 + $r, $y2 - $r, 2 * $r, 2 * $r, $col);
        imagefilledellipse($this->im, $x2 - $r, $y2 - $r, 2 * $r, 2 * $r, $col);
    }
}