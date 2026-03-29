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

        $roles = Role::where('is_deleted', false)
            ->where(function ($q) {
                $q->where('feedback_enabled', true)
                    ->orWhereHas('events', function ($eq) {
                        $eq->where('feedback_enabled', true);
                    });
            })->get()->filter(fn ($role) => $role->isPro());

        foreach ($roles as $role) {
            try {
                if (is_demo_role($role)) {
                    continue;
                }

                // Check if email sending is possible for this role
                if (config('app.hosted')) {
                    if (! $role->hasEmailSettings()) {
                        continue;
                    }
                } else {
                    $mailer = config('mail.default');
                    if (in_array($mailer, ['log', 'array'])) {
                        continue;
                    }
                }

                $events = $role->events()
                    ->where(function ($q) use ($role) {
                        if ($role->feedback_enabled) {
                            $q->where('events.feedback_enabled', true)
                                ->orWhereNull('events.feedback_enabled');
                        } else {
                            $q->where('events.feedback_enabled', true);
                        }
                    })
                    ->get();

                foreach ($events as $event) {
                    $sales = Sale::where('event_id', $event->id)
                        ->where('subdomain', $role->subdomain)
                        ->where('status', 'paid')
                        ->where('is_deleted', false)
                        ->whereNull('feedback_sent_at')
                        ->whereDoesntHave('feedback')
                        ->where(function ($q) {
                            $q->whereNull('group_id')
                                ->orWhereColumn('group_id', 'id');
                        })
                        ->excludeTestEmails()
                        ->get();

                    foreach ($sales as $sale) {
                        // Check if enough time has passed since event ended
                        $endDateTime = $event->getEndDateTime($sale->event_date);

                        // Don't send feedback requests for events that ended more than 30 days ago
                        if ($endDateTime->copy()->addDays(30)->isPast()) {
                            continue;
                        }

                        $delayHours = $role->feedback_delay_hours ?? 24;

                        if ($endDateTime->copy()->addHours($delayHours)->isFuture()) {
                            continue;
                        }

                        try {
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
                            Log::error('Failed to dispatch feedback request: '.$e->getMessage(), [
                                'sale_id' => $sale->id,
                                'event_id' => $event->id,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to process feedback for role '.$role->subdomain.': '.$e->getMessage());
                report($e);
            }
        }

    }
}
