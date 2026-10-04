<?php

namespace Tests\Feature;

use App\Services\LinkImportService;
use App\Utils\IcsImportUtils;
use App\Utils\ImportRun;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Keeps the site from saying a link cannot be imported, now that it can.
 *
 * Until October 2026 the import read only the text and the image it was handed, and the site said
 * so on purpose and at length: /features/ai was built around the headline "Two inputs cross it. A
 * link does not.", the import guide carried a callout titled "Text or image, not a link", and two
 * source comments gave the same premise as the reason for a wording choice. LinkImportService
 * made every one of them false on the day it shipped.
 *
 * Four checks, because any one alone passes on the wrong fix:
 *  - the old sentences must not come back, comments included (a comment that states the old
 *    premise is what talks the next editor into restoring the old copy);
 *  - the pages that now describe reading a link must also say what a link cannot do, so copy
 *    that was deleted rather than corrected still fails;
 *  - the guide quotes the import page's buttons and messages word for word, and must keep
 *    matching the language file when one of them is renamed;
 *  - the limits the guide states are the ones the code enforces, with their units.
 */
class LinkImportDocsClaimTest extends TestCase
{
    private const AI_PAGE = 'resources/views/marketing/ai.blade.php';

    private const GUIDE = 'resources/views/marketing/docs/ai-import.blade.php';

    /** Each is written to match the old sentence and not its corrected form. */
    private const STALE = [
        '~two inputs cross it~i',
        '~a URL for it to read~i',
        '~never reads a web page~i',
        '~does not go browsing~i',
        '~does not go and fetch a web page~i',
        '~text or image, not a link~i',
        '~pasting a URL on its own will not produce an event~i',
        '~paste the text,? not the\s+link~i',
        '~paste the text off the page rather than the link~i',
        '~nothing fetches a URL~i',
        '~takes pasted text\s+(//\s*)?or a dropped image only~i',
        '~importing events from a URL or by city~i',
    ];

    /** Retired pieces of the import page the guide used to give directions by. */
    private const STALE_IN_GUIDE = [
        '~More Options~',
        '~arrow button~i',
        '~Save All~',
        '~Type event details or drag~i',
        '~accepts two kinds of input~i',
    ];

    private function source(string $path): string
    {
        return File::get(base_path($path));
    }

    /** @return array<string, string> path => contents, for every marketing view and its copy sources */
    private function marketingSources(): array
    {
        $files = [];
        foreach (File::allFiles(resource_path('views/marketing')) as $file) {
            $files['resources/views/marketing/'.$file->getRelativePathname()] = $file->getContents();
        }
        foreach (['app/Http/Controllers/MarketingController.php', 'config/docs.php', 'docs/FEATURES.md', 'public/llms.txt', 'public/llms-full.txt'] as $path) {
            $files[$path] = $this->source($path);
        }

        return $files;
    }

    public function test_no_page_says_a_link_cannot_be_read(): void
    {
        $found = [];
        foreach ($this->marketingSources() as $path => $contents) {
            foreach (self::STALE as $pattern) {
                if (preg_match($pattern, $contents, $match)) {
                    $found[] = $path.': "'.$match[0].'"';
                }
            }
        }

        $this->assertSame([], $found, 'A link is read by the import page since October 2026 (LinkImportService).');
    }

    public function test_the_guide_gives_directions_by_what_is_on_the_page_now(): void
    {
        $guide = $this->source(self::GUIDE);

        foreach (self::STALE_IN_GUIDE as $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, $guide);
        }
    }

    public function test_pages_that_describe_reading_a_link_say_what_it_cannot_do(): void
    {
        // The feature page: it reads links, and it is plain about the ones it cannot. Checked in
        // the section and in the FAQ answer separately: the page says "Facebook and Instagram"
        // in three places (the concept note at the top is one), so a page-wide search passed
        // with the card's own sentence deleted.
        $page = $this->source(self::AI_PAGE);
        $section = Str::between($page, '<section id="crosses"', '</section>');
        $this->assertStringContainsString('A link to where they are listed', $section);
        foreach (['A sign-in wall does not', 'Facebook and Instagram', 'A private calendar cannot be read from its link', 'one-time copy, not a subscription', 'not on the public submission form'] as $limit) {
            $this->assertStringContainsString($limit, $section, "The section on what the import reads no longer says: {$limit}");
        }
        $answer = Str::between($page, "'q' => 'What can the AI parser read?'", '],');
        foreach (['a link you paste on your own import screen', 'uses no AI at all', 'Facebook and Instagram', 'only loads its events after it opens'] as $limit) {
            $this->assertStringContainsString($limit, $answer, "The FAQ on what the parser reads no longer says: {$limit}");
        }
        $this->assertStringContainsString('their form does not take is a link', $page);
        // Still a complete grid: three that cross and one that does not, two by two.
        $this->assertSame(3, substr_count($page, '<span class="es-spark-num mb-4">IN</span>'));
        $this->assertSame(1, substr_count($page, '<span class="es-spark-num mb-4">NOT IN</span>'));
        $this->assertStringContainsString('<div class="grid gap-4 sm:grid-cols-2" data-reveal-group="90">', $page);

        // The guide: where it is, what it reads without AI, and the same limits.
        $guide = $this->source(self::GUIDE);
        foreach (['id="link-import"', 'id="choosing-events"', 'id="undo-import"'] as $anchor) {
            $this->assertStringContainsString($anchor, $guide);
        }
        foreach (['A link is a one-time copy', 'Facebook and Instagram', 'not on the public request form', 'involves no AI', 'uses none of the allowance', 'someone already holds a ticket or a booking for is kept'] as $limit) {
            $this->assertStringContainsString($limit, $guide, "The import guide describes link import without: {$limit}");
        }

        // The daily sweep of a list of URLs is a different thing and still selfhost-only.
        $this->assertStringContainsString('A daily automatic import from a list of URLs, filtered by city, also selfhost-only', $this->source('resources/views/marketing/about.blade.php'));

        // The privacy policy names what is sent to the model: a page's text now is.
        $this->assertStringContainsString("the text of a web page whose link a schedule's editor pastes", $this->source('resources/views/marketing/privacy.blade.php'));
    }

    public function test_the_guide_quotes_the_import_page_word_for_word(): void
    {
        $guide = $this->source(self::GUIDE);

        $labels = [
            'import_box_placeholder', 'import_link_detected', 'import_read_link', 'import_read_text',
            'import_read_flyer', 'import_other_sources', 'import_from_eventbrite', 'import_read_whole_page',
            'import_row_incomplete', 'import_already_listed', 'import_start_over', 'import_all_day',
            'eventbrite_select_all', 'add_image', 'get_api_key',
            'import_panel_undo', 'import_panel_embed', 'import_panel_view_schedule', 'import_panel_import_more',
        ];
        foreach ($labels as $key) {
            $label = trans('messages.'.$key, [], 'en');
            $this->assertNotSame('messages.'.$key, $label, "No English string for {$key}");
            $this->assertStringContainsString($label, $guide, "The import guide no longer quotes \"{$label}\" ({$key}).");
        }

        // Labels that carry a number are quoted by their fixed part.
        foreach (['import_panel_title' => ': :count', 'import_times_shown_in' => ' :timezone'] as $key => $tail) {
            $label = str_replace($tail, '', trans('messages.'.$key, [], 'en'));
            $this->assertStringNotContainsString(':', $label);
            $this->assertStringContainsString($label, $guide, "The import guide no longer quotes \"{$label}\" ({$key}).");
        }
        $this->assertStringContainsString(trans('messages.import_add_many', ['count' => 12], 'en'), $guide);
        $this->assertStringContainsString(trans('messages.import_saving_progress', ['done' => 3, 'total' => 12], 'en'), $guide);
        $this->assertStringContainsString(trans('messages.import_selected_count', ['selected' => 12, 'total' => 14], 'en'), $guide);
    }

    public function test_the_limits_the_copy_states_are_the_ones_the_code_enforces(): void
    {
        $guide = $this->source(self::GUIDE);
        $page = $this->source(self::AI_PAGE);

        $this->assertStringContainsString('Up to '.LinkImportService::MAX_EVENTS.' events come back per read', $guide);
        $this->assertStringContainsString('returns up to '.LinkImportService::MAX_EVENTS.' events', $guide);
        $this->assertStringContainsString('Up to '.LinkImportService::MAX_EVENTS.' at a time', $page);

        $this->assertStringContainsString('its next '.IcsImportUtils::SERIES_DATES.' dates', $guide);
        $this->assertSame(365, IcsImportUtils::WINDOW_DAYS, 'The guide says "the next 12 months".');
        $this->assertStringContainsString('for the next 12 months', $guide);

        $this->assertStringContainsString('offered for '.ImportRun::UNDO_HOURS.' hours', $guide);

        // "10 links a minute" is the limiter in EventController::parseLink(); "30 submissions a
        // minute" is the parse route's throttle.
        $this->assertStringContainsString('30 submissions a minute, and 10 links a minute', $guide);
        $this->assertMatchesRegularExpression('~RateLimiter::tooManyAttempts\\(\\$\\w+, 10\\)~', $this->source('app/Http/Controllers/EventController.php'));
        $this->assertContains('throttle:30,1', app('router')->getRoutes()->getByName('event.parse')->gatherMiddleware());
    }
}
