<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A Hebrew event title that embeds a Latin band name has to read right-to-left.
 *
 * detect_content_dir() decides by counting strong characters, so "להקת LadyD" (4 Hebrew
 * letters, 5 Latin ones) came out 'ltr'. That direction landed on the title element, the
 * browser resolved the trailing colon against an LTR base, and the whole title rendered
 * backwards on a Hebrew schedule - while a pure-Hebrew title beside it was fine. The
 * counting rule is now only consulted where the authoring language leaves real doubt;
 * see resolve_content_dir() in app/helpers.php.
 *
 * These assertions go through the real guest pages, not the helper, because the helper
 * being right is worth nothing if the payload or the template drops the value.
 */
class MixedScriptDirectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The three titles from the bug report, plus the pure-Hebrew one that always worked. */
    private const MIXED_TITLES = [
        'להקת LadyD',
        'להקת Rock Bandits חוזרת',
        'אבי אמתי & The Love Machine',
    ];

    private const HEBREW_ONLY_TITLE = 'ערב 3 הופעות מקור';

    private function hebrewSchedule(): Role
    {
        return $this->createRole($this->createOwner(), 'talent', [
            'name' => 'לוח הופעות',
            'language_code' => 'he',
            'event_layout' => 'list',
        ]);
    }

    /**
     * The events the guest page actually renders.
     *
     * Not scraped from the HTML: the schedule page ships an empty payload and fetches
     * /api/calendar-events, so that endpoint (CalendarDataTrait) is the builder the live
     * page really uses. The Blade closure in calendar.blade.php only runs for ?graphic.
     */
    private function vueEvents(Role $role): array
    {
        $response = $this->getJson('/'.$role->subdomain.'/api/calendar-events?'.http_build_query([
            'year' => now()->addDays(7)->year,
            'month' => now()->addDays(7)->month,
        ]))->assertOk();

        return $response->json('events') ?? [];
    }

    private function eventNamed(array $events, string $name): array
    {
        foreach ($events as $event) {
            if (($event['name'] ?? null) === $name) {
                return $event;
            }
        }

        $this->fail('No event named "'.$name.'" in the payload.');
    }

    public function test_a_hebrew_title_with_a_latin_band_name_is_tagged_rtl_in_the_calendar_payload(): void
    {
        $role = $this->hebrewSchedule();

        foreach (self::MIXED_TITLES as $title) {
            $this->createEvent($role, ['name' => $title]);
        }
        $this->createEvent($role, ['name' => self::HEBREW_ONLY_TITLE]);

        $events = $this->vueEvents($role);

        foreach (self::MIXED_TITLES as $title) {
            $this->assertSame('rtl', $this->eventNamed($events, $title)['dir'], $title.' should read RTL');
        }

        // The one that was never broken must not move.
        $this->assertSame('rtl', $this->eventNamed($events, self::HEBREW_ONLY_TITLE)['dir']);
    }

    public function test_a_latin_only_title_on_a_hebrew_schedule_stays_ltr(): void
    {
        $role = $this->hebrewSchedule();
        $this->createEvent($role, ['name' => 'Jazz Night']);

        $events = $this->vueEvents($role);

        // Nothing RTL in it at all, so the schedule's language has nothing to anchor to.
        $this->assertSame('ltr', $this->eventNamed($events, 'Jazz Night')['dir']);
    }

    public function test_the_event_page_heading_carries_the_same_direction(): void
    {
        $role = $this->hebrewSchedule();
        $event = $this->createEvent($role, ['name' => 'להקת LadyD']);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<h1[^>]*\sdir="rtl"/',
            $html,
            'The event page heading should declare an RTL base direction.'
        );
    }

    public function test_venue_and_description_resolve_their_own_direction(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'האוטובוס', 'language_code' => 'he']);
        $role = $this->hebrewSchedule();

        // short_description, NOT description: description_dir is built from
        // shortDescriptionInLanguage(), so seeding the wrong column made this assertion pass
        // against an empty string via the blank-language fallback and pin nothing.
        $this->createEvent($role, [
            'name' => 'להקת LadyD',
            'short_description' => 'במחווה לזמרות הרוק הגדולות',
        ])->roles()->attach($venue->id, ['is_accepted' => true]);

        $payload = $this->eventNamed($this->vueEvents($role), 'להקת LadyD');

        // Both used to inherit the event NAME's direction (or the schedule-wide one), which
        // is the wrong signal for a different string.
        $this->assertSame('rtl', $payload['venue_dir'], 'venue_dir: '.($payload['venue_name'] ?? ''));
        $this->assertSame('rtl', $payload['description_dir']);

        // And the pair that proves description_dir is read from the description rather than
        // inherited: a Hebrew title whose description happens to be English.
        $this->createEvent($role, [
            'name' => 'להקת Night Owls',
            'short_description' => 'An evening of live rock music',
            'starts_at' => now()->addDays(8)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $mixed = $this->eventNamed($this->vueEvents($role), 'להקת Night Owls');
        $this->assertSame('rtl', $mixed['dir']);
        $this->assertSame('ltr', $mixed['description_dir']);
    }

    public function test_a_hebrew_event_on_an_english_schedule_still_reads_rtl(): void
    {
        // The curator case content_dir_for_language() exists for: the viewing schedule is
        // English, so the text itself has to be allowed to override the language.
        $role = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'Tour Dates',
            'language_code' => 'en',
            'event_layout' => 'list',
        ]);
        $this->createEvent($role, ['name' => 'מסיבת ריקודים בתל אביב']);

        $events = $this->vueEvents($role);

        $this->assertSame('rtl', $this->eventNamed($events, 'מסיבת ריקודים בתל אביב')['dir']);
    }

    public function test_talent_names_carry_their_own_direction(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['name' => 'להקת LadyD', 'language_code' => 'he']);
        $role = $this->hebrewSchedule();

        $this->createEvent($role, ['name' => 'ערב הופעות'])
            ->roles()->attach($talent->id, ['is_accepted' => true]);

        $payload = $this->eventNamed($this->vueEvents($role), 'ערב הופעות');

        // This is the assertion that would have caught the miss: the direction was added to the
        // two builders that never run on a live page, and left out of the one that does.
        $this->assertNotEmpty($payload['talent'], 'the talent list should not be empty');
        $this->assertSame('rtl', $payload['talent'][0]['dir']);
    }

    public function test_the_dashboard_resolves_direction_from_the_events_own_schedule(): void
    {
        // /dashboard aggregates every schedule the user owns, so it passes no $role and the
        // display language lands on 'en'. Without a per-event fallback a Hebrew title has its
        // direction measured against English and still reads backwards.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'name' => 'לוח הופעות',
            'language_code' => 'he',
            'event_layout' => 'list',
        ]);
        // Every real creation path stamps creator_role_id (EventRepo, EventController, the
        // calendar sync services, BackupService); CreatesScheduleData does not, and that column
        // is the only per-event language signal the dashboard has.
        $this->createEvent($role, ['name' => 'להקת LadyD', 'creator_role_id' => $role->id]);

        $events = $this->actingAs($owner)
            ->getJson(route('home.calendar_events', [
                'year' => now()->addDays(7)->year,
                'month' => now()->addDays(7)->month,
            ]))->assertOk()->json('events') ?? [];

        $this->assertSame('rtl', $this->eventNamed($events, 'להקת LadyD')['dir']);
    }
}
