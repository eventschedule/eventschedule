<?php

namespace App\Services\designs;

use App\Services\AbstractEventDesign;
use Carbon\Carbon;

class ModernDesign extends AbstractEventDesign
{
    // Canvas
    private const WIDTH = 800;
    private const HEIGHT = 1000;

    // Colors
    private const COLOR_NAVY      = [28, 40, 79];
    private const COLOR_ORANGE    = [244, 125, 84];
    private const COLOR_PEACH     = [248, 170, 114];
    private const COLOR_YELLOW    = [255, 204, 0];
    private const COLOR_WHITE     = [255, 255, 255];
    private const COLOR_GREY      = [110, 110, 110];
    private const COLOR_FOOTER_BG = [228, 98, 101];

    // Layout
    private const MARGIN = 48;
    private const EVENTS_TOP = 230;
    private const ROW_H = 85;
    private const DATE_W = 66;
    private const DATE_H = 66;
    private const DATE_R = 10;
    private const COL_GAP = 36;
    private const TEXT_GAP = 18;

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
        $this->gradient(self::COLOR_ORANGE, self::COLOR_PEACH);
        $this->header();
        $this->eventsGrid();
        $this->footer();

        ob_start(); 
        imagepng($this->im); 
        $out = ob_get_clean();
        return $out;
    }

    // ---------- colors/bg ----------
    protected function allocateColors(): void
    {
        foreach ([
            'navy'=>self::COLOR_NAVY,'orange'=>self::COLOR_ORANGE,'peach'=>self::COLOR_PEACH,
            'yellow'=>self::COLOR_YELLOW,'white'=>self::COLOR_WHITE,'grey'=>self::COLOR_GREY,'footer'=>self::COLOR_FOOTER_BG
        ] as $k=>$rgb) $this->c[$k]=imagecolorallocate($this->im,$rgb[0],$rgb[1],$rgb[2]);
    }

    private function gradient(array $top,array $bot): void
    {
        for($y=0;$y<self::HEIGHT;$y++){
            $t=$y/(self::HEIGHT-1);
            $r=(int)round($top[0]*(1-$t)+$bot[0]*$t);
            $g=(int)round($top[1]*(1-$t)+$bot[1]*$t);
            $b=(int)round($top[2]*(1-$t)+$bot[2]*$t);
            $col=imagecolorallocate($this->im,$r,$g,$b);
            imageline($this->im,0,$y,self::WIDTH,$y,$col);
        }
    }

    // ---------- header ----------
    private function header(): void
    {
        $company = $this->sanitize($this->role->translatedName() ?: 'YOUR COMPANY NAME');

        if ($this->rtl) {
            $w=$this->textW($company,10,$this->fontRegular());
            $this->drawEmojiText($this->vis($company),10,self::WIDTH-self::MARGIN-$w,44,$this->c['white'],$this->fontRegular(),true);
        } else {
            $this->drawEmojiText(mb_strtoupper($company),10,self::MARGIN,44,$this->c['white'],$this->fontRegular());
        }

        [$bold,$script]=$this->i18n[$this->lang];

        if ($this->rtl) {
            $right=self::WIDTH-self::MARGIN;
            $wScript=$this->textW($script,54,$this->fontScript());
            $wBold=$this->textW($bold,48,$this->fontBold());
            $this->drawEmojiText($this->vis($bold),48,$right-$wBold,112,$this->c['navy'],$this->fontBold(),true);
            $this->drawEmojiText($this->vis($script),54,$right-$wBold-16-$wScript,120,$this->c['white'],$this->fontScript(),true);
        } else {
            $this->drawEmojiText(mb_strtoupper($bold),48,self::MARGIN,120,$this->c['navy'],$this->fontBold());
            $this->drawEmojiText($script,52,self::MARGIN+285,120,$this->c['white'],$this->fontScript());
        }

        $year=(string)now()->year;
        $w=160;$h=64;$r=14;$x1=self::WIDTH-self::MARGIN-$w;$y1=72;
        $this->roundRect($x1,$y1,$x1+$w,$y1+$h,$r,$this->c['navy']);
        $this->center($year,30,$this->fontBold(),'yellow',$x1+$w/2,$y1+$h/2+10);
    }

    // ---------- events ----------
    private function eventsGrid(): void
    {
        $n=$this->events->count(); if(!$n) return;
        $leftCount=(int)ceil($n/2);
        $colW=(self::WIDTH-2*self::MARGIN-self::COL_GAP)/2;
        $xL=self::MARGIN; $xR=self::MARGIN+$colW+self::COL_GAP;
        if($this->rtl){ [$xL,$xR]=[$xR,$xL]; }

        $cols=[
            $this->events->slice(0,$leftCount)->values(),
            $this->events->slice($leftCount)->values()
        ];

        $palL=[$this->c['peach'],$this->c['navy'],$this->c['navy'],$this->c['peach'],$this->c['navy']];
        $palR=[$this->c['navy'],$this->c['peach'],$this->c['navy'],$this->c['peach'],$this->c['navy']];

        foreach([0,1] as $ci){
            $x0=$ci===0?$xL:$xR; $y=self::EVENTS_TOP;

            foreach($cols[$ci] as $i=>$event){
                $pillCol=($ci===0?$palL[$i%5]:$palR[$i%5]);
                $pillX=$x0+($this->rtl?($colW-self::DATE_W):0);
                $this->roundRect($pillX,$y,$pillX+self::DATE_W,$y+self::DATE_H,self::DATE_R,$pillCol);

                $dt=Carbon::parse($event->starts_at);
                $day=$dt->format('d'); $mon=$this->monthAbbr($dt);
                $this->center($day,22,$this->fontBold(),'white',$pillX+self::DATE_W/2,$y+36);
                $this->center($mon,12,$this->fontRegular(),'white',$pillX+self::DATE_W/2,$y+56);

                $left=$this->rtl?$x0:($x0+self::DATE_W+self::TEXT_GAP);
                $right=$this->rtl?($pillX-self::TEXT_GAP):($x0+$colW);
                $title=$this->sanitize($event->translatedName() ?: $event->name);
                $desc =$this->sanitize($event->translatedDescription() ?: '');

                $this->clampLines($this->vis($this->maybeUpper($title)),16,$this->fontBold(),$this->c['navy'],$left,$y+26,$right-$left,1,$this->rtl);
                $this->clampLines($this->vis($desc),11,$this->fontRegular(),$this->c['grey'],$left,$y+48,$right-$left,2,$this->rtl);

                $y+=self::ROW_H;
            }
        }
    }

    // ---------- footer ----------
    private function footer(): void
    {
        $h=82; imagefilledrectangle($this->im,0,self::HEIGHT-$h,self::WIDTH,self::HEIGHT,$this->c['footer']);
        $text=$this->i18n[$this->lang][2];
        $this->center($this->vis($text),12,$this->fontRegular(),'white',self::WIDTH/2,self::HEIGHT-$h/2+6);
    }

    // ---------- draw helpers ----------
    private function center(string $text,int $size,string $font,string $colorKey,float $cx,float $cy): void
    {
        $b=imagettfbbox($size,0,$font,$text);
        $w=abs($b[2]-$b[0]); $h=abs($b[7]-$b[1]);
        $x=(int)round($cx-$w/2); $y=(int)round($cy+$h/2);
        $this->drawEmojiText($text,$size,$x,$y,$this->c[$colorKey],$font,$this->rtl);
    }

    private function roundRect(int $x1,int $y1,int $x2,int $y2,int $r,int $col): void
    {
        $r=max(0,min($r,(int)floor(min($x2-$x1,$y2-$y1)/2)));
        imagefilledrectangle($this->im,$x1+$r,$y1,$x2-$r,$y2,$col);
        imagefilledrectangle($this->im,$x1,$y1+$r,$x2,$y2-$r,$col);
        imagefilledellipse($this->im,$x1+$r,$y1+$r,2*$r,2*$r,$col);
        imagefilledellipse($this->im,$x2-$r,$y1+$r,2*$r,2*$r,$col);
        imagefilledellipse($this->im,$x1+$r,$y2-$r,2*$r,2*$r,$col);
        imagefilledellipse($this->im,$x2-$r,$y2-$r,2*$r,2*$r,$col);
    }
}
