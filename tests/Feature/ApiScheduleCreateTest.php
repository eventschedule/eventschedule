<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * POST /api/schedules makes a schedule the caller can use, and says what it made.
 *
 * On a hosted install it did neither until 2026-10. Without an `email` it saved the row and then
 * mailed a verification link to nobody, which threw: a 500, with the schedule left behind and its
 * owner never attached. With one it answered {"data": {}}, because a new schedule is on the Free
 * plan and Role::toApiData() is empty below Pro, so the caller never learned the subdomain.
 */
class ApiScheduleCreateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function keyFor(User $owner): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        return ['X-API-Key' => $raw];
    }

    public function test_on_hosted_a_schedule_named_without_an_address_takes_the_accounts(): void
    {
        config(['app.hosted' => true]);
        Notification::fake();
        $owner = $this->createOwner();

        $response = $this->postJson('/api/schedules', ['name' => 'Probe Hall', 'type' => 'venue'], $this->keyFor($owner));

        $response->assertCreated();
        $role = Role::where('name', 'Probe Hall')->firstOrFail();
        $this->assertSame($owner->email, $role->email);
        $this->assertNotNull($role->email_verified_at, 'the account had already verified that address');
        $this->assertTrue($owner->roles()->where('roles.id', $role->id)->wherePivot('level', 'owner')->exists(), 'the schedule was left with no owner');
        Notification::assertNothingSent();
    }

    public function test_on_hosted_the_answer_names_the_free_schedule_it_made(): void
    {
        config(['app.hosted' => true]);
        Notification::fake();
        $owner = $this->createOwner();

        $response = $this->postJson('/api/schedules', ['name' => 'Probe Hall', 'type' => 'venue', 'email' => 'hall@gmail.com'], $this->keyFor($owner));

        $response->assertCreated();
        $role = Role::where('name', 'Probe Hall')->firstOrFail();
        $this->assertFalse($role->isPro(), 'sanity check: a schedule made over the API starts on the Free plan');
        $response->assertJsonPath('data.subdomain', $role->subdomain);
        $response->assertJsonPath('data.name', 'Probe Hall');
        $response->assertJsonPath('data.type', 'venue');
        $response->assertJsonPath('data.email', 'hall@gmail.com');
        $this->assertNotEmpty($response->json('data.id'));
    }

    public function test_reading_a_free_schedule_is_still_a_pro_matter(): void
    {
        config(['app.hosted' => true]);
        Notification::fake();
        $owner = $this->createOwner();
        $headers = $this->keyFor($owner);
        $this->postJson('/api/schedules', ['name' => 'Probe Hall', 'type' => 'venue'], $headers)->assertCreated();
        $role = Role::where('name', 'Probe Hall')->firstOrFail();

        $this->getJson('/api/schedules/'.$role->subdomain, $headers)->assertStatus(403);
        $this->assertSame([], $this->getJson('/api/schedules', $headers)->assertOk()->json('data'));
        // The gate itself, which webhooks and every other reader of the object rely on.
        $this->assertEquals(new \stdClass, $role->toApiData(), 'a Free schedule is described to anyone who asks');
        $this->assertSame($role->subdomain, $role->toApiData(whateverThePlan: true)->subdomain);
    }

    public function test_on_selfhost_a_schedule_may_still_have_no_address(): void
    {
        config(['app.hosted' => false]);
        $owner = $this->createOwner();

        $response = $this->postJson('/api/schedules', ['name' => 'Probe Hall', 'type' => 'venue'], $this->keyFor($owner));

        $response->assertCreated();
        $role = Role::where('name', 'Probe Hall')->firstOrFail();
        $this->assertNull($role->email);
        $this->assertNotNull($role->email_verified_at);
        $response->assertJsonPath('data.subdomain', $role->subdomain);
    }
}
