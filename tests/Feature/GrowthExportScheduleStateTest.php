<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Services\GrowthExportService;
use App\Utils\ReleaseHistory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Schema 21: where each schedule stands now, so a growth review answers from the pull what it
 * used to ask of the server. Is a quiet seller between events or stuck, which schedule a trial or
 * a cancellation belongs to, and is a sync pulling somebody's primary calendar.
 *
 * Hosted, as GrowthExportTest explains: off hosted every schedule reads as enterprise.
 */
class GrowthExportScheduleStateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function build(): array
    {
        return app(GrowthExportService::class)->build(now()->subDays(30), now(), now()->subDays(60), now()->subDays(31));
    }

    private function row(array $data, Role $role): array
    {
        $i = array_flip($data['schedules']['columns']);
        $sid = 's:'.substr(hash_hmac('sha256', (string) $role->id, (string) config('app.key')), 0, 12);
        $row = collect($data['schedules']['rows'])->firstWhere($i['sid'], $sid);
        $this->assertNotNull($row, "no schedule row for role {$role->id}");

        return array_combine($data['schedules']['columns'], $row);
    }

    private function proRole(): Role
    {
        return $this->createRole($this->createOwner(), 'curator', [
            'plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d'), 'plan_source' => 'admin',
        ]);
    }

    private function freeRole(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'free', 'plan_expires' => now()->subYear()->format('Y-m-d'), 'trial_ends_at' => null,
        ]);
    }

    private function onSale(Role $role, array $attrs = [], float $price = 20): Event
    {
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true, ...$attrs]);
        $this->createTicket($event, ['price' => $price]);

        return $event;
    }

    /** A weekly class is one event on sale, not one per week; past, far-off, draft and cancelled are none. */
    public function test_upcoming_ticketed_events_counts_a_series_once_and_only_what_is_on_sale(): void
    {
        $role = $this->proRole();
        $this->onSale($role, ['days_of_week' => '0100000', 'recurring_frequency' => 'weekly',
            'starts_at' => now()->subWeeks(3)->setTime(12, 0)->format('Y-m-d H:i:s')]);
        $this->onSale($role, ['starts_at' => now()->addDays(10)->setTime(12, 0)->format('Y-m-d H:i:s')]);
        $this->onSale($role, ['starts_at' => now()->addDays(20)->setTime(12, 0)->format('Y-m-d H:i:s')], 0);
        $this->onSale($role, ['starts_at' => now()->addDays(GrowthExportService::UPCOMING_DAYS + 5)->format('Y-m-d H:i:s')]);
        $this->onSale($role, ['starts_at' => now()->subDays(3)->format('Y-m-d H:i:s')]);
        $this->onSale($role, ['starts_at' => now()->addDays(5)->format('Y-m-d H:i:s'), 'is_draft' => true]);
        $this->onSale($role, ['starts_at' => now()->addDays(5)->format('Y-m-d H:i:s'), 'is_cancelled' => true]);
        $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => false,
            'starts_at' => now()->addDays(4)->format('Y-m-d H:i:s')]);

        $row = $this->row($this->build(), $role);

        $this->assertSame(3, $row['upcoming_ticketed_events'], 'the series, the one-off and the free one');
        $this->assertSame(2, $row['upcoming_paid_events'], 'the free one has no priced ticket type');
        $this->assertSame(0, $row['upcoming_paid_blocked'], 'a Pro schedule can sell them');
    }

    /** A free schedule's paid event made after the amnesty cannot sell; a grandfathered one can. */
    public function test_upcoming_paid_blocked_is_paid_events_the_plan_cannot_sell(): void
    {
        $role = $this->freeRole();
        $this->onSale($role);
        $this->onSale($role, ['tickets_grandfathered_at' => now()->subMonth()]);

        $row = $this->row($this->build(), $role);

        $this->assertSame(2, $row['upcoming_paid_events']);
        $this->assertSame(1, $row['upcoming_paid_blocked']);

        // The selling trial opens them.
        DB::table('roles')->where('id', $role->id)->update(['ticket_trial_ends_at' => now()->addDays(2)]);
        $row = $this->row($this->build(), $role);

        $this->assertSame(0, $row['upcoming_paid_blocked']);
        $this->assertSame('0-3', $row['ticket_trial_ends_in']);
    }

    public function test_orders_on_upcoming_dates_and_unpaid_orders_are_counted_apart(): void
    {
        $role = $this->proRole();
        $event = $this->onSale($role);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 20]);
        $this->createSale($event, $role, ['status' => 'unpaid', 'payment_method' => 'cash']);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'import']);
        $this->createSale($event, $role, ['status' => 'expired', 'payment_method' => 'payment_url']);
        $old = $this->createSale($event, $role, ['status' => 'expired']);
        DB::table('sales')->where('id', $old->id)->update(['created_at' => now()->subDays(40)]);

        $row = $this->row($this->build(), $role);

        $this->assertSame(2, $row['upcoming_orders'], 'the paid and the unpaid order; an import is nobody\'s order');
        $this->assertSame(['unpaid' => 1, 'expired' => 1], $row['unpaid_orders_30d'], 'the 40-day-old one is outside the window');
        $this->assertSame('0-7', $row['last_event_created']);
    }

    /** A feed or a sync adds events with nobody there, so it says nothing about the organizer. */
    public function test_last_event_created_leaves_out_feeds_and_syncs(): void
    {
        $role = $this->proRole();
        $byHand = $this->createEvent($role, ['creator_role_id' => $role->id]);
        DB::table('events')->where('id', $byHand->id)->update(['created_at' => now()->subDays(40)]);
        $this->createEvent($role, ['creator_role_id' => $role->id, 'import_source' => Event::IMPORT_FEED]);
        $this->createEvent($role, ['creator_role_id' => $role->id, 'import_source' => Event::IMPORT_GOOGLE]);

        $this->assertSame('31-90', $this->row($this->build(), $role)['last_event_created']);

        $this->createEvent($role, ['creator_role_id' => $role->id, 'import_source' => Event::IMPORT_AI]);
        $this->assertSame('0-7', $this->row($this->build(), $role)['last_event_created'], 'an AI import is a person at work');
    }

    public function test_a_trial_and_a_cancellation_say_when_they_end_and_why_without_the_comment(): void
    {
        config(['services.stripe_platform.price_monthly' => 'price_test_monthly']);

        $trialing = $this->freeRole();
        $trialing->subscriptions()->create(['type' => 'default', 'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'trialing', 'stripe_price' => 'price_test_monthly', 'quantity' => 1,
            'trial_ends_at' => now()->addDays(5)]);

        $leaving = $this->freeRole();
        $leaving->subscriptions()->create(['type' => 'default', 'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'active', 'stripe_price' => 'price_test_monthly', 'quantity' => 1,
            'ends_at' => now()->addDays(10)]);
        DB::table('subscription_cancellations')->insert(['role_id' => $leaving->id, 'source' => 'app',
            'reason' => 'season_over', 'comment' => 'Quillon the promoter is closing for winter', 'created_at' => now()]);

        $data = $this->build();
        $trial = $this->row($data, $trialing);
        $cancel = $this->row($data, $leaving);

        $this->assertSame('trialing', $trial['sub_status']);
        $this->assertSame('4-7', $trial['trial_ends_in']);
        $this->assertFalse($trial['cancel_pending']);
        $this->assertNull($trial['cancel_reason']);

        $this->assertSame('active', $cancel['sub_status']);
        $this->assertTrue($cancel['cancel_pending']);
        $this->assertTrue($cancel['billing'], 'still paying until the period ends');
        $this->assertSame('8-14', $cancel['period_ends_in']);
        $this->assertSame('season_over', $cancel['cancel_reason']);
        $this->assertStringNotContainsString('Quillon', json_encode($data));
    }

    /** A cancelled trial is pending too, but pays nothing: it is not in billing and will not convert. */
    public function test_a_cancelled_trial_is_pending_and_not_billing(): void
    {
        config(['services.stripe_platform.price_monthly' => 'price_test_monthly']);

        $role = $this->freeRole();
        $role->subscriptions()->create(['type' => 'default', 'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'trialing', 'stripe_price' => 'price_test_monthly', 'quantity' => 1,
            'trial_ends_at' => now()->addDays(5), 'ends_at' => now()->addDays(5)]);
        DB::table('subscription_cancellations')->insert(['role_id' => $role->id, 'source' => 'app',
            'reason' => 'not_using', 'created_at' => now()]);

        $row = $this->row($this->build(), $role);

        $this->assertSame('trialing', $row['sub_status']);
        $this->assertTrue($row['cancel_pending']);
        $this->assertFalse($row['billing']);
        $this->assertSame('not_using', $row['cancel_reason']);
    }

    /** A cancellation row from before the current subscription belongs to an earlier one. */
    public function test_an_older_cancellation_is_not_the_reason_for_a_newer_subscription(): void
    {
        $role = $this->freeRole();
        DB::table('subscription_cancellations')->insert(['role_id' => $role->id, 'source' => 'app',
            'reason' => 'too_expensive', 'created_at' => now()->subMonths(2)]);
        $role->subscriptions()->create(['type' => 'default', 'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'active', 'stripe_price' => 'price_x', 'quantity' => 1, 'ends_at' => now()->addDays(20)]);

        $row = $this->row($this->build(), $role);

        $this->assertTrue($row['cancel_pending']);
        $this->assertNull($row['cancel_reason']);
    }

    /** An admin grant ends on a date nobody is reminded of; a Stripe subscription reports nothing here. */
    public function test_plan_ends_in_is_for_a_paid_tier_no_subscription_pays_for(): void
    {
        $granted = $this->createRole($this->createOwner(), 'venue', ['plan_type' => 'enterprise',
            'plan_expires' => now()->addDays(5)->format('Y-m-d'), 'plan_source' => 'admin']);
        $free = $this->freeRole();

        $data = $this->build();

        $this->assertSame('4-7', $this->row($data, $granted)['plan_ends_in']);

        // A generic trial that ends first does not end a grant that runs on.
        $both = $this->createRole($this->createOwner(), 'venue', ['plan_type' => 'pro', 'plan_source' => 'admin',
            'plan_expires' => now()->addDays(20)->format('Y-m-d'), 'trial_ends_at' => now()->addDays(2)]);
        $this->assertSame('15-30', $this->row($this->build(), $both)['plan_ends_in']);
        $this->assertNull($this->row($data, $granted)['sub_status']);
        $this->assertNull($this->row($data, $free)['plan_ends_in']);
        $this->assertNull($this->row($data, $free)['ticket_trial_ends_in']);
    }

    public function test_a_sync_with_no_calendar_chosen_reads_primary(): void
    {
        $primary = $this->freeRole();
        $chosen = $this->freeRole();
        $none = $this->freeRole();
        DB::table('roles')->whereIn('id', [$primary->id, $chosen->id])->update(['sync_direction' => 'from']);
        DB::table('role_user')->where('role_id', $chosen->id)->where('user_id', $chosen->user_id)
            ->update(['google_calendar_id' => 'c_123@group.calendar.google.com']);

        $data = $this->build();

        $this->assertSame(['from', 'primary'], [$this->row($data, $primary)['gsync_direction'], $this->row($data, $primary)['gsync_calendar']]);
        $this->assertSame('chosen', $this->row($data, $chosen)['gsync_calendar']);
        $this->assertNull($this->row($data, $none)['gsync_calendar']);
        $this->assertStringNotContainsString('c_123', json_encode($data));
    }

    /** The busiest day and what made it, so a sync that dumps a calendar on a schedule shows. */
    public function test_the_busiest_day_carries_its_count_and_source(): void
    {
        $role = $this->freeRole();
        $insert = fn (int $n, ?string $source, Carbon $at) => DB::table('events')->insert(array_map(fn ($k) => [
            'user_id' => $role->user_id, 'creator_role_id' => $role->id, 'name' => 'Synced', 'slug' => 'synced-'.Str::random(10),
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'), 'import_source' => $source,
            'created_at' => $at, 'updated_at' => $at,
        ], range(1, $n)));
        $insert(12, Event::IMPORT_GOOGLE, now()->subDays(3)->setTime(9, 0));
        $insert(3, null, now()->subDays(3)->setTime(10, 0));
        $insert(5, null, now()->subDays(1));
        $insert(40, null, now()->subDays(120));

        $row = $this->row($this->build(), $role);

        $this->assertSame(15, $row['max_events_one_day_90d']);
        $this->assertSame('google', $row['max_day_source']);
    }

    public function test_the_payload_names_the_commit_it_was_built_from_and_nothing_else(): void
    {
        config(['app.commit' => null]);
        $this->assertNull($this->build()['meta']['app_commit']);

        config(['app.commit' => 'not a hash; rm -rf /']);
        $this->assertNull($this->build()['meta']['app_commit']);

        config(['app.commit' => 'ABCDEF0123456789abcdef0123456789abcdef01']);
        $this->assertSame('abcdef012345', $this->build()['meta']['app_commit']);
    }

    /** A new commit under the same version is a deploy; a container that cannot say adds nothing. */
    public function test_release_history_records_each_commit_once_and_ignores_a_container_without_one(): void
    {
        config(['self-update.version_installed' => 'v1.0.160', 'app.commit' => 'aaaaaaaaaaaa']);
        ReleaseHistory::touch();
        ReleaseHistory::touch();

        config(['app.commit' => 'bbbbbbbbbbbb']);
        ReleaseHistory::touch();

        // The worker without APP_COMMIT, ticking between the web container's ticks.
        config(['app.commit' => null]);
        ReleaseHistory::touch();
        config(['app.commit' => 'bbbbbbbbbbbb']);
        ReleaseHistory::touch();
        config(['app.commit' => null]);
        ReleaseHistory::touch();

        // A rolling deploy: the old container and the new one tick side by side.
        config(['app.commit' => 'aaaaaaaaaaaa']);
        ReleaseHistory::touch();
        config(['app.commit' => 'bbbbbbbbbbbb']);
        ReleaseHistory::touch();
        config(['app.commit' => 'aaaaaaaaaaaa']);
        ReleaseHistory::touch();

        // A lost cache must not duplicate what is recorded.
        \Illuminate\Support\Facades\Cache::forget('release_history.current');
        ReleaseHistory::touch();

        $releases = ReleaseHistory::all();
        $this->assertSame(['aaaaaaaaaaaa', 'bbbbbbbbbbbb'], array_column($releases, 'commit'));
        $this->assertSame(['v1.0.160', 'v1.0.160'], array_column($releases, 'version'));
    }
}
