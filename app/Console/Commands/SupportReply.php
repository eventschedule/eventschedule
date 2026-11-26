<?php

namespace App\Console\Commands;

use App\Models\SupportConversation;
use App\Models\User;
use App\Services\SupportReplies;
use App\Utils\UrlUtils;
use Illuminate\Console\Command;

/**
 * Send an answer in a support conversation as an administrator, exactly as the reply box on
 * /admin/support sends it (SupportReplies::send(): saved, emailed, pushed). The /support workflow
 * in Claude Code runs it only after the operator has approved the text.
 *
 * The body is base64, so a reply with quotes and new lines survives the console's shell intact.
 */
class SupportReply extends Command
{
    protected $signature = 'app:support-reply
        {conversation : The encoded conversation id app:support-pending prints}
        {--body= : The reply, base64-encoded}
        {--admin= : The administrator sending it, by email; the first administrator when left out}';

    protected $description = 'Send an approved reply in a support conversation';

    public function handle(): int
    {
        $id = UrlUtils::decodeId((string) $this->argument('conversation'));
        $conversation = $id ? SupportConversation::with('user')->find($id) : null;

        if (! $conversation) {
            $this->error('No such conversation.');

            return self::FAILURE;
        }

        $body = trim((string) base64_decode((string) $this->option('body'), true));

        if ($body === '' || mb_strlen($body) > SupportReplies::MAX_LENGTH || ! mb_check_encoding($body, 'UTF-8')) {
            $this->error('The reply must be base64 of 1 to '.SupportReplies::MAX_LENGTH.' characters of UTF-8.');

            return self::FAILURE;
        }

        $admins = User::query()->where('is_admin', true)->whereNull('blocked_at');
        $admin = $this->option('admin')
            ? $admins->where('email', (string) $this->option('admin'))->first()
            : $admins->orderBy('id')->first();

        if (! $admin) {
            $this->error('No such administrator.');

            return self::FAILURE;
        }

        $message = SupportReplies::send($conversation, $admin, $body);

        $this->info('Sent as message '.$message->id.' to '.$conversation->displayName().'.');

        return self::SUCCESS;
    }
}
