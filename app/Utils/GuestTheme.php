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
 *  - stage, raised, well the page's own ground (one surface every guest page sits on, over the
 *                       schedule's background), a card raised on it, and a sunken field. A
 *                       paper in light mode and a deep ink in dark, each faintly in the accent's
 *                       hue, so a schedule's pages carry its colour without a coloured panel.
 *  - stageInk 1..3      text on those three, each at least 4.5:1 on all of them.
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

    /**
     * Every ground the accent is read on as text: a panel, the page's gray-50, and the kit's own
     * well (gray-100 light, gray-700 dark, --gk-well). The accent's own tint is added per theme.
     */
    public const LIGHT_PANELS = ['#ffffff', '#f9fafb', '#f3f4f6'];

    public const DARK_PANELS = ['#1e1e1e', '#252526', '#2d2d30'];

    /**
     * Below this chroma (the spread between a colour's strongest and weakest channel, 0 to 1) an
     * accent is a grey. Chroma and not HSL saturation, which a near-white or near-black inflates:
     * by saturation #e5e7eb was a colour and stayed a pale grey button, and #010000 was a red.
     */
    private const GREY_BELOW = 0.08;

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
        public readonly array $stageLight = [],
        public readonly array $stageDark = [],
    ) {}

    /**
     * The stage's surfaces and inks for one mode, in the accent's hue. A grey accent gets a
     * neutral cool grey, never the accident of its rounding.
     *
     * @return array{stage: string, raised: string, well: string, ink: string, ink2: string, ink3: string}
     */
    private static function stage(float $hue, float $saturation, bool $neutral, bool $dark): array
    {
        $h = $neutral ? 0.6 : $hue;
        $s = $neutral ? 0.06 : min($saturation, $dark ? 0.3 : 0.5);

        if ($dark) {
            // One deep ink-blue for every schedule, as the owner's mock draws it (#0d1a22): the
            // schedule's colour is the glow, the button and the date line, never a muddy ground
            // (a yellow accent made the ink an olive).
            $stage = ColorUtils::fromHsl(0.56, 0.45, 0.092);
            $raised = ColorUtils::fromHsl(0.56, 0.34, 0.135);
            $well = ColorUtils::fromHsl(0.56, 0.3, 0.17);
            [$h, $s] = [0.55, 0.45];
            $grounds = [$stage, $raised, $well];

            return [
                'stage' => $stage,
                'raised' => $raised,
                'well' => $well,
                'ink' => ColorUtils::shiftUntil(ColorUtils::fromHsl($h, $s * 0.3, 0.96), $grounds, false, 7.0) ?? '#f5f5f5',
                'ink2' => ColorUtils::shiftUntil(ColorUtils::fromHsl($h, $s * 0.25, 0.83), $grounds, false, 5.5) ?? '#e5e5e5',
                'ink3' => ColorUtils::shiftUntil(ColorUtils::fromHsl($h, $s * 0.2, 0.68), $grounds, false) ?? '#d4d4d4',
            ];
        }

        $stage = ColorUtils::fromHsl($h, $s * 0.45, 0.982);
        $raised = '#ffffff';
        $well = ColorUtils::fromHsl($h, $s * 0.4, 0.95);
        $grounds = [$stage, $raised, $well];

        return [
            'stage' => $stage,
            'raised' => $raised,
            'well' => $well,
            'ink' => ColorUtils::shiftUntil(ColorUtils::fromHsl($h, $s * 0.35, 0.1), $grounds, true, 7.0) ?? '#111111',
            'ink2' => ColorUtils::shiftUntil(ColorUtils::fromHsl($h, $s * 0.2, 0.28), $grounds, true, 5.5) ?? '#333333',
            'ink3' => ColorUtils::shiftUntil(ColorUtils::fromHsl($h, $s * 0.15, 0.4), $grounds, true) ?? '#555555',
        ];
    }

    public static function for(?Role $role): self
    {
        return self::fromAccent($role?->accent_color);
    }

    public static function fromAccent(?string $accent): self
    {
        $source = ColorUtils::normalizeHex($accent) ?? self::DEFAULT_ACCENT;
        [$hue, $saturation, $lightness] = ColorUtils::toHsl($source);
        $channels = ColorUtils::toRgb($source);
        $neutral = (max($channels) - min($channels)) / 255 < self::GREY_BELOW;

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
        // A grey is moved as a grey. Its hue is an accident of rounding (#010000 is "red"), and
        // lightening along it turned a near-black into a pink.
        $text = $neutral ? ColorUtils::fromHsl(0.0, 0.0, $lightness) : $source;
        $tint = ColorUtils::mix($source, self::LIGHT_PANELS[0], 0.12);
        $tintDark = ColorUtils::mix($source, self::DARK_PANELS[0], 0.24);
        $stageLight = self::stage($hue, $saturation, $neutral, false);
        $stageDark = self::stage($hue, $saturation, $neutral, true);

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
            // On its own tint as well: that pair is the accent chip, and a readable that was
            // only checked against white fell to 4:1 there.
            // And on the stage's three grounds, where the date line and the section links use it.
            readable: ColorUtils::shiftUntil($text, [...self::LIGHT_PANELS, $tint, $stageLight['stage'], $stageLight['well']], true) ?? self::INK,
            readableDark: ColorUtils::shiftUntil($text, [...self::DARK_PANELS, $tintDark, $stageDark['stage'], $stageDark['raised'], $stageDark['well']], false) ?? self::PAPER,
            tint: $tint,
            tintDark: $tintDark,
            glow: implode(' ', ColorUtils::toRgb($glow)),
            // The light as INK on the dark ticket: a title, an icon. Mixed here rather than with
            // CSS color-mix(), which an older browser drops along with the whole declaration.
            glowInk: ColorUtils::mix($glow, '#ffffff', 0.62),
            stageLight: $stageLight,
            stageDark: $stageDark,
        );
    }

    /**
     * The schedule whose look an event page takes.
     *
     * An event on a curator's or a venue's schedule is shown in the look of its own venue or
     * performer when that schedule is a real one that chose a look. Three places decide this
     * today, each with its own test: the layout's background asks for a claimed schedule with a
     * background, the event page's buttons for a claimed one, the calendar's "today" for any
     * accent at all. So a page can wear one schedule's background under another's buttons. This
     * is the one rule (the layout's, the strictest); the tokens already follow it, and each of
     * those three places moves onto it as its page moves onto the kit. Until then, on the rare
     * claimed schedule that never chose a background, the tokens say this page's schedule while
     * the inline buttons still say the other one.
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
     * The portal's six palettes (config('app.ap_palettes'), resources/css/app.css), for a guest
     * page whose owner picked one (Style > Background > Page style): its brightness, the page's
     * ground, a card raised on it, a sunken field, and three inks.
     */
    public const PALETTES = [
        'sand' => ['light', '#f6f4ef', '#ffffff', '#efebe1', '#1a1814', '#4a4540', '#857f73'],
        'mist' => ['light', '#eceef2', '#ffffff', '#e3e7ee', '#16171a', '#444649', '#7a7e85'],
        'paper' => ['light', '#ffffff', '#fafaf9', '#f0f0ef', '#18181a', '#45454a', '#7c7c82'],
        'espresso' => ['dark', '#15140f', '#1f1e18', '#2a2820', '#f6f4ef', '#c8c2b5', '#a39d91'],
        'midnight' => ['dark', '#0f1115', '#181b21', '#22262e', '#eceef2', '#b7bcc8', '#979ca8'],
        'carbon' => ['dark', '#000000', '#0e0e0e', '#1a1a1a', '#f2f2f2', '#b8b8b8', '#9a9a9a'],
    ];

    /** The schedule whose look the page wears (the layout's rule), for pageMode() and pagePalette(). */
    private static function lookOf(Role $role, ?Role $otherRole, $event): Role
    {
        return ($event && $otherRole && $otherRole->isClaimed() && $otherRole->hasConfiguredBackground()) ? $otherRole : $role;
    }

    /**
     * The hosts of the guest pages this page links to that sit on a domain of their own (a
     * schedule's custom domain): a link there carries the visitor's light or dark in its
     * fragment (theme-script), since the cookie cannot follow. Links under the platform's own
     * domain need nothing, and the script never decorates a link to any other site.
     */
    public static function carryHosts(?Role $role, ?Role $otherRole = null, $event = null): array
    {
        $roles = collect([$role, $otherRole]);
        if ($event && $event->relationLoaded('roles')) {
            $roles = $roles->merge($event->roles);
        }
        $base = function_exists('_base_domain') ? _base_domain() : '';
        $hosts = [];
        foreach ($roles->filter() as $r) {
            // Only a domain that draws the page itself reads the fragment: a redirect-mode domain
            // 301s to the subdomain (the cookie's own ground), and is never printed on a page
            // whose canonical is the subdomain (CustomDomainCanonicalTest).
            if (! $r->exists || ! $r->servesOnCustomDomain()) {
                continue;
            }
            $host = parse_url((string) $r->getGuestUrl(true), PHP_URL_HOST);
            if ($host && ! ($base !== '' && ($host === $base || str_ends_with($host, '.'.$base)))) {
                $hosts[$host] = true;
            }
        }

        return array_keys($hosts);
    }

    /** The palette the owner picked, or null for Automatic. */
    public static function pagePalette(Role $role, ?Role $otherRole = null, $event = null): ?string
    {
        $bg = self::lookOf($role, $otherRole, $event);
        $chosen = $bg->exists ? $bg->guestPageSetting->page_mode : 'auto';

        return isset(self::PALETTES[$chosen]) ? $chosen : null;
    }

    /**
     * A picked palette's stage tokens, as CSS for body (the page is forced to the palette's own
     * mode, so one rule). Every ink, and the accent as text, is held to 4.5:1 or better on the
     * palette's three grounds. Only colours made here, never the owner's own string.
     */
    public function paletteCss(string $palette): string
    {
        [$mode, $stage, $raised, $well, $ink, $ink2, $ink3] = self::PALETTES[$palette];
        $grounds = [$stage, $raised, $well];
        $darker = $mode === 'light';
        $fix = fn (string $c, float $r) => ColorUtils::shiftUntil($c, $grounds, $darker, $r) ?? ($darker ? '#111111' : '#f5f5f5');
        $tokens = [
            '--gk-stage' => $stage,
            '--gk-stage-raised' => $raised,
            '--gk-stage-well' => $well,
            '--gk-stage-ink' => $fix($ink, 7.0),
            '--gk-stage-ink-2' => $fix($ink2, 5.5),
            '--gk-stage-ink-3' => $fix($ink3, 4.5),
            '--es-accent-readable' => $fix($darker ? $this->readable : $this->readableDark, 4.5),
        ];

        // Only in the palette's own brightness: a visitor whose first page set the other one keeps
        // it (theme-script, es_gp_mode), and this schedule then shows its automatic colours.
        // :where() keeps it as light as css()'s own `body`: the owner's custom CSS, printed after
        // it, must be able to overrule a palette as it overrules the automatic colours.
        $selector = $mode === 'dark' ? ':where(html.dark) body' : ':where(html:not(.dark)) body';

        return $selector.' { '.implode(' ', array_map(fn ($k, $v) => $k.': '.$v.';', array_keys($tokens), $tokens)).' }';
    }

    /**
     * The mode a guest page is drawn in (the 2026-11 redesign): the owner's choice of light or dark
     * where they made one, and otherwise ("auto") dark unless the schedule whose
     * background the page wears (the layout's rule, see lookRole()) chose a LIGHT solid colour or
     * gradient, which gets the light version of the same design. A photo or Aurora stays dark: a
     * picture's brightness is not known here, and Aurora is drawn as light on dark.
     */
    public static function pageMode(Role $role, ?Role $otherRole = null, $event = null): string
    {
        $bg = self::lookOf($role, $otherRole, $event);

        // The owner's own choice, where they made one (Style > Background > Page style): a
        // palette is light or dark by itself.
        if ($palette = self::pagePalette($role, $otherRole, $event)) {
            return self::PALETTES[$palette][0];
        }
        // A plain 'light' or 'dark' (not offered by the form; kept for a stored value of the first
        // version of this setting) forces the brightness with the accent-tinted colours.
        $plain = $bg->exists ? $bg->guestPageSetting->page_mode : null;
        if ($plain === 'light' || $plain === 'dark') {
            return $plain;
        }

        $colours = match ($bg->background) {
            'solid' => [$bg->background_color],
            'gradient' => explode(',', (string) $bg->background_colors),
            default => [],
        };
        $colours = array_values(array_filter(array_map(fn ($c) => ColorUtils::normalizeHex(trim((string) $c)), $colours)));
        if (! $colours) {
            return 'dark';
        }

        $luminance = array_sum(array_map(fn ($c) => ColorUtils::getLuminance($c), $colours)) / count($colours);

        return $luminance > 0.45 ? 'light' : 'dark';
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
                ...self::stageTokens($this->stageLight),
            ],
            [
                '--es-accent' => $this->fillDark,
                '--es-accent-text' => $this->onFillDark,
                '--es-accent-edge' => $this->edgeDark,
                '--es-accent-readable' => $this->readableDark,
                '--es-accent-tint' => $this->tintDark,
                ...self::stageTokens($this->stageDark),
            ],
        ];
    }

    /** @return array<string, string> */
    private static function stageTokens(array $stage): array
    {
        return $stage ? [
            '--gk-stage' => $stage['stage'],
            '--gk-stage-raised' => $stage['raised'],
            '--gk-stage-well' => $stage['well'],
            '--gk-stage-ink' => $stage['ink'],
            '--gk-stage-ink-2' => $stage['ink2'],
            '--gk-stage-ink-3' => $stage['ink3'],
        ] : [];
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

        // :where(.dark) carries no weight, so the dark rule outranks the light one only by coming
        // second, and an owner's own `body { --es-accent: ... }`, printed later, outranks both.
        return $selector.' { '.$body($light).' }'."\n".':where(.dark) '.$selector.' { '.$body($dark).' }';
    }

    /**
     * The dark values alone, for a surface that is dark in both modes (the ticket). Without it a
     * visitor in light mode got the fill made for a white panel on the ticket's near-black card:
     * a black, white or grey accent drew "Complete payment" as a label with no button around it.
     */
    public function darkCss(string $selector): string
    {
        [, $dark] = $this->tokens();

        return $selector.' { '.implode(' ', array_map(fn ($name, $value) => $name.': '.$value.';', array_keys($dark), $dark)).' }';
    }
}
