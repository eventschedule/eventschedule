<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\AppointmentReminder;
use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendAppointmentReminders extends Command
{
    protected $signature = 'app:send-appointment-reminders';

    protected $description = 'Send reminder emails ~24h before confirmed appointment bookings';

    public function handle(): int
    {
        $windowEnd = now()->addDay();

        $sales = Sale::query()
            ->whereNull('reminder_sent_at')
            ->whereIn('status', ['paid', 'unpaid'])
            ->where('is_deleted', false)
            ->whereHas('event', function ($q) use ($windowEnd) {
                $q->whereNotNull('appointment_type_id')
                    ->where('is_cancelled', false)
                    ->where('starts_at', '>=', now()->format('Y-m-d H:i:s'))
                    ->where('starts_at', '<=', $windowEnd->format('Y-m-d H:i:s'));
            })
            ->with(['event.appointmentType', 'event.creatorRole'])
            ->get();

        $sent = 0;
        foreach ($sales as $sale) {
            $event = $sale->event;
            if (! $event) {
                continue;
            }

            // Only CONFIRMED bookings: skip pending approval (pivot null) and unpaid card holds.
            $pivot = $event->roles()->where('roles.id', $event->creator_role_id)->first()?->pivot;
            if (! $pivot || is_null($pivot->is_accepted)) {
                continue;
            }
            if ($sale->status !== 'paid' && in_array($event->payment_method, ['stripe', 'payment_url'], true)) {
                continue;
            }

            $role = $event->getRoleWithEmailSettings() ?: $event->creatorRole;
            if (! $role || ! $role->isPro()) {
                continue; // feature lapsed
            }
            if (is_demo_role($role) || $this->isTestEmail($sale->email)) {
                continue;
            }

            // Idempotency: claim the reminder under a row lock before dispatching.
            $claimed = false;
            DB::transaction(function () use ($sale, &$claimed) {
                $locked = Sale::whereKey($sale->id)->lockForUpdate()->first();
                if (! $locked || $locked->reminder_sent_at) {
                    return;
                }
                $locked->forceFill(['reminder_sent_at' => now()])->saveQuietly();
                $claimed = true;
            });

            if (! $claimed) {
                continue;
            }

            try {
                SendQueuedEmail::dispatch(
                    new AppointmentReminder($sale, $event, $role, $event->appointmentType),
                    $sale->email,
                    $role->id,
                    app()->getLocale()
                );
                $sent++;
            } catch (\Throwable $e) {
                report($e);
                Sale::whereKey($sale->id)->update(['reminder_sent_at' => null]); // let a later run retry
            }
        }

        $this->info("Sent {$sent} appointment reminders.");

        return self::SUCCESS;
    }

    private function isTestEmail(?string $email): bool
    {
        return $email && (str_contains($email, '@example.') || str_ends_with((string) $email, '@test.com'));
    }
}
