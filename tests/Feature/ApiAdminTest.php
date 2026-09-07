<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\VerifyEmail;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class ApiAdminTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Configure an API key on the user and return the raw key for the X-API-Key header. */
    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.\Illuminate\Support\Str::random(24);
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

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
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

        $this->deleteJson('/api/events/'.UrlUtils::encodeId($event->id), [], ['X-API-Key' => $key])
            ->assertSuccessful();

        $this->assertNull(\App\Models\Event::find($event->id));
    }

    public function test_api_create_group(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $key = $this->apiKey($owner);

        $this->postJson('/api/schedules/'.$role->subdomain.'/groups', [
            'name' => 'Main Stage',
        ], ['X-API-Key' => $key])->assertSuccessful();

        $this->assertDatabaseHas('groups', ['role_id' => $role->id, 'name' => 'Main Stage']);
    }

    public function test_api_update_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $key = $this->apiKey($owner);

        $this->putJson('/api/schedules/'.$role->subdomain, [
            'name' => 'Renamed Schedule',
        ], ['X-API-Key' => $key])->assertSuccessful();

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'Renamed Schedule']);
    }

    /**
     * Deleting through the API releases the subdomain, like the admin action does.
     *
     * It used to only set is_deleted, which left the name consumed forever - roles.subdomain is
     * UNIQUE and nothing checking availability looks at the flag - making this endpoint the
     * supported way to squat a good name permanently.
     */
    public function test_api_delete_schedule_releases_the_subdomain(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $subdomain = $role->subdomain;
        $key = $this->apiKey($owner);

        $this->deleteJson('/api/schedules/'.$subdomain, [], ['X-API-Key' => $key])->assertSuccessful();

        $this->assertTrue((bool) $role->fresh()->is_deleted);
        $this->assertSame($subdomain, $role->fresh()->subdomain_before_delete);
        $this->assertFalse(\App\Models\Role::where('subdomain', $subdomain)->exists(), 'the name is free again');
    }

    /**
     * An owner deleting their own schedule is not an admin action.
     *
     * The shared ScheduleDeletionService defaults to admin.schedule_delete, which is the category
     * /admin/audit-log filters on - so this path has to pass its own action, and must not also log
     * the event a second time inline.
     */
    public function test_api_delete_schedule_is_audited_once_and_not_as_an_admin_action(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $key = $this->apiKey($owner);

        $this->deleteJson('/api/schedules/'.$role->subdomain, [], ['X-API-Key' => $key])->assertSuccessful();

        $actions = \App\Models\AuditLog::where('model_id', $role->id)
            ->whereIn('action', ['schedule.delete', 'admin.schedule_delete'])
            ->pluck('action')
            ->all();

        $this->assertSame(['schedule.delete'], $actions);
    }

    /**
     * Role::boot() has no created hook, so the create-time verification email is an
     * explicit call in each store() path. The API path used to omit it, leaving the
     * schedule permanently unverified - and an unverified schedule never gets a public
     * page, events or discovery - with its owner never told there was anything to do.
     */
    public function test_api_create_schedule_sends_the_verification_email(): void
    {
        Notification::fake();
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $key = $this->apiKey($owner);

        $this->postJson('/api/schedules', [
            'name' => 'Api Made Venue',
            'type' => 'venue',
            'email' => 'venue@gmail.com',
        ], ['X-API-Key' => $key])->assertCreated();

        $role = Role::where('name', 'Api Made Venue')->firstOrFail();

        $this->assertNull($role->email_verified_at);
        Notification::assertSentTo($role, VerifyEmail::class);
    }

    /**
     * store() copies the owner's verification when the addresses match, so there is
     * nothing to confirm and the email would be noise.
     */
    public function test_api_create_schedule_skips_the_email_when_it_reuses_the_owners_address(): void
    {
        Notification::fake();
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $key = $this->apiKey($owner);

        $this->postJson('/api/schedules', [
            'name' => 'Owner Email Venue',
            'type' => 'venue',
            'email' => $owner->email,
        ], ['X-API-Key' => $key])->assertCreated();

        $role = Role::where('name', 'Owner Email Venue')->firstOrFail();

        $this->assertNotNull($role->email_verified_at);
        Notification::assertNothingSent();
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
            ->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('messages.active_users_7_days'));
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

        $this->actingAs($owner)->get(route('referrals'))
            ->assertOk()
            ->assertSee(__('messages.referral_program'));
    }
}
