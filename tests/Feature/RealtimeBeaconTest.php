<?php

namespace Tests\Feature;

use App\Models\RealtimeHit;
use App\Models\Setting;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * POST /api/realtime, the /admin/realtime beacon: what each message stores, and what the server
 * refuses to store whatever the client sends.
 */
class RealtimeBeaconTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Mobile/15E148 Safari/604.1';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Setting::set('realtime_enabled', '1');
    }

    public function test_a_consented_page_view_stores_an_identified_row(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'r' => 'www.google.com', 'ti' => 'Pricing | Event Schedule'])
            ->assertNoContent();

        $hit = RealtimeHit::sole();
        $this->assertTrue($hit->consented);
        $this->assertSame('wp', $hit->surface);
        $this->assertSame('/pricing', $hit->path);
        $this->assertSame('Pricing', $hit->title);
        $this->assertSame('search', $hit->source_channel);
        $this->assertSame('google.com', $hit->source_name);
        $this->assertSame('mobile', $hit->device);
        $this->assertSame('Safari', $hit->browser);
        $this->assertSame('iOS', $hit->os);
        $this->assertTrue($hit->is_entrance);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $hit->visitor_key);
        $this->assertNull($hit->engaged_at, 'a full-mode page view engages through its first heartbeat');
    }

    public function test_a_count_only_page_view_stores_no_identifier_even_when_the_context_names_a_user(): void
    {
        $user = $this->createOwner();

        $this->beacon([
            't' => 'pv', 'm' => 'c', 'k' => $this->key(1),
            'c' => $this->context(['u' => UrlUtils::encodeId($user->id), 's' => 'ap', 'p' => '/home']),
            'r' => 'news.ycombinator.com', 'ti' => 'Dashboard | Event Schedule',
        ])->assertNoContent();

        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->consented);
        $this->assertNull($hit->visitor_key);
        $this->assertNull($hit->user_id);
        $this->assertNull($hit->title);
        $this->assertNull($hit->browser);
        $this->assertNull($hit->os);
        $this->assertSame('other', $hit->source_channel, 'an entrance keeps its source');
        $this->assertNotNull($hit->engaged_at, 'count-only views are sent at the moment of engagement');
    }

    /**
     * The mode is the page's claim, and an identified row needs it said out loud: a page view that
     * arrives without one (an old cached page, a hand-made request) is stored count-only.
     */
    public function test_a_page_view_without_a_mode_is_count_only(): void
    {
        $user = $this->createOwner();

        $this->beacon([
            't' => 'pv', 'k' => $this->key(1),
            'c' => $this->context(['u' => UrlUtils::encodeId($user->id), 's' => 'ap', 'p' => '/home']),
            'ti' => 'Dashboard | Event Schedule',
        ])->assertNoContent();

        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->consented);
        $this->assertNull($hit->visitor_key);
        $this->assertNull($hit->user_id);
        $this->assertNull($hit->title);
    }

    public function test_a_count_only_internal_navigation_keeps_no_source(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(1), 'c' => $this->context(), 'r' => parse_url(config('app.url'), PHP_URL_HOST)]);

        $hit = RealtimeHit::sole();
        $this->assertNull($hit->source_channel);
        $this->assertFalse($hit->is_entrance);
    }

    public function test_a_count_only_heartbeat_or_end_is_ignored(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(1), 'c' => $this->context()]);
        $before = RealtimeHit::sole()->last_seen_at;

        $this->travel(2)->minutes();
        $this->beacon(['t' => 'hb', 'm' => 'c', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1]);
        $this->beacon(['t' => 'end', 'm' => 'c', 'k' => $this->key(1)]);

        $hit = RealtimeHit::sole();
        $this->assertEquals($before, $hit->last_seen_at);
        $this->assertNull($hit->ended_at);
    }

    public function test_a_bad_or_stale_signature_or_a_bot_writes_nothing(): void
    {
        $tampered = $this->context();
        $tampered['p'] = '/admin';
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $tampered])->assertNoContent();

        $stale = $this->context(['t' => RealtimeTracker::now()->subDays(2)->timestamp]);
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(2), 'c' => $stale])->assertNoContent();

        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(3), 'c' => $this->context()], ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->assertNoContent();

        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(4), 'c' => $this->context()], ['HTTP_ACCEPT_LANGUAGE' => ''])
            ->assertNoContent();

        $this->assertSame(0, RealtimeHit::count());
    }

    public function test_a_malformed_message_is_refused(): void
    {
        $this->beacon(['t' => 'pv', 'k' => 'not-a-key', 'c' => $this->context()])->assertStatus(422);
        $this->beacon(['t' => 'nope', 'k' => $this->key(1)])->assertStatus(422);
        $this->beacon(['t' => 'pv', 'k' => $this->key(1), 'ti' => str_repeat('x', 5000)])->assertStatus(422);

        $this->assertSame(0, RealtimeHit::count());
    }

    public function test_nothing_is_written_while_tracking_is_off(): void
    {
        Setting::set('realtime_enabled', '0');

        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()])->assertNoContent();

        $this->assertSame(0, RealtimeHit::count());
    }

    public function test_the_beacon_never_sets_a_cookie(): void
    {
        $response = $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()]);

        $this->assertSame([], $response->headers->getCookies());
    }

    public function test_an_engagement_heartbeat_that_overtakes_its_page_view_still_ends_as_one_complete_row(): void
    {
        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1]);
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'r' => 'instagram.com', 'ti' => 'Pricing']);

        $hit = RealtimeHit::sole();
        $this->assertSame('social', $hit->source_channel);
        $this->assertSame('Pricing', $hit->title);
        $this->assertNotNull($hit->engaged_at);
    }

    public function test_heartbeats_keep_a_long_page_alive_and_end_closes_it(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()]);

        // Two in the same second: the second changes no row, which must not recreate it.
        $this->freezeSecond();
        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1]);
        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1]);
        $this->assertSame(1, RealtimeHit::count());

        // Open for two hours: heartbeats match by key with no age limit.
        for ($minute = 1; $minute <= 120; $minute += 30) {
            $this->travel(30)->minutes();
            $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1]);
            RealtimeTracker::prune();
        }

        $hit = RealtimeHit::sole();
        $this->assertTrue($hit->last_seen_at->gte(RealtimeTracker::now()->subMinute()));
        $this->assertNull($hit->ended_at);

        $this->beacon(['t' => 'end', 'm' => 'f', 'k' => $this->key(1)]);
        $this->assertNotNull(RealtimeHit::sole()->ended_at);

        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()]);
        $this->assertNull(RealtimeHit::sole()->ended_at, 'coming back to the tab clears the end');
    }

    public function test_a_pruned_page_view_is_recreated_by_a_heartbeat_but_never_by_an_end(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()]);
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(2), 'c' => $this->context()]);

        $this->travel(2)->hours();
        RealtimeTracker::prune();
        $this->assertSame(0, RealtimeHit::count());

        $this->beacon(['t' => 'end', 'm' => 'f', 'k' => $this->key(2)]);
        $this->assertSame(0, RealtimeHit::count());

        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1]);
        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->is_entrance);
        $this->assertNotNull($hit->engaged_at);
    }

    public function test_a_later_internal_page_inherits_the_visit_source(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'r' => 'www.bing.com', 'u' => ['campaign' => '']]);
        $this->travel(3)->minutes();
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(2), 'c' => $this->context(['p' => '/features']), 'r' => parse_url(config('app.url'), PHP_URL_HOST)]);

        $second = RealtimeHit::where('hit_key', $this->key(2))->sole();
        $this->assertSame('search', $second->source_channel);
        $this->assertSame('bing.com', $second->source_name);
        $this->assertFalse($second->is_entrance);
        $this->assertSame(RealtimeHit::where('hit_key', $this->key(1))->value('visitor_key'), $second->visitor_key);
    }

    public function test_accepting_mid_page_upgrades_the_count_only_row_in_place(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(1), 'c' => $this->context(), 'r' => 'duckduckgo.com']);
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'r' => 'duckduckgo.com', 'ti' => 'Pricing']);

        $hit = RealtimeHit::sole();
        $this->assertTrue($hit->consented);
        $this->assertNotNull($hit->visitor_key);
        $this->assertSame('Pricing', $hit->title);
        $this->assertNotNull($hit->engaged_at, 'already engaged when the count-only view was sent');
    }

    public function test_withdrawing_consent_strips_identity_from_this_visitor_and_no_one_else(): void
    {
        $user = $this->createOwner();
        $context = $this->context(['u' => UrlUtils::encodeId($user->id), 's' => 'ap', 'p' => '/home']);

        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $context, 'ti' => 'Home']);
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(2), 'c' => $this->context()], ['REMOTE_ADDR' => '198.51.100.9']);

        // From another network: only the account can reach row 1 now, not the visitor key or the
        // page's own key, so this pins the user_id branch.
        $this->beacon(['t' => 'revoke', 'k' => $this->key(3), 'c' => $context], ['REMOTE_ADDR' => '192.0.2.50']);

        $mine = RealtimeHit::where('hit_key', $this->key(1))->sole();
        $this->assertFalse($mine->consented);
        $this->assertNull($mine->visitor_key);
        $this->assertNull($mine->user_id);
        $this->assertNull($mine->title);

        $theirs = RealtimeHit::where('hit_key', $this->key(2))->sole();
        $this->assertTrue($theirs->consented);
        $this->assertNotNull($theirs->visitor_key);
    }

    public function test_revoke_clears_the_current_page_by_its_key_even_from_a_new_network(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()]);

        // A new IP since the page loaded: a different visitor key, and no account to fall back on.
        $this->beacon(['t' => 'revoke', 'k' => $this->key(1), 'c' => $this->context()], ['REMOTE_ADDR' => '198.51.100.44']);

        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->consented);
        $this->assertNull($hit->visitor_key);
    }

    public function test_revoke_strips_an_anonymous_visitors_inherited_source_but_keeps_an_entrance(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'r' => 'www.google.com']);
        $this->travel(1)->minutes();
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(2), 'c' => $this->context(['p' => '/features']), 'r' => parse_url(config('app.url'), PHP_URL_HOST)]);
        $this->assertSame('search', RealtimeHit::where('hit_key', $this->key(2))->value('source_channel'), 'the second page inherited the source');

        $this->beacon(['t' => 'revoke', 'k' => $this->key(3), 'c' => $this->context()]);

        $this->assertSame('search', RealtimeHit::where('hit_key', $this->key(1))->value('source_channel'), 'an entrance keeps its source');
        $this->assertNull(RealtimeHit::where('hit_key', $this->key(2))->value('source_channel'), 'an inherited source would link the two views');
        $this->assertSame(0, RealtimeHit::whereNotNull('visitor_key')->count());
    }

    public function test_a_count_only_view_of_a_page_whose_path_names_someone_stores_the_route_template(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(1),
            'c' => $this->context(['s' => 'ap', 'p' => '/acme-band/schedule', 'pt' => '/{subdomain}/{tab}'])]);
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(2),
            'c' => $this->context(['s' => 'gp', 'p' => '/ticket/order/15/{secret}', 'pt' => '/ticket/order/{order_id}/{secret}'])]);
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(3),
            'c' => $this->context(['s' => 'gp', 'p' => '/jazz-club', 'pt' => '/{subdomain}', 'r' => UrlUtils::encodeId(7)])]);

        $this->assertSame('/{subdomain}/{tab}', RealtimeHit::where('hit_key', $this->key(1))->value('path'));
        $this->assertSame('/ticket/order/{order_id}/{secret}', RealtimeHit::where('hit_key', $this->key(2))->value('path'));
        $this->assertSame('/jazz-club', RealtimeHit::where('hit_key', $this->key(3))->value('path'), 'a public schedule page keeps its address');

        // A visitor who accepted is identified anyway, so their row keeps the real address.
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(4),
            'c' => $this->context(['s' => 'ap', 'p' => '/acme-band/schedule', 'pt' => '/{subdomain}/{tab}'])]);
        $this->assertSame('/acme-band/schedule', RealtimeHit::where('hit_key', $this->key(4))->value('path'));

        // Clicking Allow on the page upgrades row 1 in place: the real address, and the template
        // kept beside it for a later withdrawal.
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1),
            'c' => $this->context(['s' => 'ap', 'p' => '/acme-band/schedule', 'pt' => '/{subdomain}/{tab}'])]);
        $upgraded = RealtimeHit::where('hit_key', $this->key(1))->sole();
        $this->assertTrue($upgraded->consented);
        $this->assertSame('/acme-band/schedule', $upgraded->path);
        $this->assertSame('/{subdomain}/{tab}', $upgraded->path_template);
    }

    public function test_withdrawing_swaps_a_path_that_names_the_visitor_for_its_template(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1),
            'c' => $this->context(['s' => 'ap', 'p' => '/acme-band/schedule', 'pt' => '/{subdomain}/{tab}'])]);
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(2), 'c' => $this->context()]);

        $app = RealtimeHit::where('hit_key', $this->key(1))->sole();
        $this->assertSame('/acme-band/schedule', $app->path);
        $this->assertSame('/{subdomain}/{tab}', $app->path_template);
        $this->assertNull(RealtimeHit::where('hit_key', $this->key(2))->value('path_template'), 'a public page needs no template');

        // Heartbeats stretch the page's life; a declined page view never had any.
        $this->travel(3)->minutes();
        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1]);
        $this->assertNotEquals($app->started_at, $app->fresh()->last_seen_at);

        $this->beacon(['t' => 'revoke', 'k' => $this->key(3), 'c' => $this->context()]);

        $app = $app->fresh();
        $this->assertSame('/{subdomain}/{tab}', $app->path, 'the schedule subdomain no longer names them');
        $this->assertNull($app->path_template);
        $this->assertEquals($app->started_at, $app->last_seen_at);
        $this->assertSame('/pricing', RealtimeHit::where('hit_key', $this->key(2))->value('path'));
    }

    public function test_withdrawing_after_half_past_midnight_still_reaches_yesterdays_rows(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-01 23:50:00', 'UTC'));
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()]);

        // Within the hour the row lives, but past visitorKeys()' half-hour overlap.
        $this->travelTo(\Carbon\Carbon::parse('2026-10-02 00:45:00', 'UTC'));
        $this->beacon(['t' => 'revoke', 'k' => $this->key(2), 'c' => $this->context()]);

        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->consented);
        $this->assertNull($hit->visitor_key);
    }

    public function test_accepting_then_declining_before_engaging_still_counts_the_view(): void
    {
        // Allow: a full page view, not engaged yet. Decline within five seconds: revoke, then the
        // count-only page view at engagement, under the same key.
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()]);
        $this->beacon(['t' => 'revoke', 'k' => $this->key(1), 'c' => $this->context()]);
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(1), 'c' => $this->context()]);

        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->consented);
        $this->assertNull($hit->visitor_key);
        $this->assertNotNull($hit->engaged_at, 'an unengaged row is left out of every number');
    }

    public function test_global_privacy_control_stops_heartbeats_from_identifying_anyone(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()]);
        $before = RealtimeHit::sole()->last_seen_at;

        $this->travel(3)->minutes();
        $gpc = ['HTTP_SEC_GPC' => '1'];
        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1], $gpc)->assertNoContent();
        // A heartbeat for a page with no row would otherwise insert an identified continuation.
        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(2), 'c' => $this->context(), 'g' => 1], $gpc)->assertNoContent();

        $this->assertEquals($before, RealtimeHit::sole()->last_seen_at);
    }

    public function test_a_tab_left_open_across_an_account_deletion_is_not_stored_as_that_user(): void
    {
        $user = $this->createOwner();
        $context = $this->context(['u' => UrlUtils::encodeId($user->id), 's' => 'ap', 'p' => '/home']);
        $user->delete();

        // The purged row is gone, so the next heartbeat recreates it from the (still valid) context.
        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $context, 'g' => 1])->assertNoContent();

        $hit = RealtimeHit::sole();
        $this->assertNull($hit->user_id);
        $this->assertSame(0, RealtimeHit::whereNotNull('user_id')->count());
    }

    public function test_a_malformed_context_never_errors_and_writes_nothing(): void
    {
        $signed = $this->context();

        foreach ([
            array_merge($signed, ['hb' => []]),
            array_merge($signed, ['t' => (string) $signed['t']]),
            array_merge($signed, ['p' => ['/pricing']]),
            array_diff_key($signed, ['sig' => true]),
            array_diff_key($signed, ['pt' => true]),
            'not-an-object',
        ] as $i => $context) {
            $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(10 + $i), 'c' => $context])->assertNoContent();
            $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(10 + $i), 'c' => $context])->assertNoContent();
        }

        $this->assertSame(0, RealtimeHit::count());
    }

    public function test_a_cross_site_post_writes_nothing_and_global_privacy_control_is_count_only(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()], ['HTTP_SEC_FETCH_SITE' => 'cross-site'])
            ->assertNoContent();
        $this->assertSame(0, RealtimeHit::count());

        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(2), 'c' => $this->context()], ['HTTP_SEC_FETCH_SITE' => 'same-origin', 'HTTP_SEC_GPC' => '1']);
        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->consented);
        $this->assertNull($hit->visitor_key);

        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(3), 'c' => $this->context()], ['HTTP_SEC_FETCH_SITE' => 'same-origin']);
        $this->assertTrue(RealtimeHit::where('hit_key', $this->key(3))->value('consented') == 1);
    }

    public function test_an_overtaking_heartbeat_leaves_the_page_view_an_entrance(): void
    {
        $this->beacon(['t' => 'hb', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'g' => 1]);
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(), 'r' => 'www.bing.com']);

        $this->assertTrue(RealtimeHit::sole()->is_entrance);
    }

    public function test_an_embed_is_stored_anonymously_and_a_preview_on_our_own_host_not_at_all(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(['s' => 'gp', 'p' => '/jazz']), 'fr' => 1, 'r' => 'www.jazzclub.example']);
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(2), 'c' => $this->context(['s' => 'gp', 'p' => '/jazz']), 'fr' => 1, 'r' => parse_url(config('app.url'), PHP_URL_HOST)]);

        $hit = RealtimeHit::sole();
        $this->assertSame('embed', $hit->surface);
        $this->assertFalse($hit->consented);
        $this->assertNull($hit->visitor_key);
        $this->assertNotNull($hit->engaged_at);
    }

    public function test_it_answers_on_a_custom_domain(): void
    {
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        DB::table('roles')->where('id', $role->id)->update([
            'custom_domain_host' => 'events.jazzclub.example',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);

        $this->call('POST', 'https://events.jazzclub.example/api/realtime', [], [], [], $this->server(), json_encode([
            't' => 'pv', 'm' => 'f', 'k' => $this->key(1),
            'c' => $this->context(['s' => 'gp', 'p' => '/', 'r' => UrlUtils::encodeId($role->id)]),
            'r' => 'events.jazzclub.example',
        ]))->assertNoContent();

        $hit = RealtimeHit::sole();
        $this->assertSame($role->id, $hit->role_id);
        $this->assertSame('direct', $hit->source_channel, 'the custom domain is an internal host');
    }

    public function test_the_shared_demo_login_is_never_stored_as_a_user(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context(['s' => 'ap', 'u' => '', 'd' => 1])]);

        $hit = RealtimeHit::sole();
        $this->assertNull($hit->user_id);
        $this->assertTrue($hit->is_demo);
    }

    /**
     * The team bit: a signed-in member of a schedule looking at that schedule's own guest page.
     * Their row is marked whatever their cookie choice, because the mark comes from the session
     * that rendered the page and not from consent, and a schedule's owner never counts it
     * (App\Services\ScheduleRealtime). Mutation: read is_team from the unsigned message.
     */
    public function test_a_member_viewing_their_own_schedule_page_is_marked_even_when_count_only(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $stranger = $this->createOwner();

        $render = function (?\App\Models\User $user) use ($role): ?array {
            $request = \Illuminate\Http\Request::create('/'.$role->subdomain);
            $request->setUserResolver(fn () => $user);

            return RealtimeTracker::context($request, 'gp', $role);
        };

        $member = $render($owner);
        $this->assertSame(1, $member['tm']);
        $this->assertTrue(RealtimeTracker::verify($member)['is_team']);

        // Anyone else's context is exactly what it was before the bit existed.
        $this->assertArrayNotHasKey('tm', $render($stranger));
        $this->assertArrayNotHasKey('tm', $render(null));

        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(1), 'c' => $member])->assertNoContent();

        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->consented);
        $this->assertTrue($hit->is_team);
    }

    /**
     * Nobody can add the bit (the signature would need it) and nobody can strip it (the signature
     * already has it); and a page rendered before the bit shipped still verifies, as nobody's team.
     * Mutation: always sign `tm`, or never sign it.
     */
    public function test_the_team_bit_cannot_be_forged_or_stripped_and_old_pages_still_verify(): void
    {
        $plain = $this->context(['s' => 'gp', 'p' => '/somewhere']);
        $this->assertFalse(RealtimeTracker::verify($plain)['is_team']);

        // A tab opened before the bit existed, signed the way that release signed: these eleven
        // fields and nothing else. Spelled out here, not taken from sign(), which is the thing
        // that must go on producing it.
        $legacy = ['s' => 'gp', 'p' => '/somewhere', 'pt' => '/somewhere', 'r' => '', 'e' => '', 'u' => '', 'a' => 0, 'd' => 0, 'em' => 0, 'hb' => 60, 't' => RealtimeTracker::now()->timestamp];
        $legacy['sig'] = hash_hmac('sha256', 'realtime-ctx|'.json_encode($legacy, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE), (string) config('app.key'));
        $this->assertNotNull(RealtimeTracker::verify($legacy), 'an open tab must not start being refused');

        $forged = $plain + ['tm' => 1];
        $this->assertNull(RealtimeTracker::verify($forged));

        $team = $this->context(['s' => 'gp', 'p' => '/somewhere', 'tm' => 1]);
        $this->assertTrue(RealtimeTracker::verify($team)['is_team']);

        $stripped = $team;
        unset($stripped['tm']);
        $this->assertNull(RealtimeTracker::verify($stripped));

        $this->assertNull(RealtimeTracker::verify(['tm' => 'yes'] + $plain), 'a mistyped bit is refused, not cast');
    }

    /**
     * Who may be listed for a schedule's organizer is the SERVER's reading of the visitor's own
     * recorded choice: it has to allow analytics and carry "org", the token cookie-consent.js
     * writes when the notice that was answered said that an organizer sees visits to their pages.
     * Nothing a page posts can stand in for it, and a count-only view has nobody to list.
     *
     * This used to be a bit the page sent after comparing the choice's date with a stamp, which
     * no test ran and which was wrong for anyone who answered an older notice after the stamp.
     * Mutation: set owner_visible from $identified alone, or from a posted `o`.
     */
    public function test_only_a_choice_made_on_a_notice_that_named_organizers_lists_the_visitor(): void
    {
        Setting::set('realtime_owner_view', '1');

        $at = now()->timestamp;
        $told = ['cookie_consent' => "analytics.marketing.org.{$at}"];
        $view = fn (int $n, string $mode, array $cookies, array $more = [], array $server = []) => $this->beacon(
            ['t' => 'pv', 'm' => $mode, 'k' => $this->key($n), 'c' => $this->context()] + $more, $server, $cookies
        )->assertNoContent();

        $view(1, 'f', $told);
        $view(2, 'f', ['cookie_consent' => "analytics.marketing.{$at}"]);
        $view(3, 'f', [], ['o' => 1]);
        $view(4, 'c', $told);
        $view(5, 'f', ['cookie_consent' => "marketing.org.{$at}"]);
        $view(6, 'f', ['cookie_consent' => 'analytics.org.'.now()->subYears(2)->timestamp]);
        $view(7, 'f', $told, [], ['HTTP_SEC_GPC' => '1']);
        $view(8, 'f', ['cookie_consent' => 'granted']);

        $visible = fn (int $n) => RealtimeHit::where('hit_key', $this->key($n))->sole()->owner_visible;

        $this->assertTrue($visible(1));
        $this->assertFalse($visible(2), 'answered a notice that did not name organizers: counted, never listed');
        $this->assertFalse($visible(3), 'a page cannot say it for its visitor');
        $this->assertFalse($visible(4), 'a count-only view has no one to list');
        $this->assertFalse($visible(5), 'the token means nothing without analytics');
        $this->assertFalse($visible(6), 'a choice that lapsed');
        $this->assertFalse($visible(7), 'Global Privacy Control declines everything');
        $this->assertFalse($visible(8), 'the one-category notice of before never named anyone');

        // And with the organizers' view off the notice does not name them, so nothing is marked
        // even for a choice that carries the token from a time it was on.
        Setting::set('realtime_owner_view', '0');
        $view(9, 'f', $told);
        $this->assertFalse($visible(9));
    }

    /**
     * Accepting mid-page upgrades the row in place and may bring the mark with it; withdrawing
     * takes it away with the rest of the identity. And a heartbeat that has to recreate a pruned
     * row reads the same choice. Mutation: leave owner_visible out of revoke().
     */
    public function test_accepting_mid_page_can_list_the_visitor_and_withdrawing_removes_them(): void
    {
        Setting::set('realtime_owner_view', '1');
        $told = ['cookie_consent' => 'analytics.org.'.now()->timestamp];

        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(1), 'c' => $this->context()])->assertNoContent();
        $this->assertFalse(RealtimeHit::sole()->owner_visible);

        $this->beacon(['t' => 'pv', 'm' => 'f', 'k' => $this->key(1), 'c' => $this->context()], [], $told)->assertNoContent();
        $this->assertTrue(RealtimeHit::sole()->owner_visible);

        $this->beacon(['t' => 'revoke', 'k' => $this->key(1), 'c' => $this->context()], [], $told)->assertNoContent();

        $hit = RealtimeHit::sole();
        $this->assertFalse($hit->consented);
        $this->assertFalse($hit->owner_visible);

        RealtimeHit::query()->delete();
        $this->beacon(['t' => 'hb', 'g' => 1, 'k' => $this->key(2), 'c' => $this->context()], [], $told)->assertNoContent();
        $this->assertTrue(RealtimeHit::sole()->owner_visible, 'a recreated row is the same visitor under the same choice');
    }

    /** Raw cookies, as a browser sends them: the api group neither encrypts nor decrypts any. */
    private function beacon(array $message, array $server = [], array $cookies = []): TestResponse
    {
        return $this->call('POST', '/api/realtime', [], $cookies, [], array_merge($this->server(), $server), json_encode($message));
    }

    private function server(): array
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_USER_AGENT' => self::UA,
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'REMOTE_ADDR' => '203.0.113.7',
        ];
    }

    private function context(array $overrides = []): array
    {
        $context = array_merge([
            's' => 'wp', 'p' => '/pricing', 'pt' => '/pricing', 'r' => '', 'e' => '', 'u' => '', 'a' => 0, 'd' => 0, 'em' => 0,
            'hb' => 60, 't' => RealtimeTracker::now()->timestamp,
        ], $overrides);

        $context['sig'] = RealtimeTracker::sign($context);

        return $context;
    }

    private function key(int $n): string
    {
        return str_pad(dechex($n), 32, '0', STR_PAD_LEFT);
    }
}
