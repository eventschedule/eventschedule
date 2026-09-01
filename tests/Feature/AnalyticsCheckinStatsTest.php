<?php

namespace Tests\Feature;

use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * AnalyticsService::getCheckinStats() assigned $roleIds only on the no-event branch, but read it
 * again at the timezone lookup, which runs on both. An ?event_id= URL carrying no role_id
 * therefore fataled on ->isNotEmpty().
 *
 * The trap when pinning it: the empty-sale-tickets case returns BEFORE that lookup, so a fixture
 * whose event has no paid sale tickets inside the date window passes against the bug. The window
 * is the default last_30_days, which spans past dates only - a future event (createEvent's
 * default) is outside it.
 */
class AnalyticsCheckinStatsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_checkins_tab_survives_an_event_id_with_no_role_id(): void
    {
        $owner = $this->createOwner();

        // Two schedules on purpose: AnalyticsController auto-selects role_id only for a user who
        // has exactly one, and it is the null role_id that leaves $roleIds unset.
        $role = $this->createRole($owner);
        $this->createRole($owner);

        $event = $this->createEvent($role, [
            'starts_at' => Carbon::now()->subDays(3)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'creator_role_id' => $role->id,
        ]);
        $ticket = $this->createTicket($event, ['price' => 25]);
        $this->createSale($event, $role, ['payment_amount' => 25, 'status' => 'paid'], $ticket);

        $this->actingAs($owner)->get(route('analytics', [
            'event_id' => UrlUtils::encodeId($event->id),
            'tab' => 'checkins',
        ]))->assertOk();
    }
}
