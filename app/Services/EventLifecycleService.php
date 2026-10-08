<?php

namespace App\Services;

use App\Jobs\NotifyEventCancelled;
use App\Models\AnalyticsEventsDaily;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Cancelling, restoring, publishing and deleting an event, in one place.
 *
 * The four used to live in the controllers that offered them, so the admin portal, the API and a
 * calendar-driven deletion each did a different subset: three copies of the code that stops a
 * boost, two of the one that cancels a booking, and no way for anything without a request (a feed,
 * a job) to do any of it. Nothing here reads the request, the signed-in user or the session: the
 * caller says who acted and which of the side effects it wants, and reads the outcome.
 *
 * The order inside each method is the order the admin portal has always used, because some of it
 * is about money: boosts and installment plans are unwound BEFORE the flag flips.
 * Tests\Feature\Characterization\EventLifecycleCharacterizationTest holds each entry point to what
 * it did before the move.
 */
class EventLifecycleService
{
    public const CANCELLED = 'cancelled';

    public const ALREADY_CANCELLED = 'already_cancelled';

    public const BOOKING_CANCELLED = 'booking_cancelled';

    public const RESTORED = 'restored';

    public const NOT_CANCELLED = 'not_cancelled';

    public const PUBLISHED = 'published';

    public const NOT_PUBLISHABLE = 'not_publishable';

    /** The longest note a cancellation notice carries to the people it is sent to. */
    public const NOTE_LENGTH = 280;

    /**
     * Soft-cancel an event: the row and its sales stay, it stops being advertised and sold, it
     * leaves connected calendars, and the people registered are told if the caller asks.
     *
     * The flags exist for the callers that must NOT do the whole of it. A deletion that arrives
     * from a connected calendar flips the flag and nothing else (pushing back out would loop, and
     * a calendar entry going away is weaker evidence than an owner pressing Cancel).
     *
     * @param  bool  $asBooking  An appointment booking is cancelled through its sale, which frees
     *                           the slot and tells the guest; false treats it as any other event.
     */
    public function cancel(
        Event $event,
        ?int $actorUserId = null,
        bool $notifyAttendees = false,
        ?string $note = null,
        bool $cancelBoosts = true,
        bool $stopInstallments = true,
        bool $pushToCalendars = true,
        bool $webhook = true,
        bool $bumpSequence = true,
        bool $asBooking = true,
        bool $audit = true,
    ): string {
        if ($event->is_cancelled) {
            return self::ALREADY_CANCELLED;
        }

        if ($audit) {
            AuditService::log(AuditService::EVENT_CANCEL, $actorUserId, 'Event', $event->id, null, null, $event->name);
        }

        if ($asBooking && $event->appointment_type_id) {
            $this->cancelBooking($event);

            return self::BOOKING_CANCELLED;
        }

        // Stop spending and stop charging before the flag flips. Cancelling an event leaves its
        // sales `paid` on purpose, so nothing below reaches Sale::booted()'s cancel branch: without
        // the second call a buyer's card goes on being debited monthly for an event that is off.
        if ($cancelBoosts) {
            $this->cancelBoosts($event);
        }
        if ($stopInstallments) {
            $this->stopInstallmentPlans($event);
        }

        $fields = ['is_cancelled' => true, 'cancelled_at' => now()];
        if ($bumpSequence) {
            // Subscribed calendars pick a change up by its sequence.
            $fields['ical_sequence'] = (int) $event->ical_sequence + 1;
        }
        // save(), not saveQuietly(): federation and the homepage wall hang off the saved event.
        $event->forceFill($fields)->save();

        if ($pushToCalendars) {
            $event->dispatchCalendarSync('delete');
        }

        if ($webhook && ! $event->is_draft) {
            WebhookService::dispatch('event.cancelled', $event);
        }

        // Whoever there is to tell: buyers AND the interest list. notifyCancellation() applies
        // the "this schedule mails from its own address" gate to the buyers' half itself, where it
        // belongs. Applied here it silenced the interest list too, and those people were told on
        // the schedule's own page that they would hear if anything changed.
        if ($notifyAttendees && ! $event->is_draft && EventChangeNotifier::hasAnyoneToTell($event)) {
            NotifyEventCancelled::dispatch($event->id, $note ? Str::limit($note, self::NOTE_LENGTH, '') : null);
            $event->forceFill(['attendees_notified_at' => now()])->saveQuietly();
        }

        return self::CANCELLED;
    }

    /**
     * Undo a cancellation: sales reopen and the event goes back onto connected calendars. Nobody is
     * emailed (a second surprise is worse than none).
     */
    public function restore(
        Event $event,
        ?int $actorUserId = null,
        bool $pushToCalendars = true,
        bool $webhook = true,
        bool $audit = true,
    ): string {
        if (! $event->is_cancelled) {
            return self::NOT_CANCELLED;
        }

        if ($audit) {
            AuditService::log(AuditService::EVENT_RESTORE, $actorUserId, 'Event', $event->id, null, null, $event->name);
        }

        $event->forceFill([
            'is_cancelled' => false,
            'cancelled_at' => null,
            'ical_sequence' => (int) $event->ical_sequence + 1,
        ])->save();

        if (! $event->is_draft) {
            if ($pushToCalendars) {
                $event->dispatchCalendarSync('create');
            }
            if ($webhook) {
                WebhookService::dispatch('event.updated', $event);
            }
        }

        return self::RESTORED;
    }

    /**
     * Make a draft public. An internal event is never public, and neither is a cancelled one:
     * publishing it used to push a cancelled event onto connected calendars as though it were on.
     *
     * @param  Role|null  $through  The schedule the publish runs through: its calendar connections
     *                              are the ones pushed to.
     */
    public function publish(Event $event, ?int $actorUserId = null, ?Role $through = null, bool $audit = true): string
    {
        if (! $event->is_draft || $event->is_internal || $event->is_cancelled) {
            return self::NOT_PUBLISHABLE;
        }

        $event->setVisibilityState('public');
        $event->save();

        if ($through) {
            if ($through->syncsToGoogle()) {
                $event->syncToGoogleCalendar('create');
            }
            if ($through->syncsToMicrosoft()) {
                $event->syncToMicrosoftCalendar('create');
            }
            if ($through->syncsToCalDAV()) {
                $event->syncToCalDAV('create');
            }
        }

        WebhookService::dispatch('event.created', $event);

        if ($audit) {
            AuditService::log(AuditService::EVENT_PUBLISH, $actorUserId, 'Event', $event->id, null, null, $event->name);
        }

        return self::PUBLISHED;
    }

    /**
     * Delete an event nobody holds a ticket or a booking for. The caller has already decided this
     * event may go: sales.event_id cascades, so an event with sales must never reach this.
     */
    public function delete(Event $event, ?int $actorUserId = null, bool $audit = true): void
    {
        if ($audit) {
            AuditService::log(AuditService::EVENT_DELETE, $actorUserId, 'Event', $event->id, null, null, $event->name);
        }

        // Before the row goes, or a campaign on Meta is left running with nothing behind it.
        $this->cancelBoosts($event);

        // The payload is built while there is still an event to describe.
        if (! $event->is_draft) {
            WebhookService::dispatch('event.deleted', $event, [
                'event' => 'event.deleted',
                'timestamp' => now()->toIso8601String(),
                'data' => $event->toApiData(),
            ]);
        }

        $event->delete();
    }

    /**
     * Cancel an appointment booking on the owner's side. The live sale is cancelled, and its hook
     * (AppointmentService::cancelFromSale) soft-cancels the event and frees the slot; the guest is
     * emailed, and the owner is reminded to refund a booking that was paid for. With no live sale
     * left, the event is cancelled directly.
     */
    public function cancelBooking(Event $event): void
    {
        $sale = Sale::where('event_id', $event->id)
            ->whereNotIn('status', ['cancelled', 'refunded', 'expired'])
            ->first();

        if ($sale) {
            $wasPaid = $sale->status === 'paid';
            $wasPaidMoney = $wasPaid && (float) $sale->payment_amount > 0;
            $sale->status = 'cancelled';
            $sale->save();
            if ($wasPaid) {
                AnalyticsEventsDaily::decrementSale($event->id, (float) $sale->payment_amount, $sale->created_at->toDateString());
            }
            app(EmailService::class)->sendAppointmentGuestCancellation($sale);
            if ($wasPaidMoney) {
                app(EmailService::class)->sendAppointmentOwnerCancellation($sale, true);
            }

            return;
        }

        if (! $event->is_cancelled) {
            $event->forceFill([
                'is_cancelled' => true,
                'cancelled_at' => now(),
                'ical_sequence' => (int) $event->ical_sequence + 1,
            ])->saveQuietly();
            $event->dispatchCalendarSync('delete');
        }
    }

    /**
     * Cancel and refund every boost campaign still running for an event, so a cancelled or deleted
     * event never keeps buying ads.
     */
    public function cancelBoosts(Event $event): void
    {
        $campaigns = BoostCampaign::where('event_id', $event->id)->unsettled()->get();

        foreach ($campaigns as $campaign) {
            try {
                $cancelled = DB::transaction(function () use ($campaign) {
                    $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);
                    if (! $campaign || ! $campaign->canBeCancelled()) {
                        return false;
                    }
                    $campaign->update([
                        'status' => 'cancelled',
                        'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null,
                    ]);

                    return true;
                });

                if (! $cancelled) {
                    continue;
                }

                if ($campaign->meta_campaign_id) {
                    (new MetaAdsService)->deleteCampaign($campaign);
                }

                // Gate the STRIPE call, not the refund. settlePayment()'s credit branch debits
                // boost_credit regardless of mode, so gating the whole block meant deleting on
                // selfhost destroyed the advertiser's wallet balance outright. refundOnCancellation()
                // reaches Stripe only when there is an intent, which a selfhost campaign never has.
                $campaign->refresh();
                if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                    $billing = new BoostBillingService;
                    if ($campaign->billing_status === 'pending') {
                        if (config('app.hosted') && ! config('app.is_testing') && $campaign->stripe_payment_intent_id) {
                            $billing->cancelPaymentIntent($campaign);
                        }
                    } else {
                        $billing->refundOnCancellation($campaign);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to cancel boost campaign', [
                    'campaign_id' => $campaign->id,
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Stop the off-session charges of every installment plan on this event. Failures are logged
     * rather than thrown: the cancellation itself must still complete.
     */
    private function stopInstallmentPlans(Event $event): void
    {
        try {
            $saleIds = Sale::where('event_id', $event->id)
                ->where('is_deleted', false)
                ->pluck('id');

            $cancelled = app(InstallmentService::class)->cancelPlansForSales($saleIds, 'event_cancelled');

            if ($cancelled) {
                Log::info('Cancelled installment plans for cancelled event', [
                    'event_id' => $event->id,
                    'plans' => $cancelled,
                ]);
            }
        } catch (\Exception $e) {
            report($e);
            Log::error('Failed to cancel installment plans for cancelled event', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
