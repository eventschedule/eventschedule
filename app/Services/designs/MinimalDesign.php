<?php

namespace App\Services\designs;

use App\Services\AbstractEventDesign;
use Carbon\Carbon;

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

    public function getWidth(): int
    {
        return self::WIDTH;
    }

    public function getHeight(): int
    {
        return self::HEIGHT;
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

        [$bold, $script] = $this->i18n[$this->lang];
        
        if ($this->rtl) {
            $right = self::WIDTH - self::MARGIN;
            $wBold = $this->textW($bold, 24, $this->fontBold());
            $this->drawEmojiText($this->vis($bold), 24, $right - $wBold, 100, $this->c['black'], $this->fontBold(), true);
        } else {
            $this->drawEmojiText($bold, 24, self::MARGIN, 100, $this->c['black'], $this->fontBold());
        }
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
}
