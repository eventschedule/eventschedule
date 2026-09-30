<?php

namespace Tests\Feature;

use App\Jobs\SendSupportReplyEmail;
use App\Mail\SupportMessageNotification;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Utils\SupportPresence;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An admin's replies reach someone who has left the chat as ONE email per burst, and not at all
 * if they came back and read them first (App\Jobs\SendSupportReplyEmail).
 */
class SupportReplyEmailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    private function reply(User $admin, SupportConversation $conversation, string $body)
    {
        return $this->adminActing($admin)
            ->postJson(route('admin.support.reply', ['id' => UrlUtils::encodeId($conversation->id)]), ['body' => $body])
            ->assertOk();
    }

    private function guestConversation(): SupportConversation
    {
        return SupportConversation::create([
            'user_id' => null,
            'guest_token' => str_repeat('c', 48),
            'guest_email' => 'visitor@example.com',
            'guest_page' => '/features',
            'status' => 'open',
            'last_message_at' => now(),
        ]);
    }

    public function test_a_burst_of_replies_queues_one_delayed_job(): void
    {
        Queue::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->guestConversation();

        $this->reply($admin, $conversation, 'First');
        $this->reply($admin, $conversation, 'Second');

        Queue::assertPushed(SendSupportReplyEmail::class, 1);
        Queue::assertPushed(SendSupportReplyEmail::class, function ($job) use ($conversation) {
            return $job->conversationId === $conversation->id && $job->delay !== null;
        });
    }

    /**
     * Still on the page with the reply unread: no email yet, and the job looks again later
     * rather than giving up. Called directly, not through adminReply, whose try/catch would let
     * a crashing job pass this test too.
     */
    public function test_while_they_are_still_on_the_page_the_job_waits_instead_of_emailing(): void
    {
        Mail::fake();
        Queue::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->guestConversation();
        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'Hello', 'is_from_admin' => true]);
        Cache::put($conversation->presenceKey(), ['page' => '/features'], now()->addMinutes(2));

        (new SendSupportReplyEmail($conversation->id))->handle();

        Mail::assertNothingSent();
        Queue::assertPushed(SendSupportReplyEmail::class, fn ($job) => $job->conversationId === $conversation->id
            && $job->waits === 1
            && $job->delay !== null);
    }

    public function test_after_waiting_long_enough_it_emails_even_if_they_are_still_there(): void
    {
        Mail::fake();
        Queue::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->guestConversation();
        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'Hello', 'is_from_admin' => true]);
        Cache::put($conversation->presenceKey(), ['page' => '/features'], now()->addMinutes(2));

        (new SendSupportReplyEmail($conversation->id, SendSupportReplyEmail::MAX_WAITS))->handle();

        Mail::assertSent(SupportMessageNotification::class, 1);
        Queue::assertNothingPushed();
    }

    public function test_replies_already_emailed_are_not_repeated(): void
    {
        Mail::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->guestConversation();

        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'First answer', 'is_from_admin' => true]);
        (new SendSupportReplyEmail($conversation->id))->handle();

        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'Second answer', 'is_from_admin' => true]);
        (new SendSupportReplyEmail($conversation->id))->handle();

        Mail::assertSent(SupportMessageNotification::class, 2);
        $this->assertSame(
            (int) SupportMessage::where('body', 'Second answer')->value('id'),
            (int) $conversation->fresh()->last_emailed_message_id
        );
        $sent = Mail::sent(SupportMessageNotification::class)->map(fn ($mail) => $mail->render())->values();
        $this->assertStringContainsString('First answer', $sent[0]);
        $this->assertStringContainsString('Second answer', $sent[1]);
        $this->assertStringNotContainsString('First answer', $sent[1], 'The first reply was already emailed.');
    }

    /**
     * The marker lives on the conversation. In the file cache it was wiped by every deploy, and
     * the next email resent everything still unread.
     */
    public function test_emailed_replies_are_not_repeated_after_the_cache_is_cleared(): void
    {
        Mail::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->guestConversation();

        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'First answer', 'is_from_admin' => true]);
        (new SendSupportReplyEmail($conversation->id))->handle();

        Cache::flush();

        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'Second answer', 'is_from_admin' => true]);
        (new SendSupportReplyEmail($conversation->id))->handle();

        $second = Mail::sent(SupportMessageNotification::class)->values()[1]->render();
        $this->assertStringContainsString('Second answer', $second);
        $this->assertStringNotContainsString('First answer', $second);
    }

    /**
     * Unique only until processing starts: a reply saved while an email is being sent must be
     * able to queue its own job rather than be swallowed by the running one's lock.
     */
    public function test_the_lock_is_released_when_processing_starts(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing::class,
            new SendSupportReplyEmail(1)
        );
    }

    /**
     * Watching when the reply was sent, gone when the job runs, reply still unread: the one
     * case deciding at reply time used to miss.
     */
    public function test_a_visitor_who_leaves_without_reading_is_still_emailed(): void
    {
        Queue::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->guestConversation();
        Cache::put($conversation->presenceKey(), ['page' => '/features'], now()->addMinutes(2));

        $this->reply($admin, $conversation, 'Hello');
        Queue::assertPushed(SendSupportReplyEmail::class, 1);

        Mail::fake();
        Cache::forget($conversation->presenceKey());
        (new SendSupportReplyEmail($conversation->id))->handle();

        Mail::assertSent(SupportMessageNotification::class, fn ($mail) => $mail->hasTo('visitor@example.com'));
    }

    public function test_the_job_sends_every_unread_reply_in_one_email(): void
    {
        Mail::fake();
        $admin = $this->createOwner(true);
        $admin->update(['name' => 'Hillel Coren']);
        SupportPresence::goOnline($admin);
        $conversation = $this->guestConversation();

        foreach (['First answer', 'Second answer'] as $body) {
            SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => $body, 'is_from_admin' => true]);
        }

        (new SendSupportReplyEmail($conversation->id))->handle();

        Mail::assertSent(SupportMessageNotification::class, 1);
        Mail::assertSent(SupportMessageNotification::class, function ($mail) use ($conversation) {
            $html = $mail->render();

            return $mail->hasTo('visitor@example.com')
                && $mail->hasReplyTo(config('app.support_email'))
                && $mail->envelope()->subject === 'New reply from Hillel at Event Schedule'
                && str_contains($html, 'First answer')
                && str_contains($html, 'Second answer')
                && str_contains($html, '/features#support-chat='.$conversation->guest_token);
        });
    }

    public function test_the_admin_safety_net_emails_only_what_is_still_unread(): void
    {
        Mail::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->guestConversation();
        $conversation->update(['guest_name' => 'Marie']);
        $message = SupportMessage::create(['support_conversation_id' => $conversation->id, 'body' => 'Anyone there?', 'is_from_admin' => false]);

        (new \App\Jobs\NotifyAdminOfUnreadSupport($conversation->id))->handle();

        Mail::assertSent(SupportMessageNotification::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email)
                && $mail->envelope()->subject === 'New chat from a website visitor: Marie'
                && str_contains($mail->render(), 'Anyone there?');
        });

        $message->update(['read_at' => now()]);
        Mail::fake();
        (new \App\Jobs\NotifyAdminOfUnreadSupport($conversation->id))->handle();
        Mail::assertNothingSent();
    }

    public function test_an_anonymous_visitor_does_not_become_website_visitor_in_the_subject(): void
    {
        Mail::fake();
        $this->createOwner(true);
        $conversation = SupportConversation::create(['user_id' => null, 'guest_token' => str_repeat('e', 48), 'status' => 'open', 'last_message_at' => now()]);
        SupportMessage::create(['support_conversation_id' => $conversation->id, 'body' => 'Hi', 'is_from_admin' => false]);

        (new \App\Jobs\NotifyAdminOfUnreadSupport($conversation->id))->handle();

        Mail::assertSent(SupportMessageNotification::class, function ($mail) {
            return $mail->envelope()->subject === 'New chat from a website visitor'
                && str_contains($mail->render(), 'A website visitor wrote:');
        });
    }

    public function test_the_job_sends_nothing_once_the_replies_were_read(): void
    {
        Mail::fake();
        $admin = $this->createOwner(true);
        $conversation = $this->guestConversation();
        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'Answer', 'is_from_admin' => true, 'read_at' => now()]);

        (new SendSupportReplyEmail($conversation->id))->handle();

        Mail::assertNothingSent();
    }

    public function test_an_account_holder_gets_the_login_link_not_the_widget_link(): void
    {
        Mail::fake();
        $admin = $this->createOwner(true);
        $user = $this->createOwner();
        $conversation = SupportConversation::create(['user_id' => $user->id, 'status' => 'open', 'last_message_at' => now()]);
        SupportMessage::create(['support_conversation_id' => $conversation->id, 'user_id' => $admin->id, 'body' => 'Fixed it', 'is_from_admin' => true]);

        (new SendSupportReplyEmail($conversation->id))->handle();

        Mail::assertSent(SupportMessageNotification::class, function ($mail) use ($user) {
            $html = $mail->render();

            return $mail->hasTo($user->email)
                && str_contains($html, 'Log in to reply')
                && ! str_contains($html, '#support-chat=');
        });
    }
}
