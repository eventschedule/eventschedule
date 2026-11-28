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
        $this->post(route('role.participants.item', [...$params, 'links']), ['url' => 'https://band.test/social'])->assertSessionHasNoErrors();
        $this->put(route('role.participants.promotion', $params), ['promotion' => 'Private preview'])->assertSessionHasNoErrors();
        $this->get(route('role.view_admin', [$schedule->subdomain, 'participants', 'entry' => $params[1]]))->assertOk()->assertSee('Tour manager')->assertSee('Private preview')->assertSee('https://band.test/social')->assertSee('Alex');
        $this->assertNull($band->fresh()->email);
        $this->delete(route('role.participants.destroy', $params))->assertRedirect();
        $this->assertDatabaseCount('backstage_contacts', 0);
        $this->assertSame(2, DB::table('event_role')->where('role_id', $band->id)->count());
        $this->get(route('role.view_admin', [$schedule->subdomain, 'participants']))->assertOk()->assertSee('Evening Trio'); // eligible to add again
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
            ->assertSee('entry='.$params[1], false)->assertSee('name="participant" value="'.UrlUtils::encodeId($band->id).'"', false)->assertDontSee('<select name="participant"', false);
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
