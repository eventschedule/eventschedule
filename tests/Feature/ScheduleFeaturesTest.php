<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Services\AuditService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class ScheduleFeaturesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_schedule_merge_moves_events_to_target(): void
    {
        $owner = $this->createOwner();
        $source = $this->createRole($owner, 'venue');
        $target = $this->createRole($owner, 'venue');
        // Merge only allows an unclaimed source (canMergeRoles guards verified records).
        \App\Models\Role::where('id', $source->id)->update(['email_verified_at' => null]);
        $source->refresh();
        $event = $this->createEvent($source);

        $this->actingAs($owner)->post(route('role.merge', ['subdomain' => $source->subdomain]), [
            'target_subdomain' => $target->subdomain,
        ]);

        $this->assertDatabaseHas('event_role', ['event_id' => $event->id, 'role_id' => $target->id]);
        $this->assertDatabaseMissing('event_role', ['event_id' => $event->id, 'role_id' => $source->id]);
    }

    public function test_schedule_merge_keeps_the_stronger_pivot_level(): void
    {
        $owner = $this->createOwner();
        $source = $this->createRole($owner, 'venue');
        // The target is a stub the user merely follows, so before the fix the merge demoted
        // them from owner to follower on the venue they kept.
        $target = $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        \App\Models\Role::where('id', $source->id)->update(['email_verified_at' => null]);
        \App\Models\Role::where('id', $target->id)->update(['user_id' => null]);
        DB::table('role_user')->where('role_id', $target->id)->update(['level' => 'follower']);
        $source->refresh();

        $this->actingAs($owner)->post(route('role.merge', ['subdomain' => $source->subdomain]), [
            'target_subdomain' => $target->subdomain,
        ]);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $target->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);

        // An unclaimed target absorbing an owned source inherits the owner too.
        $this->assertSame($owner->id, $target->fresh()->user_id);
    }

    public function test_schedule_merge_never_relabels_a_follower_as_viewer(): void
    {
        $owner = $this->createOwner();
        $teammate = $this->createOwner();
        $source = $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        $target = $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        $source->refresh();

        // The level sweep runs over every user with a pivot on both schedules, not just the one
        // clicking merge. This teammate is read-only on the source and follows the target.
        // viewer must NOT outrank follower: only a follower gets edit rights on an unclaimed
        // role (Role::isEditableBy) and only a follower appears on the Following page
        // (User::following), so promoting them to viewer would quietly take both away.
        $this->followRole($teammate, $source, 'viewer');
        $this->followRole($teammate, $target, 'follower');

        $this->actingAs($owner)->post(route('role.merge', ['subdomain' => $source->subdomain]), [
            'target_subdomain' => $target->subdomain,
        ]);

        $this->assertDatabaseHas('role_user', [
            'role_id' => $target->id,
            'user_id' => $teammate->id,
            'level' => 'follower',
        ]);
    }

    public function test_schedule_merge_does_not_hand_over_a_stranger_owner_id(): void
    {
        $owner = $this->createOwner();
        $stranger = $this->createOwner();

        // An unclaimed venue can still carry someone else's user_id: ConvertsLocationToVenue
        // stamps it on every venue it invents while attaching that user only as a follower.
        $source = $this->createRole($stranger, 'venue', ['email_verified_at' => null]);
        DB::table('role_user')->where('role_id', $source->id)->update(['level' => 'follower']);
        $this->followRole($owner, $source);

        $target = $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        \App\Models\Role::where('id', $target->id)->update(['user_id' => null]);
        $source->refresh();

        $this->actingAs($owner)->post(route('role.merge', ['subdomain' => $source->subdomain]), [
            'target_subdomain' => $target->subdomain,
        ]);

        // The survivor must not end up nominally owned by a user who never ran the source.
        $this->assertNull($target->fresh()->user_id);
    }

    public function test_schedule_merge_repoints_outlook_sync_rows(): void
    {
        $owner = $this->createOwner();
        $source = $this->createRole($owner, 'venue');
        $target = $this->createRole($owner, 'venue');
        \App\Models\Role::where('id', $source->id)->update(['email_verified_at' => null]);
        $source->refresh();
        $event = $this->createEvent($source);

        DB::table('microsoft_calendar_syncs')->insert([
            'user_id' => $owner->id,
            'event_id' => $event->id,
            'role_id' => $source->id,
            'microsoft_event_id' => 'graph-event-1',
            'microsoft_calendar_id' => 'graph-calendar-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('role.merge', ['subdomain' => $source->subdomain]), [
            'target_subdomain' => $target->subdomain,
        ]);

        $this->assertDatabaseHas('microsoft_calendar_syncs', [
            'event_id' => $event->id,
            'role_id' => $target->id,
        ]);
        $this->assertDatabaseMissing('microsoft_calendar_syncs', [
            'event_id' => $event->id,
            'role_id' => $source->id,
        ]);
    }

    public function test_promote_co_owned_acceptance_accepts_curator_events_on_owned_venue(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $venue = $this->createRole($owner, 'venue');

        // Accepted on the curator; the importer left the venue side unaccepted (false).
        $event = $this->createEvent($curator);
        $event->roles()->attach($venue->id, ['is_accepted' => false]);

        $this->artisan('app:promote-co-owned-acceptance', ['--curator' => $curator->subdomain])
            ->assertExitCode(0);

        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id, 'role_id' => $venue->id, 'is_accepted' => 1,
        ]);
    }

    public function test_promote_skips_venue_owned_by_someone_else(): void
    {
        $curatorOwner = $this->createOwner();
        $venueOwner = $this->createOwner();
        $curator = $this->createRole($curatorOwner, 'curator');
        $venue = $this->createRole($venueOwner, 'venue'); // different owner -> not co-owned

        $event = $this->createEvent($curator);
        $event->roles()->attach($venue->id, ['is_accepted' => false]);

        $this->artisan('app:promote-co-owned-acceptance', ['--curator' => $curator->subdomain])
            ->assertExitCode(0);

        // Unchanged: acceptance must not carry onto someone else's venue.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id, 'role_id' => $venue->id, 'is_accepted' => 0,
        ]);
    }

    public function test_merge_venues_promotes_acceptance_on_co_owned_target(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        // Same name + empty city/country => detected as duplicates by venueDuplicateGroups().
        $target = $this->createRole($owner, 'venue', ['name' => 'Dup Venue']);
        $source = $this->createRole($owner, 'venue', ['name' => 'Dup Venue', 'email_verified_at' => null]);
        $source->refresh();

        // Two future events accepted on the curator, one per venue, unaccepted on the venue side.
        $eventA = $this->createEvent($curator);
        $eventA->roles()->attach($source->id, ['is_accepted' => false]);
        $eventB = $this->createEvent($curator);
        $eventB->roles()->attach($target->id, ['is_accepted' => false]);

        $this->actingAs($owner)->post(route('role.merge_venues_group', ['subdomain' => $curator->subdomain]), [
            'target_id' => UrlUtils::encodeId($target->id),
            'source_ids' => [UrlUtils::encodeId($source->id)],
        ]);

        // Source event moved onto the target, and both are now accepted on the target venue.
        $this->assertDatabaseHas('event_role', ['event_id' => $eventA->id, 'role_id' => $target->id, 'is_accepted' => 1]);
        $this->assertDatabaseHas('event_role', ['event_id' => $eventB->id, 'role_id' => $target->id, 'is_accepted' => 1]);
    }

    public function test_curator_merge_venues_page_renders_its_groups(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $target = $this->createRole($owner, 'venue', ['name' => 'Dup Venue']);
        $source = $this->createRole($owner, 'venue', ['name' => 'Dup Venue', 'email_verified_at' => null]);

        $eventA = $this->createEvent($curator);
        $eventA->roles()->attach($source->id, ['is_accepted' => false]);
        $eventB = $this->createEvent($curator);
        $eventB->roles()->attach($target->id, ['is_accepted' => false]);

        // The merge view is shared with the account-wide page and takes its URLs and copy keys
        // as variables now, so it has to be rendered on this path too, not only posted to.
        $response = $this->actingAs($owner)
            ->get(route('role.merge_venues', ['subdomain' => $curator->subdomain]))
            ->assertOk();

        $this->assertCount(1, $response->viewData('groups'));
        $response->assertSee(__('messages.merge_venues_intro'));
        $response->assertSee(route('role.merge_venues_group', ['subdomain' => $curator->subdomain]));
    }

    public function test_calendar_import_dedupes_venue_string_variants(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');

        $sync = new class
        {
            use \App\Services\Concerns\ConvertsLocationToVenue;

            public function run($role, $location)
            {
                return $this->convertLocationToVenue($role, $location);
            }
        };

        // Same place, two string variants (extra comma + spacing) must resolve to ONE venue.
        $a = $sync->run($curator, "Patrick's Caesarea");
        $b = $sync->run($curator, "Patrick's,  Caesarea");

        $this->assertNotNull($a);
        $this->assertEquals($a->id, $b->id);
        $this->assertEquals(1, \App\Models\Role::where('type', 'venue')->where('user_id', $owner->id)->count());
    }

    public function test_calendar_import_matches_users_existing_named_venue(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $existing = $this->createRole($owner, 'venue', ['name' => "Mike's Place"]);

        $sync = new class
        {
            use \App\Services\Concerns\ConvertsLocationToVenue;

            public function run($role, $location)
            {
                return $this->convertLocationToVenue($role, $location);
            }
        };

        // A "Venue, City" location must match the user's existing cleanly-named venue (no new record).
        $matched = $sync->run($curator, "Mike's Place, Tel Aviv");

        $this->assertEquals($existing->id, $matched->id);
        $this->assertEquals(1, \App\Models\Role::where('type', 'venue')->where('user_id', $owner->id)->count());
    }

    /** Anonymous holder for the calendar-sync trait, so the attach decision can be driven directly. */
    private function locationVenueSyncer(): object
    {
        return new class
        {
            use \App\Services\Concerns\ConvertsLocationToVenue;

            public function run($event, $role, $location)
            {
                return $this->attachLocationVenue($event, $role, $location);
            }
        };
    }

    public function test_calendar_import_creates_no_venue_when_syncing_a_venue_schedule(): void
    {
        $owner = $this->createOwner();
        $venueSchedule = $this->createRole($owner, 'venue', ['name' => 'Zappa Club']);
        $event = $this->createEvent($venueSchedule);

        // The schedule IS the venue, so the location can never be attached. Creating a venue
        // here left an orphan - zero events, follower-attached - in the user's venue dropdown.
        $attached = $this->locationVenueSyncer()->run($event, $venueSchedule, 'Derech Shlomo 24, Tel Aviv');

        $this->assertFalse($attached);
        $this->assertSame(1, \App\Models\Role::where('type', 'venue')->count());
    }

    public function test_calendar_import_creates_no_venue_when_the_event_already_has_one(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue', ['name' => 'Barby']);

        $event = $this->createEvent($curator);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        // A changed location on an already-placed event: we do not re-point the event, so
        // resolving the new string may only ever be a no-op, never a new record.
        $attached = $this->locationVenueSyncer()->run($event, $curator, 'Somewhere Else, Haifa');

        $this->assertFalse($attached);
        $this->assertSame(1, \App\Models\Role::where('type', 'venue')->count());
        $this->assertSame($venue->id, $event->fresh()->venue->id);
    }

    public function test_calendar_import_still_attaches_a_location_venue_for_a_curator(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($curator);

        $attached = $this->locationVenueSyncer()->run($event, $curator, 'The Anchor, Haifa');

        $this->assertTrue($attached);

        $venue = \App\Models\Role::where('type', 'venue')->sole();
        $this->assertSame('The Anchor, Haifa', $venue->name);
        $this->assertSame($venue->id, $event->fresh()->venue->id);
    }

    public function test_calendar_import_matches_a_venue_the_user_administers(): void
    {
        $owner = $this->createOwner();
        $admin = $this->createOwner();
        $curator = $this->createRole($admin, 'curator');
        $venue = $this->createRole($owner, 'venue', ['name' => "Mike's Place"]);

        // The curator's user only administers the venue. Before, only owner/follower pivots were
        // searched, so their sync recreated a venue they already had access to.
        $this->followRole($admin, $venue, 'admin');

        $event = $this->createEvent($curator);
        $this->locationVenueSyncer()->run($event, $curator, "Mike's Place, Tel Aviv");

        $this->assertSame(1, \App\Models\Role::where('type', 'venue')->count());
        $this->assertSame($venue->id, $event->fresh()->venue->id);
    }

    /**
     * false is the DECLINED state, not "undecided": guest listings filter is_accepted = true and
     * the Requests tab filters whereNull, so a false row is invisible on the venue's page AND
     * missing from the queue its owner approves from, with nothing that revisits it.
     *
     * The old expression was isMember(), which counts owner/admin/viewer, while the venue lookup
     * deliberately includes FOLLOWER - so a venue the user merely follows always landed there.
     */
    public function test_calendar_import_leaves_a_followed_venue_pending_not_declined(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);

        // Somebody else's claimed venue, which this user follows and which moderates submissions.
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', [
            'name' => 'Barby',
            'accept_requests' => true,
            'require_approval' => true,
        ]);
        $this->followRole($owner, $venue, 'follower');

        $event = $this->createEvent($curator);
        $this->assertTrue($this->locationVenueSyncer()->run($event, $curator, 'Barby'));

        $pivot = \Illuminate\Support\Facades\DB::table('event_role')
            ->where('event_id', $event->id)
            ->where('role_id', $venue->id)
            ->first();

        $this->assertNull($pivot->is_accepted,
            'pending, so the venue owner is asked - false would hide it from them for good');
    }

    /**
     * The venue the sync just invented for this user must not land pending.
     *
     * convertLocationToVenue() sets user_id but attaches the user only as a `follower`, so
     * autoAcceptsEventFrom() answers no on every rule: not a member (follower is not
     * owner/admin/viewer), user_id IS set so the ownerless rule misses, and require_approval
     * defaults to TRUE so accept_requests does not save it. That leaves is_accepted = NULL on a
     * venue whose only human is a follower - and accept() requires isEditor(), so nobody can ever
     * clear it. One stuck row per synced event with a new location, forever.
     */
    public function test_calendar_import_accepts_onto_the_stub_it_just_created(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($curator);

        $this->assertTrue($this->locationVenueSyncer()->run($event, $curator, 'The Anchor, Haifa'));

        $venue = \App\Models\Role::where('type', 'venue')->sole();

        $pivot = \Illuminate\Support\Facades\DB::table('event_role')
            ->where('event_id', $event->id)
            ->where('role_id', $venue->id)
            ->first();

        $this->assertEquals(1, $pivot->is_accepted,
            "the user's own imported event belongs on the venue the import made for them");
    }

    /**
     * The same rule for an UNCLAIMED venue somebody else's import left behind, which this user
     * merely follows. Role::isEditableBy already says a follower may edit an unclaimed role, so
     * their own event belongs on it - and pending would be a dead end, because accept() requires
     * isEditor() and an unclaimed venue has no owner or admin to satisfy it.
     */
    public function test_calendar_import_accepts_onto_an_unclaimed_venue_the_user_follows(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);

        // A stub somebody else's import left behind: it carries THEIR user_id (so the ownerless
        // rule does not fire) but was never verified, so it is unclaimed and has no owner or
        // admin. This user only follows it.
        $stranger = $this->createOwner();
        $venue = $this->createRole($stranger, 'venue', ['name' => 'Barby', 'email_verified_at' => null]);
        DB::table('role_user')->where('role_id', $venue->id)->update(['level' => 'follower']);
        $this->followRole($owner, $venue);

        $event = $this->createEvent($curator);
        $this->locationVenueSyncer()->run($event, $curator, 'Barby');

        $pivot = DB::table('event_role')
            ->where('event_id', $event->id)
            ->where('role_id', $venue->id)
            ->first();

        $this->assertEquals(1, $pivot->is_accepted,
            'pending here is a dead end: no owner or admin exists to accept it');
    }

    /** The other half of the same rule: a venue the user is a member of still auto-accepts. */
    public function test_calendar_import_auto_accepts_onto_the_users_own_venue(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue', ['name' => 'Barby']);

        $event = $this->createEvent($curator);
        $this->locationVenueSyncer()->run($event, $curator, 'Barby');

        $pivot = \Illuminate\Support\Facades\DB::table('event_role')
            ->where('event_id', $event->id)
            ->where('role_id', $venue->id)
            ->first();

        $this->assertEquals(1, $pivot->is_accepted);
    }

    public function test_save_youtube_video_for_talent(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        // Unclaimed: a curator may only attach videos to acts that do not run their own schedule.
        $talent = $this->createRole($owner, 'talent', [
            'email_verified_at' => null,
            'youtube_links' => json_encode([]),
        ]);

        $event = $this->createEvent($curator);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($owner)->post(route('role.save_video', ['subdomain' => $curator->subdomain]), [
            'role_id' => $talent->id,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_title' => 'Live Set',
        ]);

        $links = json_decode($talent->fresh()->youtube_links, true) ?? [];
        $this->assertNotEmpty($links);
        $this->assertStringContainsString('dQw4w9WgXcQ', json_encode($links));
    }

    public function test_a_curator_cannot_attach_a_video_to_a_claimed_talent(): void
    {
        // Otherwise the matcher can create a video the curator has no way to remove: both
        // canRemoveVideo and clearVideos refuse claimed targets, so writes would reach a set
        // removals cannot.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $talent = $this->createRole($this->createOwner(), 'talent', ['youtube_links' => json_encode([])]);

        $this->assertTrue($talent->isClaimed());

        $event = $this->createEvent($curator);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($owner)->post(route('role.save_video', ['subdomain' => $curator->subdomain]), [
            'role_id' => $talent->id,
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_title' => 'Live Set',
        ])->assertStatus(403);

        $this->assertSame([], json_decode($talent->fresh()->youtube_links, true));
    }

    public function test_the_video_matcher_does_not_offer_claimed_talents(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $claimed = $this->createRole($this->createOwner(), 'talent', ['name' => 'Claimed Act']);
        $unclaimed = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'Unclaimed Act',
            'email_verified_at' => null,
        ]);

        $event = $this->createEvent($curator);
        $event->roles()->attach($claimed->id, ['is_accepted' => true]);
        $event->roles()->attach($unclaimed->id, ['is_accepted' => true]);

        $names = collect(
            $this->actingAs($owner)
                ->get(route('role.talent_roles_without_videos', ['subdomain' => $curator->subdomain]))
                ->assertOk()
                ->json()
        )->pluck('name');

        $this->assertContains('Unclaimed Act', $names);
        $this->assertNotContains('Claimed Act', $names);
    }

    /**
     * Two videos on an unclaimed talent, one of which is broken.
     */
    private function talentWithTwoVideos(\App\Models\User $talentOwner, array $attrs = []): \App\Models\Role
    {
        return $this->createRole($talentOwner, 'talent', $attrs + [
            'youtube_links' => json_encode([
                ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'title' => 'Broken', 'type' => 'youtube'],
                ['name' => 'Good one', 'url' => 'https://www.youtube.com/watch?v=oHg5SJYRHA0', 'thumbnail_url' => 'https://i.ytimg.com/x.jpg'],
            ]),
        ]);
    }

    public function test_curator_editor_removes_only_the_named_video_from_an_unclaimed_talent(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');

        // A different owner, and unverified, so the talent is unclaimed and $owner is not on its team.
        $talent = $this->talentWithTwoVideos($this->createOwner(), ['email_verified_at' => null]);

        $event = $this->createEvent($curator);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($owner)->post(route('role.remove_video', ['subdomain' => $curator->subdomain]), [
            'role_hash' => UrlUtils::encodeId($talent->id),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $links = json_decode($talent->fresh()->youtube_links, true);

        $this->assertCount(1, $links);
        $this->assertStringNotContainsString('dQw4w9WgXcQ', json_encode($links));
        // The survivor keeps the keys the schedule editor wrote, not a rebuilt subset.
        $this->assertSame('Good one', $links[0]['name']);
        $this->assertSame('https://i.ytimg.com/x.jpg', $links[0]['thumbnail_url']);
    }

    public function test_removing_a_video_is_audited_where_the_actor_can_see_it(): void
    {
        // Filed against the target instead, the row lands on an unclaimed act with no team and no
        // reachable admin panel, so the trail exists in the database and nowhere else.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $talent = $this->talentWithTwoVideos($this->createOwner(), ['email_verified_at' => null]);

        $event = $this->createEvent($curator);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($owner)->post(route('role.remove_video', ['subdomain' => $curator->subdomain]), [
            'role_hash' => UrlUtils::encodeId($talent->id),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditService::SCHEDULE_VIDEO_REMOVE,
            'model_type' => 'Role',
            'model_id' => $curator->id,
        ]);

        // And it actually reaches the page, which is the whole point.
        $this->actingAs($owner)
            ->get(route('role.audit_log', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee($talent->name);
    }

    public function test_removing_the_last_video_writes_null_so_the_matcher_offers_the_talent_again(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $talent = $this->createRole($this->createOwner(), 'talent', [
            'email_verified_at' => null,
            'youtube_links' => json_encode([
                ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'title' => 'Broken', 'type' => 'youtube'],
            ]),
        ]);

        $event = $this->createEvent($curator);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($owner)->post(route('role.remove_video', ['subdomain' => $curator->subdomain]), [
            'role_hash' => UrlUtils::encodeId($talent->id),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        // Not '[]' - that is the Skip tombstone, and it would blacklist the act permanently.
        $this->assertNull($talent->fresh()->youtube_links);
    }

    public function test_curator_cannot_remove_videos_from_a_claimed_talent_it_does_not_manage(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        // Claimed (createRole verifies by default) and owned by someone else.
        $talent = $this->talentWithTwoVideos($this->createOwner());

        $event = $this->createEvent($curator);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($owner)->post(route('role.remove_video', ['subdomain' => $curator->subdomain]), [
            'role_hash' => UrlUtils::encodeId($talent->id),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])
            ->assertSessionHas('error');

        $this->assertCount(2, json_decode($talent->fresh()->youtube_links, true));
    }

    public function test_curator_cannot_remove_videos_from_a_talent_with_no_shared_accepted_event(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $talent = $this->talentWithTwoVideos($this->createOwner(), ['email_verified_at' => null]);

        // An event exists, but the talent is not on it.
        $this->createEvent($curator);

        $this->actingAs($owner)->post(route('role.remove_video', ['subdomain' => $curator->subdomain]), [
            'role_hash' => UrlUtils::encodeId($talent->id),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])
            ->assertSessionHas('error');

        $this->assertCount(2, json_decode($talent->fresh()->youtube_links, true));
    }

    public function test_a_schedule_editor_can_remove_a_video_from_their_own_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->talentWithTwoVideos($owner, ['type' => 'venue']);

        $this->actingAs($owner)->post(route('role.remove_video', ['subdomain' => $role->subdomain]), [
            'role_hash' => UrlUtils::encodeId($role->id),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->assertCount(1, json_decode($role->fresh()->youtube_links, true));
    }

    public function test_a_signed_in_stranger_cannot_remove_a_video(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $talent = $this->talentWithTwoVideos($this->createOwner(), ['email_verified_at' => null]);

        $event = $this->createEvent($curator);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->actingAs($this->createOwner())->post(route('role.remove_video', ['subdomain' => $curator->subdomain]), [
            'role_hash' => UrlUtils::encodeId($talent->id),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])
            ->assertSessionHas('error');

        $this->assertCount(2, json_decode($talent->fresh()->youtube_links, true));
    }

    public function test_videos_tab_renders_a_well_formed_vue_template(): void
    {
        // The tab mounts a Vue app and this project uses the full build, so the server-rendered
        // HTML *is* the template. A double quote inside an attribute value - which @json() emits -
        // terminates the attribute, and the parser then sprays the remainder as bogus attributes
        // (classically one named literally ':'). Vue's compiler throws, mount() empties the
        // container, and the tab renders blank with nothing logged server-side.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');

        $html = $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $curator->subdomain, 'tab' => 'videos']))
            ->assertOk()
            ->getContent();

        // Checked against the raw source, not a parsed DOM: libxml is far more forgiving than a
        // browser's HTML5 parser and quietly discards the broken attributes instead of surfacing
        // them, so a DOM-based assertion passes even on markup that blanks the page.
        //
        // The fingerprint is an attribute that opens and closes immediately and is then followed by
        // more content - `:aria-label=""Preview video""` - which is what serialising JSON into an
        // attribute produces. A genuinely empty attribute (alt="") is followed by whitespace, > or /.
        preg_match_all('/\S+=""[^\s>\/]/', $html, $matches);

        $this->assertSame(
            [],
            $matches[0],
            'An attribute value closed early; this breaks the Vue mount and blanks the tab: '.implode(', ', $matches[0])
        );

        // And prove the template we care about actually reached the page, so the assertion above
        // cannot pass by rendering nothing at all.
        $this->assertStringContainsString('id="videos-app"', $html);
        $this->assertStringContainsString('previewVideo(video)', $html);
    }

    public function test_owner_can_view_audit_log(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // Simulate a logged action via the real audit path.
        AuditService::log('category.added', $owner->id, 'Role', $role->id, null, ['name' => 'New Category']);

        $this->assertDatabaseHas('audit_logs', ['model_type' => 'Role', 'model_id' => $role->id]);

        $this->actingAs($owner)->get(route('role.audit_log', ['subdomain' => $role->subdomain]))
            ->assertOk();
    }

    public function test_white_label_hides_branding_for_pro(): void
    {
        $owner = $this->createOwner();
        $freeRole = $this->createRole($owner, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);
        $proRole = $this->createRole($owner); // enterprise by default

        // Free tier shows the "Powered by" branding; Pro/Enterprise is white-labeled.
        $this->assertTrue($freeRole->showBranding());
        $this->assertFalse($proRole->showBranding());

        // The guest event page footer shows branding only for the free schedule.
        $freeEvent = $this->createEvent($freeRole);
        $proEvent = $this->createEvent($proRole);
        $this->get($this->guestEventUrl($freeRole, $freeEvent))->assertOk()->assertSee('Invoice Ninja');
        $this->get($this->guestEventUrl($proRole, $proEvent))->assertOk()->assertDontSee('Invoice Ninja');
    }

    public function test_guest_event_submission(): void
    {
        $owner = $this->createOwner();
        // roles.require_account defaults to 1, and the guest_import.store validator then demands
        // account_name/email/password. This test covers the no-account guest path, so opt out of it.
        $role = $this->createRole($owner, 'venue', ['accept_requests' => true, 'require_account' => false]);

        $this->post(route('event.guest_import.store', ['subdomain' => $role->subdomain]), [
            'name' => 'Guest Submitted Event',
            'description' => 'From a guest',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
        ]);

        $event = Event::where('name', 'Guest Submitted Event')->first();
        $this->assertNotNull($event);
        $this->assertTrue($role->events()->where('events.id', $event->id)->exists());
    }

    public function test_guest_submit_requires_valid_sub_schedule_when_configured_required(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, [
            'accept_requests' => true,
            'require_account' => true,
            'import_config' => ['required_fields' => ['group_id' => true]],
        ]);
        $group = $this->createGroup($curator);

        $payload = [
            'name' => 'Sneaky Event',
            'description' => 'From a guest',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
        ];
        $route = route('event.guest_import.store', ['subdomain' => $curator->subdomain]);

        // A crafted, non-empty but unresolvable id satisfies the presence-only 'required' rule,
        // yet must be rejected so the event can't be filed uncategorized past the requirement.
        $this->postJson($route, $payload + ['curator_group_id' => 'zzzz'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('curator_group_id');

        $this->assertNull(Event::where('name', 'Sneaky Event')->first());

        // A real sub-schedule id clears the sub-schedule gate (any remaining 422 is for the
        // account fields this no-account request omits, not for curator_group_id).
        $this->postJson($route, $payload + ['curator_group_id' => UrlUtils::encodeId($group->id)])
            ->assertJsonMissingValidationErrors('curator_group_id');
    }

    public function test_fix_events_timezone_relabels_events_and_keeps_wall_clock(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['timezone' => 'Asia/Jerusalem']);

        $startsAt = now()->addDays(30)->setTime(20, 0)->format('Y-m-d H:i:s');
        $event = $this->createEvent($curator, [
            'timezone' => 'America/New_York',
            'starts_at' => $startsAt,
            'is_private' => false,
            'description' => 'Body copy',
        ]);

        // Precondition: the event is off-timezone for the schedule, and its markdown was rendered.
        $this->assertTrue($event->isOffTimezoneFor($curator));
        $descriptionHtml = $event->description_html;
        $this->assertNotEmpty($descriptionHtml);

        $this->actingAs($owner)
            ->post(route('role.timezone_warning_fix_events', ['subdomain' => $curator->subdomain]), [
                'timezone' => 'Asia/Jerusalem',
            ])
            ->assertRedirect();

        $event->refresh();

        // Relabeled to the schedule timezone, with the wall-clock start time left intact.
        $this->assertEquals('Asia/Jerusalem', $event->timezone);
        $this->assertDatabaseHas('events', ['id' => $event->id, 'starts_at' => $startsAt]);
        $this->assertFalse($event->isOffTimezoneFor($curator->fresh()));
        // The full row was re-fetched before saving, so the saving hook did not wipe *_html.
        $this->assertEquals($descriptionHtml, $event->description_html);
    }

    public function test_fix_events_timezone_requires_editor(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['timezone' => 'Asia/Jerusalem']);
        $event = $this->createEvent($curator, [
            'timezone' => 'America/New_York',
            'starts_at' => now()->addDays(30)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);

        $outsider = $this->createOwner(); // authenticated, but not a member of the curator

        $this->actingAs($outsider)
            ->post(route('role.timezone_warning_fix_events', ['subdomain' => $curator->subdomain]), [
                'timezone' => 'Asia/Jerusalem',
            ]);

        // Non-editor: the event's timezone must be left untouched.
        $this->assertEquals('America/New_York', $event->fresh()->timezone);
    }

    public function test_fix_events_timezone_ignores_stale_posted_timezone(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['timezone' => 'Asia/Jerusalem']);
        $event = $this->createEvent($curator, [
            'timezone' => 'America/New_York',
            'starts_at' => now()->addDays(30)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);

        // Posted timezone no longer matches the schedule's own timezone -> reject, change nothing.
        $this->actingAs($owner)
            ->post(route('role.timezone_warning_fix_events', ['subdomain' => $curator->subdomain]), [
                'timezone' => 'Europe/London',
            ]);

        $this->assertEquals('America/New_York', $event->fresh()->timezone);
    }
}
