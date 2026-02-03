<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\TicketPurchase;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send ticket purchase email
     */
    public function sendTicketEmail(Sale $sale, ?Role $role = null, bool $queue = true): bool
    {
        // Skip sending to test/example email addresses
        if ($this->isTestEmail($sale->email)) {
            Log::info('Skipping email to test address: '.$sale->email);

            return false;
        }

        // Skip sending for demo role
        if (is_demo_role($role)) {
            Log::info('Skipping email for demo role');

            return false;
        }

        try {
            $event = $sale->event;

            // If no role provided, try to get it from the event
            if (! $role && $event) {
                // Load roles if not already loaded
                if (! $event->relationLoaded('roles')) {
                    $event->load('roles');
                }
                // Get the venue role if available, otherwise get the first role
                $role = $event->venue ?: $event->roles->first();
            }

            // Check if we should send email
            if (config('app.hosted')) {
                // For hosted users, only send if role has email settings
                if (! $role || ! $role->hasEmailSettings()) {
                    return false;
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
                if (config('app.hosted') && $role && $role->hasEmailSettings()) {
                    $this->configureRoleMailer($role);
                    $mailerName = 'role_'.$role->id;
                    Mail::mailer($mailerName)->to($sale->email)->send($mailable);
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

            return false;
        }
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
