<?php

namespace Tests\Feature;

use App\Jobs\SendSupportReplyEmail;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The /support workflow's two commands (.claude/skills/support): app:support-pending lists what
 * waits on an answer, app:support-reply sends an approved one exactly as the reply box does.
 *
 * Held here: a reply from the command is the same reply (saved, emailed, the burst ended), a
 * thread already answered is not listed, the visitor's token never leaves, and a body or a
 * sender that is wrong sends nothing.
 */
class SupportReplyCommandTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function conversation(array $attrs = [], string $body = 'How do I add a ticket type?'): SupportConversation
    {
        $conversation = SupportConversation::create(array_merge([
            'status' => 'open',
            'guest_token' => str_repeat('t', 40),
            'guest_name' => 'Visitor',
            'guest_email' => 'visitor@example.com',
            'last_message_at' => now(),
        ], $attrs));

        SupportMessage::create(['support_conversation_id' => $conversation->id, 'body' => $body, 'is_from_admin' => false]);

        return $conversation;
    }

    /** @return array<string, mixed> */
    private function pending(): array
    {
        Artisan::call('app:support-pending');

        return json_decode(trim(Artisan::output()), true);
    }

    public function test_pending_lists_what_waits_and_never_the_token(): void
    {
        $waiting = $this->conversation();
        $answered = $this->conversation(['guest_token' => str_repeat('u', 40)], 'Thanks');
        SupportMessage::create(['support_conversation_id' => $answered->id, 'body' => 'You are welcome', 'is_from_admin' => true]);

        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'The Hall', 'subdomain' => 'thehall']);
        $member = SupportConversation::create(['user_id' => $owner->id, 'status' => 'open', 'last_message_at' => now()->subMinute()]);
        SupportMessage::create(['support_conversation_id' => $member->id, 'user_id' => $owner->id, 'body' => 'Custom domain?', 'is_from_admin' => false]);

        $json = $this->pending();
        $ids = array_column($json['waiting'], 'id');

        $this->assertSame([UrlUtils::encodeId($waiting->id), UrlUtils::encodeId($member->id)], $ids, 'newest first, the answered one left out');
        $this->assertSame([['from' => 'them', 'body' => 'How do I add a ticket type?']], array_map(fn ($m) => ['from' => $m['from'], 'body' => $m['body']], $json['waiting'][0]['messages']));
        $this->assertSame('thehall', $json['waiting'][1]['schedules'][0]['subdomain']);
        $this->assertStringNotContainsString(str_repeat('t', 40), json_encode($json));
    }

    public function test_a_long_thread_shows_its_newest_messages_oldest_first(): void
    {
        $conversation = $this->conversation([], 'Message 1');

        // A minute apart, as a real thread is: with one timestamp the old bug hid behind a tie.
        foreach (range(2, 15) as $n) {
            $this->travel(1)->minutes();
            SupportMessage::create(['support_conversation_id' => $conversation->id, 'body' => 'Message '.$n, 'is_from_admin' => false]);
        }

        Artisan::call('app:support-pending', ['--messages' => 3]);
        $messages = json_decode(trim(Artisan::output()), true)['waiting'][0]['messages'];

        $this->assertSame(['Message 13', 'Message 14', 'Message 15'], array_column($messages, 'body'));
    }

    public function test_reply_sends_as_the_reply_box_does(): void
    {
        Queue::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->conversation();
        Cache::put($conversation->adminMailKey(), true, 600);

        $text = "Hi! Open the event, then Tickets.\n\nIt's the \"Add ticket type\" button.";
        $code = Artisan::call('app:support-reply', [
            'conversation' => UrlUtils::encodeId($conversation->id),
            '--body' => base64_encode($text),
        ]);

        $this->assertSame(0, $code);
        $reply = SupportMessage::where('is_from_admin', true)->firstOrFail();
        $this->assertSame($text, $reply->body, 'quotes and new lines survive');
        $this->assertSame($admin->id, $reply->user_id);
        $this->assertFalse(Cache::has($conversation->adminMailKey()), 'the answer ends the burst');
        Queue::assertPushed(SendSupportReplyEmail::class);
        $this->assertSame([], $this->pending()['waiting'], 'answered, so no longer waiting');
    }

    public function test_a_wrong_body_conversation_or_sender_sends_nothing(): void
    {
        $this->createOwner(true);
        $conversation = $this->conversation();
        $id = UrlUtils::encodeId($conversation->id);

        $this->assertSame(1, Artisan::call('app:support-reply', ['conversation' => $id, '--body' => 'not base64 at all!']));
        $this->assertSame(1, Artisan::call('app:support-reply', ['conversation' => $id, '--body' => base64_encode(str_repeat('a', 2001))]));
        $this->assertSame(1, Artisan::call('app:support-reply', ['conversation' => 'nope', '--body' => base64_encode('Hi')]));
        $this->assertSame(1, Artisan::call('app:support-reply', ['conversation' => $id, '--body' => base64_encode('Hi'), '--admin' => 'someone@example.com']));

        $blocked = User::where('is_admin', true)->firstOrFail();
        $blocked->forceFill(['blocked_at' => now()])->save();
        $this->assertSame(1, Artisan::call('app:support-reply', ['conversation' => $id, '--body' => base64_encode('Hi')]));

        $this->assertSame(0, SupportMessage::where('is_from_admin', true)->count());
    }
}
