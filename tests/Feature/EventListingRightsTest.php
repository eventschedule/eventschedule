<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\RoleSource;
use App\Models\User;
use App\Services\CuratorSourceService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Listing an event is not running it.
 *
 * Any curator can put a public event on its own list (EventController::curate(), or by naming a
 * schedule as a source), and no one is asked. That link used to carry every right over the event:
 * a stranger's schedule that listed one could delete it, cancel it with a note mailed to its
 * buyers, and rewrite it, and a talent or venue schedule that listed one was also given its buyers.
 *
 *   - deleting, cancelling and restoring are for the event's own people (User::runsEvent(): whoever
 *     made it, or an owner or admin of the schedule that owns it);
 *   - a curator edits only the events it made, and takes any other off its own list;
 *   - only a curator lists somebody else's event, and by a form, never by opening an address;
 *   - the venue an event is at still edits it; an event from before events.creator_role_id belongs
 *     to the schedule it was first put on.
 */
class EventListingRightsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    /** @return array{0: User, 1: Role, 2: Event, 3: User, 4: Role} */
    private function listedByAStranger(string $listerType = 'curator'): array
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'The Owner Venue']);
        $event = $this->createEvent($venue, ['name' => 'The Owner Show', 'creator_role_id' => $venue->id]);

        $stranger = $this->createOwner();
        $lister = $this->createRole($stranger, $listerType, ['name' => 'Somebody Else']);

        $this->actingAs($stranger)->post(route('event.curate', ['subdomain' => $lister->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));
        if ($listerType === 'curator') {
            $this->assertTrue($lister->events()->where('events.id', $event->id)->exists(), 'sanity check: the listing was made');
        }

        return [$owner, $venue, $event->fresh(), $stranger->fresh(), $lister];
    }

    public function test_listing_an_event_does_not_let_you_delete_it(): void
    {
        [, , $event, $stranger, $lister] = $this->listedByAStranger();

        $this->actingAs($stranger)->delete(route('event.delete', ['subdomain' => $lister->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertNotNull(Event::find($event->id));
    }

    public function test_listing_an_event_does_not_let_you_cancel_it(): void
    {
        [, , $event, $stranger, $lister] = $this->listedByAStranger();

        $this->actingAs($stranger)->post(route('event.cancel', ['subdomain' => $lister->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertFalse((bool) Event::find($event->id)->is_cancelled);
    }

    public function test_listing_an_event_does_not_let_you_rewrite_it(): void
    {
        [, , $event, $stranger, $lister] = $this->listedByAStranger();

        $this->putUpdateEvent($stranger, $lister, $event, ['name' => 'Rewritten By Somebody Else']);

        $this->assertSame('The Owner Show', Event::find($event->id)->name);
    }

    public function test_naming_a_schedule_as_a_source_does_not_let_you_delete_its_events(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);

        $stranger = $this->createOwner();
        $curator = $this->createCurator($stranger);
        RoleSource::create(['role_id' => $curator->id, 'source_role_id' => $venue->id, 'group_id' => null]);
        app(CuratorSourceService::class)->reconcile($curator);
        $this->assertTrue($curator->events()->where('events.id', $event->id)->exists(), 'sanity check: the source fed the curator');

        $this->actingAs($stranger)->delete(route('event.delete', ['subdomain' => $curator->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertNotNull(Event::find($event->id));
    }

    public function test_a_schedule_that_lists_an_event_is_not_handed_its_buyers(): void
    {
        [, , $event, $stranger, $lister] = $this->listedByAStranger('talent');

        $this->assertFalse($lister->events()->where('events.id', $event->id)->exists(), 'only a curator lists somebody else\'s event');
        $this->assertFalse($stranger->canViewEventData($event->fresh()));
        $this->assertFalse($stranger->canEditEvent($event->fresh()));
    }

    public function test_opening_the_listing_address_lists_nothing(): void
    {
        $event = $this->createEvent($this->createRole($this->createOwner(), 'venue'));
        $stranger = $this->createOwner();
        $curator = $this->createCurator($stranger);

        $this->actingAs($stranger)->get('/'.$curator->subdomain.'/curate-event/'.UrlUtils::encodeId($event->id));

        $this->assertFalse($curator->events()->where('events.id', $event->id)->exists());
    }

    public function test_a_lister_still_takes_the_event_off_its_own_list(): void
    {
        [, , $event, $stranger, $lister] = $this->listedByAStranger();

        $this->actingAs($stranger)->delete(route('event.uncurate', ['subdomain' => $lister->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertNotNull(Event::find($event->id));
        $this->assertFalse($lister->events()->where('events.id', $event->id)->wherePivot('is_accepted', true)->exists());
    }

    public function test_the_venue_an_event_is_at_still_edits_it_and_does_not_delete_it(): void
    {
        $talentOwner = $this->createOwner();
        $talent = $this->createRole($talentOwner, 'talent');
        $event = $this->createEvent($talent, ['name' => 'The Talent Show', 'creator_role_id' => $talent->id]);
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->assertTrue($venueOwner->canEditEvent($event->fresh()), 'a venue the event is at edits it, as before');
        $this->assertFalse($venueOwner->runsEvent($event->fresh()), 'and does not delete or cancel the performer\'s event');
        $this->assertTrue($talentOwner->runsEvent($event->fresh()));
    }

    public function test_a_curator_keeps_the_events_it_made_and_a_team_admin_runs_the_schedules_events(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner);
        $own = $this->createEvent($curator, ['creator_role_id' => $curator->id, 'user_id' => $owner->id]);
        $admin = $this->createOwner();
        $curator->users()->attach($admin->id, ['level' => 'admin']);
        $viewer = $this->createOwner();
        $curator->users()->attach($viewer->id, ['level' => 'viewer']);

        foreach ([$owner, $admin->fresh()] as $person) {
            $this->assertTrue($person->canEditEvent($own->fresh()));
            $this->assertTrue($person->runsEvent($own->fresh()));
        }
        $this->assertFalse($viewer->fresh()->runsEvent($own->fresh()));
    }

    public function test_an_event_with_no_owning_schedule_on_record_belongs_to_the_first_it_was_put_on(): void
    {
        $owner = $this->createOwner();
        $first = $this->createCurator($owner);
        $maker = $this->createOwner();
        $event = $this->createEvent($first, ['creator_role_id' => null, 'user_id' => $maker->id]);
        $this->assertNull($event->fresh()->creator_role_id);

        $later = $this->createOwner();
        $laterCurator = $this->createCurator($later);
        $this->actingAs($later)->post(route('event.curate', ['subdomain' => $laterCurator->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertTrue($owner->runsEvent($event->fresh()));
        $this->assertTrue($owner->canEditEvent($event->fresh()));
        $this->assertFalse($maker->runsEvent($event->fresh()), 'whoever made it, and is not one of the people who run that schedule, no longer does');
        $this->assertFalse($later->fresh()->runsEvent($event->fresh()));
        $this->assertFalse($later->fresh()->canEditEvent($event->fresh()));
    }

    public function test_the_events_own_schedule_still_deletes_and_cancels(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $one = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $two = $this->createEvent($venue, ['creator_role_id' => $venue->id]);

        $this->actingAs($owner)->post(route('event.cancel', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($one->id)]));
        $this->actingAs($owner)->delete(route('event.delete', ['subdomain' => $venue->subdomain, 'hash' => UrlUtils::encodeId($two->id)]));

        $this->assertTrue((bool) Event::find($one->id)->is_cancelled);
        $this->assertNull(Event::find($two->id));
    }

    public function test_bulk_actions_leave_another_schedules_event_alone(): void
    {
        $theirs = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($theirs, ['name' => 'Their Show', 'slug' => 'their-show', 'creator_role_id' => $theirs->id]);

        $caller = $this->createOwner();
        $curator = $this->createCurator($caller);
        $curator->events()->attach($event->id, ['is_accepted' => true]);
        $categoryId = (int) collect($curator->getEventCategories())->pluck('id')->first();

        $this->actingAs($caller)->postJson(route('role.update_all_categories', ['subdomain' => $curator->subdomain]), ['category_id' => $categoryId]);
        $this->actingAs($caller)->postJson(route('role.update_all_slugs', ['subdomain' => $curator->subdomain]), ['slug_pattern' => 'renamed-{event_name}']);

        $after = Event::find($event->id);
        $this->assertNotSame($categoryId, (int) $after->category_id, 'category');
        $this->assertSame('their-show', $after->slug, 'slug');
    }

    public function test_bulk_actions_still_reach_the_schedules_own_events(): void
    {
        $caller = $this->createOwner();
        $curator = $this->createCurator($caller);
        $own = $this->createEvent($curator, ['name' => 'Own Show', 'slug' => 'own-show', 'creator_role_id' => $curator->id, 'user_id' => $caller->id]);
        $categoryId = (int) collect($curator->getEventCategories())->pluck('id')->first();

        $this->actingAs($caller)->postJson(route('role.update_all_categories', ['subdomain' => $curator->subdomain]), ['category_id' => $categoryId])->assertOk();

        $this->assertSame($categoryId, (int) Event::find($own->id)->category_id);
    }

    public function test_a_venue_that_declined_an_event_does_not_edit_it(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        $event->roles()->attach($venue->id, ['is_accepted' => false]);

        $this->assertFalse($venueOwner->canEditEvent($event->fresh()));
        $this->assertFalse($venueOwner->canViewEventData($event->fresh()));
    }

    public function test_a_venue_that_has_not_answered_yet_or_said_yes_still_edits(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent');
        foreach ([null, true] as $answer) {
            $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
            $venueOwner = $this->createOwner();
            $venue = $this->createRole($venueOwner, 'venue');
            $event->roles()->attach($venue->id, ['is_accepted' => $answer]);

            $this->assertTrue($venueOwner->canEditEvent($event->fresh()), var_export($answer, true));
        }
    }
}
