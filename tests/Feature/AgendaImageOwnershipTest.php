<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\AiImageIssuance;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The agenda photo an agenda scan keeps on the event CREATE form is posted back by name
 * (agenda_image_url), and EventController::store() used to take any name shaped like agenda_*.
 * Deleting the event's agenda image deletes the file its column names, so an owner could post
 * another event's agenda file and then delete it. A name is now taken only when the scan issued it
 * to the schedule the event is created on (AiImageIssuance), and only on Enterprise, where agenda
 * scanning lives.
 *
 * Every file here lives on a faked disk; nothing touches real storage.
 */
class AgendaImageOwnershipTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default'));

        // No AI key: the scan then finds no parts, and still keeps the photo it was asked to keep,
        // without a network call.
        config(['services.openai.api_key' => null, 'services.google.gemini_key' => null]);
    }

    private function path(string $name): string
    {
        return config('filesystems.default') == 'local' ? 'public/'.$name : $name;
    }

    private function storedAgenda(): string
    {
        $name = 'agenda_'.strtolower(Str::random(32)).'.png';

        ob_start();
        imagepng(imagecreatetruecolor(8, 8));
        Storage::put($this->path($name), ob_get_clean());

        return $name;
    }

    /** Scans an agenda photo on $role's event form, keeping the photo; returns the response. */
    private function scan(User $owner, Role $role, ?Event $event = null)
    {
        return $this->actingAs($owner)->post(url('/'.$role->subdomain.'/parse-event-parts'), array_filter([
            'parts_image' => UploadedFile::fake()->image('agenda.jpeg', 40, 40),
            'save_agenda_image' => '1',
            'event_id' => $event ? UrlUtils::encodeId($event->id) : null,
        ]));
    }

    private function create(User $owner, Role $role, string $agenda)
    {
        return $this->postCreateEvent($owner, $role, [
            'name' => 'Agenda Night '.Str::random(4),
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00',
            'agenda_image_url' => $agenda,
        ]);
    }

    public function test_the_photo_a_scan_keeps_goes_to_the_new_event_on_that_schedule_only(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $other = $this->createRole($owner, 'venue');

        $name = $this->scan($owner, $role)->assertOk()->json('agenda_image_url');
        $this->assertMatchesRegularExpression('/^agenda_[a-z0-9]{32}\.jpeg$/', $name);
        $this->assertTrue(Storage::exists($this->path($name)));

        // The same owner's other schedule was not issued it.
        $this->create($owner, $other, $name)
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.agenda_image_not_applied'));
        $this->assertNull($this->latestEvent()->getRawOriginal('agenda_image_url'));

        $this->create($owner, $role, $name)
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_created'));
        $this->assertSame($name, $this->latestEvent()->getRawOriginal('agenda_image_url'));
    }

    public function test_another_events_agenda_file_is_refused_and_survives(): void
    {
        $theirs = $this->storedAgenda();
        $theirEvent = $this->createEvent($this->createRole($this->createOwner(), 'venue'), ['agenda_image_url' => $theirs]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->create($owner, $role, $theirs)
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.agenda_image_not_applied'));

        $created = $this->latestEvent();
        $this->assertNull($created->getRawOriginal('agenda_image_url'));

        // So deleting the new event's agenda image has nothing of theirs to delete.
        $this->actingAs($owner)->delete(route('event.delete_image', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($created->id), 'image_type' => 'agenda']));
        $this->assertTrue(Storage::exists($this->path($theirs)));
        $this->assertSame($theirs, $theirEvent->fresh()->getRawOriginal('agenda_image_url'));
    }

    public function test_a_scan_for_an_existing_event_issues_nothing_a_new_event_can_claim(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);

        $this->scan($owner, $role, $event)->assertOk();
        $name = $event->fresh()->getRawOriginal('agenda_image_url');
        $this->assertNotNull($name, 'the scan gave the existing event its photo directly');

        $this->create($owner, $role, $name)->assertSessionHas('error', __('messages.agenda_image_not_applied'));
        $this->assertNull($this->latestEvent()->getRawOriginal('agenda_image_url'), 'two events would share one file');
    }

    public function test_a_create_that_fails_before_storing_the_agenda_can_be_posted_again(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $name = $this->scan($owner, $role)->assertOk()->json('agenda_image_url');

        // Fails the first save that writes the name, as a database or storage error would: after
        // store() accepted it, before the new event holds it.
        $failed = false;
        Event::saving(function (Event $event) use ($name, &$failed) {
            if (! $failed && $event->isDirty('agenda_image_url') && ($event->getAttributes()['agenda_image_url'] ?? null) === $name) {
                $failed = true;

                throw new \RuntimeException('The save failed before the event held the image.');
            }
        });

        $this->create($owner, $role, $name)->assertStatus(500);
        $this->assertNull($this->latestEvent()->getRawOriginal('agenda_image_url'), 'fixture: the failed save stored nothing');

        // Taking the name used to use it up, so this was told to scan the agenda again.
        $this->create($owner, $role, $name)
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_created'));
        $this->assertSame($name, $this->latestEvent()->getRawOriginal('agenda_image_url'));

        // Used up once that event held it, so no other event can share the file.
        $this->create($owner, $role, $name)->assertSessionHas('error', __('messages.agenda_image_not_applied'));
        $this->assertNull($this->latestEvent()->getRawOriginal('agenda_image_url'));
    }

    public function test_a_new_event_that_refuses_both_images_says_so_about_both(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // Neither was issued to this schedule. store() used to return on the flyer's refusal, and
        // the agenda's was never said.
        $this->postCreateEvent($owner, $role, [
            'name' => 'Borrowed Everything',
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00',
            'ai_flyer_image' => 'flyer_'.strtolower(Str::random(32)).'.png',
            'agenda_image_url' => $this->storedAgenda(),
        ])
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.ai_image_not_applied').' '.__('messages.agenda_image_not_applied'));

        $created = $this->latestEvent();
        $this->assertSame('Borrowed Everything', $created->name, 'the event itself is still created');
        $this->assertNull($created->getRawOriginal('flyer_image_url'));
        $this->assertNull($created->getRawOriginal('agenda_image_url'));
    }

    public function test_a_schedule_without_agenda_scanning_takes_no_agenda_image(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $this->assertFalse($role->fresh()->isEnterprise());

        // Issued while the plan still had agenda scanning, say.
        $name = $this->storedAgenda();
        AiImageIssuance::record($name, $role->id, $owner->id);

        $this->create($owner, $role, $name)->assertRedirect();

        $this->assertNull($this->latestEvent()->getRawOriginal('agenda_image_url'));
    }
}
