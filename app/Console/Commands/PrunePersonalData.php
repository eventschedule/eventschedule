<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deletes personal data the app no longer needs (GDPR Art. 5(1)(e), storage limitation). The
 * periods are the ones the privacy policy states; change one and change the policy with it.
 *
 * Runs daily on both cron rails (routes/console.php and AppController::translateData()).
 * Everything is deleted in batches, so a backlog on an install that has never run this cannot hold
 * a long lock on a busy table.
 */
class PrunePersonalData extends Command
{
    protected $signature = 'app:prune-personal-data';

    protected $description = 'Delete personal data past its retention period: failed jobs, expired reset tokens, unconfirmed sign-ups, old guest support chats, waitlists and interest lists of past events, and the buyer details on deleted sales';

    /** A failed job's payload is a serialized mail or task, addresses and all. */
    public const FAILED_JOB_DAYS = 30;

    /** A sign-up nobody confirmed. Long enough to find the email, nowhere near forever. */
    public const UNCONFIRMED_SUBSCRIBER_DAYS = 30;

    /** A support chat started from the marketing site by someone who never signed in. */
    public const GUEST_SUPPORT_CHAT_MONTHS = 12;

    /** Waitlists and "tell me when" lists exist for one occurrence; this long after it, they go. */
    public const AFTER_EVENT_DAYS = 30;

    /**
     * A sale the organiser deleted keeps its amounts for their books, and loses the buyer's name,
     * email, phone and answers this long after the delete (the grace is for an accidental delete).
     */
    public const DELETED_SALE_DAYS = 30;

    private const BATCH = 1000;

    public function handle(): int
    {
        $counts = [
            'failed jobs' => $this->deleteInBatches(
                DB::table('failed_jobs')->where('failed_at', '<', now()->subDays(self::FAILED_JOB_DAYS))
            ),
            'expired password reset tokens' => DB::table('password_reset_tokens')
                ->where('created_at', '<', now()->subMinutes((int) config('auth.passwords.users.expire', 60)))
                ->delete(),
            'unconfirmed sign-ups' => $this->deleteInBatches(
                DB::table('role_subscribers')
                    ->whereNull('confirmed_at')
                    ->where('created_at', '<', now()->subDays(self::UNCONFIRMED_SUBSCRIBER_DAYS))
            ),
            // support_messages cascade with their conversation.
            'guest support chats' => $this->deleteInBatches(
                DB::table('support_conversations')
                    ->whereNull('user_id')
                    ->where(fn ($query) => $query
                        ->where('last_message_at', '<', now()->subMonths(self::GUEST_SUPPORT_CHAT_MONTHS))
                        ->orWhere(fn ($inner) => $inner->whereNull('last_message_at')
                            ->where('created_at', '<', now()->subMonths(self::GUEST_SUPPORT_CHAT_MONTHS))))
            ),
            // event_date is the occurrence's Y-m-d, so a string comparison orders it correctly.
            'waitlist entries' => $this->deleteInBatches(
                DB::table('ticket_waitlists')->where('event_date', '<', now()->subDays(self::AFTER_EVENT_DAYS)->format('Y-m-d'))
            ),
            'interest list addresses' => $this->deleteInBatches(
                DB::table('event_interests')->where('event_date', '<', now()->subDays(self::AFTER_EVENT_DAYS)->format('Y-m-d'))
            ),
            'deleted sales' => $this->forgetDeletedBuyers(),
        ];

        foreach ($counts as $what => $count) {
            $this->line("{$what}: {$count}");
        }

        return self::SUCCESS;
    }

    /**
     * name and email are NOT NULL, so they are blanked; the empty email is also what marks a row as
     * already done. The amounts, dates, ticket and payment references stay.
     */
    private function forgetDeletedBuyers(): int
    {
        $blank = ['name' => '', 'email' => '', 'phone' => null, 'guest_timezone' => null];

        for ($i = 1; $i <= 10; $i++) {
            $blank['custom_value'.$i] = null;
        }

        $total = 0;

        do {
            $ids = DB::table('sales')
                ->where('is_deleted', true)
                ->where('email', '!=', '')
                ->where('updated_at', '<', now()->subDays(self::DELETED_SALE_DAYS))
                ->limit(self::BATCH)
                ->pluck('id');

            if ($ids->isNotEmpty()) {
                // Query builder on purpose: no saving hooks, and updated_at keeps the delete's date.
                $total += DB::table('sales')->whereIn('id', $ids)->update($blank);
            }
        } while ($ids->count() === self::BATCH);

        return $total;
    }

    private function deleteInBatches(\Illuminate\Database\Query\Builder $query): int
    {
        $total = 0;

        do {
            $deleted = (clone $query)->limit(self::BATCH)->delete();
            $total += $deleted;
        } while ($deleted === self::BATCH);

        return $total;
    }
}
