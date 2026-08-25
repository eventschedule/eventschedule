<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\ScheduleTransferCompleted;
use App\Mail\ScheduleTransferDeclined;
use App\Mail\ScheduleTransferInvite;
use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\MicrosoftCalendarSync;
use App\Models\Role;
use App\Models\RoleTransfer;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Schedule ownership handover (discussion #119).
 *
 * Ownership lives in two places that nothing keeps in step for us - `roles.user_id`
 * (which gates delete, billing and the plan page, and which CASCADE-deletes the schedule
 * when that user deletes their account) and the `role_user` pivot at level 'owner' (which
 * gates permissions). accept() moves both inside one locked transaction; moving only one
 * is the drift CheckData::checkRoleOwnership() exists to repair.
 *
 * Money is the other half. Ticket revenue is routed by `events.user_id`, not by the
 * schedule (CheckoutContext::owner() returns $event->user), so a transfer that left events
 * alone would keep paying the previous owner - and User::canEditEvent() grants the creator
 * an edit bypass, so they would keep write access to every event they handed over.
 */
class ScheduleTransferService
{
    /**
     * Offer the schedule to an email address. Any offer already open is cancelled first:
     * two live tokens for one schedule is a race nobody needs.
     */
    public function initiate(Role $role, User $from, string $email, bool $keepPreviousOwner): RoleTransfer
    {
        $email = strtolower(trim($email));

        $role->transfers()->open()->update([
            'status' => 'cancelled',
            'responded_at' => now(),
        ]);

        $transfer = new RoleTransfer;
        $transfer->role_id = $role->id;
        $transfer->from_user_id = $from->id;
        $transfer->to_email = $email;
        $transfer->keep_previous_owner = $keepPreviousOwner;
        $transfer->save();

        AuditService::log(
            AuditService::SCHEDULE_TRANSFER_INITIATE,
            $from->id,
            'Role',
            $role->id,
            null,
            null,
            $email,
        );

        $this->sendInvite($transfer);

        return $transfer;
    }

    /**
     * (Re)send the offer email.
     *
     * Deliberately queued with a null role id so it goes out on the platform mailer:
     * RoleMailerService::sendForRole() returns false and sends NOTHING when the schedule
     * has its own SMTP inside a failure window, and an offer nobody receives is worse than
     * one that arrives from the platform address.
     */
    public function sendInvite(RoleTransfer $transfer): void
    {
        $transfer->loadMissing(['role', 'fromUser']);

        $recipient = User::whereEmail($transfer->to_email)->first();

        SendQueuedEmail::dispatch(
            new ScheduleTransferInvite($transfer),
            $transfer->to_email,
            null,
            $recipient?->language_code ?? $transfer->fromUser?->language_code,
        );
    }

    public function cancel(RoleTransfer $transfer, User $by): void
    {
        $transfer->status = 'cancelled';
        $transfer->responded_at = now();
        $transfer->save();

        AuditService::log(
            AuditService::SCHEDULE_TRANSFER_CANCEL,
            $by->id,
            'Role',
            $transfer->role_id,
            null,
            null,
            $transfer->to_email,
        );
    }

    public function decline(RoleTransfer $transfer, User $by): void
    {
        $transfer->status = 'declined';
        $transfer->to_user_id = $by->id;
        $transfer->responded_at = now();
        $transfer->save();

        AuditService::log(
            AuditService::SCHEDULE_TRANSFER_DECLINE,
            $by->id,
            'Role',
            $transfer->role_id,
            null,
            null,
            $transfer->to_email,
        );

        $transfer->loadMissing(['role', 'fromUser']);

        if ($transfer->fromUser) {
            SendQueuedEmail::dispatch(
                new ScheduleTransferDeclined($transfer),
                $transfer->fromUser->email,
                null,
                $transfer->fromUser->language_code,
            );
        }
    }

    /**
     * Hand the schedule over.
     *
     * Ordering is the whole design here, and it is the one the rest of the repo uses
     * (SubscriptionController::store, BoostController::cancel, InstallmentService's
     * captureCardIds/captureCardDisplay split):
     *
     *   1. Stop billing the previous owner. Stripe call, no transaction open, and FATAL -
     *      if we cannot stop charging them we must not take the schedule away, because
     *      afterwards they can no longer reach SubscriptionController to cancel it
     *      themselves (it gates on roles.user_id). Nothing has moved at this point, so
     *      the offer simply stays open and they can retry.
     *   2. The swap itself: one tight transaction holding the roles lock, containing
     *      zero network I/O. DemoService:884 records what happens when that lock is held
     *      across slow work - a guest page view needs an FK shared lock on the same roles
     *      row for its analytics counter, and the cycle was a live 1213.
     *   3. Everything whose failure is survivable, after the commit: card deletion,
     *      calendar webhook teardown, the cosmetic default_role_id writes, and the mail.
     *
     * @return bool false when the offer stopped being valid (someone else accepted, the
     *              owner cancelled, ownership already moved). The caller turns that into a
     *              message, not an exception.
     */
    public function accept(RoleTransfer $transfer, User $newOwner): bool
    {
        // Cheap unlocked pre-check, so a dead offer never reaches Stripe. The locked
        // re-check below is still the authority.
        if (! $transfer->isOpen() || ! $transfer->isFor($newOwner)) {
            return false;
        }

        $role = $transfer->role;

        if (! $role || $role->is_deleted || $role->user_id !== $transfer->from_user_id) {
            return false;
        }

        // Step 1. Throws on failure, deliberately uncaught - see the docblock.
        $hadSubscription = $this->cancelSubscription($role);

        $previousOwner = null;
        $keptPrevious = false;
        $webhooks = [];

        // Step 2. Pure DB.
        $moved = DB::transaction(function () use ($transfer, $newOwner, &$previousOwner, &$keptPrevious, &$webhooks) {
            // Re-read both rows under the lock. Every guard the controller checked against
            // the rendered page has to hold at write time too.
            $locked = RoleTransfer::lockForUpdate()->find($transfer->id);
            if (! $locked || ! $locked->isOpen() || ! $locked->isFor($newOwner)) {
                return false;
            }

            $role = Role::lockForUpdate()->find($locked->role_id);
            if (! $role || $role->is_deleted || $role->user_id !== $locked->from_user_id) {
                return false;
            }

            if ($role->user_id === $newOwner->id) {
                return false;
            }

            $previousOwnerId = $role->user_id;
            $previousOwner = User::find($previousOwnerId);

            // The owner column. A plain save(): the Role::updating hook only nulls the
            // verified timestamps when `email` or `phone` is dirty, and neither is touched
            // here. Do NOT move roles.email onto the new owner - it is the schedule's
            // public contact address (and stripeEmail()), and changing it would unclaim
            // the schedule mid-handover.
            $role->user_id = $newOwner->id;

            // Cashier writes these two from Stripe; clearing them here means the new owner
            // never sees the previous owner's card. The detach itself happens after the
            // commit, where a Stripe outage costs nothing.
            $role->pm_type = null;
            $role->pm_last_four = null;

            // The pivot. syncWithoutDetaching, as the claim paths do, because the
            // recipient may already be a follower/viewer/admin and (user_id, role_id) is
            // unique.
            $newOwner->roles()->syncWithoutDetaching([
                $role->id => ['level' => 'owner'],
            ]);

            $keptPrevious = $locked->keep_previous_owner
                && $role->isEnterprise()
                // The hosted 5-member cap is only enforced in createMember, so honour it
                // here rather than quietly pushing the schedule over it. Ownership moves
                // either way; the courtesy seat is what yields. The count already includes
                // both the new owner (just synced) and the departing one, so it IS the
                // final headcount if we keep them - hence <= rather than <.
                && (! config('app.hosted') || $role->members()->count() <= 5);

            if ($keptPrevious) {
                RoleUser::where('role_id', $role->id)
                    ->where('user_id', $previousOwnerId)
                    ->update(['level' => 'admin']);
            } else {
                RoleUser::where('role_id', $role->id)
                    ->where('user_id', $previousOwnerId)
                    ->delete();

                // Capture the channel ids before nulling them, so the after-commit
                // teardown still knows what to delete.
                $webhooks = [
                    'google_id' => $role->google_webhook_id,
                    'google_resource' => $role->google_webhook_resource_id,
                    'microsoft_id' => $role->microsoft_webhook_id,
                ];

                $this->releaseCalendarRows($role, $previousOwner);
            }

            $this->repointEvents($role, $previousOwnerId, $newOwner->id);

            $role->save();

            $locked->status = 'accepted';
            $locked->to_user_id = $newOwner->id;
            $locked->responded_at = now();
            $locked->save();

            AuditService::log(
                AuditService::SCHEDULE_TRANSFER_ACCEPT,
                $newOwner->id,
                'Role',
                $role->id,
                null,
                null,
                'from user_id:'.$previousOwnerId,
            );

            return true;
        });

        if (! $moved) {
            return false;
        }

        // Step 3. Nothing below may throw into the caller: the handover is already done.
        $role->refresh();

        $this->forgetPaymentMethods($role);

        if (! $keptPrevious && $previousOwner) {
            $this->releaseCalendarWebhooks($role, $previousOwner, $webhooks);
        }

        // Cosmetic, and deliberately out of the transaction: writing `users` while holding
        // the `roles` lock would invert User::claimRolesByPhone(), which takes users then
        // roles, and give the pair a deadlock cycle.
        $this->moveDefaultSchedule($role, $previousOwner, $newOwner);

        $transfer->refresh()->loadMissing('role');

        SendQueuedEmail::dispatch(
            new ScheduleTransferCompleted($transfer, false, $hadSubscription),
            $newOwner->email,
            null,
            $newOwner->language_code,
        );

        if ($previousOwner) {
            SendQueuedEmail::dispatch(
                new ScheduleTransferCompleted($transfer, true, $hadSubscription),
                $previousOwner->email,
                null,
                $previousOwner->language_code,
            );
        }

        return true;
    }

    /**
     * Point each side's "default schedule" at something still true. Idempotent, so running
     * it after the commit costs nothing if the request dies in between.
     */
    private function moveDefaultSchedule(Role $role, ?User $previousOwner, User $newOwner): void
    {
        if ($previousOwner && $previousOwner->default_role_id === $role->id) {
            $previousOwner->default_role_id = null;
            $previousOwner->saveQuietly();
        }

        if (! $newOwner->default_role_id) {
            $newOwner->default_role_id = $role->id;
            $newOwner->saveQuietly();
        }
    }

    /**
     * Move the events this schedule owns onto the new owner.
     *
     * Scoped to `user_id = previous owner` on purpose. An event created by an admin team
     * member carries THAT admin's user_id and settles into their gateway account today
     * (EventRepo::saveEvent); the handover must not redirect their money either.
     *
     * The creator_role_id branch mirrors Event::ticketAllowanceRole(), including its
     * fallback for rows predating the column - except that the fallback additionally
     * requires this schedule to be the event's ONLY listing, so a legacy event shown on
     * two of the previous owner's schedules is not claimed by whichever transfers first.
     * A curated event owned by somebody else's schedule is never touched.
     */
    private function repointEvents(Role $role, int $previousOwnerId, int $newOwnerId): void
    {
        Event::where('user_id', $previousOwnerId)
            ->where(function ($query) use ($role) {
                $query->where('creator_role_id', $role->id)
                    ->orWhere(function ($legacy) use ($role) {
                        $legacy->whereNull('creator_role_id')
                            ->whereExists(function ($exists) use ($role) {
                                $exists->selectRaw(1)
                                    ->from('event_role')
                                    ->whereColumn('event_role.event_id', 'events.id')
                                    ->where('event_role.role_id', $role->id);
                            })
                            ->whereNotExists(function ($other) use ($role) {
                                $other->selectRaw(1)
                                    ->from('event_role')
                                    ->whereColumn('event_role.event_id', 'events.id')
                                    ->where('event_role.role_id', '!=', $role->id);
                            });
                    });
            })
            // toBase(): an ownership move is not a content edit, and Eloquent's update()
            // would stamp updated_at on every row - which feeds sitemap <lastmod>
            // (SitemapController) and GrowthExportService's recently-updated metric.
            ->toBase()
            ->update(['user_id' => $newOwnerId]);
    }

    /**
     * The DB half of disconnecting the departing owner's calendars. Runs inside the
     * transaction because it is pure row work.
     *
     * Role::hasGoogleCalendarIntegration() reads the OWNER's pivot row, so removing that
     * row (done by the caller) is what puts sync back to dormant until the new owner
     * reconnects. Nulling the channel ids here means nothing re-uses a subscription that
     * belongs to someone else's OAuth token.
     *
     * Both calendar tables are cleaned. RoleController::removeMember only clears the
     * Google one - that gap is not worth copying.
     */
    private function releaseCalendarRows(Role $role, User $previousOwner): void
    {
        CalendarSync::where('user_id', $previousOwner->id)
            ->where('role_id', $role->id)
            ->delete();

        MicrosoftCalendarSync::where('user_id', $previousOwner->id)
            ->where('role_id', $role->id)
            ->delete();

        $role->google_webhook_id = null;
        $role->google_webhook_resource_id = null;
        $role->microsoft_webhook_id = null;
    }

    /**
     * The network half: tell Google and Microsoft to stop pushing. After the commit, and
     * best effort - an undeleted channel expires on its own, and by now the ids are gone
     * from the row so nothing will act on its notifications anyway.
     *
     * $webhooks carries the ids captured before releaseCalendarRows() nulled them.
     */
    protected function releaseCalendarWebhooks(Role $role, User $previousOwner, array $webhooks): void
    {
        if (! empty($webhooks['google_id']) && ! empty($webhooks['google_resource'])) {
            try {
                if ($previousOwner->google_token) {
                    $google = app(\App\Services\GoogleCalendarService::class);
                    if ($google->ensureValidToken($previousOwner)) {
                        $google->deleteWebhook($webhooks['google_id'], $webhooks['google_resource']);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to clean up Google webhook during a schedule transfer', [
                    'role_id' => $role->id,
                    'webhook_id' => $webhooks['google_id'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! empty($webhooks['microsoft_id'])) {
            try {
                if ($previousOwner->microsoft_token) {
                    app(\App\Services\MicrosoftCalendarService::class)
                        ->deleteSubscription($previousOwner, $webhooks['microsoft_id']);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to clean up Outlook subscription during a schedule transfer', [
                    'role_id' => $role->id,
                    'subscription_id' => $webhooks['microsoft_id'],
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Stop charging the previous owner, without stripping the plan the schedule already
     * paid for.
     *
     * Cashier's customer is the Role, not the User (Cashier::useCustomerModel), so the
     * subscription would otherwise ride along with the schedule and keep billing an
     * account that no longer owns it. cancel() is cancel-at-period-end: the schedule keeps
     * its tier through the grace period, then Stripe's customer.subscription.deleted
     * webhook drops plan_type to free and the new owner subscribes on their own card.
     * That is what discussion #119 asked for.
     *
     * **Deliberately not wrapped in a try/catch.** If Stripe is unreachable the transfer
     * must not proceed: once roles.user_id has moved, SubscriptionController gates the
     * previous owner out of cancel() and portal(), so a swallowed failure would leave them
     * billed with no way to stop it. Throwing here leaves the offer open and costs nothing.
     *
     * Legacy plan_expires plans (admin-comped, referral credit) carry over untouched -
     * nobody is being charged for those.
     *
     * @return bool whether there was a live subscription to cancel, so the completion mail
     *              can describe what actually happened instead of guessing.
     */
    protected function cancelSubscription(Role $role): bool
    {
        if (! config('app.hosted')) {
            return false;
        }

        // Includes past_due on purpose: hasActiveSubscription() counts Stripe's dunning
        // window as active, and a subscription still being retried is exactly one the
        // departing owner should stop being charged for.
        if (! $role->hasActiveSubscription()) {
            return false;
        }

        $subscription = $role->subscription('default');

        if (! $subscription || $subscription->canceled()) {
            return false;
        }

        $subscription->cancel();

        return true;
    }

    /**
     * Detach the previous owner's stored cards from the schedule's Stripe customer.
     *
     * After the commit and best effort: pm_type / pm_last_four were already cleared in the
     * transaction, so the new owner never sees the card either way. A card left on the
     * Stripe customer is a privacy loose end, not a charge risk - the subscription is
     * already cancelled by the time this runs. Logged separately from the cancel so the
     * two failures are never confused in Sentry.
     */
    protected function forgetPaymentMethods(Role $role): void
    {
        if (! config('app.hosted') || ! $role->stripe_id) {
            return;
        }

        try {
            $role->deletePaymentMethods();
        } catch (\Exception $e) {
            \Log::warning('Failed to detach stored cards during a schedule transfer', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
