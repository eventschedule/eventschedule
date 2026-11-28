<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class ParticipantDirectoryTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function add(Role $schedule, array $data): array
    {
        $this->post(route('role.participants.store', $schedule->subdomain), $data)->assertRedirect()->assertSessionHasNoErrors();
        $entry = DB::table('backstage_entries')->where('schedule_id', $schedule->id)->latest('id')->firstOrFail();

        return [$schedule->subdomain, UrlUtils::encodeId($entry->id)];
    }

    public function test_a_private_record_survives_repeat_bookings_and_removal_preserves_both_lineups(): void
    {
        $owner = $this->createOwner();
        $schedule = $this->createFreeRole($owner);
        $this->actingAs($owner);
        $params = $this->add($schedule, ['name' => 'Evening Trio']);
        $band = Role::where('name', 'Evening Trio')->firstOrFail();
        $this->get(route('event.create', $schedule->subdomain))->assertOk()->assertSee('Evening Trio');
        foreach ([1, 2] as $i) {
            $this->createEvent($schedule, ['creator_role_id' => $schedule->id, 'name' => 'Show '.$i])->roles()->attach($band->id, ['is_accepted' => true]);
        }
        $contacts = route('role.participants.item', [...$params, 'contacts']);
        $this->post($contacts, ['label' => 'Booking'])->assertSessionHasErrors('name');
        $this->post($contacts, ['name' => 'Sam', 'label' => 'Tour manager', 'email' => 'manager@test.invalid', 'phone' => '+1 555 0100', 'website' => 'https://manager.test'])->assertSessionHasNoErrors();
        $this->post($contacts, ['name' => 'Alex'])->assertSessionHasNoErrors();
        $this->post(route('role.participants.item', [...$params, 'links']), ['platform' => 'instagram', 'url' => 'https://band.test/social'])->assertSessionHasNoErrors();
        $this->put(route('role.participants.promotion', $params), ['promotion' => 'Private preview'])->assertSessionHasNoErrors();
        $this->get(route('role.view_admin', [$schedule->subdomain, 'participants', 'entry' => $params[1]]))->assertOk()->assertSee('Tour manager')->assertSee('Private preview')->assertSee('https://band.test/social')->assertSee('Instagram')->assertSee('Alex');
        $this->assertNull($band->fresh()->email);
        $this->delete(route('role.participants.destroy', $params))->assertRedirect();
        $this->assertDatabaseCount('backstage_contacts', 0);
        $this->assertSame(2, DB::table('event_role')->where('role_id', $band->id)->count());
        $this->get(route('role.view_admin', [$schedule->subdomain, 'participants']))->assertOk()->assertSee('Evening Trio'); // eligible to add again
    }

    public function test_extra_contacts_are_folded_under_show_more_while_add_contact_stays_visible(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner);
        $this->actingAs($owner);
        [, $entry] = $this->add($venue, ['name' => 'Evening Trio']);
        $record = DB::table('backstage_entries')->where('schedule_id', $venue->id)->firstOrFail();
        for ($i = 1; $i <= 5; $i++) {
            DB::table('backstage_contacts')->insert(['entry_id' => $record->id, 'name' => 'Contact '.$i]);
        }

        $this->get(route('role.view_admin', [$venue->subdomain, 'participants', 'entry' => $entry]))
            ->assertOk()->assertSee(__('messages.pending_action_show_more', ['count' => 1]))
            ->assertSee('Contact 1')->assertSee('Contact 4')->assertSee('Contact 5')
            ->assertSee('name="name"', false)->assertSee('value="'.route('role.participants.item', [$venue->subdomain, $entry, 'contacts']).'"', false);
    }

    public function test_schedule_editor_can_update_a_listed_participants_shared_profile(): void
    {
        $scheduleOwner = $this->createOwner();
        $participantOwner = $this->createOwner();
        $venue = $this->createRole($scheduleOwner);
        $participant = $this->createRole($participantOwner, 'talent', ['email' => 'before@trio.test']);
        $entryId = DB::table('backstage_entries')->insertGetId([
            'schedule_id' => $venue->id, 'participant_id' => $participant->id,
        ]);
        $entry = UrlUtils::encodeId($entryId);
        $this->actingAs($scheduleOwner);

        $this->get(route('role.view_admin', [$venue->subdomain, 'participants', 'entry' => $entry, 'edit' => 1]))
            ->assertOk()->assertSee('name="email"', false)->assertSee('value="before@trio.test"', false);
        $this->put(route('role.participants.basic_info', [$venue->subdomain, $entry]), [
            'email' => 'after@trio.test', 'phone' => '+15550123', 'website' => 'https://trio.test',
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('after@trio.test', $participant->fresh()->email);
    }

    public function test_basic_participant_details_use_the_shared_profile_record(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner);
        $video = 'https://youtu.be/dQw4w9WgXcQ';
        $band = $this->createRole($owner, 'talent', [
            'email' => 'booking@trio.test', 'phone' => '+1 555 0123', 'website' => 'https://trio.test',
            'youtube_links' => json_encode([['url' => $video]]),
        ]);
        $this->actingAs($owner);
        [, $entry] = $this->add($venue, ['participant' => UrlUtils::encodeId($band->id)]);
        $page = route('role.view_admin', [$venue->subdomain, 'participants', 'entry' => $entry]);
        $this->get($page)->assertOk()->assertSee('href="mailto:booking@trio.test"', false)->assertSee('href="tel:+15550123"', false)
            ->assertSee('href="https://trio.test"', false)->assertSee('href="'.$video.'"', false)->assertSee('edit=1', false)
            ->assertDontSee('name="email"', false)->assertDontSee('textarea name="promotion"', false);
        $this->get($page.'&edit=1')->assertOk()->assertSee('value="booking@trio.test"', false)->assertSee('value="+15550123"', false)
            ->assertSee('value="https://trio.test"', false)->assertSee(__('messages.cancel'));
        $update = route('role.participants.basic_info', [$venue->subdomain, $entry]);
        $this->put($update, ['email' => 'manager@venue.test', 'phone' => '+15550199', 'website' => 'https://manager.test'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('manager@venue.test', $band->fresh()->email);
        $this->assertSame('+15550199', $band->fresh()->phone);
        $this->assertSame('https://manager.test', $band->fresh()->website);
        $this->assertDatabaseHas('backstage_entries', ['id' => DB::table('backstage_entries')->where('schedule_id', $venue->id)->value('id')]);
        $band->update(['email' => 'new-booking@trio.test']);
        $this->get($page)->assertOk()->assertSee('href="mailto:new-booking@trio.test"', false);
        $this->assertDatabaseCount('backstage_contacts', 0);
        $this->assertDatabaseCount('backstage_links', 0);
    }

    public function test_profile_video_row_obeys_participant_permissions_and_keeps_other_videos(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner);
        $first = 'https://youtu.be/dQw4w9WgXcQ';
        $next = 'https://youtu.be/abcdefghijk';
        $replacement = 'https://youtu.be/01234567890';
        $band = $this->createRole($owner, 'talent', ['youtube_links' => json_encode([['url' => $first], ['url' => $next]])]);
        $this->actingAs($owner);
        $params = $this->add($venue, ['participant' => UrlUtils::encodeId($band->id)]);
        $action = route('role.participants.profile_video', $params);
        $page = route('role.view_admin', [$venue->subdomain, 'participants', 'entry' => $params[1]]);
        $this->get($page.'?edit=1')->assertOk()->assertSee($action);
        $this->post($action, ['url' => $replacement, 'original_url' => $first])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($replacement, $band->fresh()->getFirstVideoUrl());
        $other = $this->createOwner();
        $venue->users()->attach($other->id, ['level' => 'admin']);
        $this->actingAs($other)->get($page)->assertOk()->assertSee($replacement)->assertDontSee($action);
        $this->delete($action, ['original_url' => $replacement])->assertForbidden();
        $this->actingAs($owner)->delete($action, ['original_url' => $replacement])->assertRedirect();
        $this->assertSame($next, $band->fresh()->getFirstVideoUrl());
    }

    public function test_historical_lineups_get_one_private_record_for_the_owner_not_the_listing_curator(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner);
        $person = $this->createRole($owner, 'talent');
        $curator = $this->createRole($owner, 'curator');
        foreach ([null, $venue->id] as $creator) {
            $event = $this->createEvent($venue, ['creator_role_id' => $creator]);
            $event->roles()->attach([$person->id, $curator->id], ['is_accepted' => true]);
        }
        $migration = require database_path('migrations/2026_11_28_000001_register_existing_lineup_participants.php');
        $migration->up();
        $key = ['schedule_id' => $venue->id, 'participant_id' => $person->id];
        $this->assertDatabaseHas('backstage_entries', $key);
        DB::table('backstage_entries')->where($key)->update(['promotion' => 'Keep me']);
        $migration->up();
        $this->assertDatabaseHas('backstage_entries', $key + ['promotion' => 'Keep me']);
        $this->assertDatabaseMissing('backstage_entries', ['schedule_id' => $curator->id]);
    }

    public function test_name_search_lists_existing_and_available_participants_without_a_dropdown(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner);
        $this->actingAs($owner);
        $params = $this->add($venue, ['name' => 'Night Quartet']);
        $band = $this->createRole($owner, 'talent', ['name' => 'Night Trio']);
        $this->createRole($owner, 'talent', ['name' => 'Morning Duo']);
        $this->createRole($this->createOwner(), 'talent', ['name' => 'Night Stranger']);
        $url = route('role.view_admin', [$venue->subdomain, 'participants']);
        $this->get($url.'?q=Night')->assertOk()->assertSee('Night Quartet')->assertSee('Night Trio')->assertDontSee('Morning Duo')->assertDontSee('Night Stranger')
            ->assertSee('entry='.$params[1], false)->assertSee('edit=1', false)->assertSee(__('messages.view'))->assertSee(__('messages.edit'))
            ->assertSee('name="participant" value="'.UrlUtils::encodeId($band->id).'"', false)->assertDontSee('<select name="participant"', false);
        $this->get($url.'?q=%25')->assertOk()->assertSee(__('messages.no_results_found'))->assertDontSee('Night Quartet');
    }

    public function test_private_file_download_quota_and_cross_schedule_boundaries(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner);
        $other = $this->createRole($owner, 'curator');
        $band = $this->createRole($owner, 'talent');
        $this->actingAs($owner);
        $params = $this->add($venue, ['participant' => UrlUtils::encodeId($band->id)]);
        $second = $this->add($other, ['participant' => UrlUtils::encodeId($band->id)]);
        $files = route('role.participants.item', [...$params, 'files']);
        $pdf = "%PDF-1.4\n".str_repeat('private rider ', 9000)."\n%%EOF";
        $upload = fn () => UploadedFile::fake()->createWithContent('rider.pdf', $pdf);
        $this->post($files, ['file' => UploadedFile::fake()->createWithContent('notes.txt', 'plain text')])->assertSessionHasErrors('file');
        $this->post($files, ['file' => $upload(), 'label' => 'Technical'])->assertRedirect()->assertSessionHasNoErrors();
        $file = UrlUtils::encodeId(DB::table('backstage_files')->value('id'));
        $download = route('role.participants.download', [...$params, $file]);
        $this->get(route('role.view_admin', [$venue->subdomain, 'participants', 'entry' => $params[1]]))
            ->assertOk()->assertSee('Technical')->assertSee('rider.pdf')->assertSee($download);
        $this->assertSame($pdf, $this->get($download)->assertOk()->streamedContent());
        $this->get(route('role.participants.download', [...$second, $file]))->assertNotFound();
        $this->delete(route('role.participants.item', [...$second, 'files', $file]))->assertNotFound();
        $this->put(route('role.participants.promotion', [$other->subdomain, $params[1]]), ['promotion' => 'wrong schedule'])->assertNotFound();
        config(['backstage.schedule_mb' => 0]);
        $this->post($files, ['file' => $upload()])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('backstage_files', 1);
        $viewer = $this->createOwner();
        $venue->users()->attach($viewer->id, ['level' => 'viewer']);
        $this->actingAs($viewer)->get($download)->assertForbidden();
        $this->post($files, ['file' => $upload()])->assertForbidden();
        $this->get(route('role.view_admin', [$venue->subdomain, 'participants']))->assertRedirect();
        $this->actingAs($owner)->delete(route('role.participants.item', [...$params, 'files', $file]))->assertRedirect();
        $this->assertDatabaseCount('backstage_file_chunks', 0);
        config(['backstage.schedule_mb' => 1]);
        $this->post($files, ['file' => $upload()])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('backstage_files', 1);
    }
}
