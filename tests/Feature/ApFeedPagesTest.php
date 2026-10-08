<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Feeds tab of a schedule and the page a feed is added on.
 *
 * Adding is two steps: the address is checked, which reads it once and writes nothing, and then
 * the feed is added from what the check sealed. An address can be the key to a private calendar,
 * so it is posted and never printed: the pages show the site it is on.
 */
class ApFeedPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const CALENDAR = 'https://93.184.216.34/private-0123456789abcdef/basic.ics';

    private const SECRET = 'private-0123456789abcdef';

    private User $owner;

    private Role $role;

    private array $entries = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Http::preventStrayRequests();
        Http::fake(fn ($request) => match (true) {
            str_ends_with($request->url(), '/basic.ics') => Http::response($this->calendar(), 200, ['Content-Type' => 'text/calendar']),
            str_ends_with($request->url(), '/whats-on') => Http::response('<html><head><title>What\'s on</title></head><body><h1>Events</h1></body></html>', 200, ['Content-Type' => 'text/html']),
            str_ends_with($request->url(), '/feed.xml') => Http::response('<?xml version="1.0"?><rss version="2.0"><channel><title>News &amp; events</title><item><guid>1</guid><title>Concert</title><link>https://93.184.216.34/posts/1</link></item></channel></rss>', 200, ['Content-Type' => 'application/rss+xml']),
            str_ends_with($request->url(), '/posts/1') => Http::response('<html><head><script type="application/ld+json">'.json_encode(['@context' => 'https://schema.org', '@type' => 'Event', 'name' => 'Concert', 'startDate' => now('Europe/Vienna')->addDays(9)->setTime(20, 0)->toIso8601String()]).'</script></head></html>', 200, ['Content-Type' => 'text/html']),
            default => Http::response('nope', 500),
        });

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/Vienna']);
        $this->entries = [$this->entry('a', 'Farmers market', 3), $this->entry('b', 'Jazz night', 4, 'LOCATION:The Jazz Hole\, 1 Main St')];
    }

    private function entry(string $uid, string $name, int $days, string $more = ''): string
    {
        $start = now('Europe/Vienna')->addDays($days)->setTime(19, 30);

        return "UID:{$uid}\nSUMMARY:{$name}\nDTSTART;TZID=Europe/Vienna:".$start->format('Ymd\THis')."\nDTEND;TZID=Europe/Vienna:".$start->copy()->addHours(2)->format('Ymd\THis').($more ? "\n".$more : '');
    }

    private function calendar(): string
    {
        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\nX-WR-CALNAME:Town hall calendar\r\n"
            .implode('', array_map(fn ($entry) => "BEGIN:VEVENT\r\n".str_replace("\n", "\r\n", trim($entry))."\r\nEND:VEVENT\r\n", $this->entries))
            ."END:VCALENDAR\r\n";
    }

    private function feed(array $attrs = [], ?Role $role = null): EventFeed
    {
        $url = $attrs['url'] ?? 'https://calendar.example.org/'.Str::random(8).'.ics';

        return EventFeed::create($attrs + [
            'role_id' => ($role ?? $this->role)->id, 'name' => 'A feed', 'url' => $url, 'url_hash' => EventFeed::hashOf($url),
            'host' => (string) parse_url($url, PHP_URL_HOST), 'kind' => EventFeed::KIND_CALENDAR, 'source_timezone' => 'Europe/Vienna',
            'baseline_done_at' => now(), 'last_success_at' => now(), 'next_check_at' => now()->addHour(),
        ]);
    }

    private function tab(?Role $role = null, string $tab = 'feeds')
    {
        return $this->actingAs($this->owner)->get(route('role.view_admin', ['subdomain' => ($role ?? $this->role)->subdomain, 'tab' => $tab]));
    }

    private function check(string $address, ?Role $role = null)
    {
        return $this->actingAs($this->owner)->post(route('role.feeds.check', ['subdomain' => ($role ?? $this->role)->subdomain]), ['address' => $address]);
    }

    /** The sealed token a check put on the page. */
    private function token($response): string
    {
        $this->assertSame(1, preg_match('/name="feed_token" value="([^"]+)"/', $response->getContent(), $m), 'the check did not find a feed');

        return html_entity_decode($m[1]);
    }

    public function test_a_schedule_with_no_feed_has_no_tab_until_the_page_is_opened_and_then_says_how_to_add_one(): void
    {
        $this->tab(null, 'schedule')->assertOk()->assertDontSee(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']), false);

        $this->tab()->assertOk()
            ->assertSee(__('messages.feeds_empty_title'))
            ->assertSee(route('role.feeds.create', ['subdomain' => $this->role->subdomain]), false)
            ->assertSee(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']), false);
    }

    public function test_the_tab_lists_each_feed_by_what_it_needs_and_shows_the_site_never_the_address(): void
    {
        $this->feed(['name' => 'Fine and quiet', 'url' => self::CALENDAR]);
        $this->feed(['name' => 'Wants a decision', 'decide_count' => 2, 'waiting_count' => 12, 'publish_mode' => EventFeed::DRAFT]);
        $this->feed(['name' => 'Cannot be read', 'failure_count' => 5, 'last_success_at' => now()->subDays(3), 'last_status' => 'http_error']);
        $this->feed(['name' => 'Just added', 'baseline_done_at' => null, 'last_success_at' => null, 'next_check_at' => now()]);
        $this->feed(['name' => 'Waiting for a word', 'paused_at' => now(), 'pause_reason' => EventFeed::PAUSED_TRANSFER]);
        $this->feed(['name' => '{{ 7 * 7 }}<script>alert(1)</script>']);

        $response = $this->tab()->assertOk();

        $response->assertSeeInOrder(['Wants a decision', 'Cannot be read', 'Waiting for a word', 'Just added', 'Fine and quiet']);
        $response->assertSee(trans_choice('messages.feeds_status_decide', 2, ['count' => 2]))
            ->assertSee(trans_choice('messages.feeds_waiting', 12, ['count' => 12]))
            ->assertSee(__('messages.feeds_status_paused'))
            ->assertSee(__('messages.feeds_status_never'))
            ->assertSee(__('messages.feeds_status_ok'))
            ->assertSee(__('messages.feeds_read_soon'))
            ->assertSee(trans_choice('messages.feeds_decide_title', 2, ['count' => 2]));

        // The key to a private calendar is not printed. Its site is.
        $response->assertDontSee(self::SECRET, false)->assertSee('93.184.216.34');
        // A feed's name is its source's own words: text, and nothing a template could run.
        $response->assertDontSee('<script>alert(1)</script>', false);
        $this->assertStringContainsString('v-pre><bdi>{{ 7 * 7 }}&lt;script&gt;', $response->getContent());
        // Each opens its own page.
        $response->assertSee(route('role.feeds.show', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId(EventFeed::where('name', 'Fine and quiet')->value('id'))]), false);

        // The strip counts what is waiting for somebody: drafts and decisions.
        $this->assertMatchesRegularExpression('/'.preg_quote(__('messages.feeds_tab'), '/').'\s*<span class="ap-tab-count is-waiting">14<\/span>/', $this->tab(null, 'schedule')->getContent());
    }

    /** The page admits viewers, and feeds are for the people who run the schedule. */
    public function test_a_viewer_is_not_shown_the_tab_or_let_onto_any_of_it(): void
    {
        $this->feed();
        $viewer = User::factory()->create();
        $this->role->users()->attach($viewer->id, ['level' => 'viewer']);
        $feedsUrl = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']);

        $this->actingAs($viewer)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))->assertOk()->assertDontSee($feedsUrl, false);
        $this->actingAs($viewer)->get($feedsUrl)->assertRedirect(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']));
        $this->actingAs($viewer)->get(route('role.feeds.create', ['subdomain' => $this->role->subdomain]))->assertRedirect(route('home'));
        $this->actingAs($viewer)->post(route('role.feeds.check', ['subdomain' => $this->role->subdomain]), ['address' => self::CALENDAR])->assertRedirect(route('home'));
        $this->actingAs($viewer)->post(route('role.feeds.store', ['subdomain' => $this->role->subdomain]), ['publish_mode' => 'draft'])->assertRedirect(route('home'));

        Http::assertNothingSent();
        $this->assertSame(1, EventFeed::count());
    }

    public function test_a_plan_without_feeds_is_told_what_they_are_and_what_works_today(): void
    {
        $pro = $this->createRole($this->owner, 'talent', ['plan_type' => 'pro']);

        $this->tab($pro)->assertOk()
            ->assertSee(__('messages.feeds_gate_text'))
            ->assertSee(__('messages.feeds_gate_bullet_edits'))
            ->assertSee(__('messages.feeds_import_once'))
            ->assertSee('source=feeds', false)
            ->assertDontSee(route('role.feeds.create', ['subdomain' => $pro->subdomain]), false);

        $this->actingAs($this->owner)->get(route('role.feeds.create', ['subdomain' => $pro->subdomain]))
            ->assertRedirect(route('role.view_admin', ['subdomain' => $pro->subdomain, 'tab' => 'feeds']))
            ->assertSessionHas('error', __('messages.feeds_need_enterprise'));
        $this->check(self::CALENDAR, $pro)->assertRedirect();
        Http::assertNothingSent();

        // A selfhost install has them on every plan.
        config(['app.hosted' => false]);
        $this->tab($pro)->assertOk()->assertSee(__('messages.feeds_empty_title'));
        $this->actingAs($this->owner)->get(route('role.feeds.create', ['subdomain' => $pro->subdomain]))->assertOk();
    }

    /**
     * The tab only shows once a schedule has a feed, so the way in is a row of the schedule
     * form's Integrations tab: it leads to adding one, to the ones there are, or to what the
     * plan would give.
     */
    public function test_the_schedule_form_has_a_row_that_leads_to_feeds(): void
    {
        $form = fn (Role $role) => $this->actingAs($this->owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $paneOf = function (string $html): string {
            $from = strpos($html, '<div id="integration-tab-feeds"');
            $this->assertNotFalse($from);

            return substr($html, $from, strpos($html, '<div id="integration-tab-advanced"') - $from);
        };
        $rowOf = function (string $html): string {
            $from = strpos($html, 'data-row-group="integration" data-tab="feeds"');

            return substr($html, $from, strpos($html, '</button>', $from) - $from);
        };
        $tab = fn (Role $role) => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'feeds']);
        $add = fn (Role $role) => route('role.feeds.create', ['subdomain' => $role->subdomain]);

        // None yet: the row says so and offers to add one.
        $html = $form($this->role);
        $this->assertStringContainsString('<span class="event-row-title">'.__('messages.integration_row_incoming_feeds').'</span>', $html);
        $this->assertStringContainsString('<bdi>'.__('messages.none').'</bdi>', $rowOf($html));
        $this->assertStringNotContainsString(__('messages.enterprise'), $rowOf($html));
        $this->assertStringContainsString('href="'.$add($this->role).'"', $paneOf($html));
        $this->assertStringNotContainsString('href="'.$tab($this->role).'"', $paneOf($html));

        // With feeds: their names on the row, never an address, and the way to them.
        foreach (['Town hall', '<b>Shopper</b>', 'Third', 'Fourth'] as $i => $name) {
            $this->feed(['name' => $name, 'url' => 'https://93.184.216.34/'.self::SECRET.'/'.$i.'.ics']);
        }
        $html = $form($this->role);
        $this->assertStringContainsString('<bdi>Town hall, &lt;b&gt;Shopper&lt;/b&gt;, Third, ...</bdi>', $rowOf($html));
        $this->assertStringContainsString('href="'.$tab($this->role).'"', $paneOf($html));
        $this->assertStringNotContainsString('href="'.$add($this->role).'"', $paneOf($html));
        $this->assertStringNotContainsString(self::SECRET, $html);

        // A plan without feeds: the row is marked, and leads to what they are, not to adding one.
        $pro = $this->createRole($this->owner, 'talent', ['plan_type' => 'pro']);
        $html = $form($pro);
        $this->assertStringContainsString(__('messages.enterprise'), $rowOf($html));
        $this->assertStringContainsString('href="'.$tab($pro).'"', $paneOf($html));
        $this->assertStringNotContainsString('href="'.$add($pro).'"', $paneOf($html));

        // A selfhost install has them.
        config(['app.hosted' => false]);
        $html = $form($pro);
        $this->assertStringNotContainsString(__('messages.enterprise'), $rowOf($html));
        $this->assertStringContainsString('href="'.$add($pro).'"', $paneOf($html));
    }

    /**
     * "Keep this link in sync" on the import page hands the address to the check by POST. A
     * guest's form, which shares the view, has none of it.
     */
    public function test_the_import_page_offers_to_keep_a_link_in_sync(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        $page = fn (Role $role) => $this->actingAs($this->owner)->get(route('event.show_import_ai', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $html = $page($this->role);
        $this->assertStringContainsString(__('messages.feeds_keep_in_sync'), $html);
        $this->assertStringContainsString('feedsAllowed: true', $html);
        $this->assertMatchesRegularExpression('#<form method="post" id="feed-keep-form" action="'.preg_quote(route('role.feeds.check', ['subdomain' => $this->role->subdomain]), '#').'" hidden>\s*<input type="hidden" name="_token"[^>]*>\s*<input type="hidden" name="address" id="feed-keep-address">#', $html);
        // Its own form, after the page's: a form inside a form is dropped by the browser.
        $this->assertGreaterThan(strpos($html, 'id="event-import-app"'), strpos($html, 'id="feed-keep-form"'));
        $this->assertSame(1, substr_count(substr($html, strpos($html, 'id="event-import-app"'), strpos($html, 'id="feed-keep-form"') - strpos($html, 'id="event-import-app"')), '</form>'));
        // Only for what a feed can read, and never an address in a link.
        $this->assertStringContainsString("['ics', 'page'].includes(this.preview.meta.source) && this.isLink", $html);
        $this->assertStringNotContainsString('feeds/check?', $html);

        // Off the plan the button says so in place, and nothing is posted.
        $pro = $this->createRole($this->owner, 'talent', ['plan_type' => 'pro']);
        $html = $page($pro);
        $this->assertStringContainsString('feedsAllowed: false', $html);
        $this->assertStringContainsString(__('messages.feeds_need_enterprise'), $html);
        $this->assertStringContainsString('if (! this.feedsAllowed) {', $html);

        // The guest form.
        $curator = $this->createRole($this->owner, 'curator', ['accept_requests' => true, 'require_account' => false]);
        auth()->logout();
        $guest = $this->get(route('event.guest_import', ['subdomain' => $curator->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('isGuestPage: true', $guest);
        $this->assertStringContainsString('feedsAllowed: false', $guest);
        $this->assertStringNotContainsString('feed-keep-form', $guest);
        $this->assertStringNotContainsString(__('messages.feeds_keep_in_sync'), $guest);
        $this->assertStringNotContainsString('feeds/check', $guest);
    }

    public function test_checking_an_address_shows_what_is_there_and_adds_nothing(): void
    {
        // One of the two is already on the schedule, made by hand.
        $this->createEvent($this->role, [
            'creator_role_id' => $this->role->id, 'name' => 'Jazz night',
            'starts_at' => now('Europe/Vienna')->addDays(4)->setTime(19, 30)->utc()->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($this->owner)->get(route('role.feeds.create', ['subdomain' => $this->role->subdomain]))->assertOk()
            ->assertSee(__('messages.feeds_add_lead'))
            ->assertSee(__('messages.feeds_works_jolioo_title'));

        $response = $this->check(self::CALENDAR)->assertOk();

        $response->assertSee(trans_choice('messages.feeds_found_events', 2, ['count' => 2]))
            ->assertSee(__('messages.feeds_found_kind_calendar'))
            ->assertSee(trans_choice('messages.feeds_found_matched', 1, ['count' => 1]))
            ->assertSeeInOrder(['Farmers market', 'Jazz night', 'The Jazz Hole'])
            ->assertSee(__('messages.feeds_time_note', ['zone' => 'Vienna']))
            // One of the two is new: publishing would publish one.
            ->assertSee(trans_choice('messages.feeds_add_and_publish', 1, ['count' => 1]))
            ->assertSee('value="Town hall calendar"', false)
            ->assertSee(__('messages.feeds_gone_delete'));

        // Nothing was written, and the address is nowhere on the page it was typed into.
        $this->assertSame(0, EventFeed::count());
        $this->assertSame(1, Event::count());
        $response->assertDontSee(self::SECRET, false)->assertSee('93.184.216.34');
    }

    public function test_adding_what_was_checked_makes_a_feed_that_is_due_at_once(): void
    {
        $stage = $this->createGroup($this->role);
        $token = $this->token($this->check(self::CALENDAR));

        $this->actingAs($this->owner)->post(route('role.feeds.store', ['subdomain' => $this->role->subdomain]), [
            'feed_token' => $token,
            'name' => 'Our town',
            'publish_mode' => 'publish',
            'left_action' => 'cancel',
            'group_id' => UrlUtils::encodeId($stage->id),
            'category_id' => collect($this->role->getEventCategories())->first()['id'],
            'source_timezone' => 'Europe/London',
        ])->assertRedirect(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']))
            ->assertSessionHas('message', __('messages.feeds_added_publishing'));

        $feed = EventFeed::firstOrFail();
        $this->assertSame(self::CALENDAR, $feed->url);
        $this->assertSame(['Our town', '93.184.216.34', EventFeed::KIND_CALENDAR, EventFeed::PUBLISH, EventFeed::LEFT_CANCEL, 'Europe/London'],
            [$feed->name, $feed->host, $feed->kind, $feed->publish_mode, $feed->left_action, $feed->source_timezone]);
        $this->assertSame([$this->owner->id, $stage->id, true], [$feed->added_by, $feed->group_id, $feed->can_see_leaving]);
        $this->assertNotNull($feed->category_id);
        $this->assertMatchesRegularExpression('/^[a-z0-9]{12}$/', $feed->baseline_batch);
        $this->assertTrue($feed->next_check_at->lte(now()));
        // On the record, by its site and kind.
        $log = \App\Models\AuditLog::where('action', 'schedule.feed_add')->firstOrFail();
        $this->assertStringNotContainsString(self::SECRET, (string) $log->metadata);
        $this->assertStringContainsString('93.184.216.34', (string) $log->metadata);

        // And it is the tab that now shows it.
        $this->tab()->assertOk()->assertSee('Our town')->assertSee(__('messages.feeds_status_never'));
    }

    public function test_the_choices_default_to_the_careful_ones_and_what_is_not_the_schedules_own_is_dropped(): void
    {
        $token = $this->token($this->check(self::CALENDAR));
        $someoneElses = $this->createGroup($this->createRole($this->createOwner()));

        $this->actingAs($this->owner)->post(route('role.feeds.store', ['subdomain' => $this->role->subdomain]), [
            'feed_token' => $token,
            'publish_mode' => 'draft',
            'group_id' => UrlUtils::encodeId($someoneElses->id),
            'category_id' => 999999,
        ])->assertRedirect();

        $feed = EventFeed::firstOrFail();
        $this->assertSame(['Town hall calendar', EventFeed::DRAFT, EventFeed::LEFT_KEEP, 'Europe/Vienna', null, null],
            [$feed->name, $feed->publish_mode, $feed->left_action, $feed->source_timezone, $feed->group_id, $feed->category_id]);
    }

    /** A feed of posts lists its newest few, so it is never asked to act on an event being gone. */
    public function test_a_feed_of_posts_is_followed_to_its_pages_and_is_not_offered_what_it_cannot_tell(): void
    {
        $response = $this->check('93.184.216.34/feed.xml')->assertOk();

        $response->assertSee(trans_choice('messages.feeds_found_posts', 1, ['count' => 1]))
            ->assertSee(__('messages.feeds_found_kind_items'))
            ->assertSee('Concert')
            ->assertSee(__('messages.feeds_gone_cannot'))
            ->assertDontSee(__('messages.feeds_gone_delete'))
            ->assertSee('value="News &amp; events"', false);

        $this->actingAs($this->owner)->post(route('role.feeds.store', ['subdomain' => $this->role->subdomain]), [
            'feed_token' => $this->token($response), 'publish_mode' => 'draft', 'left_action' => 'delete',
        ])->assertRedirect();

        $feed = EventFeed::firstOrFail();
        $this->assertSame([EventFeed::KIND_ITEMS, EventFeed::LEFT_KEEP, false, 'https://93.184.216.34/feed.xml'], [$feed->kind, $feed->left_action, $feed->can_see_leaving, $feed->url]);
    }

    public function test_an_address_that_cannot_be_a_feed_says_why_and_keeps_what_was_typed(): void
    {
        foreach ([
            'not an address' => __('messages.feeds_problem_invalid_url'),
            'https://www.facebook.com/events/123' => __('messages.feeds_problem_sign_in_wall', ['platform' => 'Facebook']),
            'https://93.184.216.34/whats-on' => __('messages.feeds_problem_no_events_title'),
            'https://93.184.216.34/broken' => __('messages.feeds_problem_http_error', ['status' => 500]),
            'http://127.0.0.1/calendar.ics' => __('messages.feeds_problem_unreachable'),
        ] as $address => $message) {
            $this->check($address)->assertOk()->assertSee($message)->assertDontSee('name="feed_token"', false);
        }

        $this->feed(['url' => self::CALENDAR]);
        $this->check(self::CALENDAR)->assertOk()->assertSee(__('messages.feeds_problem_already_added'));
        $this->assertSame(1, EventFeed::count());
    }

    /** A token is what a check sealed, for this person, on this schedule, in the last hour. */
    public function test_a_feed_is_only_added_from_a_check_of_ones_own(): void
    {
        $token = $this->token($this->check(self::CALENDAR));
        $add = fn (array $body, ?User $as = null, ?Role $on = null) => $this->actingAs($as ?? $this->owner)
            ->post(route('role.feeds.store', ['subdomain' => ($on ?? $this->role)->subdomain]), $body + ['publish_mode' => 'draft']);

        $elsewhere = $this->createRole($this->owner, 'talent');
        $admin = User::factory()->create();
        $this->role->users()->attach($admin->id, ['level' => 'admin']);

        $add(['feed_token' => 'made-up'])->assertRedirect(route('role.feeds.create', ['subdomain' => $this->role->subdomain]))->assertSessionHas('error', __('messages.feeds_check_again'));
        $add([])->assertRedirect(route('role.feeds.create', ['subdomain' => $this->role->subdomain]));
        $add(['feed_token' => $token], null, $elsewhere)->assertRedirect(route('role.feeds.create', ['subdomain' => $elsewhere->subdomain]));
        $add(['feed_token' => $token], $admin)->assertRedirect(route('role.feeds.create', ['subdomain' => $this->role->subdomain]));

        $this->travel(61)->minutes();
        $add(['feed_token' => $token])->assertRedirect(route('role.feeds.create', ['subdomain' => $this->role->subdomain]));
        $this->assertSame(0, EventFeed::count());

        $this->travelBack();
        $add(['feed_token' => $token, 'publish_mode' => 'sometimes'])->assertSessionHasErrors('publish_mode');
        $add(['feed_token' => $token])->assertRedirect(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']));
        // The same check cannot add the same address twice.
        $add(['feed_token' => $token])->assertSessionHas('error', __('messages.feeds_problem_already_added'));
        $this->assertSame(1, EventFeed::count());
    }

    public function test_a_schedule_has_room_for_ten_feeds(): void
    {
        for ($i = 0; $i < EventFeed::PER_SCHEDULE; $i++) {
            $this->feed();
        }

        $this->actingAs($this->owner)->get(route('role.feeds.create', ['subdomain' => $this->role->subdomain]))
            ->assertRedirect(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']))
            ->assertSessionHas('error', __('messages.feeds_limit', ['count' => 10]));
        $this->check(self::CALENDAR)->assertRedirect();
        Http::assertNothingSent();
    }
}
