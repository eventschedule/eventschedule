<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
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
 * The arrow beside a schedule's or an event's name on /admin/dashboard and /admin/realtime that
 * opens its guest page (App\Utils\PublicPageUrl, components/public-page-link).
 *
 * What is held: every place that names one carries the address, the address is a page that
 * answers, and a schedule or event whose page answers nobody (deleted, not claimed, a draft, not
 * yet accepted) carries none, so the arrow is never a way to a 404.
 */
class AdminPublicPageLinksTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private int $sequence = 0;

    private User $admin;

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

    private function asAdmin(): self
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
    }

    private function guestPage(Role $role): string
    {
        return route('role.view_guest', ['subdomain' => $role->subdomain]);
    }

    /** A link is only worth printing if it opens. Signed out, as a guest would open it. */
    private function assertOpens(?string $url): void
    {
        $this->assertNotNull($url);
        auth()->logout();
        $this->get($url)->assertOk();
    }

    // ---------------------------------------------------------------- /admin/dashboard

    /** Mutation: drop the is_draft or the $home test from PublicPageUrl::event(). */
    public function test_the_dashboards_lists_say_which_names_open_a_guest_page(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Jazz Hall']);
        // Twenty minutes apart: one schedule's events within ten minutes of each other are one row.
        $this->createEvent($venue, ['name' => 'Jazz Night', 'creator_role_id' => $venue->id, 'created_at' => now()->subMinutes(40)]);
        $this->createEvent($venue, ['name' => 'Draft Night', 'creator_role_id' => $venue->id, 'is_draft' => true, 'created_at' => now()->subMinutes(20)]);
        // Sent to a venue that has not answered yet: no page serves it.
        $this->createEvent($venue, ['name' => 'Waiting Night', 'creator_role_id' => $venue->id, 'is_accepted' => false]);

        $dashboard = new AdminDashboard;

        $this->assertSame($this->guestPage($venue), $dashboard->recentSchedules()['rows'][0]['url']);

        $events = collect($dashboard->recentEvents()['rows'])->keyBy('name');
        $this->assertTrue($events['Jazz Night']['public']);
        $this->assertFalse($events['Draft Night']['public']);
        $this->assertFalse($events['Waiting Night']['public']);
        $this->assertSame(route('event.edit_admin', ['hash' => UrlUtils::encodeId(Event::where('name', 'Draft Night')->value('id'))]), $events['Draft Night']['url'],
            'a draft opens its edit page, as it did');

        $this->assertOpens($events['Jazz Night']['url']);
    }

    /** Mutation: drop public_url from latestSignups(), or the claimed test from PublicPageUrl::schedule(). */
    public function test_a_latest_sign_up_carries_its_schedules_guest_page_beside_its_admin_page(): void
    {
        $claimed = $this->createOwner();
        $claimed->forceFill(['name' => 'Cara Claimed'])->save();
        $venue = $this->createRole($claimed, 'venue', ['name' => 'Cara Hall']);

        $unclaimed = $this->createOwner();
        $unclaimed->forceFill(['name' => 'Una Unclaimed'])->save();
        $this->createRole($unclaimed, 'venue', ['name' => 'Una Hall', 'email_verified_at' => null]);

        $latest = collect((new AdminDashboard)->signups()['latest'])->keyBy('name');

        $this->assertSame($this->guestPage($venue), $latest['Cara Claimed']['schedule']['public_url']);
        $this->assertStringContainsString('/admin/schedules/', $latest['Cara Claimed']['schedule']['url'], 'the name still opens the admin');
        $this->assertNull($latest['Una Unclaimed']['schedule']['public_url'], 'an unclaimed schedule has no guest page to open');

        $html = $this->asAdmin()->get('/admin/dashboard')->assertOk()->getContent();
        $this->assertMatchesRegularExpression(
            '~<a\s[^>]*href="'.preg_quote($this->guestPage($venue), '~').'"[^>]*target="_blank"[^>]*aria-label="'.preg_quote(e(__('messages.admin_open_public_page', ['label' => 'Cara Hall'])), '~').'"~',
            $html,
            'the arrow in the list, with the name in what it says',
        );

        $this->assertOpens($latest['Cara Claimed']['schedule']['public_url']);
    }

    // ---------------------------------------------------------------- /admin/realtime

    /**
     * Mutation: drop the event or the schedule arm of the public link in RealtimeActivity::render(),
     * or the fallback from an event without a page to its schedule.
     */
    public function test_each_activity_item_opens_what_it_names(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Jazz Hall']);
        $gone = $this->createRole($owner, 'venue', ['name' => 'Gone Hall', 'is_deleted' => true]);
        $event = $this->createEvent($venue, ['name' => 'Jazz Night', 'creator_role_id' => $venue->id]);
        $draft = $this->createEvent($venue, ['name' => 'Draft Night', 'creator_role_id' => $venue->id, 'is_draft' => true]);
        $sale = $this->createSale($event, $venue, ['payment_amount' => 20]);

        AuditService::log(AuditService::AUTH_REGISTER, $owner->id, 'User', $owner->id);
        AuditService::log(AuditService::SCHEDULE_CREATE, $owner->id, 'Role', $venue->id);
        AuditService::log(AuditService::SCHEDULE_CREATE, $owner->id, 'Role', $gone->id);
        AuditService::log(AuditService::SALE_PAID, null, 'Sale', $sale->id, ['status' => 'unpaid'], ['status' => 'paid']);
        // A browser's user agent: the test client's is the scheduler's, whose imports are left out.
        // Twenty minutes apart, so neither is folded into the other.
        foreach ([[$event, 30], [$draft, 50]] as [$made, $minutes]) {
            DB::table('audit_logs')->insert(['user_id' => $owner->id, 'action' => AuditService::EVENT_CREATE, 'model_type' => 'Event', 'model_id' => $made->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()->subMinutes($minutes)]);
        }

        $items = collect((new RealtimeDashboard)->payload()['activity']['items']);
        $schedulePage = $this->guestPage($venue);

        $this->assertSame($schedulePage, $items->firstWhere('type', 'signup')['public_url'], 'a sign-up opens their schedule');

        $this->assertEqualsCanonicalizing([$schedulePage, null], $items->where('type', 'schedule')->pluck('public_url')->all(),
            'a deleted schedule has no page to open');

        $added = $items->where('type', 'events')->values();
        $this->assertSame(['Jazz Night', 'Jazz Hall'], $added->pluck('public_label')->all(), 'the event, and for a draft its schedule');
        $this->assertSame($schedulePage, $added[1]['public_url']);

        $order = $items->firstWhere('type', 'order');
        $this->assertSame('Jazz Night', $order['public_label']);
        $this->assertSame($added[0]['public_url'], $order['public_url'], 'an order opens the event it was for');

        $this->assertOpens($order['public_url']);
    }

    /** Mutation: drop public_url from RealtimeActivity::signups() or from expanded()'s schedules. */
    public function test_the_sign_ups_card_and_an_expanded_visitor_carry_the_schedules_guest_page(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Jazz Hall']);
        AuditService::log(AuditService::AUTH_REGISTER, $owner->id, 'User', $owner->id);
        $this->hit(['visitor_key' => 'abababababababab', 'user_id' => $owner->id, 'surface' => 'ap', 'path' => '/home']);

        $row = (new RealtimeDashboard)->payload()['activity']['signups']['rows'][0];
        $this->assertSame($this->guestPage($venue), $row['schedule']['public_url']);

        $expanded = (new RealtimeDashboard(expand: 'u:'.UrlUtils::encodeId($owner->id)))->payload()['expanded'];
        $this->assertSame($this->guestPage($venue), $expanded['schedules'][0]['public_url']);
    }

    /** Mutation: drop the PublicPageUrl::schedule() or the is_draft test from RealtimeDashboard::guestUrl(). */
    public function test_a_top_page_opens_only_while_its_page_still_answers(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Jazz Hall']);
        $gone = $this->createRole($owner, 'venue', ['name' => 'Gone Hall', 'is_deleted' => true]);
        $event = $this->createEvent($venue, ['name' => 'Jazz Night', 'creator_role_id' => $venue->id]);
        $draft = $this->createEvent($venue, ['name' => 'Draft Night', 'creator_role_id' => $venue->id, 'is_draft' => true]);

        $this->hit(['visitor_key' => '1111111111111111', 'surface' => 'gp', 'role_id' => $venue->id]);
        $this->hit(['visitor_key' => '2222222222222222', 'surface' => 'gp', 'role_id' => $venue->id, 'event_id' => $event->id]);
        $this->hit(['visitor_key' => '3333333333333333', 'surface' => 'gp', 'role_id' => $venue->id, 'event_id' => $draft->id]);
        $this->hit(['visitor_key' => '4444444444444444', 'surface' => 'gp', 'role_id' => $gone->id]);
        $this->hit(['visitor_key' => '5555555555555555', 'surface' => 'ap', 'path' => '/home', 'title' => 'Home']);

        $pages = collect((new RealtimeDashboard)->payload()['breakdowns']['pages'])->keyBy('label');

        $this->assertSame($this->guestPage($venue), $pages['Jazz Hall']['url']);
        $this->assertNotNull($pages['Jazz Night']['url']);
        $this->assertNull($pages['Draft Night']['url']);
        $this->assertNull($pages['Gone Hall']['url']);
        $this->assertNull($pages['Home']['url'], 'the admin panel is not a guest page');

        $this->assertOpens($pages['Jazz Night']['url']);
    }

    public function test_the_realtime_page_draws_the_arrow_from_the_payload(): void
    {
        $html = $this->asAdmin()->get('/admin/realtime')->assertOk()->getContent();

        // Four places: the activity feed, the sign-ups card, an expanded visitor, a top page.
        foreach (['item.public_url', 'row.schedule.public_url', 'schedule.public_url', 'row.url'] as $field) {
            $this->assertMatchesRegularExpression('~<a\s[^>]*target="_blank"[^>]*v-if="'.preg_quote($field).'"\s+v-bind:href="'.preg_quote($field).'"~', $html);
        }
        // The label is filled in by the page, from a string it is handed raw.
        $this->assertStringContainsString(json_encode(__('messages.admin_open_public_page')), $html);
    }

    private function hit(array $attributes): void
    {
        $now = now();

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
