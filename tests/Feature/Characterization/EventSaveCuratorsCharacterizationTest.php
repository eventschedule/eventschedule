<?php

namespace Tests\Feature\Characterization;

use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterizes the post-save roles()->sync() section of
 * EventRepo::saveEvent() (REFACTOR_PLAN.md P11): curators[] attach/detach
 * semantics, the authoritative-schedules-tab rule, the venue_submitted /
 * members_submitted preservation flags, and venue switching on update.
 */
class EventSaveCuratorsCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_curators_attach_with_group_pivot_and_auto_acceptance(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        // Own curator: accept_requests on, no approval requirement.
        $curator = $this->createCurator($owner, ['require_approval' => false]);
        $group = $this->createGroup($curator);

        $this->postCreateEvent($owner, $role, [
            'curators' => [UrlUtils::encodeId($curator->id)],
            'curator_groups' => [UrlUtils::encodeId($curator->id) => UrlUtils::encodeId($group->id)],
        ])->assertRedirect();

        // Owner is a member of the curator schedule -> auto-accepted, and the
        // curator_groups pivot lands as group_id.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $curator->id,
            'is_accepted' => 1,
            'group_id' => $group->id,
        ]);
    }

    public function test_curator_requiring_approval_is_attached_unaccepted_for_non_member(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner, ['require_approval' => true]);
        // The submitting user follows the curator so it is visible/selectable.
        $this->followRole($owner, $curator);

        $this->postCreateEvent($owner, $role, [
            'curators' => [UrlUtils::encodeId($curator->id)],
        ])->assertRedirect();

        // Not a member, approval required -> attached but NOT accepted. This
        // is the is_accepted visibility gate: the event won't show on the
        // curator's page until they approve the request.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $curator->id,
            'is_accepted' => null,
        ]);
    }

    public function test_update_detaches_curator_absent_from_curators_array(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $curator = $this->createCurator($owner);
        $event = $this->createEvent($role);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        // Schedules tab is authoritative: previously-attached + visible in the
        // tab + absent from curators[] -> detached.
        $this->putUpdateEvent($owner, $role, $event, [
            'curators' => [],
        ])->assertRedirect();

        $this->assertDatabaseMissing('event_role', [
            'event_id' => $event->id,
            'role_id' => $curator->id,
        ]);
        // The schedule being edited stays attached.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_update_preserves_attachments_the_user_cannot_see(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        // A curator the user does NOT follow / is not a member of - invisible
        // in the schedules tab, e.g. attached by the curator importing the event.
        $otherOwner = $this->createOwner();
        $hiddenCurator = $this->createCurator($otherOwner);
        $event = $this->createEvent($role);
        $event->roles()->attach($hiddenCurator->id, ['is_accepted' => true]);

        $this->putUpdateEvent($owner, $role, $event, [
            'curators' => [],
        ])->assertRedirect();

        // Invisible attachments are never dropped by a form save.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $hiddenCurator->id,
            'is_accepted' => 1,
        ]);
    }

    public function test_update_switching_venue_replaces_pivot_rows(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $venueA = $this->createVenueWithAddress($owner, ['name' => 'Venue A']);
        $venueB = $this->createVenueWithAddress($owner, ['name' => 'Venue B']);

        $this->postCreateEvent($owner, $role, [
            'venue_id' => UrlUtils::encodeId($venueA->id),
        ])->assertRedirect();
        $event = $this->latestEvent();

        // The form submits venue_submitted when its venue section is rendered,
        // so a switched venue drops the old one.
        $this->putUpdateEvent($owner, $role, $event, [
            'venue_id' => UrlUtils::encodeId($venueB->id),
            'venue_submitted' => '1',
        ])->assertRedirect();

        $this->assertDatabaseMissing('event_role', [
            'event_id' => $event->id,
            'role_id' => $venueA->id,
        ]);
        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $venueB->id,
            'is_accepted' => 1,
        ]);
    }

    public function test_update_without_venue_submitted_preserves_existing_venue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $venueOwner = $this->createOwner();
        // A venue the user can't see in their schedules tab (not followed).
        $venue = $this->createRole($venueOwner, 'venue');
        $event = $this->createEvent($role);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        // Programmatic callers (API, importers) never submit venue_submitted,
        // so the attached venue survives an update that omits it.
        $this->putUpdateEvent($owner, $role, $event, [])->assertRedirect();

        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $venue->id,
            'is_accepted' => 1,
        ]);
    }

    public function test_current_role_group_id_sets_sub_schedule_on_own_pivot(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $group = $this->createGroup($role);

        $this->postCreateEvent($owner, $role, [
            'current_role_group_id' => UrlUtils::encodeId($group->id),
        ])->assertRedirect();

        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $role->id,
            'group_id' => $group->id,
        ]);
    }
}
