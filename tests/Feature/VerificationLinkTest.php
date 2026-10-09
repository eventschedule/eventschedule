<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The link that verifies an address, an account's or a schedule's.
 *
 * It is proof that the mail arrived only if nobody else could have written it. The route used to
 * check the account's own id and a hash of its own address, both known to the account, and never
 * the signature the mail already carried: an account verified any address it had typed, and a
 * stranger verified any schedule's. Verifying is what hands over the schedules and the tickets
 * waiting for that address (User::claimRolesByEmail(), claimSalesByEmail()).
 *
 *   - a link nobody mailed, an altered one and one past its day verify nothing;
 *   - the mailed link verifies, and so does one mailed before the check existed, which is signed
 *     over the whole address where today's is signed by path (the request that checks a link can
 *     see another scheme or host than the one that mailed it);
 *   - only the schedule's own editors can have its address mailed again.
 */
class VerificationLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function mailedLink(string $type, $notifiable, string $subdomain = ''): string
    {
        $notification = new VerifyEmail($type, $subdomain);

        return (fn () => $this->verificationUrl($notifiable))->call($notification);
    }

    public function test_a_link_nobody_mailed_verifies_no_account(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/verify-email/'.$user->id.'/'.sha1($user->email));

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_a_link_nobody_mailed_verifies_no_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        Role::where('id', $role->id)->update(['email_verified_at' => null]);
        $stranger = $this->createOwner();

        $this->actingAs($stranger)->get('/'.$role->subdomain.'/verify/'.sha1($role->email));

        $this->assertNull($role->fresh()->email_verified_at);
    }

    public function test_the_mailed_link_still_verifies_an_account(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get($this->mailedLink('user', $user));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_the_mailed_link_still_verifies_a_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        Role::where('id', $role->id)->update(['email_verified_at' => null]);

        $this->actingAs($owner)->get($this->mailedLink('role', $role->fresh(), $role->subdomain));

        $this->assertNotNull($role->fresh()->email_verified_at);
    }

    public function test_a_link_mailed_before_the_fix_still_verifies(): void
    {
        $user = User::factory()->unverified()->create();
        $old = \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->actingAs($user)->get($old);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_an_expired_or_altered_link_verifies_nothing(): void
    {
        $user = User::factory()->unverified()->create();
        $link = $this->mailedLink('user', $user);

        $this->actingAs($user)->get(str_replace(sha1($user->email), sha1('someone-else@gmail.com'), $link));
        $this->assertFalse($user->fresh()->hasVerifiedEmail(), 'altered');

        $this->travel(config('auth.verification.expire', 60) + 1)->minutes();
        $this->actingAs($user)->get($link);
        $this->assertFalse($user->fresh()->hasVerifiedEmail(), 'expired');
    }

    public function test_the_schedules_own_editor_can_ask_for_the_mail_again(): void
    {
        Notification::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        Role::where('id', $role->id)->update(['email_verified_at' => null]);

        $this->actingAs($owner)->get('/'.$role->subdomain.'/resend');

        Notification::assertSentTo($role->fresh(), VerifyEmail::class);
    }

    public function test_a_stranger_cannot_have_a_schedule_mailed(): void
    {
        Notification::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        Role::where('id', $role->id)->update(['email_verified_at' => null]);

        $this->actingAs($this->createOwner())->get('/'.$role->subdomain.'/resend');

        Notification::assertNothingSent();
    }
}
