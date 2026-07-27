<?php

namespace App\Support;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AppointmentService;

/**
 * The single answer to "may this booking be moved, and if not, what do we tell the person?"
 *
 * Its own class rather than a controller method because FOUR surfaces need the same answer and they are
 * not all controllers: the guest secret-link endpoints, the owner AP endpoints, the guest manage page,
 * and the AP Bookings rows. When the rows re-derived their own predicates instead they drifted in both
 * directions at once - offering Reschedule where the POST refuses it, and hiding it where it is allowed.
 *
 * A Blade view can call this without reaching through a controller, which is what the alternative would
 * have required.
 */
class AppointmentRescheduleGate
{
    /**
     * Null when the move is allowed, otherwise the translated message to show.
     *
     * Deliberately NOT expressed in terms of AppointmentController::bookingState(): that checks the pivot
     * before payment, so a requires_approval + stripe booking is pivot-null AND unpaid and reports
     * 'pending', which would slip straight through a state allow-list.
     *
     * $planIsCurrent lets a caller that already knows the schedule's plan status pass it in.
     * Role::isPro() fans out to a subscription lookup, a trial check and an enterprise check, so calling
     * this per row over a 50-row page would add up to 150 queries. Null means "work it out".
     */
    public static function blockedReason(
        Event $event,
        Sale $sale,
        ?Role $role = null,
        bool $checkCooldown = true,
        ?bool $planIsCurrent = null
    ): ?string {
        $role = $role ?: $event->creatorRole;
        $type = $event->appointmentType;

        $unavailable = fn () => __('messages.appointments_reschedule_unavailable', [
            'schedule' => $role?->name ?? '',
        ]);

        if ($sale->is_deleted || $event->is_cancelled
            || in_array($sale->status, ['cancelled', 'refunded', 'expired'], true)) {
            return $unavailable();
        }

        // An unpaid card hold expires on its CREATION clock (ReleaseTickets keys off sales.created_at),
        // so moving it would hand the guest a slot that silently dies. They pay first. Note cash
        // bookings are 'unpaid' too but never expire, so they are intentionally not caught here.
        if ($sale->status !== 'paid' && in_array($event->payment_method, ['stripe', 'payment_url'], true)) {
            return __('messages.appointments_reschedule_blocked_payment');
        }

        // Load-bearing on its own: bookingState() checks the pivot first, so a pending booking never
        // reports 'passed'.
        if ($event->getStartDateTime()->isPast()) {
            return $unavailable();
        }

        // A move commits a NEW slot, so it obeys the same rules as making one: the type has to still
        // exist and be active. Pointedly NOT the full isBookable() check - that also requires
        // paymentMethodAvailable(), which would freeze an already-paid booking the moment the owner
        // disconnected Stripe.
        if (! $type || $type->is_deleted || ! $type->is_active) {
            return $unavailable();
        }

        // Pro gate, matching the booking path. Cancel stays deliberately ungated, so a guest on a lapsed
        // schedule is never trapped - they can still release the slot.
        $planIsCurrent ??= ! config('app.hosted') || (bool) $role?->isPro();
        if (! $planIsCurrent) {
            return $unavailable();
        }

        // Report the cooldown so the manage page does not offer a Reschedule button, and the picker does
        // not render a whole calendar, for a move the write path will refuse. Null until a real move
        // happens, so this never fires on a freshly-made booking.
        //
        // Skipped on the POST paths ($checkCooldown false): reschedule() enforces it authoritatively
        // inside the row lock AND knows the one legitimate exemption - a duplicate delivery whose target
        // is already the live start, which must report success rather than a spurious conflict.
        if ($checkCooldown && $event->rescheduled_at
            && $event->rescheduled_at->diffInMinutes(now()) < AppointmentService::RESCHEDULE_COOLDOWN_MINUTES) {
            return __('messages.appointments_reschedule_too_soon');
        }

        return null;
    }
}
