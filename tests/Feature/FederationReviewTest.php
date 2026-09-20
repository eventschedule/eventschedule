<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminFederationController;
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

    // ------------------------------------------------- settling a flag with nothing to adopt

    /**
     * The flagged tab lists approved instances only, so its Approve button always ran
     * against a row whose status was not changing - and applyStatus() returned before the
     * line that nulls flagged_at. Ticking the box and pressing Approve did nothing at all
     * while still flashing a success, and AdminAlertService's federation_flagged row had
     * no way to drain, against that service's own rule.
     */
    public function test_approving_an_already_approved_instance_settles_its_flag(): void
    {
        $admin = $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
        ]);

        $this->post(route('admin.federation.bulk'), [
            'action' => 'approve',
            'hashes' => [UrlUtils::encodeId($instance->id)],
        ])->assertRedirect();

        $instance->refresh();
        $this->assertNull($instance->flagged_at);
        $this->assertSame(FederatedInstance::STATUS_APPROVED, $instance->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditService::ADMIN_FEDERATION_CLEAR_FLAG,
            'user_id' => $admin->id,
            'model_type' => 'FederatedInstance',
            'model_id' => $instance->id,
        ]);
    }

    /** The same review through the per-row button, which posts to the approve route. */
    public function test_marking_a_flagged_instance_reviewed_clears_the_flag(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
        ]);

        $this->post(route('admin.federation.approve', UrlUtils::encodeId($instance->id)))
            ->assertRedirect();

        $this->assertNull($instance->fresh()->flagged_at);
    }

    /**
     * Nothing about the install's standing changed, so its operator has nothing to be told.
     * Mailing here would make draining the alert a reason to bother every flagged install.
     */
    public function test_settling_a_flag_notifies_nobody(): void
    {
        Mail::fake();
        \Illuminate\Support\Facades\Queue::fake();
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            // Welcomed already, so the welcome is not what is being suppressed here.
            'welcomed_at' => now()->subDay(),
        ]);

        $this->post(route('admin.federation.approve', UrlUtils::encodeId($instance->id)));

        $this->assertNull($instance->fresh()->flagged_at);
        \Illuminate\Support\Facades\Queue::assertNothingPushed();
        Mail::assertNothingSent();
    }

    /**
     * A flag carrying a reported address is a LIVE mismatch. Clearing it without adopting
     * the address or suspending the install is a dismiss that the next push re-raises
     * within the hour, so the dashboard alert would flap instead of settling.
     */
    public function test_approving_does_not_settle_a_reported_address_change(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'https://moved.test',
        ]);

        $this->post(route('admin.federation.bulk'), [
            'action' => 'approve',
            'hashes' => [UrlUtils::encodeId($instance->id)],
        ])->assertRedirect();

        $instance->refresh();
        $this->assertNotNull($instance->flagged_at);
        $this->assertSame('https://moved.test', $instance->reported_site_url);
        $this->assertSame('https://operator.test', $instance->site_url);
    }

    /**
     * The count, not a flat "Instances updated". Reporting a success on a selection it
     * left untouched is what made the no-op above invisible for as long as it was.
     */
    public function test_bulk_reports_what_actually_changed(): void
    {
        Mail::fake();
        $this->adminActing();
        $settled = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        // Literals, not trans_choice() - computing the expected value with the same call
        // the controller makes would pass even if the plural string lost its {0} branch
        // and every count rendered identically. This is the only place {0} is exercised.
        // Amber, not a green success: nothing moved.
        $this->post(route('admin.federation.bulk'), [
            'action' => 'approve',
            'hashes' => [UrlUtils::encodeId($settled->id)],
        ])->assertSessionHas('warning', 'No instances needed changing.')
            ->assertSessionMissing('message');

        $pending = $this->makeInstance(['instance_id' => (string) Str::uuid()]);

        $this->post(route('admin.federation.bulk'), [
            'action' => 'approve',
            'hashes' => [UrlUtils::encodeId($pending->id), UrlUtils::encodeId($settled->id)],
        ])->assertSessionHas('message', '1 instance updated.');
    }

    /**
     * "No instances needed changing" was the same lie in a quieter voice: a row still
     * claiming an address DOES need changing, just not by this button. Saying so, and in
     * amber, is the difference between a report and a shrug.
     */
    public function test_bulk_names_the_rows_it_refused_to_settle(): void
    {
        $this->adminActing();
        $claiming = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'https://moved.test',
        ]);

        $this->post(route('admin.federation.bulk'), [
            'action' => 'approve',
            'hashes' => [UrlUtils::encodeId($claiming->id)],
        ])->assertSessionHas('warning', trans_choice('messages.federation_instances_bulk_address_pending', 1, ['count' => 1]))
            // Never the "nothing needed changing" branch: this one did.
            ->assertSessionMissing('message');

        $this->assertNotNull($claiming->fresh()->flagged_at);
    }

    /**
     * The row this whole path exists for: approved, flagged, and carrying no address to
     * adopt. Per-row Approve is hidden on an approved row and acceptAddress() has nothing
     * to accept, so without this button the only way out was Suspend.
     */
    public function test_a_flagged_row_with_no_reported_address_offers_a_way_out(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
        ]);

        $this->get(route('admin.federation', ['status' => 'flagged']))
            ->assertOk()
            ->assertSeeText(__('messages.federation_mark_reviewed'))
            // The route, not just the label. Asserting the text alone let the button's
            // formaction be pointed anywhere - at suspend, at accept-address - with the
            // whole suite still green, because every behavioural test posts to the approve
            // route directly rather than through the markup.
            ->assertSee(route('admin.federation.approve', UrlUtils::encodeId($instance->id)), false)
            // Nothing was reported, so there is no address to offer adopting.
            ->assertDontSeeText(__('messages.federation_accept_address'));
    }

    /**
     * The push path stores what an install reports without validating it, so a
     * misconfigured APP_URL lands junk in reported_site_url. Branching the panel on the
     * column being non-null drew an Accept button that acceptAddress() then refused,
     * while settleFlag() refused too - a row whose only exit was Suspend, and whose
     * flagged_at never re-stamped because the stored value never changed. That is a
     * dashboard alert pinned open forever, which is the thing this whole change exists to
     * stop. hasAdoptableAddress() reads junk as nothing to adopt.
     */
    public function test_a_junk_reported_address_is_reviewable_not_stranded(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'wp-content',
        ]);

        $this->get(route('admin.federation', ['status' => 'flagged']))
            ->assertOk()
            ->assertSeeText(__('messages.federation_mark_reviewed'))
            // No button whose action would refuse.
            ->assertDontSeeText(__('messages.federation_accept_address'));

        $this->post(route('admin.federation.approve', UrlUtils::encodeId($instance->id)))
            ->assertRedirect();

        $instance->refresh();
        $this->assertNull($instance->flagged_at);
        // The unusable claim goes with the flag rather than sitting there unexplained.
        $this->assertNull($instance->reported_site_url);
    }

    /**
     * An install pushes hourly, and settleFlag() used to read its guard and then save().
     * A push landing between the two wrote a brand-new claim that save() would then
     * silently swallow - only flagged_at was dirty - leaving an approved install claiming
     * a different host with no flag, no panel and nothing on screen to say so. Simulated
     * here by writing the claim underneath the model the way the push does.
     */
    public function test_a_claim_arriving_mid_review_is_not_swallowed(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
        ]);

        FederatedInstance::whereKey($instance->id)->update([
            'reported_site_url' => 'https://clone.test',
            'flagged_at' => now(),
        ]);

        // $instance still holds what the admin looked at, which is the point: the guard
        // passes on that stale read, and only the conditional UPDATE can catch it.
        $controller = new class extends AdminFederationController
        {
            public function settle(FederatedInstance $instance): bool
            {
                return $this->settleFlag($instance);
            }
        };

        $this->assertFalse($controller->settle($instance));

        $fresh = $instance->fresh();
        $this->assertNotNull($fresh->flagged_at);
        $this->assertSame('https://clone.test', $fresh->reported_site_url);
    }

    /** And the other way round: a reported address is adopted, not waved through. */
    public function test_a_row_reporting_an_address_is_not_offered_the_review_button(): void
    {
        $this->adminActing();
        $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'https://moved.test',
        ]);

        $this->get(route('admin.federation', ['status' => 'flagged']))
            ->assertOk()
            ->assertSeeText(__('messages.federation_accept_address'))
            ->assertDontSeeText(__('messages.federation_mark_reviewed'));
    }

    /**
     * authenticateInstance() leaves a pending or suspended flag standing for a human, and
     * AdminAlertService deliberately does not count one - re-surfacing a suspended instance
     * would hand it an escape from moderation. So clearing a flag here would drain nothing
     * and destroy the only column saying the install once moved host. Hence settleFlag() is
     * reached on an approve and never on a suspend.
     */
    public function test_suspending_an_already_suspended_instance_leaves_its_flag(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_SUSPENDED,
            'flagged_at' => now(),
        ]);

        $this->post(route('admin.federation.suspend', UrlUtils::encodeId($instance->id)));

        $this->assertNotNull($instance->fresh()->flagged_at);
        $this->assertDatabaseMissing('audit_logs', ['action' => AuditService::ADMIN_FEDERATION_CLEAR_FLAG]);
    }

    /**
     * A claim with no flag to explain it. The old applyStatus() settled a flag without
     * clearing the address that came with it, so the nexus database still holds rows
     * carrying an address their install stopped reporting. Nothing in the app creates
     * that state any more, which is why this fixture has to build it directly - and why
     * the guard needs its own test or it reads as dead code and gets deleted.
     *
     * Adopting one rewrites site_url, the authority every backlink host check runs
     * against, from a page that renders no warning panel at all (it is gated on
     * flagged_at) and therefore shows nothing of what is being adopted.
     */
    public function test_a_claim_with_no_flag_cannot_be_adopted(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'reported_site_url' => 'https://stale.test',
        ]);

        $this->post(route('admin.federation.accept_address', UrlUtils::encodeId($instance->id)))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.federation_address_none_reported'));

        $this->assertSame('https://operator.test', $instance->fresh()->site_url);
    }

    /**
     * Suspend is documented as the way to REJECT a reported address, and a rejection has
     * to take the claim with it. Left behind, it outlives the flag that explained it: the
     * panel is gated on flagged_at so it goes invisible, while acceptAddress() would
     * still adopt an address the install stopped reporting - rewriting site_url, which is
     * the authority every backlink host check runs against.
     */
    public function test_rejecting_a_reported_address_takes_the_claim_with_it(): void
    {
        Mail::fake();
        $this->adminActing();
        $instance = $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
            'reported_site_url' => 'https://moved.test',
        ]);

        $this->post(route('admin.federation.suspend', UrlUtils::encodeId($instance->id)));

        $instance->refresh();
        $this->assertNull($instance->flagged_at);
        $this->assertNull($instance->reported_site_url);
        $this->assertSame('https://operator.test', $instance->site_url);

        // And with the claim gone, a stale tab cannot resurrect it.
        $this->post(route('admin.federation.accept_address', UrlUtils::encodeId($instance->id)))
            ->assertSessionHas('error', __('messages.federation_address_none_reported'));

        $this->assertSame('https://operator.test', $instance->fresh()->site_url);
    }

    /**
     * The regression risk in splitting the warning panel on isApproved(): a register-path
     * flag sits on a PENDING row, where Approve is rendered and settles it by changing the
     * status. That row must keep the copy about approving and must not be offered a review
     * button that would duplicate the Approve already beside it.
     */
    public function test_a_flagged_pending_instance_keeps_the_approve_copy(): void
    {
        $this->adminActing();
        $this->makeInstance(['flagged_at' => now()]);

        $this->get(route('admin.federation', ['status' => 'pending']))
            ->assertOk()
            ->assertSeeText(__('messages.federation_flagged_warning'))
            ->assertDontSeeText(__('messages.federation_flagged_unknown_warning'))
            ->assertDontSeeText(__('messages.federation_mark_reviewed'));
    }

    /**
     * The per-row half of the same lie: a stale page, or a second submit of the review
     * button, reached applyStatus() with nothing to do and still flashed "Saved".
     */
    public function test_a_review_that_changes_nothing_says_so(): void
    {
        $this->adminActing();
        $instance = $this->makeInstance(['status' => FederatedInstance::STATUS_APPROVED]);

        // Amber, not red: nothing failed, it simply had no effect.
        $this->post(route('admin.federation.approve', UrlUtils::encodeId($instance->id)))
            ->assertRedirect()
            ->assertSessionHas('warning', __('messages.federation_nothing_changed'))
            ->assertSessionMissing('message');

        $this->assertDatabaseMissing('audit_logs', ['action' => AuditService::ADMIN_FEDERATION_CLEAR_FLAG]);
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
