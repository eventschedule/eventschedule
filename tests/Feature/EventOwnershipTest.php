<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Repos\EventRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The columns of an event that only the server writes.
 *
 * EventRepo::saveEvent() fills the event from the whole request, and these columns are fillable
 * for the code that sets them by hand. So anybody who could save the event could post them: a
 * schedule that merely LISTED an event posted its own id as creator_role_id and became the event's
 * owner, which is the schedule that sells its tickets and sees its sales (Event::ticketingRole()).
 */
class EventOwnershipTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    public function test_a_schedule_that_lists_an_event_cannot_make_itself_its_owner(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);

        $curatorUser = $this->createOwner();
        $curator = $this->createFreeRole($curatorUser, 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $this->putUpdateEvent($curatorUser, $curator, $event, ['name' => 'Renamed by the curator', 'creator_role_id' => $curator->id])->assertRedirect();

        $this->assertSame('Renamed by the curator', Event::find($event->id)->name, 'sanity check: the save went through, it was not refused');
        $this->assertSame($talent->id, Event::find($event->id)->creator_role_id);
        $this->assertSame($talent->id, Event::find($event->id)->ticketingRole()?->id, 'and so it still sells for the schedule that made it');
    }

    public function test_the_owner_cannot_be_posted_away_by_the_owner_either(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $other = $this->createRole($owner, 'venue');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);

        $this->putUpdateEvent($owner, $talent, $event, ['creator_role_id' => $other->id])->assertRedirect();

        $this->assertSame($talent->id, Event::find($event->id)->creator_role_id);
    }

    public function test_a_new_event_belongs_to_the_schedule_it_was_made_on_whatever_is_posted(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $stranger = $this->createRole($this->createOwner(), 'talent');

        $this->postCreateEvent($owner, $talent, ['creator_role_id' => $stranger->id])->assertRedirect();

        $this->assertSame($talent->id, $this->latestEvent()->creator_role_id);
    }

    public function test_counters_and_bookkeeping_are_not_taken_from_the_form(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id, 'rsvp_enabled' => true, 'rsvp_limit' => 40]);
        $event->forceFill(['rsvp_sold' => json_encode(['2026-08-15' => 12]), 'translation_attempts' => 3, 'last_notified_fan_content_count' => 7])->save();
        $before = Event::find($event->id)->only(EventRepo::SERVER_OWNED_FIELDS);

        $this->putUpdateEvent($owner, $talent, $event, [
            'rsvp_sold' => json_encode(['2026-08-15' => 0]),
            'translation_attempts' => 0,
            'last_translated_at' => '2020-01-01 00:00:00',
            'last_notified_fan_content_count' => 0,
        ])->assertRedirect();

        $after = Event::find($event->id)->only(EventRepo::SERVER_OWNED_FIELDS);
        // The save itself may reset a translation counter when the text changed; this save changed
        // only the name the helper posts, so compare the columns a form has no business near.
        foreach (['creator_role_id', 'rsvp_sold', 'last_notified_fan_content_count'] as $column) {
            $this->assertEquals($before[$column], $after[$column], "{$column} is the server's");
        }
    }

    /** The two translation bookkeeping columns, on a save that changes no text (so nothing resets them). */
    public function test_translation_bookkeeping_is_not_taken_from_the_form(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id, 'name' => 'Characterized Event']);
        $event->forceFill(['translation_attempts' => 3, 'last_translated_at' => '2026-05-01 10:00:00'])->save();

        $this->putUpdateEvent($owner, $talent, $event, [
            'translation_attempts' => 0,
            'last_translated_at' => '2020-01-01 00:00:00',
        ])->assertRedirect();

        $fresh = Event::find($event->id);
        $this->assertSame(3, (int) $fresh->translation_attempts);
        $this->assertSame('2026-05-01 10:00:00', \Illuminate\Support\Carbon::parse($fresh->last_translated_at)->format('Y-m-d H:i:s'));
    }

    /**
     * The list is only useful while it names real fillable columns: a column that stops being
     * fillable no longer needs it, and one misspelt here protects nothing.
     */
    public function test_every_server_owned_field_is_one_the_request_could_otherwise_fill(): void
    {
        $fillable = (new Event)->getFillable();

        foreach (EventRepo::SERVER_OWNED_FIELDS as $field) {
            $this->assertContains($field, $fillable, "{$field} is not fillable, so it does not belong in the list");
        }
        $this->assertContains('creator_role_id', EventRepo::SERVER_OWNED_FIELDS);
    }
}
