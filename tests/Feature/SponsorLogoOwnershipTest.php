<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A sponsor logo is a bare filename in JSON the browser builds, and whatever a later save drops
 * from the stored list is deleted as an orphan (as is every logo when the schedule or event goes).
 * So a logo that is not already the schedule's or the event's own never enters its list: posted in
 * existing_sponsors, in existing_event_sponsors, or in the schedule's fillable sponsor_logos, it
 * used to be stored and then deleted from under the schedule it belongs to.
 *
 * Every file here lives on a faked disk; nothing touches real storage.
 */
class SponsorLogoOwnershipTest extends TestCase
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

    private function storedLogo(): string
    {
        $name = 'sponsor_'.strtolower(Str::random(32)).'.png';

        ob_start();
        imagepng(imagecreatetruecolor(8, 8));
        Storage::put($this->path($name), ob_get_clean());

        return $name;
    }

    /** A schedule whose own sponsor list shows $logo. */
    private function scheduleShowing(string $logo): Role
    {
        return $this->createRole($this->createOwner(), 'curator', [
            'sponsor_logos' => json_encode([['name' => 'Their Sponsor', 'logo' => $logo, 'url' => null, 'tier' => 'gold']]),
        ]);
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
        return array_merge(['starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00'], $overrides);
    }

    public function test_a_schedule_keeps_no_logo_it_does_not_hold(): void
    {
        $theirs = $this->storedLogo();
        $theirSchedule = $this->scheduleShowing($theirs);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        $this->saveSchedule($owner, $role, [
            'existing_sponsors' => json_encode([['name' => 'Borrowed', 'logo' => $theirs, 'url' => 'https://borrowed.test', 'tier' => 'silver']]),
        ])->assertRedirect()->assertSessionHasNoErrors();

        // The sponsor stays; the filename that was not ours does not.
        $this->assertSame(
            [['name' => 'Borrowed', 'url' => 'https://borrowed.test', 'tier' => 'silver']],
            json_decode($role->fresh()->sponsor_logos, true)
        );

        // Dropping the sponsor later has nothing of theirs to delete.
        $this->saveSchedule($owner, $role, ['existing_sponsors' => '[]'])->assertRedirect();

        $this->assertTrue(Storage::exists($this->path($theirs)));
        $this->assertStringContainsString($theirs, $theirSchedule->fresh()->sponsor_logos);
        $this->get(route('role.view_guest', ['subdomain' => $theirSchedule->subdomain]))
            ->assertOk()
            ->assertSee($theirs, false);
    }

    public function test_a_posted_sponsor_logos_field_cannot_become_the_stored_list(): void
    {
        $theirs = $this->storedLogo();
        $this->scheduleShowing($theirs);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        // Filled and saved before the sponsor block ran, this was the "stored" list that block
        // compared against, so everything in it was an orphan to delete.
        $this->saveSchedule($owner, $role, [
            'sponsor_logos' => json_encode([['name' => 'Planted', 'logo' => $theirs]]),
            'existing_sponsors' => '[]',
        ])->assertRedirect();

        $this->assertTrue(Storage::exists($this->path($theirs)));
        $this->assertNull($role->fresh()->sponsor_logos);
    }

    public function test_a_schedules_own_logos_are_kept_reordered_and_deleted_when_dropped(): void
    {
        $owner = $this->createOwner();
        $first = $this->storedLogo();
        $second = $this->storedLogo();
        $role = $this->createRole($owner, 'curator', [
            'sponsor_logos' => json_encode([
                ['name' => 'First', 'logo' => $first, 'url' => null, 'tier' => 'gold'],
                ['name' => 'Second', 'logo' => $second, 'url' => null, 'tier' => ''],
            ]),
        ]);

        $this->saveSchedule($owner, $role, ['existing_sponsors' => json_encode([
            ['name' => 'Second', 'logo' => $second, 'url' => null, 'tier' => ''],
            ['name' => 'First', 'logo' => $first, 'url' => null, 'tier' => 'gold'],
        ])])->assertRedirect();

        $this->assertSame([$second, $first], array_column(json_decode($role->fresh()->sponsor_logos, true), 'logo'));

        $this->saveSchedule($owner, $role, ['existing_sponsors' => json_encode([
            ['name' => 'Second', 'logo' => $second, 'url' => null, 'tier' => ''],
        ])])->assertRedirect();

        $this->assertFalse(Storage::exists($this->path($first)), 'a logo the schedule drops is still deleted');
        $this->assertTrue(Storage::exists($this->path($second)));
    }

    public function test_an_event_keeps_no_logo_it_does_not_hold(): void
    {
        $theirs = $this->storedLogo();
        $theirSchedule = $this->scheduleShowing($theirs);
        $theirEvent = $this->createEvent($theirSchedule, [
            'sponsor_mode' => 'custom',
            'sponsor_logos' => json_encode([['name' => 'Their Sponsor', 'logo' => $theirs, 'url' => null, 'tier' => '']]),
        ]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);

        $this->putUpdateEvent($owner, $role, $event, $this->eventOverrides([
            'sponsor_mode' => 'custom',
            'existing_event_sponsors' => json_encode([['name' => 'Borrowed', 'logo' => $theirs, 'url' => null, 'tier' => 'bronze']]),
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(
            [['name' => 'Borrowed', 'url' => null, 'tier' => 'bronze']],
            json_decode($event->fresh()->sponsor_logos, true)
        );

        // Both ways a later save deletes an event's logos: dropping the sponsor, and leaving the
        // custom list, which deletes every logo it held.
        $this->putUpdateEvent($owner, $role, $event, $this->eventOverrides([
            'sponsor_mode' => 'custom',
            'existing_event_sponsors' => '[]',
        ]))->assertRedirect();
        $this->putUpdateEvent($owner, $role, $event, $this->eventOverrides(['sponsor_mode' => 'none']))->assertRedirect();

        $this->assertTrue(Storage::exists($this->path($theirs)));
        $this->assertStringContainsString($theirs, $theirEvent->fresh()->sponsor_logos);
    }
}
