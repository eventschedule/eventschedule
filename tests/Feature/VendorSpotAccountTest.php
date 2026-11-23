<?php

namespace Tests\Feature;

use App\Http\Controllers\VendorSpotController;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use App\Notifications\SignupVerificationCode;
use App\Services\Blocklist;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The vendor link's account step (GuestAccountService::sendSignInCode() / signInWithCode(), and
 * Google through HomeController::home()): an emailed code and no password, built as a sign-in
 * door with everything such a door owes (CLAUDE.md): two-factor sends the person to the sign-in
 * page, wrong codes count against the account, the operator's list is asked where an account is
 * made, a waiting account is not handed over off hosted, and the public step has a honeypot.
 */
class VendorSpotAccountTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $market;

    private VendorMap $map;

    private Event $event;

    private VendorMapSpot $spot;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => false]);
        Notification::fake();

        $this->market = $this->createRole($this->createOwner(), 'venue', ['name' => 'Riverside Farmers Market', 'language_code' => 'he', 'timezone' => 'Asia/Jerusalem']);
        $this->event = $this->createRecurringEvent($this->market, [
            'days_of_week' => '0000010',
            'starts_at' => Carbon::now('UTC')->next(Carbon::FRIDAY)->setTime(6, 0)->format('Y-m-d H:i:s'),
            'creator_role_id' => $this->market->id,
        ]);
        $this->map = VendorMap::create(['role_id' => $this->market->id, 'kind' => 'market', 'event_id' => $this->event->id]);
        $this->map->ensureClaimToken();
        $this->spot = VendorMapSpot::create(['vendor_map_id' => $this->map->id, 'label' => '14', 'x' => 10, 'y' => 10, 'w' => 60, 'h' => 40]);
    }

    private function url(): string
    {
        return route('vendor_map.claim.submit', ['subdomain' => $this->market->subdomain, 'token' => $this->map->claim_token]);
    }

    private function details(array $over = []): array
    {
        return $over + [
            'spot' => $this->spot->id,
            'name' => "Avi's Vegetables",
            'categories' => ['produce', 'eggs_dairy'],
            'sells' => 'Tomatoes and greens',
            'every' => true,
            'website' => '',
        ];
    }

    private function sendCode(string $email, array $over = [])
    {
        return $this->postJson($this->url(), $this->details($over + ['action' => 'send_code', 'email' => $email]));
    }

    private function codeFor(string $email): ?string
    {
        return Cache::get('guest_code:'.hash('sha256', strtolower($email)));
    }

    public function test_a_new_vendor_asks_for_a_stall_with_an_emailed_code_and_no_password(): void
    {
        $email = 'avi.vegetables@gmail.com';

        $this->sendCode($email)->assertOk()->assertJsonPath('sent', true);
        Notification::assertSentOnDemand(SignupVerificationCode::class);
        $this->assertGuest();
        $this->assertSame(0, VendorMapOccupant::count(), 'nothing is asked for before the code');

        $code = $this->codeFor($email);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $this->postJson($this->url(), ['action' => 'verify_code', 'code' => $code])
            ->assertOk()
            ->assertJsonPath('waiting.spotId', $this->spot->id)
            ->assertJsonPath('signedIn', true);

        $user = User::where('email', $email)->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->password, 'no password: one can be set later');
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('vendor', $user->signup_intent);

        $page = $user->talents()->sole();
        $this->assertSame("Avi's Vegetables", $page->name);
        $this->assertSame('he', $page->language_code, "the market's language");
        $this->assertSame('Asia/Jerusalem', $page->timezone);

        $row = VendorMapOccupant::sole();
        $this->assertSame('pending', $row->status);
        $this->assertSame($page->id, $row->vendor_role_id);
        $this->assertSame(['produce', 'eggs_dairy'], $row->categories);

        // The code is spent.
        $this->assertNull($this->codeFor($email));
    }

    public function test_a_stall_taken_while_the_code_was_on_its_way_signs_the_vendor_in_and_says_so(): void
    {
        $email = 'late.vendor@gmail.com';
        $this->sendCode($email)->assertOk();

        // Another vendor asks for the same stall before this one types the code.
        VendorMapOccupant::create([
            'vendor_map_id' => $this->map->id, 'spot_id' => $this->spot->id, 'event_date' => VendorMapOccupant::SEASON,
            'kind' => 'present', 'status' => 'pending', 'placeholder_name' => 'Somebody quicker', 'categories' => ['bread'],
        ]);

        $this->postJson($this->url(), ['action' => 'verify_code', 'code' => $this->codeFor($email)])
            ->assertOk()
            ->assertJsonPath('signedIn', true)
            ->assertJsonPath('back', 'list')
            ->assertJsonStructure(['error']);

        $this->assertAuthenticatedAs(User::where('email', $email)->sole());
        $this->assertSame(1, VendorMapOccupant::count(), 'no second request for the taken stall');
    }

    public function test_an_existing_account_signs_in_with_the_code(): void
    {
        $user = $this->createOwner();
        $page = $this->createRole($user, 'talent', ['name' => "Avi's Vegetables"]);

        $this->sendCode($user->email)->assertOk();
        $this->postJson($this->url(), ['action' => 'verify_code', 'code' => $this->codeFor($user->email)])->assertOk();

        $this->assertAuthenticatedAs($user);
        // Their page of the same name is used, not a second one.
        $this->assertSame($page->id, VendorMapOccupant::sole()->vendor_role_id);
        $this->assertSame(1, $user->talents()->count());
    }

    public function test_the_honeypot_stops_the_account_step(): void
    {
        $this->sendCode('bot@gmail.com', ['website' => 'http://spam.test'])->assertStatus(422);
        Notification::assertNothingSent();

        $this->postJson($this->url(), $this->details(['action' => 'google', 'website' => 'http://spam.test']))->assertStatus(422);
        $this->assertNull(session(VendorSpotController::PENDING));
    }

    public function test_an_account_with_two_factor_is_sent_to_the_sign_in_page(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['two_factor_secret' => 'secret', 'two_factor_confirmed_at' => now()])->save();

        $this->sendCode($user->email)->assertStatus(422)->assertJsonValidationErrors('email');
        Notification::assertNothingSent();

        // Two-factor turned on between the two steps is caught at the second.
        $other = $this->createOwner();
        $this->sendCode($other->email)->assertOk();
        $code = $this->codeFor($other->email);
        $other->forceFill(['two_factor_secret' => 'secret', 'two_factor_confirmed_at' => now()])->save();
        $this->postJson($this->url(), ['action' => 'verify_code', 'code' => $code])->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertGuest();
        $this->assertSame(0, VendorMapOccupant::count());
    }

    public function test_wrong_codes_count_against_the_account(): void
    {
        $user = $this->createOwner();
        $this->sendCode($user->email)->assertOk();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson($this->url(), ['action' => 'verify_code', 'code' => $this->codeFor($user->email) === '000000' ? '111111' : '000000'])
                ->assertStatus(422)->assertJsonValidationErrors('code');
        }

        // The fifth wrong guess spent the code; a new one does not renew the allowance.
        $this->assertNull($this->codeFor($user->email));
        $this->sendCode($user->email)->assertOk();
        $this->postJson($this->url(), ['action' => 'verify_code', 'code' => $this->codeFor($user->email)])
            ->assertStatus(422)->assertJsonValidationErrors('code');

        $this->assertGuest();
        $this->assertSame(0, VendorMapOccupant::count());
    }

    public function test_a_blocked_account_or_a_listed_address_is_refused(): void
    {
        $blocked = $this->createOwner();
        $blocked->forceFill(['blocked_at' => now()])->save();
        $this->sendCode($blocked->email)->assertStatus(422)->assertJsonValidationErrors('email');

        Blocklist::add(Blocklist::EMAIL, 'listed@gmail.com');
        $this->sendCode('listed@gmail.com')->assertStatus(422)->assertJsonValidationErrors('email');

        Notification::assertNothingSent();
    }

    public function test_off_hosted_a_waiting_account_is_mailed_its_set_password_link_instead(): void
    {
        $stub = User::factory()->create(['email' => 'invited@gmail.com', 'password' => null]);
        $this->assertTrue($stub->isStub());

        $this->sendCode($stub->email)->assertStatus(422)->assertJsonValidationErrors('email');
        Notification::assertNotSentTo($stub, SignupVerificationCode::class);
        $this->assertNull($this->codeFor($stub->email));
    }

    public function test_google_holds_the_request_and_home_asks_for_it_after_sign_in(): void
    {
        config(['services.google.client_id' => 'test-client', 'app.allow_registration' => true]);

        $this->postJson($this->url(), $this->details(['action' => 'google']))
            ->assertOk()
            ->assertJsonPath('redirect', fn ($url) => str_contains($url, '/auth/google'));

        $pending = session(VendorSpotController::PENDING);
        $this->assertIsArray($pending);
        $this->assertSame(0, VendorMapOccupant::count(), 'nothing is asked for before the sign-in');

        // What the Google callback does for a new account, then where it sends the browser.
        $user = User::factory()->create(['email_verified_at' => now(), 'google_id' => 'g-1']);
        $this->assertSame(route('home', absolute: false), post_signup_redirect_url($user));

        $this->actingAs($user)
            ->withSession([VendorSpotController::PENDING => $pending])
            ->get(route('home'))
            ->assertRedirect(route('vendor_map.claim', ['subdomain' => $this->market->subdomain, 'token' => $this->map->claim_token]));

        $row = VendorMapOccupant::sole();
        $this->assertSame('pending', $row->status);
        $this->assertSame($user->id, $row->user_id);
        $this->assertSame('he', Role::find($row->vendor_role_id)->language_code);
        $this->assertNull(session(VendorSpotController::PENDING));

        // The link then shows the Done screen.
        $this->get(route('vendor_map.claim', ['subdomain' => $this->market->subdomain, 'token' => $this->map->claim_token]))
            ->assertOk()
            ->assertSee('"justSent":true', false);
    }

    public function test_a_held_request_on_a_link_closed_meanwhile_is_not_made(): void
    {
        config(['services.google.client_id' => 'test-client', 'app.allow_registration' => true]);
        $this->postJson($this->url(), $this->details(['action' => 'google']))->assertOk();
        $pending = session(VendorSpotController::PENDING);

        $this->map->rotateClaimToken();

        $user = User::factory()->create(['email_verified_at' => now(), 'google_id' => 'g-2']);
        $this->actingAs($user)->withSession([VendorSpotController::PENDING => $pending])->get(route('home'))->assertRedirect();

        $this->assertSame(0, VendorMapOccupant::count());
        $this->assertSame(0, $user->talents()->count());
    }
}
