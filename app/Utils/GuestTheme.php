<?php

namespace App\Utils;

use App\Models\Role;

/**
 * How a schedule's guest pages are coloured: one accent, worked out once into the handful of
 * colours a page needs from it.
 *
 * The guest pages used to paste the owner's accent inline wherever a colour was wanted, as a
 * button's fill, as link text, as a border, and each place made its own guess about contrast (or
 * none). This is the one place that decides, as App\Utils\EmailTheme is for mail:
 *
 *  - fill / onFill      the main button. The owner's accent as it is, with black or white on it
 *                       by the rule the pages have always used (accent_contrast_color()).
 *  - readable           the accent as TEXT, an icon or a thin border on a panel. Moved along its
 *                       own hue until it reads (4.5:1), so a yellow schedule gets a dark gold and
 *                       still looks like itself.
 *  - tint               a wash of the accent for a chosen chip or a quiet highlight.
 *  - edge               a hairline for a fill that has no edge of its own against the panel.
 *  - glow               the accent as a light on a dark ground (the ticket). "r g b", for
 *                       rgb(var(--es-glow) / .35).
 *  - glowInk            that light as ink on the dark ground: the ticket's title and icons.
 *
 * Each has a light and a dark value, because a panel is white in one and near-black in the other.
 *
 * Two accents are not used as they are, because as a button they do not read as one:
 *  - a GREY (or black, or white) with too little contrast against the panel. A mid grey button
 *    looks disabled, so the fill becomes ink on a light panel and paper on a dark one.
 *  - any accent the panel swallows: a near-black navy on the dark panel is lifted along its hue
 *    until it stands out.
 *
 * Printed as CSS custom properties by partials/guest-theme, on the guest layout's body, BEFORE the
 * owner's custom CSS, so an owner's rule still wins.
 */
final class GuestTheme
{
    public const DEFAULT_ACCENT = '#4e81fa';

    /** What a button's label is checked against when the accent is replaced. */
    public const INK = '#16181d';

    public const PAPER = '#f3f4f6';

    /** A panel and the well inside it: white and gray-50, then the dark fallbacks (--ap-bg, --ap-surface). */
    public const LIGHT_PANELS = ['#ffffff', '#f9fafb'];

    public const DARK_PANELS = ['#1e1e1e', '#252526'];

    /** Below this saturation an accent is a grey, whatever its hue says. */
    private const GREY_BELOW = 0.12;

    private function __construct(
        public readonly string $source,
        public readonly bool $neutral,
        public readonly string $fill,
        public readonly string $onFill,
        public readonly string $fillDark,
        public readonly string $onFillDark,
        public readonly string $edge,
        public readonly string $edgeDark,
        public readonly string $readable,
        public readonly string $readableDark,
        public readonly string $tint,
        public readonly string $tintDark,
        public readonly string $glow,
        public readonly string $glowInk,
    ) {}

    public static function for(?Role $role): self
    {
        return self::fromAccent($role?->accent_color);
    }

    public static function fromAccent(?string $accent): self
    {
        $source = ColorUtils::normalizeHex($accent) ?? self::DEFAULT_ACCENT;
        [$hue, $saturation, $lightness] = ColorUtils::toHsl($source);
        $neutral = $saturation < self::GREY_BELOW;

        $fill = $source;
        $fillDark = $source;

        if ($neutral) {
            // A grey reads as a button only when it is nearly the opposite of the panel.
            if (ColorUtils::getContrastRatio($source, self::LIGHT_PANELS[0]) < 7.0) {
                $fill = self::INK;
            }
            if (ColorUtils::getContrastRatio($source, self::DARK_PANELS[0]) < 7.0) {
                $fillDark = self::PAPER;
            }
        } elseif (ColorUtils::getContrastRatio($source, self::DARK_PANELS[0]) < 1.6) {
            // A colour the dark panel swallows: the same hue, lifted until it stands out.
            $fillDark = ColorUtils::shiftUntil($source, self::DARK_PANELS, false, 3.0) ?? self::PAPER;
        }

        $glow = $neutral ? '#94a3b8' : ColorUtils::fromHsl($hue, $saturation, max($lightness, 0.55));

        return new self(
            source: $source,
            neutral: $neutral,
            fill: $fill,
            onFill: ColorUtils::getContrastColor($fill),
            fillDark: $fillDark,
            onFillDark: ColorUtils::getContrastColor($fillDark),
            // A fill with no edge of its own against the panel (a cream on white) gets a hairline.
            edge: ColorUtils::getContrastRatio($fill, self::LIGHT_PANELS[0]) < 1.25 ? '#cbd5e1' : 'transparent',
            edgeDark: ColorUtils::getContrastRatio($fillDark, self::DARK_PANELS[0]) < 1.25 ? '#4b5563' : 'transparent',
            readable: ColorUtils::shiftUntil($source, self::LIGHT_PANELS, true) ?? self::INK,
            readableDark: ColorUtils::shiftUntil($source, self::DARK_PANELS, false) ?? self::PAPER,
            tint: ColorUtils::mix($source, self::LIGHT_PANELS[0], 0.12),
            tintDark: ColorUtils::mix($source, self::DARK_PANELS[0], 0.24),
            glow: implode(' ', ColorUtils::toRgb($glow)),
            // The light as INK on the dark ticket: a title, an icon. Mixed here rather than with
            // CSS color-mix(), which an older browser drops along with the whole declaration.
            glowInk: ColorUtils::mix($glow, '#ffffff', 0.62),
        );
    }

    /**
     * The schedule whose look an event page takes.
     *
     * An event on a curator's or a venue's schedule is shown in the look of its own venue or
     * performer when that schedule is a real one that chose a look. Three places used to decide
     * this with two different tests (the background asked for a claimed schedule with a
     * background, the buttons for a claimed one, the calendar's "today" for any accent at all),
     * so a page could wear one schedule's background under another's buttons.
     *
     * @param  object|null  $selectedGroup  the sub-schedule being viewed, if any
     */
    public static function lookRole(Role $role, ?Role $otherRole = null, ?object $selectedGroup = null): Role
    {
        if ($otherRole && $otherRole->isClaimed() && $otherRole->hasConfiguredBackground()) {
            return $otherRole;
        }

        if ($selectedGroup && ($selectedGroup->role ?? null) instanceof Role) {
            return $selectedGroup->role;
        }

        return $role;
    }

    /**
     * The custom properties, as two rule bodies: [light, dark].
     *
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    public function tokens(): array
    {
        return [
            [
                '--es-accent' => $this->fill,
                '--es-accent-text' => $this->onFill,
                '--es-accent-edge' => $this->edge,
                '--es-accent-readable' => $this->readable,
                '--es-accent-tint' => $this->tint,
                '--es-glow' => $this->glow,
                '--es-glow-ink' => $this->glowInk,
            ],
            [
                '--es-accent' => $this->fillDark,
                '--es-accent-text' => $this->onFillDark,
                '--es-accent-edge' => $this->edgeDark,
                '--es-accent-readable' => $this->readableDark,
                '--es-accent-tint' => $this->tintDark,
            ],
        ];
    }

    /**
     * The tokens as CSS for $selector and its dark twin. Every value is a colour this class made
     * (a hex, 'transparent' or three integers), never the owner's own string, so it is safe to
     * print inside a style element.
     */
    public function css(string $selector = 'body'): string
    {
        [$light, $dark] = $this->tokens();
        $body = fn (array $tokens) => implode(' ', array_map(fn ($name, $value) => $name.': '.$value.';', array_keys($tokens), $tokens));

        return $selector.' { '.$body($light).' }'."\n".'.dark '.$selector.' { '.$body($dark).' }';
    }
}
