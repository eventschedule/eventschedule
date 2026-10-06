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

    /**
     * The venue is decided by the Event tab and the participants by the Participants tab. "Also
     * list on" used to list them too, as ticked boxes, and to win: unticking the venue there took
     * it off the event while the Event tab still showed it picked. The list no longer offers them,
     * so a save that leaves them out of curators[] must not read that as an untick.
     */
    public function test_the_events_own_venue_and_participants_are_not_also_list_on_ticks(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = $this->createRole($owner, 'venue');
        $act = $this->createRole($owner, 'talent');
        $curator = $this->createCurator($owner, ['require_approval' => false]);

        $posted = fn (array $curators) => [
            'venue_id' => UrlUtils::encodeId($venue->id),
            'venue_submitted' => 1,
            'members' => [
                UrlUtils::encodeId($talent->id) => ['name' => $talent->name, 'email' => ''],
                UrlUtils::encodeId($act->id) => ['name' => $act->name, 'email' => ''],
            ],
            'members_submitted' => 1,
            'curators_submitted' => 1,
            'curators' => $curators,
        ];

        $this->postCreateEvent($owner, $talent, $posted([UrlUtils::encodeId($curator->id)]))->assertRedirect();
        $event = $this->latestEvent();
        $attached = fn () => $event->roles()->pluck('roles.id')->sort()->values()->all();
        $everyone = collect([$talent->id, $venue->id, $act->id, $curator->id])->sort()->values()->all();
        $this->assertSame($everyone, $attached(), 'sanity check: made with a venue, a second act and a curator');

        // The form as it is now: only the curator has a box.
        $this->putUpdateEvent($owner, $talent, $event, $posted([UrlUtils::encodeId($curator->id)]))->assertRedirect();
        $this->assertSame($everyone, $attached(), 'the venue and the second act stay');
        $this->assertDatabaseHas('event_role', ['event_id' => $event->id, 'role_id' => $venue->id, 'is_accepted' => 1]);
        $this->assertDatabaseHas('event_role', ['event_id' => $event->id, 'role_id' => $act->id, 'is_accepted' => 1]);

        // And the list still decides what it does hold: the curator's box unticked.
        $this->putUpdateEvent($owner, $talent, $event, $posted([]))->assertRedirect();
        $this->assertSame(collect([$talent->id, $venue->id, $act->id])->sort()->values()->all(), $attached());
    }

    /** Their own tabs still remove them: a participant dropped there, a venue cleared there. */
    public function test_a_participant_removed_on_its_own_tab_is_still_removed(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $act = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $talent, [
            'members' => [
                UrlUtils::encodeId($talent->id) => ['name' => $talent->name, 'email' => ''],
                UrlUtils::encodeId($act->id) => ['name' => $act->name, 'email' => ''],
            ],
            'members_submitted' => 1,
            'curators_submitted' => 1,
        ])->assertRedirect();
        $event = $this->latestEvent();
        $this->assertContains($act->id, $event->roles()->pluck('roles.id')->all());

        $this->putUpdateEvent($owner, $talent, $event, [
            'members' => [UrlUtils::encodeId($talent->id) => ['name' => $talent->name, 'email' => '']],
            'members_submitted' => 1,
            'curators_submitted' => 1,
        ])->assertRedirect();

        $this->assertNotContains($act->id, $event->roles()->pluck('roles.id')->all());
    }

    /** The other half of that sentence: the venue cleared on the Event tab is taken off the event. */
    public function test_a_venue_cleared_on_its_own_tab_is_still_removed(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = $this->createRole($owner, 'venue');

        $this->postCreateEvent($owner, $talent, [
            'venue_id' => UrlUtils::encodeId($venue->id),
            'venue_submitted' => 1,
            'curators_submitted' => 1,
        ])->assertRedirect();
        $event = $this->latestEvent();
        $this->assertContains($venue->id, $event->roles()->pluck('roles.id')->all(), 'sanity check: made at the venue');

        // The section on the page, with no venue in it (an online event keeps the save valid).
        $this->putUpdateEvent($owner, $talent, $event, [
            'venue_submitted' => 1,
            'event_url' => 'https://meet.example.org/room',
            'curators_submitted' => 1,
        ])->assertRedirect();

        $this->assertNotContains($venue->id, $event->roles()->pluck('roles.id')->all());
    }
}
