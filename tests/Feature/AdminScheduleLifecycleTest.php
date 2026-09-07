<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Services\DemoService;
use App\Services\ScheduleDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An admin taking a squatted subdomain back on /admin/schedules.
 *
 * roles.subdomain is UNIQUE and nothing checking availability looks at is_deleted, so marking a
 * schedule deleted used to leave it holding the name forever. The only thing that ever freed one
 * was the owner's hard delete, which cascades roughly twenty child tables. Releasing therefore
 * renames the row and remembers what it was called.
 */
class AdminScheduleLifecycleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
    }

    /**
     * The /admin routes are registered at boot from IS_HOSTED, which differs by environment, and
     * EnsureUserIsAdmin gates them on a password confirmed this session.
     */
    private function actingAsAdmin(): self
    {
        if (! Route::has('admin.schedules')) {
            $this->markTestSkipped('Hosted-only admin routes are not registered in this environment.');
        }

        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(true));
    }

    /** An auto-created schedule, the way EventRepo::saveEvent makes one: no owner. */
    private function createUnclaimedRole(string $name, string $subdomain): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->name = $name;
        $role->type = 'venue';
        $role->timezone = 'America/New_York';
        $role->save();

        return $role->fresh();
    }

    public function test_marking_a_schedule_deleted_releases_its_subdomain(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);

        $this->actingAsAdmin()
            ->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]))
            ->assertRedirect();

        $role->refresh();

        $this->assertTrue((bool) $role->is_deleted);
        $this->assertSame('tel-aviv-deleted-'.$role->id, $role->subdomain);
        $this->assertSame('tel-aviv', $role->subdomain_before_delete);
    }

    /** The whole point: the freed name is genuinely available to the next schedule. */
    public function test_a_new_schedule_can_take_the_released_subdomain(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);

        $this->actingAsAdmin()
            ->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]));

        $this->assertFalse(
            Role::where('subdomain', 'tel-aviv')->exists(),
            'nothing holds the name any more, which is what the unique index cares about'
        );

        $replacement = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $this->assertSame('tel-aviv', $replacement->fresh()->subdomain);
    }

    /**
     * The API, unfollow and merge paths soft-delete WITHOUT renaming, so this covers a row that
     * still holds its name as well as one that has been released.
     */
    public function test_a_deleted_schedules_guest_page_is_gone(): void
    {
        $owner = $this->createOwner();

        $released = $this->createRole($owner, 'venue', ['subdomain' => 'tel-aviv']);
        $this->actingAsAdmin()->post(route('admin.schedules.mark_deleted', ['role' => $released->encodeId()]));

        $neverRenamed = $this->createRole($owner, 'venue', ['subdomain' => 'haifa']);
        Role::whereKey($neverRenamed->id)->update(['is_deleted' => true]);

        $this->get('/'.$released->fresh()->subdomain)->assertRedirect();
        $this->get('/haifa')->assertRedirect();
    }

    /**
     * The iCal and RSS feeds carry the same events as the guest page, are public, and are served
     * with a one-hour public Cache-Control - so a deleted schedule left syndicating there stays
     * readable long after its page has gone. They were the two endpoints that kept the old
     * `! $role->isClaimed()` idiom when the rest were guarded.
     */
    public function test_the_syndication_feeds_refuse_a_deleted_schedule(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $this->createEvent($role, ['name' => 'Upcoming Show', 'creator_role_id' => $role->id]);

        $this->get(route('feed.ical', ['subdomain' => 'tel-aviv']))->assertOk()->assertSee('Upcoming Show');
        $this->get(route('feed.rss', ['subdomain' => 'tel-aviv']))->assertOk()->assertSee('Upcoming Show', false);

        Role::whereKey($role->id)->update(['is_deleted' => true]);

        $this->get(route('feed.ical', ['subdomain' => 'tel-aviv']))->assertNotFound();
        $this->get(route('feed.rss', ['subdomain' => 'tel-aviv']))->assertNotFound();
    }

    /**
     * The JSON feeds behind the guest page must go quiet with it, not keep serving its events.
     *
     * The schedule needs a real event and a real past event, or both feeds come back empty
     * whether or not the guard is there and this test pins nothing.
     */
    public function test_the_guest_json_feeds_refuse_a_deleted_schedule(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $this->createEvent($role, ['name' => 'Upcoming Show', 'creator_role_id' => $role->id]);
        $this->createEvent($role, [
            'name' => 'Past Show',
            'creator_role_id' => $role->id,
            'starts_at' => now()->subDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        // Proves the feeds do serve this schedule's events while it is live, so the empty
        // assertions below are the guard working rather than there being nothing to return.
        // list_past_events needs `before`: without it the method returns empty for any schedule,
        // which would make this test pass with the guard removed.
        $this->assertNotEmpty(
            $this->getJson(route('role.calendar_events', ['subdomain' => 'tel-aviv']))->assertOk()->json('events')
        );
        $this->assertNotEmpty(
            $this->getJson(route('role.list_past_events', ['subdomain' => 'tel-aviv', 'before' => now()->format('Y-m-d')]))->assertOk()->json('events')
        );

        Role::whereKey($role->id)->update(['is_deleted' => true]);

        $this->getJson(route('role.calendar_events', ['subdomain' => 'tel-aviv']))
            ->assertOk()
            ->assertJsonPath('events', []);

        $this->getJson(route('role.list_past_events', ['subdomain' => 'tel-aviv', 'before' => now()->format('Y-m-d')]))
            ->assertOk()
            ->assertJsonPath('events', []);
    }

    public function test_restore_reclaims_the_original_subdomain_when_free(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $admin = $this->actingAsAdmin();

        $admin->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]));
        $admin->post(route('admin.schedules.restore', ['role' => $role->encodeId()]));

        $role->refresh();

        $this->assertFalse((bool) $role->is_deleted);
        $this->assertSame('tel-aviv', $role->subdomain);
        $this->assertNull($role->subdomain_before_delete);
    }

    /** Losing the original name is the expected outcome of a release that did its job. */
    public function test_restore_keeps_the_released_name_when_the_original_was_taken(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $admin = $this->actingAsAdmin();

        $admin->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]));
        $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);

        $admin->post(route('admin.schedules.restore', ['role' => $role->encodeId()]));

        $role->refresh();

        $this->assertFalse((bool) $role->is_deleted);
        $this->assertSame('tel-aviv-deleted-'.$role->id, $role->subdomain);
        $this->assertSame('tel-aviv', $role->subdomain_before_delete, 'the original stays visible to the admin');
    }

    /**
     * approved_subdomains holds subdomain STRINGS, and autoAcceptsEventFrom() reads it to decide
     * whether a submitted event publishes to a curator's page immediately. A name that changes
     * hands must not carry the previous holder's auto-accept trust to a stranger.
     */
    public function test_releasing_prunes_the_name_from_other_schedules_approved_subdomains(): void
    {
        $junk = $this->createRole($this->createOwner(), 'talent', ['subdomain' => 'tel-aviv']);

        $curator = $this->createRole($this->createOwner(), 'curator');
        $curator->approved_subdomains = ['tel-aviv', 'somewhere-else'];
        $curator->save();

        $this->actingAsAdmin()
            ->post(route('admin.schedules.mark_deleted', ['role' => $junk->encodeId()]));

        $stranger = $this->createRole($this->createOwner(), 'talent', ['subdomain' => 'tel-aviv']);

        $curator->refresh();
        $this->assertSame(['somewhere-else'], $curator->approved_subdomains);
        $this->assertFalse(
            $curator->autoAcceptsEventFrom(null, $stranger),
            'the new holder of a freed name must not inherit auto-accept'
        );
    }

    /** A rename is the same schedule at a new address, so the trust follows it rather than dying. */
    public function test_an_admin_rename_rewrites_approved_subdomains_rather_than_dropping_them(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', ['subdomain' => 'tel-aviv']);

        $curator = $this->createRole($this->createOwner(), 'curator');
        $curator->approved_subdomains = ['tel-aviv'];
        $curator->save();

        $this->actingAsAdmin()->put(route('admin.schedules.update_details', ['role' => $talent->encodeId()]), [
            'name' => $talent->name,
            'new_subdomain' => 'tel-aviv-music',
            'email' => $talent->email,
        ])->assertRedirect();

        $this->assertSame('tel-aviv-music', $talent->fresh()->subdomain);
        $this->assertSame(['tel-aviv-music'], $curator->fresh()->approved_subdomains);
    }

    public function test_an_admin_rename_refuses_a_taken_subdomain(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['subdomain' => 'alpha']);
        $this->createRole($owner, 'venue', ['subdomain' => 'beta']);

        $this->actingAsAdmin()->put(route('admin.schedules.update_details', ['role' => $role->encodeId()]), [
            'name' => $role->name,
            'new_subdomain' => 'beta',
            'email' => $role->email,
        ])->assertSessionHasErrors('new_subdomain');

        $this->assertSame('alpha', $role->fresh()->subdomain);
    }

    /** cleanSubdomain() would silently hand back Str::random(8); an operator should be told. */
    public function test_an_admin_rename_refuses_a_reserved_subdomain(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'alpha']);

        $this->actingAsAdmin()->put(route('admin.schedules.update_details', ['role' => $role->encodeId()]), [
            'name' => $role->name,
            'new_subdomain' => 'blog',
            'email' => $role->email,
        ])->assertSessionHasErrors('new_subdomain');

        $this->assertSame('alpha', $role->fresh()->subdomain);
    }

    /**
     * The subdomain field is posted on every save of this form, so a stored value that no longer
     * passes the current rules must not lock the admin out of editing the schedule's name.
     */
    public function test_saving_details_keeps_a_grandfathered_reserved_subdomain(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        Role::whereKey($role->id)->update(['subdomain' => 'blog']);
        $role->refresh();

        $this->actingAsAdmin()->put(route('admin.schedules.update_details', ['role' => $role->encodeId()]), [
            'name' => 'Renamed Anyway',
            'new_subdomain' => 'blog',
            'email' => $role->email,
        ])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame('Renamed Anyway', $role->name);
        $this->assertSame('blog', $role->subdomain);
    }

    /**
     * The likeliest squatter: a venue EventRepo::saveEvent() auto-created while importing an
     * event. It has no user_id, so adminListable() hid it from this page entirely.
     */
    public function test_an_ownerless_schedule_is_reachable_and_can_be_marked_deleted(): void
    {
        $orphan = $this->createUnclaimedRole('Tel Aviv', 'tel-aviv');
        $admin = $this->actingAsAdmin();

        $this->assertSame(0, $admin->get(route('admin.schedules'))->viewData('roles')->total());

        $this->assertSame(
            1,
            $admin->get(route('admin.schedules', ['owner' => 'unclaimed']))->viewData('roles')->total()
        );

        $admin->post(route('admin.schedules.mark_deleted', ['role' => $orphan->encodeId()]))->assertRedirect();

        $this->assertFalse(Role::where('subdomain', 'tel-aviv')->exists());
        $this->assertSame('tel-aviv-deleted-'.$orphan->id, $orphan->fresh()->subdomain);
    }

    /**
     * The backlog: ApiScheduleController::destroy(), unfollow() and performMerge() have all been
     * soft-deleting without renaming, so those rows are deleted AND still holding good names.
     */
    public function test_an_already_deleted_schedule_can_release_its_subdomain(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        Role::whereKey($role->id)->update(['is_deleted' => true]);

        $this->actingAsAdmin()
            ->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]))
            ->assertRedirect();

        $role->refresh();

        $this->assertTrue((bool) $role->is_deleted, 'it was already deleted and stays deleted');
        $this->assertSame('tel-aviv-deleted-'.$role->id, $role->subdomain);
        $this->assertFalse(Role::where('subdomain', 'tel-aviv')->exists());
    }

    /**
     * ResolveCustomDomain rewrites HOST to {subdomain}.{base} from a row it caches for ten
     * minutes, so a deleted schedule still resolving there would route that domain's traffic to
     * whichever schedule has since taken its name.
     */
    public function test_a_deleted_schedules_custom_domain_stops_resolving(): void
    {
        $role = $this->givenAScheduleOnACustomDomain();

        $this->get('https://events.example.org')->assertOk();

        $this->actingAsAdmin()->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]));

        $this->get('https://events.example.org')->assertNotFound();
    }

    /**
     * And comes back on restore rather than staying dark until the cache entry ages out.
     *
     * Deliberately does NOT assert the 404 in between, the way the sibling test above does: a
     * request to the custom domain re-roots the URL generator at that host, so the next
     * route()-driven admin POST is issued against events.example.org and the middleware 404s it
     * before the controller runs. Same class of trap as the pinAppUrl() rule in CLAUDE.md.
     */
    public function test_restoring_brings_the_custom_domain_back(): void
    {
        $role = $this->givenAScheduleOnACustomDomain();
        $admin = $this->actingAsAdmin();

        $admin->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]));
        $admin->post(route('admin.schedules.restore', ['role' => $role->encodeId()]));

        $this->assertFalse((bool) $role->fresh()->is_deleted);
        $this->get('https://events.example.org')->assertOk();
    }

    private function givenAScheduleOnACustomDomain(): Role
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $role->custom_domain = 'https://events.example.org';
        $role->custom_domain_host = 'events.example.org';
        $role->custom_domain_mode = 'direct';
        $role->custom_domain_status = 'active';
        $role->save();

        return $role;
    }

    public function test_the_demo_schedule_cannot_be_marked_deleted_or_renamed(): void
    {
        $demo = $this->createRole($this->createOwner(), 'venue', [
            'subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN,
        ]);
        $admin = $this->actingAsAdmin();

        $admin->post(route('admin.schedules.mark_deleted', ['role' => $demo->encodeId()]));
        $admin->put(route('admin.schedules.update_details', ['role' => $demo->encodeId()]), [
            'name' => 'Hijacked',
            'new_subdomain' => 'hijacked',
        ]);

        $demo->refresh();
        $this->assertFalse((bool) $demo->is_deleted);
        $this->assertSame(DemoService::DEMO_ROLE_SUBDOMAIN, $demo->subdomain);
    }

    public function test_the_actions_are_audit_logged_with_both_subdomains(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);

        $this->actingAsAdmin()->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]));

        $log = AuditLog::where('action', 'admin.schedule_delete')->latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('tel-aviv', $log->old_values['subdomain']);
        $this->assertSame('tel-aviv-deleted-'.$role->id, $log->new_values['subdomain']);
    }

    /**
     * An operator may legitimately blank a junk schedule's address - the owner-facing form
     * requires one, so only this page can.
     *
     * It used to throw from inside Role's `updating` hook, which fired a verification mail at a
     * null address; the resulting LogicException escaped before the UPDATE, so the whole save was
     * lost - name and subdomain included, with a 500 for the admin.
     */
    public function test_clearing_the_email_saves_the_rest_of_the_form(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);

        $this->actingAsAdmin()->put(route('admin.schedules.update_details', ['role' => $role->encodeId()]), [
            'name' => 'Renamed While Clearing Email',
            'new_subdomain' => 'tel-aviv',
            'email' => '',
        ])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertNull($role->email);
        $this->assertSame('Renamed While Clearing Email', $role->name);
    }

    /**
     * A field the request never carried is a no-op, not a clear. `?? null` conflated "submitted
     * empty" with "absent" and silently wiped stored contact details - and for phone that drops
     * phone_verified_at through the updating hook too.
     */
    public function test_omitting_a_contact_field_leaves_it_alone(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        Role::whereKey($role->id)->update(['phone' => '+15551234567', 'phone_verified_at' => now()]);

        $this->actingAsAdmin()->put(route('admin.schedules.update_details', ['role' => $role->encodeId()]), [
            'name' => 'Only The Name Changed',
            'new_subdomain' => 'tel-aviv',
            'email' => $role->email,
        ])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame('+15551234567', $role->phone);
        $this->assertNotNull($role->phone_verified_at);
    }

    /** A restored schedule that could not reclaim its name must not keep advertising the old one. */
    public function test_the_was_name_is_only_shown_while_deleted(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $admin = $this->actingAsAdmin();

        $admin->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]));
        $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $admin->post(route('admin.schedules.restore', ['role' => $role->encodeId()]));

        // The column is kept on purpose so the edit page can explain what happened, but the list
        // must not badge a live schedule with it.
        $this->assertSame('tel-aviv', $role->fresh()->subdomain_before_delete);
        $admin->get(route('admin.schedules', ['search' => 'Test Schedule']))
            ->assertOk()
            ->assertDontSee('was tel-aviv');
    }

    /**
     * A repeat release must not forget what the schedule was originally called.
     *
     * releasedSubdomain() is idempotent, but the write around it was not: it recorded the row's
     * CURRENT name, which on a second pass is the mangled one - losing the real original and
     * leaving restore() nothing to reclaim. Reachable by a double-submitted POST.
     */
    public function test_releasing_twice_keeps_the_first_recorded_name(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);
        $service = app(ScheduleDeletionService::class);

        $service->markDeleted($role);
        $service->markDeleted($role->fresh());

        $role->refresh();
        $this->assertSame('tel-aviv', $role->subdomain_before_delete);
        $this->assertSame('tel-aviv-deleted-'.$role->id, $role->subdomain);
    }

    /**
     * Pruning an approve list must not run Role's saving hook on the curator.
     *
     * That hook geocodes through a 10-second Http::get() whenever a row's stored geo_address does
     * not match its composed address, and this runs inside markDeleted()'s transaction holding a
     * row lock. It also re-renders description_html and friends on a row we only meant to drop one
     * entry from. A query-builder write fires no model events, which is what this pins.
     */
    public function test_pruning_an_approve_list_fires_no_model_events_on_the_curator(): void
    {
        Http::fake();
        // Without a key the geocode branch is skipped and the test would prove nothing about it.
        config(['services.google.backend' => 'test-key']);

        $junk = $this->createRole($this->createOwner(), 'talent', ['subdomain' => 'tel-aviv']);

        $curator = $this->createRole($this->createOwner(), 'curator', ['city' => 'Haifa']);
        $curator->approved_subdomains = ['tel-aviv'];
        $curator->save();

        // Stale rendered HTML the saving hook would rewrite, and a stale geocode watermark that
        // would send it to Google. Written past the model so no hook runs here either.
        Role::whereKey($curator->id)->update([
            'description' => '# Heading',
            'description_html' => '<!--stale-->',
            'geo_address' => null,
        ]);

        // Only requests made by the release itself count; building the fixtures above legitimately
        // geocodes through the same hook.
        $before = count(Http::recorded());

        app(ScheduleDeletionService::class)->markDeleted($junk->fresh());

        $curator->refresh();

        $this->assertNull($curator->approved_subdomains, 'the entry is still pruned');
        $this->assertSame('<!--stale-->', $curator->description_html, 'the saving hook must not have run');
        $this->assertCount($before, Http::recorded(), 'the release must make no outbound request');
    }

    /**
     * All three mutating routes, not just the first. update_details matters most: its FormRequest
     * loads the schedule and runs full validation BEFORE the controller's own isAdmin() guard, so
     * EnsureUserIsAdmin is the only thing standing in front of it.
     */
    public function test_a_non_admin_cannot_reach_any_of_it(): void
    {
        if (! Route::has('admin.schedules')) {
            $this->markTestSkipped('Hosted-only admin routes are not registered in this environment.');
        }

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['subdomain' => 'tel-aviv', 'name' => 'Untouched']);
        Role::whereKey($role->id)->update(['is_deleted' => true, 'subdomain_before_delete' => 'tel-aviv']);

        // Asserted against home rather than a bare assertRedirect(): the controllers' own
        // redirect()->back() would also satisfy that, so it would not prove the middleware ran.
        $this->actingAs($owner)
            ->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]))
            ->assertRedirect(route('home'));

        $this->actingAs($owner)
            ->post(route('admin.schedules.restore', ['role' => $role->encodeId()]))
            ->assertRedirect(route('home'));

        $this->actingAs($owner)
            ->put(route('admin.schedules.update_details', ['role' => $role->encodeId()]), [
                'name' => 'Hijacked',
                'new_subdomain' => 'hijacked',
                'email' => 'attacker@gmail.com',
            ])
            ->assertRedirect(route('home'));

        $role->refresh();
        $this->assertTrue((bool) $role->is_deleted, 'restore must not have run');
        $this->assertSame('tel-aviv', $role->subdomain);
        $this->assertSame('Untouched', $role->name);
    }
}
