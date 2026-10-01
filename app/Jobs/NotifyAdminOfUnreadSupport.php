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
 * No longer dispatched: delete in a later release.
 *
 * This was the delayed email for a message that arrived while the admin was shown as available.
 * Every new message now emails the primary admin straight away, online or not
 * (SupportConversation::emailPrimaryAdmin). The class stays for one release so a check queued by
 * the previous release still runs after the deploy, rather than failing to unserialize into
 * failed_jobs (which raises the jobs_failed admin alert) and never emailing that message at all.
 */
class NotifyAdminOfUnreadSupport implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $uniqueFor = 900;

    public function __construct(public int $conversationId) {}

    public function uniqueId(): string
    {
        return 'support-admin-unread-'.$this->conversationId;
    }

    public function handle(): void
    {
        $conversation = SupportConversation::with('user')->find($this->conversationId);
        $admin = SupportPresence::primaryAdmin();

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
