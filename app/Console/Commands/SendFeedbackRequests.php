<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\FeedbackRequest;
use App\Models\Event;
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
        Log::info('Sending feedback requests...');

        $count = 0;

        $roles = Role::where(function ($q) {
            $q->where('feedback_enabled', true)
                ->orWhereHas('events', function ($eq) {
                    $eq->where('feedback_enabled', true);
                });
        })->get()->filter(fn ($role) => $role->isPro());

        foreach ($roles as $role) {
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
                ->where(function ($q) {
                    $q->where('events.tickets_enabled', true)
                        ->orWhere('events.rsvp_enabled', true);
                })
                ->get();

            foreach ($events as $event) {
                $sales = Sale::where('event_id', $event->id)
                    ->where('status', 'paid')
                    ->where('is_deleted', false)
                    ->whereNull('feedback_sent_at')
                    ->whereDoesntHave('feedback')
                    ->get();

                foreach ($sales as $sale) {
                    // Skip empty or test emails
                    if (empty($sale->email) || $this->isTestEmail($sale->email)) {
                        continue;
                    }

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
                        $mailable = new FeedbackRequest($sale, $event, $role);

                        SendQueuedEmail::dispatch(
                            $mailable,
                            $sale->email,
                            $role->id,
                            $role->language_code ?? app()->getLocale()
                        );

                        $sale->feedback_sent_at = now();
                        $sale->save();
                        $count++;
                    } catch (\Exception $e) {
                        report($e);
                        Log::error('Failed to send feedback request: '.$e->getMessage(), [
                            'sale_id' => $sale->id,
                            'event_id' => $event->id,
                        ]);
                    }
                }
            }
        }

        Log::info("Sent {$count} feedback request emails");
    }

    protected function isTestEmail(string $email): bool
    {
        $email = strtolower($email);

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
