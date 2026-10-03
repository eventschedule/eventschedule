<?php

namespace Tests\Feature;

use App\Mail\ClaimRole;
use App\Mail\OnboardingNudge;
use App\Mail\TicketPurchase;
use App\Models\NewsletterSegment;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Opting out has to work, and nobody is put on a list they did not ask for.
 *
 * A List-Unsubscribe header is a promise that a mail client's one-click POST (RFC 8058) stops the
 * mail. Ticket receipts and other transactional guest mail used to point it at the unsigned
 * role.unsubscribe form, which needs a CSRF token and an address the POST never carries, so the
 * button always failed - and targeted the schedule's contact address, not the guest, anyway.
 */
class EmailOptOutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_ticket_receipt_carries_no_one_click_header(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role);

        $mail = new TicketPurchase($sale, $event, $role);
        $headers = method_exists($mail, 'headers') ? $mail->headers()->text : [];

        $this->assertArrayNotHasKey('List-Unsubscribe', $headers);
    }

    public function test_a_nudge_carries_a_signed_one_click_opt_out(): void
    {
        $user = $this->createOwner();

        $headers = (new OnboardingNudge($user, 1))->headers()->text;

        $this->assertSame('List-Unsubscribe=One-Click', $headers['List-Unsubscribe-Post']);
        $this->assertStringContainsString('/user/unsubscribe', $headers['List-Unsubscribe']);
        $this->assertStringContainsString('sig=', $headers['List-Unsubscribe']);
    }

    public function test_a_claim_invitation_one_click_works_only_when_signed(): void
    {
        $organizer = $this->createOwner();
        $venue = $this->createRole($organizer, 'venue');
        $event = $this->createEvent($venue);
        $act = $this->createRole($organizer, 'talent', ['email' => 'act@example.com', 'is_subscribed' => true]);
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $header = (new ClaimRole($event->fresh(), $act))->headers()->text['List-Unsubscribe'];
        $url = trim($header, '<>');
        $this->assertStringContainsString('/unsubscribe/one-click', $url);

        // A forged signature writes nothing, and the answer gives nothing away.
        $forged = preg_replace('/sig=[0-9a-f]+/', 'sig='.str_repeat('0', 64), $url);
        $this->post($forged, ['List-Unsubscribe' => 'One-Click'])->assertNoContent();
        $this->assertTrue((bool) Role::find($act->id)->is_subscribed);

        $this->post($url, ['List-Unsubscribe' => 'One-Click'])->assertNoContent();
        $this->assertFalse((bool) Role::find($act->id)->is_subscribed);
    }

    /** An address added to a manual segment is a row in that list, not an account or a follower. */
    public function test_importing_contacts_creates_no_accounts_and_no_followers(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $segment = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Imported', 'type' => 'manual']);
        $existing = $this->createOwner();

        $this->actingAs($owner)->post(route('newsletter.import.store').'?role_id='.UrlUtils::encodeId($role->id), [
            'segment_target' => 'existing',
            'segment_id' => UrlUtils::encodeId($segment->id),
            'entries' => [
                ['email' => 'stranger@example.com', 'name' => 'A Stranger'],
                ['email' => $existing->email, 'name' => 'Has An Account'],
            ],
        ]);

        $this->assertDatabaseHas('newsletter_segment_users', ['email' => 'stranger@example.com', 'user_id' => null]);
        $this->assertDatabaseHas('newsletter_segment_users', ['email' => strtolower($existing->email), 'user_id' => $existing->id]);
        $this->assertDatabaseMissing('users', ['email' => 'stranger@example.com']);
        $this->assertFalse(DB::table('role_user')->where('role_id', $role->id)->where('user_id', $existing->id)->exists());
    }
}
