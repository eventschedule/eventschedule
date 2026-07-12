<?php

namespace Tests\Feature;

use App\Utils\EventTextGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Regression coverage for the WhatsApp share text (event graphics).
 *
 * v1.0.103 changed generate() to prepend a bidi marker to EVERY line. For
 * Hebrew/Arabic that marker is U+200F (RLM), which on a URL line forces the URL
 * to render right-to-left and breaks desktop WhatsApp's link auto-detection so
 * the link won't open. The fix marks ONLY lines that contain Hebrew/Arabic; URLs
 * (and any other LTR content) are left unmarked regardless of host shape.
 */
class EventTextGeneratorTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const RLM = "\u{200F}";

    private const LRM = "\u{200E}";

    /** Force a dotted-TLD root so the scheme-less guest URL looks like production. */
    private function forceDottedHost(): void
    {
        URL::forceRootUrl('https://eventschedule.test');
    }

    public function test_url_lines_are_left_unmarked_for_rtl_schedule(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'language_code' => 'he',
            'accept_requests' => true,
        ]);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        $text = EventTextGenerator::generate($role, [$event]);
        $lines = explode("\n", $text);

        // The URL lines (event link + "want to see your event here?" request link)
        // must NOT carry the RTL marker, or WhatsApp desktop can't open them.
        $urlLines = array_values(array_filter($lines, fn ($l) => str_contains($l, 'eventschedule.test')));
        $this->assertGreaterThanOrEqual(2, count($urlLines), 'expected event + request URL lines');
        foreach ($urlLines as $urlLine) {
            $this->assertStringStartsNotWith(self::RLM, $urlLine, "URL line unexpectedly marked RTL: {$urlLine}");
            $this->assertStringStartsWith('eventschedule.test', $urlLine);
        }

        // A Hebrew text line still carries the RTL marker (the intended display).
        $nameLine = collect($lines)->first(fn ($l) => str_contains($l, 'מפגש בדיקה'));
        $this->assertNotNull($nameLine);
        $this->assertStringStartsWith(self::RLM, $nameLine);
    }

    public function test_ip_host_url_line_is_left_unmarked_for_rtl_schedule(): void
    {
        // Selfhost via a raw IP: the URL has no Hebrew, so it stays unmarked/LTR.
        URL::forceRootUrl('http://192.168.0.10');

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        $text = EventTextGenerator::generate($role, [$event]);

        $urlLine = collect(explode("\n", $text))->first(fn ($l) => str_contains($l, '192.168.0.10'));
        $this->assertNotNull($urlLine, 'expected an IP-host URL line');
        $this->assertStringStartsNotWith(self::RLM, $urlLine);
        $this->assertStringStartsWith('192.168.0.10', $urlLine);
        // Marking is still active for Hebrew content (guards against a vacuous pass).
        $this->assertStringContainsString(self::RLM, $text);
    }

    public function test_latin_content_line_is_left_unmarked_for_rtl_schedule(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'Jazz Night']);

        $text = EventTextGenerator::generate($role, [$event]);
        $lines = explode("\n", $text);

        // An all-Latin event name (no Hebrew/Arabic) renders LTR - left unmarked.
        $nameLine = collect($lines)->first(fn ($l) => str_contains($l, 'Jazz Night'));
        $this->assertNotNull($nameLine);
        $this->assertStringStartsNotWith(self::RLM, $nameLine);

        // The translated Hebrew day-name line is still marked (marking is active).
        $this->assertStringContainsString(self::RLM, $text);
    }

    public function test_ltr_schedule_gets_no_direction_marks(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'en']);
        $event = $this->createEvent($role, ['name' => 'Test Event']);

        $text = EventTextGenerator::generate($role, [$event]);

        // LTR-language schedules need no marker at all (pre-v1.0.103 behavior).
        $this->assertStringNotContainsString(self::RLM, $text);
        $this->assertStringNotContainsString(self::LRM, $text);
    }

    public function test_scheme_included_url_line_is_left_unmarked_for_rtl_schedule(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        // With url_include_https on, the URL keeps its scheme (https://...).
        $text = EventTextGenerator::generate($role, [$event], false, null, ['url_include_https' => true]);

        $urlLine = collect(explode("\n", $text))->first(fn ($l) => str_contains($l, 'https://eventschedule.test'));
        $this->assertNotNull($urlLine);
        $this->assertStringStartsWith('https://eventschedule.test', $urlLine);
    }
}
