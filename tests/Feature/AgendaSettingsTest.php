<?php

namespace Tests\Feature;

use App\Models\EventPart;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The agenda tab's settings (show times, show descriptions, keep the photo) belong to the schedule
 * and are remembered for its next event.
 *
 * EventController wrote all three from the request on EVERY event save, with boolean(): so a save
 * from a page that did not render the inputs switched them off for the whole schedule. That page
 * is every install with no AI key, where the inputs sat inside the import block - and where no
 * control existed to switch them back on. And it wrote them with $role->save(), which runs the
 * schedule's geocode hook and moves the updated_at its guest page publishes as <lastmod>, on every
 * event save (CLAUDE.md, the geocoding rule).
 */
class AgendaSettingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    private function settings(Role $role): array
    {
        return (array) DB::table('roles')->where('id', $role->id)->first(['agenda_show_times', 'agenda_show_description', 'agenda_save_image']);
    }

    public function test_a_save_that_sends_no_agenda_settings_leaves_the_schedules_alone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['agenda_show_times' => true, 'agenda_show_description' => true, 'agenda_save_image' => true]);
        $event = $this->createEvent($role);

        $this->putUpdateEvent($owner, $role, $event)->assertRedirect();
        $this->assertSame(['agenda_show_times' => 1, 'agenda_show_description' => 1, 'agenda_save_image' => 1], $this->settings($role), 'an update');

        $this->postCreateEvent($owner, $role)->assertRedirect();
        $this->assertSame(['agenda_show_times' => 1, 'agenda_show_description' => 1, 'agenda_save_image' => 1], $this->settings($role), 'a new event');
    }

    public function test_a_setting_that_was_never_chosen_stays_unchosen(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);

        $this->putUpdateEvent($owner, $role, $event)->assertRedirect();

        // null is "never chosen", which the form reads as shown: false would hide the fields.
        $this->assertSame(['agenda_show_times' => null, 'agenda_show_description' => null, 'agenda_save_image' => null], $this->settings($role));
    }

    public function test_a_setting_the_form_sent_is_remembered(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['agenda_show_times' => true, 'agenda_show_description' => true]);
        $event = $this->createEvent($role);

        $this->putUpdateEvent($owner, $role, $event, ['agenda_show_times' => '0', 'agenda_show_description' => '1', 'save_agenda_image' => '1'])->assertRedirect();
        $this->assertSame(['agenda_show_times' => 0, 'agenda_show_description' => 1, 'agenda_save_image' => 1], $this->settings($role));

        $this->postCreateEvent($owner, $role, ['agenda_show_times' => '1', 'agenda_show_description' => '0', 'save_agenda_image' => '0'])->assertRedirect();
        $this->assertSame(['agenda_show_times' => 1, 'agenda_show_description' => 0, 'agenda_save_image' => 0], $this->settings($role));
    }

    public function test_remembering_them_does_not_resave_the_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);
        DB::table('roles')->where('id', $role->id)->update(['updated_at' => '2026-01-02 03:04:05']);

        $this->putUpdateEvent($owner, $role, $event, ['agenda_show_times' => '0', 'agenda_show_description' => '0', 'save_agenda_image' => '0'])->assertRedirect();

        $this->assertSame(0, $this->settings($role)['agenda_show_times'], 'sanity check: the setting was written');
        $this->assertSame('2026-01-02 03:04:05', (string) DB::table('roles')->where('id', $role->id)->value('updated_at'), 'the guest page\'s <lastmod> did not move for an editor\'s preference');
    }

    /**
     * A part keeps what a save did not send. The form used to leave a part's time fields off the
     * page when "Show times" was unticked, the save read that as "no times", and every part's
     * times were deleted: hiding a column deleted its contents. The form now says so when it means
     * it (empty values, announced by the save bar), and the server keeps what it was not sent.
     */
    public function test_a_part_keeps_the_fields_a_save_did_not_send(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);
        $part = EventPart::create(['event_id' => $event->id, 'name' => 'Doors', 'start_time' => '19:00', 'end_time' => '20:00', 'description' => 'Bar opens', 'sort_order' => 0]);

        $this->putUpdateEvent($owner, $role, $event, ['event_parts' => [['id' => $part->id, 'name' => 'Doors open']]])->assertRedirect();

        $fresh = EventPart::find($part->id);
        $this->assertSame('Doors open', $fresh->name, 'sanity check: the part was saved');
        $this->assertSame(['19:00', '20:00', 'Bar opens'], [$fresh->start_time, $fresh->end_time, $fresh->description]);
    }

    public function test_a_part_loses_a_field_that_was_sent_empty(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);
        $part = EventPart::create(['event_id' => $event->id, 'name' => 'Doors', 'start_time' => '19:00', 'end_time' => '20:00', 'description' => 'Bar opens', 'sort_order' => 0]);

        // What the form posts with "Show times" off: the fields, empty.
        $this->putUpdateEvent($owner, $role, $event, ['event_parts' => [['id' => $part->id, 'name' => 'Doors', 'start_time' => '', 'end_time' => '', 'description' => 'Bar opens']]])->assertRedirect();

        $fresh = EventPart::find($part->id);
        $this->assertNull($fresh->start_time);
        $this->assertNull($fresh->end_time);
        $this->assertSame('Bar opens', $fresh->description);
    }
}
