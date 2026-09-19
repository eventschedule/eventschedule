<?php

namespace Tests\Feature;

use App\Mail\FederationInstanceReviewed;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Models\User;
use App\Services\AuditService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Nexus-side moderation: approving an instance, suspending it, and blocking a
 * single listing.
 */
class FederationReviewTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    private function makeInstance(array $attributes = []): FederatedInstance
    {
        return FederatedInstance::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'contact_email' => 'ops@operator.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_PENDING,
        ], $attributes));
    }

    private function makeEvent(FederatedInstance $instance, array $attributes = []): FederatedEvent
    {
        return FederatedEvent::create(array_merge([
            'federated_instance_id' => $instance->id,
            'external_id' => Str::random(8),
            'url' => 'https://operator.test/show',
            'name' => 'Summer Show',
            'next_occurrence_at' => now()->addWeek(),
            'image_url' => 'https://operator.test/f.jpg',
        ], $attributes));
    }

    /**
     * contact_email is optional at registration and applyStatus() silently skips the mail when it
     * is missing, so without this panel an admin approves an install believing the operator was
     * told. Both directions asserted: a warning that always shows would be just as useless.
     */
    public function test_an_instance_with_no_contact_email_is_flagged_in_the_queue(): void
    {
        $this->adminActing();
        $this->makeInstance(['contact_email' => null]);

        $this->get(route('admin.federation'))
            ->assertOk()
            ->assertSeeText(__('messages.federation_no_contact_email_warning'));
    }

    public function test_an_instance_with_a_contact_email_is_not_flagged(): void
    {
        $this->adminActing();
        $this->makeInstance();

        $this->get(route('admin.federation'))
            ->assertOk()
            ->assertDontSeeText(__('messages.federation_no_contact_email_warning'));
    }

    public function test_the_queue_renders_with_pending_instances_and_a_sample(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance();
        $this->makeEvent($instance, ['name' => 'A Sampled Listing']);

        $this->get(route('admin.federation'))
            ->assertOk()
            ->assertSee('Operator')
            // Approving on a name alone would be approving unseen content.
            ->assertSee('A Sampled Listing');
    }

    /**
     * The reported bug: on the Approved tab the only link was site_url, which on a
     * selfhost install redirects to its login page.
     */
    public function test_an_approved_instance_links_to_its_public_schedules(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $this->makeEvent($instance, [
            'schedule_name' => 'Dieppe Agenda',
            'schedule_url' => 'https://operator.test/dieppe-agenda',
        ]);

        $this->get(route('admin.federation', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('Dieppe Agenda')
            ->assertSee('https://operator.test/dieppe-agenda');
    }

    /** One row per schedule, not per listing, with the listing count beside it. */
    public function test_listings_from_one_schedule_are_listed_once(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        foreach (['One', 'Two', 'Three'] as $name) {
            $this->makeEvent($instance, [
                'name' => $name,
                'schedule_name' => 'Dieppe Agenda',
                'schedule_url' => 'https://operator.test/dieppe-agenda',
            ]);
        }

        $response = $this->get(route('admin.federation', ['status' => 'approved']))->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), 'https://operator.test/dieppe-agenda'));
        $response->assertSee('3 listings');
    }

    /**
     * schedule_url is host-checked at intake now, but rows stored before that check
     * kept whatever the sender sent, and a re-push never rewrites them. Writing
     * straight through the model is exactly how those rows exist.
     */
    public function test_a_schedule_link_off_the_instances_host_is_not_clickable(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $this->makeEvent($instance, [
            'schedule_name' => 'Dieppe Agenda',
            'schedule_url' => 'https://evil.test/phish',
        ]);

        $this->get(route('admin.federation', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('Dieppe Agenda')
            ->assertDontSee('evil.test');
    }

    public function test_an_instance_with_no_listings_says_so(): void
    {
        $this->adminActing();
        $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        $this->get(route('admin.federation', ['status' => 'approved']))
            ->assertOk()
            ->assertSee(__('messages.federation_no_listings_yet'));
    }

    /** Samples used to load only for pending rows, leaving the Approved tab blank. */
    public function test_sample_listings_render_on_the_approved_tab(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $this->makeEvent($instance, ['name' => 'A Sampled Listing']);

        $this->get(route('admin.federation', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('A Sampled Listing');
    }

    /**
     * image_path is deliberately absent from $fillable, so it has to be assigned
     * after creation - mass assignment drops it silently and scopeLive() then matches
     * nothing, which would let this test pass with the link absent, proving nothing.
     */
    public function test_the_live_listings_link_needs_an_approved_instance_with_live_rows(): void
    {
        $this->adminActing();

        $pending = $this->makeInstance(['name' => 'Still Pending']);
        $pendingEvent = $this->makeEvent($pending, [
            'schedule_name' => 'Pending Schedule',
            'schedule_url' => 'https://operator.test/pending',
        ]);
        $pendingEvent->image_path = 'federated/f.jpg';
        $pendingEvent->save();

        // Approval is what makes a listing live, so a pending instance gets no link
        // to a page that would be empty.
        $this->get(route('admin.federation', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Pending Schedule')
            ->assertDontSee('instance='.UrlUtils::encodeId($pending->id), false);

        $approved = $this->makeInstance([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://live.test',
            'name' => 'Live Operator',
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);
        $approvedEvent = $this->makeEvent($approved, [
            'url' => 'https://live.test/show',
            'schedule_name' => 'Live Schedule',
            'schedule_url' => 'https://live.test/live-schedule',
        ]);
        $approvedEvent->image_path = 'federated/g.jpg';
        $approvedEvent->save();

        $this->get(route('admin.federation', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('instance='.UrlUtils::encodeId($approved->id), false);
    }

    public function test_approving_lights_up_everything_already_received(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance();
        $event = $this->makeEvent($instance);
        $event->image_path = 'federated/f.jpg';
        $event->save();

        // Held back while pending, even though the listing is already stored.
        $this->assertSame(0, FederatedEvent::listable()->count());

        $this->post(route('admin.federation.approve', UrlUtils::encodeId($instance->id)))
            ->assertRedirect();

        $this->assertSame('approved', $instance->fresh()->status);
        $this->assertSame(1, FederatedEvent::listable()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.federation_approve']);
    }

    public function test_suspending_hides_the_listings_again(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $event = $this->makeEvent($instance);
        $event->image_path = 'federated/f.jpg';
        $event->save();

        $this->assertSame(1, FederatedEvent::listable()->count());

        $this->post(route('admin.federation.suspend', UrlUtils::encodeId($instance->id)))
            ->assertRedirect();

        $this->assertSame(0, FederatedEvent::listable()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.federation_suspend']);
    }

    /**
     * Mail is tied to the admin decision, never to the unauthenticated registration. The first
     * approval is the welcome (FederationWelcomeTest covers it); a suspension is the short note.
     */
    public function test_the_operator_is_emailed_on_a_decision(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance();

        $this->post(route('admin.federation.approve', UrlUtils::encodeId($instance->id)));

        Mail::assertSent(\App\Mail\FederationInstanceWelcome::class, fn ($mail) => $mail->hasTo('ops@operator.test'));

        $this->post(route('admin.federation.suspend', UrlUtils::encodeId($instance->id)));

        Mail::assertSent(FederationInstanceReviewed::class, fn ($mail) => $mail->hasTo('ops@operator.test'));
    }

    public function test_blocking_a_listing_hides_it_without_deleting_the_row(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);
        $event = $this->makeEvent($instance);
        $event->image_path = 'federated/f.jpg';
        $event->save();

        $this->post(route('admin.federation.block_event', UrlUtils::encodeId($event->id)))
            ->assertRedirect();

        $this->assertNotNull($event->fresh()->blocked_at);
        $this->assertSame(0, FederatedEvent::listable()->count());
        // The row survives so a re-push cannot quietly restore it.
        $this->assertDatabaseHas('federated_events', ['id' => $event->id]);
    }

    /**
     * bulk() can approve up to MAX_BULK instances in one request. Sending each email
     * inline would mean that many blocking SMTP round-trips inside a single admin
     * request, so the notification goes through the queue like the rest of the app.
     */
    public function test_decision_emails_are_queued_not_sent_inline(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $this->adminActing();
        $instance = $this->makeInstance();

        $this->post(route('admin.federation.approve', UrlUtils::encodeId($instance->id)));

        // The first approval's mail is the welcome, queued on its own job so a final failure can
        // hand the claim back.
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendFederationWelcome::class);
    }

    public function test_bulk_approval_covers_several_instances_at_once(): void
    {
        Mail::fake();
        $this->adminActing();
        $a = $this->makeInstance();
        $b = $this->makeInstance(['instance_id' => (string) Str::uuid()]);

        $this->post(route('admin.federation.bulk'), [
            'action' => 'approve',
            'hashes' => [UrlUtils::encodeId($a->id), UrlUtils::encodeId($b->id)],
        ])->assertRedirect();

        $this->assertSame('approved', $a->fresh()->status);
        $this->assertSame('approved', $b->fresh()->status);
    }

    // ------------------------------------------------- resolving an address change

    /**
     * The push path flags a mismatch and leaves the instance APPROVED, where
     * applyStatus() - the only other thing that clears flagged_at - early-returns
     * because the status is not changing, and the queue hides the Approve button. So
     * this row offered nothing but Suspend, and the dashboard alert counting it could
     * never drain.
     */
    public function test_accepting_a_reported_address_adopts_it_and_settles_the_flag(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'https://moved.test',
        ]);

        $this->post(route('admin.federation.accept_address', UrlUtils::encodeId($instance->id)))
            ->assertRedirect();

        $instance->refresh();
        $this->assertSame('https://moved.test', $instance->site_url);
        $this->assertNull($instance->reported_site_url);
        $this->assertNull($instance->flagged_at);
        // Still approved: accepting settles the mismatch, it does not re-review the install.
        $this->assertSame(FederatedInstance::STATUS_APPROVED, $instance->status);
    }

    /** site_url is the authority every backlink check runs against, so the move is logged. */
    public function test_accepting_an_address_is_audited(): void
    {
        $admin = $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'https://moved.test',
        ]);

        $this->post(route('admin.federation.accept_address', UrlUtils::encodeId($instance->id)));

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditService::ADMIN_FEDERATION_ACCEPT_ADDRESS,
            'user_id' => $admin->id,
            'model_type' => 'FederatedInstance',
            'model_id' => $instance->id,
        ]);
    }

    /**
     * Rows flagged before reported_site_url existed carry the mismatch but not the
     * address, and so does a second submit. Neither may rewrite site_url to null.
     */
    public function test_accepting_with_nothing_reported_changes_nothing(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
        ]);

        // The exact message, not just "an error": "no address reported yet" is the normal
        // state of a row flagged before the column existed, while "not a valid URL" is a
        // fault. Asserting only that something failed lets either guard cover for the
        // other, and this test then pins neither.
        $this->post(route('admin.federation.accept_address', UrlUtils::encodeId($instance->id)))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.federation_address_none_reported'));

        $instance->refresh();
        $this->assertSame('https://operator.test', $instance->site_url);
        $this->assertNotNull($instance->flagged_at);
    }

    /**
     * It arrived signed, but this is the moment it becomes the authority for ownsUrl()
     * and every backlink check, so it is held to registration's own rule.
     */
    public function test_a_reported_address_that_is_not_a_url_is_refused(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'not-a-url',
        ]);

        $this->post(route('admin.federation.accept_address', UrlUtils::encodeId($instance->id)))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.federation_address_invalid'));

        $this->assertSame('https://operator.test', $instance->fresh()->site_url);
    }

    /** The queue filter the dashboard alert links to, matched to the alert's own query. */
    public function test_the_flagged_filter_lists_only_flagged_approved_instances(): void
    {
        $this->adminActing();
        $flagged = $this->makeInstance([
            'name' => 'Moved Install',
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'https://moved.test',
        ]);
        $this->makeInstance([
            'instance_id' => (string) Str::uuid(),
            'name' => 'Settled Install',
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);

        $this->get(route('admin.federation', ['status' => 'flagged']))
            ->assertOk()
            ->assertSeeText($flagged->name)
            ->assertDontSeeText('Settled Install')
            // Both addresses, so the reviewer can see what is actually being claimed.
            ->assertSeeText('https://moved.test')
            ->assertSeeText(__('messages.federation_accept_address'));
    }

    /** A tab that is empty on every healthy install is noise, so it is opt-in on content. */
    public function test_the_flagged_tab_is_absent_when_nothing_is_flagged(): void
    {
        $this->adminActing();
        $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        $this->get(route('admin.federation'))
            ->assertOk()
            ->assertDontSee(route('admin.federation', ['status' => 'flagged']))
            ->assertDontSeeText(__('messages.federation_status_flagged'));
    }

    public function test_accepting_an_address_is_absent_off_the_nexus(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'https://moved.test',
        ]);
        config(['app.is_nexus' => false]);

        $this->post(route('admin.federation.accept_address', UrlUtils::encodeId($instance->id)))
            ->assertNotFound();
    }

    public function test_moderation_requires_an_admin(): void
    {
        $this->actingAs($this->createOwner());

        // The admin middleware redirects web requests away and 403s JSON ones.
        $this->get(route('admin.federation'))->assertRedirect(route('home'));
        $this->getJson(route('admin.federation'))->assertStatus(403);
    }

    public function test_moderation_is_absent_off_the_nexus(): void
    {
        $this->adminActing();
        config(['app.is_nexus' => false]);

        $this->get(route('admin.federation'))->assertNotFound();
    }
}
