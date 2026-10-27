<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\GuestPageSetting;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Show Next Event (Style > Events): the owner's switch for the card above the month that names
 * the next event (#gp-next-event). It lives in guest_page_settings, because `roles` is full, and
 * no row reads as on.
 */
class GuestNextEventSwitchTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Queue::fake();
        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'venue', ['email' => $this->owner->email]);
        $this->createEvent($this->role, ['name' => 'Next Show', 'creator_role_id' => $this->role->id]);
    }

    private function save(array $fields = []): TestResponse
    {
        return $this->actingAs($this->owner)
            ->from(route('role.edit', ['subdomain' => $this->role->subdomain]))
            ->put(route('role.update', ['subdomain' => $this->role->subdomain]), array_merge([
                'name' => $this->role->name,
                'email' => $this->role->email,
                'timezone' => $this->role->timezone,
                'new_subdomain' => $this->role->subdomain,
            ], $fields));
    }

    private function hasCard(): bool
    {
        auth()->logout();

        return str_contains($this->get('/'.$this->role->subdomain.'?layout=calendar')->assertOk()->getContent(), 'id="gp-next-event"');
    }

    private function formHtml(): string
    {
        return $this->actingAs($this->owner)->get(route('role.edit', ['subdomain' => $this->role->subdomain]))->assertOk()->getContent();
    }

    public function test_with_nothing_stored_the_card_is_shown_and_the_switch_is_on(): void
    {
        $this->assertFalse(GuestPageSetting::where('role_id', $this->role->id)->exists());
        $this->assertTrue($this->hasCard());
        $this->assertMatchesRegularExpression('/<input type="checkbox" id="show_next_event" name="show_next_event" value="1"\s+checked/', $this->formHtml());
    }

    public function test_the_switch_takes_the_card_off_and_a_save_without_it_leaves_it(): void
    {
        $this->save(['show_next_event' => '0'])->assertSessionHasNoErrors();
        $this->assertFalse(GuestPageSetting::where('role_id', $this->role->id)->value('show_next_event'));
        $this->assertFalse($this->hasCard());
        $this->assertMatchesRegularExpression('/<input type="checkbox" id="show_next_event" name="show_next_event" value="1"\s+class=/', $this->formHtml(), 'the switch comes back off');

        // A save that did not draw the switch (the API's, another form's) is not a save of "on".
        $this->save(['description' => 'New words'])->assertSessionHasNoErrors();
        $this->assertFalse($this->hasCard());

        $this->save(['show_next_event' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($this->hasCard());

        $this->save(['show_next_event' => 'maybe'])->assertSessionHasErrors('show_next_event');
        $this->assertTrue($this->hasCard());
    }

    public function test_the_switch_survives_a_backup(): void
    {
        GuestPageSetting::store($this->role, ['show_next_event' => false]);

        $job = BackupJob::create(['user_id' => $this->owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = json_decode(json_encode(app(BackupService::class)->exportSchedules([$this->role->fresh()], false, $job)['json']), true);

        $job = BackupJob::create(['user_id' => $this->owner->id, 'type' => 'import', 'status' => 'processing']);
        app(BackupService::class)->importSchedules($data, [0], $this->owner->id, $job);
        $restored = Role::where('user_id', $this->owner->id)->where('id', '!=', $this->role->id)->latest('id')->firstOrFail();

        $this->assertFalse($restored->guestPageSetting->show_next_event);
    }
}
