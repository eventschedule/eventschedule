<?php

namespace Tests\Feature;

use App\Models\EventFeed;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminAlertService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /admin/feeds: every feed on the install, for the people who run it.
 *
 * What is held: that it lists what is not being read first, that it shows the site and never
 * the address (for a private calendar the address is the key to it, and an admin has no more
 * need of it than a member), that its two buttons do what they say, and that nobody who is not a
 * platform admin gets any of it.
 */
class AdminFeedsPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SECRET = 'private-0123456789abcdef';

    private User $admin;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['app.hosted' => true]);
        $this->admin = $this->createOwner(true);
        $this->role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Springfield Events']);
        AdminAlertService::flush();
    }

    private function admin(): self
    {
        if (! Route::has('admin.feeds')) {
            $this->markTestSkipped('The admin routes are not registered in this environment.');
        }

        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
    }

    private function feed(string $name, array $attrs = [], ?Role $role = null): EventFeed
    {
        $url = 'https://calendar.example.org/'.self::SECRET.'/'.Str::random(8).'.ics';

        return EventFeed::create($attrs + [
            'role_id' => ($role ?? $this->role)->id, 'name' => $name, 'url' => $url, 'url_hash' => EventFeed::hashOf($url),
            'host' => 'calendar.example.org', 'kind' => EventFeed::KIND_CALENDAR, 'source_timezone' => 'Europe/Vienna',
            'baseline_done_at' => now(), 'last_success_at' => now()->subMinutes(20), 'next_check_at' => now()->addMinutes(40),
        ]);
    }

    public function test_the_page_lists_what_is_not_being_read_first_and_never_an_address(): void
    {
        $this->feed('Fine and quiet');
        $this->feed('Paused by its team', ['paused_at' => now(), 'pause_reason' => EventFeed::PAUSED_BY_OWNER]);
        $this->feed('Cannot be read', ['failure_count' => 3, 'last_status' => 'http_error', 'stats' => ['last_http' => 503], 'last_success_at' => now()->subDays(2)]);
        $this->feed('Has drafts <b>waiting</b>', ['waiting_count' => 12, 'decide_count' => 2]);
        $this->feed('On a plan without feeds', [], $this->createRole($this->createOwner(), 'talent', ['plan_type' => 'pro']));

        $response = $this->admin()->get(route('admin.feeds'))->assertOk();

        $response->assertSeeInOrder(['Cannot be read', 'Paused by its team', 'Fine and quiet'])
            ->assertSee('Springfield Events')
            ->assertSee('calendar.example.org')
            // A reason and a status code, never what the other server said.
            ->assertSee('http_error 503')
            ->assertSee(trans_choice('messages.feeds_admin_tries', 3, ['count' => 3]))
            ->assertSee(__('messages.feeds_admin_reason_owner'))
            ->assertSee(trans_choice('messages.feeds_admin_waiting_count', 14, ['count' => 14]))
            ->assertSee(__('messages.feeds_status_off_plan'))
            ->assertSee('Has drafts &lt;b&gt;waiting&lt;/b&gt;', false)
            ->assertDontSee(self::SECRET, false)
            ->assertDontSee(__('messages.feeds_admin_many_failing'));

        // The figures over the list.
        $html = $response->getContent();
        // The schedule's name opens the schedule. The raw id went into the address once, and
        // the page behind it decodes an encoded one: every one of these links was a 404.
        $schedule = route('admin.schedules.edit', ['role' => $this->role->encodeId()]);
        $this->assertStringContainsString('href="'.$schedule.'"', $html);
        $this->admin()->get($schedule)->assertOk()->assertSee('Springfield Events');
        $this->assertSame(1, substr_count($html, route('admin.feeds.resume', ['hash' => UrlUtils::encodeId(EventFeed::where('name', 'Paused by its team')->value('id'))])));
        // Read now for what is being read; nothing to press for a plan that cannot read.
        $this->assertStringContainsString(route('admin.feeds.read', ['hash' => UrlUtils::encodeId(EventFeed::where('name', 'Cannot be read')->value('id'))]), $html);
        $this->assertStringNotContainsString(route('admin.feeds.read', ['hash' => UrlUtils::encodeId(EventFeed::where('name', 'On a plan without feeds')->value('id'))]), $html);
        $this->assertStringNotContainsString(route('admin.feeds.read', ['hash' => UrlUtils::encodeId(EventFeed::where('name', 'Paused by its team')->value('id'))]), $html);
    }

    public function test_the_list_is_searched_and_filtered(): void
    {
        $this->feed('Town hall');
        $this->feed('Shopper', ['failure_count' => 1]);
        // Paused after failing: it is paused, and no longer one of the failing.
        $this->feed('Tavern', ['paused_at' => now(), 'pause_reason' => EventFeed::PAUSED_FAILING, 'failure_count' => 9]);
        $this->feed('Library', ['waiting_count' => 3]);
        $other = $this->createRole($this->createOwner(), 'talent', ['name' => 'Shelbyville Nights', 'plan_type' => 'enterprise']);
        $this->feed('Bowlarama', [], $other);
        $names = fn (array $query) => collect(['Town hall', 'Shopper', 'Tavern', 'Library', 'Bowlarama'])
            ->filter(fn ($name) => str_contains($this->admin()->get(route('admin.feeds', $query))->assertOk()->getContent(), '<bdi>'.$name.'</bdi>'))->values()->all();

        $this->assertSame(['Shopper'], $names(['state' => 'failing']));
        $this->assertSame(['Tavern'], $names(['state' => 'paused']));
        $this->assertSame(['Library'], $names(['state' => 'waiting']));
        $this->assertSame(['Bowlarama'], $names(['search' => 'Shelbyville']));
        $this->assertSame(['Bowlarama'], $names(['search' => $other->subdomain]));
        $this->assertSame(['Town hall'], $names(['search' => 'town']));
        $this->assertCount(5, $names(['search' => 'calendar.example']));
        // A wildcard typed into the box is a character, not a wildcard.
        $this->assertSame([], $names(['search' => '%']));

        // A schedule that was deleted takes its feeds off this list.
        $other->forceFill(['is_deleted' => true])->save();
        $this->assertSame([], $names(['search' => 'Bowlarama']));
    }

    public function test_read_now_and_resume_do_what_they_say(): void
    {
        $backedOff = $this->feed('Backed off', ['failure_count' => 4, 'next_check_at' => now()->addDay(), 'last_checked_at' => now()->subHours(3)]);
        $paused = $this->feed('Paused', ['paused_at' => now(), 'pause_reason' => EventFeed::PAUSED_FAILING, 'failure_count' => 9]);
        $hash = fn (EventFeed $feed) => ['hash' => UrlUtils::encodeId($feed->id)];

        $this->admin()->from(route('admin.feeds'))->post(route('admin.feeds.read', $hash($backedOff)))
            ->assertRedirect(route('admin.feeds'))->assertSessionHas('message', __('messages.feeds_read_now_done'));
        $this->assertFalse($backedOff->fresh()->next_check_at->isFuture());

        // A paused feed is not read by asking: it is resumed.
        $this->admin()->from(route('admin.feeds'))->post(route('admin.feeds.read', $hash($paused)))
            ->assertSessionHas('error', __('messages.feeds_admin_paused_first'));
        $this->assertTrue($paused->fresh()->isPaused());

        $this->admin()->from(route('admin.feeds'))->post(route('admin.feeds.resume', $hash($paused)))
            ->assertSessionHas('message', __('messages.feeds_resumed_done'));
        $this->assertFalse($paused->fresh()->isPaused());
        $this->assertSame(0, $paused->fresh()->failure_count);
        $this->assertSame(1, \App\Models\AuditLog::where('action', 'schedule.feed_update')->where('model_id', $this->role->id)->count());

        $this->admin()->post(route('admin.feeds.read', ['hash' => UrlUtils::encodeId(987654)]))->assertNotFound();
    }

    public function test_nobody_but_a_platform_admin_gets_any_of_it(): void
    {
        $feed = $this->feed('Paused', ['paused_at' => now(), 'pause_reason' => EventFeed::PAUSED_FAILING]);
        $owner = User::find($this->role->user_id);

        $this->actingAs($owner)->get(route('admin.feeds'))->assertDontSee('Paused');
        $this->actingAs($owner)->post(route('admin.feeds.resume', ['hash' => UrlUtils::encodeId($feed->id)]));
        $this->actingAs($owner)->post(route('admin.feeds.read', ['hash' => UrlUtils::encodeId($feed->id)]));

        $this->assertTrue($feed->fresh()->isPaused());
        auth()->logout();
        $this->get(route('admin.feeds'))->assertRedirect();
    }

    /** Many failing at once is the platform's to hear about: a row on the dashboard that opens this page. */
    public function test_many_feeds_failing_at_once_is_an_alert_that_opens_the_page(): void
    {
        foreach (range(1, 4) as $n) {
            $this->feed('Failing '.$n, ['failure_count' => 2, 'last_checked_at' => now()->subHour(), 'host' => "site{$n}.example"]);
        }
        $alert = function () {
            AdminAlertService::flush();

            return AdminAlertService::items()->firstWhere('type', 'feeds_failing');
        };
        $this->assertNull($alert(), 'four failing feeds are four schedules with a problem each');

        $this->feed('Failing 5', ['failure_count' => 2, 'last_checked_at' => now()->subHour(), 'host' => 'site5.example']);
        $row = $alert();
        $this->assertNotNull($row);
        $this->assertSame(5, $row['count']);
        $this->assertSame('amber', $row['color']);
        $this->assertStringContainsString('/admin/feeds?state=failing', $row['url']);

        $this->admin()->get(route('admin.feeds'))->assertOk()->assertSee(__('messages.feeds_admin_many_failing'));
        $this->admin()->get(route('admin.dashboard'))->assertOk()->assertSee(trans_choice('messages.admin_alert_feeds_failing', 5, ['count' => 5]));

        // The navigation names the page under Manage.
        $this->admin()->get(route('admin.feeds'))->assertSee(route('admin.feeds'), false);
    }
}
