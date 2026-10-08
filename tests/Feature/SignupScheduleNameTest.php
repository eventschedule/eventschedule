<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The homepage asks for "your-name.eventschedule.com" and offers "Claim it free" (beside its
 * headline and again in its finale since the 2026-10 redesign; a name typed in either box is the
 * name in both, and rides every other sign-up link on the page), and until 2026-09 the name was
 * thrown away at the click: the link was a plain /sign_up.
 *
 * initClaim() in resources/js/marketing-home.js now adds ?schedule=<slug> to that link,
 * RegisteredUserController::create() keeps it in the session (so it survives the Google round
 * trip, like signup_role_type), and RoleController::create() starts the first schedule with it.
 */
class SignupScheduleNameTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_sign_up_page_keeps_the_claimed_name(): void
    {
        $this->get(route('sign_up', ['schedule' => 'blue-room']))->assertOk();

        $this->assertSame('blue-room', session('signup_schedule_name'));
    }

    /** Only the shape the claim box produces; anything else would end up in a form value. */
    public function test_anything_but_a_claim_box_slug_is_ignored(): void
    {
        foreach (['Blue-Room', '<script>', 'blue room', '-blue', 'blue-', str_repeat('a', 31), ''] as $value) {
            $this->flushSession();

            $this->get(route('sign_up', ['schedule' => $value]))->assertOk();

            $this->assertNull(session('signup_schedule_name'), var_export($value, true).' was kept');
        }

        $this->flushSession();
        $this->get(route('sign_up').'?schedule[]=blue')->assertOk();
        $this->assertNull(session('signup_schedule_name'), 'an array was kept');
    }

    public function test_the_longest_claim_box_slug_is_kept(): void
    {
        $slug = str_repeat('a', 30);

        $this->get(route('sign_up', ['schedule' => $slug]))->assertOk();

        $this->assertSame($slug, session('signup_schedule_name'));
    }

    public function test_a_first_schedule_starts_with_the_claimed_name(): void
    {
        $user = $this->createOwner();

        $this->actingAs($user)
            ->withSession(['signup_schedule_name' => 'blue-room'])
            ->get(route('new', ['type' => 'venue']))
            ->assertOk()
            ->assertSee('value="Blue Room"', false);

        // Read, not pulled: going back to the chooser and picking another type keeps it.
        $this->assertSame('blue-room', session('signup_schedule_name'));
    }

    /** It wins over the talent default (the user's own name): it is what they asked for. */
    public function test_the_claimed_name_wins_over_the_talent_default(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['name' => 'Jane Person'])->save();

        $this->actingAs($user)
            ->withSession(['signup_schedule_name' => 'jane-sings'])
            ->get(route('new', ['type' => 'talent']))
            ->assertOk()
            ->assertSee('value="Jane Sings"', false)
            ->assertDontSee('value="Jane Person"', false);
    }

    /** A stale claim must not name a SECOND schedule, and is discarded once one exists. */
    public function test_a_user_who_already_owns_a_schedule_gets_no_prefill(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $this->actingAs($user)
            ->withSession(['signup_schedule_name' => 'blue-room'])
            ->get(route('new', ['type' => 'venue']))
            ->assertOk()
            ->assertDontSee('value="Blue Room"', false);

        $this->assertNull(session('signup_schedule_name'));
    }

    /**
     * The round trip: the schedule saved from that form gets the address the visitor typed.
     *
     * Not what generateSubdomain() alone gives: it hands out the shortest free prefix, so
     * "Blue Room" would be "blue" - a different address from the one the box promised.
     */
    public function test_the_saved_schedule_gets_the_claimed_subdomain(): void
    {
        $this->assertSame('blue', Role::generateSubdomain('Blue Room'), 'the fixture no longer shows the prefix rule');

        $this->actingAs($this->createOwner())->withSession(['signup_schedule_name' => 'blue-room']);

        $role = $this->submitNewScheduleForm('venue');

        $this->assertSame('Blue Room', $role->name);
        $this->assertSame('blue-room', $role->subdomain);
    }

    /**
     * create() can only guess the casing and punctuation ("Dj Mc"), so fixing it is the likeliest
     * edit anyone makes - and it must not cost them the address. An exact match against the
     * prefill dropped the claim here and handed out "dj".
     */
    public function test_fixing_the_prefilled_casing_keeps_the_claimed_subdomain(): void
    {
        $this->actingAs($this->createOwner())->withSession(['signup_schedule_name' => 'dj-mc']);

        $role = $this->submitNewScheduleForm('talent', ['name' => 'DJ MC']);

        $this->assertSame('DJ MC', $role->name);
        $this->assertSame('dj-mc', $role->subdomain);
    }

    /** Punctuation the claim box stripped, put back: still the same address. */
    public function test_restoring_punctuation_keeps_the_claimed_subdomain(): void
    {
        $this->actingAs($this->createOwner())->withSession(['signup_schedule_name' => 'oreillys-bar']);

        $role = $this->submitNewScheduleForm('venue', ['name' => "O'Reilly's Bar"]);

        $this->assertSame('oreillys-bar', $role->subdomain);
    }

    /**
     * Reaching /login means this is not the claim-then-register flow, so an abandoned claim must
     * not name a later account's first schedule. Same rule as signup_role_type.
     */
    public function test_the_login_page_forgets_an_abandoned_claim(): void
    {
        $this->withSession(['signup_schedule_name' => 'blue-room'])
            ->get(route('login'))
            ->assertOk();

        $this->assertNull(session('signup_schedule_name'));
    }

    /** Renaming it in the form is a new choice: the claim no longer applies. */
    public function test_a_renamed_schedule_gets_a_subdomain_from_its_new_name(): void
    {
        $this->actingAs($this->createOwner())->withSession(['signup_schedule_name' => 'blue-room']);

        $role = $this->submitNewScheduleForm('venue', ['name' => 'Green Hall']);

        $this->assertSame('green', $role->subdomain);
    }

    /** A claim someone else already holds falls back to the usual generated address. */
    public function test_a_taken_claim_falls_back_to_a_generated_subdomain(): void
    {
        $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'blue-room']);

        $this->actingAs($this->createOwner())->withSession(['signup_schedule_name' => 'blue-room']);

        $role = $this->submitNewScheduleForm('venue');

        $this->assertSame('Blue Room', $role->name);
        $this->assertSame('blue', $role->subdomain);
    }

    /** Too short or reserved: what cleanSubdomain() would never hand out, the claim cannot either. */
    public function test_a_claim_clean_subdomain_would_refuse_is_not_used(): void
    {
        foreach (['ab', 'admin'] as $claim) {
            $this->actingAs($this->createOwner())->withSession(['signup_schedule_name' => $claim]);

            $role = $this->submitNewScheduleForm('venue');

            $this->assertNotSame($claim, $role->subdomain, $claim.' was handed out');
        }
    }

    //
    // Kept on the account (users.pending_schedule_type / pending_schedule_name), so the onboarding
    // email - or a visit after /login has cleared the session - can send them back to the form
    // they left rather than to the type chooser.
    //

    public function test_signing_up_keeps_the_type_and_claim_on_the_account(): void
    {
        $this->withSession(['signup_role_type' => 'venue', 'signup_schedule_name' => 'blue-room'])
            ->post('/sign_up', [
                'terms' => '1',
                'name' => 'Claimant',
                'email' => 'claimant@gmail.com',
                'password' => 'password',
            ])->assertSessionHasNoErrors();

        $user = User::whereEmail('claimant@gmail.com')->firstOrFail();
        $this->assertSame('venue', $user->pending_schedule_type);
        $this->assertSame('blue-room', $user->pending_schedule_name);
    }

    /** About half of all accounts are created through Google, which has its own create(). */
    public function test_signing_up_with_google_keeps_them_too(): void
    {
        $socialUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-oauth-claim');
        $socialUser->shouldReceive('getEmail')->andReturn('googleclaim@gmail.com');
        $socialUser->shouldReceive('getName')->andReturn('Google Claimant');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->user = ['locale' => 'en'];

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->withSession(['signup_role_type' => 'talent', 'signup_schedule_name' => 'jane-sings'])
            ->get(route('auth.google.callback'));

        $user = User::whereEmail('googleclaim@gmail.com')->firstOrFail();
        $this->assertSame('talent', $user->pending_schedule_type);
        $this->assertSame('jane-sings', $user->pending_schedule_name);
    }

    /** Picking a type in the chooser is the most common way to reach the form, with no ?type= at signup. */
    public function test_opening_the_form_records_the_type_on_the_account(): void
    {
        $user = $this->createOwner();

        $this->actingAs($user)->get(route('new', ['type' => 'curator']))->assertOk();

        $this->assertSame('curator', $user->fresh()->pending_schedule_type);
    }

    /** The claim from signup outlives the session: the prefill no longer depends on it. */
    public function test_the_claim_kept_on_the_account_prefills_a_later_visit(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['pending_schedule_name' => 'blue-room'])->save();

        $this->actingAs($user)
            ->get(route('new', ['type' => 'talent']))
            ->assertOk()
            ->assertSee('value="Blue Room"', false);

        $this->assertSame('blue-room', $user->fresh()->pending_schedule_name, 'a visit with no session claim keeps it');
        $this->assertSame('talent', $user->fresh()->pending_schedule_type, 'the latest pick wins');
    }

    public function test_the_claim_kept_on_the_account_gives_the_first_schedule_its_address(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['pending_schedule_name' => 'blue-room'])->save();

        $this->actingAs($user);
        $role = $this->submitNewScheduleForm('venue');

        $this->assertSame('blue-room', $role->subdomain);

        // Done with: the schedule they were setting up exists now.
        $user->refresh();
        $this->assertNull($user->pending_schedule_name);
        $this->assertNull($user->pending_schedule_type);
    }

    /**
     * store() has no first-schedule guard for the session copy because create() drops that one for
     * an existing owner. The account copy needs its own, or a second schedule that shares the name
     * inherits the address.
     */
    public function test_a_second_schedule_never_inherits_the_kept_claim(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);
        $user->forceFill(['pending_schedule_name' => 'blue-room'])->save();

        $this->actingAs($user);
        $role = $this->submitNewScheduleForm('venue', ['name' => 'Blue Room']);

        $this->assertNotSame('blue-room', $role->subdomain);
    }

    //
    // /getting-started?type= is the onboarding email's button for someone who had picked a type.
    //

    public function test_getting_started_forwards_a_picked_type_to_its_form(): void
    {
        $this->actingAs($this->createOwner())
            ->get(route('getting-started', ['type' => 'venue']))
            ->assertRedirect(route('new', ['type' => 'venue']));
    }

    public function test_getting_started_ignores_a_type_that_does_not_exist(): void
    {
        $this->actingAs($this->createOwner())
            ->get(route('getting-started', ['type' => 'vendor']))
            ->assertOk();
    }

    /** A days-old email opened after they set up a schedule must not offer a second one. */
    public function test_getting_started_still_sends_an_owner_home_with_a_type(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $this->actingAs($user)
            ->get(route('getting-started', ['type' => 'venue']))
            ->assertRedirect(route('home'));
    }
}
