<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class SponsorsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array<int, array<string, string|null>> */
    private function sponsorData(int $count): array
    {
        $sponsors = [];

        for ($i = 1; $i <= $count; $i++) {
            $sponsors[] = [
                'name' => 'Partner '.$i,
                // demo_ logos resolve to /images/demo/ without touching storage.
                'logo' => 'demo_sponsor_'.$i.'.jpg',
                'url' => 'https://partner'.$i.'.test',
                'tier' => 'gold',
            ];
        }

        return $sponsors;
    }

    private function createSponsoredRole(User $owner, int $count, array $attrs = []): Role
    {
        return $this->createRole($owner, 'curator', array_merge([
            'name' => 'Sponsored Curator',
            'sponsor_logos' => json_encode($this->sponsorData($count)),
        ], $attrs));
    }

    private function updateRole(User $owner, Role $role, array $payload)
    {
        return $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
            'name' => $role->name,
            'timezone' => $role->timezone,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
        ], $payload));
    }

    public function test_guest_page_renders_sponsors_hidden_until_every_logo_has_loaded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 3);

        $response = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]));

        $response->assertOk();
        $response->assertSee('data-sponsor-grid', false);
        // The panel ships hidden and is revealed by JS once the logos are in.
        $response->assertSee('es-sponsors-pending', false);
        $response->assertSee('/images/demo/demo_sponsor_1.jpg', false);
        // Above-the-fold logos must not be lazy, or they trickle in one at a time.
        $response->assertSee('loading="eager"', false);
        // No-JS visitors still see the panel.
        $response->assertSee('<noscript><style>[data-sponsor-grid].es-sponsors-pending', false);
    }

    public function test_sponsor_grid_gets_denser_columns_past_the_original_twelve(): void
    {
        $owner = $this->createOwner();

        $sparse = $this->createSponsoredRole($owner, 4);
        $this->get(route('role.view_guest', ['subdomain' => $sparse->subdomain]))
            ->assertOk()
            ->assertSee('grid-cols-2 sm:grid-cols-3 md:grid-cols-4', false);

        $dense = $this->createSponsoredRole($owner, 20);
        $this->get(route('role.view_guest', ['subdomain' => $dense->subdomain]))
            ->assertOk()
            ->assertSee('grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6', false);
    }

    public function test_default_background_keeps_the_translucent_panel(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 2);

        $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee('bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm', false);
    }

    public function test_transparent_background_drops_the_panel(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 2, ['sponsor_background_color' => 'transparent']);

        $response = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]));

        $response->assertOk();
        $response->assertSee('data-sponsor-grid', false);
        $response->assertDontSee('w-full bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl', false);
    }

    public function test_custom_background_applies_the_color_and_a_readable_text_color(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 2, ['sponsor_background_color' => '#102030']);

        $response = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]));

        $response->assertOk();
        $response->assertSee('background-color: #102030', false);
        // Dark panel, so the title/name text flips to white.
        $response->assertSee('color: #ffffff', false);
    }

    public function test_malformed_background_falls_back_to_the_default_panel(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 2, ['sponsor_background_color' => 'javascript:alert(1)']);

        $response = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]));

        $response->assertOk();
        $response->assertDontSee('javascript:alert(1)', false);
        $response->assertSee('bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm', false);
    }

    public function test_sponsors_are_capped_server_side(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 1);
        $max = config('app.max_sponsors');

        $this->updateRole($owner, $role, [
            'existing_sponsors' => json_encode($this->sponsorData($max + 10)),
        ])->assertRedirect();

        $this->assertCount($max, json_decode($role->fresh()->sponsor_logos, true));
    }

    public function test_sponsors_below_the_cap_are_saved_in_full(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 1);

        $this->updateRole($owner, $role, [
            'existing_sponsors' => json_encode($this->sponsorData(30)),
        ])->assertRedirect();

        $this->assertCount(30, json_decode($role->fresh()->sponsor_logos, true));
    }

    public function test_background_color_is_saved_and_can_be_cleared(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 2);

        $this->updateRole($owner, $role, ['sponsor_background_color' => '#abcdef'])->assertRedirect();
        $this->assertSame('#abcdef', $role->fresh()->sponsor_background_color);

        $this->updateRole($owner, $role, ['sponsor_background_color' => 'transparent'])->assertRedirect();
        $this->assertSame('transparent', $role->fresh()->sponsor_background_color);

        // The "Default" option submits an empty string; it must land as null, not ''.
        $this->updateRole($owner, $role, ['sponsor_background_color' => ''])->assertRedirect();
        $this->assertNull($role->fresh()->sponsor_background_color);
    }

    public function test_invalid_background_color_is_rejected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createSponsoredRole($owner, 2, ['sponsor_background_color' => '#abcdef']);

        $this->updateRole($owner, $role, ['sponsor_background_color' => 'red; content: url(x)'])
            ->assertSessionHasErrors('sponsor_background_color');

        $this->assertSame('#abcdef', $role->fresh()->sponsor_background_color);
    }

    public function test_edit_page_shows_the_limit_using_the_configured_maximum(): void
    {
        $owner = $this->createOwner();
        $max = config('app.max_sponsors');
        $role = $this->createSponsoredRole($owner, $max);

        $response = $this->actingAs($owner)->get('/'.$role->subdomain.'/edit');

        $response->assertOk();
        $response->assertSee(__('messages.max_sponsors_reached', ['count' => $max]));
        $response->assertSee('id="sponsor_background_mode"', false);
    }
}
