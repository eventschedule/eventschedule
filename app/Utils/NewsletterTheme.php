<?php

namespace App\Utils;

/**
 * How one newsletter looks: which of the five designs it is, every colour it paints with, its
 * type, its shape and its direction.
 *
 * A newsletter is the one outgoing mail whose colours are the OWNER's (background, accent, text,
 * saved with it), so it cannot use EmailTheme's fixed slate palette. What it takes from EmailTheme
 * is the method: the three saved colours are the only inputs, and every other colour a block
 * paints with (the ground behind the sheet, a raised panel, hairlines, two quieter inks, the label
 * on an accent, an accent that reads as text) is derived here as a solid 6-digit hex and checked
 * to 4.5:1 against the surface it sits on. No block mixes a colour of its own, and none appends
 * an alpha byte to a hex: Outlook drops an 8-digit colour and the text turns black.
 *
 * That check has to hold for ANY three colours, a mid-grey or a pure red page included. On any
 * page either black or white reads at 4.58:1 or better, so the surfaces are derived first and
 * pulled back toward the page until that pole still reads on them (surface()), and every ink is
 * then moved along its own hue, in whichever direction gets there (readable()), with the pole as
 * the last resort. NewsletterDesignTest sweeps a grid of pages, accents and text colours.
 *
 * The design decides structure (whether there is a sheet, how a heading and an event read, how
 * loud a button is); the saved settings decide colour, typeface and corners. So Bold on a white
 * background is still one coherent page, which it was not while Bold hard-coded its own navy.
 */
final class NewsletterTheme
{
    public const DESIGNS = ['modern', 'classic', 'minimal', 'bold', 'compact'];

    /**
     * Unquoted, as EmailTheme::FONT is: a name made of identifiers is valid without quotes, and a
     * style="" attribute then needs no escaping. Each stack ends in its own generic family, where
     * the old `'Georgia', sans-serif` fell back to the wrong kind of face.
     */
    public const FONTS = [
        'System' => '-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif',
        'Arial' => 'Arial, Helvetica, sans-serif',
        'Helvetica' => 'Helvetica, Arial, sans-serif',
        'Verdana' => 'Verdana, Geneva, sans-serif',
        'Tahoma' => 'Tahoma, Verdana, sans-serif',
        'Trebuchet MS' => 'Trebuchet MS, Lucida Grande, Tahoma, sans-serif',
        'Georgia' => 'Georgia, Times New Roman, Times, serif',
        'Times New Roman' => 'Times New Roman, Times, serif',
        'Courier New' => 'Courier New, Courier, monospace',
    ];

    public const MONO = 'SFMono-Regular, Menlo, Consolas, Liberation Mono, Courier New, monospace';

    private const AA = 4.5;

    private function __construct(
        public readonly string $design,
        public readonly bool $sheeted,
        public readonly bool $dark,
        // Surfaces
        public readonly string $ground,
        public readonly string $sheet,
        public readonly string $panel,
        public readonly string $rule,
        public readonly string $ruleStrong,
        // Inks
        public readonly string $ink,
        public readonly string $ink2,
        public readonly string $ink3,
        public readonly string $footInk,
        public readonly string $groundInk,
        // Accent
        public readonly string $accent,
        public readonly string $onAccent,
        public readonly string $accentEdge,
        public readonly string $accentMark,
        public readonly string $accentInk,
        public readonly string $accentTint,
        public readonly string $discFill,
        public readonly string $ruleDouble,
        // Type
        public readonly string $font,
        public readonly bool $serif,
        public readonly string $numFont,
        public readonly ?string $outlookFont,
        public readonly int $body,
        public readonly int $bodyLine,
        public readonly int $small,
        public readonly int $smallLine,
        // Shape and rhythm
        public readonly int $radius,
        public readonly int $cardRadius,
        public readonly int $sheetRadius,
        public readonly int $gutter,
        public readonly int $inner,
        public readonly int $gap,
        public readonly int $para,
        public readonly int $top,
        // Direction
        public readonly string $dir,
        public readonly string $start,
        public readonly string $end,
        public readonly string $arrow,
    ) {}

    public static function make(?string $design, array $style, bool $rtl): self
    {
        $design = in_array($design, self::DESIGNS, true) ? $design : 'modern';
        $sheeted = in_array($design, ['modern', 'classic'], true);

        $bg = ColorUtils::normalizeHex($style['backgroundColor'] ?? null) ?? '#ffffff';
        $text = ColorUtils::normalizeHex($style['textColor'] ?? null) ?? '#333333';
        $base = ColorUtils::normalizeHex($style['accentColor'] ?? null) ?? '#4e81fa';

        // A page is dark when white reads better on it than black does.
        $dark = ColorUtils::getContrastRatio('#ffffff', $bg) > ColorUtils::getContrastRatio('#000000', $bg);
        $pole = $dark ? '#ffffff' : '#000000';

        // A raised panel: a shade down on a near-white page, a shade up on anything else, so it
        // reads as raised on cream, on grey and on navy alike.
        $panel = self::surface(match (true) {
            $dark => ColorUtils::mix('#ffffff', $bg, 0.07),
            ColorUtils::getLuminance($bg) > 0.93 => ColorUtils::mix($text, $bg, 0.035),
            default => ColorUtils::mix('#ffffff', $bg, 0.6),
        }, $bg, $pole);
        $tint = self::surface(ColorUtils::mix($base, $bg, $dark ? 0.2 : 0.1), $bg, $pole);

        // Text sits on all three: the page, a panel, and the tint (a date tile, an offer).
        $ink = self::readable($text, [$bg, $panel, $tint], $dark) ?? $pole;

        // Modern's ground carries a breath of the schedule's own colour, so the page is theirs
        // before a word is read; Classic's is the paper a shade deeper.
        $ground = match (true) {
            ! $sheeted => $bg,
            $dark => ColorUtils::mix($design === 'modern' ? $base : '#000000', ColorUtils::mix('#000000', $bg, 0.3), $design === 'modern' ? 0.08 : 0),
            $design === 'modern' => ColorUtils::mix($base, ColorUtils::mix($ink, $bg, 0.03), 0.07),
            default => ColorUtils::mix($ink, $bg, 0.06),
        };
        // The ground is its own surface: on a saturated page it can want the other pole.
        $groundDark = ColorUtils::getContrastRatio('#ffffff', $ground) > ColorUtils::getContrastRatio('#000000', $ground);
        $groundPole = $groundDark ? '#ffffff' : '#000000';

        [$accent, $onAccent] = self::fill($base);
        $accentInk = self::readable($base, [$bg, $panel, $tint], $dark) ?? $ink;

        $square = ($style['buttonRadius'] ?? 'rounded') === 'square';
        $fontName = array_key_exists($style['fontFamily'] ?? '', self::FONTS) ? $style['fontFamily'] : 'Arial';
        $compact = $design === 'compact';

        return new self(
            design: $design,
            sheeted: $sheeted,
            dark: $dark,
            ground: $ground,
            sheet: $bg,
            panel: $panel,
            rule: ColorUtils::mix($ink, $bg, 0.14),
            ruleStrong: ColorUtils::mix($ink, $bg, 0.3),
            ink: $ink,
            ink2: self::readable(ColorUtils::mix($ink, $bg, 0.84), [$bg, $panel, $tint], $dark) ?? $ink,
            ink3: self::readable(ColorUtils::mix($ink, $bg, 0.64), [$bg, $panel, $tint], $dark) ?? $ink,
            footInk: self::readable(ColorUtils::mix($ink, $ground, 0.62), [$ground], $groundDark) ?? $groundPole,
            // The schedule's name, where the masthead stands on the ground (Modern).
            groundInk: self::readable($ink, [$ground], $groundDark) ?? $groundPole,
            accent: $accent,
            onAccent: $onAccent,
            // A fill too close to the page it sits on gets an edge, or the button has no shape.
            accentEdge: ColorUtils::getContrastRatio($accent, $bg) < 1.6 ? ColorUtils::mix($ink, $bg, 0.3) : $accent,
            // A rule or a bar drawn in the accent. One the colour of the page would not be there.
            accentMark: ColorUtils::getContrastRatio($accent, $bg) < 1.6 ? $accentInk : $accent,
            accentInk: $accentInk,
            accentTint: $tint,
            // The social icons are white glyphs: the disc behind them is the accent where white
            // reads on it, else the accent darkened until it does (a yellow becomes its own olive).
            discFill: $onAccent === '#ffffff' ? $accent : (ColorUtils::shiftUntil($base, ['#ffffff'], true) ?? EmailTheme::INK),
            ruleDouble: ColorUtils::mix($ink, $bg, 0.5),
            font: self::FONTS[$fontName],
            serif: in_array($fontName, ['Georgia', 'Times New Roman'], true),
            // Georgia's figures hang below the line; a date tile's numeral is set in a sans face.
            numFont: in_array($fontName, ['Georgia', 'Times New Roman'], true) ? self::FONTS['Helvetica'] : self::FONTS[$fontName],
            // Outlook for Windows does not know "-apple-system" and sets a stack that opens with
            // it in Times New Roman. The shell names a face for it in an Outlook-only rule.
            outlookFont: $fontName === 'System' ? "'Segoe UI', Arial, sans-serif" : null,
            body: $compact ? 14 : ($design === 'classic' ? 17 : 16),
            bodyLine: $compact ? 21 : ($design === 'classic' ? 28 : 26),
            small: $compact ? 12 : 14,
            smallLine: $compact ? 17 : 20,
            radius: $square ? 0 : ['modern' => 10, 'classic' => 4, 'minimal' => 6, 'bold' => 10, 'compact' => 6][$design],
            cardRadius: $square ? 0 : ['modern' => 14, 'classic' => 4, 'minimal' => 0, 'bold' => 14, 'compact' => 8][$design],
            sheetRadius: $square || ! $sheeted ? 0 : ['modern' => 18, 'classic' => 6][$design],
            gutter: $gutter = ['modern' => 40, 'classic' => 44, 'minimal' => 28, 'bold' => 32, 'compact' => 24][$design],
            // What is left between the gutters of the 600px column; a sheet's hairline takes two more.
            inner: 600 - 2 * $gutter - ($sheeted ? 2 : 0),
            gap: $compact ? 16 : 28,
            // The space under a paragraph, a list or a quote inside a Text block.
            para: $compact ? 10 : 16,
            top: $compact ? 20 : 36,
            dir: $rtl ? 'rtl' : 'ltr',
            start: $rtl ? 'right' : 'left',
            end: $rtl ? 'left' : 'right',
            arrow: $rtl ? '&larr;' : '&rarr;',
        );
    }

    /** font-family, size, line height and colour for one run of text, as a style fragment. */
    public function type(int $size, int $line, string $colour, int|string $weight = 400): string
    {
        return "font-family: {$this->font}; font-size: {$size}px; line-height: {$line}px; font-weight: {$weight}; color: {$colour};";
    }

    public function bodyType(?string $colour = null): string
    {
        return $this->type($this->body, $this->bodyLine, $colour ?? $this->ink2);
    }

    public function smallType(?string $colour = null, int|string $weight = 400): string
    {
        return $this->type($this->small, $this->smallLine, $colour ?? $this->ink3, $weight);
    }

    /**
     * A small label: a date line, a section label, a tier. Spaced capitals in a sans face; in a
     * serif one an italic line in sentence case, because Georgia's figures sit badly in capitals.
     */
    public function labelType(?string $colour = null, int $size = 12): string
    {
        if ($this->serif) {
            return $this->type($size + 3, $size + 8, $colour ?? $this->accentInk, 400).$this->italic();
        }

        return $this->type($size, $size + 6, $colour ?? $this->accentInk, 700).$this->tracking(0.08).' text-transform: uppercase;';
    }

    /**
     * Letter-spacing, for capitals. None in a right-to-left mail: spacing pulls Arabic's joined
     * letters apart, and neither Arabic nor Hebrew has capitals for it to open up.
     */
    public function tracking(float $em): string
    {
        return $this->dir === 'rtl' ? '' : " letter-spacing: {$em}em;";
    }

    /** A date tile's numeral. */
    public function numType(int $size, int $line, ?string $colour = null): string
    {
        return "font-family: {$this->numFont}; font-size: {$size}px; line-height: {$line}px; font-weight: 700; color: ".($colour ?? $this->ink).';';
    }

    /** Italic, where the script has one: a slanted Hebrew or Arabic line is a rendering fault. */
    public function italic(): string
    {
        return $this->dir === 'rtl' ? '' : ' font-style: italic;';
    }

    /**
     * Owner-written HTML from a Text block, with the page's own spacing and colours written onto
     * each bare tag.
     *
     * Markdown arrives as bare <p>, <a>, <h2>, <ul>, <blockquote>, <pre>, and a mail client gives
     * each its own defaults: a link in browser blue (unreadable on a dark page), a 1em gap above
     * the first paragraph, a quote that is only an indent, and a <pre> (which is what a paragraph
     * indented four spaces becomes) that does not wrap and lays the whole mail out as wide as its
     * longest line. Inline, so it holds where a client throws the <style> block away. A tag that
     * already carries a style is left alone.
     *
     * One pass over the tags and nothing else. Every block element keeps its gap, the last one
     * too: the Text block's own cell allows for it (gap - para). Taking the last element's gap
     * off needed a pattern that walked to the end of the text a character at a time, and on a
     * long block preg_replace() answered null, which this method's return type turned into an
     * Error the send job does not catch.
     */
    public function prose(string $html): string
    {
        $pad = "padding-{$this->start}";
        $gap = "margin: 0 0 {$this->para}px;";
        $upright = $this->dir === 'rtl' ? 'font-style: normal;' : null;
        $styles = [
            'p' => $gap,
            'a' => "color: {$this->accentInk}; text-decoration: underline;",
            'h1' => $this->type($this->body + 6, $this->bodyLine + 4, $this->ink, 700).' margin: 0 0 12px;',
            'h2' => $this->type($this->body + 3, $this->bodyLine + 1, $this->ink, 700).' margin: 8px 0 10px;',
            'h3' => $this->type($this->body + 1, $this->bodyLine, $this->ink, 700).' margin: 8px 0 8px;',
            'h4' => $this->type($this->body, $this->bodyLine, $this->ink, 700).' margin: 8px 0 8px;',
            'h5' => $this->type($this->body, $this->bodyLine, $this->ink, 700).' margin: 8px 0 8px;',
            'h6' => $this->type($this->body, $this->bodyLine, $this->ink, 700).' margin: 8px 0 8px;',
            'ul' => "{$gap} {$pad}: 22px;",
            'ol' => "{$gap} {$pad}: 22px;",
            'li' => 'margin: 0 0 6px;',
            'strong' => "color: {$this->ink};",
            'b' => "color: {$this->ink};",
            // Hebrew and Arabic have no italic: a slanted line there is a rendering fault.
            'em' => $upright,
            'i' => $upright,
            // 1px under the quoted paragraph's own gap, or that gap collapses out through the panel.
            'blockquote' => "{$gap} padding: 16px 18px 1px; background-color: {$this->panel}; border-radius: {$this->cardRadius}px;{$this->italic()} color: {$this->ink};",
            'pre' => "{$gap} padding: 12px 14px; background-color: {$this->panel}; border-radius: {$this->cardRadius}px; white-space: pre-wrap; word-break: break-word; font-family: ".self::MONO.';',
            'hr' => "border: 0; border-top: 1px solid {$this->rule}; height: 0; margin: 4px 0 {$this->para}px;",
            'img' => "max-width: 100%; height: auto; border: 0; border-radius: {$this->cardRadius}px;",
            'code' => 'font-family: '.self::MONO."; font-size: 0.92em; background-color: {$this->panel}; padding: 1px 4px;",
        ];

        $inLink = 0;

        $styled = preg_replace_callback('/<(\/?)(p|a|h[1-6]|ul|ol|li|strong|b|em|i|blockquote|pre|hr|img|code)\b([^>]*)>/i', function ($m) use ($styles, &$inLink) {
            $tag = strtolower($m[2]);

            if ($m[1] === '/') {
                $inLink -= $tag === 'a' && $inLink > 0 ? 1 : 0;

                return $m[0];
            }

            $inLink += $tag === 'a' ? 1 : 0;

            // Bold inside a link keeps the link's colour.
            $style = $inLink > 0 && in_array($tag, ['strong', 'b'], true) ? null : $styles[$tag];

            // " style=", and not any "style=": a link to "?style=vintage" has no style of its own.
            if ($style === null || preg_match('/\sstyle\s*=/i', $m[3])) {
                return $m[0];
            }

            $attrs = rtrim($m[3]);
            $selfClosing = str_ends_with($attrs, '/');

            return '<'.$m[2].($selfClosing ? rtrim(substr($attrs, 0, -1)) : $attrs).' style="'.$style.'"'.($selfClosing ? ' /' : '').'>';
        }, $html);

        // Never nothing: if the pattern cannot be run, the text goes out as it came.
        return $styled ?? $html;
    }

    /** The dot between two facts on one line. */
    public function dot(): string
    {
        // An ordinary space last, so a line of several facts can break after a dot. Glued on both
        // sides, "zaterdag · 20:00 · Paradiso · Wekelijks · Gratis" was one unbreakable run and
        // laid a list row out wider than a phone.
        return '<span style="color: '.$this->ruleStrong.';">&nbsp;&nbsp;&middot;&nbsp;</span> ';
    }

    /**
     * A clock time, kept left to right. Bare in a Hebrew or Arabic line, "10:00 PM" is laid out
     * as "PM 10:00".
     */
    public function ltr(?string $time): string
    {
        return filled($time) ? '<span dir="ltr">'.e($time).'</span>' : '';
    }

    /** "Sat, Oct 11 · 8:00 PM", for the label line above an event's name. Empty when it has neither. */
    public function when(?string $day, ?string $time): string
    {
        return implode('&nbsp;&nbsp;&middot;&nbsp; ', array_filter([e($day ?? ''), $this->ltr($time)]));
    }

    /**
     * The quiet line under an event's name, from a row NewsletterService resolved: where (never
     * the sender's own name), how often, what it costs, and whether few are left.
     */
    public function eventMeta(array $e): string
    {
        return implode($this->dot(), $this->eventFacts($e));
    }

    /**
     * The one line a list row carries. Its date is on the tile beside it, so the line opens with
     * the weekday; a festival says its days instead.
     */
    public function eventDetail(array $e): string
    {
        return implode($this->dot(), array_filter([
            e(($e['multiDay'] ? $e['date'] : $e['weekday']) ?? ''),
            $this->ltr($e['time']),
            ...$this->eventFacts($e),
        ]));
    }

    /** @return string[] */
    private function eventFacts(array $e): array
    {
        return array_values(array_filter([
            filled($e['venue']) ? '<span dir="auto">'.e($e['venue']).'</span>' : '',
            e($e['repeat'] ?? ''),
            filled($e['price']) ? '<span style="font-weight: 700; color: '.($e['soldOut'] ? $this->ink3 : $this->ink).';">'.e($e['price']).'</span>' : '',
            filled($e['low']) ? '<span style="font-weight: 700; color: '.$this->accentInk.';">'.e($e['low']).'</span>' : '',
        ]));
    }

    /**
     * [fill, label] for a button or a band in the accent.
     *
     * White if it reads; dark ink on a light accent (a yellow, a lime), where white never could;
     * otherwise the accent darkened along its own hue just far enough for white to pass. The same
     * rule as EmailTheme::buttonColours(), so a schedule's newsletter button and its ticket mail
     * button are one colour.
     */
    private static function fill(string $accent): array
    {
        if ($accent === EmailTheme::BRAND) {
            return [EmailTheme::BRAND_BUTTON, '#ffffff'];
        }

        if (ColorUtils::getContrastRatio('#ffffff', $accent) >= self::AA) {
            return [$accent, '#ffffff'];
        }

        if (ColorUtils::getLuminance($accent) > 0.4 && ColorUtils::getContrastRatio(EmailTheme::INK, $accent) >= self::AA) {
            return [$accent, EmailTheme::INK];
        }

        return [ColorUtils::shiftUntil($accent, ['#ffffff'], true) ?? EmailTheme::INK, '#ffffff'];
    }

    /**
     * $candidate, pulled back toward the page until $pole (the black or white that reads on the
     * page) reads on it too. A panel a shade lighter than a mid-grey page, or a tint of a white
     * accent on it, is otherwise a surface nothing can be read on.
     */
    private static function surface(string $candidate, string $page, string $pole): string
    {
        foreach ([1.0, 0.75, 0.5, 0.25] as $weight) {
            $surface = ColorUtils::mix($candidate, $page, $weight);

            if (ColorUtils::getContrastRatio($pole, $surface) >= self::AA) {
                return $surface;
            }
        }

        return $page;
    }

    /**
     * $colour moved along its own hue until it reads on every surface: away from the page first,
     * then the other way, because a surface derived from a page can sit on the far side of it (a
     * dark ground under a mid-tone page). Null when neither direction gets there.
     */
    private static function readable(string $colour, array $surfaces, bool $dark): ?string
    {
        return ColorUtils::shiftUntil($colour, $surfaces, ! $dark, self::AA)
            ?? ColorUtils::shiftUntil($colour, $surfaces, $dark, self::AA);
    }
}
