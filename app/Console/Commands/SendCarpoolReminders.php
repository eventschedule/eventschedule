<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\CarpoolNotification;
use App\Models\CarpoolOffer;
use App\Models\CarpoolRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendCarpoolReminders extends Command
{
    protected $signature = 'app:send-carpool-reminders';

    protected $description = 'Send reminder emails for carpool rides happening within the next 24 hours';

    public function handle(): void
    {
        $now = now();
        $tomorrow = $now->copy()->addDay();

        // Find approved carpool requests for events starting within 24 hours
        // that haven't been reminded yet
        $requests = CarpoolRequest::where('status', 'approved')
            ->whereNull('reminder_sent_at')
            ->with(['offer.event.roles', 'offer.role', 'offer.user', 'user'])
            ->get();

        $sentCount = 0;

        foreach ($requests as $carpoolRequest) {
            $offer = $carpoolRequest->offer;
            if (! $offer || $offer->status !== 'active') {
                continue;
            }

            $event = $offer->event;
            if (! $event) {
                continue;
            }

            $role = $offer->role ?? $event->roles->first(fn ($r) => $r->isPro() && $r->carpool_enabled);
            if (! $role) {
                continue;
            }

            $eventDate = $offer->event_date?->format('Y-m-d');
            $relevantDateTime = $offer->direction === 'from_event'
                ? $event->getEndDateTime($eventDate)
                : $event->getStartDateTime($eventDate);

            if (! $relevantDateTime) {
                continue;
            }

            // Only send reminders for events within 24 hours
            if ($relevantDateTime->isBefore($now) || $relevantDateTime->isAfter($tomorrow)) {
                continue;
            }

            if (is_demo_role($role)) {
                continue;
            }

            $sent = DB::transaction(function () use ($carpoolRequest, $offer, $role, $event, $now) {
                // Lock offer row to serialize per-offer processing
                CarpoolOffer::lockForUpdate()->find($offer->id);

                $locked = CarpoolRequest::lockForUpdate()->find($carpoolRequest->id);
                if ($locked->reminder_sent_at) {
                    return false; // Already processed by concurrent run
                }

                // Send reminder to rider
                $this->sendReminder($locked->user, $role, $event, $offer, $locked);

                // Send reminder to driver (only once per offer)
                if (! $offer->approvedRequests()
                    ->whereNotNull('reminder_sent_at')
                    ->exists()) {
                    $this->sendReminder($offer->user, $role, $event, $offer, $locked);
                }

                $locked->reminder_sent_at = $now;
                $locked->save();

                return true;
            });

            if ($sent) {
                $sentCount++;
            }
        }

        if ($sentCount > 0) {
            $this->info("Sent {$sentCount} carpool reminder(s).");
        }
    }

    protected function sendReminder($recipient, $role, $event, $offer, $carpoolRequest): void
    {
        if (! $recipient->carpool_notifications_enabled || $recipient->is_subscribed === false) {
            return;
        }

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

        try {
            $mailable = new CarpoolNotification('carpool_reminder', $event, $offer, $carpoolRequest, $role, $recipient);

            SendQueuedEmail::dispatch(
                $mailable,
                $recipient->email,
                $role->id,
                $recipient->language_code ?? app()->getLocale()
            );
        } catch (\Exception $e) {
            report($e);
            Log::error('Failed to send carpool reminder: '.$e->getMessage(), [
                'offer_id' => $offer->id,
                'recipient_id' => $recipient->id,
            ]);
        }
    }
}
