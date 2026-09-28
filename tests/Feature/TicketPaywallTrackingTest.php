<?php

namespace Tests\Feature;

use App\Services\GrowthExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * users.ticket_paywall_viewed_at - the "saw the paid-ticket paywall" stage.
 *
 * Since 2026-09-21 paid selling is Pro-only, so the event editor's ticket banner is where a free
 * organizer meets the plan. The editor's Vue app posts to subscription.paywall_seen the first time
 * that banner shows; the column feeds the hit_ticket_paywall funnel stage.
 */
class TicketPaywallTrackingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function beacon(string $subdomain)
    {
        return $this->postJson(route('subscription.paywall_seen', ['subdomain' => $subdomain]));
    }

    public function test_the_beacon_stamps_the_viewer_once(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);

        $this->actingAs($owner);
        $this->beacon($role->subdomain)->assertOk();

        $first = $owner->fresh()->ticket_paywall_viewed_at;
        $this->assertNotNull($first);

        $this->travel(2)->days();
        $this->beacon($role->subdomain)->assertOk();

        $this->assertTrue($first->equalTo($owner->fresh()->ticket_paywall_viewed_at),
            'first touch only: a later view must not move the stamp');
    }

    /** An editor who is not the owner meets the paywall too, and is counted as themselves. */
    public function test_an_editor_is_stamped_on_their_own_row(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $editor = $this->createOwner();
        $role->users()->attach($editor->id, ['level' => 'admin']);

        $this->actingAs($editor);
        $this->beacon($role->subdomain)->assertOk();

        $this->assertNotNull($editor->fresh()->ticket_paywall_viewed_at);
        $this->assertNull($owner->fresh()->ticket_paywall_viewed_at);
    }

    /** Someone with no business in the schedule cannot put themselves in the stage through it. */
    public function test_a_stranger_is_refused_and_not_stamped(): void
    {
        $role = $this->createFreeRole();
        $stranger = $this->createOwner();

        $this->actingAs($stranger);
        $this->beacon($role->subdomain)->assertForbidden();

        $this->assertNull($stranger->fresh()->ticket_paywall_viewed_at);
    }

    /** Selfhost has no plans to sell, so there is no paywall to have seen. */
    public function test_selfhost_does_not_stamp(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        config(['app.hosted' => false]);

        $this->actingAs($owner);
        $this->beacon($role->subdomain)->assertForbidden();

        $this->assertNull($owner->fresh()->ticket_paywall_viewed_at);
    }

    /** An earlier paywall view on the install, which is where the stage starts being tracked. */
    private function trackedSince(int $daysAgo): void
    {
        $early = $this->createOwner();
        DB::table('users')->where('id', $early->id)->update([
            'created_at' => now()->subDays($daysAgo + 30),
            'ticket_paywall_viewed_at' => now()->subDays($daysAgo),
        ]);
    }

    private function stages(): \Illuminate\Support\Collection
    {
        $funnel = app(GrowthExportService::class)->funnelData(
            now()->subDays(30), now(), now()->subDays(60), now()->subDays(31)
        );

        return collect($funnel['stages'])->keyBy('key');
    }

    public function test_the_funnel_counts_it_in_the_plan_group_without_a_step_ratio(): void
    {
        $this->trackedSince(40);

        $owner = $this->createOwner();
        $this->createFreeRole($owner);
        $this->createOwner();

        $owner->forceFill(['ticket_paywall_viewed_at' => now()])->save();

        $stages = $this->stages();

        $this->assertSame(1, $stages['hit_ticket_paywall']['count'], 'the early user was created before the window');
        $this->assertSame('plan', $stages['hit_ticket_paywall']['group']);
        $this->assertNull($stages['hit_ticket_paywall']['step_conv'],
            'it is not a subset of saved_paid_ticket, so no ratio off the ticket stages');
        $this->assertNull($stages['reached_checkout']['step_conv'],
            'checkout is reachable from anywhere, so no ratio off the paywall either');

        // Plan order: the paywall opens the plan group, ahead of checkout.
        $keys = $stages->keys()->all();
        $this->assertSame(array_search('hit_ticket_paywall', $keys) + 1, array_search('reached_checkout', $keys));
    }

    /**
     * Tracked from the first stamp on the install, not a date in the code: the column starts
     * filling on deploy, and a window reaching back before that would report zeros for days
     * nothing could be recorded.
     */
    public function test_the_stage_is_null_until_a_window_opens_after_the_first_view(): void
    {
        $this->assertNull($this->stages()['hit_ticket_paywall']['count'], 'nothing stamped yet');

        $this->trackedSince(5);
        $this->assertNull($this->stages()['hit_ticket_paywall']['count'], 'the window opens before the first view');

        $this->travel(30)->days();
        $this->assertSame(0, $this->stages()['hit_ticket_paywall']['count'], 'a real zero once fully tracked');
    }
}
