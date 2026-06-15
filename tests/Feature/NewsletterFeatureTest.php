<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class NewsletterFeatureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesScheduleData;

    private function makeNewsletter($role, $user, array $attrs = []): Newsletter
    {
        return Newsletter::create(array_merge([
            'role_id' => $role->id,
            'user_id' => $user->id,
            'subject' => 'Test Newsletter',
            'status' => 'draft',
            'template' => 'modern',
            'blocks' => [],
            'type' => 'schedule',
        ], $attrs));
    }

    private function addFollower($role, string $email = 'follower@example.com'): User
    {
        $follower = User::factory()->create([
            'email' => $email,
            'is_subscribed' => true,
            'email_verified_at' => now(),
        ]);
        $role->users()->attach($follower->id, ['level' => 'follower']);

        return $follower;
    }

    public function test_user_can_follow_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $follower = $this->createOwner();

        $this->actingAs($follower)->get(route('role.follow', ['subdomain' => $role->subdomain]));

        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $follower->id,
            'level' => 'follower',
        ]);
    }

    public function test_newsletter_send_creates_recipients(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->addFollower($role);
        $newsletter = $this->makeNewsletter($role, $owner);
        $rid = UrlUtils::encodeId($role->id);

        $this->actingAs($owner)->post(route('newsletter.send', ['hash' => UrlUtils::encodeId($newsletter->id)]) . '?role_id=' . $rid);

        // Recipients are materialized and the newsletter transitions out of draft.
        $this->assertDatabaseHas('newsletter_recipients', ['newsletter_id' => $newsletter->id]);
        $this->assertContains($newsletter->fresh()->status, ['sending', 'sent']);
    }

    public function test_newsletter_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->makeNewsletter($role, $owner);
        $rid = UrlUtils::encodeId($role->id);

        $this->actingAs($owner)->post(route('newsletter.schedule', ['hash' => UrlUtils::encodeId($newsletter->id)]) . '?role_id=' . $rid, [
            'scheduled_at' => now()->addHours(2)->format('Y-m-d H:i'),
        ]);

        $this->assertDatabaseHas('newsletters', [
            'id' => $newsletter->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_newsletter_segment_create(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $rid = UrlUtils::encodeId($role->id);

        $this->actingAs($owner)->post(route('newsletter.segment.store') . '?role_id=' . $rid, [
            'name' => 'VIPs',
            'type' => 'manual',
            'emails' => "alice@example.com, Alice\nbob@example.com, Bob",
        ]);

        $this->assertDatabaseHas('newsletter_segments', [
            'role_id' => $role->id,
            'name' => 'VIPs',
        ]);
    }

    public function test_newsletter_import_subscribers(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $segment = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Imported', 'type' => 'manual']);
        $rid = UrlUtils::encodeId($role->id);

        $this->actingAs($owner)->post(route('newsletter.import.store') . '?role_id=' . $rid, [
            'segment_target' => 'existing',
            'segment_id' => UrlUtils::encodeId($segment->id),
            'entries' => [
                ['email' => 'import1@example.com', 'name' => 'Import One'],
                ['email' => 'import2@example.com', 'name' => 'Import Two'],
            ],
        ]);

        $this->assertDatabaseHas('newsletter_segment_users', ['email' => 'import1@example.com']);
    }

    public function test_newsletter_template_create(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $rid = UrlUtils::encodeId($role->id);

        $this->actingAs($owner)->post(route('newsletter.template.store') . '?role_id=' . $rid, [
            'name' => 'Welcome Template',
            'template' => 'modern',
            'blocks' => json_encode([['type' => 'heading', 'data' => ['text' => 'Hi']]]),
        ]);

        $this->assertDatabaseHas('newsletter_templates', [
            'role_id' => $role->id,
            'name' => 'Welcome Template',
        ]);
    }

    public function test_newsletter_open_tracking(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->makeNewsletter($role, $owner, ['status' => 'sent']);
        $recipient = NewsletterRecipient::create([
            'newsletter_id' => $newsletter->id,
            'email' => 'open@example.com',
            'name' => 'Opener',
            'token' => Str::random(64),
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->get(route('newsletter.track_open', ['token' => $recipient->token]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->assertNotNull($recipient->fresh()->opened_at);
    }

    public function test_newsletter_click_tracking(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->makeNewsletter($role, $owner, ['status' => 'sent']);
        $recipient = NewsletterRecipient::create([
            'newsletter_id' => $newsletter->id,
            'email' => 'click@example.com',
            'name' => 'Clicker',
            'token' => Str::random(64),
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $target = 'https://example.com/landing';
        $encoded = strtr(base64_encode($target), '+/', '-_');

        // The tracker appends UTM params, so assert the destination contains the target.
        $this->get(route('newsletter.track_click', ['token' => $recipient->token, 'encodedUrl' => $encoded]))
            ->assertRedirectContains($target);

        $this->assertNotNull($recipient->fresh()->clicked_at);
    }

    public function test_newsletter_unsubscribe(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->makeNewsletter($role, $owner, ['status' => 'sent']);
        $recipient = NewsletterRecipient::create([
            'newsletter_id' => $newsletter->id,
            'email' => 'unsub@example.com',
            'name' => 'Unsubber',
            'token' => Str::random(64),
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->post(route('newsletter.unsubscribe', ['token' => $recipient->token]));

        $this->assertDatabaseHas('newsletter_unsubscribes', [
            'role_id' => $role->id,
            'email' => 'unsub@example.com',
        ]);
    }

    public function test_newsletter_ab_test_creates_variant(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = $this->makeNewsletter($role, $owner);
        $rid = UrlUtils::encodeId($role->id);

        $this->actingAs($owner)->post(route('newsletter.ab_test', ['hash' => UrlUtils::encodeId($newsletter->id)]) . '?role_id=' . $rid, [
            'test_field' => 'subject',
            'sample_percentage' => 20,
            'winner_criteria' => 'open_rate',
            'winner_wait_hours' => 24,
        ]);

        $this->assertDatabaseHas('newsletters', [
            'ab_test_id' => $newsletter->fresh()->ab_test_id,
            'ab_variant' => 'B',
        ]);
    }
}
