<?php

namespace Tests\Feature;

use App\Jobs\SyncEventToGoogleCalendar;
use App\Models\Event;
use App\Models\MicrosoftCalendarSync;
use App\Models\Role;
use App\Services\MicrosoftCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Inbound calendar delete-sync: when an event is deleted in a connected calendar, Event::applyInboundDeletion()
 * honors the schedule's calendar_delete_action (ignore / cancel / delete) and never echoes a delete back out.
 */
class CalendarInboundDeletionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * Owner + bidirectional Google role: the deleting hook WOULD push a delete outbound unless the
     * inbound handler suppresses it, which makes the loop-safety assertions meaningful.
     */
    private function syncingRole(string $deleteAction): Role
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref'])->save();

        return $this->createRole($owner, 'venue', [
            'sync_direction' => 'both',
            'calendar_delete_action' => $deleteAction,
        ]);
    }

    public function test_ignore_leaves_the_event_untouched(): void
    {
        $role = $this->syncingRole('ignore');
        $event = $this->createEvent($role, ['name' => 'Keep Me']);

        $outcome = $event->applyInboundDeletion($role->calendarDeleteAction());

        $this->assertSame('ignored', $outcome);
        $this->assertNotNull(Event::find($event->id));
        $this->assertFalse((bool) $event->fresh()->is_cancelled);
    }

    public function test_cancel_hides_without_deleting_or_dispatching_outbound(): void
    {
        $role = $this->syncingRole('cancel');
        $event = $this->createEvent($role);

        Bus::fake();
        $outcome = $event->applyInboundDeletion($role->calendarDeleteAction());

        $this->assertSame('cancelled', $outcome);
        $this->assertNotNull(Event::find($event->id));
        $this->assertTrue((bool) $event->fresh()->is_cancelled);
        Bus::assertNotDispatchedSync(SyncEventToGoogleCalendar::class);
    }

    public function test_delete_hard_deletes_without_dispatching_outbound(): void
    {
        $role = $this->syncingRole('delete');
        $event = $this->createEvent($role);
        $id = $event->id;

        Bus::fake();
        $outcome = $event->applyInboundDeletion($role->calendarDeleteAction());

        $this->assertSame('deleted', $outcome);
        $this->assertNull(Event::find($id));
        Bus::assertNotDispatchedSync(SyncEventToGoogleCalendar::class);
    }

    public function test_normal_delete_still_dispatches_outbound(): void
    {
        // Control: proves the "not dispatched" assertions above are meaningful (the hook normally pushes).
        $role = $this->syncingRole('delete');
        $event = $this->createEvent($role);

        Bus::fake();
        $event->delete();

        Bus::assertDispatchedSync(SyncEventToGoogleCalendar::class);
    }

    public function test_delete_is_guarded_to_cancel_when_event_has_sales(): void
    {
        $role = $this->syncingRole('delete');
        $event = $this->createEvent($role);
        $this->createSale($event, $role, ['is_deleted' => false]);

        $outcome = $event->applyInboundDeletion($role->calendarDeleteAction());

        $this->assertSame('guarded_cancelled', $outcome);
        $this->assertNotNull(Event::find($event->id));
        $this->assertTrue((bool) $event->fresh()->is_cancelled);
    }

    public function test_delete_removes_event_that_has_an_attached_venue_role(): void
    {
        // Regression: inbound events auto-attach a venue role, so a role-count guard would wrongly
        // block the common case. Deletion must still go through.
        $role = $this->syncingRole('delete');
        $event = $this->createEvent($role);

        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Hall']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->assertGreaterThan(1, $event->fresh()->roles()->count());

        $outcome = $event->applyInboundDeletion($role->calendarDeleteAction());

        $this->assertSame('deleted', $outcome);
        $this->assertNull(Event::find($event->id));
    }

    public function test_role_edit_renders_the_delete_action_control(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['google_token' => 'tok', 'google_refresh_token' => 'ref'])->save();
        $role = $this->createRole($owner, 'venue', [
            'sync_direction' => 'from',
            'calendar_delete_action' => 'delete',
        ]);

        $response = $this->actingAs($owner)->get('/'.$role->subdomain.'/edit');

        $response->assertOk();
        $response->assertSee('id="google-delete-action-group"', false);
        $response->assertSee(__('messages.calendar_delete_action'), false);
        // 'delete' is the selected option, so the amber warning block + its text render.
        $response->assertSee('data-delete-action-warning', false);
        $response->assertSee(__('messages.calendar_delete_warning'), false);
    }

    public function test_delete_action_control_renders_once_when_both_calendars_connected(): void
    {
        // Regression: two visible copies would share one radio name and collide. Only the Google
        // copy should render when Google is connected.
        $owner = $this->createOwner();
        $owner->forceFill([
            'google_token' => 'tok', 'google_refresh_token' => 'ref',
            'microsoft_token' => 'ms-tok', 'microsoft_refresh_token' => 'ms-ref',
            'microsoft_token_expires_at' => now()->addHour(),
        ])->save();
        $role = $this->createRole($owner, 'venue', [
            'sync_direction' => 'from',
            'microsoft_sync_direction' => 'from',
            'calendar_delete_action' => 'cancel',
        ]);

        $response = $this->actingAs($owner)->get('/'.$role->subdomain.'/edit');

        $response->assertOk();
        // Assert on the rendered element id (id="...") - the group name also appears as a bare
        // string literal in the JS, so only the id= form distinguishes an actual rendered control.
        $content = $response->getContent();
        $this->assertStringContainsString('id="google-delete-action-group"', $content);
        $this->assertStringNotContainsString('id="microsoft-delete-action-group"', $content);
    }

    public function test_microsoft_removed_item_deletes_the_local_event(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'microsoft_sync_direction' => 'from',
            'calendar_delete_action' => 'delete',
        ]);
        $event = $this->createEvent($role);

        MicrosoftCalendarSync::create([
            'user_id' => $owner->id,
            'event_id' => $event->id,
            'role_id' => $role->id,
            'microsoft_event_id' => 'ms-del-1',
        ]);

        $service = app(MicrosoftCalendarService::class);
        $method = new \ReflectionMethod(MicrosoftCalendarService::class, 'processDeltaItem');
        $method->setAccessible(true);

        $outcome = $method->invoke($service, ['@removed' => ['reason' => 'deleted'], 'id' => 'ms-del-1'], $role, null);

        $this->assertSame('skipped', $outcome);
        $this->assertNull(Event::find($event->id));
    }
}
