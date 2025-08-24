<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Collection;
use GdImage;

abstract class AbstractEventDesign
{
    // Fonts in resources/fonts (unique values only)
    private const FONT_LATIN_BOLD    = 'Montserrat-VariableFont_wght.ttf';
    private const FONT_LATIN_REGULAR = 'Montserrat-VariableFont_wght.ttf';
    private const FONT_LATIN_SCRIPT  = 'Pacifico-Regular.ttf';
    private const FONT_HE_BOLD       = 'NotoSansHebrew-Bold.ttf';
    private const FONT_HE_REGULAR    = 'NotoSansHebrew-VariableFont_wdth,wght.ttf';
    private const FONT_AR_BOLD       = 'NotoSansArabic-Bold.ttf';
    private const FONT_AR_REGULAR    = 'NotoSansArabic-VariableFont_wdth,wght.ttf';

    // Twemoji config
    private string $twemojiDir = 'public/twemoji/72x72';
    private const EMOJI_SCALE = 1.0;

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
    protected string $lang;
    protected bool $rtl;
    private bool $hasFribidi;
    private bool $hasArabicShaper;

    // Track temporary files for cleanup
    protected array $tempFiles = [];

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
        $this->events = $events->take(10)->values();
        $this->lang   = in_array(strtolower($role->language_code), ['ar','de','en','es','fr','he','it','nl','pt'], true)
            ? strtolower($role->language_code) : 'en';

        $this->rtl = in_array($this->lang, ['ar','he'], true);
        $this->hasFribidi = function_exists('fribidi_log2vis');
        $this->hasArabicShaper = class_exists('\I18N_Arabic');

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

    public function __destruct()
    {
        imagedestroy($this->im);
        $this->cleanupTempFiles();
    }

    abstract public function generate(): string;
    abstract protected function allocateColors(): void;

    abstract public function getWidth(): int;
    abstract public function getHeight(): int;

    protected function fontBold(): string   { return $this->lang==='ar'?$this->fontArBold:($this->lang==='he'?$this->fontHeBold:$this->fontLatinBold); }
    protected function fontRegular(): string{ return $this->lang==='ar'?$this->fontArRegular:($this->lang==='he'?$this->fontHeRegular:$this->fontLatinRegular); }
    protected function fontScript(): string { return $this->rtl ? $this->fontBold() : $this->fontLatinScript; }

    /**
     * Smart font selection for mixed content
     * Uses appropriate font based on text content and language
     */
    protected function smartFontBold(string $text): string
    {
        // For non-RTL languages, always use Latin bold
        if (!$this->rtl) {
            return $this->fontLatinBold;
        }

        // For RTL languages, check if text contains Latin characters/symbols
        if ($this->containsLatinOrSymbols($text)) {
            return $this->fontLatinBold;
        }

        // Pure RTL text uses the appropriate RTL bold font
        return $this->fontBold();
    }

    /**
     * Check if text contains Latin characters, numbers, or symbols
     */
    private function containsLatinOrSymbols(string $text): bool
    {
        // Check for Latin characters (a-z, A-Z)
        if (preg_match('/[a-zA-Z]/', $text)) {
            return true;
        }

        // Check for numbers (0-9)
        if (preg_match('/[0-9]/', $text)) {
            return true;
        }

        // Check for common symbols that might not be in RTL fonts
        $symbols = ['…', '.', ',', '!', '?', ':', ';', '-', '_', '(', ')', '[', ']', '{', '}', '<', '>', '=', '+', '-', '*', '/', '\\', '|', '&', '%', '$', '#', '@', '~', '`', '^'];
        foreach ($symbols as $symbol) {
            if (str_contains($text, $symbol)) {
                return true;
            }
        }

        return false;
    }

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

    protected function clampLines(string $text,int $size,string $font,int $color,int $x,int $y,int $maxW,int $maxLines,bool $rtl): int
    {
        $words=preg_split('/\s+/u',$text) ?: [];
        $line=''; $lines=[];
        foreach($words as $w){
            $try=$line===''?$w:"$line $w";
            if($this->textW($try,$size,$font) <= $maxW){ $line=$try; }
            else{
                if($line===''){ $line=$this->truncate($w,$size,$font,$maxW,'',$rtl); }
                $lines[]=$line; $line=$w;
                if(count($lines)>=$maxLines) break;
            }
        }
        if($line!=='' && count($lines)<$maxLines) $lines[]=$line;
        if(count($lines)===$maxLines){
            $last=end($lines);
            $lines[count($lines)-1]=$this->truncate($last,$size,$font,$maxW,'…',$rtl);
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

    protected function drawEmojiText(string $text,int $size,int $x,int $y,int $color,string $font,bool $rtlAlign=false): void
    {
        $text = $this->rtl ? $this->vis($text) : $text;

        $plain = $this->stripEmojisKeepSpaces($text);
        imagettftext($this->im,$size,0,$x,$y,$color,$font,$plain);

        $clusters = $this->graphemes($text);
        $cursorX = $x;

        foreach ($clusters as $g) {
            if (!$this->isEmoji($g)) {
                $cursorX += $this->textW($g,$size,$font);
                continue;
            }
            
            error_log("Processing emoji: " . $g . " (hex: " . bin2hex($g) . ")");
            
            $path = $this->twemojiPathFor($g);
            if ($path && file_exists($path)) {
                error_log("Found emoji file: {$path}");
                $emojiImg = imagecreatefrompng($path);
                if ($emojiImg !== false) {
                    $target = (int)round($size * self::EMOJI_SCALE);
                    $w = imagesx($emojiImg); $h = imagesy($emojiImg);
                    imagecopyresampled(
                        $this->im, $emojiImg,
                        (int)$cursorX, $y - $target + 2,
                        0, 0,
                        $target, $target,
                        $w, $h
                    );
                    imagedestroy($emojiImg);
                    $cursorX += $target;
                    error_log("Successfully rendered emoji: {$g}");
                    continue;
                } else {
                    error_log("Failed to load emoji image: {$path}");
                }
            } else {
                if ($this->isEmoji($g)) {
                    error_log("Emoji file not found for: " . bin2hex($g) . " (text: " . $g . ")");
                    $cps = $this->codepoints($g);
                    $variations = $this->generateFilenameVariations($cps);
                    error_log("Attempted variations: " . implode(', ', $variations));
                }
            }
            $cursorX += $this->textW(' ',$size,$font);
        }
    }

    /**
     * Smart text rendering with automatic font selection for mixed content
     * This method intelligently chooses fonts for different parts of the text
     */
    protected function drawSmartText(string $text, int $size, int $x, int $y, int $color, bool $rtlAlign = false): void
    {
        $text = $this->rtl ? $this->vis($text) : $text;

        // Split text into segments based on character type
        $segments = $this->splitTextByFont($text);
        
        $cursorX = $x;
        foreach ($segments as $segment) {
            $font = $segment['font'];
            $text = $segment['text'];
            
            // Handle emojis in this segment
            if ($this->containsEmojis($text)) {
                $this->drawEmojiText($text, $size, $cursorX, $y, $color, $font, $rtlAlign);
            } else {
                // Regular text rendering
                imagettftext($this->im, $size, 0, $cursorX, $y, $color, $font, $text);
            }
            
            // Move cursor to next position
            $cursorX += $this->textW($text, $size, $font);
        }
    }

    /**
     * Split text into segments based on which font should be used
     */
    private function splitTextByFont(string $text): array
    {
        $segments = [];
        $currentFont = null;
        $currentText = '';
        
        $clusters = $this->graphemes($text);
        
        foreach ($clusters as $cluster) {
            // Skip emojis in this pass - they'll be handled separately
            if ($this->isEmoji($cluster)) {
                // If we have accumulated text, add it as a segment
                if ($currentText !== '') {
                    $segments[] = [
                        'text' => $currentText,
                        'font' => $currentFont ?? $this->fontLatinBold
                    ];
                    $currentText = '';
                }
                
                // Add emoji as its own segment with current font
                $segments[] = [
                    'text' => $cluster,
                    'font' => $currentFont ?? $this->fontLatinBold
                ];
                continue;
            }
            
            // Determine appropriate font for this character
            $neededFont = $this->getFontForCharacter($cluster);
            
            // If font changes, save current segment and start new one
            if ($currentFont !== $neededFont && $currentText !== '') {
                $segments[] = [
                    'text' => $currentText,
                    'font' => $currentFont
                ];
                $currentText = '';
            }
            
            $currentFont = $neededFont;
            $currentText .= $cluster;
        }
        
        // Add final segment
        if ($currentText !== '') {
            $segments[] = [
                'text' => $currentText,
                'font' => $currentFont ?? $this->fontLatinBold
            ];
        }
        
        return $segments;
    }

    /**
     * Get the appropriate font for a single character
     */
    private function getFontForCharacter(string $char): string
    {
        // For non-RTL languages, always use Latin fonts
        if (!$this->rtl) {
            return $this->fontLatinBold;
        }
        
        // Check if character is Latin, number, or symbol
        if ($this->isLatinOrSymbol($char)) {
            return $this->fontLatinBold;
        }
        
        // RTL characters use appropriate RTL font
        return $this->fontBold();
    }

    /**
     * Check if a single character is Latin, number, or symbol
     */
    private function isLatinOrSymbol(string $char): bool
    {
        // Latin characters
        if (preg_match('/[a-zA-Z]/', $char)) {
            return true;
        }
        
        // Numbers
        if (preg_match('/[0-9]/', $char)) {
            return true;
        }
        
        // Common symbols
        $symbols = ['…', '.', ',', '!', '?', ':', ';', '-', '_', '(', ')', '[', ']', '{', '}', '<', '>', '=', '+', '-', '*', '/', '\\', '|', '&', '%', '$', '#', '@', '~', '`', '^'];
        return in_array($char, $symbols);
    }

    /**
     * Check if text contains emojis
     */
    private function containsEmojis(string $text): bool
    {
        return (bool) preg_match(self::EMOJI_REGEX, $text);
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

    private function twemojiPathFor(string $emoji): ?string
    {
        $cps = $this->codepoints($emoji);
        if (empty($cps)) return null;

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
        
        $variations[] = strtolower(implode('-', array_map(fn($cp) => dechex($cp), $codepoints))).'.png';
        
        $filtered = array_filter($codepoints, fn($cp) => $cp !== 0xFE0F);
        if (count($filtered) !== count($codepoints)) {
            $variations[] = strtolower(implode('-', array_map(fn($cp) => dechex($cp), $filtered))).'.png';
        }
        
        $filtered = [];
        foreach ($codepoints as $i => $cp) {
            if ($cp === 0xFE0F) {
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

    protected function sanitize(string $s): string
    { 
        $s = trim(mb_convert_encoding(strip_tags($s),'UTF-8','UTF-8'));
        
        $s = preg_replace('/[\x{FFF0}-\x{FFFF}]/u', '', $s);
        $s = preg_replace('/[\x{2000}-\x{200F}]/u', '', $s);
        $s = preg_replace('/[\x{2028}-\x{202F}]/u', '', $s);
        $s = preg_replace('/[\x{2060}-\x{206F}]/u', '', $s);
        
        return $s;
    }

    protected function vis(string $s): string
    {
        if (!$this->rtl) return $s;

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
        
        $s = preg_replace('/[\x{FFF0}-\x{FFFF}]/u', '', $s);
        $s = preg_replace('/[\x{2000}-\x{200F}]/u', '', $s);
        $s = preg_replace('/[\x{2028}-\x{202F}]/u', '', $s);
        
        return $s;
    }

    private function truncate(string $text,int $size,string $font,int $maxW,string $ellipsis='',bool $rtl=false): string
    {
        $lo=0;$hi=mb_strlen($text);$best='';
        while($lo<=$hi){
            $mid=intdiv($lo+$hi,2);
            
            if ($this->rtl) {
                $sub=$ellipsis.mb_substr($text,-$mid);
            } else {
                $sub=mb_substr($text,0,$mid).$ellipsis;
            }
            
            if($this->textW($sub,$size,$font) <= $maxW){ $best=$sub; $lo=$mid+1; }
            else{ $hi=$mid-1; }
        }
        return $best ?: $ellipsis;
    }

    protected function drawProfileLogo(): void
    {
        if (!$this->role->profile_image_url) {
            return;
        }

        $logoSize = 80;
        $logoMargin = 20;
        
        if ($this->rtl) {
            $logoX = $logoMargin;
        } else {
            $logoX = $this->getWidth() - $logoMargin - $logoSize;
        }
        
        $logoY = $logoMargin;

        $profileImagePath = $this->getProfileImagePath();
        if (!$profileImagePath || !file_exists($profileImagePath)) {
            return;
        }

        $profileImg = $this->loadImage($profileImagePath);
        if ($profileImg === false) {
            return;
        }

        $origW = imagesx($profileImg);
        $origH = imagesy($profileImg);

        $this->drawRoundedLogo($profileImg, $logoX, $logoY, $logoSize, $origW, $origH);

        imagedestroy($profileImg);
    }

    protected function loadImage(string $path): GdImage|false
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'png':
                return imagecreatefrompng($path);
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($path);
            case 'gif':
                return imagecreatefromgif($path);
            case 'webp':
                if (function_exists('imagecreatefromwebp')) {
                    return imagecreatefromwebp($path);
                }
                break;
        }
        
        $imageData = file_get_contents($path);
        if ($imageData === false) {
            return false;
        }
        
        $img = imagecreatefromstring($imageData);
        if ($img !== false) {
            return $img;
        }
        
        return false;
    }

    protected function getProfileImagePath(): ?string
    {
        $profileUrl = $this->role->getAttributes()['profile_image_url'] ?? null;
        if (!$profileUrl) {
            return null;
        }

        if (config('filesystems.default') == 'local') {
            return storage_path('app/public/' . $profileUrl);
        } else {
            if (filter_var($profileUrl, FILTER_VALIDATE_URL)) {
                return $this->downloadRemoteImage($profileUrl);
            } else {
                $fullUrl = $this->role->profile_image_url;
                return $this->downloadRemoteImage($fullUrl);
            }
        }
    }

    protected function downloadRemoteImage(string $url): ?string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'profile_logo_');
        if ($tempFile === false) {
            return null;
        }

        $this->tempFiles[] = $tempFile;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'EventSchedule/1.0',
        ]);

        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($imageData === false || $httpCode !== 200) {
            unlink($tempFile);
            $this->tempFiles = array_filter($this->tempFiles, fn($f) => $f !== $tempFile);
            return null;
        }

        if (getimagesizefromstring($imageData) === false) {
            unlink($tempFile);
            $this->tempFiles = array_filter($this->tempFiles, fn($f) => $f !== $tempFile);
            return null;
        }

        if (file_put_contents($tempFile, $imageData) === false) {
            unlink($tempFile);
            $this->tempFiles = array_filter($this->tempFiles, fn($f) => $f !== $tempFile);
            return null;
        }

        return $tempFile;
    }

    protected function drawRoundedLogo(GdImage $sourceImg, int $x, int $y, int $size, int $origW, int $origH): void
    {
        $mask = imagecreatetruecolor($size, $size);
        imagealphablending($mask, false);
        imagesavealpha($mask, true);
        
        $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
        imagefill($mask, 0, 0, $transparent);
        
        $white = imagecolorallocate($mask, 255, 255, 255);
        $cornerRadius = 12;
        
        imagefilledrectangle($mask, $cornerRadius, 0, $size - $cornerRadius - 1, $size - 1, $white);
        imagefilledrectangle($mask, 0, $cornerRadius, $size - 1, $size - $cornerRadius - 1, $white);
        
        imagefilledellipse($mask, $cornerRadius, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $size - $cornerRadius - 1, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $cornerRadius, $size - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, $white);
        imagefilledellipse($mask, $size - $cornerRadius - 1, $size - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, $white);
        
        $dest = imagecreatetruecolor($size, $size);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        
        $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $transparent);
        
        imagecopyresampled($dest, $sourceImg, 0, 0, 0, 0, $size, $size, $origW, $origH);
        
        for ($i = 0; $i < $size; $i++) {
            for ($j = 0; $j < $size; $j++) {
                $alpha = imagecolorsforindex($mask, imagecolorat($mask, $i, $j));
                if ($alpha['red'] == 0) {
                    $color = imagecolorallocatealpha($dest, 0, 0, 0, 127);
                    imagesetpixel($dest, $i, $j, $color);
                }
            }
        }
        
        imagecopy($this->im, $dest, $x, $y, 0, 0, $size, $size);
        
        imagedestroy($mask);
        imagedestroy($dest);
    }

    protected function cleanupTempFiles(): void
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];
    }
}