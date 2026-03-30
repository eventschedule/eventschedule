<?php

namespace App\Console\Commands;

use App\Jobs\SendFeedbackEmail;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendFeedbackRequests extends Command
{
    protected $signature = 'app:send-feedback-requests';

    protected $description = 'Send post-event feedback request emails to attendees';

    public function handle()
    {
        $count = 0;

        // Pre-load eligible roles keyed by subdomain
        $rolesBySubdomain = Role::where('is_deleted', false)
            ->get()
            ->filter(fn ($role) => $role->isPro())
            ->keyBy('subdomain');

        // Get subdomains that are eligible to send
        $eligibleSubdomains = $rolesBySubdomain->filter(function ($role) {
            if (is_demo_role($role)) {
                return false;
            }
            if (config('app.hosted')) {
                return $role->hasEmailSettings();
            }

            return ! in_array(config('mail.default'), ['log', 'array']);
        })->keys();

        if ($eligibleSubdomains->isEmpty()) {
            return;
        }

        // Query eligible sales for roles that can send
        $sales = Sale::where('status', 'paid')
            ->where('is_deleted', false)
            ->whereNull('feedback_sent_at')
            ->whereDoesntHave('feedback')
            ->whereIn('subdomain', $eligibleSubdomains)
            ->where(function ($q) {
                $q->whereNull('group_id')
                    ->orWhereColumn('group_id', 'id');
            })
            ->excludeTestEmails()
            ->with(['event'])
            ->get();

        foreach ($sales as $sale) {
            try {
                $event = $sale->event;
                if (! $event) {
                    continue;
                }

                // Resolve role from sale's subdomain (already filtered to eligible roles)
                $role = $rolesBySubdomain->get($sale->subdomain);
                if (! $role) {
                    continue;
                }

                // Check feedback enabled using event's own setting or role fallback
                if (! is_null($event->feedback_enabled)) {
                    if (! $event->feedback_enabled) {
                        continue;
                    }
                } else {
                    if (! $role->feedback_enabled) {
                        continue;
                    }
                }

                // Check timing
                $endDateTime = $event->getEndDateTime($sale->event_date);

                // Don't send for events that ended more than 30 days ago
                if ($endDateTime->copy()->addDays(30)->isPast()) {
                    continue;
                }

                // Don't send if event hasn't ended yet
                if ($endDateTime->isFuture()) {
                    continue;
                }

                $delayHours = $role->feedback_delay_hours ?? 24;

                if ($endDateTime->copy()->addHours($delayHours)->isFuture()) {
                    continue;
                }

                $sale->feedback_sent_at = now();
                $sale->save();

                SendFeedbackEmail::dispatch(
                    $sale->id,
                    $event->id,
                    $role->id,
                    $role->language_code ?? app()->getLocale()
                );

                $count++;
            } catch (\Exception $e) {
                $sale->feedback_sent_at = null;
                $sale->save();
                report($e);
                Log::error('Failed to process feedback request: '.$e->getMessage(), [
                    'sale_id' => $sale->id,
                    'event_id' => $sale->event_id,
                ]);
            }
        }

    }
}
