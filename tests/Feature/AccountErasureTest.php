<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Sale;
use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Deleting an account erases the person and leaves everyone else's records alone
 * (App\Services\AccountDeletionService, ProfileController::destroy()).
 */
class AccountErasureTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function deleteAccount(User $user): void
    {
        $this->actingAs($user)->delete('/settings', ['password' => 'password'])->assertRedirect('/');
        $this->assertNull(User::find($user->id));
    }

    /** A purchase is the organizer's record of a sale; the cascade used to delete it. */
    public function test_a_buyers_purchases_stay_with_the_organizer(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);
        $buyer = User::factory()->create();
        $sale = $this->createSale($event, $role, ['user_id' => $buyer->id, 'email' => $buyer->email, 'payment_amount' => 40]);

        $this->deleteAccount($buyer);

        $kept = Sale::find($sale->id);
        $this->assertNotNull($kept, 'the organizer keeps the sale');
        $this->assertNull($kept->user_id, 'detached from the deleted account');
        $this->assertSame(40.0, (float) $kept->payment_amount);
    }

    /** A teammate's events belong to the schedule they made them for. */
    public function test_a_teammates_events_newsletters_and_templates_stay_with_the_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $teammate = User::factory()->create();
        $role->users()->attach($teammate->id, ['level' => 'admin']);

        $event = $this->createEvent($role, ['user_id' => $teammate->id, 'creator_role_id' => $role->id]);
        $sale = $this->createSale($event, $role);
        $newsletterId = DB::table('newsletters')->insertGetId([
            'role_id' => $role->id, 'user_id' => $teammate->id, 'subject' => 'Hello', 'status' => 'draft',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $templateId = DB::table('newsletter_templates')->insertGetId([
            'role_id' => $role->id, 'user_id' => $teammate->id, 'name' => 'Ours',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->deleteAccount($teammate);

        $this->assertSame($owner->id, Event::find($event->id)?->user_id, 'handed to the schedule owner');
        $this->assertNotNull(Sale::find($sale->id), 'and its sales with it');
        $this->assertSame($owner->id, DB::table('newsletters')->where('id', $newsletterId)->value('user_id'));
        $this->assertSame($owner->id, DB::table('newsletter_templates')->where('id', $templateId)->value('user_id'));
    }

    public function test_the_persons_own_data_and_files_go(): void
    {
        Storage::fake();
        config(['filesystems.default' => 'local']);

        $user = User::factory()->create();
        // Google vouches for the address, so what others keyed to it may go too (addressIsProven()).
        $user->forceFill(['google_oauth_id' => 'google-123'])->save();
        $role = $this->createRole($user);
        Storage::put('public/flyer-mine.jpg', 'x');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'flyer_image_url' => 'flyer-mine.jpg']);

        DB::table('event_interests')->insert([
            'event_id' => $event->id, 'event_date' => now()->addDays(3)->format('Y-m-d'), 'email' => strtolower($user->email),
            'token' => str_repeat('a', 32), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

        $this->deleteAccount($user);

        $this->assertNull(Event::find($event->id));
        Storage::assertMissing('public/flyer-mine.jpg');
        $this->assertSame(0, DB::table('event_interests')->where('email', strtolower($user->email))->count());
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
    }

    public function test_a_shared_flyer_survives_the_deletion_of_one_of_its_events(): void
    {
        Storage::fake();
        config(['filesystems.default' => 'local']);

        $user = User::factory()->create();
        $role = $this->createRole($user);
        Storage::put('public/shared.jpg', 'x');
        $this->createEvent($role, ['creator_role_id' => $role->id, 'flyer_image_url' => 'shared.jpg']);
        $other = $this->createRole($this->createOwner());
        $this->createEvent($other, ['creator_role_id' => $other->id, 'flyer_image_url' => 'shared.jpg']);

        $this->deleteAccount($user);

        Storage::assertExists('public/shared.jpg');
    }

    /**
     * A selfhost sign-up marks the address verified without any proof, so deleting that account
     * must not erase what other people keyed to the address: here, someone else's interest-list
     * entry under it.
     */
    public function test_an_unproven_selfhost_address_does_not_erase_records_keyed_to_it(): void
    {
        config(['app.hosted' => false]);
        $event = $this->createEvent($this->createRole($this->createOwner()));
        $user = User::factory()->create();

        DB::table('event_interests')->insert([
            'event_id' => $event->id, 'event_date' => now()->addDays(3)->format('Y-m-d'), 'email' => strtolower($user->email),
            'token' => str_repeat('b', 32), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->deleteAccount($user);

        $this->assertSame(1, DB::table('event_interests')->where('email', strtolower($user->email))->count());
    }

    /**
     * The hand-over runs first and stops the deletion when it fails: going on would let the
     * cascades take other people's events with the account.
     */
    public function test_a_failed_hand_over_stops_the_deletion_before_anything_is_lost(): void
    {
        $user = User::factory()->create();
        $this->app->instance(AccountDeletionService::class, new class extends AccountDeletionService
        {
            public function handOver(User $user): void
            {
                throw new \RuntimeException('Lock wait timeout exceeded');
            }
        });

        $this->actingAs($user)->delete('/settings', ['password' => 'password'])
            ->assertRedirect(route('profile.edit').'#section-delete')
            ->assertSessionHas('error');

        $this->assertNotNull(User::find($user->id));
        $this->assertAuthenticatedAs($user);
    }

    public function test_the_last_selfhost_admin_cannot_delete_their_account(): void
    {
        config(['app.hosted' => false]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->delete('/settings', ['password' => 'password'])
            ->assertRedirect(route('profile.edit').'#section-delete');
        $this->assertNotNull(User::find($admin->id));

        User::factory()->create(['is_admin' => true]);
        $this->deleteAccount($admin);
    }
}
