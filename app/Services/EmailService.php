<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\GiftCardReceipt;
use App\Mail\GiftCardRecipient;
use App\Mail\GiftCardSaleNotification;
use App\Mail\NewSaleNotification;
use App\Mail\PassBookingConfirmation;
use App\Mail\TicketPurchase;
use App\Models\Event;
use App\Models\GiftCard;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    const ERROR_NOT_CONFIGURED = 'not_configured';

    const ERROR_SEND_FAILED = 'send_failed';

    const ERROR_SKIPPED = 'skipped';

    /**
     * Send ticket purchase email
     */
    public function sendTicketEmail(Sale $sale, ?Role $role = null, bool $queue = true): string|true
    {
        // Appointment bookings never get the QR ticket email. A resend re-sends the appointment
        // confirmation, and only for a booking that is actually confirmed.
        if ($sale->event?->appointment_type_id) {
            if (! $sale->confirmed_at || $sale->event->is_cancelled) {
                return self::ERROR_SKIPPED;
            }
            $this->sendAppointmentGuestMail($sale, \App\Mail\AppointmentConfirmed::class);

            return true;
        }

        // Skip sending to test/example email addresses
        if ($this->isTestEmail($sale->email)) {
            return self::ERROR_SKIPPED;
        }

        // Skip sending for demo role
        if (is_demo_role($role)) {
            return self::ERROR_SKIPPED;
        }

        try {
            $event = $sale->event;

            // If no role provided, try to get it from the event
            if (! $role && $event) {
                $role = $event->getRoleWithEmailSettings();
            }

            // Check if we should send email.
            //
            // A buyer's ticket is transactional: somebody paid, so they must receive what they
            // bought. When the schedule has no sender of its own we fall back to the platform
            // rather than dropping the mail. This matters most for the free plan, whose organizers
            // almost never configure SMTP, and which can now sell up to its monthly allowance -
            // without the fallback the tier would take money and deliver nothing, silently, because
            // this failure is a swallowed return value. The per-schedule sender remains the branded
            // upgrade, and every non-transactional mail (newsletters, sale notifications) keeps the
            // stricter gate.
            if (config('app.hosted')) {
                if (! $role) {
                    return self::ERROR_NOT_CONFIGURED;
                }
            } else {
                // For selfhosted, check if a real mail transport is configured
                $mailer = config('mail.default');
                if (in_array($mailer, ['log', 'array'])) {
                    return self::ERROR_NOT_CONFIGURED;
                }
            }

            $mailable = new TicketPurchase($sale, $event, $role);

            if ($queue) {
                SendQueuedEmail::dispatch(
                    $mailable,
                    $sale->email,
                    $role?->id,
                    app()->getLocale()
                );
            } else {
                if (config('app.hosted') && $role) {
                    app(RoleMailerService::class)->sendForRole($role, $sale->email, $mailable);
                } else {
                    Mail::to($sale->email)->send($mailable);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send ticket email: '.$e->getMessage(), [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::ERROR_SEND_FAILED;
        }
    }

    /**
     * Confirm a single advance booking (pass holder reserved an occurrence).
     * Mirrors sendTicketEmail's transport guards.
     */
    public function sendPassBookingConfirmation(Sale $sale, Event $bookedEvent, string $date, ?Role $role = null, bool $queue = true): string|true
    {
        if ($this->isTestEmail($sale->email)) {
            return self::ERROR_SKIPPED;
        }

        if (is_demo_role($role)) {
            return self::ERROR_SKIPPED;
        }

        try {
            if (! $role) {
                $role = $sale->event?->getRoleWithEmailSettings();
            }

            if (config('app.hosted')) {
                if (! $role || ! $role->hasEmailSettings()) {
                    return self::ERROR_NOT_CONFIGURED;
                }
            } else {
                $mailer = config('mail.default');
                if (in_array($mailer, ['log', 'array'])) {
                    return self::ERROR_NOT_CONFIGURED;
                }
            }

            $mailable = new PassBookingConfirmation($sale, $bookedEvent, $date, $role);

            if ($queue) {
                SendQueuedEmail::dispatch($mailable, $sale->email, $role?->id, app()->getLocale());
            } elseif (config('app.hosted') && $role) {
                app(RoleMailerService::class)->sendForRole($role, $sale->email, $mailable);
            } else {
                Mail::to($sale->email)->send($mailable);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send pass booking confirmation: '.$e->getMessage(), [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);

            return self::ERROR_SEND_FAILED;
        }
    }

    /**
     * Send gift card emails after activation: the card to the recipient, a receipt
     * to the purchaser, and a sale notification to opted-in editors. Only fires for
     * active cards. Call outside DB transactions so a rollback does not leave queued
     * emails behind.
     */
    public function sendGiftCardEmails(GiftCard $giftCard, bool $recipientOnly = false): void
    {
        if ($giftCard->status !== 'active') {
            Log::warning('Skipping gift card emails: card not active', [
                'gift_card_id' => $giftCard->id,
                'status' => $giftCard->status,
            ]);

            return;
        }

        $role = $giftCard->role;

        if (is_demo_role($role)) {
            return;
        }

        // Check the mail transport (mirrors sendTicketEmail)
        if (config('app.hosted')) {
            if (! $role || ! $role->hasEmailSettings()) {
                return;
            }
        } else {
            $mailer = config('mail.default');
            if (in_array($mailer, ['log', 'array'])) {
                return;
            }
        }

        try {
            if (! $this->isTestEmail($giftCard->recipient_email)) {
                SendQueuedEmail::dispatch(
                    new GiftCardRecipient($giftCard, $role),
                    $giftCard->recipient_email,
                    $role->id,
                    app()->getLocale()
                );
            }

            if ($recipientOnly) {
                return;
            }

            // Skip the separate receipt when the buyer sent the card to themselves
            if (strcasecmp($giftCard->purchaser_email, $giftCard->recipient_email) !== 0
                && ! $this->isTestEmail($giftCard->purchaser_email)) {
                SendQueuedEmail::dispatch(
                    new GiftCardReceipt($giftCard, $role),
                    $giftCard->purchaser_email,
                    $role->id,
                    app()->getLocale()
                );
            }

            foreach ($role->getEditorsWantingNotification('new_sale') as $editor) {
                SendQueuedEmail::dispatch(
                    new GiftCardSaleNotification($giftCard, $role, $editor),
                    $editor->email,
                    $role->id,
                    $editor->language_code ?? app()->getLocale()
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send gift card emails: '.$e->getMessage(), [
                'gift_card_id' => $giftCard->id,
            ]);
        }
    }

    /**
     * Send new sale notification to opted-in editors
     */
    public function sendNewSaleNotification(Sale $sale, Event $event, Role $role, bool $isFirstSale = false): void
    {
        if (is_demo_role($role)) {
            return;
        }

        // Ongoing sale notifications are Pro. The FIRST paid sale on an event always notifies,
        // whatever the plan: it costs nothing, it is the moment a free organizer finds out the
        // product works, and without it they would have to poll the Sales page to learn anything
        // sold at all.
        if (! $isFirstSale && ! $role->isPro()) {
            return;
        }

        $editors = $role->getEditorsWantingNotification('new_sale');

        // Push is an independent channel - mirror the email to editors who have
        // enabled push, regardless of whether email/SMTP is configured. No-op
        // when OneSignal is unconfigured or the editor has not opted in.
        foreach ($editors as $editor) {
            OneSignalService::pushToUser($editor, [
                'title_key' => 'messages.push_new_sale_title',
                'body_key' => 'messages.push_new_sale_body',
                'body_params' => ['event' => $event->name],
                'url' => route('sales'),
                'options' => ['icon' => $role->profile_image_url],
            ], $role);
        }

        // Email requires a configured mail transport.
        if (config('app.hosted')) {
            if (! $role->hasEmailSettings()) {
                return;
            }
        } else {
            $mailer = config('mail.default');
            if (in_array($mailer, ['log', 'array'])) {
                return;
            }
        }

        foreach ($editors as $editor) {
            try {
                $mailable = new NewSaleNotification($sale, $event, $role, $editor);

                SendQueuedEmail::dispatch(
                    $mailable,
                    $editor->email,
                    $role->id,
                    $editor->language_code ?? app()->getLocale()
                );
            } catch (\Exception $e) {
                Log::error('Failed to send sale notification: '.$e->getMessage(), [
                    'sale_id' => $sale->id,
                    'editor_id' => $editor->id,
                ]);
            }
        }
    }

    /**
     * Dispatch ticket confirmation to the buyer and new-sale notification to editors.
     * Only fires when the sale is fully paid - prevents leaks for abandoned checkouts,
     * failed payments, and amount-mismatch webhooks. Call outside DB transactions so a
     * rollback does not leave queued emails behind.
     */
    public function sendSaleConfirmationEmails(Sale $sale): void
    {
        if ($sale->status !== 'paid') {
            Log::warning('Skipping sale confirmation emails: sale not paid', [
                'sale_id' => $sale->id,
                'status' => $sale->status,
            ]);

            return;
        }

        // Appointment bookings confirm through the appointment service (idempotent): calendar sync
        // create + the guest AppointmentConfirmed email. Catches the Stripe webhook, payment-URL
        // success, and mark-paid, all of which route through here.
        if ($sale->event?->appointment_type_id) {
            $event = $sale->event;
            if ($event->is_cancelled) {
                // Late payment on a dead booking (e.g. a Stripe webhook reviving an expired sale):
                // never send "request received"/confirmation for it. Money reconciliation is manual.
                Log::warning('Payment arrived for a cancelled appointment booking', ['sale_id' => $sale->id]);

                return;
            }
            $pivot = $event->roles()->where('roles.id', $event->creator_role_id)->first()?->pivot;
            if ($pivot && is_null($pivot->is_accepted)) {
                // Paid but awaiting approval: send "request received", not a confirmation - but only
                // when the payment itself is the trigger (online methods). Cash/free pending mails
                // already went out at booking time; a later mark-paid must not repeat them.
                if (in_array($sale->payment_method, ['stripe', 'payment_url'], true)) {
                    $this->sendAppointmentPendingEmails($sale);
                }
            } else {
                app(\App\Services\AppointmentService::class)->confirm($sale);
            }

            return;
        }

        // Everything below belongs to ONE event: the ticket email carries that event's QR, the
        // push its name, and the new-sale notice goes to that event's schedule. A checkout that
        // spanned several events therefore has to run it once per leg - driving it from the order
        // primary alone sends the buyer a ticket for the leg that happens to anchor the order and
        // never tells them about the events they also paid for.
        //
        // orderLegs() is just [$sale] for an ordinary sale, so the single-event path is untouched.
        foreach ($sale->orderLegs() as $leg) {
            // A leg released before the payment landed keeps its own status - the paid cascade
            // deliberately skips cancelled/refunded/expired rows - so it gets no ticket.
            if ($leg->status !== 'paid') {
                continue;
            }

            $this->sendLegConfirmationEmails($leg);
        }
    }

    /**
     * The buyer's ticket and the owner's new-sale notice for ONE event's sale.
     *
     * Split out of sendSaleConfirmationEmails() so a multi-event order can run it per leg; the
     * group (individual-tickets) fan-out inside a single leg is unchanged.
     */
    private function sendLegConfirmationEmails(Sale $sale): void
    {
        try {
            $event = $sale->event;
            if (! $event) {
                return;
            }

            // Push is an independent channel: send buyer confirmation push(es)
            // even when no email transport is configured. Resolve any associated
            // role for the Pro-gate and notification branding.
            $pushRole = $event->getRoleWithEmailSettings() ?: $event->roles->first();
            if ($event->individual_tickets && $sale->group_id && $sale->isPrimarySale()) {
                foreach (Sale::where('group_id', $sale->id)->get() as $groupSale) {
                    $this->pushTicketConfirmation($groupSale, $event, $pushRole);
                }
            } else {
                $this->pushTicketConfirmation($sale, $event, $pushRole);
            }

            $role = $event->getRoleWithEmailSettings();
            if (! $role) {
                return;
            }

            if ($event->individual_tickets && $sale->group_id && $sale->isPrimarySale()) {
                $groupedSales = Sale::where('group_id', $sale->id)->get();
                foreach ($groupedSales as $groupSale) {
                    $this->sendTicketEmail($groupSale, $role);
                }
            } else {
                $this->sendTicketEmail($sale, $role);
            }

            // Is this the event's first paid sale? Counted excluding this one, and excluding the
            // grouped guest rows that individual-ticket orders create, so a single order of five
            // does not read as five sales. Drives the always-send carve-out in
            // sendNewSaleNotification(): a free organizer gets told their first ticket sold.
            $isFirstSale = ! Sale::where('event_id', $sale->event_id)
                ->where('id', '!=', $sale->id)
                ->where('status', 'paid')
                ->where('is_deleted', false)
                ->whereNotIn('payment_method', ['rsvp', 'import'])
                // Primaries and ungrouped sales. A primary carries group_id = its own id, so
                // whereNull() alone matched nothing on an individual-tickets event and every order
                // looked like the first. Same idiom TicketController uses in four other places.
                ->where(fn ($q) => $q->whereNull('group_id')->orWhereColumn('group_id', 'id'))
                ->exists();

            $this->sendNewSaleNotification($sale, $event, $role, $isFirstSale);
        } catch (\Exception $e) {
            Log::error('Failed to send sale confirmation emails: '.$e->getMessage(), [
                'sale_id' => $sale->id,
                'event_id' => $sale->event_id,
            ]);
        }
    }

    /** Guest confirmation + owner "new booking" notification (called by AppointmentService::confirm). */
    public function sendAppointmentConfirmationEmails(Sale $sale): void
    {
        $this->sendAppointmentGuestMail($sale, \App\Mail\AppointmentConfirmed::class);
        $this->sendAppointmentOwnerNotification($sale, 'booked');
    }

    /** Guest "request received" + owner "new request" notification (approval-required bookings). */
    public function sendAppointmentPendingEmails(Sale $sale): void
    {
        $this->sendAppointmentGuestMail($sale, \App\Mail\AppointmentPending::class);
        $this->sendAppointmentOwnerNotification($sale, 'pending');
    }

    /** Guest "complete your payment" notice with the manage link (payment_url bookings). */
    public function sendAppointmentPaymentDueEmail(Sale $sale): void
    {
        $this->sendAppointmentGuestMail($sale, \App\Mail\AppointmentPaymentDue::class);
    }

    /** Guest decline notice. */
    public function sendAppointmentDeclinedEmail(Sale $sale): void
    {
        $this->sendAppointmentGuestMail($sale, \App\Mail\AppointmentDeclined::class);
    }

    /** Guest cancellation notice (owner cancelled the booking). */
    public function sendAppointmentGuestCancellation(Sale $sale): void
    {
        $this->sendAppointmentGuestMail($sale, \App\Mail\AppointmentCancelled::class);
    }

    /**
     * Owner cancellation notice. $wasPaid is captured pre-cancel by the caller (the sale's live
     * status has already flipped by the time the queued mailable renders).
     */
    public function sendAppointmentOwnerCancellation(Sale $sale, ?bool $wasPaid = null): void
    {
        $this->sendAppointmentOwnerNotification($sale, 'cancelled', $wasPaid);
    }

    /**
     * Reschedule notices.
     *
     * Deliberately NOT AppointmentPending for the back-to-pending case: that mailable cannot attach an
     * .ics, so the guest's calendar would keep showing the old time while the owner's had already
     * moved - exactly backwards. AppointmentRescheduled covers both, with a pending variant.
     *
     * @param  string  $oldStartsAt  Previous UTC `starts_at`, passed as a scalar (SerializesModels
     *                               re-fetches the event, which now holds the new time).
     * @param  bool  $notifyGuest  False when an owner chose "Don't notify". The calendar sync and the
     *                             other editors' notifications still happen.
     */
    public function sendAppointmentRescheduledEmails(
        Sale $sale,
        string $oldStartsAt,
        string $initiator = 'guest',
        bool $notifyGuest = true,
        ?string $note = null
    ): void {
        $event = $sale->event;
        if (! $event) {
            return;
        }

        $pending = $event->isAwaitingCreatorApproval();

        if ($notifyGuest) {
            $this->sendAppointmentGuestMail($sale, \App\Mail\AppointmentRescheduled::class, [
                $oldStartsAt, $pending, $note,
            ]);
        }

        // The acting owner gets nothing, but their co-admins must: otherwise a second admin's calendar
        // silently shifts with no explanation. A guest-initiated move notifies everyone.
        //
        // 'rescheduled_pending', not a bare 'pending': a guest move on an approval-required type sends
        // the booking back to pending, and reporting that as an ordinary new request gave the owner an
        // email identical to any first-time booking - no old time, no short-notice warning, and no hint
        // that something they had already approved had changed underneath them.
        $this->sendAppointmentOwnerNotification(
            $sale,
            $pending ? 'rescheduled_pending' : 'rescheduled',
            null,
            $oldStartsAt,
            $initiator === 'owner' ? auth()->id() : null
        );
    }

    protected function sendAppointmentGuestMail(Sale $sale, string $mailClass, array $extraArgs = []): void
    {
        try {
            $event = $sale->event;
            if (! $event) {
                return;
            }
            $role = $event->getRoleWithEmailSettings() ?: $event->creatorRole;
            if ($this->isTestEmail($sale->email) || is_demo_role($role) || ! $this->appointmentCanSend($role)) {
                return;
            }

            SendQueuedEmail::dispatch(
                new $mailClass($sale, $event, $role, $event->appointmentType, ...$extraArgs),
                $sale->email,
                $role?->id,
                app()->getLocale()
            );
        } catch (\Exception $e) {
            Log::error('Failed to send appointment guest mail: '.$e->getMessage(), ['sale_id' => $sale->id]);
        }
    }

    protected function sendAppointmentOwnerNotification(
        Sale $sale,
        string $kind,
        ?bool $wasPaid = null,
        ?string $oldStartsAt = null,
        ?int $excludeUserId = null
    ): void {
        try {
            $event = $sale->event;
            if (! $event) {
                return;
            }
            $role = $event->getRoleWithEmailSettings() ?: $event->creatorRole;
            if (! $role || ! $this->appointmentCanSend($role)) {
                return;
            }

            // Pending requests block the slot until the owner acts, so they ride the default-on
            // 'new_request' preference; booked/cancelled notices use the opt-in 'new_sale' one.
            $isMove = in_array($kind, ['rescheduled', 'rescheduled_pending'], true);
            $preference = ($isMove || $kind === 'pending') ? 'new_request' : 'new_sale';

            // A move satisfies EITHER preference. getEditorsWantingNotification() returns true only when
            // the key is absent, so an owner who explicitly set new_request:false - entirely reasonable on
            // an auto-approve schedule - got no notice of any reschedule at all, which is the exact silent
            // calendar drift the notification exists to prevent. Someone who turned BOTH off has asked for
            // silence and still gets it.
            $editors = $role->getEditorsWantingNotification($preference);
            if ($isMove) {
                $editors = $editors->concat($role->getEditorsWantingNotification('new_sale'))->unique('id');
            }

            foreach ($editors as $editor) {
                if ($this->isTestEmail($editor->email)) {
                    continue;
                }
                // The editor who performed the action does not need telling.
                if ($excludeUserId && $editor->id === $excludeUserId) {
                    continue;
                }
                SendQueuedEmail::dispatch(
                    new \App\Mail\AppointmentBookedNotification($sale, $event, $role, $event->appointmentType, $kind, $wasPaid, $oldStartsAt),
                    $editor->email,
                    $role->id,
                    $editor->language_code ?? app()->getLocale()
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send appointment owner notification: '.$e->getMessage(), ['sale_id' => $sale->id]);
        }
    }

    protected function appointmentCanSend(?Role $role): bool
    {
        return config('app.hosted')
            ? ($role && $role->hasEmailSettings())
            : ! in_array(config('mail.default'), ['log', 'array']);
    }

    /**
     * Queue a ticket-confirmation push to the buyer (targeted by the hashed
     * email alias the guest portal sets on the confirmation page). No-op unless
     * OneSignal is configured and the buyer opted into push on their device.
     */
    protected function pushTicketConfirmation(Sale $sale, Event $event, ?Role $role): void
    {
        if ($this->isTestEmail($sale->email)) {
            return;
        }

        OneSignalService::pushToGuestEmail($sale->email, app()->getLocale(), [
            'title_key' => 'messages.push_ticket_title',
            'body_key' => 'messages.push_ticket_body',
            'body_params' => ['event' => $event->name],
            'url' => $event->getGuestUrl(false, $sale->event_date ?? null, true),
            'options' => ['icon' => $role?->profile_image_url],
        ], $role);
    }

    /**
     * Send test email to verify SMTP credentials
     */
    public function sendTestEmail(Role $role, string $toEmail): bool
    {
        // Block test emails from demo account
        if (is_demo_role($role)) {
            throw new \Exception('Cannot send test emails from demo account');
        }

        try {
            if (config('app.hosted')) {
                // For hosted, use role's SMTP settings
                if (! $role->hasEmailSettings()) {
                    throw new \Exception('Role does not have email settings configured');
                }

                // Configure role-specific mailer
                $this->configureRoleMailer($role);
            }
            // For selfhost, use system email settings (no configuration needed)

            // Send simple test email
            $fromAddress = config('mail.from.address');
            $fromName = config('mail.from.name');

            if ($role && $role->hasEmailSettings()) {
                $emailSettings = $role->getEmailSettings();
                if (! empty($emailSettings['from_address'])) {
                    $fromAddress = $emailSettings['from_address'];
                }
                if (! empty($emailSettings['from_name'])) {
                    $fromName = $emailSettings['from_name'];
                }
            }

            $testEmailCallback = function ($message) use ($toEmail, $fromAddress, $fromName) {
                $message->to($toEmail)
                    ->subject(__('messages.test_email_subject'))
                    ->from($fromAddress, $fromName);
            };

            // Use role-specific mailer if configured, otherwise use default
            if (config('app.hosted') && $role && $role->hasEmailSettings()) {
                $mailerName = 'role_'.$role->id;
                Mail::mailer($mailerName)->raw(__('messages.test_email_body'), $testEmailCallback);
            } else {
                Mail::raw(__('messages.test_email_body'), $testEmailCallback);
            }

            UsageTrackingService::track(UsageTrackingService::EMAIL_TEST, $role->id);

            // Successful test send proves the credentials work, so clear any
            // previously recorded failure flag and let queued emails resume
            // using the role's custom SMTP.
            $role->clearEmailSettingsFailure();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send test email: '.$e->getMessage(), [
                'role_id' => $role->id,
                'to_email' => $toEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Configure mailer with role-specific SMTP settings
     */
    protected function configureRoleMailer(Role $role): void
    {
        $emailSettings = $role->getEmailSettings();

        if (empty($emailSettings)) {
            return;
        }

        // Create a unique mailer name for this role
        $mailerName = 'role_'.$role->id;

        // Configure the mailer
        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $emailSettings['host'] ?? config('mail.mailers.smtp.host'),
            'port' => $emailSettings['port'] ?? config('mail.mailers.smtp.port'),
            'encryption' => $emailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
            'username' => $emailSettings['username'] ?? null,
            'password' => $emailSettings['password'] ?? null,
            'timeout' => null,
            'local_domain' => config('mail.mailers.smtp.local_domain'),
        ]);
    }

    /**
     * Check if email is a test/example address that should not receive emails
     */
    protected function isTestEmail(string $email): bool
    {
        $email = strtolower($email);

        // Block example.com and related domains (RFC 2606 reserved domains)
        $testDomains = [
            '@example.com',
            '@example.org',
            '@example.net',
            '@test.com',
            '@test.org',
            '@test.net',
            '@localhost',
        ];

        foreach ($testDomains as $domain) {
            if (str_contains($email, $domain)) {
                return true;
            }
        }

        return false;
    }
}
