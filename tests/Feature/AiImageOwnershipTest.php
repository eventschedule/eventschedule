<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Utils\AiImageIssuance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The AI image a save posts back by name - ai_profile_image, ai_header_image and
 * ai_background_image on the schedule form, ai_flyer_image on the event form - is stored only when
 * this app issued it to the schedule being saved (AiImageIssuance).
 *
 * The name used to be stored as posted, and the image it replaced deleted. So an owner could post
 * ANOTHER schedule's filename: the next replace, delete or save of their own image then deleted
 * that schedule's file. For flyers the check was a shape, flyer_*.png, which an uploaded flyer
 * matches too, while real AI flyers saved as JPEG or WebP were refused.
 *
 * Every file here lives on a faked disk; nothing touches real storage.
 */
class AiImageOwnershipTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default'));
    }

    private function path(string $name): string
    {
        return config('filesystems.default') == 'local' ? 'public/'.$name : $name;
    }

    /** A real image on the faked disk, under a name shaped like the generator's. */
    private function storedImage(string $slot, string $extension = 'png'): string
    {
        $name = $slot.'_'.strtolower(Str::random(32)).'.'.$extension;

        ob_start();
        imagepng(imagecreatetruecolor(8, 8));
        Storage::put($this->path($name), ob_get_clean());

        return $name;
    }

    private function saveSchedule(User $owner, Role $role, array $payload)
    {
        return $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
            'name' => $role->name,
            'timezone' => $role->timezone,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
        ], $payload));
    }

    private function eventOverrides(array $overrides): array
    {
        // A future date, so the save does not move the event into the past.
        return array_merge(['starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00'], $overrides);
    }

    /** @return array<string, array{0: string, 1: string, 2: string}> */
    public static function slots(): array
    {
        return [
            'profile' => ['profile', 'ai_profile_image', 'profile_image_url'],
            'header' => ['header', 'ai_header_image', 'header_image_url'],
            'background' => ['background', 'ai_background_image', 'background_image_url'],
        ];
    }

    #[DataProvider('slots')]
    public function test_a_name_issued_to_another_schedule_is_ignored(string $slot, string $input, string $column): void
    {
        $theirOwner = $this->createOwner();
        $theirs = $this->storedImage($slot);
        $theirSchedule = $this->createRole($theirOwner, 'venue', [$column => $theirs]);
        // Genuinely issued, to them: only the schedule it was issued to may store it.
        AiImageIssuance::record($theirs, $theirSchedule->id, $theirOwner->id);

        $owner = $this->createOwner();
        $current = $this->storedImage($slot);
        $role = $this->createRole($owner, 'venue', [$column => $current]);

        $this->saveSchedule($owner, $role, [$input => $theirs])
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.ai_image_not_applied'));

        $this->assertSame($current, $role->fresh()->getRawOriginal($column), 'the refused name is not stored');
        $this->assertTrue(Storage::exists($this->path($current)), 'a refused name must not cost the current image');
        $this->assertTrue(Storage::exists($this->path($theirs)), 'the other schedule\'s file');
        $this->assertSame($theirs, $theirSchedule->fresh()->getRawOriginal($column));
    }

    public function test_a_name_never_issued_is_ignored(): void
    {
        $theirs = $this->storedImage('profile');
        $theirSchedule = $this->createRole($this->createOwner(), 'venue', ['profile_image_url' => $theirs]);

        $owner = $this->createOwner();
        $current = $this->storedImage('profile');
        $role = $this->createRole($owner, 'venue', ['profile_image_url' => $current]);

        $this->saveSchedule($owner, $role, ['ai_profile_image' => $theirs])
            ->assertSessionHas('error', __('messages.ai_image_not_applied'));

        $this->assertSame($current, $role->fresh()->getRawOriginal('profile_image_url'));
        $this->assertTrue(Storage::exists($this->path($current)));
        $this->assertTrue(Storage::exists($this->path($theirs)));

        // Nor can a later delete of this schedule's photo reach their file.
        $this->actingAs($owner)->delete(route('role.delete_image', ['subdomain' => $role->subdomain, 'image_type' => 'profile']));
        $this->assertTrue(Storage::exists($this->path($theirs)));
        $this->assertSame($theirs, $theirSchedule->fresh()->getRawOriginal('profile_image_url'));
    }

    #[DataProvider('slots')]
    public function test_an_issued_name_replaces_the_current_image(string $slot, string $input, string $column): void
    {
        $owner = $this->createOwner();
        $current = $this->storedImage($slot);
        $role = $this->createRole($owner, 'venue', [$column => $current]);

        $generated = $this->storedImage($slot, 'jpg');
        AiImageIssuance::record($generated, $role->id, $owner->id);

        $this->saveSchedule($owner, $role, [$input => $generated])
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.updated_schedule'))
            ->assertSessionMissing('error');

        $role->refresh();
        $this->assertSame($generated, $role->getRawOriginal($column));
        $this->assertTrue(Storage::exists($this->path($generated)));
        $this->assertFalse(Storage::exists($this->path($current)), 'the image it replaces is deleted, as it always was');

        if ($slot === 'background') {
            $this->assertSame('image', $role->background);
        }
    }

    public function test_posting_the_stored_name_again_changes_nothing(): void
    {
        // The form still holding the name after it was saved: a second submit, or the page
        // restored by the back button. The old code deleted the file and stored its name.
        $owner = $this->createOwner();
        $current = $this->storedImage('header');
        $role = $this->createRole($owner, 'venue', ['header_image_url' => $current, 'header_image' => '']);

        $this->saveSchedule($owner, $role, ['ai_header_image' => $current])
            ->assertSessionHas('message', __('messages.updated_schedule'))
            ->assertSessionMissing('error');

        $this->assertSame($current, $role->fresh()->getRawOriginal('header_image_url'));
        $this->assertTrue(Storage::exists($this->path($current)));
    }

    public function test_a_path_or_a_line_break_is_ignored_even_when_recorded(): void
    {
        $owner = $this->createOwner();
        $current = $this->storedImage('profile');
        $role = $this->createRole($owner, 'venue', ['profile_image_url' => $current]);
        $theirs = $this->storedImage('profile');

        foreach (['../'.$theirs, 'profile/../'.$theirs, $current."\n".$theirs] as $value) {
            // As if issued, so only the name's shape can refuse it.
            AiImageIssuance::record($value, $role->id, $owner->id);

            $this->saveSchedule($owner, $role, ['ai_profile_image' => $value])
                ->assertSessionHas('error', __('messages.ai_image_not_applied'));

            $this->assertSame($current, $role->fresh()->getRawOriginal('profile_image_url'), json_encode($value));
            $this->assertTrue(Storage::exists($this->path($current)));
            $this->assertTrue(Storage::exists($this->path($theirs)));
        }
    }

    public function test_another_events_uploaded_flyer_is_ignored(): void
    {
        // An uploaded flyer is flyer_<32>.png too, which is all the old check asked for.
        $theirs = $this->storedImage('flyer');
        $theirEvent = $this->createEvent($this->createRole($this->createOwner(), 'venue'), ['flyer_image_url' => $theirs]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $current = $this->storedImage('flyer');
        $event = $this->createEvent($role, ['flyer_image_url' => $current]);

        $this->putUpdateEvent($owner, $role, $event, $this->eventOverrides(['ai_flyer_image' => $theirs]))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.ai_image_not_applied'));

        $this->assertSame($current, $event->fresh()->getRawOriginal('flyer_image_url'));
        $this->assertTrue(Storage::exists($this->path($current)));
        $this->assertTrue(Storage::exists($this->path($theirs)));
        $this->assertSame($theirs, $theirEvent->fresh()->getRawOriginal('flyer_image_url'));
    }

    public function test_a_flyer_issued_to_another_schedule_is_ignored(): void
    {
        $theirSchedule = $this->createRole($this->createOwner(), 'venue');
        $theirs = $this->storedImage('flyer', 'webp');
        AiImageIssuance::record($theirs, $theirSchedule->id, $theirSchedule->user_id);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);

        $this->putUpdateEvent($owner, $role, $event, $this->eventOverrides(['ai_flyer_image' => $theirs]))
            ->assertSessionHas('error', __('messages.ai_image_not_applied'));

        $this->assertNull($event->fresh()->getRawOriginal('flyer_image_url'));
        $this->assertTrue(Storage::exists($this->path($theirs)));
    }

    public function test_an_issued_jpeg_flyer_replaces_the_current_one(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $current = $this->storedImage('flyer');
        $event = $this->createEvent($role, ['flyer_image_url' => $current]);

        // The old check allowed .png only, so an AI flyer saved as a JPEG never stuck.
        $generated = $this->storedImage('flyer', 'jpg');
        AiImageIssuance::record($generated, $role->id, $owner->id);

        $this->putUpdateEvent($owner, $role, $event, $this->eventOverrides(['ai_flyer_image' => $generated]))
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertSame($generated, $event->fresh()->getRawOriginal('flyer_image_url'));
        $this->assertFalse(Storage::exists($this->path($current)));
    }

    public function test_a_new_event_takes_its_issued_flyer(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // Generated on the create form, before the event exists: keyed to the schedule alone.
        $generated = $this->storedImage('flyer', 'webp');
        AiImageIssuance::record($generated, $role->id, $owner->id);

        $this->postCreateEvent($owner, $role, $this->eventOverrides(['name' => 'Flyer Night', 'ai_flyer_image' => $generated]))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_created'));

        $this->assertSame($generated, $this->latestEvent()->getRawOriginal('flyer_image_url'));
    }

    public function test_a_new_event_is_created_without_a_flyer_it_was_not_issued(): void
    {
        $theirs = $this->storedImage('flyer');
        $this->createEvent($this->createRole($this->createOwner(), 'venue'), ['flyer_image_url' => $theirs]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->postCreateEvent($owner, $role, $this->eventOverrides(['name' => 'Borrowed Flyer', 'ai_flyer_image' => $theirs]))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.ai_image_not_applied'));

        $created = $this->latestEvent();
        $this->assertSame('Borrowed Flyer', $created->name, 'the event itself is still created');
        $this->assertNull($created->getRawOriginal('flyer_image_url'));
        $this->assertTrue(Storage::exists($this->path($theirs)));
    }

    public function test_a_flyer_path_is_ignored_even_when_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $current = $this->storedImage('flyer');
        $event = $this->createEvent($role, ['flyer_image_url' => $current]);
        $theirs = $this->storedImage('flyer');

        $value = 'flyer_'.strtolower(Str::random(32)).'.png/../../'.$theirs;
        AiImageIssuance::record($value, $role->id, $owner->id);

        $this->putUpdateEvent($owner, $role, $event, $this->eventOverrides(['ai_flyer_image' => $value]))
            ->assertSessionHas('error', __('messages.ai_image_not_applied'));

        $this->assertSame($current, $event->fresh()->getRawOriginal('flyer_image_url'));
        $this->assertTrue(Storage::exists($this->path($current)));
        $this->assertTrue(Storage::exists($this->path($theirs)));
    }
}
