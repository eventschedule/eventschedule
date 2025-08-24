<?php

namespace App\Services\designs;

use App\Services\AbstractEventDesign;
use Carbon\Carbon;

class ModernDesign extends AbstractEventDesign
{
    // Canvas
    private const WIDTH  = 800;
    private const HEIGHT = 1000;

    // Colors (left-poster vibe)
    // Coral background, ink/dark text, cream date cards, warm grey body copy.
    private const COLOR_CORAL   = [232, 111, 82];   // bg
    private const COLOR_INK     = [27, 27, 27];     // headings/text
    private const COLOR_CREAM   = [244, 232, 216];  // date cards
    private const COLOR_GREY    = [92, 92, 92];     // descriptions
    private const COLOR_WHITE   = [255, 255, 255];
    private const COLOR_BORDER  = [205, 190, 170];  // card stroke
    private const COLOR_FOOTER  = [27, 27, 27];     // footer text

    // Layout
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
        // Flat coral background
        $this->fill(self::COLOR_CORAL);

        $this->header();
        $this->eventsGrid();
        $this->footer();

        ob_start();
        imagepng($this->im);
        return (string) ob_get_clean();
    }

    /* ---------- colors/bg ---------- */

    protected function allocateColors(): void
    {
        // allocate opaque colors we reuse a lot
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

        // translucent shadow color (used under date cards)
        $this->c['shadow'] = imagecolorallocatealpha($this->im, 0, 0, 0, 100); // ~60% alpha
    }

    // Add extra bold font method for event titles
    private function fontExtraBold(): string
    {
        return $this->fontBold(); // Use the same font file but we'll make it appear bolder
    }

    private function fill(array $rgb): void
    {
        $col = imagecolorallocate($this->im, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($this->im, 0, 0, self::WIDTH, self::HEIGHT, $col);
    }

    /* ---------- header ---------- */
    private function header(): void
    {
        $company = $this->sanitize($this->role->translatedName() ?: 'YOUR COMPANY NAME');

        // small company line at top-left
        if ($this->rtl) {
            $w = $this->textW($company, 12, $this->fontRegular());
            $this->drawEmojiText($this->vis($company), 12, self::WIDTH - self::MARGIN - $w, 44, $this->c['ink'], $this->fontRegular(), true);
        } else {
            $this->drawEmojiText(mb_strtoupper($company), 12, self::MARGIN, 44, $this->c['ink'], $this->fontRegular());
        }

        // Big stacked headline: UPCOMING (smaller) / EVENTS (bigger). No year badge.
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

    /* ---------- events ---------- */
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
                // Date card position
                $pillX = $x0 + ($this->rtl ? ($colW - self::DATE_W) : 0);

                // Shadow
                imagefilledrectangle(
                    $this->im,
                    $pillX + 3, $y + 4,
                    $pillX + 3 + self::DATE_W, $y + 4 + self::DATE_H,
                    $this->c['shadow']
                );

                // Cream rounded card
                $this->roundRect($pillX, $y, $pillX + self::DATE_W, $y + self::DATE_H, self::DATE_R, $this->c['cream']);
                // Thin border
                imagerectangle($this->im, $pillX, $y, $pillX + self::DATE_W, $y + self::DATE_H, $this->c['border']);

                // Date text (black)
                $dt  = Carbon::parse($event->starts_at);
                $day = $dt->format('d');
                $mon = $this->monthAbbr($dt);

                $this->center($day, 22, $this->fontBold(), 'ink', $pillX + self::DATE_W / 2, $y + 28);
                $this->center($mon, 12, $this->fontRegular(), 'ink', $pillX + self::DATE_W / 2, $y + 48);

                // Event text block
                $left  = $this->rtl ? $x0 : ($x0 + self::DATE_W + self::TEXT_GAP);
                $right = $this->rtl ? ($pillX - self::TEXT_GAP) : ($x0 + $colW);

                $title = $this->sanitize($event->translatedName() ?: $event->name);
                $desc  = $this->sanitize($event->translatedDescription() ?: '');

                $this->clampLines($this->vis($this->maybeUpper($title)), 20, $this->fontExtraBold(),   $this->c['ink'],  $left, $y + 26, $right - $left, 1, $this->rtl);
                $this->clampLines($this->vis($desc),                      11, $this->fontRegular(),$this->c['grey'], $left, $y + 48, $right - $left, 2, $this->rtl);

                $y += self::ROW_H;
            }
        }
    }

    /* ---------- footer ---------- */
    private function footer(): void
    {
        // No footer bar on the left poster; just a centered line in dark ink.
        $text = $this->i18n[$this->lang][2];
        $this->center($this->vis($text), 12, $this->fontRegular(), 'footer', self::WIDTH / 2, self::HEIGHT - 28);
    }

    /* ---------- draw helpers ---------- */
    private function center(string $text, int $size, string $font, string $colorKey, float $cx, float $cy): void
    {
        $b = imagettfbbox($size, 0, $font, $text);
        $w = abs($b[2] - $b[0]);
        $h = abs($b[7] - $b[1]);
        $x = (int) round($cx - $w / 2);
        $y = (int) round($cy + $h / 2);
        $this->drawEmojiText($text, $size, $x, $y, $this->c[$colorKey], $font, $this->rtl);
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
