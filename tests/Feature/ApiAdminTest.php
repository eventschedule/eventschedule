<?php

namespace Tests\Feature;

use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class ApiAdminTest extends TestCase
{
    use RefreshDatabase;
    use CreatesScheduleData;

    /** Configure an API key on the user and return the raw key for the X-API-Key header. */
    private function apiKey(User $user): string
    {
        $raw = 'testapikey_' . \Illuminate\Support\Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    public function test_api_list_sales(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $this->createSale($event, $role, ['name' => 'API Buyer', 'status' => 'paid'], $ticket, 1);
        $key = $this->apiKey($owner);

        $this->getJson('/api/sales', ['X-API-Key' => $key])
            ->assertOk()
            ->assertJsonFragment(['name' => 'API Buyer']);
    }

    public function test_api_update_event(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['name' => 'Original Name']);
        $key = $this->apiKey($owner);

        $this->putJson('/api/events/' . UrlUtils::encodeId($event->id), [
            'name' => 'Updated Via Api',
        ], ['X-API-Key' => $key])->assertSuccessful();

        $this->assertDatabaseHas('events', ['id' => $event->id, 'name' => 'Updated Via Api']);
    }

    public function test_api_delete_event(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $key = $this->apiKey($owner);

        $this->deleteJson('/api/events/' . UrlUtils::encodeId($event->id), [], ['X-API-Key' => $key])
            ->assertSuccessful();

        $this->assertNull(\App\Models\Event::find($event->id));
    }

    public function test_api_create_group(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $key = $this->apiKey($owner);

        $this->postJson('/api/schedules/' . $role->subdomain . '/groups', [
            'name' => 'Main Stage',
        ], ['X-API-Key' => $key])->assertSuccessful();

        $this->assertDatabaseHas('groups', ['role_id' => $role->id, 'name' => 'Main Stage']);
    }

    public function test_api_update_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $key = $this->apiKey($owner);

        $this->putJson('/api/schedules/' . $role->subdomain, [
            'name' => 'Renamed Schedule',
        ], ['X-API-Key' => $key])->assertSuccessful();

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Renamed Schedule']);
    }

    public function test_backup_export_creates_job(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->post(route('backup.export'), [
            'role_ids' => [$role->id],
            'include_images' => false,
        ])->assertSuccessful();

        $this->assertDatabaseHas('backup_jobs', [
            'user_id' => $owner->id,
            'type' => 'export',
        ]);
    }

    public function test_admin_dashboard_loads(): void
    {
        $admin = $this->createOwner(true);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_blog_create(): void
    {
        $admin = $this->createOwner(true);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)->post(route('blog.store'), [
                'title' => 'Hello World Post',
                'content' => 'Body of the post.',
                'is_published' => true,
            ]);

        $this->assertDatabaseHas('blog_posts', ['title' => 'Hello World Post']);
    }

    public function test_referral_page_loads(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);

        $this->actingAs($owner)->get(route('referrals'))->assertOk();
    }
}
