<?php

namespace App\Traits;

use App\Exceptions\BusinessException;
use App\Exceptions\SlotUnavailableException;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AppointmentService;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Services\WebhookService;
use App\Support\AppointmentRescheduleGate;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

/**
 * Shared reschedule eligibility + write-and-respond, used by BOTH the guest secret-link endpoints
 * (AppointmentController) and the owner AP endpoints (AppointmentTypeController).
 *
 * It lives here rather than on one controller because the two paths must agree exactly on what may be
 * moved - AppointmentTypeController::bookingCancel() re-derived its own guards and ended up missing the
 * unpaid-card-hold one as a result.
 */
trait ReschedulesAppointments
{
    /**
     * Whether a booking may be moved, or a translated reason why not.
     *
     * Delegates to AppointmentRescheduleGate, which is where the rule lives so the AP Bookings rows and
     * the guest manage page can ask the same question without reaching through a controller. Kept here as
     * a thin pass-through because both controllers already call it by this name.
     *
     * @return null|string Null when the move is allowed, otherwise the message to show.
     */
    public function rescheduleBlockedReason(
        Event $event,
        Sale $sale,
        ?Role $role = null,
        bool $checkCooldown = true
    ): ?string {
        return AppointmentRescheduleGate::blockedReason($event, $sale, $role, $checkCooldown);
    }

    /**
     * Shared move-then-respond used by the guest and owner endpoints.
     *
     * Every response is JSON on purpose. The picker posts with fetch() and parses the body; a redirect
     * lands in its parse-failure branch and tells the guest their session expired, which is both wrong
     * and unrecoverable-looking.
     */
    public function applyReschedule(
        Sale $sale,
        Event $event,
        ?Role $role,
        string $slotUtc,
        string $initiator,
        ?string $fromSlotUtc = null,
        ?string $guestTimezone = null,
        bool $ownerMode = false,
        bool $notifyGuest = true,
        ?string $note = null
    ) {
        $type = $event->appointmentType;

        try {
            $oldStartsAt = $this->rescheduleService()->reschedule(
                $sale, $slotUtc, $initiator, $fromSlotUtc, $guestTimezone, $ownerMode
            );
        } catch (SlotUnavailableException $e) {
            // Hand back the refreshed day so the picker's existing slot-taken recovery can redraw it -
            // with the exclusion, or it would erase the booking's own current slot from the grid.
            return response()->json([
                'error' => $e->getMessage(),
                'slots' => $this->refreshDayForReschedule($type, $slotUtc, $event->id, $ownerMode),
            ], 422);
        } catch (BusinessException $e) {
            // No `slots` for anything that is not an availability problem. The picker's recovery handler
            // replaces the whole day and clears the selection when it sees that key, which for "wait a
            // moment" or "this can no longer be changed" just loses the guest their place for no reason.
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (QueryException $e) {
            // reschedule() holds two row locks, so a lock-wait timeout is reachable on a busy schedule.
            // Uncaught, it fell through to the picker's slotTaken fallback and told the guest their time
            // had just been booked - so they picked another slot and took it, leaving the original booking
            // un-moved with no trace. Never surface the driver message; report it and say something true.
            report($e);

            return response()->json(['error' => __('messages.appointments_reschedule_failed')], 500);
        }

        $moved = $oldStartsAt !== $event->fresh()->starts_at;

        // The move is COMMITTED by this point, so nothing below may turn a success into an error page.
        // AuditService already swallows Throwable and EmailService swallows Exception, but
        // WebhookService::dispatch() runs Event::toApiData() inline and was completely unguarded.
        if ($moved) {
            try {
                AuditService::log(
                    AuditService::EVENT_UPDATE,
                    $initiator === 'owner' ? auth()->id() : null,
                    'Event',
                    $event->id,
                    ['starts_at' => $oldStartsAt],
                    ['starts_at' => $event->fresh()->starts_at],
                    'appointment_reschedule:'.$initiator
                );

                (new EmailService)->sendAppointmentRescheduledEmails(
                    $sale->fresh(), $oldStartsAt, $initiator, $notifyGuest, $note
                );

                WebhookService::dispatch('event.updated', $event->fresh());
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            // ?moved=1 only when something actually moved. The grid excludes the booking's own event, so
            // its current slot renders as an ordinary selectable button - and picking it used to land the
            // guest on a green "Your appointment has been moved" band telling them to update their
            // calendar, for a booking that had not changed and about which no mail was sent.
            'redirect_url' => $initiator === 'owner'
                ? route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings'])
                : $this->guestManageUrl($sale->fresh()).($moved ? '?moved=1' : ''),
        ]);
    }

    /**
     * One day's slots, with this booking excluded, for the picker's slot-taken recovery. The exclusion
     * matters: the recovery handler replaces the whole day, so without it the booking's own current slot
     * disappears from the grid with no way to re-select it.
     */
    protected function refreshDayForReschedule(AppointmentType $type, string $slotUtc, ?int $excludeEventId = null, bool $ownerMode = false): array
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slotUtc, 'UTC')
                ->setTimezone($type->timezone())->format('Y-m-d');
        } catch (\Throwable $e) {
            $date = Carbon::now($type->timezone())->format('Y-m-d');
        }

        return app(AppointmentService::class)->availableSlots($type, $date, 1, null, true, $excludeEventId, $ownerMode);
    }

    /**
     * The guest's own manage page.
     *
     * Built here rather than calling $this->manageUrl(), which exists only on AppointmentController: the
     * owner controller uses this trait too and has no manage page of its own, so an abstract declaration
     * would be a fatal error there and a plain call was a latent "Call to undefined method" waiting for
     * the first owner-side call that passed a non-'owner' initiator.
     */
    protected function guestManageUrl(Sale $sale): string
    {
        return route('appointments.manage', [
            'event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id),
            'secret' => $sale->secret,
        ]);
    }

    /** The service, whether or not the using class injected it. */
    protected function rescheduleService(): AppointmentService
    {
        return property_exists($this, 'appointments') && $this->appointments
            ? $this->appointments
            : app(AppointmentService::class);
    }
}
