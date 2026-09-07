<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Role;
use App\Models\RoleSource;
use App\Services\BackupService;
use App\Services\CuratorSourceService;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Event Sources section on the schedule edit page, and the role.update handling
 * behind it.
 */
class CuratorSourcesUiTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The role.update payload needs the required fields plus whatever we are testing.
     *
     * groups[] has to be resubmitted the way the real form does: update() deletes every
     * sub-schedule missing from it, and role_sources.group_id is ON DELETE SET NULL.
     */
    protected function updatePayload(Role $role, array $overrides = []): array
    {
        $groups = [];
        foreach ($role->groups()->get() as $group) {
            $groups[$group->id] = ['name' => $group->name, 'slug' => $group->slug];
        }

        return array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => $role->language_code ?: 'en',
            'new_subdomain' => $role->subdomain,
            'groups' => $groups,
            'source_schedules_submitted' => 1,
        ], $overrides);
    }

    protected function putRole(Role $role, array $overrides = [])
    {
        return $this->actingAs($role->user)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->updatePayload($role, $overrides)
        );
    }

    public function test_the_sources_section_renders_only_for_curators(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee('id="section-sources"', false)
            ->assertSee('name="source_schedules_submitted"', false)
            ->assertSee(__('messages.event_sources'));

        // Not 'section-sources' on its own: HelpUtils::getAnchorMap() is inlined on every
        // AP page, so that string is present regardless.
        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $venue->subdomain]))
            ->assertOk()
            ->assertDontSee('id="section-sources"', false)
            ->assertDontSee('name="source_schedules_submitted"', false);
    }

    public function test_saving_a_source_links_the_events_and_removing_it_unlinks_them(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue);

        $this->putRole($curator, ['source_schedules' => [$venue->subdomain], 'source_groups' => ['']]);

        $this->assertDatabaseHas('role_sources', ['role_id' => $curator->id, 'source_role_id' => $venue->id]);
        $this->assertDatabaseHas('event_role', [
            'role_id' => $curator->id,
            'event_id' => $event->id,
            'is_accepted' => 1,
            'is_auto_sourced' => 1,
        ]);

        // An empty list with the marker present means "remove everything", not "not submitted".
        $this->putRole($curator, ['source_schedules' => [], 'source_groups' => []]);

        $this->assertDatabaseMissing('role_sources', ['role_id' => $curator->id]);
        $this->assertDatabaseMissing('event_role', ['role_id' => $curator->id, 'event_id' => $event->id]);
    }

    public function test_a_save_without_the_marker_leaves_sources_alone(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');
        RoleSource::create(['role_id' => $curator->id, 'source_role_id' => $venue->id]);

        $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $curator->subdomain]),
            array_merge($this->updatePayload($curator), ['source_schedules_submitted' => 0])
        );

        $this->assertDatabaseHas('role_sources', ['role_id' => $curator->id, 'source_role_id' => $venue->id]);
    }

    public function test_a_curator_or_itself_cannot_be_a_source(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $otherCurator = $this->createCurator($owner, ['name' => 'Other']);

        $this->putRole($curator, [
            'source_schedules' => [$curator->subdomain, $otherCurator->subdomain],
            'source_groups' => ['', ''],
        ]);

        $this->assertDatabaseCount('role_sources', 0);
    }

    public function test_a_sub_schedule_from_another_curator_is_rejected(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $stranger = $this->createCurator($owner, ['name' => 'Stranger']);
        $foreignGroup = $this->createGroup($stranger);
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue);

        $this->putRole($curator, [
            'source_schedules' => [$venue->subdomain],
            'source_groups' => [UrlUtils::encodeId($foreignGroup->id)],
        ]);

        $this->assertDatabaseHas('role_sources', [
            'role_id' => $curator->id,
            'source_role_id' => $venue->id,
            'group_id' => null,
        ]);
        $this->assertDatabaseHas('event_role', [
            'role_id' => $curator->id,
            'event_id' => $event->id,
            'group_id' => null,
        ]);
    }

    public function test_changing_the_sub_schedule_refiles_already_linked_events(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $gigs = $this->createGroup($curator, ['name' => 'Gigs']);
        $talks = $this->createGroup($curator, ['name' => 'Talks']);
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue);

        $this->putRole($curator, [
            'source_schedules' => [$venue->subdomain],
            'source_groups' => [UrlUtils::encodeId($gigs->id)],
        ]);
        $this->assertDatabaseHas('event_role', ['role_id' => $curator->id, 'event_id' => $event->id, 'group_id' => $gigs->id]);

        $this->putRole($curator, [
            'source_schedules' => [$venue->subdomain],
            'source_groups' => [UrlUtils::encodeId($talks->id)],
        ]);
        $this->assertDatabaseHas('event_role', ['role_id' => $curator->id, 'event_id' => $event->id, 'group_id' => $talks->id]);
    }

    /** Over the cap the save is refused, not quietly trimmed to the first N. */
    public function test_too_many_sources_is_rejected_rather_than_truncated(): void
    {
        config(['usage.curator_source_limit' => 2]);

        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $subdomains = [];
        for ($i = 0; $i < 4; $i++) {
            $subdomains[] = $this->createRole($owner, 'venue')->subdomain;
        }

        $this->putRole($curator, [
            'source_schedules' => $subdomains,
            'source_groups' => array_fill(0, 4, ''),
        ])->assertSessionHasErrors('source_schedules');

        $this->assertDatabaseCount('role_sources', 0);

        // At the cap it goes through.
        $this->putRole($curator, [
            'source_schedules' => array_slice($subdomains, 0, 2),
            'source_groups' => ['', ''],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('role_sources', 2);
    }

    /** is_demo_mode() keys off the signed-in user being the demo account. */
    public function test_demo_mode_cannot_change_event_sources(): void
    {
        $owner = $this->createOwner();
        $owner->email = DemoService::DEMO_EMAIL;
        $owner->save();
        // Changing the email clears email_verified_at, so re-verify AFTER the save or the
        // 'verified' middleware bounces the request to /verify-email and this test passes
        // without ever reaching the controller.
        $owner->forceFill(['email_verified_at' => now()])->saveQuietly();

        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');
        $this->createEvent($venue);

        $this->putRole($curator, [
            'name' => 'Renamed by demo user',
            'source_schedules' => [$venue->subdomain],
            'source_groups' => [''],
        ]);

        // Proves the save landed: the name went through, the sources did not.
        $this->assertSame('Renamed by demo user', $curator->fresh()->name);
        $this->assertDatabaseCount('role_sources', 0);
    }

    /**
     * A backup carries the curator's calendar, not its live source links.
     *
     * Restore always builds a new schedule out of fresh event copies, so re-establishing the
     * source as well would list every event twice - the imported copy and the live original.
     * That holds for hand-curated events too, not just sourced ones, since the original is
     * still on the source schedule. So the sources are simply not carried, and the restored
     * schedule holds each event exactly once.
     */
    public function test_a_backup_carries_the_events_but_not_the_source_links(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');
        $this->createEvent($venue, ['name' => 'Sourced']);
        $byHand = $this->createEvent($venue, ['name' => 'By hand']);
        $curator->events()->attach($byHand->id, ['is_accepted' => true]);

        RoleSource::create(['role_id' => $curator->id, 'source_role_id' => $venue->id]);
        app(CuratorSourceService::class)->reconcile($curator);

        $backup = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $backup->exportSchedules([$curator->fresh()], false, $exportJob)['json'];

        $this->assertArrayNotHasKey('event_sources', $data['schedules'][0]);
        $this->assertEqualsCanonicalizing(
            ['Sourced', 'By hand'],
            array_column($data['schedules'][0]['events'], 'name'),
            'the calendar itself still travels, sourced events included'
        );

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $backup->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)
            ->where('type', 'curator')
            ->where('id', '!=', $curator->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertDatabaseMissing('role_sources', ['role_id' => $restored->id]);

        // Reconciling must not touch it either: with no sources of its own, nothing links.
        app(CuratorSourceService::class)->reconcile();

        $names = DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->where('event_role.role_id', $restored->id)
            ->pluck('events.name')
            ->sort()
            ->values()
            ->all();

        $this->assertSame(['By hand', 'Sourced'], $names,
            'each event appears exactly once on the restored schedule');
        $this->assertSame(0, DB::table('event_role')
            ->where('role_id', $restored->id)
            ->where('is_auto_sourced', true)
            ->count(), 'restored rows are hand-curated, so the reconcile can never prune them');
    }

    public function test_the_source_picker_only_offers_talent_and_venue_schedules(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'Findable Venue']);
        $this->createRole($owner, 'talent', ['name' => 'Findable Talent']);
        $this->createCurator($owner, ['name' => 'Findable Curator']);

        $results = $this->actingAs($owner)
            ->getJson(route('role.search-subdomains', ['q' => 'Findable', 'types' => 'talent,venue']))
            ->assertOk()
            ->json();

        $this->assertEqualsCanonicalizing(
            ['Findable Venue', 'Findable Talent'],
            array_column($results, 'name')
        );
    }

    /**
     * The per-source count on the edit page.
     *
     * It counts this source's events that are on the curator's calendar and still accepted,
     * so it says what the calendar shows rather than what the source holds.
     */
    public function test_a_source_row_shows_how_many_of_its_events_are_on_the_calendar(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');
        for ($i = 0; $i < 3; $i++) {
            $this->createEvent($venue, ['name' => 'Event '.$i]);
        }

        $this->putRole($curator, ['source_schedules' => [$venue->subdomain], 'source_groups' => ['']]);

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee('3 events on your calendar')
            ->assertDontSee('No events on your calendar yet');
    }

    /** A connected source with nothing to give says so, rather than showing nothing. */
    public function test_a_source_with_no_events_on_the_calendar_says_so(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');
        $this->createEvent($venue, ['is_draft' => true]);

        $this->putRole($curator, ['source_schedules' => [$venue->subdomain], 'source_groups' => ['']]);

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee('No events on your calendar yet');
    }

    /**
     * An event the curator removed by hand is not counted.
     *
     * Removal leaves an is_accepted = false tombstone on the auto-sourced row rather than
     * deleting it, so counting the rows without that gate would keep reporting the event
     * long after it left the calendar.
     */
    public function test_an_event_removed_from_the_curator_is_not_counted(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');
        $events = [];
        for ($i = 0; $i < 3; $i++) {
            $events[] = $this->createEvent($venue, ['name' => 'Event '.$i]);
        }

        $this->putRole($curator, ['source_schedules' => [$venue->subdomain], 'source_groups' => ['']]);

        // The shape EventController::uncurate() writes.
        DB::table('event_role')
            ->where('role_id', $curator->id)
            ->where('event_id', $events[0]->id)
            ->update(['is_accepted' => false]);

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee('2 events on your calendar');
    }

    /**
     * An event the curator had already added by hand still counts against the source.
     *
     * The number is coverage, not provenance: linkMissing() never overwrites an existing
     * event_role row, so that one keeps is_auto_sourced = 0 - but it is on the calendar and it
     * is one of the source's events, so the source gets credit for it. Filtering on
     * is_auto_sourced here is what made a fully working source report zero.
     */
    public function test_an_event_added_by_hand_still_counts_against_the_source(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');
        $byHand = $this->createEvent($venue, ['name' => 'By hand']);
        $this->createEvent($venue, ['name' => 'Sourced']);

        // Added before the source exists, so the reconcile leaves the row alone.
        $curator->events()->attach($byHand->id, ['is_accepted' => true]);

        $this->putRole($curator, ['source_schedules' => [$venue->subdomain], 'source_groups' => ['']]);

        // Only one of the two carries the auto-sourced marker.
        $this->assertSame(1, DB::table('event_role')
            ->where('role_id', $curator->id)
            ->where('is_auto_sourced', true)
            ->count());

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee('2 events on your calendar');
    }

    /**
     * A venue that fans its events across with default_curator_ids AND is listed as a source
     * still reports them.
     *
     * Role::autoCurateEvent() attaches without is_auto_sourced, and linkMissing() never
     * overwrites the row the push side wrote first, so not one of those events carries the
     * marker. Counting provenance made this read "No events on your calendar yet" while every
     * one of the venue's events was on the calendar - a working source looking broken.
     */
    public function test_a_push_curated_source_still_reports_its_events(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue');

        $venue->default_curator_ids = [$curator->id];
        $venue->save();

        for ($i = 0; $i < 3; $i++) {
            $venue->autoCurateEvent($this->createEvent($venue, ['name' => 'E'.$i]), $owner);
        }

        $this->putRole($curator, ['source_schedules' => [$venue->subdomain], 'source_groups' => ['']]);

        $this->assertSame(0, DB::table('event_role')
            ->where('role_id', $curator->id)
            ->where('is_auto_sourced', true)
            ->count(), 'the push side got there first, so nothing is marked auto-sourced');

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee('3 events on your calendar')
            ->assertDontSee('No events on your calendar yet');
    }

    /** Each row carries its own number, not the same one repeated. */
    public function test_each_source_row_carries_its_own_count(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        // Sorted by name, so the rows render Alpha first.
        $alpha = $this->createRole($owner, 'venue', ['name' => 'Alpha Venue']);
        $beta = $this->createRole($owner, 'venue', ['name' => 'Beta Venue']);
        $this->createEvent($alpha, ['name' => 'A1']);
        $this->createEvent($alpha, ['name' => 'A2']);
        $this->createEvent($beta, ['name' => 'B1']);

        $this->putRole($curator, [
            'source_schedules' => [$alpha->subdomain, $beta->subdomain],
            'source_groups' => ['', ''],
        ]);

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSeeInOrder([
                $alpha->subdomain,
                '2 events on your calendar',
                $beta->subdomain,
                '1 event on your calendar',
            ]);
    }

    /**
     * A source that has not accepted the event onto its own schedule gets no credit for it.
     *
     * linkMissing() only pulls through a source whose own pivot is accepted, so an event that
     * is merely pending on a second source was never supplied by it. Without the src.is_accepted
     * half of the join the count would credit that source for an event it has not even taken.
     */
    public function test_a_source_that_has_not_accepted_the_event_gets_no_credit(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        // Named so the name sort renders the venue first.
        $venue = $this->createRole($owner, 'venue', ['name' => 'Alpha Venue']);
        $talent = $this->createRole($owner, 'talent', ['name' => 'Beta Talent']);

        $event = $this->createEvent($venue, ['name' => 'Shared']);
        // Pending on the talent: the shape a submission leaves when the talent has not answered.
        $event->roles()->attach($talent->id, ['is_accepted' => null]);

        $this->putRole($curator, [
            'source_schedules' => [$venue->subdomain, $talent->subdomain],
            'source_groups' => ['', ''],
        ]);

        // It landed once, through the venue.
        $this->assertSame(1, DB::table('event_role')
            ->where('role_id', $curator->id)
            ->where('is_auto_sourced', true)
            ->count());

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSeeInOrder([
                $venue->subdomain,
                '1 event on your calendar',
                $talent->subdomain,
                'No events on your calendar yet',
            ]);
    }

    /**
     * An event covered by two sources counts under both.
     *
     * event_role records no provenance, so each source reports what it supplies and the numbers
     * deliberately do not sum to the curator's total - here 2 + 2 over 3 distinct events. A
     * later de-duplication across sources would be a behaviour change, not a fix.
     */
    public function test_an_event_on_two_sources_counts_under_both(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $venue = $this->createRole($owner, 'venue', ['name' => 'Alpha Venue']);
        $talent = $this->createRole($owner, 'talent', ['name' => 'Beta Talent']);

        $shared = $this->createEvent($venue, ['name' => 'Shared']);
        $shared->roles()->attach($talent->id, ['is_accepted' => true]);
        $this->createEvent($venue, ['name' => 'Venue only']);
        $this->createEvent($talent, ['name' => 'Talent only']);

        $this->putRole($curator, [
            'source_schedules' => [$venue->subdomain, $talent->subdomain],
            'source_groups' => ['', ''],
        ]);

        // Three distinct events on the curator, but each source supplies two of them.
        $this->assertSame(3, DB::table('event_role')
            ->where('role_id', $curator->id)
            ->where('is_auto_sourced', true)
            ->count());

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSeeInOrder([
                $venue->subdomain,
                '2 events on your calendar',
                $talent->subdomain,
                '2 events on your calendar',
            ]);
    }
}
