<?php

namespace App\Jobs;

use App\Mail\SupportMessageNotification;
use App\Models\SupportConversation;
use App\Utils\SupportPresence;
use App\Utils\UrlUtils;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

/**
 * The email safety net while the admin is shown as available.
 *
 * A message that arrives while the admin is available is announced in the AP (toast, chime, tab
 * title) rather than by email. That only works if they are actually looking: an AP tab left open
 * in the background keeps them "present", and a tab nobody has clicked cannot even play the
 * chime. So each such conversation gets one delayed check, and whatever is still unread when it
 * runs is emailed after all.
 *
 * Unique per conversation until it has run, so a burst of messages is one check and one email.
 */
class NotifyAdminOfUnreadSupport implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public const DELAY_MINUTES = 5;

    public int $tries = 3;

    public int $backoff = 60;

    public int $uniqueFor = 900;

    public function __construct(public int $conversationId) {}

    public function uniqueId(): string
    {
        return 'support-admin-unread-'.$this->conversationId;
    }

    /**
     * Not under the sync driver: it ignores the delay, so the check would email at once and the
     * in-AP alert would never get its five minutes.
     */
    public static function queueFor(SupportConversation $conversation): void
    {
        if (SendSupportReplyEmail::queueIsSync()) {
            return;
        }

        self::dispatch($conversation->id)->delay(now()->addMinutes(self::DELAY_MINUTES));
    }

    public function handle(): void
    {
        $conversation = SupportConversation::with('user')->find($this->conversationId);
        $admin = SupportPresence::agentUser();

        if (! $conversation || ! $admin) {
            return;
        }

        $bodies = $conversation->unreadForAdmin()->reorder()->orderBy('id')->pluck('body')->all();

        if (! $bodies) {
            return;
        }

        Mail::to($admin->email)->send(new SupportMessageNotification(
            $bodies,
            $conversation->isGuest() ? $conversation->visitorLabel() : $conversation->displayName(),
            false,
            app_url('/admin/support?c='.UrlUtils::encodeId($conversation->id)),
            $conversation->isGuest()
        ));
    }
}
