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
 * for a screen reader at any width), and owner HTML outside the Alpine description toggles goes
 * through MarkdownUtils::demoteH1().
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
        ]);
        $this->createEvent($role, ['starts_at' => '2026-10-24 23:30:00', 'creator_role_id' => $role->id]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertSame(1, $this->h1Count($html));
        // The mobile copy is the <h1>; the desktop copy is still a level-1 heading to a screen
        // reader, and both keep the ids custom CSS hooks onto.
        $this->assertMatchesRegularExpression('~id="gp-header-body-mobile".*?<h1 [^>]*>\s*Blue Note\s*</h1>~s', $html);
        $this->assertMatchesRegularExpression('~id="gp-header-body-desktop".*?<div role="heading" aria-level="1"[^>]*>\s*Blue Note\s*</div>~s', $html);
        $this->assertStringContainsString('<h2 data-es-h1', $html, 'the announcement heading was demoted, not dropped');
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
