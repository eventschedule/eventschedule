<?php

namespace Tests\Feature;

use App\Utils\EventTextGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Regression coverage for the WhatsApp share text (event graphics) bidi handling.
 *
 * For Hebrew/Arabic schedules, generate() prepends a Right-to-Left Mark (U+200F,
 * RLM) to EVERY non-empty line. This forces each line - and, because the first
 * character of the whole message is then an RLM, the entire message bubble - to
 * display right-to-left when pasted into apps like WhatsApp (which resolve
 * direction from the first strong directional character).
 *
 * URLs stay clickable because they are wrapped in a Left-to-Right Isolate
 * (U+2066 ... U+2069): the URL is a clean LTR run inside the RTL line, instead of
 * being immediately preceded by a bare RLM (which broke desktop WhatsApp's link
 * auto-detection - the v1.0.103 regression). LTR-language and force-English text
 * get no marks at all.
 */
class EventTextGeneratorTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const RLM = "\u{200F}";

    private const LRM = "\u{200E}";

    private const LRI = "\u{2066}";

    private const PDI = "\u{2069}";

    /** Force a dotted-TLD root so the scheme-less guest URL looks like production. */
    private function forceDottedHost(): void
    {
        URL::forceRootUrl('https://eventschedule.test');
    }

    public function test_every_line_is_rtl_marked_and_urls_are_isolated_for_rtl_schedule(): void
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

        // The whole message starts with an RLM so WhatsApp renders the bubble RTL.
        $this->assertStringStartsWith(self::RLM, $text);

        // Every non-empty line carries the RLM; blank separator lines stay blank.
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }
            $this->assertStringStartsWith(self::RLM, $line, "line not RTL-marked: {$line}");
        }

        // URL lines (event link + "want to see your event here?" request link) are
        // still RTL-marked, but the URL itself is wrapped in an LTR isolate so it
        // stays a clean, link-detectable run.
        $urlLines = array_values(array_filter($lines, fn ($l) => str_contains($l, 'eventschedule.test')));
        $this->assertGreaterThanOrEqual(2, count($urlLines), 'expected event + request URL lines');
        foreach ($urlLines as $urlLine) {
            $this->assertStringStartsWith(self::RLM, $urlLine, "URL line not RTL-marked: {$urlLine}");
            // The URL is bounded by the isolate, with a clean host right after LRI.
            $this->assertStringContainsString(self::LRI.'eventschedule.test', $urlLine);
            $this->assertStringContainsString(self::PDI, $urlLine);
        }

        // A Hebrew text line still carries the RTL marker (the intended display).
        $nameLine = collect($lines)->first(fn ($l) => str_contains($l, 'מפגש בדיקה'));
        $this->assertNotNull($nameLine);
        $this->assertStringStartsWith(self::RLM, $nameLine);
    }

    public function test_ip_host_url_line_is_isolated_for_rtl_schedule(): void
    {
        // Selfhost via a raw IP: the URL is wrapped in an LTR isolate so it renders
        // correctly inside the RTL line.
        URL::forceRootUrl('http://192.168.0.10');

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        $text = EventTextGenerator::generate($role, [$event]);

        $urlLine = collect(explode("\n", $text))->first(fn ($l) => str_contains($l, '192.168.0.10'));
        $this->assertNotNull($urlLine, 'expected an IP-host URL line');
        $this->assertStringStartsWith(self::RLM, $urlLine);
        $this->assertStringContainsString(self::LRI.'192.168.0.10', $urlLine);
        $this->assertStringContainsString(self::PDI, $urlLine);
    }

    public function test_latin_content_line_is_rtl_marked_for_rtl_schedule(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'Jazz Night']);

        $text = EventTextGenerator::generate($role, [$event]);
        $lines = explode("\n", $text);

        // Even an all-Latin event name line is RTL-marked so the whole block stays
        // right-aligned in WhatsApp (the Latin text still reads LTR within the line).
        $nameLine = collect($lines)->first(fn ($l) => str_contains($l, 'Jazz Night'));
        $this->assertNotNull($nameLine);
        $this->assertStringStartsWith(self::RLM, $nameLine);
    }

    public function test_ltr_schedule_gets_no_direction_marks(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'en']);
        $event = $this->createEvent($role, ['name' => 'Test Event']);

        $text = EventTextGenerator::generate($role, [$event]);

        // LTR-language schedules need no marks at all.
        $this->assertStringNotContainsString(self::RLM, $text);
        $this->assertStringNotContainsString(self::LRM, $text);
        $this->assertStringNotContainsString(self::LRI, $text);
        $this->assertStringNotContainsString(self::PDI, $text);
    }

    public function test_force_english_gets_no_direction_marks_for_rtl_schedule(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        // Forced-English output is LTR, so it must carry no bidi marks.
        $text = EventTextGenerator::generate($role, [$event], false, null, [], true);

        $this->assertStringNotContainsString(self::RLM, $text);
        $this->assertStringNotContainsString(self::LRI, $text);
        $this->assertStringNotContainsString(self::PDI, $text);
    }

    public function test_scheme_included_url_line_is_isolated_for_rtl_schedule(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        // With url_include_https on, the URL keeps its scheme (https://...) and is
        // still wrapped in the LTR isolate.
        $text = EventTextGenerator::generate($role, [$event], false, null, ['url_include_https' => true]);

        $urlLine = collect(explode("\n", $text))->first(fn ($l) => str_contains($l, 'https://eventschedule.test'));
        $this->assertNotNull($urlLine);
        $this->assertStringContainsString(self::LRI.'https://eventschedule.test', $urlLine);
        $this->assertStringContainsString(self::PDI, $urlLine);
    }
}
