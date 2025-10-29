<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Notifications\TicketPaymentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendUnpaidTicketReminders extends Command
{
    protected $signature = 'app:remind-unpaid-tickets';

    protected $description = 'Send reminder emails for unpaid ticket reservations.';

    public function handle(): int
    {
        Log::info('Sending unpaid ticket reminders...');

        $now = Carbon::now();
        $remindersSent = 0;

        $sales = Sale::where('status', 'unpaid')
            ->where('is_deleted', false)
            ->whereNotNull('email')
            ->whereHas('event', function ($query) {
                $query->where('events.remind_unpaid_tickets_every', '>', 0);
            })
            ->with(['event', 'event.user', 'saleTickets.ticket'])
            ->get();

        foreach ($sales as $sale) {
            $event = $sale->event;
            $interval = (int) ($event->remind_unpaid_tickets_every ?? 0);

            if ($interval <= 0 || ! $sale->created_at) {
                continue;
            }

            $lastReminder = $sale->last_reminder_sent_at;
            $nextReminderAt = $lastReminder
                ? $lastReminder->copy()->addHours($interval)
                : $sale->created_at->copy()->addHours($interval);

            if ($now->lt($nextReminderAt)) {
                continue;
            }

            Notification::route('mail', $sale->email)
                ->notify(new TicketPaymentReminderNotification($sale));

            $sale->last_reminder_sent_at = $now;
            $sale->save();
            $remindersSent++;
        }

        Log::info('Unpaid ticket reminders complete.', [
            'processed' => $sales->count(),
            'sent' => $remindersSent,
        ]);

        return self::SUCCESS;
    }
}
