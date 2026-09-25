<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * One <h1> per guest page: the schedule's name on a schedule page, the event's on an event page.
 *
 * A crawl of production found 181 of 188 schedule pages with two - the banner header prints the
 * name twice, a mobile and a desktop copy, and only CSS hides one - and 39 event pages with more,
 * because an owner's markdown "# Heading" renders as an <h1> under the page's own. The desktop copy
 * is now a role="heading" aria-level="1" div (display:none keeps the pair to one level-1 heading
 * for a screen reader at any width), and every piece of owner HTML on a guest page goes through
 * MarkdownUtils::demoteH1(): the announcement, the event and part descriptions, the ticket form's
 * payment instructions, and the schedule, act and venue descriptions inside their Alpine show-more
 * toggles.
 */
class GuestHeadingStructureTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-01 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** Opening <h1> tags, never an <h10> or a data-es-h1 attribute. */
    private function h1Count(string $html): int
    {
        return preg_match_all('~<h1[\s>]~i', $html);
    }

    public function test_a_banner_header_schedule_page_has_one_h1(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Blue Note',
            'header_style' => 'banner',
            // The announcement bar is owner markdown on every schedule page.
            'banner_enabled' => true,
            'banner_message' => '# Big news',
            // And so is the description, which the banner prints twice: once in the mobile header
            // and once in the desktop one, each inside its own show-more toggle.
            'description' => "# About us\n\nWe play jazz.",
        ]);
        $this->createEvent($role, ['starts_at' => '2026-10-24 23:30:00', 'creator_role_id' => $role->id]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertSame(1, $this->h1Count($html));
        // The mobile copy is the <h1>; the desktop copy is still a level-1 heading to a screen
        // reader, and both keep the ids custom CSS hooks onto.
        $this->assertMatchesRegularExpression('~id="gp-header-body-mobile".*?<h1 [^>]*>\s*Blue Note\s*</h1>~s', $html);
        $this->assertMatchesRegularExpression('~id="gp-header-body-desktop".*?<div role="heading" aria-level="1"[^>]*>\s*Blue Note\s*</div>~s', $html);
        $this->assertStringContainsString('<h2 data-es-h1 id="big-news">Big news</h2>', $html, 'the announcement heading was demoted, not dropped');
        $this->assertSame(2, substr_count($html, '<h2 data-es-h1 id="about-us">About us</h2>'), 'both copies of the description were demoted');
    }

    public function test_a_compact_header_schedule_page_has_one_h1(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'Late Show',
            'header_style' => 'compact',
            // Rendered in the strip under the compact bar, outside any Alpine island.
            'description' => "# About us\n\nWe play jazz.",
        ]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertSame(1, $this->h1Count($html));
        $this->assertStringContainsString('<h2 data-es-h1 id="about-us">About us</h2>', $html);
    }

    public function test_an_event_page_whose_description_has_a_top_level_heading_has_one_h1(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Late Show']);
        $event = $this->createEvent($role, [
            'name' => 'Jazz Night',
            'starts_at' => '2026-10-24 23:30:00',
            'creator_role_id' => $role->id,
            'description' => "# Heading\n\nAn evening of standards.\n\n# Tickets\n\nAt the door.",
        ]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertSame(1, $this->h1Count($html));
        $this->assertMatchesRegularExpression('~<h1\s[^>]*id="gp-event-title"~', $html, 'the one <h1> is the event name');
        $this->assertStringContainsString('<h2 data-es-h1 id="heading">Heading</h2>', $html);
        $this->assertStringContainsString('<h2 data-es-h1 id="tickets">Tickets</h2>', $html);
    }

    /**
     * An act's card prints its own description, the schedule's `description` rendered to
     * description_html (never the short description, which is plain text the card does not show).
     * Over five words it sits in the show-more toggle's expanded copy; five or fewer, it is printed
     * as it is. Both are demoted.
     */
    public function test_an_acts_description_heading_is_demoted_whatever_its_length(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Note']);

        $cases = [
            'over five words' => "# Headliner\n\nPlays the late set every Friday night.",
            'five or fewer' => "# Headliner\n\nLate sets.",
        ];

        foreach ($cases as $case => $description) {
            $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'Late Show', 'description' => $description]);
            $event = $this->createEvent($venue, [
                'name' => 'Jazz Night',
                'starts_at' => '2026-10-24 23:30:00',
                'creator_role_id' => $venue->id,
            ]);
            $event->roles()->attach($act->id, ['is_accepted' => true]);

            $html = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();

            $this->assertSame(1, $this->h1Count($html), $case);
            $this->assertSame(1, substr_count($html, '<h2 data-es-h1 id="headliner">Headliner</h2>'), $case);
        }
    }

    /** The venue card prints its description twice, the clamped copy and the expanded one. */
    public function test_a_venues_description_heading_is_demoted_in_both_copies(): void
    {
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'Late Show']);
        $venue = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Blue Note',
            'description' => "# The room\n\nA basement club with a low ceiling and a long bar.",
        ]);
        $event = $this->createEvent($act, [
            'name' => 'Jazz Night',
            'starts_at' => '2026-10-24 23:30:00',
            'creator_role_id' => $act->id,
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $html = $this->get($this->guestEventUrl($act, $event))->assertOk()->getContent();

        $this->assertSame(1, $this->h1Count($html));
        $this->assertSame(2, substr_count($html, '<h2 data-es-h1 id="the-room">The room</h2>'));
    }

    /**
     * The ticket form is rendered into the event page, hidden until the buy button opens it, and
     * a cash event's form prints the owner's payment instructions.
     */
    public function test_the_payment_instructions_heading_is_demoted(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Note']);
        $event = $this->createEvent($role, [
            'name' => 'Jazz Night',
            'starts_at' => '2026-10-24 23:30:00',
            'creator_role_id' => $role->id,
            'tickets_enabled' => true,
            'payment_method' => 'cash',
            'payment_instructions' => "# Pay at the door\n\nCash only, exact change please.",
        ]);
        $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 50]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringContainsString('id="gp-event-form"', $html, 'fixture: the ticket form is on the page');
        $this->assertSame(1, $this->h1Count($html));
        $this->assertSame(1, substr_count($html, '<h2 data-es-h1 id="pay-at-the-door">Pay at the door</h2>'));
    }

    /** Both agenda layouts: the timed timeline and the untimed setlist render parts separately. */
    public function test_an_event_parts_heading_is_demoted_too(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Late Show']);

        foreach (['timed' => '20:00', 'untimed' => null] as $layout => $startTime) {
            $event = $this->createEvent($role, [
                'name' => 'Jazz Night',
                'starts_at' => '2026-10-24 23:30:00',
                'creator_role_id' => $role->id,
            ]);
            $event->parts()->create(['name' => 'Opening set', 'description' => '# Set list', 'start_time' => $startTime, 'sort_order' => 0]);

            $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

            $this->assertSame(1, $this->h1Count($html), $layout);
            $this->assertStringContainsString('<h2 data-es-h1 id="set-list">Set list</h2>', $html, $layout);
        }
    }
}
