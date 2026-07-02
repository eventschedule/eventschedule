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
        $roles = Role::where('is_deleted', false)
            ->whereNotNull('graphic_settings')
            ->get();

        foreach ($roles as $role) {
            try {
                $settings = $role->graphic_settings ?? [];

                // Silent skip for roles where the user hasn't enabled scheduled emails -
                // avoids log spam on instances with many roles.
                if (empty($settings['enabled'])) {
                    continue;
                }

                if (! $role->isEnterprise()) {
                    continue;
                }

                $recipientEmails = $settings['recipient_emails'] ?? '';
                if (empty($recipientEmails)) {
                    continue;
                }

                $reason = $this->shouldSendReason($role, $settings);
                if ($reason !== 'ok') {
                    continue;
                }

                $service = new GraphicEmailService;
                $result = $service->sendGraphicEmail($role, $recipientEmails);

                if ($result) {
                    $settings['last_sent_at'] = now()->toIso8601String();
                    $role->graphic_settings = $settings;
                    $role->save();

                    \Log::info('SendGraphicEmails: sent', [
                        'role_id' => $role->id,
                        'subdomain' => $role->subdomain,
                        'recipient_count' => count(array_filter(array_map('trim', explode(',', $recipientEmails)))),
                        // True when the schedule's custom SMTP is failing and the
                        // email went out via the platform mailer fallback instead.
                        'via_platform_fallback' => $role->isEmailSettingsFailureActive(),
                    ]);
                }
                // A false result means the schedule's custom SMTP is failing and
                // there were no upcoming flyer events to send via the fallback.
            } catch (\Exception $e) {
                \Log::error('Failed to send graphic email for role '.$role->subdomain.': '.$e->getMessage());
            }
        }
    }

    /**
     * Determine whether the role is due for a send right now.
     *
     * Returns 'ok' if the email should be sent, otherwise a short reason string
     * identifying which gate blocked it. Blocked roles are skipped silently.
     *
     * Period model: each frequency defines a "period" (a calendar day for daily,
     * a calendar day for weekly, a calendar month for monthly). At most one send
     * is recorded per period via $settings['last_sent_at']. The gate fires once
     * the scheduled send time inside the current period has passed and the
     * period has not yet had a send recorded - so cron drift past the exact
     * sendHour no longer loses sends within the same day/period.
     *
     * Note: if two cron processes evaluate this gate simultaneously they could
     * both pass before either persists last_sent_at. The withoutOverlapping()
     * lock on the scheduler plus the td_hourly cache lock in translateData()
     * make this very unlikely in practice; row-level locking would close it
     * fully but is deferred until observed.
     */
    protected function shouldSendReason(Role $role, array $settings): string
    {
        $frequency = $settings['frequency'] ?? 'weekly';
        $sendDay = (int) ($settings['send_day'] ?? 1);
        $sendHour = (int) ($settings['send_hour'] ?? 9);
        $lastSentAt = $settings['last_sent_at'] ?? null;

        $timezone = $role->timezone ?: 'UTC';
        $now = Carbon::now($timezone);
        $lastSent = $lastSentAt ? Carbon::parse($lastSentAt)->setTimezone($timezone) : null;

        switch ($frequency) {
            case 'daily':
                if ($now->hour < $sendHour) {
                    return 'wrong_hour';
                }
                if ($lastSent && $lastSent->toDateString() === $now->toDateString()) {
                    return 'already_sent_this_period';
                }

                return 'ok';

            case 'weekly':
                $sendDays = $settings['send_days'] ?? null;

                // Backward compat: roles configured before send_days (plural) was added
                // only have send_day (singular) holding the day-of-week.
                if ($sendDays === null && isset($settings['send_day'])) {
                    $legacyDay = (int) $settings['send_day'];
                    if ($legacyDay >= 0 && $legacyDay <= 6) {
                        $sendDays = [$legacyDay];
                    }
                }

                $sendDays = array_values(array_filter(array_map('intval', (array) ($sendDays ?? [])), fn ($d) => $d >= 0 && $d <= 6));

                if (empty($sendDays)) {
                    return 'weekly_no_days_configured';
                }
                if (! in_array($now->dayOfWeek, $sendDays, true)) {
                    return 'wrong_day';
                }
                if ($now->hour < $sendHour) {
                    return 'wrong_hour';
                }
                if ($lastSent && $lastSent->toDateString() === $now->toDateString()) {
                    return 'already_sent_this_period';
                }

                return 'ok';

            case 'monthly':
                // Clamp to the month length so a send_day of 29-31 still fires on the
                // last day of shorter months (e.g. day 31 in a 30-day month).
                $sendDay = min($sendDay, $now->daysInMonth);

                $isScheduledDayPassed = $now->day > $sendDay
                    || ($now->day === $sendDay && $now->hour >= $sendHour);

                if (! $isScheduledDayPassed) {
                    return $now->day === $sendDay ? 'wrong_hour' : 'wrong_day';
                }
                if ($lastSent && $lastSent->year === $now->year && $lastSent->month === $now->month) {
                    return 'already_sent_this_period';
                }

                return 'ok';

            default:
                return 'wrong_day';
        }
    }
}
