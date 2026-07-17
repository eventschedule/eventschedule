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
 * RLM) to every non-empty TEXT line. This forces each line - and, because the
 * first character of the whole message is then an RLM, the entire message bubble -
 * to display right-to-left when pasted into apps like WhatsApp (which resolve
 * direction from the first strong directional character).
 *
 * A line that is nothing but a URL is the exception: it is left completely free of
 * invisible bidi characters (no RLM, no isolate). WhatsApp's link auto-detection is
 * unreliable when any invisible char sits next to a URL - a leading mark can stop
 * the match starting at the URL, and a trailing one can be swallowed into the
 * tapped href and corrupt it. The Left-to-Right Isolate (U+2066 ... U+2069) that
 * wraps the {url} token is only a transient marker so generate() can spot the URL
 * line; it is stripped back off there, leaving a bare, link-detectable URL that
 * renders LTR (left-aligned) inside the RTL bubble. LTR-language and force-English
 * text get no marks at all.
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

    public function test_text_lines_are_rtl_marked_and_url_lines_are_clean_for_rtl_schedule(): void
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

        // Every non-empty TEXT line carries the RLM; URL-only lines are exempt (see
        // below); blank separator lines stay blank.
        foreach ($lines as $line) {
            if ($line === '' || str_contains($line, 'eventschedule.test')) {
                continue;
            }
            $this->assertStringStartsWith(self::RLM, $line, "line not RTL-marked: {$line}");
        }

        // URL lines (event link + "want to see your event here?" request link) are
        // completely clean: no RLM and no isolate, so WhatsApp reliably auto-links
        // them. The host is the very first character of the line.
        $urlLines = array_values(array_filter($lines, fn ($l) => str_contains($l, 'eventschedule.test')));
        $this->assertGreaterThanOrEqual(2, count($urlLines), 'expected event + request URL lines');
        foreach ($urlLines as $urlLine) {
            $this->assertStringStartsWith('eventschedule.test', $urlLine, "URL line not clean: {$urlLine}");
            $this->assertStringNotContainsString(self::RLM, $urlLine);
            $this->assertStringNotContainsString(self::LRI, $urlLine);
            $this->assertStringNotContainsString(self::PDI, $urlLine);
        }

        // A Hebrew text line still carries the RTL marker (the intended display).
        $nameLine = collect($lines)->first(fn ($l) => str_contains($l, 'מפגש בדיקה'));
        $this->assertNotNull($nameLine);
        $this->assertStringStartsWith(self::RLM, $nameLine);
    }

    public function test_ip_host_url_line_is_clean_for_rtl_schedule(): void
    {
        // Selfhost via a raw IP: the URL line is left completely clean (no RLM, no
        // isolate) so it stays link-detectable in WhatsApp.
        URL::forceRootUrl('http://192.168.0.10');

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        $text = EventTextGenerator::generate($role, [$event]);

        $urlLine = collect(explode("\n", $text))->first(fn ($l) => str_contains($l, '192.168.0.10'));
        $this->assertNotNull($urlLine, 'expected an IP-host URL line');
        $this->assertStringStartsWith('192.168.0.10', $urlLine);
        $this->assertStringNotContainsString(self::RLM, $urlLine);
        $this->assertStringNotContainsString(self::LRI, $urlLine);
        $this->assertStringNotContainsString(self::PDI, $urlLine);
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

    public function test_scheme_included_url_line_is_clean_for_rtl_schedule(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        // With url_include_https on, the URL keeps its scheme (https://...) and the
        // line is still left completely clean (no RLM, no isolate).
        $text = EventTextGenerator::generate($role, [$event], false, null, ['url_include_https' => true]);

        $urlLine = collect(explode("\n", $text))->first(fn ($l) => str_contains($l, 'https://eventschedule.test'));
        $this->assertNotNull($urlLine);
        $this->assertStringStartsWith('https://eventschedule.test', $urlLine);
        $this->assertStringNotContainsString(self::RLM, $urlLine);
        $this->assertStringNotContainsString(self::LRI, $urlLine);
        $this->assertStringNotContainsString(self::PDI, $urlLine);
    }

    public function test_default_template_url_lines_are_bare_for_rtl_schedule(): void
    {
        $this->forceDottedHost();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'language_code' => 'he',
            'accept_requests' => true,
        ]);
        $event = $this->createEvent($role, ['name' => 'מפגש בדיקה']);

        $text = EventTextGenerator::generate($role, [$event]);

        // The bubble still opens RTL (the first line is a text line).
        $this->assertStringStartsWith(self::RLM, $text);

        // For the default template every URL is alone on its line, so the isolate
        // marker is fully consumed - NOT A SINGLE isolate char survives anywhere in
        // the message. This is the strongest guard against reintroducing an invisible
        // character next to a URL (the whole cause of the WhatsApp link breakage).
        $this->assertStringNotContainsString(self::LRI, $text);
        $this->assertStringNotContainsString(self::PDI, $text);

        // Each URL line begins with the bare host (no leading bidi mark) and carries
        // no RLM, so WhatsApp reliably auto-links it.
        $urlLines = array_values(array_filter(explode("\n", $text), fn ($l) => str_contains($l, 'eventschedule.test')));
        $this->assertNotEmpty($urlLines);
        foreach ($urlLines as $urlLine) {
            $this->assertStringStartsWith('eventschedule.test', $urlLine, "URL line not bare: {$urlLine}");
            $this->assertStringNotContainsString(self::RLM, $urlLine);
        }
    }
}
