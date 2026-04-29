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

        $considered = 0;
        $sent = 0;
        $skipped = 0;

        foreach ($roles as $role) {
            $considered++;

            try {
                $settings = $role->graphic_settings ?? [];

                // Silent skip for roles where the user hasn't enabled scheduled emails -
                // avoids log spam on instances with many roles.
                if (empty($settings['enabled'])) {
                    $skipped++;

                    continue;
                }

                if (! $role->isEnterprise()) {
                    $this->logSkip($role, 'not_enterprise');
                    $skipped++;

                    continue;
                }

                $recipientEmails = $settings['recipient_emails'] ?? '';
                if (empty($recipientEmails)) {
                    $this->logSkip($role, 'no_recipients');
                    $skipped++;

                    continue;
                }

                $reason = $this->shouldSendReason($role, $settings);
                if ($reason !== 'ok') {
                    $this->logSkip($role, $reason);
                    $skipped++;

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
                    ]);

                    $sent++;
                } else {
                    $this->logSkip($role, 'no_flyer_events');
                    $skipped++;
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send graphic email for role '.$role->subdomain.': '.$e->getMessage());
                $skipped++;
            }
        }

        \Log::info('SendGraphicEmails: complete', [
            'considered' => $considered,
            'sent' => $sent,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Determine whether the role is due for a send right now.
     *
     * Returns 'ok' if the email should be sent, otherwise a short reason string
     * identifying which gate blocked it. Caller is responsible for any logging
     * (so that disabled roles can be skipped silently before this is called).
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

    protected function logSkip(Role $role, string $reason): void
    {
        \Log::info('SendGraphicEmails: skipped', [
            'role_id' => $role->id,
            'subdomain' => $role->subdomain,
            'reason' => $reason,
        ]);
    }
}
