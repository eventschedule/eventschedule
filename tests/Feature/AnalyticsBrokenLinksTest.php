<?php

namespace Tests\Feature;

use App\Models\AnalyticsDaily;
use App\Models\AnalyticsMissingDaily;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A dead event address used to 302 to the schedule home. The visit counted for the schedule and for
 * NO event, the visitor landed on a working page, and the owner had no way to tell a rotted link
 * from analytics that had stopped working - which is exactly how this was reported ("this event is
 * missing from statistics", for an address that turned out to name nothing at all).
 *
 * The address now 404s and the miss is counted, so the owner can see the broken link instead of
 * inferring it from an event that mysteriously has no views.
 */
class AnalyticsBrokenLinksTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A real browser's headers; recordView() and the miss counter both drop anything else. */
    private function browserHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept' => 'text/html,application/xhtml+xml',
        ];
    }

    public function test_a_dead_address_is_counted_and_shown_to_the_owner(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator', ['name' => 'Emek Live']);

        foreach (range(1, 3) as $ignored) {
            $this->withHeaders($this->browserHeaders())
                ->get('/'.$role->subdomain.'/ba-be-09-9')
                ->assertNotFound();
        }

        $this->assertDatabaseHas('analytics_missing_daily', [
            'role_id' => $role->id,
            'slug' => 'ba-be-09-9',
            'views' => 3,
        ]);

        // A miss is not a page view: it must not inflate the schedule's own total, which is what
        // the old redirect did on its way to rendering the calendar.
        $this->assertSame(0, AnalyticsDaily::where('role_id', $role->id)->count());

        $this->actingAs($owner)
            ->get(route('analytics', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()
            ->assertSee(__('messages.broken_links'))
            ->assertSee('/ba-be-09-9');
    }

    public function test_scanner_shaped_paths_are_not_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // The slug is whatever the visitor typed, so the panel is only useful if probe traffic
        // cannot crowd out the one real broken link it exists to surface.
        foreach (['wp-login.php', '.env', 'Admin', 'a_b'] as $probe) {
            $this->withHeaders($this->browserHeaders())
                ->get('/'.$role->subdomain.'/'.$probe)
                ->assertNotFound();
        }

        $this->assertSame(0, AnalyticsMissingDaily::where('role_id', $role->id)->count());
    }

    public function test_a_bot_is_not_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept' => 'text/html',
        ])->get('/'.$role->subdomain.'/ba-be-09-9')->assertNotFound();

        $this->assertSame(0, AnalyticsMissingDaily::where('role_id', $role->id)->count());
    }

    public function test_one_visitor_cannot_flood_the_panel_with_distinct_slugs(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // Creating rows is budgeted per visitor per day; incrementing an existing one is not, so a
        // genuinely broken link still outranks one-shot noise.
        foreach (range(1, 12) as $i) {
            $this->withHeaders($this->browserHeaders())
                ->get('/'.$role->subdomain.'/probe-'.$i)
                ->assertNotFound();
        }

        $this->assertSame(5, AnalyticsMissingDaily::where('role_id', $role->id)->count());
    }
}
