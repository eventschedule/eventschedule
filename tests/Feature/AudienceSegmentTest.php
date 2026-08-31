<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\NewsletterSegment;
use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Services\NewsletterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Account-less subscribers reaching the newsletter composer.
 *
 * The half of the audience feature that makes slice 1 worth shipping on its own: the composer
 * already exists, its audience was just empty.
 */
class AudienceSegmentTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole($this->createOwner());
    }

    private function subscribe(Role $role, string $email, bool $confirmed = true): RoleSubscriber
    {
        return RoleSubscriber::create([
            'role_id' => $role->id,
            'email' => $email,
            'name' => 'A Fan',
            'token' => RoleSubscriber::newToken(),
            'confirmed_at' => $confirmed ? now() : null,
        ]);
    }

    private function segment(Role $role, string $type): NewsletterSegment
    {
        return NewsletterSegment::create([
            'role_id' => $role->id,
            'name' => 'seg',
            'type' => $type,
        ]);
    }

    public function test_the_segment_resolves_account_less_rows(): void
    {
        $this->subscribe($this->role, 'Fan@Fans.test');

        $rows = $this->segment($this->role, 'all_subscribers')->resolveRecipients();

        $this->assertCount(1, $rows);
        // user_id null is what tells the rest of the pipeline these have no account.
        // newsletter_recipients.user_id is nullable precisely for this.
        $this->assertNull($rows->first()->user_id);
        $this->assertSame('fan@fans.test', $rows->first()->email);
    }

    public function test_an_unconfirmed_subscriber_is_never_resolved(): void
    {
        $this->subscribe($this->role, 'confirmed@fans.test');
        $this->subscribe($this->role, 'pending@fans.test', confirmed: false);

        $emails = $this->segment($this->role, 'all_subscribers')->resolveRecipients()->pluck('email');

        // Assert the SET, not the count: with one subscriber either way this passes whether or not
        // the confirmed() scope is applied.
        $this->assertEqualsCanonicalizing(['confirmed@fans.test'], $emails->all());
    }

    public function test_the_segment_does_not_leak_across_schedules(): void
    {
        $other = $this->createRole($this->createOwner(), 'talent');
        $this->subscribe($this->role, 'mine@fans.test');
        $this->subscribe($other, 'theirs@fans.test');

        $emails = $this->segment($this->role, 'all_subscribers')->resolveRecipients()->pluck('email');

        $this->assertEqualsCanonicalizing(['mine@fans.test'], $emails->all());
    }

    public function test_all_followers_still_excludes_subscribers(): void
    {
        // The non-regression half. Without it, someone "simplifying" by widening all_followers to
        // cover both passes every other test in this file.
        $follower = $this->createOwner();
        $this->followRole($follower, $this->role);
        $this->subscribe($this->role, 'subscriber@fans.test');

        $emails = $this->segment($this->role, 'all_followers')->resolveRecipients()->pluck('email');

        $this->assertNotContains('subscriber@fans.test', $emails->all());
        $this->assertContains(strtolower($follower->email), $emails->all());
    }

    public function test_the_composer_default_audience_includes_subscribers(): void
    {
        // resolveRecipients() reaches followers by TWO routes - a saved all_followers segment, and
        // a direct fallback when there is none. This exercises the fallback, which is the one
        // nearly every schedule actually hits, and missing it is silent.
        $this->subscribe($this->role, 'fan@fans.test');

        $emails = app(NewsletterService::class)
            ->resolveRecipients($this->role, [])
            ->pluck('email');

        $this->assertContains('fan@fans.test', $emails->all());
    }

    public function test_the_composer_default_audience_includes_subscribers_via_a_segment(): void
    {
        // And the other route: a schedule that HAS saved segments.
        $this->segment($this->role, 'all_subscribers');
        $this->subscribe($this->role, 'fan@fans.test');

        $emails = app(NewsletterService::class)
            ->resolveRecipients($this->role, [])
            ->pluck('email');

        $this->assertContains('fan@fans.test', $emails->all());
    }

    public function test_the_default_audience_includes_subscribers_even_with_a_saved_followers_segment(): void
    {
        // The case the first fix missed. Saving an all_followers segment is an ordinary thing to
        // do, and it used to make the subscriber union conditional-out: account followers were
        // reached, the account-less audience silently got nothing, and the composer still said
        // "will be sent to all followers by default".
        $this->segment($this->role, 'all_followers');
        $follower = $this->createOwner();
        $this->followRole($follower, $this->role);
        $this->subscribe($this->role, 'fan@fans.test');

        $emails = app(NewsletterService::class)->resolveRecipients($this->role, [])->pluck('email');

        $this->assertContains('fan@fans.test', $emails->all());
        $this->assertContains(strtolower($follower->email), $emails->all());
    }

    public function test_the_default_audience_does_not_duplicate_a_person_two_sources_share(): void
    {
        // The union is additive now, so the dedupe below it is load-bearing.
        $this->segment($this->role, 'all_subscribers');
        $this->subscribe($this->role, 'fan@fans.test');

        $emails = app(NewsletterService::class)->resolveRecipients($this->role, [])->pluck('email');

        $this->assertSame(1, $emails->filter(fn ($e) => $e === 'fan@fans.test')->count());
    }

    public function test_an_explicit_segment_still_wins(): void
    {
        // Additive applies only to the "everyone" default. Choosing a segment must still mean it.
        $segment = $this->segment($this->role, 'all_followers');
        $this->subscribe($this->role, 'fan@fans.test');

        $emails = app(NewsletterService::class)
            ->resolveRecipients($this->role, [$segment->id])->pluck('email');

        $this->assertNotContains('fan@fans.test', $emails->all());
    }

    public function test_an_unsubscribed_address_is_dropped_from_the_composer(): void
    {
        $this->subscribe($this->role, 'fan@fans.test');
        \App\Models\NewsletterUnsubscribe::create([
            'role_id' => $this->role->id,
            'email' => 'fan@fans.test',
            'unsubscribed_at' => now(),
        ]);

        $emails = app(NewsletterService::class)
            ->resolveRecipients($this->role, [])
            ->pluck('email');

        $this->assertNotContains('fan@fans.test', $emails->all());
    }

    public function test_the_type_label_is_not_subschedule(): void
    {
        // Every one of the four duplicated ternaries this replaced fell through to
        // messages.subschedule for an unrecognised type, so a new segment type rendered as
        // "Sub-schedule" while every test still passed.
        $this->assertSame(
            __('messages.all_subscribers'),
            NewsletterSegment::typeLabel('all_subscribers')
        );
        $this->assertNotSame(
            __('messages.subschedule'),
            NewsletterSegment::typeLabel('all_subscribers')
        );
    }

    public function test_every_existing_type_keeps_its_label(): void
    {
        // The refactor must be pure. 'group' is the one that legitimately maps to subschedule.
        $this->assertSame(__('messages.all_followers'), NewsletterSegment::typeLabel('all_followers'));
        $this->assertSame(__('messages.ticket_buyers'), NewsletterSegment::typeLabel('ticket_buyers'));
        $this->assertSame(__('messages.manual'), NewsletterSegment::typeLabel('manual'));
        $this->assertSame(__('messages.waitlist'), NewsletterSegment::typeLabel('waitlist'));
        $this->assertSame(__('messages.subschedule'), NewsletterSegment::typeLabel('group'));
    }
}
