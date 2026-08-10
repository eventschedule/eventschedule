<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\RoleSource;
use App\Services\CuratorSourceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Curator event sources: a curator lists talent/venue schedules and every one of their
 * events - past and future - is linked onto the curator automatically.
 *
 * The feature materialises event_role rows rather than teaching ~35 read sites about
 * sources, so most of what matters is that the two reconcile queries in
 * CuratorSourceService produce exactly the right set of rows.
 */
class CuratorSourcesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function service(): CuratorSourceService
    {
        return app(CuratorSourceService::class);
    }

    /**
     * An unclaimed, contact-less venue - what an import or a calendar sync leaves behind, and
     * the only shape the merge tool will destroy (canMergeRoles refuses a claimed source).
     */
    protected function stubVenue(string $name, array $attrs = []): Role
    {
        $venue = new Role;
        $venue->subdomain = 'stub'.strtolower(\Illuminate\Support\Str::random(10));
        $venue->type = 'venue';
        $venue->name = $name;
        $venue->email = null;
        $venue->phone = null;

        foreach ($attrs as $key => $value) {
            $venue->{$key} = $value;
        }

        $venue->save();

        return $venue->fresh();
    }

    protected function addSource(Role $curator, Role $source, $group = null): RoleSource
    {
        return RoleSource::create([
            'role_id' => $curator->id,
            'source_role_id' => $source->id,
            'group_id' => $group?->id,
        ]);
    }

    /** Event ids currently linked to the schedule and visible on its calendar. */
    protected function acceptedEventIds(Role $role): array
    {
        return DB::table('event_role')
            ->where('role_id', $role->id)
            ->where('is_accepted', true)
            ->pluck('event_id')
            ->sort()
            ->values()
            ->all();
    }

    protected function pivot(Role $role, Event $event): ?object
    {
        return DB::table('event_role')
            ->where('role_id', $role->id)
            ->where('event_id', $event->id)
            ->first();
    }

    /**
     * A curator may source a VENUE, and venues are what the merge tool merges. performMerge()
     * only soft-deletes the source, so role_sources' onDelete('cascade') never fires - and
     * applyEligibility() requires source_role.is_deleted = false, so the next reconcile used to
     * delete every auto-sourced row the venue was feeding and add nothing back.
     */
    public function test_merging_a_sourced_venue_keeps_the_curators_events(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);

        $survivor = $this->createRole($owner, 'venue', ['name' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $duplicate = $this->stubVenue('Ozen Bar', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $duplicate);

        // The stub is unclaimed (user_id null), so the event carries the account owner.
        $event = $this->createEvent($duplicate, ['user_id' => $owner->id]);

        $this->addSource($curator, $duplicate);
        $this->service()->reconcile($curator);

        $this->assertSame([$event->id], $this->acceptedEventIds($curator), 'precondition: the curator has the event');

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => $survivor->id,
            'source_ids' => [$duplicate->id],
        ])->assertRedirect();

        // The source now points at the survivor, so the event survives the reconcile that the
        // scheduler runs every five minutes.
        $this->service()->reconcile();

        $this->assertSame([$event->id], $this->acceptedEventIds($curator));
        $this->assertDatabaseHas('role_sources', [
            'role_id' => $curator->id,
            'source_role_id' => $survivor->id,
        ]);
    }

    /** The unique is (role_id, source_role_id), so a curator already on both sides must not collide. */
    public function test_merging_a_venue_a_curator_already_sources_drops_the_duplicate_row(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);

        $survivor = $this->createRole($owner, 'venue', ['name' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $duplicate = $this->stubVenue('Ozen Bar', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $duplicate);

        $event = $this->createEvent($duplicate, ['user_id' => $owner->id]);

        $this->addSource($curator, $survivor);
        $this->addSource($curator, $duplicate);
        $this->service()->reconcile($curator);

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => $survivor->id,
            'source_ids' => [$duplicate->id],
        ])->assertRedirect();

        $this->service()->reconcile();

        $this->assertSame(1, RoleSource::where('role_id', $curator->id)->count());
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));
    }

    public function test_adding_a_source_pulls_in_past_and_future_events(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);

        $past = $this->createEvent($venue, [
            'name' => 'Last Year',
            'starts_at' => Carbon::now()->subYear()->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $future = $this->createEvent($venue, ['name' => 'Next Week']);

        $this->assertSame([], $this->acceptedEventIds($curator));

        $this->addSource($curator, $venue);
        $result = $this->service()->reconcile($curator);

        $this->assertSame(2, $result['added']);
        $this->assertEqualsCanonicalizing(
            [$past->id, $future->id],
            $this->acceptedEventIds($curator),
            'both the past and the future event should land on the curator'
        );

        foreach ([$past, $future] as $event) {
            $this->assertEquals(1, $this->pivot($curator, $event)->is_auto_sourced);
        }
    }

    public function test_reconcile_is_idempotent(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->assertSame(1, $this->service()->reconcile($curator)['added']);

        $second = $this->service()->reconcile($curator);
        $this->assertSame(['added' => 0, 'removed' => 0], $second);
        $this->assertCount(1, $this->acceptedEventIds($curator));
    }

    public function test_only_events_the_source_itself_publishes_are_pulled(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);

        $accepted = $this->createEvent($venue, ['name' => 'Accepted']);
        $declined = $this->createEvent($venue, ['name' => 'Declined', 'is_accepted' => false]);

        // createEvent()'s `$attrs['is_accepted'] ?? true` cannot express a null pivot
        // (?? treats null as absent), so set the pending state directly.
        $pending = $this->createEvent($venue, ['name' => 'Pending']);
        $venue->events()->updateExistingPivot($pending->id, ['is_accepted' => null]);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);

        $this->assertSame([$accepted->id], $this->acceptedEventIds($curator));
        $this->assertNull($this->pivot($curator, $pending));
        $this->assertNull($this->pivot($curator, $declined));
    }

    public function test_draft_internal_and_unlisted_events_are_not_pulled(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);

        $public = $this->createEvent($venue, ['name' => 'Public']);
        $draft = $this->createEvent($venue, ['name' => 'Draft', 'is_draft' => true]);
        $internal = $this->createEvent($venue, ['name' => 'Internal', 'is_draft' => true, 'is_internal' => true]);
        $unlisted = $this->createEvent($venue, ['name' => 'Unlisted', 'is_private' => true]);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);

        $this->assertSame([$public->id], $this->acceptedEventIds($curator));

        foreach ([$draft, $internal, $unlisted] as $hidden) {
            $this->assertNull($this->pivot($curator, $hidden), "{$hidden->name} should not be pulled in");
        }
    }

    public function test_publishing_a_draft_pulls_it_in_and_re_drafting_removes_it(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue, ['is_draft' => true]);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $this->assertSame([], $this->acceptedEventIds($curator));

        $event->is_draft = false;
        $event->save();
        $this->assertSame(1, $this->service()->reconcile($curator)['added']);
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));

        $event->is_draft = true;
        $event->save();
        $this->assertSame(1, $this->service()->reconcile($curator)['removed']);
        $this->assertSame([], $this->acceptedEventIds($curator));
    }

    public function test_removing_a_source_removes_its_events_but_keeps_hand_curated_ones(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);

        $sourced = $this->createEvent($venue, ['name' => 'Sourced']);
        $byHand = $this->createEvent($venue, ['name' => 'By hand']);

        // Curated before the source existed, so it carries no auto marker.
        $curator->events()->attach($byHand->id, ['is_accepted' => true]);

        $source = $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $this->assertEqualsCanonicalizing([$sourced->id, $byHand->id], $this->acceptedEventIds($curator));
        $this->assertEquals(0, $this->pivot($curator, $byHand)->is_auto_sourced);

        $source->delete();
        $this->service()->reconcile($curator);

        $this->assertSame([$byHand->id], $this->acceptedEventIds($curator));
    }

    public function test_an_event_covered_by_two_sources_survives_removing_one(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $talent = $this->createRole($owner, 'talent');
        $curator = $this->createCurator($owner);

        $shared = $this->createEvent($venue, ['name' => 'Shared']);
        $shared->roles()->attach($talent->id, ['is_accepted' => true]);
        $venueOnly = $this->createEvent($venue, ['name' => 'Venue only']);

        $venueSource = $this->addSource($curator, $venue);
        $this->addSource($curator, $talent);
        $this->service()->reconcile($curator);
        $this->assertEqualsCanonicalizing([$shared->id, $venueOnly->id], $this->acceptedEventIds($curator));

        $venueSource->delete();
        $this->service()->reconcile($curator);

        $this->assertSame([$shared->id], $this->acceptedEventIds($curator),
            'the shared event is still covered by the talent source and must stay');
    }

    public function test_a_declined_row_is_never_resurrected(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));

        // What Uncurate leaves behind on an auto-sourced row.
        $curator->events()->updateExistingPivot($event->id, ['is_accepted' => false]);

        $this->service()->reconcile($curator);

        $this->assertSame([], $this->acceptedEventIds($curator));
        $this->assertEquals(0, $this->pivot($curator, $event)->is_accepted,
            'the tombstone must survive so the event does not come back');
    }

    public function test_the_source_sub_schedule_is_applied_and_can_be_changed(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $gigs = $this->createGroup($curator, ['name' => 'Gigs']);
        $talks = $this->createGroup($curator, ['name' => 'Talks']);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue, $gigs);
        $this->service()->reconcile($curator);

        $this->assertEquals($gigs->id, $this->pivot($curator, $event)->group_id);

        $this->service()->refileSource($curator, $venue->id, $talks->id);
        $this->assertEquals($talks->id, $this->pivot($curator, $event)->group_id);
    }

    /**
     * Roles are retired with an is_deleted flag as well as by real deletion - unfollowing an
     * unclaimed schedule that has no other followers sets it. The FK cascade never fires for
     * those, so the reconcile has to check the flag itself.
     */
    public function test_a_soft_deleted_source_stops_feeding_events(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));

        $venue->is_deleted = true;
        $venue->save();

        $this->assertSame(1, $this->service()->reconcile()['removed']);
        $this->assertSame([], $this->acceptedEventIds($curator));

        // And a new event on the retired source is never picked up.
        $this->createEvent($venue, ['name' => 'After retirement']);
        $this->service()->reconcile();
        $this->assertSame([], $this->acceptedEventIds($curator));
    }

    public function test_a_soft_deleted_curator_is_not_reconciled(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $curator->is_deleted = true;
        $curator->save();

        $this->assertSame(['added' => 0, 'removed' => 0], $this->service()->reconcile());
        $this->assertSame([], $this->acceptedEventIds($curator));
    }

    public function test_sources_do_not_leak_between_curators(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $subscribed = $this->createCurator($owner, ['name' => 'Subscribed']);
        $other = $this->createCurator($owner, ['name' => 'Other']);
        $event = $this->createEvent($venue);

        $this->addSource($subscribed, $venue);
        $this->service()->reconcile();

        $this->assertSame([$event->id], $this->acceptedEventIds($subscribed));
        $this->assertSame([], $this->acceptedEventIds($other));
    }

    public function test_a_new_event_reaches_the_curator_without_running_the_reconcile_command(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $this->addSource($curator, $venue);

        $this->postCreateEvent($owner, $venue, ['name' => 'Straight through']);
        $event = $this->latestEvent();

        $this->assertSame([$event->id], $this->acceptedEventIds($curator),
            'the EventRepo::saveEvent hook should link it immediately');
    }

    public function test_saving_an_event_does_not_drop_its_auto_sourced_rows(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        // Co-owned, so the curator IS visible to the saving user - the case where
        // roles()->sync() detaches it and the preservation block does not help.
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));

        // No curators[] and no marker: exactly what the API and the importers send.
        $this->putUpdateEvent($owner, $venue, $event, ['name' => 'Renamed']);

        $this->assertSame([$event->id], $this->acceptedEventIds($curator),
            'a programmatic save must not knock the event off the curator');
    }

    public function test_unticking_the_curator_on_the_event_form_sticks(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));

        // The form renders the marker and lists only the venue as ticked.
        $this->putUpdateEvent($owner, $venue, $event, [
            'curators_submitted' => 1,
            'curators' => [$venue->encodeId()],
        ]);

        $this->assertSame([], $this->acceptedEventIds($curator));

        // It must survive the reconcile pass...
        $this->service()->reconcile();
        $this->assertSame([], $this->acceptedEventIds($curator));

        // ...and a later save that never rendered the schedules tab.
        $this->putUpdateEvent($owner, $venue, $event, ['name' => 'Renamed again']);
        $this->assertSame([], $this->acceptedEventIds($curator),
            'a programmatic save must not resurrect a curator the user removed');
    }

    /**
     * The event form leaves the schedule being edited out of its "Add to schedules" list
     * (event/edit.blade.php filters $schedules by subdomain), so the curator is absent from
     * curators[] on every save made from its own page. Reading that as an untick dropped the
     * event off the very curator being edited.
     */
    public function test_saving_a_sourced_event_from_the_curators_own_form_keeps_it(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));

        // Editing on /{curator}/edit-event/{hash}: the venue field re-selects the venue (the
        // dedicated section is authoritative for it), and the schedules tab renders with the
        // venue ticked but not the curator, which is never listed on its own page.
        $this->putUpdateEvent($owner, $curator, $event, [
            'venue_id' => $venue->encodeId(),
            'venue_submitted' => 1,
            'curators_submitted' => 1,
            'curators' => [$venue->encodeId()],
        ]);

        $this->assertSame([$event->id], $this->acceptedEventIds($curator),
            'the curator being edited is not on its own schedules tab, so it was never unticked');

        $this->service()->reconcile();
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));
    }

    /**
     * A removal has to be reversible. Ticking the curator back on has to actually put the event
     * back, or the only way out is deleting and re-adding the whole source, which relinks
     * everything.
     */
    public function test_re_ticking_a_removed_curator_puts_the_event_back(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);

        $ticked = ['venue_id' => $venue->encodeId(), 'venue_submitted' => 1, 'curators_submitted' => 1];

        // Untick it.
        $this->putUpdateEvent($owner, $venue, $event, $ticked + ['curators' => []]);
        $this->assertSame([], $this->acceptedEventIds($curator));

        // Tick it back on.
        $this->putUpdateEvent($owner, $venue, $event, $ticked + ['curators' => [$curator->encodeId()]]);

        $this->assertSame([$event->id], $this->acceptedEventIds($curator),
            're-ticking the curator must undo the removal');

        $this->service()->reconcile();
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));
    }

    /**
     * The docs promise a removed event "stays gone even though the source is still connected".
     * The source owner unpublishing and republishing must not quietly undo it.
     */
    public function test_a_removal_survives_the_source_unpublishing_and_republishing(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $curator->events()->updateExistingPivot($event->id, ['is_accepted' => false]);

        $event->is_draft = true;
        $event->save();
        $this->service()->reconcile();

        $event->is_draft = false;
        $event->save();
        $this->service()->reconcile();

        $this->assertSame([], $this->acceptedEventIds($curator),
            'the event came back on a schedule that had removed it');
    }

    /** Retiring a curator must not destroy the per-event removals it had made. */
    public function test_soft_deleting_a_curator_leaves_its_links_alone(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $kept = $this->createEvent($venue, ['name' => 'Kept']);
        $removed = $this->createEvent($venue, ['name' => 'Removed']);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $curator->events()->updateExistingPivot($removed->id, ['is_accepted' => false]);

        $curator->is_deleted = true;
        $curator->save();
        $this->assertSame(0, $this->service()->reconcile()['removed'],
            'a retired curator should be skipped, not stripped');

        $curator->is_deleted = false;
        $curator->save();
        $this->service()->reconcile();

        $this->assertSame([$kept->id], $this->acceptedEventIds($curator));
        $this->assertEquals(0, $this->pivot($curator, $removed)->is_accepted,
            'the removal has to survive the round trip');
    }

    /**
     * The checkbox has to tell the truth, or a removal looks like it did not happen and the only
     * way to undo it (ticking the box) is invisible.
     */
    public function test_the_event_form_does_not_tick_a_schedule_that_declined_the_event(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner, ['name' => 'Aggregator']);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);

        $checkbox = 'id="curator_'.$curator->encodeId().'"';

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $venue->subdomain, 'hash' => $event->hashedId()]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($checkbox, $html, 'the curator should be listed at all');
        $this->assertMatchesRegularExpression('/'.preg_quote($checkbox, '/').'[^>]*checked/', $html);

        // Remove it, then re-render.
        $curator->events()->updateExistingPivot($event->id, ['is_accepted' => false]);

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $venue->subdomain, 'hash' => $event->hashedId()]))
            ->assertOk()
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/'.preg_quote($checkbox, '/').'[^>]*checked/', $html,
            'a declined schedule must render unticked');
    }

    public function test_uncurate_tombstones_an_auto_sourced_event_instead_of_detaching(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);

        $this->actingAs($owner)->delete(route('event.uncurate', [
            'subdomain' => $curator->subdomain,
            'hash' => $event->hashedId(),
        ]));

        $this->assertSame([], $this->acceptedEventIds($curator));

        $this->service()->reconcile();
        $this->assertSame([], $this->acceptedEventIds($curator),
            'the reconcile pass must not undo Uncurate');
    }

    /**
     * The realistic shape: the curator belongs to SOMEBODY ELSE, so it is not in the venue
     * owner's availableEventSchedules() and never appears on their schedules tab.
     *
     * saveEvent's preservation loop appends such attached-but-invisible schedules to
     * $selectedCurators so a save cannot silently detach them, and syncCuratorSources() then read
     * that same mutated array to decide whether the user had re-ticked the curator. It always
     * looked re-ticked, so the tombstone was skipped - and the accept loop had already flipped
     * the row back to accepted, putting the event back on the curator's public page against
     * their explicit removal, permanently.
     */
    public function test_a_removal_survives_the_event_owner_saving_the_form(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        $event = $this->createEvent($venue);

        // A curator owned by someone else, taking submissions without approval.
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner, ['accept_requests' => true, 'require_approval' => false]);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);
        $this->assertSame([$event->id], $this->acceptedEventIds($curator));

        $this->actingAs($curatorOwner)->delete(route('event.uncurate', [
            'subdomain' => $curator->subdomain,
            'hash' => $event->hashedId(),
        ]));
        $this->assertSame([], $this->acceptedEventIds($curator), 'precondition: the curator removed it');

        // The venue owner edits their own event. They cannot see that curator at all, but the
        // form posts the marker, so this is the "the tab was rendered" path.
        $this->putUpdateEvent($venueOwner, $venue, $event, ['curators_submitted' => 1]);

        $this->assertSame([], $this->acceptedEventIds($curator),
            'a save by the event owner must not undo the curator removal');

        $this->service()->reconcile();
        $this->assertSame([], $this->acceptedEventIds($curator),
            'nor may the next reconcile pass');
    }

    /**
     * saveEventSources() refuses a curator as a source, because a curator pulling from a curator
     * chains one aggregation onto another. But `type` is fillable and RoleController::update()
     * mass-fills the request, so a talent could be flipped to a curator afterwards while its
     * role_sources rows survived - and the reconcile queries only ever re-checked the type on the
     * curator side, never on the source.
     */
    public function test_a_source_flipped_to_a_curator_stops_feeding_its_parent(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $parent = $this->createCurator($owner);

        $event = $this->createEvent($talent);

        $this->addSource($parent, $talent);
        $this->service()->reconcile($parent);
        $this->assertSame([$event->id], $this->acceptedEventIds($parent));

        $talent->type = 'curator';
        $talent->save();

        $this->service()->reconcile();

        $this->assertSame([], $this->acceptedEventIds($parent),
            'a source that is now a curator must not chain its own aggregation upwards');
    }

    /**
     * A programmatic save (the API sends no curators[]) detaches a visible curator, and
     * linkMissing() rebuilds the row from five columns only - so everything else on the pivot was
     * silently reset, including translations that had already been bought from the AI provider
     * and the CalDAV keys used for dedup.
     */
    public function test_a_programmatic_save_keeps_the_curator_pivots_translations(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);

        DB::table('event_role')
            ->where('event_id', $event->id)
            ->where('role_id', $curator->id)
            ->update([
                'name_translated' => 'Nombre traducido',
                'description_translated' => 'Descripcion traducida',
                'caldav_event_uid' => 'uid-123',
            ]);

        // The AP form path, with the schedules tab never rendered - the same shape the API sends.
        // Same name on purpose: renaming an event legitimately invalidates its translations, and
        // this is about a save that changes nothing losing them anyway.
        $this->putUpdateEvent($owner, $venue, $event, ['name' => $event->name]);

        $pivot = $this->pivot($curator, $event);

        $this->assertSame('Nombre traducido', $pivot->name_translated, 'a paid translation must survive a save');
        $this->assertSame('Descripcion traducida', $pivot->description_translated);
        $this->assertSame('uid-123', $pivot->caldav_event_uid);
    }

    public function test_uncurate_still_detaches_a_hand_curated_event(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($venue);
        $curator->events()->attach($event->id, ['is_accepted' => true]);

        $this->actingAs($owner)->delete(route('event.uncurate', [
            'subdomain' => $curator->subdomain,
            'hash' => $event->hashedId(),
        ]));

        $this->assertNull($this->pivot($curator, $event),
            'without a source in play the old detach behaviour is unchanged');
    }

    public function test_sourced_events_reach_the_curator_graphics_query(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);
        $this->createEvent($venue, [
            'name' => 'Past',
            'starts_at' => Carbon::now()->subMonth()->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $upcoming = $this->createEvent($venue, ['name' => 'Upcoming']);

        $this->addSource($curator, $venue);
        $this->service()->reconcile($curator);

        // Mirrors GraphicController::generateGraphicData's base query.
        $found = Event::whereHas('roles', function ($query) use ($curator) {
            $query->where('role_id', $curator->id)->where('is_accepted', true);
        })
            ->upcomingOrOngoing()
            ->where('is_private', false)
            ->where('is_draft', false)
            ->where('is_cancelled', false)
            ->whereNull('event_password')
            ->pluck('events.id')
            ->all();

        $this->assertSame([$upcoming->id], $found);
    }
}
