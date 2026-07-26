<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\FederationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The nudge that gets federation discovered at all: it is off by default and buried in
 * /admin/settings, so without a prompt no existing install would ever find it.
 */
class FederationPromptTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Federation is an instance-side feature; the suite runs as the nexus.
        config(['app.is_nexus' => false]);
        // The "is there anything to share?" answer is cached, so clear it between cases.
        Cache::flush();
    }

    private function service(): FederationService
    {
        return app(FederationService::class);
    }

    /** An admin with one event that would actually be shared. */
    private function adminWithShareableEvent(): array
    {
        $admin = $this->createOwner(true);
        $role = $this->createRole($admin, 'venue');
        $event = $this->createEvent($role, [
            'name' => 'A Real Show',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ]);

        return [$admin, $role, $event];
    }

    public function test_it_shows_for_an_admin_once_there_is_something_to_share(): void
    {
        [$admin] = $this->adminWithShareableEvent();

        $this->assertTrue($this->service()->shouldPromptAdoption($admin));
    }

    /**
     * The trigger is "the first event exists" - prompting an empty install to join a
     * listings network is meaningless.
     */
    public function test_it_is_absent_before_any_event_exists(): void
    {
        $admin = $this->createOwner(true);
        $this->createRole($admin, 'venue');

        $this->assertFalse($this->service()->shouldPromptAdoption($admin));
    }

    public function test_it_is_absent_for_a_non_admin(): void
    {
        [$admin, $role] = $this->adminWithShareableEvent();
        $plain = $this->createOwner();

        // The event exists, so the only thing separating them is permission.
        $this->assertTrue($this->service()->shouldPromptAdoption($admin));
        $this->assertFalse($this->service()->shouldPromptAdoption($plain));
    }

    public function test_it_is_absent_on_the_nexus(): void
    {
        [$admin] = $this->adminWithShareableEvent();
        config(['app.is_nexus' => true]);

        $this->assertFalse($this->service()->shouldPromptAdoption($admin));
    }

    public function test_it_is_absent_once_federation_is_already_enabled(): void
    {
        [$admin] = $this->adminWithShareableEvent();
        Setting::set('federation_enabled', '1');

        $this->assertFalse($this->service()->shouldPromptAdoption($admin));
    }

    /**
     * Someone who enabled federation and then deliberately turned it off must not be
     * nagged to re-enable it. The toggle alone cannot tell that story.
     */
    public function test_it_is_absent_for_an_install_that_already_tried_federation(): void
    {
        [$admin] = $this->adminWithShareableEvent();

        // Enabled at some point (register() writes this), then switched back off.
        Setting::set('federation_instance_id', 'a-previous-identity');
        Setting::set('federation_enabled', null);

        $this->assertFalse($this->service()->shouldPromptAdoption($admin));
    }

    public function test_dismissing_hides_it_permanently_for_that_user(): void
    {
        [$admin] = $this->adminWithShareableEvent();
        $this->actingAs($admin);

        $this->post(route('home.federation_prompt_dismiss'))->assertRedirect();

        $this->assertTrue($admin->fresh()->federation_prompt_dismissed);
        $this->assertFalse($this->service()->shouldPromptAdoption($admin->fresh()));
    }

    /** Dismissal is per user: one admin saying no does not decide for another. */
    public function test_dismissal_does_not_hide_it_for_a_different_admin(): void
    {
        [$admin] = $this->adminWithShareableEvent();
        $other = $this->createOwner(true);

        $admin->federation_prompt_dismissed = true;
        $admin->saveQuietly();

        $this->assertFalse($this->service()->shouldPromptAdoption($admin->fresh()));
        $this->assertTrue($this->service()->shouldPromptAdoption($other));
    }

    public function test_it_renders_on_the_dashboard(): void
    {
        [$admin] = $this->adminWithShareableEvent();

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee(__('messages.federation_prompt_title'));
    }

    /**
     * The page you actually land on after creating an event
     * (EventController redirects to role.view_admin, not the dashboard).
     */
    public function test_it_renders_on_the_schedule_page(): void
    {
        [$admin, $role] = $this->adminWithShareableEvent();

        $this->actingAs($admin)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertSee(__('messages.federation_prompt_title'));
    }

    /**
     * The docs pages only exist on the nexus. marketing_url() is what a white-labeled
     * operator points at their own site, so building the link from it would 404 for
     * exactly the installs this banner targets.
     */
    public function test_the_learn_more_link_points_at_the_nexus_not_the_marketing_site(): void
    {
        [$admin] = $this->adminWithShareableEvent();

        config([
            'app.marketing_url' => 'https://white-labeled-operator.test',
            'app.nexus_url' => 'https://eventschedule.com',
        ]);

        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('https://eventschedule.com/docs/selfhost/federation', false)
            ->assertDontSee('https://white-labeled-operator.test/docs', false);
    }

    public function test_dismissing_from_one_page_hides_it_on_both(): void
    {
        [$admin, $role] = $this->adminWithShareableEvent();
        $this->actingAs($admin);

        $this->post(route('home.federation_prompt_dismiss'));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(__('messages.federation_prompt_title'));

        $this->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertDontSee(__('messages.federation_prompt_title'));
    }
}
