<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Newsletter;
use App\Models\NewsletterSegment;
use App\Models\SupportConversation;
use App\Models\User;
use App\Models\Webhook;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AdminFeaturesTest extends TestCase
{
    use RefreshDatabase;
    use CreatesScheduleData;

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    public function test_admin_audit_log_lists_entries(): void
    {
        $admin = $this->createOwner(true);
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'auth.login_success',
            'ip_address' => '192.0.2.1',
            'created_at' => now(),
        ]);

        $this->adminActing($admin)->get(route('admin.audit_log'))
            ->assertOk()
            ->assertSee('auth.login_success');
    }

    public function test_support_chat_message_and_admin_reply(): void
    {
        Mail::fake();

        $user = $this->createOwner();
        $this->actingAs($user)->post(route('support-chat.send'), ['body' => 'Need help please']);

        $this->assertDatabaseHas('support_messages', [
            'body' => 'Need help please',
            'is_from_admin' => false,
        ]);

        $conversation = SupportConversation::where('user_id', $user->id)->firstOrFail();

        $admin = $this->createOwner(true);
        $this->adminActing($admin)->post(route('admin.support.reply', ['id' => UrlUtils::encodeId($conversation->id)]), [
            'body' => 'How can I help?',
        ]);

        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'body' => 'How can I help?',
            'is_from_admin' => true,
        ]);
    }

    public function test_admin_newsletter_broadcast(): void
    {
        Queue::fake();

        $admin = $this->createOwner(true);
        // A subscribed recipient + an all-users segment.
        User::factory()->create(['is_subscribed' => true, 'email_verified_at' => now()]);
        $segment = NewsletterSegment::create(['role_id' => null, 'name' => 'All Users', 'type' => 'all_users']);

        $this->adminActing($admin)->post(route('admin.newsletters.store'), [
            'subject' => 'Platform Update',
            'template' => 'modern',
            'segment_ids' => [$segment->id],
        ]);

        $newsletter = Newsletter::where('type', 'admin')->firstOrFail();

        $this->adminActing($admin)->post(route('admin.newsletters.send', ['hash' => UrlUtils::encodeId($newsletter->id)]));

        $this->assertContains($newsletter->fresh()->status, ['sending', 'sent']);
        $this->assertDatabaseHas('newsletter_recipients', ['newsletter_id' => $newsletter->id]);
    }

    public function test_admin_sale_approval(): void
    {
        // amount_mismatch is now a valid sales.status (see the 2026_06_16 migration).
        $admin = $this->createOwner(true);
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 50]);
        $sale = $this->createSale($event, $role, [
            'status' => 'amount_mismatch',
            'payment_method' => 'stripe',
            'payment_amount' => 50,
            'transaction_reference' => 'pi_test',
        ], $ticket, 1);

        $this->adminActing($admin)->post(route('admin.sale.approve', ['sale' => $sale->id]));

        $this->assertSame('paid', $sale->fresh()->status);
    }

    public function test_webhook_can_be_configured(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);

        $this->actingAs($owner)->post(route('webhooks.store'), [
            'url' => 'https://example.com/webhook',
            'description' => 'Test hook',
            'event_types' => ['sale.created'],
        ]);

        $this->assertDatabaseHas('webhooks', [
            'user_id' => $owner->id,
            'url' => 'https://example.com/webhook',
        ]);
    }

    public function test_backup_restore_imports_schedule(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $owner = $this->createOwner();

        $backup = [
            'meta' => ['version' => '1.0', 'exported_at' => now()->toIso8601String(), 'includes_images' => false],
            'schedules' => [[
                'role' => [
                    'subdomain' => 'imported' . strtolower(\Illuminate\Support\Str::random(6)),
                    'name' => 'Imported Schedule',
                    'type' => 'venue',
                    'email' => 'imported@gmail.com',
                    'timezone' => 'UTC',
                ],
                'events' => [], 'groups' => [], 'newsletters' => [], 'segments' => [],
            ]],
        ];

        $zipPath = tempnam(sys_get_temp_dir(), 'bk') . '.zip';
        $zip = new \ZipArchive;
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('backup.json', json_encode($backup));
        $zip->close();

        $upload = $this->actingAs($owner)->post(route('backup.upload'), [
            'file' => new \Illuminate\Http\UploadedFile($zipPath, 'backup.zip', 'application/zip', null, true),
        ]);
        $filePath = $upload->json('file_path');

        $this->actingAs($owner)->post(route('backup.confirm'), [
            'file_path' => $filePath,
            'selected_indices' => [0],
        ]);

        $this->assertDatabaseHas('roles', ['name' => 'Imported Schedule']);
    }

    public function test_analytics_view_recording_and_dashboard(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        // Browser-like headers bypass PageView bot-detection.
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ])->get($this->guestEventUrl($role, $event))->assertOk();

        $this->assertDatabaseHas('analytics_daily', ['role_id' => $role->id]);

        $this->actingAs($owner)->get(route('analytics', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk();
    }
}
