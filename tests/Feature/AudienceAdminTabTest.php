<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoleSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The owner-facing audience list.
 *
 * Follower and subscriber emails are visible on schedule-owner-facing surfaces and NOWHERE else.
 */
class AudienceAdminTabTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

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

    public function test_the_owner_sees_subscribers_and_their_confirmation_state(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->subscribe($role, 'yes@fans.test');
        $this->subscribe($role, 'pending@fans.test', confirmed: false);

        $html = $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'followers']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('yes@fans.test', $html);
        $this->assertStringContainsString('pending@fans.test', $html);

        // Unconfirmed rows are listed but never mailed, so the list count and the recipient count
        // on a send differ. The page has to explain that or it reads as a bug.
        $this->assertStringContainsString(__('messages.subscriber_pending'), $html);
    }

    public function test_a_stranger_cannot_see_the_audience(): void
    {
        $role = $this->createRole($this->createOwner());
        $this->subscribe($role, 'private@fans.test');

        // viewAdmin redirects a non-member away rather than 404ing. What matters is that the
        // address is not in the response either way, so assert both.
        $response = $this->actingAs($this->createOwner())
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'followers']));

        $response->assertRedirect();
        $this->assertStringNotContainsString('private@fans.test', $response->getContent());
    }

    public function test_subscribers_do_not_leak_between_schedules_on_the_tab(): void
    {
        $owner = $this->createOwner();
        $mine = $this->createRole($owner);
        $theirs = $this->createRole($this->createOwner(), 'talent');
        $this->subscribe($mine, 'mine@fans.test');
        $this->subscribe($theirs, 'theirs@fans.test');

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $mine->subdomain, 'tab' => 'followers']))
            ->assertOk()
            ->assertSee('mine@fans.test')
            ->assertDontSee('theirs@fans.test');
    }

    public function test_the_owner_can_remove_a_subscriber(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $sub = $this->subscribe($role, 'fan@fans.test');

        $this->actingAs($owner)
            ->delete(route('role.subscribers.remove', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($sub->id)]))
            ->assertRedirect();

        $this->assertSame(0, RoleSubscriber::count());
    }

    public function test_a_stranger_cannot_remove_a_subscriber(): void
    {
        $role = $this->createRole($this->createOwner());
        $sub = $this->subscribe($role, 'fan@fans.test');

        $this->actingAs($this->createOwner())
            ->delete(route('role.subscribers.remove', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($sub->id)]))
            ->assertStatus(403);

        $this->assertSame(1, RoleSubscriber::count());
    }

    public function test_merging_two_schedules_moves_subscribers_without_a_duplicate_key_error(): void
    {
        // role_subscribers is unique on (role_id, email), so a bare
        // update(['role_id' => target]) throws 1062 the moment one address subscribed to both.
        // The shared address is the whole point of the fixture.
        $owner = $this->createOwner();
        $target = $this->createRole($owner, 'venue', ['name' => 'Dup Venue']);
        $source = $this->createRole($owner, 'venue', ['name' => 'Dup Venue', 'email_verified_at' => null]);
        $source->refresh();

        $this->subscribe($target, 'both@fans.test');
        $this->subscribe($source, 'both@fans.test');
        $this->subscribe($source, 'only-source@fans.test');

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => \App\Utils\UrlUtils::encodeId($target->id),
            'source_ids' => [\App\Utils\UrlUtils::encodeId($source->id)],
        ]);

        // The unique address moved; the shared one was dropped rather than colliding.
        $this->assertSame(2, RoleSubscriber::where('role_id', $target->id)->count());
        $this->assertSame(0, RoleSubscriber::where('role_id', $source->id)->count());
        $this->assertEqualsCanonicalizing(
            ['both@fans.test', 'only-source@fans.test'],
            RoleSubscriber::where('role_id', $target->id)->pluck('email')->all()
        );
    }

    public function test_merging_carries_the_suppression_list_over(): void
    {
        // Otherwise somebody who opted out of the source starts hearing from the survivor.
        $owner = $this->createOwner();
        $target = $this->createRole($owner, 'venue', ['name' => 'Dup Venue']);
        $source = $this->createRole($owner, 'venue', ['name' => 'Dup Venue', 'email_verified_at' => null]);
        $source->refresh();

        \App\Models\NewsletterUnsubscribe::create([
            'role_id' => $source->id, 'email' => 'nope@fans.test', 'unsubscribed_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => \App\Utils\UrlUtils::encodeId($target->id),
            'source_ids' => [\App\Utils\UrlUtils::encodeId($source->id)],
        ]);

        $this->assertSame(1, \App\Models\NewsletterUnsubscribe::where('role_id', $target->id)
            ->where('email', 'nope@fans.test')->count());
        $this->assertSame(0, \App\Models\NewsletterUnsubscribe::where('role_id', $source->id)->count());
    }

    public function test_a_backup_round_trip_keeps_subscribers_and_their_confirmation_state(): void
    {
        // An omitted table is silent, and the schedule loses its entire audience on restore.
        // confirmed_at matters as much as the row itself - a restore must not promote somebody who
        // never confirmed into a mailable recipient.
        //
        // Actually round-trips. An earlier version of this test asserted the export half only,
        // while calling itself a round trip.
        config(['app.hosted' => false]); // the PII gate strips subscribers from hosted exports

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->subscribe($role, 'yes@fans.test');
        $this->subscribe($role, 'pending@fans.test', confirmed: false);

        // Backdated, so the created_at assertion below cannot pass merely because both rows were
        // written in the same second.
        $subscribedOn = '2026-03-14 09:15:00';
        RoleSubscriber::where('role_id', $role->id)->where('email', 'yes@fans.test')
            ->update(['created_at' => $subscribedOn]);

        $svc = app(\App\Services\BackupService::class);

        $exportJob = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $this->assertCount(2, $data['schedules'][0]['role_subscribers'] ?? []);

        $importJob = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $rows = RoleSubscriber::where('role_id', $restored->id)->get()->keyBy('email');

        $this->assertCount(2, $rows, 'a restore must not lose the schedule audience');
        $this->assertNotNull($rows['yes@fans.test']->confirmed_at);
        $this->assertNull($rows['pending@fans.test']->confirmed_at,
            'a restore must not promote an unconfirmed address into a mailable one');

        // Fresh tokens: the exported ones may still be live in somebody's inbox pointing at the
        // original schedule.
        $this->assertNotSame(
            RoleSubscriber::where('role_id', $role->id)->where('email', 'yes@fans.test')->value('token'),
            $rows['yes@fans.test']->token
        );

        // The signup DATE survives too. The export has always carried created_at, but it is not
        // fillable, so the importer's create() dropped it and every restored subscriber came back
        // dated the restore - which is the column the followers tab renders as "Date".
        $this->assertSame(
            $subscribedOn,
            $rows['yes@fans.test']->created_at->toDateTimeString(),
            'a restore must keep the date the person actually subscribed',
        );
    }

    public function test_deleting_an_account_erases_its_subscriptions(): void
    {
        // No foreign key does this: role_subscribers is keyed on the address, not a user id. The
        // privacy policy calls account deletion final, total and irreversible.
        $role = $this->createRole($this->createOwner());
        $user = $this->createOwner();
        $this->subscribe($role, strtolower($user->email));

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password']);

        $this->assertSame(0, RoleSubscriber::where('email', strtolower($user->email))->count());
    }

    public function test_the_empty_state_still_renders_with_no_audience_at_all(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'followers']))
            ->assertOk()
            ->assertSee(__('messages.no_followers'));
    }

    public function test_the_empty_state_offers_a_copyable_follow_link(): void
    {
        // The page used to ask the organizer to "share your link" while offering only a QR code
        // DOWNLOAD and nowhere to copy the URL as text.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'followers']))
            ->assertOk()
            ->assertSee('subscribe=1', false)
            ->assertSee(__('messages.copy_link'));
    }

    public function test_the_qr_code_points_at_the_subscribe_form(): void
    {
        // Scanning it used to land a phone at the top of a forty-event calendar with the form
        // somewhere below the fold.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)
            ->get(route('role.qr_code', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_the_deep_link_scrolls_the_guest_page_to_the_form(): void
    {
        $role = $this->createRole($this->createOwner());

        $this->get($role->getGuestUrl().'?subscribe=1')
            ->assertOk()
            ->assertSee('subscribe-panel', false);
    }

    public function test_the_empty_state_gives_way_to_the_table_when_only_subscribers_exist(): void
    {
        // The pre-existing empty state keyed on $followers only. A schedule whose entire audience
        // is account-less would have been told it had nobody while holding a full list.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->subscribe($role, 'fan@fans.test');

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'followers']))
            ->assertOk()
            ->assertDontSee(__('messages.no_followers'))
            ->assertSee('fan@fans.test');
    }
}
