<?php

namespace Tests\Feature;

use App\Utils\ExampleSchedules;
use App\Utils\PosterWall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * /examples is two walls of the demo schedules' own pictures: the hand-made examples, then the
 * made-up town, each picture whole and in its own shape in rows that end flush (PosterWall, as on
 * /browse). A name typed in the hero is set over every picture; a picture that is pressed brings a
 * photograph of that schedule's page forward. The pictures and what was measured on them are
 * config/example_shots.php, which app:generate-example-shots writes.
 *
 * What this holds, each of which failed quietly while the page was designed:
 *
 *  - The page stands without that file. It is generated, so a fresh clone and CI may not have what
 *    this machine has; and with no photographs the page must not speak of any.
 *  - The town stays on its own wall. The owner asked for the Simpsons schedules to be separated
 *    from the hand-made examples; one list order or one filter is all that mixes them again.
 *  - Every row has an end at every breakpoint. A missing break leaves one row running on.
 *  - Nothing read off a demo page reaches a style attribute unchecked. Six of the demos can be
 *    edited by anyone, and the command reads their typeface and colours off the live page.
 *  - What the file names exists, and each name and numbered part lies inside its picture: a
 *    photograph retaken after the guest page changed can move them out of it.
 *  - The page's script parses. Everything a visitor does here (the name, the chips, the page
 *    brought forward, the numbered parts) is one inline script, and a slip in it leaves a page
 *    that renders, passes every assertion on its markup, and does nothing: two stray lines after
 *    an edit did exactly that on the day it was built, and only a browser noticed.
 */
class ExamplesPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The page is rendered from files in the repository. Nothing here may call out.
        Http::preventStrayRequests();
    }

    private function page(?array $shots = null): string
    {
        if ($shots !== null) {
            config(['example_shots' => $shots]);
        }

        return $this->get('/examples')->assertOk()->getContent();
    }

    /** The markup of one wall: from its opening tag to the next wall's, or to the pill after them. */
    private function wall(string $html, string $key): string
    {
        $from = strpos($html, 'data-ex-grid="'.$key.'"');
        $this->assertNotFalse($from, "the {$key} wall is not on the page");
        $next = strpos($html, 'data-ex-grid="', $from + 20);
        $end = $next !== false ? $next : strpos($html, 'data-ex-bar', $from);

        return substr($html, $from, ($end ?: strlen($html)) - $from);
    }

    /**
     * A fixture with every schedule photographed (a press brings a page forward only when all of
     * them are), to put chosen values through the view.
     */
    private function fixture(array $title = [], array $shot = []): array
    {
        $shots = [];

        foreach (ExampleSchedules::all() as $schedule) {
            $shots[$schedule['subdomain']] = $shot + [
                'full' => ['file' => $schedule['subdomain'].'.webp', 'h' => 1640, 'pw' => 780, 'ph' => 3280],
                'title' => $title + ['text' => $schedule['name'], 'font' => 'Roboto', 'size' => 32, 'weight' => 600, 'color' => 'rgb(21, 27, 38)', 'x' => 44, 'y' => 229, 'w' => 302, 'h' => 80],
                'glow' => '84 137 198',
            ];
        }

        return ['taken' => '2026-10-09', 'art' => [], 'shots' => $shots, 'xray' => []];
    }

    // ------------------------------------------------------- the two walls

    public function test_every_listed_schedule_is_on_a_wall_once_and_the_town_is_on_its_own(): void
    {
        $html = $this->page();
        $examples = $this->wall($html, 'examples');
        $town = $this->wall($html, 'town');
        $townKey = ExampleSchedules::townKey();

        foreach (ExampleSchedules::byCategory() as $kind => $schedules) {
            foreach ($schedules as $schedule) {
                $address = '>'.$schedule['subdomain'].'.eventschedule.com</p>';
                $onTown = $kind === ExampleSchedules::TOWN;

                $this->assertSame($onTown ? 0 : 1, substr_count($examples, $address), "{$schedule['subdomain']} is on the wrong wall, or twice");
                $this->assertSame($onTown ? 1 : 0, substr_count($town, $address), "{$schedule['subdomain']} is on the wrong wall, or twice");
            }
        }

        $this->assertSame(12, substr_count($examples, 'class="ex-tile ex-unit"'));
        $this->assertSame(6, substr_count($town, 'class="ex-tile ex-unit"'));
        $this->assertStringNotContainsString('data-room="'.$townKey.'"', $examples);
        $this->assertSame(6, substr_count($town, 'data-room="'.$townKey.'"'));

        // The town has a heading of its own and is not one of the chips that sort the examples.
        $this->assertSame(1, preg_match('#<div class="ex-town" id="town">.*?<h2[^>]*>\s*Springfield Demo Town\s*</h2>#s', $html));
        $this->assertStringNotContainsString('data-ex-room="'.$townKey.'"', $html);
        $this->assertSame(5, preg_match_all('#<button type="button" class="ex-room" data-ex-room="#', $html), 'the chips are All and the four kinds of example');

        // The examples' wall ends on the free space; the town's has none.
        $this->assertSame(1, substr_count($examples, 'class="ex-tile ex-blank"'));
        $this->assertStringNotContainsString('ex-blank"', $town);
    }

    public function test_every_row_of_both_walls_has_an_end_at_every_breakpoint(): void
    {
        $html = $this->page();
        $walls = ExampleSchedules::walls(ExampleSchedules::byCategory(), ExampleSchedules::shots()['art']);

        foreach ($walls as $key => $set) {
            $markup = $this->wall($html, $key);

            foreach (array_keys(PosterWall::PROFILES) as $profile) {
                $this->assertNotEmpty($set['rows'][$profile]['ends'], "{$key} has no rows at {$profile}");
                $this->assertSame(count($set['tiles']) - 1, end($set['rows'][$profile]['ends']), "{$key}'s last row at {$profile} does not end on its last tile");
                $this->assertSame(
                    count($set['rows'][$profile]['ends']),
                    substr_count($markup, 'class="ex-brk ex-brk-'.$profile.'"'),
                    "{$key} does not print one break for each of its rows at {$profile}"
                );
            }
        }

        // Each tile's shape is its own picture's, kept between a tall poster's and a long banner's.
        foreach ($walls as $set) {
            foreach ($set['units'] as $at => $unit) {
                $true = $unit['picture']['w'] / $unit['picture']['h'];
                $this->assertEqualsWithDelta(max(1.2, min(3.2, $true)), $set['tiles'][$at]['ratio'], 0.002, "{$unit['subdomain']} is not hung in its own shape");
            }
        }
    }

    // ------------------------------------------------------- with and without the generated file

    public function test_the_page_stands_without_the_generated_pictures_and_says_nothing_of_them(): void
    {
        $html = $this->page(['taken' => null, 'art' => [], 'shots' => [], 'xray' => []]);

        $this->assertSame(18, substr_count($html, 'class="ex-tile ex-unit"'));
        // Each picture is the header the schedule is listed with, and a press opens the live page.
        $this->assertStringContainsString('/images/examples/header_villageidiot.webp', $html);
        $this->assertStringNotContainsString('data-full=', $html);
        // (The element, not the word: the page's script names the attribute either way.)
        $this->assertStringNotContainsString('<dialog', $html);
        $this->assertStringContainsString('Press any one to open the live page.', $html);

        // What a visitor can read: the page's script holds the words it would say with photographs.
        $read = preg_replace('#<script\b.*?</script>#s', '', $html);
        $this->assertIsString($read);

        foreach (['photographed', 'as a phone shows it', 'to see its page', 'Not in these pictures', 'on its page when you press one', 'own typeface'] as $claim) {
            $this->assertStringNotContainsString($claim, $read, "with no photographs the page still says \"{$claim}\"");
        }

        // The numbered parts are all there, as a list with no pictures beside it.
        $this->assertSame(14, preg_match_all('#<li class="ex-part[^"]*" data-ex-part="#', $html));
        $this->assertStringNotContainsString('data-ex-pin=', $read);
    }

    public function test_with_the_pictures_a_press_brings_a_page_forward_and_the_page_says_so(): void
    {
        // config/example_shots.php and its pictures are committed, so a schedule with no
        // photograph here means the file was emptied or cut short, not that it was never made.
        $stored = ExampleSchedules::shots();
        $this->assertCount(count(ExampleSchedules::all()), $stored['shots'], 'config/example_shots.php does not hold a photograph for every listed schedule: run php artisan app:generate-example-shots');

        $html = $this->page();

        $this->assertSame(18, substr_count($html, 'data-full="'));
        $this->assertSame(1, substr_count($html, '<dialog class="ex-fit" data-ex-fitroom'));
        $this->assertStringContainsString('data-fit="Press any one to see its page."', $html);
        $this->assertStringContainsString('/images/examples/wall/wall-villageidiot.webp', $html);
        // The line under a phone that is brought forward says when its page was photographed.
        $this->assertMatchesRegularExpression('#The page as a phone shows it, photographed on [A-Z][a-z]+ \d{1,2}, \d{4}\.#', $html);
    }

    public function test_with_a_photograph_missing_for_one_schedule_a_press_opens_the_live_page_for_all(): void
    {
        // Some with a page to bring forward and some without would have the page say one thing
        // ("press any one to see its page") and do another for the ones that have none.
        $partial = $this->fixture();
        unset($partial['shots']['karateclub']);

        $html = $this->page($partial);

        $this->assertStringNotContainsString('data-full=', $html);
        $this->assertStringNotContainsString('<dialog', $html);
        $this->assertStringNotContainsString('data-fit=', $html);
        $this->assertStringContainsString('Press any one to open the live page.', $html);
    }

    // ------------------------------------------------------- what the generated file names

    public function test_what_the_generated_file_names_exists_and_lies_inside_its_picture(): void
    {
        $stored = ExampleSchedules::shots();
        $listed = array_column(ExampleSchedules::all(), 'subdomain');

        $this->assertEqualsCanonicalizing($listed, array_keys($stored['art']), 'config/example_shots.php does not hold a picture for exactly the listed schedules: run php artisan app:generate-example-shots');
        $this->assertEqualsCanonicalizing($listed, array_keys($stored['shots']), 'config/example_shots.php does not hold a photograph for exactly the listed schedules');
        $this->assertNotEmpty($stored['xray'], 'config/example_shots.php holds no pictures for "Look closer"');

        foreach ($stored['art'] as $subdomain => $own) {
            $this->assertContains($subdomain, $listed, "{$subdomain} has a picture and is not a listed schedule");
            $this->assertFileExists(public_path(ExampleSchedules::ART_DIR.$own['header']));
            $this->assertFileExists(public_path(ExampleSchedules::ART_DIR.$own['logo']));
            [$width, $height] = getimagesize(public_path(ExampleSchedules::ART_DIR.$own['header']));
            $this->assertSame([$width, $height], [$own['w'], $own['h']], "{$subdomain}'s recorded size is not its picture's, so its tile is the wrong shape");
        }

        foreach ($stored['shots'] as $subdomain => $shot) {
            $this->assertContains($subdomain, $listed);
            $this->assertFileExists(public_path(ExampleSchedules::PAGES_DIR.$shot['full']['file']));
            $title = $shot['title'];
            $this->assertNotSame('', trim($title['text']), "{$subdomain} was photographed with no name on it");
            $this->assertGreaterThanOrEqual(0, $title['x']);
            $this->assertLessThanOrEqual(390, $title['x'] + $title['w'], "{$subdomain}'s name runs off its page");
            $this->assertGreaterThan(0, $title['y']);
            $this->assertLessThan($shot['full']['h'], $title['y'] + $title['h'], "{$subdomain}'s name is below its picture");

            if (! empty($title['font'])) {
                $this->assertNotNull(font_stylesheet_url($title['font']), "{$subdomain}'s typeface {$title['font']} is not one the app bundles");
            }
        }

        foreach ($stored['xray'] as $name => $picture) {
            $this->assertFileExists(public_path(ExampleSchedules::PAGES_DIR.$picture['file']));

            foreach ($picture['pins'] as $part => $at) {
                $this->assertGreaterThanOrEqual(0, $at['x'], "{$part} on {$name}");
                $this->assertLessThanOrEqual($picture['w'], $at['x'] + $at['w'], "{$part} runs off {$name}");
                $this->assertLessThan($picture['h'], $at['y'], "{$part} is below {$name}");
            }
        }
    }

    public function test_the_script_the_page_runs_on_parses_with_the_pictures_and_without(): void
    {
        $pages = [
            'with the pictures' => $this->page(),
            'without them' => $this->page(['taken' => null, 'art' => [], 'shots' => [], 'xray' => []]),
        ];

        foreach ($pages as $which => $html) {
            preg_match_all('#<script nonce="[^"]*">(.*?)</script>#s', $html, $found);
            $scripts = array_values(array_filter($found[1], fn ($script) => str_contains($script, 'data-ex-title')));
            $this->assertCount(1, $scripts, "the page's own script was not found {$which}");

            $file = tempnam(sys_get_temp_dir(), 'examples-script-').'.js';
            file_put_contents($file, $scripts[0]);

            try {
                $process = new Process(['node', '--check', $file]);
                $process->setTimeout(30);
                $process->run();

                $this->assertTrue($process->isSuccessful(), "The page's script does not parse {$which}. If node is missing, install it - this test must not be skipped.\n".$process->getErrorOutput());
            } finally {
                @unlink($file);
                @unlink(substr($file, 0, -3));
            }
        }
    }

    // ------------------------------------------------------- nothing read off a page is trusted

    public function test_a_value_read_off_a_demo_page_cannot_leave_its_style_attribute(): void
    {
        // What somebody could set on a demo anyone can edit: a typeface and a colour that close
        // the declaration and open their own, and a light colour that is not three numbers.
        $hostile = fn (string $start, string $tag) => $start.'; background: url(https://evil.example/'.$tag.')';
        $html = $this->page($this->fixture(
            [
                'font' => "Roboto'".$hostile('', 'font'),
                'color' => $hostile('red', 'color'),
                'weight' => $hostile('600', 'weight'),
                'size' => $hostile('32', 'size'),
                'x' => $hostile('44', 'x'),
                'y' => $hostile('229', 'y'),
                'w' => $hostile('302', 'w'),
                'h' => $hostile('80', 'h'),
            ],
            [
                'glow' => $hostile('1 2 3', 'glow'),
                'full' => ['file' => 'a-page.webp', 'h' => $hostile('1640', 'height'), 'pw' => $hostile('780', 'pw'), 'ph' => $hostile('3280', 'ph')],
            ]
        ));

        $this->assertStringNotContainsString('evil.example', $html);
        $this->assertSame(0, preg_match('#style="[^"]*(?:background|url\()#', $this->wall($html, 'examples')), 'a photographed value opened a declaration of its own');
        // The checked fallbacks stand in: the page's own ink, a plain weight, the default light,
        // and every measurement as the number it began with.
        $this->assertStringContainsString('--ink: #151b26', $html);
        $this->assertStringContainsString('--wt: 600;', $html);
        $this->assertStringContainsString('data-glow="96 124 200"', $html);
        $this->assertStringContainsString('data-title-style="--pw: 390; --x: 44; --y: 229; --w: 302; --th: 80; --h: 1640; --fs: 32; --wt: 600; --ink: #151b26;"', $html);
        $this->assertStringContainsString('data-full-pw="780" data-full-ph="3280"', $html);
    }

    public function test_a_typeface_is_used_only_if_the_app_bundles_it(): void
    {
        $bundled = $this->page($this->fixture(['font' => 'Roboto']));
        $this->assertStringContainsString("--font: 'Roboto'", $bundled);
        $this->assertSame(1, preg_match_all('#<link rel="stylesheet" href="[^"]*/vendor/fonts/Roboto/font\.css">#', $bundled));

        $unknown = $this->page($this->fixture(['font' => 'No Such Typeface']));
        $this->assertStringNotContainsString('No Such Typeface', $unknown);
        $this->assertStringNotContainsString('--font:', $this->wall($unknown, 'examples'));
    }

    // ------------------------------------------------------- the name, and where it goes

    public function test_the_name_box_keeps_a_name_as_written_and_every_sign_up_link_can_carry_it(): void
    {
        $html = $this->page();

        // A plain field: the shared address box would turn "Joe's Bar & Grill" into an address as
        // it is typed. The address the name makes is handed to that box, in the last panel.
        $this->assertSame(1, preg_match('#<div class="hp-claim ex-namebox">\s*<input id="ex-name" data-ex-namebox type="text"#', $html));
        $this->assertSame(1, preg_match('#<div dir="ltr" class="es-claim hp-claim">\s*<input id="es-claim-input"#', $html));

        // The hero's button, the pill's and the free space's are sign-up links the shared script
        // gives the address to; the last panel's is the one beside the address box.
        $this->assertGreaterThanOrEqual(3, preg_match_all('#<a href="[^"]*/sign_up" class="[^"]*"\s+data-claim-link>#', $html));

        // One h1, and it still carries the page's search phrase.
        $this->assertSame(1, preg_match_all('#<h1\b#', $html));
        $this->assertSame(1, preg_match('#<h1[^>]*>.*?Event Schedule examples.*?</h1>#s', $html));
    }
}
