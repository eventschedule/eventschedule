<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The homepage finale asks for "your-name.eventschedule.com" and offers "Claim it free", and until
 * now the name was thrown away at the click: the link was a plain /sign_up.
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
}
