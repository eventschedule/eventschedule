<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Unlisted and Internal are Enterprise states, and an event keeps them whichever schedule's form
 * saves it.
 *
 * EventRepo::saveEvent() strips them when the plan does not allow them, and used to ask only the
 * schedule the form was OPENED from. That schedule's form has no Unlisted choice to show, so an
 * Enterprise schedule's Unlisted event saved from it - by the same owner's other schedule, or by a
 * curator that lists it - became a Draft and lost its password with nothing on screen to say so.
 */
class EventVisibilityAcrossSchedulesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function unlistedEvent(): Event
    {
        $talent = $this->createRole($this->createOwner(), 'talent');
        $this->assertTrue($talent->isEnterprise(), 'sanity check: the event\'s own schedule may hold an Unlisted event');

        return $this->createEvent($talent, ['creator_role_id' => $talent->id, 'is_private' => true, 'event_password' => 'opensesame']);
    }

    public function test_an_unlisted_event_saved_by_a_schedule_without_enterprise_stays_unlisted(): void
    {
        $event = $this->unlistedEvent();
        $curatorUser = $this->createOwner();
        $curator = $this->createFreeRole($curatorUser, 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        // What that schedule's form posts: the flags as the page data had them, no pill pressed.
        $this->putUpdateEvent($curatorUser, $curator, $event, ['name' => 'Renamed by the curator', 'is_draft' => 0, 'is_private' => 1, 'is_internal' => 0, 'event_password' => 'opensesame'])->assertRedirect();

        $fresh = Event::find($event->id);
        $this->assertSame('Renamed by the curator', $fresh->name, 'sanity check: the save went through, it was not refused');
        $this->assertSame('unlisted', $fresh->visibilityState());
        $this->assertSame('opensesame', $fresh->event_password);
    }

    /**
     * That schedule's form has no password field to send: the page shows no Unlisted choice, so
     * it posts the three flags as its data had them and nothing else.
     */
    public function test_the_password_survives_a_form_that_has_no_field_for_it(): void
    {
        $event = $this->unlistedEvent();
        $curatorUser = $this->createOwner();
        $curator = $this->createFreeRole($curatorUser, 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $this->putUpdateEvent($curatorUser, $curator, $event, ['name' => 'Renamed again', 'is_draft' => 0, 'is_private' => 1, 'is_internal' => 0])->assertRedirect();

        $fresh = Event::find($event->id);
        $this->assertSame('Renamed again', $fresh->name);
        $this->assertSame('unlisted', $fresh->visibilityState());
        $this->assertSame('opensesame', $fresh->event_password);
    }

    public function test_the_same_owner_saving_from_their_free_schedule_keeps_it_unlisted(): void
    {
        $event = $this->unlistedEvent();
        $owner = $event->user;
        $freeVenue = $this->createFreeRole($owner, 'venue');
        $event->roles()->attach($freeVenue->id, ['is_accepted' => true]);

        $this->putUpdateEvent($owner, $freeVenue, $event, ['is_draft' => 0, 'is_private' => 1, 'is_internal' => 0, 'event_password' => 'opensesame'])->assertRedirect();

        $this->assertSame('unlisted', Event::find($event->id)->visibilityState());
    }

    /** The strip itself is unchanged where no schedule involved has Enterprise. */
    public function test_an_event_of_schedules_without_enterprise_cannot_be_made_unlisted(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createFreeRole($owner, 'talent');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);

        $this->putUpdateEvent($owner, $talent, $event, ['is_draft' => 0, 'is_private' => 1, 'is_internal' => 0, 'event_password' => 'secret'])->assertRedirect();

        $fresh = Event::find($event->id);
        $this->assertFalse((bool) $fresh->is_private);
        $this->assertTrue((bool) $fresh->is_draft, 'hidden as a Draft, never flipped to Public');
        $this->assertNull($fresh->event_password);
    }
}
