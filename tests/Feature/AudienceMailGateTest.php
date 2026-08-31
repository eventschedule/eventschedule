<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Models\User;
use App\Services\NewsletterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Role::canSendAudienceMail(), the one gate in front of every piece of schedule-authored bulk mail.
 *
 * This file exists because the gate shipped with ZERO coverage and its first branch short-circuits
 * on config('app.is_testing') - so under the default test environment it answers true to
 * everything, and the refactor that pointed NewsletterService::send() and four
 * NewsletterController call sites at it could have broken the gate outright with a green suite.
 *
 * EVERY test here must therefore turn app.is_testing OFF. Without that line they all pass no matter
 * what the method does.
 */
class AudienceMailGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true, 'app.is_testing' => false]);
    }

    private function unverifiedOwner(): User
    {
        return tap($this->createOwner(), fn ($u) => $u->forceFill(['phone_verified_at' => null])->save());
    }

    public function test_selfhost_is_never_gated(): void
    {
        // The operator runs their own mail server; there is no shared reputation to protect.
        config(['app.hosted' => false]);
        $role = $this->createRole($this->unverifiedOwner());

        $this->assertTrue($role->canSendAudienceMail(100000));
    }

    public function test_a_small_audience_goes_out_unverified(): void
    {
        // The whole point of the change: requiring SMTP or an SMS-verified phone before ANY bulk
        // mail is why 4 of 671 schedules have ever sent a newsletter, and the long tail this is
        // meant to serve has single-digit audiences.
        $role = $this->createRole($this->unverifiedOwner());

        $this->assertTrue($role->canSendAudienceMail(10));
    }

    public function test_a_large_audience_still_needs_verification(): void
    {
        $role = $this->createRole($this->unverifiedOwner());

        $this->assertFalse($role->canSendAudienceMail(
            config('usage.audience_mail_unverified_max_recipients') + 1
        ));
    }

    public function test_the_threshold_is_inclusive(): void
    {
        // Pins the boundary, so an off-by-one in either direction is visible.
        $role = $this->createRole($this->unverifiedOwner());
        $limit = config('usage.audience_mail_unverified_max_recipients');

        $this->assertTrue($role->canSendAudienceMail($limit));
        $this->assertFalse($role->canSendAudienceMail($limit + 1));
    }

    public function test_a_verified_phone_lifts_the_ceiling(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['phone_verified_at' => now()])->save();
        $role = $this->createRole($owner);

        $this->assertTrue($role->canSendAudienceMail(100000));
    }

    public function test_an_ownerless_schedule_fails_closed(): void
    {
        // roles.user_id is nullable - AI-imported claimable venues, which can still accumulate
        // followers. With no owner there is nobody to hold accountable, so it must refuse.
        $role = $this->createRole($this->unverifiedOwner());
        $role->forceFill(['user_id' => null])->save();

        $this->assertFalse($role->fresh()->canSendAudienceMail(1));
    }

    public function test_the_actor_is_the_sender_not_the_owner(): void
    {
        // NOT a behaviour-preserving detail: the old gate read $newsletter->user, the COMPOSING
        // user. An admin-level member with a verified phone can send today and must keep being
        // able to, even though the schedule's owner has verified nothing.
        $role = $this->createRole($this->unverifiedOwner());
        $admin = $this->createOwner();
        $admin->forceFill(['phone_verified_at' => now()])->save();

        $this->assertFalse($role->canSendAudienceMail(100000));
        $this->assertTrue($role->canSendAudienceMail(100000, $admin));
    }

    public function test_a_platform_admin_is_never_gated(): void
    {
        $role = $this->createRole($this->unverifiedOwner());
        $admin = $this->createOwner(admin: true);

        $this->assertTrue($role->canSendAudienceMail(100000, $admin));
    }

    public function test_a_zero_recipient_send_is_refused_for_an_unverified_schedule(): void
    {
        // Callers that have not resolved recipients yet pass 0, which is the strict answer and
        // preserves the pre-existing behaviour of the compose-time warning.
        $role = $this->createRole($this->unverifiedOwner());

        $this->assertFalse($role->canSendAudienceMail(0));
    }

    public function test_a_refused_scheduled_newsletter_leaves_the_cron_queue(): void
    {
        // The defect: send() put a refused row back to 'scheduled' with its scheduled_at still in
        // the past, so ProcessScheduledNewsletters re-picked it every single minute, re-resolved
        // the whole recipient set and re-refused, forever - while the composer went on showing it
        // as scheduled and the owner was told nothing.
        //
        // 'draft' with a null scheduled_at is terminal: the cron only reads 'scheduled' rows.
        $owner = $this->unverifiedOwner();
        $role = $this->createRole($owner);

        // Over the unverified ceiling, so the gate refuses.
        $over = (int) config('usage.audience_mail_unverified_max_recipients', 50) + 5;
        for ($i = 0; $i < $over; $i++) {
            RoleSubscriber::create([
                'role_id' => $role->id,
                'email' => "fan{$i}@fans.test",
                'token' => RoleSubscriber::newToken(),
                'confirmed_at' => now(),
            ]);
        }

        $newsletter = Newsletter::create([
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'subject' => 'Test Newsletter',
            'status' => 'scheduled',
            'scheduled_at' => now()->subMinutes(5),
            'template' => 'modern',
            'blocks' => [],
            'type' => 'schedule',
        ]);

        $result = app(NewsletterService::class)->send($newsletter);

        $this->assertSame('requires_verification', $result[0]);

        $newsletter->refresh();
        $this->assertSame('draft', $newsletter->status);
        $this->assertNull($newsletter->scheduled_at, 'a refused row must not stay due for the cron');
        $this->assertNull($newsletter->send_token);

        // The thing that actually mattered: a second pass finds nothing to do.
        $this->assertSame(
            0,
            Newsletter::where('status', 'scheduled')->where('scheduled_at', '<=', now())->count(),
        );
    }
}
