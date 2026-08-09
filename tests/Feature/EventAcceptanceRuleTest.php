<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Role::autoAcceptsEventFrom() - the single rule deciding whether an event attaching to a
 * schedule lands accepted or pending. Feature rather than Unit: isMember() hits the database.
 */
class EventAcceptanceRuleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Claimed venue that moderates everything: the baseline "must stay pending" fixture. */
    private function moderatedVenue(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_approval' => true,
        ]);
    }

    /** Schedule with no owner at all - the placeholder an importer invents. */
    private function ownerlessVenue(array $attrs = []): Role
    {
        $venue = new Role;
        $venue->name = 'Placeholder Hall';
        $venue->subdomain = 'placeholder'.strtolower(Str::random(8));
        $venue->type = 'venue';

        foreach ($attrs as $key => $value) {
            $venue->{$key} = $value;
        }

        $venue->save();

        return $venue->fresh();
    }

    public function test_member_of_the_schedule_auto_accepts(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['require_approval' => true]);

        $this->assertTrue($venue->autoAcceptsEventFrom($owner));
    }

    public function test_viewer_level_counts_as_a_member(): void
    {
        $venue = $this->moderatedVenue();
        $viewer = $this->createOwner();
        $this->followRole($viewer, $venue, 'viewer');

        $this->assertTrue($venue->autoAcceptsEventFrom($viewer));
    }

    public function test_follower_is_not_a_member(): void
    {
        $venue = $this->moderatedVenue();
        $follower = $this->createOwner();
        $this->followRole($follower, $venue);

        $this->assertFalse($venue->autoAcceptsEventFrom($follower));
    }

    public function test_ownerless_schedule_auto_accepts_even_when_it_moderates(): void
    {
        // The settings that would otherwise force a pending row. Nobody owns this schedule, so
        // that row could never be actioned and the event would be hidden on its page forever.
        $venue = $this->ownerlessVenue([
            'accept_requests' => false,
            'require_approval' => true,
        ]);

        $this->assertNull($venue->user_id);
        $this->assertTrue($venue->autoAcceptsEventFrom(null));
    }

    public function test_claimed_schedule_without_approval_auto_accepts(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', [
            'accept_requests' => true,
            'require_approval' => false,
        ]);

        $this->assertTrue($venue->autoAcceptsEventFrom(null));
    }

    public function test_claimed_schedule_requiring_approval_stays_pending(): void
    {
        $stranger = $this->createOwner();

        $this->assertFalse($this->moderatedVenue()->autoAcceptsEventFrom($stranger));
    }

    public function test_talent_ignores_a_false_require_approval_column(): void
    {
        // getRequireApprovalAttribute() forces true for talents, so the column cannot open
        // them up. Only membership or ownerlessness can.
        $talent = $this->createRole($this->createOwner(), 'talent', [
            'accept_requests' => true,
            'require_approval' => false,
        ]);

        $this->assertTrue($talent->require_approval);
        $this->assertFalse($talent->autoAcceptsEventFrom(null));
    }

    public function test_approved_subdomain_auto_accepts_and_others_do_not(): void
    {
        $curatorOwner = $this->createOwner();
        $submitter = $this->createRole($this->createOwner(), 'talent');

        $curator = $this->createRole($curatorOwner, 'curator', [
            'accept_requests' => true,
            'require_approval' => true,
            'approved_subdomains' => [$submitter->subdomain],
        ]);

        $other = $this->createRole($this->createOwner(), 'talent');

        $this->assertTrue($curator->autoAcceptsEventFrom(null, $submitter));
        $this->assertFalse($curator->autoAcceptsEventFrom(null, $other));
        $this->assertFalse($curator->autoAcceptsEventFrom(null, null));
    }

    /**
     * The guard that stops rule 2 from ever being "simplified" to ! isClaimed(). On hosted,
     * Role::updating nulls email_verified_at on every email change, so an owner editing their
     * venue's contact would otherwise turn a moderated schedule into an auto-accepting one
     * until they clicked the verify link.
     */
    public function test_unverified_contact_does_not_make_an_owned_schedule_auto_accept(): void
    {
        config(['app.hosted' => true]);
        \Illuminate\Support\Facades\Notification::fake();

        $venue = $this->moderatedVenue();
        $this->assertTrue($venue->isClaimed());

        $venue->email = 'changed@gmail.com';
        $venue->save();
        $venue->refresh();

        $this->assertNull($venue->email_verified_at);
        $this->assertFalse($venue->isClaimed());
        $this->assertNotNull($venue->user_id);

        $this->assertFalse($venue->autoAcceptsEventFrom($this->createOwner()));
    }
}
