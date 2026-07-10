<?php

namespace Tests\Feature\Characterization;

use App\Jobs\NotifyEventChange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterizes saveEvent()'s lifecycle effects (REFACTOR_PLAN.md P11):
 * draft transitions, the iCal-sequence bump + attendee notification gate,
 * flyer storage priority, the audit-log chokepoint, and the NOT NULL boolean
 * re-coercion for non-JS clients (landmine L6).
 */
class EventSaveLifecycleCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_draft_create_and_publish_transition(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, ['is_draft' => '1'])->assertRedirect();
        $event = $this->latestEvent();
        $this->assertDatabaseHas('events', ['id' => $event->id, 'is_draft' => 1]);

        $this->putUpdateEvent($owner, $role, $event, ['is_draft' => '0'])
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_updated'));

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'is_draft' => 0,
            // Publishing is not a "material change": no sequence bump.
            'ical_sequence' => 0,
        ]);
    }

    public function test_material_change_with_notify_bumps_sequence_and_dispatches_notification(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['starts_at' => '2026-08-16 00:00:00', 'timezone' => 'America/New_York']);
        // Paid attendee with a REAL domain - example.com is excluded by
        // Sale::excludeTestEmails and would make hasRecipients() false.
        $this->createSale($event, $role, ['email' => 'attendee@gmail.com', 'status' => 'paid']);

        $response = $this->putUpdateEvent($owner, $role, $event, [
            'starts_at' => '2026-08-20 21:00:00',
            'notify_attendees' => '1',
            'notify_message' => 'Moved to Thursday!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message', __('messages.attendees_notified', ['count' => 1]));

        $this->assertDatabaseHas('events', ['id' => $event->id, 'ical_sequence' => 1]);
        Queue::assertPushed(NotifyEventChange::class);
    }

    public function test_material_change_without_notify_still_bumps_sequence_but_sends_nothing(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['starts_at' => '2026-08-16 00:00:00', 'timezone' => 'America/New_York']);
        $this->createSale($event, $role, ['email' => 'attendee@gmail.com', 'status' => 'paid']);

        $response = $this->putUpdateEvent($owner, $role, $event, [
            'starts_at' => '2026-08-20 21:00:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('message', __('messages.event_updated'));

        // Subscribed calendars still need the change -> sequence advances,
        // but no attendee notification is dispatched.
        $this->assertDatabaseHas('events', ['id' => $event->id, 'ical_sequence' => 1]);
        Queue::assertNotPushed(NotifyEventChange::class);
    }

    public function test_immaterial_update_neither_bumps_sequence_nor_notifies(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['starts_at' => '2026-08-16 00:00:00', 'timezone' => 'America/New_York']);
        $this->createSale($event, $role, ['email' => 'attendee@gmail.com', 'status' => 'paid']);

        // Same wall clock and duration resubmitted; only the name changes.
        $this->putUpdateEvent($owner, $role, $event, [
            'name' => 'Renamed Event',
            'starts_at' => '2026-08-15 20:00:00',
            'duration' => 2,
            'notify_attendees' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Renamed Event',
            'ical_sequence' => 0,
        ]);
        Queue::assertNotPushed(NotifyEventChange::class);
    }

    public function test_flyer_upload_is_stored_and_recorded(): void
    {
        Storage::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // The form's file input is named flyer_image (the flyer_image_url
        // validation rule in EventCreateRequest does not carry the upload).
        $this->postCreateEvent($owner, $role, [
            'flyer_image' => UploadedFile::fake()->image('flyer.png', 400, 500),
        ])->assertRedirect();

        $event = $this->latestEvent();
        $filename = $event->getAttributes()['flyer_image_url'];
        $this->assertMatchesRegularExpression('/^flyer_[a-z0-9]+\.png$/', $filename);
        Storage::assertExists('public/'.$filename);
    }

    public function test_clone_flyer_image_copies_to_a_fresh_file(): void
    {
        Storage::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        Storage::put('public/flyer_source123.png', 'png-bytes');

        $this->postCreateEvent($owner, $role, [
            'clone_flyer_image' => 'flyer_source123.png',
        ])->assertRedirect();

        $event = $this->latestEvent();
        $filename = $event->getAttributes()['flyer_image_url'];

        // A fresh filename, never the source's - deleting the clone's flyer
        // must not touch the original.
        $this->assertNotSame('flyer_source123.png', $filename);
        $this->assertMatchesRegularExpression('/^flyer_[a-z0-9]+\.png$/', $filename);
        Storage::assertExists('public/'.$filename);
        Storage::assertExists('public/flyer_source123.png');
        $this->assertSame('png-bytes', Storage::get('public/'.$filename));
    }

    public function test_audit_log_rows_on_create_and_update(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role)->assertRedirect();
        $event = $this->latestEvent();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'event.create',
            'user_id' => $owner->id,
            'model_type' => 'Event',
            'model_id' => $event->id,
        ]);

        $this->putUpdateEvent($owner, $role, $event, ['name' => 'Renamed'])->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'event.update',
            'user_id' => $owner->id,
            'model_type' => 'Event',
            'model_id' => $event->id,
        ]);
    }

    public function test_non_js_client_empty_boolean_fields_store_as_false_not_null(): void
    {
        // Landmine L6: the form's Vue-bound hidden inputs submit "" from
        // non-JS clients; ConvertEmptyStringsToNull turns them into null, and
        // saveEvent re-coerces the NOT NULL boolean columns via has()+boolean().
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'tickets_enabled' => '',
            'rsvp_enabled' => '',
            'ask_phone' => '',
            'require_phone' => '',
            'country_code_phone' => '',
            'individual_tickets' => '',
            'individual_ticket_fields' => '',
            'sell_after_start' => '',
            'show_unavailable_tickets' => '',
            'is_draft' => '',
            'is_private' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'tickets_enabled' => 0,
            'rsvp_enabled' => 0,
            'ask_phone' => 0,
            'require_phone' => 0,
            'country_code_phone' => 0,
            'individual_tickets' => 0,
            'individual_ticket_fields' => 0,
            'sell_after_start' => 0,
            'show_unavailable_tickets' => 0,
            'is_draft' => 0,
            'is_private' => 0,
        ]);
    }
}
