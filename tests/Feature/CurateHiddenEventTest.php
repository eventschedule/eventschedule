<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Curating adds an event to the schedule in the URL, a curator. A draft and an appointment booking
 * are members-only (Event::isMembersOnly()), and the gate asked whether the visitor belonged to
 * THAT schedule, the one the event would be added to, rather than to a schedule the event is on.
 * So anybody who runs a curator could request /{their-curator}/curate-event/{hash} with another
 * schedule's draft, or a booking the API had listed, attach it to their own calendar and see it
 * there as one of its members.
 *
 * What counts now is membership of the event's own schedules, as it already did for an unlisted
 * event, and anybody else is answered exactly as an unknown event is.
 */
class CurateHiddenEventTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const UNKNOWN_ID = 987654;

    private User $venueOwner;

    private Role $venue;

    protected function setUp(): void
    {
        parent::setUp();

        // As in production. With debug on, a JSON 404 carries the file and line it was thrown
        // from, which would tell any two refusals apart.
        config(['app.debug' => false]);

        $this->venueOwner = $this->createOwner();
        $this->venue = $this->createRole($this->venueOwner, 'venue', ['name' => 'Harbour Hall']);
    }

    private function draft(): Event
    {
        return $this->createEvent($this->venue, [
            'name' => 'Unannounced Residency',
            'is_draft' => true,
            'creator_role_id' => $this->venue->id,
        ]);
    }

    /** A booking the API has listed: members-only for being a booking, not for being unlisted. */
    private function listedBooking(): Event
    {
        $type = $this->createAppointmentType($this->venue, ['name' => 'Intro Chat']);

        return $this->createEvent($this->venue, [
            'name' => 'Intro Chat - Jane Private',
            'appointment_type_id' => $type->id,
            'is_private' => false,
            'creator_role_id' => $this->venue->id,
        ]);
    }

    private function curateUrl(Role $curator, int $eventId): string
    {
        return '/'.$curator->subdomain.'/curate-event/'.UrlUtils::encodeId($eventId);
    }

    /** The response without what changes per request, and with the event's id taken out. */
    private function comparable(TestResponse $response, int $eventId): array
    {
        $hash = UrlUtils::encodeId($eventId);
        $body = (string) $response->getContent();

        if ($response->headers->get('Content-Type') === 'application/json') {
            // "No query results for model [App\Models\Event] 123", the id each caller sent.
            $body = preg_replace('/\] '.$eventId.'\b/', '] {id}', $body);
        }

        $body = str_replace($hash, '{event}', preg_replace(
            ['~nonce="[^"]*"~', '~(<meta name="csrf-token" content=")[^"]*~', '~(name="_token" value=")[^"]*~'],
            ['nonce=""', '$1', '$1'],
            $body
        ));

        return [
            'status' => $response->getStatusCode(),
            'location' => str_replace($hash, '{event}', (string) $response->headers->get('Location')),
            'body' => $body,
        ];
    }

    private function assertAnswersAsAnUnknownEvent(User $as, Role $curator, Event $event): void
    {
        $asked = [
            'a page' => fn (int $id) => $this->actingAs($as)->get($this->curateUrl($curator, $id)),
            // What the import page sends (event/import.blade.php).
            'the import page' => fn (int $id) => $this->actingAs($as)->getJson($this->curateUrl($curator, $id), ['X-Requested-With' => 'XMLHttpRequest']),
        ];

        foreach ($asked as $how => $ask) {
            $expected = $ask(self::UNKNOWN_ID);
            $this->assertSame(404, $expected->getStatusCode(), "fixture: {$how} gets a 404 for an unknown event");

            $answer = $ask($event->id);

            $this->assertStringNotContainsString($event->name, (string) $answer->getContent(), "{$how}: names the event");
            $this->assertSame(
                $this->comparable($expected, self::UNKNOWN_ID),
                $this->comparable($answer, $event->id),
                "{$how}: answers a hidden event unlike an event that does not exist"
            );
        }

        $this->assertDatabaseMissing('event_role', ['event_id' => $event->id, 'role_id' => $curator->id]);
    }

    public function test_a_stranger_cannot_curate_another_schedules_draft_into_their_curator(): void
    {
        $stranger = $this->createOwner();
        $curator = $this->createCurator($stranger);

        $this->assertAnswersAsAnUnknownEvent($stranger, $curator, $this->draft());
    }

    public function test_a_stranger_cannot_curate_a_listed_booking_into_their_curator(): void
    {
        $stranger = $this->createOwner();
        $curator = $this->createCurator($stranger);

        $this->assertAnswersAsAnUnknownEvent($stranger, $curator, $this->listedBooking());
    }

    public function test_a_member_of_the_events_own_schedule_still_curates_it(): void
    {
        $draft = $this->draft();
        $curator = $this->createCurator($this->venueOwner);

        $this->actingAs($this->venueOwner)->get($this->curateUrl($curator, $draft->id))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_added_to_schedule'));

        $this->assertDatabaseHas('event_role', ['event_id' => $draft->id, 'role_id' => $curator->id, 'is_accepted' => 1]);
    }
}
