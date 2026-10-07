<?php

namespace App\Utils;

use App\Models\Role;

/**
 * How one outgoing email looks: whose voice it speaks in, its colours, and its direction.
 *
 * An email view builds one and hands it to <x-email.layout :theme="$theme">, and every
 * <x-email.*> component inside reads it back through @aware. It is built in the VIEW, at render
 * time, never in a Mailable's constructor: the constructor runs in the dispatcher's locale, the
 * view in the recipient's, and the direction and lang below must be the recipient's.
 *
 * Three voices:
 *  - guest:   mail a schedule sends its own audience (tickets, appointments, announcements). The
 *             schedule's logo, name and accent colour, and never the platform's name - putting our
 *             name into a schedule's outgoing mail is a decision that has not been made
 *             (docs/BRANDING_MATRIX.md rule 5), so $appName is null and no component can print it.
 *  - owner:   mail the platform sends a schedule's owner about that schedule. The schedule's logo
 *             and name, so an owner of several can tell at a glance which one a "New sale" is
 *             about, in the platform's colours.
 *  - account: mail the platform sends a person about their account. The app's name as a wordmark.
 *
 * Every colour a component paints text or a button with comes from here, checked to WCAG AA
 * (4.5:1) against the surface it sits on, in light and in dark mode. An owner's accent is darkened
 * along its own hue until it passes, rather than swapped for ours, so the mail still reads as theirs.
 */
final class EmailTheme
{
    public const BRAND = '#4e81fa';

    /** The AP's --brand-blue-dark. White on it is 4.53:1; white on BRAND is 3.59:1, which fails. */
    public const BRAND_BUTTON = '#3d6fe8';

    public const WHITE = '#ffffff';

    public const INK = '#0f172a';

    public const CARD_DARK = '#111827';

    /** The raised panel inside the card (the event block, details), light and dark. */
    public const PANEL = '#f8fafc';

    public const PANEL_DARK = '#1a2332';

    public const LINK_DARK_FALLBACK = '#93c5fd';

    public const AA = 4.5;

    /** Unquoted (valid for names made of identifiers), so a style="" attribute needs no escaping. */
    public const FONT = '-apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif';

    public const MONO = 'SFMono-Regular, Menlo, Consolas, Liberation Mono, Courier New, monospace';

    /**
     * State colours: eyebrow ink, callout background and border, light then dark. The light values
     * are CLAUDE.md's callout palette; every ink is at least 4.5:1 on white and on its own tint.
     */
    public const TONES = [
        'danger' => ['ink' => '#b91c1c', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'dark_ink' => '#fca5a5', 'dark_bg' => '#2a1416', 'dark_border' => '#7f1d1d'],
        'warning' => ['ink' => '#b45309', 'bg' => '#fffbeb', 'border' => '#fcd34d', 'dark_ink' => '#fcd34d', 'dark_bg' => '#2a2111', 'dark_border' => '#78350f'],
        'success' => ['ink' => '#15803d', 'bg' => '#f0fdf4', 'border' => '#86efac', 'dark_ink' => '#86efac', 'dark_bg' => '#10241a', 'dark_border' => '#14532d'],
        'info' => ['ink' => '#1d4ed8', 'bg' => '#eff6ff', 'border' => '#bfdbfe', 'dark_ink' => '#93c5fd', 'dark_bg' => '#111d35', 'dark_border' => '#1e3a8a'],
        'neutral' => ['ink' => '#475569', 'bg' => '#f8fafc', 'border' => '#e2e8f0', 'dark_ink' => '#cbd5e1', 'dark_bg' => '#1a2332', 'dark_border' => '#273244'],
    ];

    /** The identity tile's side, and a wide logo's box inside its own tile (6px top and bottom). */
    private const TILE = 44;

    private const LOGO_HEIGHT = 32;

    private const LOGO_MAX_WIDTH = 140;

    private function __construct(
        public readonly string $voice,
        public readonly ?string $senderName,
        public readonly ?string $avatarUrl,
        public readonly int $avatarWidth,
        public readonly int $avatarHeight,
        public readonly bool $avatarWide,
        public readonly ?string $initial,
        public readonly ?string $appName,
        public readonly string $accent,
        public readonly string $onAccent,
        public readonly string $accentBorder,
        public readonly string $accentInk,
        public readonly string $accentTint,
        public readonly string $darkTint,
        public readonly string $darkLink,
        public readonly bool $darkRing,
        public readonly string $dir,
        public readonly string $start,
        public readonly string $end,
        public readonly string $lang,
    ) {}

    /** Mail a schedule sends its own audience. A null schedule shows no sender row at all. */
    public static function guest(?Role $role): self
    {
        return self::build('guest', $role, $role?->accent_color, null);
    }

    /** Mail the platform sends a schedule's owner about that schedule. */
    public static function owner(?Role $role): self
    {
        return self::build('owner', $role, null, (string) config('app.name'));
    }

    /** Mail the platform sends a person about their account. */
    public static function account(): self
    {
        return self::build('account', null, null, (string) config('app.name'));
    }

    /** Ink, background and border for a state, light and dark; unknown tones read as neutral. */
    public function tone(?string $tone): array
    {
        return self::TONES[self::toneName($tone)];
    }

    /** A known tone's name, so a class like es-ink-{name} always has dark-mode rules behind it. */
    public static function toneName(?string $tone): string
    {
        return array_key_exists((string) $tone, self::TONES) ? $tone : 'neutral';
    }

    /**
     * A 6-digit lowercase hex, or null. Role::cssHexColor() lets 3- and 8-digit forms through, and
     * ColorUtils::getLuminance() reads only the first six characters, so '#abc' would measure as
     * the colour 0x0abc.. and an alpha byte would be silently ignored. Alpha is dropped: there is
     * no backdrop in an email to blend it against.
     */
    public static function normalizeHex(?string $hex): ?string
    {
        return ColorUtils::normalizeHex($hex);
    }

    private static function build(string $voice, ?Role $role, ?string $accent, ?string $appName): self
    {
        $dir = is_rtl() ? 'rtl' : 'ltr';
        $base = self::normalizeHex($accent) ?? self::BRAND;

        [$button, $onButton] = self::buttonColours($base);
        $tint = self::mix($base, self::WHITE, 0.10);
        $darkTint = self::mix($base, self::CARD_DARK, 0.18);

        $senderName = $voice === 'account' ? $appName : ($role ? trim((string) $role->name) : null);
        [$avatarUrl, $width, $height, $wide] = self::avatar($voice === 'account' ? null : $role);

        return new self(
            voice: $voice,
            senderName: ($senderName ?? '') !== '' ? $senderName : null,
            avatarUrl: $avatarUrl,
            avatarWidth: $width,
            avatarHeight: $height,
            avatarWide: $wide,
            initial: ($senderName ?? '') !== '' ? self::initial($senderName) : null,
            appName: $appName !== '' ? $appName : null,
            accent: $button,
            onAccent: $onButton,
            // A near-white button would have no edge on the white card.
            accentBorder: ColorUtils::getContrastRatio($button, self::WHITE) < 1.5 ? '#cbd5e1' : $button,
            accentInk: self::darkenUntil($base, [self::WHITE, self::PANEL, $tint, ...array_column(self::TONES, 'bg')]),
            accentTint: $tint,
            darkTint: $darkTint,
            darkLink: self::lightenUntil($base, [self::CARD_DARK, self::PANEL_DARK, $darkTint, ...array_column(self::TONES, 'dark_bg')]) ?? self::LINK_DARK_FALLBACK,
            darkRing: ColorUtils::getContrastRatio($button, self::CARD_DARK) < 3.0,
            dir: $dir,
            start: $dir === 'rtl' ? 'right' : 'left',
            end: $dir === 'rtl' ? 'left' : 'right',
            lang: str_replace('_', '-', app()->getLocale()),
        );
    }

    /**
     * [background, label] for a button on $accent.
     *
     * White if it reads; dark ink on a light accent (yellows, limes) where white never could;
     * otherwise the accent darkened just far enough for white to pass. Our own blue short-circuits
     * to the AP's darker button blue, so a platform button matches a colour the product already uses.
     */
    private static function buttonColours(string $accent): array
    {
        if ($accent === self::BRAND) {
            return [self::BRAND_BUTTON, self::WHITE];
        }

        if (ColorUtils::getContrastRatio(self::WHITE, $accent) >= self::AA) {
            return [$accent, self::WHITE];
        }

        if (ColorUtils::getLuminance($accent) > 0.4 && ColorUtils::getContrastRatio(self::INK, $accent) >= self::AA) {
            return [$accent, self::INK];
        }

        return [self::darkenUntil($accent, [self::WHITE]), self::WHITE];
    }

    /**
     * [url, width, height, wide] for the sender row's picture, or nulls for none.
     *
     * The 480px derivative is WebP, which Outlook for Windows cannot show; the layout gives Outlook
     * the initial tile instead, so the derivative is safe to prefer everywhere else. A near-square
     * picture is fitted inside the 44px tile; a wide one (a logo with the name in it) gets a wider
     * tile of its own at logo height, up to 140px. A size of 0x0 means none was recorded (an
     * upload from before the variant pipeline): the sender row then lets the picture keep its own
     * shape inside the tile rather than forcing it square.
     */
    private static function avatar(?Role $role): array
    {
        $url = $role ? trim((string) $role->getProfileImageUrl(480)) : '';

        if ($url === '') {
            return [null, 0, 0, false];
        }

        $size = $role->imageSourceDimensions();

        if (! $size) {
            return [$url, 0, 0, false];
        }

        $ratio = $size[0] / $size[1];

        if ($ratio > 1.25) {
            $width = min(self::LOGO_MAX_WIDTH, (int) round(self::LOGO_HEIGHT * $ratio));

            return [$url, $width, max(1, (int) round($width / $ratio)), true];
        }

        // Inside a 1px-bordered tile, so the picture itself is two pixels smaller.
        $box = self::TILE - 2;

        return $ratio >= 1.0
            ? [$url, $box, max(1, (int) round($box / $ratio)), false]
            : [$url, max(1, (int) round($box * $ratio)), $box, false];
    }

    /**
     * The character a reader would recognise the name by: its first letter or digit, so "@jazz"
     * and a quoted "\"Blue\" Note" read as J and B, else its first grapheme.
     */
    private static function initial(string $name): ?string
    {
        if (preg_match('/[\p{L}\p{N}]/u', $name, $m)) {
            return mb_strtoupper($m[0]);
        }

        $first = function_exists('grapheme_substr') ? grapheme_substr($name, 0, 1) : mb_substr($name, 0, 1);

        return ($first === false || $first === '') ? null : $first;
    }

    /** $colour darkened along its own hue until it reaches 4.5:1 against every background. */
    private static function darkenUntil(string $colour, array $backgrounds): string
    {
        [$h, $s, $l] = self::toHsl($colour);

        for ($candidate = $colour; $l > 0; $l = max(0, $l - 0.01)) {
            $candidate = self::fromHsl($h, $s, $l);

            if (self::passesAll($candidate, $backgrounds)) {
                return $candidate;
            }
        }

        return self::INK;
    }

    /** $colour lightened along its own hue until it reaches 4.5:1 against every background, or null. */
    private static function lightenUntil(string $colour, array $backgrounds): ?string
    {
        [$h, $s, $l] = self::toHsl($colour);

        for (; $l <= 1; $l += 0.01) {
            $candidate = self::fromHsl($h, $s, min(1, $l));

            if (self::passesAll($candidate, $backgrounds)) {
                return $candidate;
            }
        }

        return null;
    }

    private static function passesAll(string $colour, array $backgrounds): bool
    {
        foreach ($backgrounds as $background) {
            if (ColorUtils::getContrastRatio($colour, $background) < self::AA) {
                return false;
            }
        }

        return true;
    }

    /**
     * The colour maths below is ColorUtils', shared with GuestTheme. These names stay so the
     * rules above read as they always have.
     */
    private static function mix(string $colour, string $base, float $weight): string
    {
        return ColorUtils::mix($colour, $base, $weight);
    }

    private static function toHsl(string $hex): array
    {
        return ColorUtils::toHsl($hex);
    }

    private static function fromHsl(float $h, float $s, float $l): string
    {
        return ColorUtils::fromHsl($h, $s, $l);
    }
}
