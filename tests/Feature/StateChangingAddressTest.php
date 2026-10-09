<?php

namespace Tests\Feature;

use App\Http\Controllers\AppController;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An address that changes something is never one a browser can simply open.
 *
 * A page can make a visitor's browser load any address (an image is enough), and on the hosted
 * install every schedule's page shares the visitor's session with the app. Opening
 * /{schedule}/unfollow used to take an OWNER off their own schedule, and could mark it deleted;
 * opening /follow made the visitor a follower, which shows their name and email to the schedule,
 * with the consent step only in the page.
 *
 *   - follow, unfollow, the calendar disconnects and clear-videos are posted with the page's token;
 *   - unfollowing detaches a follower and nobody else: an owner or a team member leaves from the
 *     Team tab;
 *   - a follow begun before signing up is finished inside the dashboard request, not by sending
 *     the browser to the follow address.
 *
 * The setup wizard's database test is here for the same reason: open to every signed-in account
 * after setup, and outside the CSRF check, it had the server connect to whatever host was named.
 */
class StateChangingAddressTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_an_owner_is_not_taken_off_their_schedule_by_the_unfollow_address(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['email' => null]);

        $this->actingAs($owner)->get('/'.$role->subdomain.'/unfollow');

        $this->assertTrue($owner->fresh()->isEditor($role->subdomain));
        $this->assertFalse((bool) $role->fresh()->is_deleted);
    }

    public function test_opening_an_address_does_not_unfollow(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $visitor = $this->createOwner();
        $this->followRole($visitor, $role);

        $this->actingAs($visitor)->get('/'.$role->subdomain.'/unfollow');

        $this->assertTrue($visitor->fresh()->isFollowing($role->subdomain));
    }

    public function test_opening_an_address_does_not_follow(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $visitor = $this->createOwner();

        $this->actingAs($visitor)->get(route('role.follow', ['subdomain' => $role->subdomain]));

        $this->assertFalse($visitor->fresh()->isConnected($role->subdomain));
    }

    public function test_the_follow_and_unfollow_buttons_still_work_as_forms(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $visitor = $this->createOwner();

        $this->actingAs($visitor)->post(route('role.follow', ['subdomain' => $role->subdomain]))->assertRedirect();
        $this->assertTrue($visitor->fresh()->isFollowing($role->subdomain));

        $this->actingAs($visitor)->post(route('role.unfollow', ['subdomain' => $role->subdomain]))->assertRedirect();
        $this->assertFalse($visitor->fresh()->isConnected($role->subdomain));
    }

    public function test_an_owner_is_not_taken_off_by_the_unfollow_form_or_the_bulk_one(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['email' => null]);

        $this->actingAs($owner)->post(route('role.unfollow', ['subdomain' => $role->subdomain]));
        $this->actingAs($owner)->post(route('following.bulk-unfollow'), ['subdomains' => json_encode([$role->subdomain])]);

        $this->assertTrue($owner->fresh()->isEditor($role->subdomain));
        $this->assertFalse((bool) $role->fresh()->is_deleted);
    }

    public function test_a_follow_begun_before_signing_up_is_finished_from_the_dashboard(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $visitor = $this->createOwner();

        $this->actingAs($visitor)->withSession(['pending_follow' => $role->subdomain])->get(route('home'))->assertRedirect();

        $this->assertTrue($visitor->fresh()->isFollowing($role->subdomain));
    }

    public function test_the_other_addresses_that_change_something_are_not_opened_either(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue');
        $event = $this->createEvent($role);

        // No route answers a GET there any more: the address is an unknown page, or refused.
        foreach (['/google-calendar/disconnect', '/microsoft-calendar/disconnect', '/'.$role->subdomain.'/clear-videos/'.UrlUtils::encodeId($event->id).'/'.UrlUtils::encodeId($role->id)] as $address) {
            $this->assertContains($this->actingAs($user)->get($address)->status(), [404, 405], $address);
        }
        $this->actingAs($user)->post('/microsoft-calendar/disconnect')->assertRedirect();
    }

    public function test_the_database_test_is_closed_once_the_install_has_accounts(): void
    {
        config(['app.hosted' => false]);
        $this->actingAs($this->createOwner());

        $response = app(AppController::class)->testDatabase(Request::create('/test_database', 'POST', [
            'host' => '127.0.0.1', 'port' => 1, 'database' => 'x', 'username' => 'x', 'password' => 'x',
        ]));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_the_wizard_still_asks_on_an_install_with_no_account(): void
    {
        config(['app.hosted' => false]);
        $this->assertSame(0, User::count());

        $response = app(AppController::class)->testDatabase(Request::create('/test_database', 'POST', [
            'host' => '127.0.0.1', 'port' => 1, 'database' => 'x', 'username' => 'x', 'password' => 'x',
        ]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
    }
}
