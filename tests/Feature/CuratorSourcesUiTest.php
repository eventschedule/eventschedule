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
}
