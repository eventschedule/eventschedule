<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's public page before its events arrive (role/partials/calendar,
 * role/partials/guest-list-wait), as a frame-by-frame look at the nexus found it in 2026-11:
 *
 * - before the script started, every block that only means something once the events are in
 *   was drawn at once, so a laptop showed the phone's month, "No events" and "Next month";
 * - the list's placeholder was a generic card with no date column and the picture on the other
 *   side, so the list jumped when it filled;
 * - the chips arrived with the events and pushed the list down;
 * - the schedule's display font was asked for late and the name was drawn twice.
 *
 * Held here as what the server prints, which is what a visitor sees first.
 */
class GuestPageLoadingStateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function page($role, string $query = ''): string
    {
        return $this->get('/'.$role->subdomain.$query)->assertOk()->getContent();
    }

    /** The opening tag of the first element carrying $marker. */
    private function tagWith(string $html, string $marker): string
    {
        $at = strpos($html, $marker);
        $this->assertNotFalse($at, $marker.' is on the page');
        $open = strrpos(substr($html, 0, $at), '<');

        return substr($html, $open, strpos($html, '>', $at) - $open + 1);
    }

    public function test_a_list_page_opens_on_the_lists_own_shape_and_nothing_else(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'list']);
        $html = $this->page($role);

        // The placeholder in the list's own classes, shown before the script starts.
        $this->assertStringContainsString('class="gk-days gk-wait animate-pulse"', $html);
        $this->assertStringContainsString('gk-row-media gk-wait-media', $html);
        $this->assertStringNotContainsString('v-cloak', $this->tagWith($html, 'gk-days gk-wait'), 'its wrapper is not cloaked');

        // What only means something once the events are in waits for the script.
        $this->assertStringContainsString('v-cloak', $this->tagWith($html, 'data-phone-month'));
        $this->assertMatchesRegularExpression("/v-show=\"currentView === 'calendar'\" v-cloak class=/", $html, 'the month is not the view that opens');
        $this->assertMatchesRegularExpression("/v-if=\"currentView === 'list' && isLoadingEvents\"\s+class=\"gk-list\"/", $html, 'the list placeholder is what shows');
    }

    public function test_a_calendar_page_opens_on_the_months_shape_with_its_weeks(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'calendar']);
        $html = $this->page($role);

        $this->assertMatchesRegularExpression("/v-show=\"currentView === 'calendar'\"\s+class=/", $html, 'the month is what shows');
        // Before the script, a v-for is one row of one day: the server draws the month's weeks.
        $weeks = substr_count($html, '<div v-if="false" class="gk-cal-week">');
        $this->assertGreaterThanOrEqual(4, $weeks);
        $this->assertLessThanOrEqual(6, $weeks);
        $this->assertMatchesRegularExpression("/v-if=\"currentView === 'list' && isLoadingEvents\" v-cloak class=\"gk-list\"/", $html, 'the list placeholder waits: the list is not what opens');
    }

    public function test_the_chips_room_is_kept_only_where_there_will_be_chips(): void
    {
        $plain = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'list']);
        $this->createEvent($plain, ['name' => 'One']);
        // The class as an element carries it: the stylesheet on the page names gk-pills-wait too.
        $this->assertStringNotContainsString('class="gk-pills gk-pills-wait', $this->page($plain));

        $mixed = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'list']);
        [$a, $b] = array_slice(array_keys(config('app.event_categories')), 0, 2);
        $this->createEvent($mixed, ['name' => 'Gig', 'category_id' => $a]);
        $this->createEvent($mixed, ['name' => 'Talk', 'category_id' => $b]);

        $html = $this->page($mixed);
        $this->assertStringContainsString('class="gk-pills gk-pills-wait', $html);
        $this->assertStringContainsString('isLoadingEvents && quickChips.length <= 1', $html, 'it steps aside once the real row can be drawn');
    }

    public function test_the_schedules_display_font_is_asked_for_with_its_stylesheet(): void
    {
        $this->assertStringEndsWith('/vendor/fonts/Alfa_Slab_One/6NUQ8FmMKwSEKjnm5-4v-4Jh2dJhe_escmA.woff2', (string) font_preload_url('Alfa Slab One'));
        $this->assertNull(font_preload_url('No Such Font'));
        $this->assertNull(font_preload_url('../etc'));

        $role = $this->createRole($this->createOwner(), 'venue', ['font_family' => 'Alfa Slab One']);
        $this->assertStringContainsString('<link rel="preload" as="font" type="font/woff2" href="'.font_preload_url('Alfa Slab One').'" crossorigin>', $this->page($role));
    }

    public function test_the_events_are_asked_for_from_the_head_with_the_url_the_script_builds(): void
    {
        $list = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'list']);
        $html = $this->page($list);
        $url = route('role.calendar_events', ['subdomain' => $list->subdomain]).'?list=1&lang='.$list->displayLanguageCode();

        $this->assertStringContainsString('<link rel="preload" as="fetch" href="'.e($url).'" crossorigin>', $html);
        // The script's own request, built the same way: a mismatch would fetch twice.
        $this->assertStringContainsString("url += separator + 'list=1';", $html);

        $calendar = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'calendar']);
        $this->assertMatchesRegularExpression('~<link rel="preload" as="fetch" href="[^"]*\?month=\d{1,2}&amp;year=\d{4}&amp;lang=[a-zA-Z-]+" crossorigin>~', $this->page($calendar));
    }

    public function test_the_list_draws_its_first_screens_and_grows_from_there(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'list']);
        $html = $this->page($role);

        $this->assertStringContainsString('listRowLimit: 30,', $html);
        $this->assertStringContainsString('listAutoUntil: 200,', $html);
        $this->assertStringContainsString('pastRowLimit: 20,', $html);
        $this->assertStringContainsString('this.flatPastEvents.slice(0, this.pastRowLimit)', $html);
        $this->assertStringContainsString('data-list-sentinel aria-hidden="true"', $html);
        $this->assertStringContainsString('data-list-sentinel="past" aria-hidden="true"', $html);
        // Never past what it drew at once before: "Show more" takes over from there.
        $this->assertStringContainsString('this.listRowLimit = Math.min(this.listAutoUntil, this.listRowLimit + 40);', $html);
    }
}
