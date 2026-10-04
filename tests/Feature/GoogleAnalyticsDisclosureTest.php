<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * What a visitor is told about Google Analytics follows whether this install loads it.
 *
 * Google Analytics is one env var, ANALYTICS_ID, and google_analytics_enabled() is the predicate
 * behind the tag itself. The privacy policy, the cookie banner, /about and /features/analytics all
 * used to say it was in use whatever that variable held, so removing it would have left four
 * surfaces describing a tracker that no longer ran. Each of them now asks the same helper, and
 * the next sentence about Google Analytics will be pasted from one of them: this test is the
 * thing that fails when it is pasted without the question.
 *
 * The rendered assertions run on the RAW response, scripts and JSON-LD included, so a page of an
 * install without Google Analytics does not name it anywhere a visitor or a crawler can read,
 * view-source included. That only holds because nothing ships a comment that names it: the
 * consent reader is inlined without its header (consent_state_script()), and notes inside inline
 * scripts are Blade comments. A script comment that names it fails here, which is intended.
 *
 * What it cannot see: a new unconditional sentence in a file that already asks the helper passes
 * the file-level sweep. The rendered /privacy and /about checks are what catch that, and they
 * cover only those two pages.
 */
class GoogleAnalyticsDisclosureTest extends TestCase
{
    use RefreshDatabase;

    private const NAME = 'Google Analytics';

    /**
     * Views that name Google Analytics without asking the helper, because what they say is true
     * whether or not this install loads it.
     *
     * Keyed file => why, so an exemption cannot be added without stating a reason.
     * test_the_allow_lists_have_no_stale_entries() fails if one stops matching.
     */
    private const ALLOWED_VIEWS = [
        'marketing/faq.blade.php' => 'says the product has no Google Analytics integration for schedules',
        'marketing/index.blade.php' => 'says the built-in analytics need no Google Analytics account',
        'marketing/docs/selfhost/admin.blade.php' => 'operator docs: what ANALYTICS_ID and the header code box do',
    ];

    /** English strings that may name it, by file. Same contract as the views. */
    private const ALLOWED_KEYS = [
        'messages.php' => [
            'cookie_consent_analytics_help' => 'the banner shows it only while google_analytics_enabled()',
            'custom_header_code_help' => 'operator help: what the header code box accepts',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Realtime is what the Analytics category covers once Google Analytics is gone, and it is
        // also what raises the banner here: nothing else consent-gated is switched on.
        Cache::flush();
        Setting::set('realtime_enabled', '1');
        config([
            'ads.enabled' => false,
            'stay22.enabled' => false,
            'services.meta.pixel_id' => null,
            'app.cookie_consent_banner' => false,
        ]);
    }

    public function test_the_helper_follows_the_analytics_id(): void
    {
        config(['services.google.analytics' => null]);
        $this->assertFalse(google_analytics_enabled());

        config(['services.google.analytics' => '']);
        $this->assertFalse(google_analytics_enabled(), 'a blank ANALYTICS_ID is not an ID');

        config(['services.google.analytics' => 'G-TEST123']);
        $this->assertTrue(google_analytics_enabled());
    }

    public function test_the_privacy_policy_does_not_name_google_analytics_where_it_is_not_loaded(): void
    {
        config(['services.google.analytics' => null]);

        $html = $this->get('/privacy')->assertOk()->getContent();

        $this->assertStringNotContainsString(self::NAME, $html);
        $this->assertStringNotContainsString('Separately from Google', $html);
        // Not a bare "_ga": the AdSense row's __gads contains it.
        $this->assertStringNotContainsString('_ga, _ga_', $html);

        // What the Analytics category still covers is said in full: clause 04, then clause 12.
        $this->assertStringContainsString('The identified version of our live view, and remembering which homepage headline you saw', $html);
        $this->assertStringContainsString('the identified version of our live view (below), and remembering until the tab closes which homepage headline you saw.', $html);
        $this->assertStringContainsString('Separately, we keep our own visit statistics', $html);
        $this->assertStringContainsString('es_hero, es_hero_clicked', $html);
    }

    public function test_the_privacy_policy_names_google_analytics_where_it_is_loaded(): void
    {
        config(['services.google.analytics' => 'G-TEST123']);

        $html = $this->get('/privacy')->assertOk()->getContent();

        $this->assertStringContainsString('>Google Analytics<', $html, 'the provider schedule (clause 10)');
        $this->assertStringContainsString('Google Analytics, the identified live view, and remembering which homepage headline you saw', $html, 'the legal bases (clause 04)');
        $this->assertStringContainsString('Google Analytics 4, the identified version of our live view (below)', $html, 'the Analytics category (clause 12)');
        $this->assertStringContainsString("Google Analytics' advertising features;", $html, 'the marketing category (clause 12)');
        $this->assertStringContainsString('Separately from Google, we keep our own visit statistics', $html);
        $this->assertStringContainsString('_ga, _ga_&lt;measurement-id&gt;', $html, 'the cookie table');
    }

    public function test_the_banner_describes_the_analytics_category_this_install_has(): void
    {
        $with = __('messages.cookie_consent_analytics_help');
        $without = __('messages.cookie_consent_analytics_help_no_ga');

        // The marketing shell and the auth shell: one partial, but two of the four layouts.
        foreach (['/pricing', '/login'] as $path) {
            config(['services.google.analytics' => null]);
            $this->get($path)->assertOk()
                ->assertSee('data-cookie-consent-category="analytics"', false)
                ->assertSee($without)
                ->assertDontSee($with);

            config(['services.google.analytics' => 'G-TEST123']);
            $this->get($path)->assertOk()
                ->assertSee($with)
                ->assertDontSee($without);
        }
    }

    public function test_the_about_page_follows_the_helper(): void
    {
        config(['services.google.analytics' => null]);
        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringNotContainsString(self::NAME, $html);
        $this->assertStringNotContainsString('analytics and advertising partners', $html);
        $this->assertStringContainsString('First-party. Visits are counted as daily totals without a cookie', $html);
        $this->assertStringContainsString('Analytics that identify a visitor are opt-in', $html);
        // Twice: the answer on the page, and the FAQ schema fed from the same array, so the answer
        // search engines read moved with it.
        $this->assertSame(2, substr_count($html, 'any advertising partners the privacy policy names'));
        $this->assertSame(2, substr_count($html, 'nothing optional is stored or loaded until you allow it'));

        config(['services.google.analytics' => 'G-TEST123']);
        $html = $this->get('/about')->assertOk()->getContent();

        $this->assertStringContainsString('Opt-in. Google Analytics is not loaded at all until you allow analytics in the cookie banner', $html);
        $this->assertStringContainsString('analytics and advertising partners only with your consent. Analytics are opt-in and stay off until you allow them.', $html);
        $this->assertSame(2, substr_count($html, 'nothing from Google Analytics loads until you allow it'));
    }

    public function test_the_analytics_page_says_what_eventschedule_com_itself_runs(): void
    {
        $runs = 'eventschedule.com runs Google Analytics on its own pages';
        $doesNot = 'eventschedule.com does not run Google Analytics on its own pages either';

        config(['services.google.analytics' => null]);
        $html = $this->get('/features/analytics')->assertOk()->getContent();

        // Twice each time: the visible answer and the FAQ schema.
        $this->assertSame(0, substr_count($html, $runs));
        $this->assertSame(2, substr_count($html, $doesNot));
        // The question is "or any other tracker", so the answer still says what waits for consent.
        $this->assertSame(2, substr_count($html, 'what waits for a visitor to allow it in the cookie banner'));

        config(['services.google.analytics' => 'G-TEST123']);
        $html = $this->get('/features/analytics')->assertOk()->getContent();

        $this->assertSame(0, substr_count($html, $doesNot));
        $this->assertSame(2, substr_count($html, $runs));
    }

    /**
     * The reader every page inlines carries no header comment, which is what lets the assertions
     * above run on raw HTML: that comment lists what the analytics category can cover.
     */
    public function test_the_consent_reader_is_inlined_without_its_header_comment(): void
    {
        $source = File::get(resource_path('js/consent-state.js'));
        $inlined = consent_state_script();

        $this->assertStringStartsWith('/*', ltrim($source), 'the file still opens with the comment this strips');
        $this->assertStringStartsWith('(function (w) {', $inlined);
        $this->assertStringNotContainsString(self::NAME, $inlined);
        // Nothing but the header went: the code is the tail of the file, byte for byte.
        $this->assertStringEndsWith($inlined, $source);

        config(['services.google.analytics' => null]);
        $html = $this->get('/pricing')->assertOk()->getContent();

        $this->assertStringContainsString('w.esConsent = {', $html, 'the reader must still be inlined');
        $this->assertStringNotContainsString('This one file is the only parser', $html, 'the header comment is being sent to browsers again');
    }

    /**
     * Read from the arrays, never through __(): a missing key falls back to English there, and
     * would pass as a translation.
     */
    public function test_every_language_has_a_banner_line_without_google_analytics(): void
    {
        $english = require resource_path('lang/en/messages.php');

        foreach (array_keys(config('app.supported_languages')) as $lang) {
            $messages = require resource_path("lang/{$lang}/messages.php");

            $this->assertArrayHasKey('cookie_consent_analytics_help_no_ga', $messages, $lang);
            $this->assertStringNotContainsString('Google', $messages['cookie_consent_analytics_help_no_ga'], $lang);
            $this->assertStringContainsString(self::NAME, $messages['cookie_consent_analytics_help'], $lang);

            if ($lang !== 'en') {
                $this->assertNotSame(
                    $english['cookie_consent_analytics_help_no_ga'],
                    $messages['cookie_consent_analytics_help_no_ga'],
                    "{$lang} carries the English string"
                );
            }
        }
    }

    /**
     * The accessibility statement listed "optional analytics" among the third parties the service
     * relies on. It has no "Google" in it, so nothing above would notice it coming back.
     */
    public function test_the_accessibility_statement_does_not_list_analytics_as_a_third_party(): void
    {
        $body = (require resource_path('lang/en/accessibility.php'))['section_third_party_body'];

        $this->assertStringNotContainsStringIgnoringCase('analytics', $body);

        foreach (array_keys(config('app.supported_languages')) as $lang) {
            $translated = (require resource_path("lang/{$lang}/accessibility.php"))['section_third_party_body'] ?? null;

            $this->assertIsString($translated, $lang);
            $this->assertStringContainsString('Stripe', $translated, "{$lang} lost the rest of the list");
        }
    }

    public function test_no_view_names_google_analytics_without_asking_the_helper(): void
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            $relative = $this->relativePath($file);
            $contents = $this->withoutComments(File::get($file->getPathname()));

            if (! str_contains($contents, self::NAME)) {
                continue;
            }

            if (str_contains($contents, 'google_analytics_enabled(') || array_key_exists($relative, self::ALLOWED_VIEWS)) {
                continue;
            }

            $offenders[] = $relative;
        }

        $this->assertSame([], $offenders, implode("\n", array_merge(
            ['A view says "Google Analytics" without asking google_analytics_enabled(), so it would'],
            ['keep saying it on an install that has removed ANALYTICS_ID. Offending views:'],
            $offenders,
        )));
    }

    public function test_no_english_string_or_controller_copy_names_it_unconditionally(): void
    {
        foreach (File::files(resource_path('lang/en')) as $file) {
            $allowed = self::ALLOWED_KEYS[$file->getFilename()] ?? [];

            foreach (Arr::dot(require $file->getPathname()) as $key => $value) {
                if (is_string($value) && str_contains($value, self::NAME)) {
                    $this->assertArrayHasKey($key, $allowed, "{$file->getFilename()}: '{$key}' names Google Analytics; show it only behind google_analytics_enabled() and list it here with the reason");
                }
            }
        }

        // Page copy lives here too (FAQ answers, comparison tables, the doc search index).
        $this->assertStringNotContainsString(
            self::NAME,
            File::get(app_path('Http/Controllers/MarketingController.php')),
            'MarketingController copy cannot ask the helper per sentence; build the claim in the view'
        );
    }

    public function test_the_allow_lists_have_no_stale_entries(): void
    {
        foreach (self::ALLOWED_VIEWS as $relative => $reason) {
            $path = resource_path('views').DIRECTORY_SEPARATOR.$relative;

            $this->assertFileExists($path, "Allow-listed view no longer exists: {$relative} ({$reason})");

            $contents = $this->withoutComments(File::get($path));

            $this->assertStringContainsString(self::NAME, $contents, "{$relative} no longer names Google Analytics; drop it from the allow-list");
            $this->assertStringNotContainsString('google_analytics_enabled(', $contents, "{$relative} asks the helper now; drop it from the allow-list");
        }

        foreach (self::ALLOWED_KEYS as $filename => $keys) {
            $strings = Arr::dot(require resource_path('lang/en/'.$filename));

            foreach ($keys as $key => $reason) {
                $this->assertStringContainsString(self::NAME, (string) ($strings[$key] ?? ''), "{$filename}: '{$key}' no longer names Google Analytics; drop it from the allow-list");
            }
        }
    }

    /**
     * A view without its Blade comments and its whole-line // comments, so a file passes the sweep
     * for calling the helper in code and never for a comment that mentions it.
     *
     * By hand rather than with a lazy regex: preg_replace() answers a PCRE limit with null, which
     * would read as an empty file and pass every check at once.
     */
    private function withoutComments(string $contents): string
    {
        $out = '';
        $offset = 0;

        while (($start = strpos($contents, '{{--', $offset)) !== false) {
            $close = strpos($contents, '--}}', $start + 4);

            if ($close === false) {
                break;
            }

            $out .= substr($contents, $offset, $start - $offset);
            $offset = $close + 4;
        }

        $lines = explode("\n", $out.substr($contents, $offset));

        return implode("\n", array_filter($lines, fn (string $line) => ! str_starts_with(ltrim($line), '//')));
    }

    private function relativePath(\SplFileInfo $file): string
    {
        return str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file->getPathname());
    }
}
