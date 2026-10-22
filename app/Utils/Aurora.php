<?php

namespace App\Utils;

use App\Models\AuroraSetting;
use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Aurora: a schedule's page background drawn by the browser from the schedule's colours. Soft
 * lights that drift slowly over a tinted ground, with a fine grain, sharp at any size because
 * nothing in it is a picture.
 *
 * Built from three things a schedule keeps in `aurora_settings` (AuroraSetting):
 *
 *  - a palette: three source colours. From the accent (the accent and two hues the seed picks
 *    beside it), from the header picture (sampled here, on the server), one of PALETTES, or the
 *    owner's own three. A new schedule starts on a random palette, and its accent is that
 *    palette's (randomStart()), so the buttons match the light.
 *  - a seed: the arrangement. Which of COMPOSITIONS, mirrored or not, where each light is in its
 *    drift, and the harmony "From accent" uses. Decoded by plain bit arithmetic (decode()), so the
 *    Style tab's preview (resources/js/style/aurora.js) and this class never have to share a
 *    random number generator.
 *  - motion: the owner's "Slow drift" switch.
 *
 * The sources are re-lit for each of the visitor's modes: pale lights on a near-white ground in
 * light mode, deep glows on a near-black one in dark mode. Each light is held so the ink drawn
 * straight on the page (a list's day headings) reads at 4.5:1 over its strongest point.
 *
 * aurora.js is a port of the colour arithmetic here, kept equal by tests/fixtures/aurora.json
 * (AuroraTest) and the parity check in tests/Browser/ScheduleFormJourneyTest.php. A change to a
 * number here is a change there.
 */
final class Aurora
{
    /**
     * The curated palettes: three source colours each, and the accent a new schedule takes with
     * it (one of the Style tab's accent presets). The names are messages.aurora_palette_{key}.
     */
    public const PALETTES = [
        'lagoon' => ['colors' => ['#14B8A6', '#0EA5E9', '#6366F1'], 'accent' => '#14B8A6'],
        'glacier' => ['colors' => ['#0EA5E9', '#22D3EE', '#818CF8'], 'accent' => '#0EA5E9'],
        'iris' => ['colors' => ['#4E81FA', '#A855F7', '#22D3EE'], 'accent' => '#4E81FA'],
        'dusk' => ['colors' => ['#A855F7', '#EC4899', '#F97316'], 'accent' => '#A855F7'],
        'rose' => ['colors' => ['#EC4899', '#F43F5E', '#FB923C'], 'accent' => '#EC4899'],
        'ember' => ['colors' => ['#F97316', '#EF4444', '#EAB308'], 'accent' => '#F97316'],
        'coral' => ['colors' => ['#EF4444', '#FB7185', '#2DD4BF'], 'accent' => '#EF4444'],
        'citrus' => ['colors' => ['#EAB308', '#84CC16', '#F97316'], 'accent' => '#EAB308'],
        'meadow' => ['colors' => ['#22C55E', '#14B8A6', '#A3E635'], 'accent' => '#22C55E'],
        'graphite' => ['colors' => ['#111827', '#64748B', '#94A3B8'], 'accent' => '#111827'],
    ];

    /** The palettes that are not in PALETTES. */
    public const SPECIAL_PALETTES = ['accent', 'header', 'custom'];

    /**
     * Where each light sits, one list per composition, the strongest first: a phone draws only
     * the first three. Each light is [x, y, w, h, colour, strength, dx, dy, scale, turn, period]:
     *
     *  x, y      its centre, in % of the window
     *  w, h      its size, in hundredths of the window's longer side
     *  colour    0 to 2, the palette's sources; 3, the accent's glow
     *  strength  0 to 1, of the mode's peak
     *  dx, dy    how far it drifts, in % of its own size
     *  scale     its scale at the far end of the drift
     *  turn      its turn at the far end, in degrees
     *  period    seconds from one end of the drift to the other
     *
     * The brightest light is in the top 40%, behind the header, and the list below is quieter.
     */
    public const COMPOSITIONS = [
        // Dawn
        [[12, 4, 78, 58, 0, 1, 14, 10, 1.12, 8, 56], [86, 10, 70, 56, 1, .9, -12, 14, .92, -6, 68], [30, 62, 64, 48, 2, .55, 10, -12, 1.1, 5, 74], [56, -6, 34, 26, 3, .75, -18, 16, 1.18, 0, 48], [94, 80, 58, 44, 0, .35, -10, -8, 1.08, 0, 80]],
        // Tide
        [[6, 14, 62, 34, 0, 1, 18, 8, 1.08, 4, 60], [52, 2, 66, 32, 1, .95, -14, 12, 1.1, -3, 52], [96, 20, 52, 34, 2, .8, -16, 6, .94, 6, 70], [70, 30, 24, 16, 3, .6, -30, 10, 1.2, 0, 44], [18, 78, 60, 36, 1, .3, 12, -6, 1.06, 0, 84]],
        // Bloom
        [[50, -2, 84, 60, 0, 1, 0, 14, 1.14, 0, 58], [4, 30, 56, 50, 1, .8, 16, -8, 1.08, 10, 66], [98, 40, 56, 50, 2, .75, -16, -6, .92, -8, 72], [50, 22, 26, 20, 3, .7, 12, -14, 1.2, 0, 46], [50, 96, 70, 40, 1, .28, -8, -10, 1.1, 0, 88]],
        // Drift
        [[2, 0, 80, 60, 0, 1, 16, 14, 1.1, 12, 62], [58, 30, 62, 48, 1, .8, -12, -10, 1.12, -10, 70], [100, 86, 70, 52, 2, .55, -14, -12, .92, 6, 78], [40, 10, 28, 20, 3, .65, 20, 10, 1.15, 0, 50], [84, 4, 44, 30, 2, .45, -10, 12, 1.06, 0, 64]],
        // Halo
        [[92, -4, 96, 70, 0, 1, -10, 12, 1.1, -6, 64], [4, 36, 58, 46, 2, .75, 14, -10, 1.1, 8, 58], [40, 6, 48, 30, 1, .7, 16, 10, .94, 0, 72], [74, 18, 24, 18, 3, .7, -24, 18, 1.2, 0, 46], [20, 96, 62, 40, 1, .3, 10, -8, 1.06, 0, 86]],
        // Ember
        [[18, -2, 70, 52, 1, 1, 12, 14, 1.12, 6, 54], [84, 6, 74, 56, 0, .95, -14, 10, .94, -8, 66], [52, 34, 46, 30, 2, .6, -10, -14, 1.12, 4, 76], [46, 4, 26, 18, 3, .7, 24, 12, 1.15, 0, 42], [8, 84, 56, 40, 2, .32, 12, -8, 1.08, 0, 82]],
        // Veil
        [[22, 4, 30, 84, 0, .95, 30, 4, 1.06, 14, 60], [56, 0, 28, 76, 1, .9, -26, 6, .94, -12, 70], [86, 8, 30, 80, 2, .8, -24, 4, 1.08, 10, 64], [40, 8, 20, 40, 3, .55, 40, 8, 1.1, 0, 50], [50, 92, 90, 30, 1, .25, 0, -10, 1.06, 0, 90]],
        // Orbit
        [[30, 8, 58, 52, 0, 1, 22, 10, 1.1, 20, 56], [70, 8, 58, 52, 1, .95, -22, 12, .92, -20, 62], [50, 34, 50, 42, 2, .7, 6, -18, 1.12, 0, 70], [50, 12, 22, 18, 3, .75, -14, 14, 1.2, 0, 40], [96, 90, 52, 38, 2, .3, -10, -8, 1.06, 0, 84]],
    ];

    /** The ink a page draws straight on its ground: the body text (layouts/app-guest) and white. */
    public const INK_LIGHT = '#33383c';

    public const INK_DARK = '#ffffff';

    /** The strongest a light gets, and the grain's opacity, per mode. */
    private const PEAK = ['light' => .9, 'dark' => .82];

    private const GRAIN = ['light' => .07, 'dark' => .11];

    /** Below this HSL saturation a source is a grey, and its light stays grey. */
    private const GREY_BELOW = .08;

    /**
     * @param  string[]  $sources  three #rrggbb
     */
    private function __construct(
        public readonly array $sources,
        public readonly string $accent,
        public readonly int $seed,
        public readonly bool $motion,
        public readonly string $palette,
    ) {}

    public static function for(Role $role): self
    {
        $setting = $role->auroraSetting;

        // Match header with no picture to read (deleted, or a header with none) stands on the
        // accent, whatever colours were last sampled.
        $colors = ($setting->palette === 'header' && ! self::hasHeaderPicture($role)) ? null : $setting->colors;

        return self::fromParts(
            $role->accent_color,
            $setting->palette ?? 'accent',
            $colors,
            $setting->seed ?? self::seedFor($role),
            (bool) ($setting->motion ?? true),
        );
    }

    public static function fromParts(?string $accent, ?string $palette, ?string $colors, int $seed, bool $motion): self
    {
        $accent = self::hex($accent) ?? GuestTheme::DEFAULT_ACCENT;
        $seed = max(0, min(65535, $seed));
        $palette = self::isPalette($palette) ? $palette : 'accent';
        $decoded = self::decode($seed);
        $own = self::colorList($colors);

        $sources = match (true) {
            isset(self::PALETTES[$palette]) => array_map('strtolower', self::PALETTES[$palette]['colors']),
            in_array($palette, ['custom', 'header'], true) && count($own) === 3 => $own,
            default => self::harmonise($accent, $decoded['harmony']),
        };

        // A header palette whose picture is gone, or a custom one without its three colours,
        // stands on the accent (the sources above), and says so: a backup carries the palette
        // that is really drawn. The Style tab shows the owner's choice and draws it from the
        // accent too (resources/js/style/state.js auroraTokens()).
        if (in_array($palette, ['custom', 'header'], true) && count($own) !== 3) {
            $palette = 'accent';
        }

        return new self($sources, $accent, $seed, $motion, $palette);
    }

    /** The arrangement a schedule with no stored seed gets: its own, from its id, so it never changes. */
    public static function seedFor(Role $role): int
    {
        return $role->id ? crc32((string) $role->id) & 0xFFFF : 0;
    }

    public static function randomSeed(): int
    {
        return random_int(0, 65535);
    }

    /**
     * A new schedule's Aurora: a random palette, its accent and a random arrangement.
     *
     * @return array{palette: string, accent: string, seed: int}
     */
    public static function randomStart(): array
    {
        $palette = array_rand(self::PALETTES);

        return ['palette' => $palette, 'accent' => self::PALETTES[$palette]['accent'], 'seed' => self::randomSeed()];
    }

    public static function isPalette(?string $palette): bool
    {
        return is_string($palette) && (isset(self::PALETTES[$palette]) || in_array($palette, self::SPECIAL_PALETTES, true));
    }

    /**
     * What a seed decides, besides colour.
     *
     * @return array{comp: int, mirror: int, phase: int, still: float, harmony: int}
     */
    public static function decode(int $seed): array
    {
        return [
            'comp' => $seed % count(self::COMPOSITIONS),
            'mirror' => ($seed >> 3) & 1,
            'phase' => ($seed >> 4) % 60,
            'still' => (($seed >> 6) & 15) / 15.0,
            'harmony' => ($seed >> 10) % 3,
        ];
    }

    /**
     * Three sources from one accent: the accent and two hues beside it, the pair chosen by the
     * seed (analogous, a soft split complement, or a triad). A grey accent gives greys.
     *
     * @return string[]
     */
    public static function harmonise(string $accent, int $harmony): array
    {
        [$h, $s] = self::toHsl(self::rgb($accent));

        if ($s < self::GREY_BELOW) {
            return [strtolower($accent), self::toHex(self::fromHsl([$h, 0, .55])), self::toHex(self::fromHsl([$h, 0, .75]))];
        }

        $offsets = [[0, 28, -34], [0, 40, 190], [0, -62, 118]][$harmony % 3];

        return array_map(fn ($o) => self::toHex(self::fromHsl([$h + $o, $s, .55])), $offsets);
    }

    /**
     * The colours one mode draws: the ground, the three sources and the accent's glow re-lit,
     * the peak strength and the grain.
     *
     * @return array{ground: float[], lights: array<int, float[]>, alpha: float, grain: float}
     */
    public function tokens(string $mode): array
    {
        $mode = $mode === 'dark' ? 'dark' : 'light';
        [$h0, $s0] = self::toHsl(self::rgb($this->sources[0]));
        $grey = $s0 < self::GREY_BELOW;

        $ground = $mode === 'light'
            ? self::fromHsl([$h0, $grey ? 0 : min($s0, .6) * .55, .972])
            : self::fromHsl([$h0, $grey ? 0 : .32, .062]);

        $alpha = self::PEAK[$mode];
        $lights = [];
        foreach ([$this->sources[0], $this->sources[1], $this->sources[2], $this->accent] as $i => $hex) {
            $lights[] = self::relight($hex, $mode, $i, $ground, $alpha);
        }

        return ['ground' => $ground, 'lights' => $lights, 'alpha' => $alpha, 'grain' => self::GRAIN[$mode]];
    }

    /** The ground of one mode, #rrggbb: the page's own background colour under the layer. */
    public function ground(string $mode): string
    {
        return self::toHex($this->tokens($mode)['ground']);
    }

    /**
     * The custom properties a mode's colours ride in: light on $selector, dark under
     * `:where(.dark)`, the class the visitor's choice and ?dark= set. Every value is a number, so
     * nothing stored can break out of the rule.
     */
    public function css(string $selector): string
    {
        $vars = function (string $mode): string {
            $t = $this->tokens($mode);
            $out = '--aurora-ground:'.self::triplet($t['ground']).';';
            foreach ($t['lights'] as $i => $light) {
                $out .= '--aurora-c'.$i.':'.self::triplet($light).';';
            }

            return $out.'--aurora-a:'.$t['alpha'].';--aurora-grain:'.$t['grain'].';';
        };

        return $selector.'{'.$vars('light').'}:where(.dark) '.$selector.'{'.$vars('dark').'}';
    }

    /**
     * The layer's attributes: which composition, mirrored, still or moving. $still is the page's
     * own reason (an embed, a graphic); the owner's switch is added here.
     *
     * @return array<string, string>
     */
    public function attributes(bool $still = false): array
    {
        $d = self::decode($this->seed);

        return [
            'data-aurora-comp' => (string) $d['comp'],
            'data-aurora-mirror' => (string) $d['mirror'],
            'data-aurora-motion' => ($still || ! $this->motion) ? 'off' : 'on',
        ];
    }

    public function moves(): bool
    {
        return $this->motion;
    }

    /**
     * The lights, as the <i> elements partials/aurora-styles places. Numbers only.
     *
     * @return array<int, string> one style="" value per light
     */
    public function lightStyles(): array
    {
        $d = self::decode($this->seed);
        $styles = [];

        foreach (self::COMPOSITIONS[$d['comp']] as $i => [$x, $y, $w, $h, $c, $a, $dx, $dy, $scale, $turn, $period]) {
            $styles[] = '--x:'.$x.';--y:'.$y.';--w:'.$w.';--h:'.$h.';--c:var(--aurora-c'.$c.');--a:'.$a
                .';--dx:'.$dx.'%;--dy:'.$dy.'%;--s:'.$scale.';--r:'.$turn.'deg;--t:'.round($d['still'], 4)
                .';animation-name:aurora-k'.$i.';animation-duration:'.$period.'s;animation-delay:-'.(($d['phase'] + $i * 13) % 60).'s';
        }

        return $styles;
    }

    /**
     * The drift of each light, as keyframes with the numbers written out: a keyframe that reads a
     * custom property may not be run by the compositor, and this one runs for as long as the page
     * is open. One set per page, which only ever draws one Aurora.
     */
    public function keyframes(): string
    {
        $d = self::decode($this->seed);
        $out = '';

        foreach (self::COMPOSITIONS[$d['comp']] as $i => [, , , , , , $dx, $dy, $scale, $turn]) {
            $out .= '@keyframes aurora-k'.$i.'{from{transform:translate3d(0,0,0) scale(1) rotate(0deg)}'
                .'to{transform:translate3d('.$dx.'%,'.$dy.'%,0) scale('.$scale.') rotate('.$turn.'deg)}}';
        }

        return $out;
    }

    /**
     * One still frame as stacked gradients, for a surface that draws no layer: the ticket page.
     * The first four lights, where the drift starts.
     */
    public function staticGradient(string $mode): string
    {
        $t = $this->tokens($mode);
        $d = self::decode($this->seed);
        $layers = [];

        foreach (array_slice(self::COMPOSITIONS[$d['comp']], 0, 4) as [$x, $y, $w, $h, $c, $a]) {
            $x = $d['mirror'] ? 100 - $x : $x;
            $rgb = self::triplet($t['lights'][$c]);
            $alpha = fn (float $v) => floor($v * 100 + .5) / 100;
            $layers[] = 'radial-gradient('.(int) floor($w * .75 + .5).'% '.$h.'% at '.$x.'% '.$y.'%, rgb('.$rgb.' / '.$alpha($a * $t['alpha']).'), rgb('.$rgb.' / '.$alpha($a * $t['alpha'] * .35).') 45%, transparent 75%)';
        }

        return implode(', ', $layers);
    }

    /** The first two lights of light mode, for a picture drawn on the server (event graphics). */
    public function graphicColors(): array
    {
        $t = $this->tokens('light');

        return [self::toHex($t['lights'][0]), self::toHex($t['lights'][1])];
    }

    /**
     * Keep a schedule's Aurora settings. A colour list is stored only for the two palettes that
     * have one of their own; a preset or the accent stores none, so a stale one cannot surface.
     */
    public static function saveSettings(Role $role, string $palette, ?string $colors, ?int $seed, bool $motion): void
    {
        if (! $role->id) {
            return;
        }

        $palette = self::isPalette($palette) ? $palette : 'accent';
        $own = self::colorList($colors);

        $values = [
            'palette' => $palette,
            'colors' => in_array($palette, ['custom', 'header'], true) && count($own) === 3 ? implode(', ', $own) : null,
            'seed' => $seed === null ? null : max(0, min(65535, $seed)),
            'motion' => $motion,
        ];

        try {
            AuroraSetting::updateOrCreate(['role_id' => $role->id], $values);
        } catch (QueryException $e) {
            // Two first saves at once (two tabs, the API beside the form): the other one made the
            // row between this one's look and its insert. This one's values still win.
            if (($e->errorInfo[1] ?? null) !== 1062) {
                throw $e;
            }
            AuroraSetting::where('role_id', $role->id)->update($values);
        }

        $role->unsetRelation('auroraSetting');
    }

    /**
     * The Style tab's fields (and the new-schedule form's hidden ones). Called only when the post
     * carries aurora_submitted: a save without it leaves the settings as they are.
     *
     * The three colour fields are read only for Custom (a colour input always posts a value), and
     * Match header is sampled here from the picture the save leaves the schedule with.
     */
    public static function saveFromRequest(Role $role, Request $request): void
    {
        $palette = (string) $request->input('aurora_palette', 'accent');
        $palette = self::isPalette($palette) ? $palette : 'accent';

        $colors = null;
        if ($palette === 'custom') {
            $colors = implode(', ', array_filter([
                self::hex($request->input('aurora_color1')),
                self::hex($request->input('aurora_color2')),
                self::hex($request->input('aurora_color3')),
            ]));
        } elseif ($palette === 'header') {
            $colors = ($sampled = self::cachedHeaderColors($role)) ? implode(', ', $sampled) : null;
        }

        $seed = $request->filled('aurora_seed') && is_numeric($request->input('aurora_seed'))
            ? (int) $request->input('aurora_seed')
            : $role->auroraSetting->seed;

        self::saveSettings($role, $palette, $colors, $seed, $request->boolean('aurora_motion'));
    }

    /**
     * Sample the header again for a schedule on the Match header palette, so the light follows
     * the picture: called by the header's variants job once an upload's copies exist. A header
     * changed on the form is sampled by saveFromRequest(), and for() never uses colours of a
     * header that has no picture.
     */
    public static function refreshHeaderColors(Role $role): void
    {
        $setting = $role->auroraSetting;

        if (! $setting->exists || $setting->palette !== 'header') {
            return;
        }

        $sampled = self::cachedHeaderColors($role);
        $colors = $sampled ? implode(', ', $sampled) : null;

        if ($colors !== $setting->colors) {
            $setting->colors = $colors;
            $setting->save();
        }
    }

    /**
     * Whether Match header has a picture to sample: a banner header showing a built-in header or
     * an upload. The compact header shows none, and 'none', 'gradient' and 'logos' are not
     * pictures (Role::HEADER_IMAGE_KEYWORDS).
     */
    public static function hasHeaderPicture(Role $role): bool
    {
        return $role->headerStyle() === 'banner' && $role->headerImageUrl() !== null;
    }

    /**
     * Three dominant colours of the header picture, most present first, or null when there is no
     * picture or it cannot be read. On the server, because an uploaded header lives on the CDN,
     * another origin, where the Style tab's canvas could not read it.
     *
     * Read from the smallest copy there is: a built-in header's WebP, an upload's 960 derivative
     * once the variants job has made it, and the original until then (or $file, the upload this
     * very request carries). Every decode is measured first (ImageUtils::canDecodePixels()).
     *
     * @return string[]|null
     */
    public static function headerColors(Role $role): ?array
    {
        if (! self::hasHeaderPicture($role)) {
            return null;
        }

        $bytes = null;

        try {
            if (filled($role->header_image)) {
                // A built-in header's name, never a path: the column has no rule of its own.
                if (! preg_match('/^[A-Za-z0-9_-]+\z/', (string) $role->header_image)) {
                    return null;
                }
                $path = public_path('images/headers/'.$role->header_image.'.webp');
                $bytes = is_file($path) ? file_get_contents($path) : null;
            } else {
                $stored = $role->imageVariantFilename(960, 'header') ?? $role->imageVariantSource('header');
                $bytes = $stored ? Storage::get(ImageUtils::storagePathFor($stored)) : null;
            }
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        return is_string($bytes) && $bytes !== '' ? self::dominantColors($bytes) : null;
    }

    /**
     * headerColors(), kept for a month under the picture it was read from: the Style tab asks on
     * every visit, and a decode per page view is not worth it for a swatch.
     *
     * @return string[]|null
     */
    public static function cachedHeaderColors(Role $role): ?array
    {
        if (! self::hasHeaderPicture($role)) {
            return null;
        }

        $picture = filled($role->header_image)
            ? 'builtin:'.$role->header_image
            : 'upload:'.$role->imageVariantSource('header').':'.($role->imageVariantFilename(960, 'header') ?? '');

        $key = 'aurora:header:'.md5($picture);

        try {
            $cached = Cache::get($key);
            if (is_array($cached)) {
                return $cached ?: null;
            }

            // A miss is kept for minutes, not the month: it may be a disk that did not answer.
            $colors = self::headerColors($role);
            Cache::put($key, $colors ?? [], $colors ? now()->addDays(30) : now()->addMinutes(5));

            return $colors;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Three dominant colours of an image: k-means over a 24x24 copy, each pixel weighted by its
     * saturation so a grey sky does not outvote the stage lights, near-black and near-white left
     * out.
     *
     * @return string[]|null
     */
    public static function dominantColors(string $bytes): ?array
    {
        $info = @getimagesizefromstring($bytes);
        if ($info === false || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $previousMemoryLimit = ini_get('memory_limit');
        $image = null;
        $small = null;

        try {
            if (! ImageUtils::canDecodePixels(($info[0] ?? 0) * ($info[1] ?? 0))) {
                return null;
            }

            $image = @imagecreatefromstring($bytes);
            if (! $image) {
                return null;
            }

            $small = imagecreatetruecolor(24, 24);
            imagecopyresampled($small, $image, 0, 0, 0, 0, 24, 24, imagesx($image), imagesy($image));

            $pixels = [];
            for ($y = 0; $y < 24; $y++) {
                for ($x = 0; $x < 24; $x++) {
                    $c = imagecolorat($small, $x, $y);
                    $rgb = [($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF];
                    [, $s, $l] = self::toHsl($rgb);
                    if ($l > .06 && $l < .96) {
                        $pixels[] = [$rgb, .25 + $s];
                    }
                }
            }
        } finally {
            if ($image) {
                imagedestroy($image);
            }
            if ($small) {
                imagedestroy($small);
            }
            ImageUtils::restoreMemoryLimit($previousMemoryLimit);
        }

        if (count($pixels) < 3) {
            return null;
        }

        $distance = fn (array $a, array $b) => ($a[0] - $b[0]) ** 2 + ($a[1] - $b[1]) ** 2 + ($a[2] - $b[2]) ** 2;

        // Seeded by farthest point, so the result is the same on every run.
        $centres = [$pixels[0][0]];
        while (count($centres) < 3) {
            $best = null;
            $bestDistance = -1;
            foreach ($pixels as [$rgb, $weight]) {
                $d = min(array_map(fn ($q) => $distance($rgb, $q), $centres)) * $weight;
                if ($d > $bestDistance) {
                    $bestDistance = $d;
                    $best = $rgb;
                }
            }
            $centres[] = $best;
        }

        $groups = [];
        for ($round = 0; $round < 10; $round++) {
            $groups = array_fill(0, 3, ['sum' => [0, 0, 0], 'w' => 0]);
            foreach ($pixels as [$rgb, $weight]) {
                $k = 0;
                $kd = PHP_INT_MAX;
                foreach ($centres as $j => $q) {
                    $d = $distance($rgb, $q);
                    if ($d < $kd) {
                        $kd = $d;
                        $k = $j;
                    }
                }
                $groups[$k]['w'] += $weight;
                foreach ([0, 1, 2] as $i) {
                    $groups[$k]['sum'][$i] += $rgb[$i] * $weight;
                }
            }
            foreach ($groups as $j => $g) {
                if ($g['w'] > 0) {
                    $centres[$j] = array_map(fn ($v) => $v / $g['w'], $g['sum']);
                }
            }
        }

        $order = [0, 1, 2];
        usort($order, fn ($a, $b) => $groups[$b]['w'] <=> $groups[$a]['w']);

        return array_map(fn ($j) => self::toHex($centres[$j]), $order);
    }

    /**
     * A source re-lit for one mode, then held so the ink drawn straight on the page reads at 4.5:1
     * over the light at its strongest: lighter in light mode, darker in dark mode.
     *
     * @param  float[]  $ground
     * @return float[]
     */
    private static function relight(string $hex, string $mode, int $i, array $ground, float $alpha): array
    {
        [$h, $s] = self::toHsl(self::rgb($hex));
        $grey = $s < self::GREY_BELOW;

        if ($mode === 'light') {
            $s = $grey ? 0 : max(.7, min(.95, $s));
            $l = [.56, .60, .64, .54][$i];
            $c = self::fromHsl([$h, $s, $l]);
            $ink = self::rgb(self::INK_LIGHT);
            for ($n = 0; $n < 40 && self::contrast(self::over($c, $alpha, $ground), $ink) < 4.5; $n++) {
                $l += .01;
                $c = self::fromHsl([$h, $s, $l]);
            }

            return $c;
        }

        $s = $grey ? 0 : max(.6, min(.9, $s));
        $l = [.50, .46, .42, .56][$i];
        $c = self::fromHsl([$h, $s, $l]);
        $ink = self::rgb(self::INK_DARK);
        for ($n = 0; $n < 50 && self::contrast(self::over($c, $alpha, $ground), $ink) < 4.5; $n++) {
            $l -= .01;
            $c = self::fromHsl([$h, $s, $l]);
        }

        return $c;
    }

    /** @return string[] every #rrggbb in a "#a, #b, #c" list, lower-cased */
    public static function colorList(?string $colors): array
    {
        $list = array_values(array_filter(array_map(fn ($c) => self::hex(trim($c)), explode(',', (string) $colors))));

        return array_slice($list, 0, 3);
    }

    /** #rrggbb, lower-cased, from #rrggbb or #rgb; null for anything else. */
    public static function hex(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        if (preg_match('/^#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])\z/', $value, $m)) {
            $value = '#'.$m[1].$m[1].$m[2].$m[2].$m[3].$m[3];
        }

        return preg_match('/^#[0-9a-fA-F]{6}\z/', $value) ? strtolower($value) : null;
    }

    /** @return int[] */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    /** @param float[]|int[] $c */
    public static function toHex(array $c): string
    {
        return '#'.implode('', array_map(fn ($v) => str_pad(dechex(self::channel($v)), 2, '0', STR_PAD_LEFT), $c));
    }

    /** @param float[]|int[] $c */
    public static function triplet(array $c): string
    {
        return implode(' ', array_map(fn ($v) => self::channel($v), $c));
    }

    /**
     * A channel as a whole number, rounded as JavaScript's Math.round() does. Not round(), which
     * pre-rounds to fifteen digits and turned 244.49999999999997 into 245 where the Style tab's
     * preview drew 244.
     */
    private static function channel(float|int $v): int
    {
        return (int) floor(max(0, min(255, $v)) + .5);
    }

    /**
     * @param  int[]|float[]  $c
     * @return float[] [hue 0-360, saturation 0-1, lightness 0-1]
     */
    private static function toHsl(array $c): array
    {
        [$r, $g, $b] = [$c[0] / 255, $c[1] / 255, $c[2] / 255];
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $h = 0;
        $s = 0;
        $l = ($max + $min) / 2;

        if ($max != $min) {
            $d = $max - $min;
            $s = $l > .5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            $h = match (true) {
                $max == $r => ($g - $b) / $d + ($g < $b ? 6 : 0),
                $max == $g => ($b - $r) / $d + 2,
                default => ($r - $g) / $d + 4,
            };
            $h *= 60;
        }

        return [$h, $s, $l];
    }

    /**
     * @param  float[]  $hsl
     * @return float[]
     */
    private static function fromHsl(array $hsl): array
    {
        [$h, $s, $l] = $hsl;
        $h = fmod(fmod($h, 360) + 360, 360);
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return [($r + $m) * 255, ($g + $m) * 255, ($b + $m) * 255];
    }

    /** @param float[] $fg @param float[] $bg */
    private static function over(array $fg, float $a, array $bg): array
    {
        return [$fg[0] * $a + $bg[0] * (1 - $a), $fg[1] * $a + $bg[1] * (1 - $a), $fg[2] * $a + $bg[2] * (1 - $a)];
    }

    private static function luminance(array $c): float
    {
        $lin = function ($v) {
            $v /= 255;

            return $v <= .03928 ? $v / 12.92 : (($v + .055) / 1.055) ** 2.4;
        };

        return .2126 * $lin($c[0]) + .7152 * $lin($c[1]) + .0722 * $lin($c[2]);
    }

    public static function contrast(array $a, array $b): float
    {
        $x = self::luminance($a);
        $y = self::luminance($b);

        return (max($x, $y) + .05) / (min($x, $y) + .05);
    }
}
