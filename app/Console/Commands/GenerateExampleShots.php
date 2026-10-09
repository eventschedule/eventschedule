<?php

namespace App\Console\Commands;

use App\Utils\ExampleSchedules;
use Facebook\WebDriver\Chrome\ChromeDevToolsDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use GdImage;
use Illuminate\Console\Command;
use Laravel\Dusk\Chrome\ChromeProcess;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * The pictures /examples shows.
 *
 * Two parts, and either can be run alone:
 *
 * --art     Each demo schedule's own header and logo, from the files this repository already
 *           holds, resized to what the wall uses. No browser, no network.
 * --pages   A photograph of each demo schedule's live page as a phone shows it (what a picture
 *           brings forward when it is pressed), and three of one schedule for "Look closer", with
 *           what was measured on them: where the page's name stands and in which typeface, and
 *           where each numbered part is. Read-only page loads of the public demos. Needs Dusk's
 *           ChromeDriver (php artisan dusk:chrome-driver).
 *
 * Both write config/example_shots.php, which is committed. Run by hand, after a release that
 * changes the guest page; never scheduled.
 *
 * Rules the photographing keeps, each of which cost a round when the page was designed:
 *
 * - The window is made as tall as the picture BEFORE it is photographed. A page's ground is often
 *   fixed to the window, and past the window's foot it simply stops.
 * - The page's name is found by what it says (the largest visible element whose text is the first
 *   h1's), never by a tag or a class, and a numbered part by its own words. The guest page is
 *   redesigned from time to time; a part that is not found has no pin, and the page says so.
 * - The event that is photographed is found on the day: the demo town is rebuilt every hour.
 * - Nothing read off a page is trusted. Six of the demos can be edited by anyone, so a typeface is
 *   kept only if the app bundles it, a colour only if it is one, and the names are printed to be
 *   read before the file is committed. The view checks every value again.
 */
class GenerateExampleShots extends Command
{
    protected $signature = 'app:generate-example-shots
        {--art : Only resize the schedules\' own pictures}
        {--pages : Only photograph the schedules\' pages}';

    protected $description = 'Prepare the pictures the /examples page shows: each demo schedule\'s own images, and photographs of their live pages';

    /** The wall's pictures: a header this wide at most, a logo this square. */
    private const HEADER_WIDTH = 1040;

    private const LOGO_SIZE = 192;

    /** A phone's window, and how much of a page is kept (in its own pixels, at twice the density). */
    private const PHONE = ['width' => 390, 'height' => 844];

    private const PAGE_HEIGHT = 1640;

    /** The schedule "Look closer" opens up, and its performers by name (DemoService's seeds). */
    private const CLOSER = 'demo-moestavern';

    private const ACTS = [
        'Lisa Simpson Jazz Quartet', 'Krusty Entertainment', 'DJ Sideshow Bob', 'Springfield Rockers',
        'Lurleen Lumpkin', 'Troy McClure Productions', 'Professor Frink Presents', 'Stonecutters Guild',
    ];

    private ?ChromeDevToolsDriver $cdp = null;

    /** Pictures saved under a temporary name, to their final one. */
    private array $pending = [];

    public function handle(): int
    {
        $doArt = $this->option('art') || ! $this->option('pages');
        $doPages = $this->option('pages') || ! $this->option('art');
        $stored = ExampleSchedules::shots();

        // The photographs first: if a page cannot be read the command stops there, with nothing
        // written at all.
        if ($doPages) {
            $pages = $this->pages($stored['shots']);

            if ($pages === null) {
                return 1;
            }

            $stored['shots'] = $pages['shots'];
            $stored['xray'] = $pages['xray'];
            $stored['taken'] = now()->toDateString();
        }

        if ($doArt) {
            $stored['art'] = $this->art();
        }

        $this->write($stored);
        $this->info('Wrote config/example_shots.php. Read the names above and the diff before committing.');

        return 0;
    }

    /**
     * Each schedule's header, whole, at the width the wall can use, and its logo small.
     */
    private function art(): array
    {
        $dir = public_path(ExampleSchedules::ART_DIR);
        $this->ensureDirectory($dir);
        $art = [];

        foreach (ExampleSchedules::all() as $schedule) {
            $subdomain = $schedule['subdomain'];
            $header = $this->load(public_path($schedule['header_image_url']));
            $width = imagesx($header);
            $height = imagesy($header);

            if ($width > self::HEADER_WIDTH) {
                $header = $this->resize($header, self::HEADER_WIDTH, (int) round($height * self::HEADER_WIDTH / $width));
            }

            $this->put($header, $dir.'wall-'.$subdomain.'.webp', 76);

            $logo = $this->resize($this->load(public_path($schedule['profile_image_url'])), self::LOGO_SIZE, self::LOGO_SIZE);
            $this->put($logo, $dir.'logo-'.$subdomain.'.webp', 80);

            $art[$subdomain] = [
                'header' => 'wall-'.$subdomain.'.webp',
                'w' => imagesx($header),
                'h' => imagesy($header),
                'logo' => 'logo-'.$subdomain.'.webp',
            ];
        }

        $this->info('Resized '.count($art).' headers and logos into '.ExampleSchedules::ART_DIR);

        return $art;
    }

    /**
     * The photographs. Null when a page could not be read, in which case nothing is written:
     * every picture is saved beside its place under another name, and they are all moved into
     * place together at the end.
     *
     * @param  array  $before  what the last run read, to say which names have changed since
     * @return array{shots: array, xray: array}|null
     */
    private function pages(array $before): ?array
    {
        $dir = public_path(ExampleSchedules::PAGES_DIR);
        $this->ensureDirectory($dir);

        $port = $this->findAvailablePort();
        $chrome = (new ChromeProcess)->toProcess(["--port={$port}"]);
        $chrome->start();

        if (! $this->waitForServer($port)) {
            $this->error('ChromeDriver failed to start. Run: php artisan dusk:chrome-driver');
            $this->error($chrome->getErrorOutput());
            $chrome->stop();

            return null;
        }

        $options = (new ChromeOptions)->addArguments([
            '--window-size=1280,900',
            '--headless=new',
            '--disable-gpu',
            '--hide-scrollbars',
            '--mute-audio',
            '--force-color-profile=srgb',
            '--disable-search-engine-choice-screen',
        ]);
        $driver = null;
        $this->pending = [];

        try {
            $driver = RemoteWebDriver::create(
                "http://localhost:{$port}",
                DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options),
                30000,
                120000
            );
            $this->cdp = new ChromeDevToolsDriver($driver);
            // The demos' own clock, so "Today" on a page is their today.
            $this->cdp->execute('Emulation.setTimezoneOverride', ['timezoneId' => 'America/New_York']);

            $shots = [];
            $rows = [];

            foreach (ExampleSchedules::all() as $schedule) {
                $subdomain = $schedule['subdomain'];
                $taken = $this->photograph($driver, $schedule['url'], self::PHONE['width'], self::PHONE['height'], true, self::PAGE_HEIGHT, $this->scriptHideName(2).' return { title };');
                $title = $this->title($taken['info']['title'] ?? null);

                if ($title === null) {
                    return $this->abandon("No name was found on {$schedule['url']}.");
                }

                $picture = $taken['image'];
                $this->save($picture, $dir.$subdomain.'.webp', 78);

                // The room the name has on its page, held inside the page.
                $x = max(0.0, min(self::PHONE['width'] - 40.0, (float) ($taken['info']['title']['x'] ?? 44)));
                $w = max(40.0, min(self::PHONE['width'] - $x, (float) ($taken['info']['title']['w'] ?? 302)));

                $shots[$subdomain] = [
                    'full' => [
                        'file' => $subdomain.'.webp',
                        'h' => round(imagesy($picture) / 2, 1),
                        'pw' => imagesx($picture),
                        'ph' => imagesy($picture),
                    ],
                    'title' => ['x' => round($x, 1), 'w' => round($w, 1)] + $title,
                    'glow' => $this->glow($picture),
                ];

                $was = $before[$subdomain]['title']['text'] ?? null;
                $rows[] = [
                    $subdomain,
                    OutputFormatter::escape($title['text']),
                    $title['font'] ?? '(none bundled)',
                    $was !== null && $was !== $title['text'] ? 'CHANGED, was: '.OutputFormatter::escape($was) : '',
                ];
            }

            // Names are printed escaped: one of them could carry the console's own colour tags.
            $this->table(['Schedule', 'The name read off its page', 'Typeface', 'Since the last run'], $rows);

            $xray = $this->closer($driver, $dir);

            if ($xray === null) {
                return $this->abandon('No event was found on '.self::CLOSER."'s page to photograph for \"Look closer\".");
            }

            foreach ($this->pending as $temporary => $final) {
                rename($temporary, $final);
            }

            $this->pending = [];

            return ['shots' => $shots, 'xray' => $xray];
        } finally {
            foreach (array_keys($this->pending) as $temporary) {
                @unlink($temporary);
            }

            $driver?->quit();
            $chrome->stop();
        }
    }

    /** Say why, and write nothing (the pictures saved so far are thrown away by pages()). */
    private function abandon(string $why): ?array
    {
        $this->error($why.' Nothing was written.');

        return null;
    }

    /** Save a picture beside its place; pages() moves them all into place when every one is taken. */
    private function save(GdImage $image, string $final, int $quality): void
    {
        $temporary = $final.'.new';

        if (! imagewebp($image, $temporary, $quality)) {
            throw new RuntimeException("Could not write {$final}");
        }

        $this->pending[$temporary] = $final;
    }

    /**
     * Three photographs of one schedule, and where each numbered part stands in them.
     */
    private function closer(RemoteWebDriver $driver, string $dir): ?array
    {
        $home = 'https://'.self::CLOSER.'.eventschedule.com/';
        $event = $this->findEvent($driver, $home);

        // Without one the page would show the schedule a second time and call it an event.
        if ($event === null) {
            return null;
        }

        $this->line('Look closer: '.$home.' and '.$event);

        $jobs = [
            'xr-desk-home' => [$home, 1280, 800, false, 1500, 1600, $this->scriptHideName(1).$this->scriptParts('home')],
            'xr-phone-home' => [$home, self::PHONE['width'], self::PHONE['height'], true, 1500, 585, $this->scriptHideName(2).$this->scriptParts('home')],
            'xr-phone-event' => [$event, self::PHONE['width'], self::PHONE['height'], true, 3100, 585, $this->scriptParts('event')],
        ];
        $xray = [];

        foreach ($jobs as $name => [$url, $width, $height, $mobile, $keep, $across, $script]) {
            $taken = $this->photograph($driver, $url, $width, $height, $mobile, $keep, $script.' return { pins, title: typeof title === "undefined" ? null : title };');
            $shownHeight = (int) round(imagesy($taken['image']) / 2);
            $picture = $this->resize($taken['image'], $across, (int) round(imagesy($taken['image']) * $across / imagesx($taken['image'])));
            $this->save($picture, $dir.$name.'.webp', 80);

            $pins = [];

            foreach (($taken['info']['pins'] ?? []) as $part => $at) {
                // A part whose box runs off an edge of the picture (a row that is swiped, a bar
                // below the foot) is not in the picture.
                if (is_array($at) && isset($at['x'], $at['y'], $at['w'], $at['h'])
                    && $at['w'] > 0 && $at['x'] >= 0 && $width >= $at['x'] + $at['w'] && $at['y'] >= 0 && $at['y'] < $shownHeight - 30) {
                    $pins[$part] = ['x' => (int) $at['x'], 'y' => (int) $at['y'], 'w' => (int) $at['w'], 'h' => (int) $at['h']];
                }
            }

            $xray[$name] = ['file' => $name.'.webp', 'w' => $width, 'h' => $shownHeight, 'pw' => imagesx($picture), 'ph' => imagesy($picture), 'pins' => $pins];
            $title = $this->title($taken['info']['title'] ?? null);

            if ($title !== null && isset($taken['info']['title']['x'], $taken['info']['title']['w'])) {
                $xray[$name]['title'] = $title + [
                    'align' => ($taken['info']['title']['align'] ?? 'center') === 'center' ? 'center' : 'start',
                    'x' => round((float) $taken['info']['title']['x'], 1),
                    'w' => round((float) $taken['info']['title']['w'], 1),
                ];
            }

            $this->line("  {$name}: ".(count($pins) ? implode(', ', array_keys($pins)) : 'no parts found'));
        }

        return $xray;
    }

    /**
     * Load a page at a window size, make the window as tall as what will be kept, run a script
     * that measures the page (and may change it), and photograph it at twice the density.
     *
     * @return array{image: GdImage, info: array}
     */
    private function photograph(RemoteWebDriver $driver, string $url, int $width, int $height, bool $mobile, int $keep, string $script): array
    {
        $this->cdp->execute('Emulation.setDeviceMetricsOverride', ['width' => $width, 'height' => $height, 'deviceScaleFactor' => 2, 'mobile' => $mobile]);
        $this->cdp->execute('Emulation.setEmulatedMedia', ['features' => [
            ['name' => 'prefers-color-scheme', 'value' => 'light'],
            ['name' => 'prefers-reduced-motion', 'value' => 'reduce'],
        ]]);
        $driver->get($url);
        usleep(1500000);

        // The cookie banner is not part of the page a visitor reads. Then walk the stretch that
        // will be kept, so pictures that load late have loaded.
        $pageHeight = (int) $this->evaluate(<<<JS
            [...document.querySelectorAll('body *')].filter(e => getComputedStyle(e).position === 'fixed' && /Allow all/.test(e.textContent) && e.getBoundingClientRect().height < 420).forEach(e => e.style.setProperty('display', 'none', 'important'));
            for (let y = 0; y <= {$keep}; y += 500) { scrollTo(0, y); await new Promise(r => setTimeout(r, 180)); }
            scrollTo(0, 0);
            await new Promise(r => setTimeout(r, 300));
            return document.documentElement.scrollHeight;
        JS);

        $tall = min($keep, max($pageHeight, $height));
        $this->cdp->execute('Emulation.setDeviceMetricsOverride', ['width' => $width, 'height' => $tall, 'deviceScaleFactor' => 2, 'mobile' => $mobile]);
        usleep(600000);

        $info = $this->evaluate($script);
        $this->evaluate('await document.fonts.ready; await Promise.all([...document.images].filter(i => i.complete || i.loading !== "lazy").map(i => i.decode().catch(() => {}))); return true;');
        usleep(500000);

        $shot = $this->cdp->execute('Page.captureScreenshot', ['format' => 'png', 'clip' => ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $tall, 'scale' => 1]]);
        $image = imagecreatefromstring(base64_decode($shot['data'] ?? ''));

        if (! $image) {
            throw new RuntimeException("No picture came back for {$url}");
        }

        return ['image' => $image, 'info' => is_array($info) ? $info : []];
    }

    /**
     * Run statements in the page (they may await) and bring back what they return.
     */
    private function evaluate(string $body): mixed
    {
        $answer = $this->cdp->execute('Runtime.evaluate', [
            'expression' => '(async () => { '.$body.' })()',
            'awaitPromise' => true,
            'returnByValue' => true,
        ]);

        if (isset($answer['exceptionDetails'])) {
            throw new RuntimeException('The page script failed: '.($answer['exceptionDetails']['exception']['description'] ?? $answer['exceptionDetails']['text'] ?? 'unknown'));
        }

        return $answer['result']['value'] ?? null;
    }

    /**
     * Measure the page's name and hide it in a box of a fixed number of lines, so the page under
     * it stands where it would for any name of that many lines or fewer. Leaves `title` for
     * whatever follows.
     */
    private function scriptHideName(int $lines): string
    {
        return <<<JS
            const vis = e => { const r = e.getBoundingClientRect(); return r.width > 2 && r.height > 2 && getComputedStyle(e).visibility !== 'hidden'; };
            const all = [...document.querySelectorAll('body *')].filter(vis);
            const said = ((document.querySelector('h1') || {}).textContent || '').trim();
            let named = null, biggest = 0;
            for (const e of all) {
                if (!said || (e.textContent || '').trim() !== said) continue;
                const size = parseFloat(getComputedStyle(e).fontSize);
                if (size > biggest || (size === biggest && named && named.contains(e))) { biggest = size; named = e; }
            }
            let title = null;
            if (named) {
                const c = getComputedStyle(named);
                title = { text: said, font: c.fontFamily.split(',')[0].replace(/["']/g, '').trim(), size: parseFloat(c.fontSize), weight: c.fontWeight, color: c.color, align: c.textAlign };
                // The room the name has. A heading is often only as wide as its words, so it is given
                // a name long enough to fill every line it could have, measured, and given its own back.
                const kept = named.innerHTML;
                named.textContent = Array(60).fill('Wm').join(' ');
                await new Promise(r => setTimeout(r, 80));
                const wide = named.getBoundingClientRect();
                named.innerHTML = kept;
                title.x = wide.left; title.w = wide.width;
                named.style.minHeight = (parseFloat(c.lineHeight) * {$lines}) + 'px';
                named.style.visibility = 'hidden';
                await new Promise(r => setTimeout(r, 300));
                const nb = named.getBoundingClientRect();
                title.y = nb.top + scrollY; title.h = nb.height;
            }
        JS;
    }

    /**
     * Find each numbered part by what it says. Returns nothing itself: it fills `pins`.
     */
    private function scriptParts(string $page): string
    {
        $acts = json_encode(self::ACTS);
        $shared = <<<JS
            const seen = e => { const r = e.getBoundingClientRect(); return r.width > 2 && r.height > 2 && getComputedStyle(e).visibility !== 'hidden'; };
            const every = [...document.querySelectorAll('body *')].filter(seen);
            const words = e => (e.textContent || '').replace(/\\s+/g, ' ').trim();
            const byText = (re) => { let best = null; for (const e of every) { if (!re.test(words(e))) continue; const r = e.getBoundingClientRect(); const a = r.width * r.height; if (!best || a < best.a) best = { e, a }; } return best ? best.e : null; };
            const box = (e) => { if (!e) return null; const r = e.getBoundingClientRect(); return { x: Math.round(r.left), y: Math.round(r.top + scrollY), w: Math.round(r.width), h: Math.round(r.height) }; };
            // An act on the bill: the first line on the page that is one of the town's performers, or a list of them.
            const acts = {$acts};
            const billed = every.filter(e => { const t = words(e); return t && t.length < 140 && t.split(/\\s*,\\s*/).every(n => acts.includes(n)); })
                .sort((a, b) => (a.getBoundingClientRect().top - b.getBoundingClientRect().top) || (b.getBoundingClientRect().width - a.getBoundingClientRect().width))[0];
            const pins = {};
            pins.lineup = box(billed);
        JS;

        if ($page === 'event') {
            return $shared.<<<'JS'
                pins.weekly = box(byText(/^Weekly . \w+$/));
                pins.tickets = box(byText(/^(Buy|Get) Tickets$/));
                pins.calendar = box(byText(/^Add to Calendar$/));
            JS;
        }

        return $shared.<<<'JS'
            pins.follow = box(byText(/^Follow$/));
            pins.chips = box(byText(/^Show All$/));
            pins.free = box(byText(/^Free entry$/));
            pins.fan = box(byText(/^Add Photo$/));
            // The switch between the list and the month: two buttons that say so in their labels.
            const views = [...document.querySelectorAll('button,a')].filter(seen).filter(e => /list|calendar|grid/i.test((e.getAttribute('aria-label') || '') + ' ' + (e.getAttribute('title') || '') + ' ' + (e.id || '')));
            if (views.length) {
                const boxes = views.slice(0, 2).map(box);
                const x0 = Math.min(...boxes.map(b => b.x)), y0 = Math.min(...boxes.map(b => b.y));
                pins.layout = { x: x0, y: y0, w: Math.max(...boxes.map(b => b.x + b.w)) - x0, h: Math.max(...boxes.map(b => b.h)) };
            }
        JS;
    }

    /**
     * An event of the schedule that has a price on it, found on its page today.
     */
    private function findEvent(RemoteWebDriver $driver, string $home): ?string
    {
        $this->cdp->execute('Emulation.setDeviceMetricsOverride', ['width' => 1280, 'height' => 900, 'deviceScaleFactor' => 1, 'mobile' => false]);
        $driver->get($home);
        usleep(1500000);

        $found = $this->evaluate(<<<'JS'
            const events = [];
            for (const a of document.querySelectorAll('a[href]')) {
                let url; try { url = new URL(a.href); } catch (e) { continue; }
                if (url.origin !== location.origin || !/^\/[^\/]+\/[A-Za-z0-9]{4,}(\/\d{4}-\d{2}-\d{2})?$/.test(url.pathname)) continue;
                let card = a;
                for (let k = 0; k < 6 && card.parentElement && (card.textContent || '').length < 60; k++) card = card.parentElement;
                events.push({ href: url.origin + url.pathname, text: card.textContent || '' });
            }
            const priced = events.find(e => /From \$[1-9]/.test(e.text)) || events.find(e => /\$[1-9]\d*(?!\s*Squish)/.test(e.text));
            return (priced || events[0] || {}).href || null;
        JS);

        return is_string($found) && str_starts_with($found, $home) ? $found : null;
    }

    /**
     * What was read of a page's name, kept only where it is what it should be.
     *
     * @return array{text: string, font: ?string, size: float, weight: int, color: string, y: float, h: float}|null
     */
    private function title(mixed $read): ?array
    {
        if (! is_array($read) || ! is_string($read['text'] ?? null) || trim($read['text']) === '') {
            return null;
        }

        $font = is_string($read['font'] ?? null) && preg_match('/^[A-Za-z0-9 ]{1,60}\z/', $read['font']) && font_stylesheet_url($read['font']) ? $read['font'] : null;
        $color = is_string($read['color'] ?? null) && preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9., ]{5,30}\))\z/', $read['color']) ? $read['color'] : '#151b26';
        $weight = (int) ($read['weight'] ?? 700);

        return array_filter([
            'text' => mb_substr(trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $read['text'])), 0, 120),
            'font' => $font,
            'size' => round((float) ($read['size'] ?? 32), 1),
            'weight' => $weight >= 100 && $weight <= 900 ? $weight : 700,
            'color' => $color,
            'y' => round((float) ($read['y'] ?? 0), 1),
            'h' => round((float) ($read['h'] ?? 0), 1),
        ], fn ($value) => $value !== null);
    }

    /**
     * A page's own light: its ground at the two edges and its header picture, not its buttons.
     * Three numbers, 0 to 255, for an rgb() colour.
     */
    private function glow(GdImage $image): string
    {
        $width = imagesx($image);
        $height = min(imagesy($image), 2600);
        $buckets = [];

        foreach ([[0, 0, 14, $height], [$width - 14, 0, $width, $height], [0, 0, $width, (int) ($width * 0.36)]] as [$x0, $y0, $x1, $y1]) {
            for ($y = $y0; $y < $y1; $y += 12) {
                for ($x = $x0; $x < $x1; $x += 12) {
                    $rgb = imagecolorat($image, $x, $y);
                    [$r, $g, $b] = [($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $rgb & 0xFF];
                    [$hue, $saturation, $value] = $this->hsv($r, $g, $b);

                    if ($value < 0.22 || $saturation < 0.22) {
                        continue;
                    }

                    $key = (int) ($hue * 16);
                    $buckets[$key] ??= [0, 0, 0, 0, 0.0];
                    $buckets[$key][0] += $r;
                    $buckets[$key][1] += $g;
                    $buckets[$key][2] += $b;
                    $buckets[$key][3]++;
                    $buckets[$key][4] += $saturation * $value;
                }
            }
        }

        if ($buckets === []) {
            return '96 124 200';
        }

        usort($buckets, fn ($a, $b) => $b[4] <=> $a[4]);
        [$r, $g, $b, $count] = $buckets[0];
        [$hue, $saturation, $value] = $this->hsv($r / $count, $g / $count, $b / $count);
        [$r, $g, $b] = $this->rgb($hue, min(1, max($saturation, 0.55)), min(1, max($value, 0.78)));

        return (int) $r.' '.(int) $g.' '.(int) $b;
    }

    /** @return array{0: float, 1: float, 2: float} hue, saturation and value, each 0 to 1 */
    private function hsv(float $r, float $g, float $b): array
    {
        [$r, $g, $b] = [$r / 255, $g / 255, $b / 255];
        $max = max($r, $g, $b);
        $delta = $max - min($r, $g, $b);

        if ($delta == 0) {
            return [0.0, 0.0, $max];
        }

        $hue = match (true) {
            $max == $r => fmod(($g - $b) / $delta, 6),
            $max == $g => ($b - $r) / $delta + 2,
            default => ($r - $g) / $delta + 4,
        } / 6;

        return [$hue < 0 ? $hue + 1 : $hue, $delta / $max, $max];
    }

    /** @return array{0: float, 1: float, 2: float} red, green and blue, each 0 to 255 */
    private function rgb(float $hue, float $saturation, float $value): array
    {
        $i = (int) floor($hue * 6);
        $f = $hue * 6 - $i;
        $p = $value * (1 - $saturation);
        $q = $value * (1 - $f * $saturation);
        $t = $value * (1 - (1 - $f) * $saturation);
        [$r, $g, $b] = match ($i % 6) {
            0 => [$value, $t, $p],
            1 => [$q, $value, $p],
            2 => [$p, $value, $t],
            3 => [$p, $q, $value],
            4 => [$t, $p, $value],
            default => [$value, $p, $q],
        };

        return [$r * 255, $g * 255, $b * 255];
    }

    private function put(GdImage $image, string $path, int $quality): void
    {
        if (! imagewebp($image, $path, $quality)) {
            throw new RuntimeException("Could not write {$path}");
        }
    }

    private function load(string $path): GdImage
    {
        $image = is_file($path) ? @imagecreatefromstring((string) file_get_contents($path)) : false;

        if (! $image) {
            throw new RuntimeException("Could not read the picture at {$path}");
        }

        return $this->resize($image, imagesx($image), imagesy($image));
    }

    /** A true-colour copy at a new size, on white (a picture with no ground of its own has one). */
    private function resize(GdImage $image, int $width, int $height): GdImage
    {
        $copy = imagecreatetruecolor($width, $height);
        imagefill($copy, 0, 0, imagecolorallocate($copy, 255, 255, 255));
        imagecopyresampled($copy, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $copy;
    }

    private function ensureDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * The file as short PHP arrays, so its diff reads.
     */
    private function write(array $stored): void
    {
        $body = "<?php\n\n"
            ."// Written by `php artisan app:generate-example-shots`. Do not edit by hand: run the command.\n"
            ."// The pictures /examples shows: each demo schedule's own header and logo at the size the wall\n"
            ."// uses (art), and the photographs of their pages with what was measured on them (shots, xray).\n\n"
            .'return '.$this->export(['taken' => $stored['taken'], 'art' => $stored['art'], 'shots' => $stored['shots'], 'xray' => $stored['xray']]).";\n";

        file_put_contents(config_path('example_shots.php'), $body);
    }

    private function export(mixed $value, int $depth = 1): string
    {
        if (is_array($value)) {
            if ($value === []) {
                return '[]';
            }

            $pad = str_repeat('    ', $depth);
            $lines = '';

            foreach ($value as $key => $item) {
                $lines .= $pad.(array_is_list($value) ? '' : var_export($key, true).' => ').$this->export($item, $depth + 1).",\n";
            }

            return "[\n".$lines.str_repeat('    ', $depth - 1).']';
        }

        return $value === null ? 'null' : var_export($value, true);
    }

    private function findAvailablePort(): int
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_bind($socket, '127.0.0.1', 0);
        socket_getsockname($socket, $addr, $port);
        socket_close($socket);

        return $port;
    }

    private function waitForServer(int $port, int $timeout = 10): bool
    {
        $start = time();

        while (time() - $start < $timeout) {
            $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);

            if ($connection) {
                fclose($connection);

                return true;
            }

            usleep(200000);
        }

        return false;
    }
}
