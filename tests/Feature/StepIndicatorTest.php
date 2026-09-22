<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The three-step onboarding indicator: Create Account, Create Schedule, Create Event.
 *
 * Its auto-detect decided step 2 versus step 3 with `$user->talents()->count() == 0` - and
 * talents() is editor() narrowed to type talent. So somebody who had just created a VENUE or a
 * CURATOR schedule was still shown "Create Schedule" as the step they were on, on the page for
 * the step after it. Only a talent organizer ever saw the truth.
 *
 * Every call site now passes an explicit step, so this pins the fallback rather than what any
 * particular page renders. It is still public API of the component, and a wrong default here is
 * silent: the indicator renders either way.
 */
class StepIndicatorTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** currentStep N renders N-1 completed circles, each carrying the checkmark path. */
    private function completedSteps(string $html): int
    {
        return substr_count($html, 'M16.707 5.293a1 1 0');
    }

    private function render(): string
    {
        return $this->blade('<x-step-indicator />');
    }

    public function test_a_venue_owner_is_past_the_create_schedule_step(): void
    {
        $user = $this->createOwner();
        $this->createRole($user, 'venue');

        $this->actingAs($user->fresh());

        $this->assertSame(2, $this->completedSteps($this->render()),
            'a venue owner has made a schedule, so steps 1 and 2 are behind them');
    }

    public function test_a_curator_owner_is_past_the_create_schedule_step(): void
    {
        $user = $this->createOwner();
        $this->createCurator($user);

        $this->actingAs($user->fresh());

        $this->assertSame(2, $this->completedSteps($this->render()));
    }

    public function test_a_talent_owner_is_past_it_too(): void
    {
        // The one case the old predicate got right. Kept so a fix that swings the other way,
        // and reports everyone as past step 2, cannot pass.
        $user = $this->createOwner();
        $this->createRole($user, 'talent');

        $this->actingAs($user->fresh());

        $this->assertSame(2, $this->completedSteps($this->render()));
    }

    public function test_someone_with_no_schedule_is_still_on_the_create_schedule_step(): void
    {
        $this->actingAs($this->createOwner());

        $this->assertSame(1, $this->completedSteps($this->render()),
            'only step 1 is behind them');
    }

    /**
     * Following somebody else's schedule is not making one.
     *
     * roles() is the obvious-looking fix for the talents() bug and is wrong the other way: it
     * includes follower pivots. member() is owner/admin/viewer, and is the same predicate
     * HomeController::gettingStarted() uses to decide whether this person still needs a schedule.
     */
    public function test_a_follower_has_not_created_a_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $follower = $this->createOwner();
        $this->followRole($follower, $role);

        $this->actingAs($follower->fresh());

        $this->assertSame(1, $this->completedSteps($this->render()));
    }

    public function test_an_unverified_account_is_on_step_one(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['email_verified_at' => null])->save();

        $this->actingAs($user->fresh());

        $this->assertSame(0, $this->completedSteps($this->render()));
    }

    /**
     * :compact="true" was passed at three call sites and silently discarded - it is not a declared
     * prop and the root element does not spread $attributes. There is no compact variant, so the
     * argument was decoration that read like configuration.
     */
    public function test_no_call_site_passes_a_prop_the_component_does_not_declare(): void
    {
        $views = [
            'resources/views/getting-started.blade.php',
            'resources/views/role/edit.blade.php',
            'resources/views/event/edit.blade.php',
            'resources/views/auth/verify-email.blade.php',
        ];

        foreach ($views as $view) {
            $this->assertStringNotContainsString(':compact', file_get_contents(base_path($view)), $view);
        }
    }
}
