<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Collection;

abstract class AbstractEventDesign
{
    // Fonts in resources/fonts (unique values only)
    private const FONT_LATIN_BOLD    = 'Montserrat-VariableFont_wght.ttf';
    private const FONT_LATIN_REGULAR = 'Montserrat-VariableFont_wght.ttf';
    private const FONT_LATIN_SCRIPT  = 'Pacifico-Regular.ttf';
    private const FONT_HE_BOLD       = 'NotoSansHebrew-VariableFont_wdth,wght.ttf';
    private const FONT_HE_REGULAR    = 'NotoSansHebrew-VariableFont_wdth,wght.ttf';
    private const FONT_AR_BOLD       = 'NotoSansArabic-VariableFont_wdth,wght.ttf';
    private const FONT_AR_REGULAR    = 'NotoSansArabic-VariableFont_wdth,wght.ttf';

    // Twemoji config
    // Directory containing PNGs named like "1f44d.png", "2764-fe0f.png", etc.
    private string $twemojiDir = 'public/twemoji/72x72';     // set in ctor -> public_path('twemoji/72x72')
    private const EMOJI_SCALE = 1.0;     // relative to font size (1.0 ≈ cap height)

    /** Emoji detection (pictographs, flags, dingbats, ZWJ chains, VS16 etc.) */
    private const EMOJI_REGEX = '/[\x{1F1E6}-\x{1F1FF}\x{1F300}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{200D}\x{FE0F}]/u';

    protected $im;
    protected Role $role;
    protected Collection $events;

    protected array $c = [];

    // font paths
    private string $fontLatinBold;
    private string $fontLatinRegular;
    private string $fontLatinScript;
    private string $fontHeBold;
    private string $fontHeRegular;
    private string $fontArBold;
    private string $fontArRegular;

    // locale/layout
    protected string $lang;  // ar|de|en|es|fr|he|it|nl|pt
    protected bool $rtl;
    private bool $hasFribidi;
    private bool $hasArabicShaper;

    // i18n strings
    protected array $i18n = [
        'en' => ['UPCOMING','events','FOR MORE INFORMATION VISIT US AT'],
        'de' => ['KOMMENDE','Events','FÜR WEITERE INFOS BESUCHEN SIE UNS UNTER'],
        'es' => ['PRÓXIMOS','eventos','PARA MÁS INFORMACIÓN VISÍTENOS EN'],
        'fr' => ['À VENIR','événements','POUR PLUS D\'INFORMATIONS, VISITEZ'],
        'it' => ['PROSSIMI','eventi','PER MAGGIORI INFORMAZIONI VISITATE'],
        'nl' => ['AANKOMENDE','evenementen','VOOR MEER INFORMATIE BEZOEK'],
        'pt' => ['PRÓXIMOS','eventos','PARA MAIS INFORMAÇÕES VISITE'],
        'he' => ['קרובים','אירועים','למידע נוסף בקרו אותנו ב־'],
        'ar' => ['القادمة','فعاليات','لمزيد من المعلومات زورونا على'],
    ];

    public function __construct(Role $role, Collection $events)
    {
        $this->role   = $role;
        $this->events = $events->take(10)->values(); // MAX_EVENTS moved to constant
        $this->lang   = in_array(strtolower($role->language_code), ['ar','de','en','es','fr','he','it','nl','pt'], true)
            ? strtolower($role->language_code) : 'en';

        $this->rtl = in_array($this->lang, ['ar','he'], true);
        $this->hasFribidi = function_exists('fribidi_log2vis');
        $this->hasArabicShaper = class_exists('\I18N_Arabic'); // optional (composer ar-php/ar-php)

        $this->twemojiDir = public_path('twemoji/72x72');

        $this->im = imagecreatetruecolor($this->getWidth(), $this->getHeight());
        imagealphablending($this->im, true);
        imagesavealpha($this->im, true);

        $this->fontLatinBold    = resource_path('fonts/'.self::FONT_LATIN_BOLD);
        $this->fontLatinRegular = resource_path('fonts/'.self::FONT_LATIN_REGULAR);
        $this->fontLatinScript  = resource_path('fonts/'.self::FONT_LATIN_SCRIPT);
        $this->fontHeBold       = resource_path('fonts/'.self::FONT_HE_BOLD);
        $this->fontHeRegular    = resource_path('fonts/'.self::FONT_HE_REGULAR);
        $this->fontArBold       = resource_path('fonts/'.self::FONT_AR_BOLD);
        $this->fontArRegular    = resource_path('fonts/'.self::FONT_AR_REGULAR);

        $this->allocateColors();
    }

    public function __destruct() { imagedestroy($this->im); }

    abstract public function generate(): string;
    abstract protected function allocateColors(): void;

    abstract public function getWidth(): int;
    abstract public function getHeight(): int;

    // ---------- fonts/locale ----------
    protected function fontBold(): string   { return $this->lang==='ar'?$this->fontArBold:($this->lang==='he'?$this->fontHeBold:$this->fontLatinBold); }
    protected function fontRegular(): string{ return $this->lang==='ar'?$this->fontArRegular:($this->lang==='he'?$this->fontHeRegular:$this->fontLatinRegular); }
    protected function fontScript(): string { return $this->rtl ? $this->fontBold() : $this->fontLatinScript; }

    protected function monthAbbr(\Carbon\Carbon $date): string
    {
        if (class_exists('\IntlDateFormatter')) {
            $fmt = new \IntlDateFormatter($this->localeTag(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'LLL');
            $label = $fmt->format($date);
            return $this->rtl ? $label : mb_strtoupper($label);
        }
        $fb=[
            'en'=>[1=>'JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'],
            'de'=>[1=>'JAN','FEB','MÄR','APR','MAI','JUN','JUL','AUG','SEP','OKT','NOV','DEZ'],
            'es'=>[1=>'ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'],
            'fr'=>[1=>'JANV.','FÉVR.','MARS','AVR.','MAI','JUIN','JUIL.','AOÛT','SEPT.','OCT.','NOV.','DÉC.'],
            'it'=>[1=>'GEN','FEB','MAR','APR','MAG','GIU','LUG','AGO','SET','OTT','NOV','DIC'],
            'nl'=>[1=>'JAN','FEB','MRT','APR','MEI','JUN','JUL','AUG','SEP','OKT','NOV','DEC'],
            'pt'=>[1=>'JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ'],
            'he'=>[1=>'ינו׳','פבר׳','מרץ','אפר׳','מאי','יוני','יולי','אוג׳','ספט׳','אוק׳','נוב׳','דצמ׳'],
            'ar'=>[1=>'ينا','فبر','مار','أبر','ماي','يون','يول','أغس','سبت','أكت','نوف','ديس'],
        ];
        $m=(int)$date->month; $lab=$fb[$this->lang][$m] ?? $date->format('M');
        return $this->rtl ? $lab : mb_strtoupper($lab);
    }

    private function localeTag(): string
    {
        return match($this->lang){
            'de'=>'de_DE','es'=>'es_ES','fr'=>'fr_FR','it'=>'it_IT','nl'=>'nl_NL','pt'=>'pt_PT','he'=>'he_IL','ar'=>'ar_EG', default=>'en_US'
        };
    }

    protected function maybeUpper(string $s): string { return $this->rtl ? $s : mb_strtoupper($s); }

    // ---------- draw helpers ----------
    protected function clampLines(string $text,int $size,string $font,int $color,int $x,int $y,int $maxW,int $maxLines,bool $rtl): int
    {
        $words=preg_split('/\s+/u',$text) ?: [];
        $line=''; $lines=[];
        foreach($words as $w){
            $try=$line===''?$w:"$line $w";
            if($this->textW($try,$size,$font) <= $maxW){ $line=$try; }
            else{
                if($line===''){ $line=$this->truncate($w,$size,$font,$maxW); }
                $lines[]=$line; $line=$w;
                if(count($lines)>=$maxLines) break;
            }
        }
        if($line!=='' && count($lines)<$maxLines) $lines[]=$line;
        if(count($lines)===$maxLines){
            $last=end($lines);
            $lines[count($lines)-1]=$this->truncate($last,$size,$font,$maxW,'…');
        }
        $dy=0;
        foreach($lines as $ln){
            $w=$this->textW($ln,$size,$font);
            $drawX=$rtl?($x+($maxW-$w)):$x;
            $this->drawEmojiText($ln,$size,$drawX,$y+$dy,$color,$font,$rtl);
            $dy+=(int)ceil($size*1.35);
        }
        return count($lines);
    }

    protected function textW(string $text,int $size,string $font): int
    {
        $b=imagettfbbox($size,0,$font,$text);
        return (int)abs($b[2]-$b[0]);
    }

    // ---------- emoji-safe text drawing (Twemoji overlay) ----------
    protected function drawEmojiText(string $text,int $size,int $x,int $y,int $color,string $font,bool $rtlAlign=false): void
    {
        $text = $this->rtl ? $this->vis($text) : $text;

        // Base draw without emojis (replace emoji clusters with spaces to keep advance)
        $plain = $this->stripEmojisKeepSpaces($text);
        imagettftext($this->im,$size,0,$x,$y,$color,$font,$plain);

        // Now overlay each emoji image
        $clusters = $this->graphemes($text);
        $cursorX = $x;

        foreach ($clusters as $g) {
            if (!$this->isEmoji($g)) {
                $cursorX += $this->textW($g,$size,$font);
                continue;
            }
            
            // Debug logging for emoji processing
            error_log("Processing emoji: " . $g . " (hex: " . bin2hex($g) . ")");
            
            $path = $this->twemojiPathFor($g);
            if ($path && file_exists($path)) {
                error_log("Found emoji file: {$path}");
                $emojiImg = imagecreatefrompng($path);
                if ($emojiImg !== false) {
                    // target size ~ font size
                    $target = (int)round($size * self::EMOJI_SCALE);
                    $w = imagesx($emojiImg); $h = imagesy($emojiImg);
                    imagecopyresampled(
                        $this->im, $emojiImg,
                        (int)$cursorX, $y - $target + 2,   // baseline align
                        0, 0,
                        $target, $target,
                        $w, $h
                    );
                    imagedestroy($emojiImg);
                    // Advance by the visual width we just placed
                    $cursorX += $target;
                    error_log("Successfully rendered emoji: {$g}");
                    continue;
                } else {
                    // Log error if image couldn't be loaded
                    error_log("Failed to load emoji image: {$path}");
                }
            } else {
                // Log missing emoji file for debugging
                if ($this->isEmoji($g)) {
                    error_log("Emoji file not found for: " . bin2hex($g) . " (text: " . $g . ")");
                    // Try to show what variations were attempted
                    $cps = $this->codepoints($g);
                    $variations = $this->generateFilenameVariations($cps);
                    error_log("Attempted variations: " . implode(', ', $variations));
                }
            }
            // Fallback: just advance by nominal width if image missing
            $cursorX += $this->textW(' ',$size,$font);
        }
    }

    private function stripEmojisKeepSpaces(string $s): string
    {
        return preg_replace(self::EMOJI_REGEX, ' ', $s);
    }

    private function isEmoji(string $g): bool
    {
        return (bool)preg_match(self::EMOJI_REGEX, $g);
    }

    private function graphemes(string $s): array
    {
        if (function_exists('grapheme_split')) return grapheme_split($s) ?: [$s];
        preg_match_all('/./u',$s,$m); return $m[0] ?? [$s];
    }

    // Build Twemoji filename from emoji cluster: hex codepoints joined with '-',
    // trying multiple variations to find the correct file
    private function twemojiPathFor(string $emoji): ?string
    {
        $cps = $this->codepoints($emoji);
        if (empty($cps)) return null;

        // Try multiple filename variations to find the correct file
        $variations = $this->generateFilenameVariations($cps);
        
        foreach ($variations as $name) {
            $path = rtrim($this->twemojiDir,'/').'/'.$name;
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    private function generateFilenameVariations(array $codepoints): array
    {
        $variations = [];
        
        // Original with all codepoints
        $variations[] = strtolower(implode('-', array_map(fn($cp) => dechex($cp), $codepoints))).'.png';
        
        // Without FE0F (variation selector)
        $filtered = array_filter($codepoints, fn($cp) => $cp !== 0xFE0F);
        if (count($filtered) !== count($codepoints)) {
            $variations[] = strtolower(implode('-', array_map(fn($cp) => dechex($cp), $filtered))).'.png';
        }
        
        // Without FE0F but keep ZWJ sequences (200D)
        $filtered = [];
        foreach ($codepoints as $i => $cp) {
            if ($cp === 0xFE0F) {
                // Skip FE0F unless it's followed by 200D (ZWJ)
                if ($i + 1 < count($codepoints) && $codepoints[$i + 1] === 0x200D) {
                    $filtered[] = $cp;
                }
                continue;
            }
            $filtered[] = $cp;
        }
        if (count($filtered) !== count($codepoints)) {
            $variations[] = strtolower(implode('-', array_map(fn($cp) => dechex($cp), $filtered))).'.png';
        }
        
        return $variations;
    }

    private function codepoints(string $s): array
    {
        $out=[];
        $len=mb_strlen($s,'UTF-8');
        for($i=0;$i<$len;$i++){
            $ch=mb_substr($s,$i,1,'UTF-8');
            $out[]=$this->uord($ch);
        }
        return $out;
    }

    private function uord(string $c): int
    {
        $k=mb_convert_encoding($c,'UCS-4BE','UTF-8');
        $k=unpack('N', $k)[1];
        return $k;
    }

    // ---------- sanitation & bidi ----------
    protected function sanitize(string $s): string
    { 
        $s = trim(mb_convert_encoding(strip_tags($s),'UTF-8','UTF-8'));
        
        // Remove problematic Unicode characters that can cause visual artifacts
        $s = preg_replace('/[\x{FFF0}-\x{FFFF}]/u', '', $s); // Remove private use area characters
        $s = preg_replace('/[\x{2000}-\x{200F}]/u', '', $s); // Remove bidirectional control characters except 200F (RTL mark)
        $s = preg_replace('/[\x{2028}-\x{202F}]/u', '', $s); // Remove other format characters
        $s = preg_replace('/[\x{2060}-\x{206F}]/u', '', $s); // Remove other format characters
        
        return $s;
    }

    protected function vis(string $s): string
    {
        if (!$this->rtl) return $s;

        // Protect emojis from FriBidi by temporarily replacing them
        $placeholders=[]; $i=0;
        $s=preg_replace_callback(self::EMOJI_REGEX,function($m) use (&$placeholders,&$i){
            $key="__EMOJI_{$i}__";
            $placeholders[$key]=$m[0]; $i++; return $key;
        },$s);

        if ($this->lang==='ar' && $this->hasArabicShaper) {
            /** @noinspection PhpUndefinedClassInspection */
            $ar=new \I18N_Arabic('Glyphs'); $s=$ar->utf8Glyphs($s);
        }
        if ($this->hasFribidi) {
            $s=fribidi_log2vis($s, FRIBIDI_AUTO, FRIBIDI_CHARSET_UTF8);
        } else {
            $s="\u{200F}".$s;
        }
        if (!empty($placeholders)) $s=strtr($s,$placeholders);
        
        // Clean up any remaining problematic Unicode characters that might cause visual artifacts
        $s = preg_replace('/[\x{FFF0}-\x{FFFF}]/u', '', $s); // Remove private use area characters
        $s = preg_replace('/[\x{2000}-\x{200F}]/u', '', $s); // Remove bidirectional control characters except 200F (RTL mark)
        $s = preg_replace('/[\x{2028}-\x{202F}]/u', '', $s); // Remove other format characters
        
        return $s;
    }

    private function truncate(string $text,int $size,string $font,int $maxW,string $ellipsis=''): string
    {
        $lo=0;$hi=mb_strlen($text);$best='';
        while($lo<=$hi){
            $mid=intdiv($lo+$hi,2);
            $sub=mb_substr($text,0,$mid).$ellipsis;
            if($this->textW($sub,$size,$font) <= $maxW){ $best=$sub; $lo=$mid+1; }
            else{ $hi=$mid-1; }
        }
        return $best ?: $ellipsis;
    }
}
