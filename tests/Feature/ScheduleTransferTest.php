<?php

namespace Tests\Feature;

use App\Mail\ScheduleTransferCompleted;
use App\Mail\ScheduleTransferDeclined;
use App\Mail\ScheduleTransferInvite;
use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\Role;
use App\Models\RoleTransfer;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\ScheduleTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Schedule ownership handover (discussion #119).
 *
 * The queue is sync under phpunit.xml, so SendQueuedEmail runs inline and mail is
 * asserted with Mail::assertSent, not assertQueued.
 */
class ScheduleTransferTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function recipient(string $email = 'newowner@gmail.com'): User
    {
        return User::factory()->create([
            'email' => $email,
            'email_verified_at' => now(),
        ]);
    }

    public function test_initiating_emails_the_target_and_opens_one_request(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)
            ->post(route('role.transfer.store', ['subdomain' => $role->subdomain]), [
                'email' => 'newowner@gmail.com',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('role_transfers', [
            'role_id' => $role->id,
            'from_user_id' => $owner->id,
            'to_email' => 'newowner@gmail.com',
            'status' => 'pending',
        ]);

        Mail::assertSent(ScheduleTransferInvite::class, fn ($mail) => $mail->hasTo('newowner@gmail.com'));

        $this->assertDatabaseHas('audit_logs', ['action' => 'schedule.transfer_initiate']);

        // A second request supersedes the first rather than leaving two live tokens.
        $this->actingAs($owner)
            ->post(route('role.transfer.store', ['subdomain' => $role->subdomain]), [
                'email' => 'someone.else@gmail.com',
            ]);

        $this->assertSame(1, $role->transfers()->open()->count());
        $this->assertSame('cancelled', RoleTransfer::where('to_email', 'newowner@gmail.com')->first()->status);
    }

    public function test_an_admin_member_cannot_transfer_the_schedule(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // Owner-level permissions, but not roles.user_id - which is the actual gate.
        $admin = $this->createOwner();
        $admin->roles()->attach($role->id, ['level' => 'admin', 'created_at' => now()]);

        $this->actingAs($admin)
            ->from(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
            ->post(route('role.transfer.store', ['subdomain' => $role->subdomain]), [
                'email' => 'newowner@gmail.com',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('role_transfers', 0);
        Mail::assertNothingSent();
    }

    public function test_the_wrong_account_cannot_see_or_accept_the_request(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $transfer = $this->openTransfer($role, $owner, 'newowner@gmail.com');

        $intruder = $this->recipient('intruder@gmail.com');

        $this->actingAs($intruder)
            ->get(route('role.transfer.show', ['token' => $transfer->token]))
            ->assertOk()
            ->assertSee('newowner@gmail.com')
            ->assertDontSee(route('role.transfer.accept', ['token' => $transfer->token]));

        $this->actingAs($intruder)
            ->post(route('role.transfer.accept', ['token' => $transfer->token]))
            ->assertRedirect(route('home'));

        $this->assertSame($owner->id, $role->fresh()->user_id);
    }

    public function test_expired_cancelled_and_answered_tokens_are_all_refused(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $newOwner = $this->recipient();

        foreach (['expired', 'cancelled', 'accepted'] as $case) {
            $role = $this->createRole($owner);
            $transfer = $this->openTransfer($role, $owner, $newOwner->email);

            if ($case === 'expired') {
                $transfer->expires_at = now()->subDay();
            } else {
                $transfer->status = $case;
            }
            $transfer->save();

            $this->actingAs($newOwner)
                ->post(route('role.transfer.accept', ['token' => $transfer->token]))
                ->assertRedirect(route('home'));

            $this->assertSame($owner->id, $role->fresh()->user_id, "the {$case} token moved ownership");
        }
    }

    public function test_accept_moves_both_the_owner_column_and_the_pivot(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newOwner = $this->recipient();
        $transfer = $this->openTransfer($role, $owner, $newOwner->email);

        $this->actingAs($newOwner)
            ->post(route('role.transfer.accept', ['token' => $transfer->token]))
            ->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']));

        $role->refresh();

        // Both sources of truth, together. Moving only one is the drift
        // CheckData::checkRoleOwnership() exists to repair.
        $this->assertSame($newOwner->id, $role->user_id);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $newOwner->id,
            'level' => 'owner',
        ]);
        $this->assertDatabaseMissing('role_user', [
            'role_id' => $role->id,
            'user_id' => $owner->id,
        ]);
        $this->assertSame($newOwner->id, $role->owner()?->id);

        $this->assertSame('accepted', $transfer->fresh()->status);
        $this->assertSame($newOwner->id, $transfer->fresh()->to_user_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'schedule.transfer_accept']);

        Mail::assertSent(ScheduleTransferCompleted::class, fn ($mail) => $mail->hasTo($newOwner->email));
        Mail::assertSent(ScheduleTransferCompleted::class, fn ($mail) => $mail->hasTo($owner->email));
    }

    /**
     * The regression that matters most: ticket revenue follows events.user_id, so the
     * handover has to move the schedule's own events - and must NOT touch an event that
     * belongs to somebody else's schedule and is merely curated in here.
     */
    public function test_accept_repoints_owned_events_but_not_curated_ones(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');
        $newOwner = $this->recipient();

        $ownEvent = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Ours']);

        // Another organizer's event, listed on this curator. Its money must keep going
        // to them.
        $stranger = $this->createOwner();
        $strangerRole = $this->createRole($stranger, 'venue');
        $curated = $this->createEvent($strangerRole, [
            'user_id' => $stranger->id,
            'creator_role_id' => $strangerRole->id,
            'name' => 'Theirs',
        ]);
        $curated->roles()->attach($role->id, ['is_accepted' => true]);

        // A legacy row with no creator_role_id, listed only here: claimed by the fallback.
        $legacy = $this->createEvent($role, ['name' => 'Legacy']);
        Event::where('id', $legacy->id)->update(['creator_role_id' => null]);

        // Same, but also listed on another of the previous owner's schedules, so the
        // fallback must leave it alone rather than guess.
        $otherRole = $this->createRole($owner, 'venue');
        $shared = $this->createEvent($role, ['name' => 'Shared']);
        $shared->roles()->attach($otherRole->id, ['is_accepted' => true]);
        Event::where('id', $shared->id)->update(['creator_role_id' => null]);

        $transfer = $this->openTransfer($role, $owner, $newOwner->email);

        $this->actingAs($newOwner)->post(route('role.transfer.accept', ['token' => $transfer->token]));

        $this->assertSame($newOwner->id, $ownEvent->fresh()->user_id, 'the schedule\'s own event did not move');
        $this->assertSame($newOwner->id, $legacy->fresh()->user_id, 'the legacy single-listing event did not move');
        $this->assertSame($stranger->id, $curated->fresh()->user_id, 'a curated event was stolen from its owner');
        $this->assertSame($owner->id, $shared->fresh()->user_id, 'an ambiguous legacy event was claimed');
    }

    public function test_accept_clears_the_previous_owners_default_schedule_and_calendar_sync(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newOwner = $this->recipient();

        $owner->default_role_id = $role->id;
        $owner->saveQuietly();

        $event = $this->createEvent($role);
        CalendarSync::create([
            'user_id' => $owner->id,
            'role_id' => $role->id,
            'event_id' => $event->id,
            'google_event_id' => 'g-1',
        ]);

        $transfer = $this->openTransfer($role, $owner, $newOwner->email);

        $this->actingAs($newOwner)->post(route('role.transfer.accept', ['token' => $transfer->token]));

        $this->assertNull($owner->fresh()->default_role_id);
        $this->assertSame($role->id, $newOwner->fresh()->default_role_id);
        $this->assertDatabaseMissing('calendar_syncs', [
            'user_id' => $owner->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_enterprise_can_keep_the_previous_owner_as_an_admin(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newOwner = $this->recipient();

        $this->actingAs($owner)->post(route('role.transfer.store', ['subdomain' => $role->subdomain]), [
            'email' => $newOwner->email,
            'remove_me' => '0',
        ]);

        $transfer = $role->openTransfer();
        $this->assertTrue($transfer->keep_previous_owner);

        $this->actingAs($newOwner)->post(route('role.transfer.accept', ['token' => $transfer->token]));

        $this->assertSame('admin', RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)->first()?->level);
        $this->assertSame($newOwner->id, $role->fresh()->user_id);
    }

    /**
     * A Free schedule holds a single member (createMember is Enterprise-gated), so the
     * courtesy seat is neither offered nor honoured there.
     *
     * app.hosted has to be forced: isEnterprise() short-circuits to true on selfhost, and
     * the ambient value differs between this machine and CI.
     */
    public function test_a_free_schedule_always_detaches_the_previous_owner(): void
    {
        Mail::fake();
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
        $this->assertFalse($role->fresh()->isPro(), 'fixture is not actually on the free plan');

        $newOwner = $this->recipient();

        $this->actingAs($owner)
            ->get(route('role.transfer.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertDontSee('name="remove_me"', false);

        // Even when the field is forged, the schedule cannot hold a second member.
        $this->actingAs($owner)->post(route('role.transfer.store', ['subdomain' => $role->subdomain]), [
            'email' => $newOwner->email,
            'remove_me' => '0',
        ]);

        $transfer = $role->openTransfer();
        $this->assertFalse($transfer->keep_previous_owner);

        $this->actingAs($newOwner)->post(route('role.transfer.accept', ['token' => $transfer->token]));

        $this->assertDatabaseMissing('role_user', ['role_id' => $role->id, 'user_id' => $owner->id]);
        $this->assertSame($newOwner->id, $role->fresh()->user_id);
    }

    public function test_declining_leaves_ownership_alone_and_tells_the_owner(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newOwner = $this->recipient();
        $transfer = $this->openTransfer($role, $owner, $newOwner->email);

        $this->actingAs($newOwner)
            ->post(route('role.transfer.decline', ['token' => $transfer->token]))
            ->assertRedirect(route('home'));

        $this->assertSame($owner->id, $role->fresh()->user_id);
        $this->assertSame('declined', $transfer->fresh()->status);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);

        Mail::assertSent(ScheduleTransferDeclined::class, fn ($mail) => $mail->hasTo($owner->email));
    }

    public function test_a_signed_out_visitor_is_offered_sign_in_and_the_token_survives(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $transfer = $this->openTransfer($role, $owner, 'newowner@gmail.com');

        $this->get(route('role.transfer.show', ['token' => $transfer->token]))
            ->assertOk()
            ->assertSee($role->name)
            ->assertSessionHas('pending_transfer', $transfer->token);

        // HomeController::home() returns them to the offer after they authenticate.
        $newOwner = $this->recipient();
        $this->actingAs($newOwner)
            ->withSession(['pending_transfer' => $transfer->token])
            ->get(route('home'))
            ->assertRedirect(route('role.transfer.show', ['token' => $transfer->token]));
    }

    public function test_an_incoming_transfer_shows_on_the_recipients_dashboard(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // Deliberately a user with no schedules of their own: the to-do row has to be
        // pushed before getPendingActionItems() bails on an empty schedule list.
        $newOwner = $this->recipient();
        $this->openTransfer($role, $owner, $newOwner->email);

        $this->actingAs($newOwner)
            ->get(route('home'))
            ->assertOk()
            ->assertSee(__('messages.pending_action_schedule_transfer'));
    }

    /**
     * Every remaining branch of transfer-accept.blade.php, plus the Team tab's pending
     * panel. The accept/decline tests all POST directly, so without this a Blade typo in
     * the 'ready' branch - the one every real recipient sees - would ship unnoticed.
     */
    public function test_every_page_state_renders(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newOwner = $this->recipient();
        $transfer = $this->openTransfer($role, $owner, $newOwner->email);

        // ready: the page a real recipient lands on.
        $this->actingAs($newOwner)
            ->get(route('role.transfer.show', ['token' => $transfer->token]))
            ->assertOk()
            ->assertSee($role->name)
            ->assertSee(__('messages.accept'))
            ->assertSee(__('messages.decline'));

        // The owner's Team tab shows the pending panel instead of the Transfer button.
        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
            ->assertOk()
            ->assertSee($newOwner->email)
            // The exact href: the panel's own Resend/Cancel actions post to
            // .../team/transfer/resend and /cancel, which contain the create URL as a
            // substring, so a bare assertDontSee on it can never pass.
            ->assertDontSee('href="'.route('role.transfer.create', ['subdomain' => $role->subdomain]).'"', false);

        // missing: an unknown token.
        $this->get(route('role.transfer.show', ['token' => 'nope']))
            ->assertOk()
            ->assertSee(__('messages.schedule_transfer_unavailable'));

        // closed: withdrawn by the owner.
        $this->actingAs($owner)->post(route('role.transfer.cancel', ['subdomain' => $role->subdomain]));
        $this->actingAs($newOwner)
            ->get(route('role.transfer.show', ['token' => $transfer->token]))
            ->assertOk()
            ->assertSee(__('messages.schedule_transfer_unavailable'));

        // With nothing pending the Transfer button is back.
        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
            ->assertOk()
            ->assertSee('href="'.route('role.transfer.create', ['subdomain' => $role->subdomain]).'"', false);

        // accepted: the recipient re-opens a link they have already used.
        $second = $this->openTransfer($role, $owner, $newOwner->email);
        $this->actingAs($newOwner)->post(route('role.transfer.accept', ['token' => $second->token]));
        $this->actingAs($newOwner)
            ->get(route('role.transfer.show', ['token' => $second->token]))
            ->assertOk()
            ->assertSee(__('messages.schedule_transfer_already_accepted'));
    }

    /**
     * The one that matters. If Stripe will not let go of the previous owner's
     * subscription, the handover must not happen: once roles.user_id moves,
     * SubscriptionController::cancel() gates them out and they have no way to stop being
     * charged.
     */
    public function test_a_failing_subscription_cancel_aborts_the_whole_transfer(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newOwner = $this->recipient();
        $transfer = $this->openTransfer($role, $owner, $newOwner->email);

        $this->app->instance(ScheduleTransferService::class, new class extends ScheduleTransferService
        {
            protected function cancelSubscription(Role $role): bool
            {
                throw new \RuntimeException('Stripe is down');
            }
        });

        $this->actingAs($newOwner)
            ->post(route('role.transfer.accept', ['token' => $transfer->token]))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', __('messages.schedule_transfer_failed'));

        // Nothing moved, and the offer is still there to retry.
        $this->assertSame($owner->id, $role->fresh()->user_id);
        $this->assertSame('pending', $transfer->fresh()->status);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);

        // And above all: nobody was told the handover happened.
        Mail::assertNotSent(ScheduleTransferCompleted::class);
    }

    /**
     * The completion mail used to promise every departing owner that their subscription
     * had been cancelled, including the ones who never had one.
     */
    public function test_the_completion_mail_does_not_invent_a_cancelled_subscription(): void
    {
        Mail::fake();
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
        $newOwner = $this->recipient();
        $transfer = $this->openTransfer($role, $owner, $newOwner->email);

        $this->actingAs($newOwner)->post(route('role.transfer.accept', ['token' => $transfer->token]));

        // Both conditions in ONE predicate. Returning true for the new owner's copy would
        // satisfy assertSent without the departing owner's copy ever being examined - the
        // assertion would then pass even with the bug reintroduced.
        Mail::assertSent(
            ScheduleTransferCompleted::class,
            fn ($mail) => $mail->hasTo($owner->email)
                && ! str_contains($mail->render(), __('messages.schedule_transfer_sent_billing'))
                && str_contains($mail->render(), __('messages.schedule_transfer_sent_no_billing')),
        );
    }

    /**
     * Structural guard for the fix, rather than an inspection-only claim: holding the
     * roles lock across a network round trip is what DemoService:884 records as a live
     * 1213, because a guest page view needs an FK shared lock on the same row.
     */
    public function test_no_external_call_happens_inside_the_transaction(): void
    {
        Mail::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newOwner = $this->recipient();
        $transfer = $this->openTransfer($role, $owner, $newOwner->email);

        $probe = new class extends ScheduleTransferService
        {
            public array $levels = [];

            protected function cancelSubscription(Role $role): bool
            {
                $this->levels['cancelSubscription'] = DB::transactionLevel();

                return false;
            }

            protected function forgetPaymentMethods(Role $role): void
            {
                $this->levels['forgetPaymentMethods'] = DB::transactionLevel();
            }

            protected function releaseCalendarWebhooks(Role $role, User $previousOwner, array $webhooks): void
            {
                $this->levels['releaseCalendarWebhooks'] = DB::transactionLevel();
            }
        };

        $this->app->instance(ScheduleTransferService::class, $probe);

        // Give the calendar teardown something to do, so its probe actually runs.
        $role->google_webhook_id = 'chan-1';
        $role->google_webhook_resource_id = 'res-1';
        $role->saveQuietly();

        $this->actingAs($newOwner)->post(route('role.transfer.accept', ['token' => $transfer->token]));

        $this->assertSame($newOwner->id, $role->fresh()->user_id, 'the transfer did not complete');

        // RefreshDatabase wraps the whole test in a transaction, so the floor is 1, not 0.
        // What matters is that none of these ran DEEPER than the ambient level - i.e. none
        // of them was inside accept()'s own transaction.
        $baseline = DB::transactionLevel();

        $this->assertNotEmpty($probe->levels);
        foreach ($probe->levels as $where => $level) {
            $this->assertSame($baseline, $level, "{$where} ran inside accept()'s transaction");
        }
        $this->assertArrayHasKey('releaseCalendarWebhooks', $probe->levels);
    }

    private function openTransfer(Role $role, User $from, string $email): RoleTransfer
    {
        $transfer = new RoleTransfer;
        $transfer->role_id = $role->id;
        $transfer->from_user_id = $from->id;
        $transfer->to_email = $email;
        $transfer->save();

        return $transfer->fresh();
    }
}
