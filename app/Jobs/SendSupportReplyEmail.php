<?php

namespace App\Jobs;

use App\Mail\SupportMessageNotification;
use App\Models\SupportConversation;
use App\Models\User;
use App\Utils\SupportPresence;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SyncQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * Emails the other side of a support conversation about replies they have not seen.
 *
 * Dispatched with a delay (DELAY_MINUTES) by every admin reply, and unique per conversation, so a
 * burst of three replies produces one job and one email carrying all three rather than three
 * emails. When it runs it re-reads what is still unread:
 *
 * - Nothing unread (they read it in the chat): nothing is sent.
 * - Unread, and they are still on the page: wait and look again, up to MAX_WAITS times. Giving
 *   up here instead lost the email for good whenever a visitor saw the preview bubble with the
 *   chat closed and then left without opening it.
 * - Unread and gone (or waited long enough): one email with every reply not emailed before.
 *
 * Unique only until processing starts, so a reply saved while this is sending queues a job of its
 * own instead of being swallowed by this one's lock.
 *
 * Only the email is batched. An account holder's push still goes out per reply, from
 * SupportChatController::adminReply, because a push is only useful while it is immediate.
 */
class SendSupportReplyEmail implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public const DELAY_MINUTES = 2;

    /** Two-minute waits while the recipient is still on the page: about half an hour. */
    public const MAX_WAITS = 15;

    public int $tries = 3;

    public int $backoff = 60;

    public int $uniqueFor = 600;

    public function __construct(public int $conversationId, public int $waits = 0) {}

    public function uniqueId(): string
    {
        return 'support-reply-'.$this->conversationId;
    }

    /**
     * The sync driver ignores delays, so anything that re-queues itself would loop on the spot.
     * Checked on the queue itself rather than on config, so Queue::fake() still sees dispatches.
     */
    public static function queueIsSync(): bool
    {
        return app('queue')->connection() instanceof SyncQueue;
    }

    public function handle(): void
    {
        $conversation = SupportConversation::with('user')->find($this->conversationId);
        $email = $conversation?->contactEmail();

        if (! $conversation || ! $email) {
            return;
        }

        // Replies already emailed are not repeated in every later email while the first one is
        // still unread. On the conversation, not in the cache: the file cache is wiped by every
        // deploy, and the next email would then resend everything unread.
        $unread = $conversation->unreadForUser()
            ->reorder()
            ->where('id', '>', (int) $conversation->last_emailed_message_id)
            ->orderBy('id')
            ->get();

        if ($unread->isEmpty()) {
            return;
        }

        if (Cache::has($conversation->presenceKey()) && $this->waits < self::MAX_WAITS) {
            if (! self::queueIsSync()) {
                self::dispatch($conversation->id, $this->waits + 1)
                    ->delay(now()->addMinutes(self::DELAY_MINUTES));
            }

            return;
        }

        if ($conversation->isGuest()) {
            // Signed by whoever actually wrote the newest reply, not by whoever is online now.
            $author = SupportPresence::describe(User::find($unread->last()->user_id))
                ?? SupportPresence::agent();

            Mail::to($email)->send(new SupportMessageNotification(
                $unread->pluck('body')->all(),
                $author['name'] ?? 'Event Schedule',
                true,
                self::guestResumeUrl($conversation),
                true
            ));
        } else {
            // An account holder reads it in their own language. A guest chatted on the English-only
            // marketing site, so the branch above leaves that mail in English.
            $locale = $conversation->user?->language_code;
            Mail::to($email)->locale(is_valid_language_code($locale) ? $locale : 'en')->send(new SupportMessageNotification(
                $unread->pluck('body')->all(),
                'Event Schedule Support',
                true,
                app_url('/dashboard')
            ));
        }

        // After the send, so a failed send is retried with the same replies.
        $conversation->update(['last_emailed_message_id' => $unread->last()->id]);
    }

    /**
     * Back to the page the visitor started on, with their token in the FRAGMENT: it never
     * reaches the server, so it cannot land in a log or split the marketing edge cache, and the
     * widget reads it, stores it and strips it from the address bar.
     *
     * Deliberately not marketing_url(): an operator may point that at a site of their own, and
     * the chat only exists on the base domain.
     */
    public static function guestResumeUrl(SupportConversation $conversation): string
    {
        $path = $conversation->guest_page ?: '/';

        $base = config('app.is_testing') || config('app.env') === 'local'
            ? rtrim(url('/'), '/')
            : 'https://'._base_domain();

        return $base.$path.'#support-chat='.$conversation->guest_token;
    }
}
