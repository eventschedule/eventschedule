<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The AP event form collects a wall-clock and EventRepo::saveEvent() converts it to UTC, so the
 * two must resolve the SAME zone or the event lands at a time nobody typed.
 *
 * The form prefills and labels itself with Role::captureTimezone() (roles.timezone, else the app
 * timezone), which is the same expression Event::scheduleTimezone() uses to DISPLAY the event.
 * saveEvent()'s own fallback chain is different - it consults the venue before the user and the
 * app - so on a schedule with no timezone of its own the capture used to land on the VENUE's zone
 * while the form was labelled with the app's, and every save shifted the event by the difference.
 *
 * Fixed by passing Role::captureTimezone() to saveEvent() as its $timezoneOverride at each of the
 * form paths, so the venue-first chain never gets a say. Same fix, and same reasoning, as the API
 * side in issue #123.
 */
class EventFormTimezoneRoundTripTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The raw stored column, not the accessor - this is about what lands in the database. */
    private function storedStartsAt(Event $event): string
    {
        return Event::query()->whereKey($event->id)->value('starts_at');
    }

    /** A wall-clock as the app timezone would store it, i.e. what the form promised the user. */
    private function asAppTimezoneUtc(string $wallClock): string
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $wallClock, config('app.timezone'))
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
    }

    private function venueIn(Role $owner_role_for, string $timezone): Role
    {
        return $this->createRole($owner_role_for->user, 'venue', [
            'name' => 'Round Trip Hall',
            'address1' => '1 Main St',
            'timezone' => $timezone,
        ]);
    }

    /**
     * The headline regression: re-saving the form without touching the time must not move it.
     * The payload is what the form itself renders - the prefill is starts_at expressed in
     * captureTimezone() - so a save that changes nothing has to be an identity.
     */
    public function test_resaving_the_form_does_not_move_an_event_on_a_schedule_without_a_timezone(): void
    {
        $owner = $this->createOwner();
        $schedule = $this->createRole($owner, 'talent', ['timezone' => null]);
        $venue = $this->venueIn($schedule, 'America/New_York');

        $event = $this->createEvent($schedule, ['starts_at' => '2026-10-20 11:00:00']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $event->forceFill(['creator_role_id' => $schedule->id])->save();

        $before = $this->storedStartsAt($event);

        // Exactly what event/edit.blade.php puts in the form for this event.
        $prefill = $event->fresh()->getStartDateTime(null, true, $schedule->captureTimezone())
            ->format('Y-m-d H:i:s');

        $this->actingAs($owner)->put(route('event.update', [
            'subdomain' => $schedule->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]), [
            'name' => 'Renamed',
            'starts_at' => $prefill,
            'duration' => $event->duration,
            'schedule_type' => 'one_time',
            // Re-posted by the form on every save, and they resolve onto the venue above.
            'venue_name' => 'Round Trip Hall',
            'venue_address1' => '1 Main St',
        ])->assertRedirect();

        $this->assertSame($before, $this->storedStartsAt($event),
            'a re-save that never changed the time moved the event');
    }

    /**
     * Creating against a venue in another zone anchors to the SCHEDULE, not the venue: the form
     * told the user which zone it was collecting, and that promise is what has to hold.
     */
    public function test_creating_an_event_anchors_the_typed_time_to_the_schedule_not_the_venue(): void
    {
        $owner = $this->createOwner();
        $schedule = $this->createRole($owner, 'talent', ['timezone' => null]);
        $venue = $this->venueIn($schedule, 'America/New_York');

        $this->actingAs($owner)->post(route('event.store', ['subdomain' => $schedule->subdomain]), [
            'name' => 'Created',
            'starts_at' => '2026-11-05 18:30:00',
            'duration' => 2,
            'schedule_type' => 'one_time',
            'venue_id' => UrlUtils::encodeId($venue->id),
        ])->assertRedirect();

        $event = Event::query()->orderByDesc('id')->firstOrFail();

        $this->assertSame($this->asAppTimezoneUtc('2026-11-05 18:30:00'), $this->storedStartsAt($event));
        $this->assertSame(config('app.timezone'), $event->timezone,
            'the event recorded a capture zone the form never offered');
    }

    /**
     * The AI import review screen posts the same wall-clock the parser put in front of the user,
     * against the same schedule, so it is anchored the same way.
     */
    public function test_an_ai_import_anchors_the_reviewed_time_to_the_schedule_not_the_venue(): void
    {
        $owner = $this->createOwner();
        $schedule = $this->createRole($owner, 'talent', ['timezone' => null]);
        $this->venueIn($schedule, 'America/New_York');

        $this->actingAs($owner)
            ->postJson(route('event.import', ['subdomain' => $schedule->subdomain]), [
                'name' => 'Imported',
                'starts_at' => '2026-11-05 18:30:00',
                'duration' => 2,
                'schedule_type' => 'one_time',
                'venue_name' => 'Round Trip Hall',
                'venue_address1' => '1 Main St',
            ])->assertSuccessful();

        $event = Event::query()->orderByDesc('id')->firstOrFail();

        $this->assertSame($this->asAppTimezoneUtc('2026-11-05 18:30:00'), $this->storedStartsAt($event));
    }

    /**
     * The guest submission form has the same contract, against a curator that may equally have no
     * timezone of its own.
     */
    public function test_a_guest_submission_anchors_to_the_curator_not_the_venue(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', [
            'timezone' => null,
            'accept_requests' => true,
            'require_account' => false,
        ]);
        $this->venueIn($curator, 'America/New_York');

        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
            'name' => 'Submitted',
            'starts_at' => '2026-11-05 18:30:00',
            'duration' => 2,
            'schedule_type' => 'one_time',
            'venue_name' => 'Round Trip Hall',
            'venue_address1' => '1 Main St',
        ])->assertOk();

        $event = Event::query()->orderByDesc('id')->firstOrFail();

        $this->assertSame($this->asAppTimezoneUtc('2026-11-05 18:30:00'), $this->storedStartsAt($event));
    }

    /**
     * The require_account variant saves onto the SUBMITTER's own talent schedule while the time was
     * typed on the CURATOR's page, which is why it passed a zone explicitly in the first place. It
     * passed roles.timezone though, so a curator with none handed saveEvent() null and the capture
     * fell back to the submitter's own schedule - the one zone the override existed to rule out.
     */
    public function test_a_guest_submission_with_an_account_anchors_to_the_curator_not_the_submitter(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', [
            'timezone' => null,
            'accept_requests' => true,
            'require_account' => true,
        ]);

        // The submitter already has a schedule, in a zone of its own. The time below was typed on
        // the curator's page, so this zone must not get a say in what it means.
        $submitter = $this->createOwner();
        $this->createRole($submitter, 'talent', ['timezone' => 'Asia/Tokyo']);

        $this->actingAs($submitter)
            ->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
                'name' => 'Submitted',
                'starts_at' => '2026-11-05 18:30:00',
                'duration' => 2,
                'schedule_type' => 'one_time',
            ])->assertOk();

        $event = Event::query()->orderByDesc('id')->firstOrFail();

        $this->assertSame($this->asAppTimezoneUtc('2026-11-05 18:30:00'), $this->storedStartsAt($event));
    }

    /**
     * The invariant the whole fix rests on: the zone a time is CAPTURED in is the zone it is
     * DISPLAYED in. If these two ever diverge, every event on a schedule is shown at a time it
     * was not entered at, and none of the assertions above would notice.
     */
    public function test_the_capture_zone_is_the_zone_events_are_displayed_in(): void
    {
        $owner = $this->createOwner();

        foreach ([null, '', 'Asia/Tokyo'] as $scheduleTimezone) {
            $schedule = $this->createRole($owner, 'talent', ['timezone' => $scheduleTimezone]);
            $event = $this->createEvent($schedule);
            $event->forceFill(['creator_role_id' => $schedule->id])->save();

            $this->assertSame(
                $event->fresh()->scheduleTimezone(),
                $schedule->captureTimezone(),
                'capture and display disagree for roles.timezone '.var_export($scheduleTimezone, true)
            );
        }
    }
}
