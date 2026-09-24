<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\GuestSeo;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest portal's <title> and meta description (App\Utils\GuestSeo).
 *
 * A crawl of production found event titles of only "{event} | {schedule}" with no date or place,
 * schedule titles of only the name, a third of the event descriptions under 50 characters, a
 * quarter carrying raw newlines, and 112 of 188 schedule descriptions reading "View the event
 * schedule for X". These pin what replaced them: the date and place while they fit in 60
 * characters, the owner's thin text joined to their long text, dates on the SCHEDULE's clock, no
 * date on a series page, and one description for the description, og: and twitter: tags.
 */
class GuestTitleDescriptionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Every event date below is absolute, so "now" is frozen before them - otherwise these
        // pass until October 2026 and then start testing past events.
        Carbon::setTestNow(Carbon::parse('2026-09-01 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function title(string $html): string
    {
        $this->assertSame(1, preg_match('~<title>(.*?)</title>~s', $html, $m), 'the page has no <title>');

        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** The meta description, after asserting og: and twitter: carry the very same text. */
    private function description(string $html): string
    {
        $this->assertSame(1, preg_match('~<meta name="description" content="([^"]*)">~', $html, $m), 'no meta description');
        $this->assertStringContainsString('<meta property="og:description" content="'.$m[1].'">', $html);
        $this->assertStringContainsString('<meta name="twitter:description" content="'.$m[1].'">', $html);

        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function talent(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'talent', array_merge(['name' => 'Late Show'], $attrs));
    }

    private function venue(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', array_merge([
            'name' => 'Blue Note',
            'address1' => '131 W 3rd St',
            'city' => 'New York',
        ], $attrs));
    }

    /** $name on $host, created by it, with $venue accepted beside it when given. */
    private function event(Role $host, string $name, array $attrs = [], ?Role $venue = null): Event
    {
        $event = $this->createEvent($host, array_merge([
            'name' => $name,
            // 2026-10-24 19:30 in New York (EDT, UTC-4).
            'starts_at' => '2026-10-24 23:30:00',
            'creator_role_id' => $host->id,
        ], $attrs));

        if ($venue) {
            $event->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        return $event->fresh();
    }

    public function test_a_one_off_event_title_carries_its_date_and_venue(): void
    {
        $talent = $this->talent();
        $event = $this->event($talent, 'Jazz Night', [], $this->venue());

        $html = $this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent();

        $this->assertSame('Jazz Night - Oct 24, 2026, Blue Note | Late Show', $this->title($html));
    }

    public function test_the_title_drops_detail_until_it_fits_and_keeps_the_old_title_as_its_floor(): void
    {
        $talent = $this->talent();
        $venue = $this->venue(['name' => 'The Blue Note Jazz Club and Supper Room']);

        // The venue does not fit, the city does not either, the date alone does.
        $event = $this->event($talent, 'Autumn Jazz Festival Opening', [], $venue);
        $title = $this->title($this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent());

        $this->assertSame('Autumn Jazz Festival Opening - Oct 24, 2026 | Late Show', $title);
        $this->assertLessThanOrEqual(60, mb_strlen($title));

        // Nothing fits: exactly the title every event page used to have.
        $long = 'The Annual Midwinter Celebration of Improvised Music and Poetry';
        $event = $this->event($talent, $long, [], $venue);
        $title = $this->title($this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent());

        $this->assertSame($long.' | Late Show', $title);
    }

    public function test_the_venue_is_left_out_on_its_own_page_where_it_is_already_the_suffix(): void
    {
        $venue = $this->venue();
        $event = $this->event($venue, 'Jazz Night');

        $html = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();

        $this->assertSame('Jazz Night - Oct 24, 2026, New York | Blue Note', $this->title($html));
    }

    public function test_an_online_event_says_online_and_never_its_join_link(): void
    {
        $talent = $this->talent();
        $event = $this->event($talent, 'Webinar', ['event_url' => 'https://zoom.us/j/123456?pwd=secret']);

        $html = $this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent();

        $this->assertSame('Webinar - Oct 24, 2026, Online | Late Show', $this->title($html));
        $this->assertStringContainsString('Online', $this->description($html));
        $this->assertStringNotContainsString('zoom.us', $this->title($html).$this->description($html));
    }

    public function test_the_date_is_the_schedules_day_for_a_viewer_east_of_it(): void
    {
        // 22:30 on Saturday Oct 24 in Los Angeles is 05:30 UTC and 14:30 in Tokyo on the 25th.
        $talent = $this->talent(['timezone' => 'America/Los_Angeles']);
        $event = $this->event($talent, 'Late Night Set', ['starts_at' => '2026-10-25 05:30:00']);

        $viewer = User::factory()->create(['timezone' => 'Asia/Tokyo', 'email_verified_at' => now()]);

        $html = $this->actingAs($viewer)->get($this->guestEventUrl($talent, $event))->assertOk()->getContent();

        $this->assertSame('Late Night Set - Oct 24, 2026 | Late Show', $this->title($html));
        // Nothing was written, so the name, then when: the day, and the time on the schedule's
        // 12-hour clock.
        $this->assertSame('Late Night Set · Sat, Oct 24, 2026, 10:30 PM', $this->description($html));
    }

    public function test_the_time_follows_the_schedules_24_hour_preference(): void
    {
        $talent = $this->talent(['use_24_hour_time' => true]);
        $event = $this->event($talent, 'Jazz Night');

        $html = $this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent();

        $this->assertSame('Jazz Night · Sat, Oct 24, 2026, 19:30', $this->description($html));
    }

    public function test_a_multi_day_event_is_described_by_its_date_range(): void
    {
        $talent = $this->talent();
        $event = $this->event($talent, 'Harvest Festival', ['duration' => 48]);

        $html = $this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent();

        $this->assertSame('Harvest Festival - Oct 24, 2026 | Late Show', $this->title($html));
        $this->assertSame('Harvest Festival · Oct 24, 2026 - Oct 26, 2026', $this->description($html));
    }

    /**
     * A date-only starts_at is already the schedule's calendar day. Read as midnight UTC and
     * converted, it would land on the day before for every schedule west of Greenwich.
     */
    public function test_a_date_only_event_keeps_its_day_west_of_greenwich(): void
    {
        $schedule = new Role(['name' => 'Late Show', 'timezone' => 'America/Los_Angeles', 'language_code' => 'en']);
        $schedule->type = 'talent';

        $event = new Event(['name' => 'All Day Fair']);
        $event->starts_at = '2026-10-24';
        $event->setRelation('creatorRole', $schedule);
        $event->setRelation('roles', collect());

        $this->assertSame('All Day Fair - Oct 24, 2026 | Late Show', GuestSeo::eventTitle($event, $schedule));
        $this->assertSame('All Day Fair · Sat, Oct 24, 2026', GuestSeo::eventDescription($event, null, 'en', $schedule));
    }

    public function test_a_thin_short_description_is_joined_to_the_long_one(): void
    {
        $talent = $this->talent();
        $event = $this->event($talent, 'Comedy Hour', [
            'short_description' => 'Lincoln Lodge',
            'description' => "Chicago's longest running comedy showcase\nContinue the fun at the bar after.",
        ], $this->venue());

        $description = $this->description($this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent());

        // The <br> MarkdownUtils renders for the newline is a sentence boundary, not glue.
        $this->assertSame(
            "Lincoln Lodge. Chicago's longest running comedy showcase. Continue the fun at the bar after. · Sat, Oct 24, 2026, 7:30 PM · Blue Note, New York",
            $description
        );
    }

    public function test_the_long_text_alone_when_it_already_opens_with_the_short_one(): void
    {
        $talent = $this->talent();
        $event = $this->event($talent, 'Comedy Hour', [
            'short_description' => 'Lincoln Lodge',
            'description' => 'Lincoln Lodge is where Chicago comedy happens, every night of the week.',
        ]);

        $description = $this->description($this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent());

        $this->assertStringStartsWith('Lincoln Lodge is where Chicago comedy happens', $description);
        $this->assertSame(1, substr_count($description, 'Lincoln Lodge'));
    }

    public function test_a_long_description_is_cut_to_155_characters_with_no_facts_squeezed_in(): void
    {
        $talent = $this->talent();
        $body = str_repeat('An evening of standards and new work from the quartet. ', 6);
        $event = $this->event($talent, 'Quartet', ['description' => $body], $this->venue());

        $description = $this->description($this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent());

        $this->assertLessThanOrEqual(155, mb_strlen($description));
        $this->assertStringStartsWith('An evening of standards', $description);
        $this->assertStringNotContainsString('Oct 24', $description, 'a long text has no room for the facts');
    }

    public function test_the_description_never_carries_a_raw_newline_or_a_double_escape(): void
    {
        $talent = $this->talent();
        $event = $this->event($talent, 'Tom & Jerry', [
            'short_description' => "Cats &amp; mice\nlive",
            'description' => "Rock & Roll <3\n\nSecond paragraph &amp; more",
        ]);

        $html = $this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent();

        preg_match('~<meta name="description" content="([^"]*)">~', $html, $raw);
        $this->assertStringNotContainsString("\n", $raw[1]);
        // The head, not the page: the body prints the stored short description as it always has.
        $head = strstr($html, '</head>', true);
        $this->assertNotFalse($head);
        $this->assertStringNotContainsString('&amp;amp;', $head);
        $this->assertStringContainsString('<title>Tom &amp; Jerry - Oct 24, 2026 | Late Show</title>', $head, 'escaped exactly once');
        $this->assertSame('Cats & mice live. Rock & Roll <3. Second paragraph & more · Sat, Oct 24, 2026, 7:30 PM', $this->description($html));
    }

    public function test_a_recurring_series_title_never_carries_a_date(): void
    {
        $talent = $this->talent();
        $venue = $this->venue();
        // Sundays from 2026-10-25, 7:30 PM in New York.
        $event = $this->createRecurringEvent($talent, [
            'name' => 'Sunday Jam',
            'days_of_week' => '1000000',
            'starts_at' => '2026-10-25 23:30:00',
            'creator_role_id' => $talent->id,
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $series = $this->get($this->guestEventUrl($talent, $event))->assertOk()->getContent();
        $occurrence = $this->get($this->guestEventUrl($talent, $event, '2026-11-08'))->assertOk()->getContent();

        $this->assertSame('Sunday Jam, Blue Note | Late Show', $this->title($series));
        $this->assertSame('Sunday Jam, Blue Note | Late Show', $this->title($occurrence), 'every occurrence shares the series title');

        // The series page is about every occurrence, so its description names none; a dated page
        // is about one, and says which.
        $this->assertSame('Sunday Jam · Blue Note, New York', $this->description($series));
        $this->assertSame('Sunday Jam · Sun, Nov 8, 2026, 7:30 PM · Blue Note, New York', $this->description($occurrence));
    }

    public function test_the_title_is_the_same_on_a_custom_domain_and_never_names_us(): void
    {
        $plain = $this->talent();
        $direct = $this->talent([
            'custom_domain' => 'https://late-show.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $venue = $this->venue();

        $titles = [];
        foreach ([$plain, $direct] as $host) {
            $event = $this->event($host, 'Jazz Night', [], $venue);
            $titles[] = $this->title($this->get($this->guestEventUrl($host, $event))->assertOk()->getContent());
            $titles[] = $this->title($this->get('/'.$host->subdomain)->assertOk()->getContent());
        }

        $this->assertSame($titles[0], $titles[2], 'the event title does not depend on the domain');
        $this->assertSame($titles[1], $titles[3], 'the schedule title does not depend on the domain');

        foreach ($titles as $title) {
            $this->assertStringNotContainsString('Event Schedule', $title);
            $this->assertStringNotContainsString('late-show.test', $title);
        }
    }

    public function test_the_translated_page_is_titled_and_described_in_its_own_language(): void
    {
        $talent = $this->talent([
            'name' => 'להקת הערב',
            'name_en' => 'Evening Band',
            'language_code' => 'he',
            'translation_language_code' => 'en',
        ]);
        $event = $this->event($talent, 'ערב ג׳אז', [
            'name_en' => 'Jazz Evening',
            'short_description' => 'מוזיקה חיה',
            'short_description_en' => 'Live music',
        ]);
        $url = $this->guestEventUrl($talent, $event);

        $hebrew = $this->get($url)->assertOk()->getContent();
        $this->assertSame('ערב ג׳אז - 24 באוק׳ 2026 | להקת הערב', $this->title($hebrew));
        $this->assertStringStartsWith('מוזיקה חיה · ש׳, 24 באוק׳ 2026', $this->description($hebrew));

        $english = $this->get($url.'?lang=en')->assertOk()->getContent();
        $this->assertSame('Jazz Evening - Oct 24, 2026 | Evening Band', $this->title($english));
        $this->assertStringStartsWith('Live music · Sat, Oct 24, 2026', $this->description($english));
    }

    public function test_the_schedule_title_says_upcoming_events_only_while_there_are_some(): void
    {
        $talent = $this->talent();

        $this->assertSame('Late Show - Events', $this->title($this->get('/'.$talent->subdomain)->assertOk()->getContent()));

        $this->event($talent, 'Jazz Night');

        $this->assertSame('Late Show - Upcoming Events', $this->title($this->get('/'.$talent->subdomain)->assertOk()->getContent()));
    }

    public function test_a_past_draft_or_unlisted_event_does_not_count_as_upcoming(): void
    {
        $talent = $this->talent();
        $this->event($talent, 'Last Year', ['starts_at' => '2025-10-24 23:30:00']);
        $this->event($talent, 'Draft', ['is_draft' => true]);
        $this->event($talent, 'Unlisted', ['is_private' => true]);
        $this->event($talent, 'Cancelled', ['is_cancelled' => true]);

        $html = $this->get('/'.$talent->subdomain)->assertOk()->getContent();

        $this->assertSame('Late Show - Events', $this->title($html));
        $this->assertStringNotContainsString('Upcoming:', $this->description($html));
    }

    public function test_a_venue_title_names_its_city_unless_its_name_does(): void
    {
        $venue = $this->venue();
        $this->event($venue, 'Jazz Night');

        $this->assertSame('Blue Note, New York - Upcoming Events', $this->title($this->get('/'.$venue->subdomain)->assertOk()->getContent()));

        $named = $this->venue(['name' => 'New York Jazz Club']);
        $this->assertSame('New York Jazz Club - Events', $this->title($this->get('/'.$named->subdomain)->assertOk()->getContent()));
    }

    public function test_the_schedule_description_adds_the_address_and_the_next_events(): void
    {
        $venue = $this->venue(['short_description' => 'Jazz club in the Village']);
        $this->event($venue, 'Jazz Night');
        $this->event($venue, 'Blues Brunch', ['starts_at' => '2026-10-25 15:00:00']);
        $this->event($venue, 'Salsa Social', ['starts_at' => '2026-10-26 23:00:00']);
        $this->event($venue, 'Organ Trio', ['starts_at' => '2026-10-27 23:00:00']);

        $description = $this->description($this->get('/'.$venue->subdomain)->assertOk()->getContent());

        // Soonest first, three of them.
        $this->assertSame(
            'Jazz club in the Village · 131 W 3rd St, New York · Upcoming: Jazz Night, Blues Brunch, Salsa Social',
            $description
        );
    }

    public function test_the_old_wording_is_only_for_a_schedule_with_nothing_to_say(): void
    {
        $talent = $this->talent();

        $this->assertSame(
            'View the event schedule for Late Show',
            $this->description($this->get('/'.$talent->subdomain)->assertOk()->getContent())
        );

        $this->event($talent, 'Jazz Night');

        $this->assertSame(
            'Upcoming: Jazz Night',
            $this->description($this->get('/'.$talent->subdomain)->assertOk()->getContent())
        );
    }

    public function test_a_sub_schedule_keeps_its_title_and_lists_its_own_events(): void
    {
        $talent = $this->talent();
        $group = $this->createGroup($talent, ['name' => 'Workshops', 'slug' => 'workshops']);
        $this->event($talent, 'Main Stage Show');
        // The sub-schedule lives on this schedule's pivot row.
        $this->event($talent, 'Drum Workshop')->roles()->updateExistingPivot($talent->id, ['group_id' => $group->id]);

        $html = $this->get('/'.$talent->subdomain.'/workshops')->assertOk()->getContent();

        $this->assertSame('Workshops | Late Show', $this->title($html));
        $this->assertSame('Upcoming: Drum Workshop', $this->description($html));
    }
}
