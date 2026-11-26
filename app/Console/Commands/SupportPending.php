<?php

namespace App\Console\Commands;

use App\Models\SupportConversation;
use App\Services\SupportReplies;
use App\Utils\UrlUtils;
use Illuminate\Console\Command;

/**
 * The support conversations waiting on an answer, as JSON, for the /support workflow in Claude
 * Code (.claude/skills/support): read on production through the console, drafted against the
 * code and the user guide, and answered with app:support-reply once the operator approves.
 *
 * Read-only. It prints what the operator sees on /admin/support (the thread, who is asking, the
 * schedules they own with their plan), and nothing else: no token, no address the person did not
 * give us.
 */
class SupportPending extends Command
{
    protected $signature = 'app:support-pending {--limit=20} {--messages=12 : The newest messages of each thread}';

    protected $description = 'Print the support conversations waiting on an answer, as JSON';

    public function handle(): int
    {
        $keep = max(1, (int) $this->option('messages'));

        $rows = SupportReplies::waiting(max(1, min(100, (int) $this->option('limit'))))
            ->map(function (SupportConversation $conversation) use ($keep) {
                $messages = $conversation->messages()->latest('id')->limit($keep)->get()->reverse()->values();
                $user = $conversation->user;

                return [
                    'id' => UrlUtils::encodeId($conversation->id),
                    'name' => $conversation->displayName(),
                    'email' => $conversation->contactEmail(),
                    'guest' => $conversation->isGuest(),
                    'page' => $conversation->guest_page,
                    'country' => $conversation->guest_country,
                    'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                    'schedules' => $user
                        ? $user->createdRoles()->where('is_deleted', false)->get(['subdomain', 'type', 'plan_type'])
                            ->map(fn ($role) => ['subdomain' => $role->subdomain, 'type' => $role->type, 'plan' => $role->plan_type])->all()
                        : [],
                    'messages' => $messages->map(fn ($message) => [
                        'from' => $message->is_from_admin ? 'us' : 'them',
                        'at' => $message->created_at?->toIso8601String(),
                        'body' => $message->body,
                    ])->all(),
                ];
            })
            ->values()
            ->all();

        $this->line(json_encode(['waiting' => $rows], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
