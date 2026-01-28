<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\GraphicEmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendGraphicEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-graphic-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled graphic emails to roles with enabled email settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('Starting graphic email scheduler...');

        // Get all roles with graphic email enabled
        $roles = Role::where('is_deleted', false)
            ->whereNotNull('graphic_settings')
            ->get();

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($roles as $role) {
            try {
                if (! $this->shouldSendEmail($role)) {
                    $skippedCount++;

                    continue;
                }

                // Check if Enterprise (required for this feature)
                if (! $role->isEnterprise()) {
                    \Log::info('Skipping graphic email for non-Enterprise role: '.$role->subdomain);
                    $skippedCount++;

                    continue;
                }

                // Get and validate recipient emails
                $settings = $role->graphic_settings;
                $recipientEmails = $settings['recipient_emails'] ?? '';

                if (empty($recipientEmails)) {
                    \Log::info('Skipping graphic email - no recipient emails configured for role: '.$role->subdomain);
                    $skippedCount++;

                    continue;
                }

                // Send to all recipients in a single email (service handles parsing and validation)
                $service = new GraphicEmailService;
                $result = $service->sendGraphicEmail($role, $recipientEmails);

                if ($result) {
                    // Update last_sent_at
                    $settings['last_sent_at'] = now()->toIso8601String();
                    $role->graphic_settings = $settings;
                    $role->save();

                    $sentCount++;
                    \Log::info('Sent graphic email for role: '.$role->subdomain);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send graphic email for role '.$role->subdomain.': '.$e->getMessage());
            }
        }

        \Log::info("Graphic email scheduler completed. Sent: {$sentCount}, Skipped: {$skippedCount}");
    }

    /**
     * Check if email should be sent based on schedule settings
     */
    protected function shouldSendEmail(Role $role): bool
    {
        $settings = $role->graphic_settings;

        // Check if enabled
        if (empty($settings['enabled'])) {
            return false;
        }

        // Check if recipient emails are set
        if (empty($settings['recipient_emails'])) {
            return false;
        }

        $frequency = $settings['frequency'] ?? 'weekly';
        $sendDay = $settings['send_day'] ?? 1;
        $sendHour = $settings['send_hour'] ?? 9;
        $lastSentAt = $settings['last_sent_at'] ?? null;

        // Get current time in role's timezone or UTC
        $timezone = $role->timezone ?? 'UTC';
        $now = Carbon::now($timezone);
        $currentHour = $now->hour;
        $currentDayOfWeek = $now->dayOfWeek; // 0 (Sunday) - 6 (Saturday)
        $currentDayOfMonth = $now->day;

        // Check if it's the right hour
        if ($currentHour !== (int) $sendHour) {
            return false;
        }

        // Check if it's the right day based on frequency
        switch ($frequency) {
            case 'daily':
                // Send every day at the specified hour
                break;

            case 'weekly':
                // send_day: 0 = Sunday, 1 = Monday, ..., 6 = Saturday
                if ($currentDayOfWeek !== (int) $sendDay) {
                    return false;
                }
                break;

            case 'monthly':
                // send_day: 1-28 (day of month)
                if ($currentDayOfMonth !== (int) $sendDay) {
                    return false;
                }
                break;

            default:
                return false;
        }

        // Check last_sent_at to avoid duplicate sends within the same hour
        if ($lastSentAt) {
            $lastSent = Carbon::parse($lastSentAt);
            $hoursSinceLastSend = $now->diffInHours($lastSent);

            // Don't send if we already sent within the last hour
            if ($hoursSinceLastSend < 1) {
                return false;
            }

            // Additional safeguard based on frequency
            switch ($frequency) {
                case 'daily':
                    // Don't send more than once per day
                    if ($hoursSinceLastSend < 20) {
                        return false;
                    }
                    break;

                case 'weekly':
                    // Don't send more than once per week
                    if ($hoursSinceLastSend < 24 * 6) {
                        return false;
                    }
                    break;

                case 'monthly':
                    // Don't send more than once per month (25 days as buffer)
                    if ($hoursSinceLastSend < 24 * 25) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }
}
