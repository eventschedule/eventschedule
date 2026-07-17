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

            // Check if we should send email
            if (config('app.hosted')) {
                // For hosted users, only send if role has email settings
                if (! $role || ! $role->hasEmailSettings()) {
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
    public function sendNewSaleNotification(Sale $sale, Event $event, Role $role): void
    {
        if (is_demo_role($role)) {
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

            $this->sendNewSaleNotification($sale, $event, $role);
        } catch (\Exception $e) {
            Log::error('Failed to send sale confirmation emails: '.$e->getMessage(), [
                'sale_id' => $sale->id,
                'event_id' => $sale->event_id,
            ]);
        }
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
