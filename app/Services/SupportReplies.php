<?php

namespace App\Services;

use App\Jobs\SendSupportReplyEmail;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * An administrator's answer in a support conversation, however it is given: the reply box on
 * /admin/support, or `app:support-reply` (the /support workflow in Claude Code, where a drafted
 * answer is sent once the operator has approved it). One path, so an answer sent either way is
 * emailed, pushed and ends the burst the same way.
 */
class SupportReplies
{
    public const MAX_LENGTH = 2000;

    public static function send(SupportConversation $conversation, User $admin, string $body): SupportMessage
    {
        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'user_id' => $admin->id,
            'body' => $body,
            'is_from_admin' => true,
        ]);

        $conversation->update(['last_message_at' => now()]);
        Cache::forget($conversation->typingKey());
        // An answer ends the burst: their next message is a new question, and is emailed.
        Cache::forget($conversation->adminMailKey());

        // Queued whether or not they are in the chat right now: SendSupportReplyEmail decides
        // when it runs, from what is still unread and whether they are still there. Deciding
        // here instead would never email a visitor who had the page open with the chat closed
        // and then left without reading the reply. Unique per conversation, so a run of
        // replies arrives as one email. The reply is already saved, so a failure past this
        // point is reported and never thrown: it would make the admin send it again.
        try {
            if ($conversation->contactEmail()) {
                SendSupportReplyEmail::dispatch($conversation->id)
                    ->delay(now()->addMinutes(SendSupportReplyEmail::DELAY_MINUTES));
            }

            // A push is only worth anything while it is immediate.
            if ($conversation->user && ! Cache::has($conversation->presenceKey())) {
                OneSignalService::pushToUser($conversation->user, [
                    'title_key' => 'messages.push_support_message_title',
                    'body_key' => 'messages.push_support_message_body',
                    'url' => app_url('/dashboard'),
                ], null);
            }
        } catch (\Exception $e) {
            report($e);
        }

        return $message;
    }

    /**
     * Conversations waiting on an answer: the latest message is theirs, newest first.
     *
     * @return Collection<int, SupportConversation>
     */
    public static function waiting(int $limit = 20)
    {
        return SupportConversation::query()
            ->with(['user', 'latestMessage'])
            ->whereHas('latestMessage', fn ($q) => $q->where('is_from_admin', false))
            ->orderByDesc('last_message_at')
            ->limit($limit)
            ->get();
    }
}
