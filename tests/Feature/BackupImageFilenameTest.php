<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A restored schedule never holds another row's image filename.
 *
 * An archive carries the schedule's image columns and sponsor lists as raw values. A local name in
 * them was meant to be cleared on restore, the files coming back under new names only when the
 * archive includes them - but the check read the accessors, which turn every filename into a full
 * URL, so every name was kept, and the schedule's sponsor logos were read with the wrong key and
 * never cleared, exported or restored at all. A kept name is shared with whichever row on this
 * install already holds it, so the restored schedule's next replace or delete removed that row's
 * file: a same-install restore took the original's images, and an archive naming someone else's
 * files took theirs.
 *
 * Every file here lives on a faked disk; nothing touches real storage.
 */
class BackupImageFilenameTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function setUp(): void
    {
        parent::setUp();

        // The image disk and the one the import reads its archive from.
        Storage::fake(config('filesystems.default'));
        Storage::fake('local');
    }

    private function path(string $name): string
    {
        return config('filesystems.default') == 'local' ? 'public/'.$name : $name;
    }

    private function storedImage(string $prefix): string
    {
        $name = $prefix.'_'.strtolower(Str::random(32)).'.png';

        ob_start();
        imagepng(imagecreatetruecolor(8, 8));
        Storage::put($this->path($name), ob_get_clean());

        return $name;
    }

    /** @return array{json: array, images: array<string, string>} */
    private function export(User $owner, Role $role, bool $images): array
    {
        $job = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $result = app(BackupService::class)->exportSchedules([$role->fresh()], $images, $job);

        // Through JSON, exactly as the archive carries it.
        return ['json' => json_decode(json_encode($result['json']), true), 'images' => $result['images']];
    }

    /** Restores the first schedule in $json for $owner, from an archive holding $images. */
    private function restore(User $owner, array $json, array $images = []): Role
    {
        $zipPath = 'backups/'.strtolower(Str::random(12)).'.zip';
        $full = Storage::disk('local')->path($zipPath);
        @mkdir(dirname($full), 0777, true);

        $zip = new \ZipArchive;
        $zip->open($full, \ZipArchive::CREATE);
        $zip->addFromString('backup.json', json_encode($json));
        foreach ($images as $key => $storagePath) {
            $zip->addFromString($key, Storage::get($storagePath));
        }
        $zip->close();

        $before = Role::max('id');
        $job = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing', 'file_path' => $zipPath]);
        $report = app(BackupService::class)->importSchedules($json, [0], $owner->id, $job);

        $this->assertArrayNotHasKey('error', $report[0], json_encode($report));

        return Role::where('id', '>', $before)->where('user_id', $owner->id)->latest('id')->firstOrFail();
    }

    private function makePro(Role $role): void
    {
        $role->forceFill(['plan_type' => 'enterprise', 'plan_expires' => now()->addYear()->format('Y-m-d')])->save();
    }

    private function sponsors(array $logos): string
    {
        return json_encode(array_map(fn ($logo, $i) => ['name' => 'Sponsor '.$i, 'logo' => $logo, 'url' => null, 'tier' => ''], $logos, array_keys($logos)));
    }

    public function test_a_restore_without_images_keeps_no_local_filename_and_every_external_url(): void
    {
        $owner = $this->createOwner();
        $profile = $this->storedImage('profile');
        $background = $this->storedImage('background');
        $logo = $this->storedImage('sponsor');
        $eventLogo = $this->storedImage('sponsor');
        $flyer = $this->storedImage('flyer');

        $role = $this->createRole($owner, 'curator', [
            'profile_image_url' => $profile,
            'header_image_url' => 'https://cdn.example.com/header.png',
            'background_image_url' => $background,
            'sponsor_logos' => $this->sponsors([$logo, 'https://cdn.example.com/logo.png']),
        ]);
        $this->createEvent($role, [
            'flyer_image_url' => $flyer,
            'sponsor_mode' => 'custom',
            'sponsor_logos' => $this->sponsors(['https://cdn.example.com/event-logo.png', $eventLogo]),
        ]);

        $restored = $this->restore($owner, $this->export($owner, $role, false)['json']);

        $this->assertNull($restored->getRawOriginal('profile_image_url'));
        $this->assertSame('https://cdn.example.com/header.png', $restored->getRawOriginal('header_image_url'));
        $this->assertNull($restored->getRawOriginal('background_image_url'));
        $this->assertSame([null, 'https://cdn.example.com/logo.png'], array_column(json_decode($restored->getRawOriginal('sponsor_logos'), true), 'logo'));
        $this->assertSame(['Sponsor 0', 'Sponsor 1'], array_column(json_decode($restored->getRawOriginal('sponsor_logos'), true), 'name'), 'the sponsors themselves are restored');

        $event = Event::where('creator_role_id', $restored->id)->firstOrFail();
        $this->assertNull($event->getRawOriginal('flyer_image_url'));
        $this->assertSame(['https://cdn.example.com/event-logo.png', null], array_column(json_decode($event->getRawOriginal('sponsor_logos'), true), 'logo'));

        // Deleting the copy leaves the original's files alone.
        $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $restored->subdomain]))->assertRedirect();
        foreach ([$profile, $background, $logo, $eventLogo, $flyer] as $name) {
            $this->assertTrue(Storage::exists($this->path($name)), $name);
        }
    }

    public function test_an_archive_naming_another_schedules_files_cannot_reach_them(): void
    {
        $theirProfile = $this->storedImage('profile');
        $theirHeader = $this->storedImage('header');
        $theirBackground = $this->storedImage('background');
        $theirLogo = $this->storedImage('sponsor');
        $theirFlyer = $this->storedImage('flyer');
        $theirSchedule = $this->createRole($this->createOwner(), 'venue', [
            'profile_image_url' => $theirProfile,
            'header_image_url' => $theirHeader,
            'header_image' => '',
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => $theirBackground,
            'sponsor_logos' => $this->sponsors([$theirLogo]),
        ]);
        $this->createEvent($theirSchedule, ['flyer_image_url' => $theirFlyer]);

        $owner = $this->createOwner();
        $json = ['meta' => ['version' => '1.0', 'includes_images' => false], 'schedules' => [[
            'role' => [
                'subdomain' => 'crafted'.strtolower(Str::random(6)),
                'name' => 'Crafted',
                'type' => 'venue',
                'email' => $owner->email,
                'timezone' => 'UTC',
                'profile_image_url' => $theirProfile,
                // Starts with http, which is all the old check asked; Storage::delete() resolves the ..
                'header_image_url' => 'https://cdn.example.com/../../'.$theirHeader,
                'header_image' => '',
                'background' => 'image',
                'background_image_url' => $theirBackground,
                'sponsor_logos' => $this->sponsors([$theirLogo, 'http/../'.$theirProfile]),
            ],
            'events' => [[
                'name' => 'Crafted Night',
                'slug' => 'crafted-night',
                'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'flyer_image_url' => $theirFlyer,
                'sponsor_mode' => 'custom',
                'sponsor_logos' => $this->sponsors([$theirLogo, 'https://x.test/../../'.$theirFlyer]),
            ]],
            'groups' => [],
        ]]];

        $restored = $this->restore($owner, $json);
        $event = Event::where('creator_role_id', $restored->id)->firstOrFail();

        foreach (['profile_image_url', 'header_image_url', 'background_image_url'] as $column) {
            $this->assertNull($restored->getRawOriginal($column), $column);
        }
        $this->assertSame([null, null], array_column(json_decode($restored->getRawOriginal('sponsor_logos'), true), 'logo'));
        $this->assertNull($event->getRawOriginal('flyer_image_url'));
        $this->assertSame([null, null], array_column(json_decode($event->getRawOriginal('sponsor_logos'), true), 'logo'));

        // Every way the restored schedule later deletes an image of its own: replacing each one,
        // deleting each one, dropping its sponsors, its event dropping its own, and going. The plan
        // is not part of a backup, and the sponsor saves only run on Pro.
        $this->makePro($restored);
        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $restored->subdomain]), [
            'name' => $restored->name,
            'timezone' => $restored->timezone,
            'email' => $restored->email,
            'new_subdomain' => $restored->subdomain,
            'profile_image' => UploadedFile::fake()->image('new.png', 64, 64),
            'header_image_url' => UploadedFile::fake()->image('header.png', 300, 100),
            'existing_sponsors' => '[]',
        ])->assertRedirect()->assertSessionHasNoErrors();
        foreach (['profile', 'header', 'background'] as $type) {
            $this->actingAs($owner)->delete(route('role.delete_image', ['subdomain' => $restored->subdomain, 'image_type' => $type]));
        }
        $this->putUpdateEvent($owner, $restored, $event, [
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00',
            'sponsor_mode' => 'none',
        ])->assertRedirect();
        $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $restored->subdomain]))->assertRedirect();

        foreach ([$theirProfile, $theirHeader, $theirBackground, $theirLogo, $theirFlyer] as $name) {
            $this->assertTrue(Storage::exists($this->path($name)), $name);
        }
    }

    public function test_a_restore_with_images_brings_the_sponsor_logos_back_under_new_names(): void
    {
        $owner = $this->createOwner();
        $logo = $this->storedImage('sponsor');
        $profile = $this->storedImage('profile');
        $role = $this->createRole($owner, 'curator', [
            'profile_image_url' => $profile,
            'sponsor_logos' => $this->sponsors([$logo, 'https://cdn.example.com/logo.png']),
        ]);

        $export = $this->export($owner, $role, true);
        $this->assertArrayHasKey('images/'.$logo, $export['images'], 'the archive carries the schedule\'s sponsor logo');

        $json = $export['json'];
        $json['meta']['includes_images'] = true;
        $restored = $this->restore($owner, $json, $export['images']);

        $logos = array_column(json_decode($restored->getRawOriginal('sponsor_logos'), true), 'logo');
        $this->assertCount(2, $logos);
        $this->assertNotSame($logo, $logos[0], 'a new file, not the original\'s');
        $this->assertTrue(Storage::exists($this->path($logos[0])));
        $this->assertSame(Storage::get($this->path($logo)), Storage::get($this->path($logos[0])));
        $this->assertSame('https://cdn.example.com/logo.png', $logos[1]);

        $restoredProfile = $restored->getRawOriginal('profile_image_url');
        $this->assertNotNull($restoredProfile);
        $this->assertNotSame($profile, $restoredProfile);

        // The copy's guest page shows its own logo.
        $restored->forceFill(['email_verified_at' => now()])->save();
        $this->get(route('role.view_guest', ['subdomain' => $restored->subdomain]))
            ->assertOk()
            ->assertSee($logos[0], false)
            ->assertDontSee($logo, false);

        // Dropping the copy's sponsors deletes the copy's file, never the original's. The plan is not
        // part of a backup, and the sponsor save only runs on Pro.
        $this->makePro($restored);
        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $restored->subdomain]), [
            'name' => $restored->name,
            'timezone' => $restored->timezone,
            'email' => $restored->email,
            'new_subdomain' => $restored->subdomain,
            'existing_sponsors' => '[]',
        ])->assertRedirect();

        $this->assertFalse(Storage::exists($this->path($logos[0])));
        $this->assertTrue(Storage::exists($this->path($logo)));
        $this->assertSame($role->fresh()->getRawOriginal('sponsor_logos'), $this->sponsors([$logo, 'https://cdn.example.com/logo.png']));
    }
}
