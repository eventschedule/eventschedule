<?php

namespace Tests\Feature;

use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\AccountBlockService;
use App\Services\AdminDashboard;
use App\Services\AuditService;
use App\Services\RealtimeDashboard;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Once an account is blocked, /admin/dashboard and /admin/realtime leave out everything that is
 * its (App\Services\BlockedAccounts): the account, the schedules it owns, and the events it made
 * or those schedules own.
 *
 * The block takes the schedules down, which the pages already honoured; what they did not was
 * the rest. The sign-up stayed in the counts and the newest organizers, an event the account had
 * also put on somebody else's venue stayed in the newest events (that venue is still up), and the
 * realtime page went on listing the account as a visitor, its day's activity in the feed and the
 * views of its pages among the top pages.
 */
class BlockedAccountsHiddenTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private int $sequence = 0;

    private User $admin;

    private User $spammer;

    private User $member;

    private Role $spamSchedule;

    private Role $honestVenue;

    private Event $spamEvent;

    private Event $teamEvent;

    private Event $submittedEvent;

    private Event $honestEvent;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Cache::flush();
        $this->travelTo(now()->setTime(12, 0));
        config(['app.hosted' => false, 'app.is_nexus' => false]);
        Setting::set('realtime_enabled', '1');

        $this->admin = $this->createOwner(true);
    }

    /**
     * A spammer with a schedule of their own and, each also at an honest venue, three events that
     * are theirs by a different rule: one they made on their schedule, one a team member made on
     * it, and one they sent to the venue, which saves it under the venue. Beside them an honest
     * owner with an event at that venue.
     */
    private function seedSpammer(): void
    {
        $honest = $this->signUp(['name' => 'Hana Honest']);
        $this->honestVenue = $this->createRole($honest, 'venue', ['name' => 'Honest Hall']);
        // No creator schedule, like an event older than the column: a NOT IN on it must let it through.
        $this->honestEvent = $this->createEvent($this->honestVenue, ['name' => 'Jazz Night']);

        $this->spammer = $this->signUp(['name' => 'Sam Spammer']);
        $this->spamSchedule = $this->createRole($this->spammer, 'venue', ['name' => 'Cheap Pills Arena']);
        AuditService::log(AuditService::SCHEDULE_CREATE, $this->spammer->id, 'Role', $this->spamSchedule->id);

        $this->spamEvent = $this->createEvent($this->spamSchedule, ['name' => 'Pill Party', 'creator_role_id' => $this->spamSchedule->id]);
        $this->spamEvent->roles()->attach($this->honestVenue->id, ['is_accepted' => true]);
        // Written as a browser writes it: the test client's user agent is the scheduler's.
        DB::table('audit_logs')->insert(['user_id' => $this->spammer->id, 'action' => AuditService::EVENT_CREATE, 'model_type' => 'Event', 'model_id' => $this->spamEvent->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()]);

        // Somebody else's doings on the spammer's schedule: a team member's event, a trial, a plan
        // bought and cancelled.
        $this->member = $this->createOwner();
        $this->teamEvent = $this->createEvent($this->spamSchedule, ['name' => 'Team Pills', 'user_id' => $this->member->id, 'creator_role_id' => $this->spamSchedule->id]);
        $this->teamEvent->roles()->attach($this->honestVenue->id, ['is_accepted' => true]);
        DB::table('audit_logs')->insert(['user_id' => $this->member->id, 'action' => AuditService::EVENT_CREATE, 'model_type' => 'Event', 'model_id' => $this->teamEvent->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()]);
        AuditService::log(AuditService::TICKET_TRIAL_START, $this->member->id, 'Role', $this->spamSchedule->id);
        AuditService::log(AuditService::SUBSCRIPTION_CREATE, $this->member->id, 'Role', $this->spamSchedule->id, null, ['plan_type' => 'pro']);
        AuditService::log(AuditService::SUBSCRIPTION_CANCEL, $this->member->id, 'Role', $this->spamSchedule->id);

        // A signed-in submission is saved under the schedule it was sent to: only its maker says whose it is.
        $this->submittedEvent = $this->createEvent($this->honestVenue, ['name' => 'Pill Submission', 'user_id' => $this->spammer->id, 'creator_role_id' => $this->honestVenue->id]);
    }

    private function block(): void
    {
        app(AccountBlockService::class)->block($this->spammer, $this->admin);
    }

    // ---------------------------------------------------------------- /admin/dashboard

    /** Mutation: drop the blocked line from signups(), or from the previous window. */
    public function test_a_blocked_sign_up_leaves_the_dashboards_counts_and_its_newest_organizers(): void
    {
        $this->seedSpammer();

        // One honest and one blocked account in the window thirty days earlier.
        $earlier = now()->subDays(31);
        $this->createOwner()->forceFill(['created_at' => $earlier])->save();
        $earlySpammer = $this->createOwner();
        $earlySpammer->forceFill(['created_at' => $earlier])->save();

        $this->block();
        app(AccountBlockService::class)->block($earlySpammer, $this->admin);

        $signups = (new AdminDashboard)->signups();

        $this->assertNotContains('Sam Spammer', array_column($signups['latest'], 'name'));
        $this->assertContains('Hana Honest', array_column($signups['latest'], 'name'));
        // The admin, Hana and the team member; not Sam.
        $this->assertSame(3, $signups['organizers']['last_30d']);
        $this->assertSame(1, $signups['organizers']['previous_30d'], 'the window it is compared with leaves its blocked accounts out too');
        $this->assertSame(4, (new AdminDashboard)->system()['accounts'], 'those three and the earlier honest account');
    }

    /**
     * The schedule a block could not take down (Stripe would not cancel) is still up, and the
     * events are still on a live venue: the block's own takedown hides none of these.
     * Mutation: drop withoutTheirEvents() from recentEvents() or events(), either half of
     * BlockedAccounts::theirEvents(), or the owner line from recentSchedules().
     */
    public function test_a_blocked_accounts_schedules_and_events_leave_the_dashboards_lists(): void
    {
        $this->seedSpammer();
        $this->block();
        DB::table('roles')->where('id', $this->spamSchedule->id)->update(['is_deleted' => false]);

        $dashboard = new AdminDashboard;

        $schedules = $dashboard->recentSchedules();
        $this->assertSame(['Honest Hall'], array_column($schedules['rows'], 'name'));
        $this->assertSame(1, $schedules['total']);

        $this->assertSame(['Jazz Night'], array_column($dashboard->recentEvents()['rows'], 'name'),
            'not the events the account made, here or on the venue, nor the one its schedule owns');

        $events = $dashboard->events();
        $this->assertSame(1, $events['total']);
        $this->assertSame(1, $events['all_time']);
        $this->assertSame(1, $events['new_30d']);

        // Let back in, it is all on the page again: the block is what hides it.
        app(AccountBlockService::class)->unblock($this->spammer->fresh(), $this->admin);

        // The spammer's schedule made two of them within minutes, so those are one row.
        $rows = (new AdminDashboard)->recentEvents()['rows'];
        $this->assertSame(['Pill Submission', 'Team Pills', 'Jazz Night'], array_column($rows, 'name'));
        $this->assertSame(1, $rows[1]['more']);
        $this->assertSame(4, (new AdminDashboard)->events()['all_time']);
    }

    // ---------------------------------------------------------------- /admin/realtime

    /** Mutation: drop the blocked ids from $excludedUsers, or any arm of withoutBlockedThings(). */
    public function test_a_blocked_accounts_day_leaves_the_activity_feed_and_the_sign_ups_card(): void
    {
        $this->seedSpammer();

        // A guest's order for the spammer's event, a gift card on their schedule, and an order
        // for the honest one.
        $spamSale = $this->createSale($this->spamEvent, $this->spamSchedule, ['payment_amount' => 25]);
        AuditService::log(AuditService::SALE_PAID, null, 'Sale', $spamSale->id, ['status' => 'unpaid'], ['status' => 'paid']);
        $honestSale = $this->createSale($this->honestEvent, $this->honestVenue, ['payment_amount' => 40]);
        AuditService::log(AuditService::SALE_PAID, null, 'Sale', $honestSale->id, ['status' => 'unpaid'], ['status' => 'paid']);
        $card = DB::table('gift_cards')->insertGetId([
            'role_id' => $this->spamSchedule->id, 'code' => 'SPAMCARD0001', 'secret' => str_repeat('a', 32),
            'amount' => 50, 'remaining_amount' => 50, 'currency_code' => 'USD', 'status' => 'active',
            'purchaser_name' => 'Pat', 'purchaser_email' => 'pat@example.com',
            'recipient_name' => 'Ray', 'recipient_email' => 'ray@example.com',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        AuditService::log(AuditService::GIFT_CARD_PAID, null, 'GiftCard', $card, ['status' => 'unpaid'], ['status' => 'active']);

        // More of the team member's doings there: the plan resumed and switched, the schedule
        // claimed and taken down, an event boosted.
        AuditService::log(AuditService::SUBSCRIPTION_RESUME, $this->member->id, 'Role', $this->spamSchedule->id);
        AuditService::log(AuditService::SUBSCRIPTION_SWAP, $this->member->id, 'Role', $this->spamSchedule->id, null, ['plan_type' => 'enterprise', 'plan_term' => 'monthly']);
        AuditService::log(AuditService::SCHEDULE_CLAIM, $this->member->id, 'Role', $this->spamSchedule->id);
        AuditService::log(AuditService::SCHEDULE_TAKEDOWN, $this->member->id, Role::class, $this->spamSchedule->id, ['is_deleted' => false], ['is_deleted' => true]);
        $boost = BoostCampaign::create([
            'event_id' => $this->spamEvent->id, 'role_id' => $this->spamSchedule->id, 'user_id' => $this->member->id,
            'channel' => 'meta', 'name' => 'Boost', 'status' => 'active', 'billing_status' => 'charged',
            'user_budget' => 20, 'total_charged' => 24,
        ]);
        AuditService::log(AuditService::BOOST_CREATE, $this->member->id, 'BoostCampaign', $boost->id);

        $before = (new RealtimeDashboard)->payload()['activity'];
        $this->assertSame(['signup' => 2, 'schedule' => 1, 'events' => 2, 'order' => 2, 'upgrade' => 1, 'deleted' => 1], collect($before['stats'])->pluck('count', 'type')->all());
        foreach (['trial', 'upgrade', 'cancel', 'gift_card', 'resume', 'plan_change', 'claim', 'schedule_deleted', 'boost'] as $type) {
            $this->assertContains($type, array_column($before['items'], 'type'));
        }

        $this->block();

        $activity = (new RealtimeDashboard)->payload()['activity'];

        $this->assertSame(
            ['signup' => 1, 'schedule' => 0, 'events' => 0, 'order' => 1],
            collect($activity['stats'])->pluck('count', 'type')->all(),
        );
        $this->assertEqualsCanonicalizing(['signup', 'order'], array_column($activity['items'], 'type'),
            'the honest sign-up and order; not the team member\'s event, trial, plan, claim, takedown or boost, nor the gift card, on the spammer\'s schedule');
        $this->assertStringContainsString('Jazz Night', collect($activity['items'])->firstWhere('type', 'order')['text']);
        $this->assertSame(['Hana Honest'], array_column($activity['signups']['rows'], 'name'));
        $this->assertSame(1, $activity['signups']['total']);
    }

    /** Mutation: drop the blocked line from the audit read in RealtimeActivity::build(). */
    public function test_a_blocked_accounts_rows_never_spend_the_activity_feeds_read(): void
    {
        $this->seedSpammer();

        // A morning's import through the web, newer than everybody else's day, and more rows than
        // the feed reads.
        $import = array_fill(0, 2000, ['user_id' => $this->spammer->id, 'action' => AuditService::EVENT_CREATE, 'model_type' => 'Event', 'model_id' => $this->spamEvent->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()]);
        foreach (array_chunk($import, 500) as $rows) {
            DB::table('audit_logs')->insert($rows);
        }

        $this->block();

        $this->assertSame(1, (new RealtimeDashboard)->payload()['activity']['signups']['total'], 'Hana\'s sign-up is still read');
    }

    /** Mutation: drop the blocked ids from $excludedUsers in RealtimeActivity::build(). */
    public function test_a_day_whose_only_sign_up_was_blocked_dates_the_last_honest_one(): void
    {
        $honest = $this->createOwner();
        DB::table('audit_logs')->insert(['user_id' => $honest->id, 'action' => AuditService::AUTH_REGISTER, 'model_type' => 'User', 'model_id' => $honest->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()->subDays(3)]);
        $this->spammer = $this->signUp(['name' => 'Sam Spammer']);

        $this->block();

        $signups = (new RealtimeDashboard)->payload()['activity']['signups'];
        $this->assertSame(0, $signups['total']);
        $this->assertSame(3 * 86400, $signups['last_ago'], 'three days, not the spammer\'s minute');
    }

    /**
     * Mutation: drop any of the three tests in BlockedAccounts::withoutTheirHits(), its call in
     * fetchRows(), expanded() or recentViews(), or the blocked keys in people() or expanded().
     */
    public function test_a_blocked_account_and_the_views_of_its_pages_leave_the_visitors_and_the_figures(): void
    {
        $this->seedSpammer();
        $key = 'abababababababab';

        // The spammer on the sign-up page and then signed in; then a neighbour on the same network
        // and browser, signed in. With the spammer's row gone the key has one user left, who must
        // not be handed the spammer's sign-up page.
        $this->hit(['visitor_key' => $key, 'surface' => 'auth', 'path' => '/sign_up', 'started_at' => now()->subMinutes(12), 'last_seen_at' => now()->subMinutes(11), 'ended_at' => now()->subMinutes(11)]);
        $this->hit(['visitor_key' => $key, 'user_id' => $this->spammer->id, 'surface' => 'ap', 'path' => '/home', 'started_at' => now()->subMinutes(10), 'last_seen_at' => now()->subMinutes(9), 'ended_at' => now()->subMinutes(9)]);
        $neighbour = $this->createOwner();
        $this->hit(['visitor_key' => $key, 'user_id' => $neighbour->id, 'surface' => 'ap', 'path' => '/home', 'title' => 'Neighbour home', 'started_at' => now()->subMinutes(7), 'last_seen_at' => now()->subMinutes(6), 'ended_at' => now()->subMinutes(6)]);
        // A visitor signed in on another key: the honest event, then a page while signed out, then the
        // spammer's event. With that last view gone, their latest signed-in view is the first, so
        // the page between belongs to nobody, in the list and in their timeline alike.
        $visitorKey = 'cdcdcdcdcdcdcdcd';
        $visitor = $this->createOwner();
        $this->hit(['visitor_key' => $visitorKey, 'user_id' => $visitor->id, 'surface' => 'gp', 'role_id' => $this->honestVenue->id, 'event_id' => $this->honestEvent->id, 'started_at' => now()->subMinutes(10), 'last_seen_at' => now()->subMinutes(9), 'ended_at' => now()->subMinutes(9)]);
        $this->hit(['visitor_key' => $visitorKey, 'path' => '/pricing', 'title' => 'Pricing', 'started_at' => now()->subMinutes(8), 'last_seen_at' => now()->subMinutes(7), 'ended_at' => now()->subMinutes(7)]);
        $this->hit(['visitor_key' => $visitorKey, 'user_id' => $visitor->id, 'surface' => 'gp', 'role_id' => $this->honestVenue->id, 'event_id' => $this->spamEvent->id, 'started_at' => now()->subMinutes(4), 'last_seen_at' => now()->subMinutes(3), 'ended_at' => now()->subMinutes(3)]);
        // Visitors on the spammer's schedule page, on their event at the honest venue, and on the honest event.
        $this->hit(['visitor_key' => '1111111111111111', 'surface' => 'gp', 'role_id' => $this->spamSchedule->id]);
        $this->hit(['visitor_key' => '2222222222222222', 'surface' => 'gp', 'role_id' => $this->honestVenue->id, 'event_id' => $this->spamEvent->id]);
        $this->hit(['visitor_key' => '3333333333333333', 'surface' => 'gp', 'role_id' => $this->honestVenue->id, 'event_id' => $this->teamEvent->id]);
        $this->hit(['visitor_key' => '4444444444444444', 'surface' => 'gp', 'role_id' => $this->honestVenue->id, 'event_id' => $this->honestEvent->id]);

        $spammerId = 'u:'.UrlUtils::encodeId($this->spammer->id);
        $this->assertNotNull(collect((new RealtimeDashboard)->payload()['visitors'])->only(['now', 'earlier'])->flatten(1)->firstWhere('id', $spammerId));
        // A key the spammer signed in on forty minutes ago, outside the list's half hour but inside
        // the timeline's hour: the page after it is nobody's, though one user is left on the key.
        $oldKey = 'efefefefefefefef';
        $this->hit(['visitor_key' => $oldKey, 'user_id' => $this->spammer->id, 'surface' => 'ap', 'path' => '/home', 'started_at' => now()->subMinutes(41), 'last_seen_at' => now()->subMinutes(40), 'ended_at' => now()->subMinutes(40)]);
        $this->hit(['visitor_key' => $oldKey, 'path' => '/features', 'title' => 'Features', 'started_at' => now()->subMinutes(20), 'last_seen_at' => now()->subMinutes(19), 'ended_at' => now()->subMinutes(19)]);
        $later = $this->createOwner();
        $this->hit(['visitor_key' => $oldKey, 'user_id' => $later->id, 'surface' => 'ap', 'path' => '/home', 'title' => 'Later home', 'started_at' => now()->subMinutes(12), 'last_seen_at' => now()->subMinutes(11), 'ended_at' => now()->subMinutes(11)]);

        // A venue made from the spammer's event location carries their user id with no owner
        // place; the block leaves it up, and so does this page.
        $placed = $this->createRole($this->spammer, 'venue', ['name' => 'Placed Venue']);
        DB::table('role_user')->where('role_id', $placed->id)->update(['level' => 'follower']);
        $this->hit(['visitor_key' => '6666666666666666', 'surface' => 'gp', 'role_id' => $placed->id]);

        $this->assertSame(6, RealtimeDashboard::recentViews(), 'the six schedule pages of the last five minutes');

        $this->block();

        $payload = (new RealtimeDashboard)->payload();
        $people = collect($payload['visitors'])->only(['now', 'earlier'])->flatten(1);

        $neighbourId = 'u:'.UrlUtils::encodeId($neighbour->id);
        $visitorId = 'u:'.UrlUtils::encodeId($visitor->id);
        $laterId = 'u:'.UrlUtils::encodeId($later->id);

        $this->assertNull($people->firstWhere('id', $spammerId));
        $this->assertEqualsCanonicalizing(['v:'.$key, $neighbourId, 'v:4444444444444444', $visitorId, 'v:'.$visitorKey, $laterId, 'v:'.$oldKey, 'v:6666666666666666'], $people->pluck('id')->all(),
            'the browsing while not signed in stays, as anonymous; the views of the account\'s pages go');
        $this->assertSame(1, $people->firstWhere('id', 'v:'.$key)['views']);
        $this->assertSame(1, $people->firstWhere('id', $neighbourId)['views'], 'the neighbour is not given the spammer\'s page');
        $this->assertSame(1, $people->firstWhere('id', $visitorId)['views']);
        $this->assertSame(1, $people->firstWhere('id', $laterId)['views'], 'the list asks over the hour, as the timeline does');
        $this->assertSame(8, $payload['overview']['win_views']);
        $this->assertEqualsCanonicalizing(
            ['e:'.UrlUtils::encodeId($this->honestVenue->id).':'.UrlUtils::encodeId($this->honestEvent->id), 'p:auth:/sign_up', 'p:ap:/home', 'p:wp:/pricing', 'p:wp:/features', 'e:'.UrlUtils::encodeId($placed->id)],
            collect($payload['breakdowns']['pages'])->pluck('key')->all(),
        );

        // Page entries only: the neighbour's account is new, so a "Signed up" marker joins it.
        $pages = fn (string $expand) => array_column(array_filter(
            (new RealtimeDashboard(expand: $expand))->payload()['expanded']['timeline'] ?? [],
            fn ($entry) => $entry['marker'] === null
        ), 'label');

        $this->assertNull((new RealtimeDashboard(expand: $spammerId))->payload()['expanded']);
        $this->assertSame([__('messages.sign_up')], $pages('v:'.$key), 'the anonymous timeline is the anonymous person the list shows');
        $this->assertSame(['Neighbour home'], $pages($neighbourId));
        $this->assertSame(['Jazz Night'], $pages($visitorId), 'the signed-out page is not theirs here either');
        $this->assertSame(['Pricing'], $pages('v:'.$visitorKey));

        $this->assertSame(['Later home'], $pages($laterId));
        $this->assertSame(2, RealtimeDashboard::recentViews(), 'the dashboard\'s line leaves out the views of the account\'s pages too');
    }

    /**
     * An account created through the sign-up form, with its audit row.
     */
    private function signUp(array $attributes): User
    {
        $user = $this->createOwner();
        $user->forceFill($attributes)->save();

        AuditService::log(AuditService::AUTH_REGISTER, $user->id, 'User', $user->id);

        return $user;
    }

    private function hit(array $attributes): void
    {
        $now = now();
        $attributes = array_map(fn ($value) => $value instanceof \DateTimeInterface ? RealtimeTracker::ts($value) : $value, $attributes);

        DB::table('realtime_hits')->insert(array_merge([
            'hit_key' => str_pad(dechex(++$this->sequence), 32, '0', STR_PAD_LEFT),
            'consented' => true,
            'surface' => 'wp',
            'path' => '/',
            'device' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'macOS',
            'country' => 'US',
            'hb' => 60,
            'started_at' => RealtimeTracker::ts($now),
            'last_seen_at' => RealtimeTracker::ts($now),
            'engaged_at' => RealtimeTracker::ts($now),
        ], $attributes));
    }
}
