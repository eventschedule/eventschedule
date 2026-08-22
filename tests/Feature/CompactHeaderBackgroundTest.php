<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest schedule page paints its mobile background as an absolutely positioned div behind the
 * header (the CSS body background is suppressed below md). That div deliberately bleeds upward,
 * which is right for the banner card - the strip above it is empty - but wrong for the compact
 * header, whose full-width bar lives in exactly that strip and was being painted over by the
 * container's z-10 stacking context.
 */
class CompactHeaderBackgroundTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function createRoleWithBackground(string $headerStyle): Role
    {
        /** @var User $owner */
        $owner = $this->createOwner();

        return $this->createRole($owner, 'curator', [
            'name' => 'Background Curator',
            'header_style' => $headerStyle,
            'background' => 'image',
            'background_image_url' => 'https://cdn.example.test/backgrounds/hero.jpg',
        ]);
    }

    private function getGuestPage(Role $role)
    {
        return $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]));
    }

    public function test_compact_header_background_starts_below_the_bar(): void
    {
        $role = $this->createRoleWithBackground('compact');

        $response = $this->getGuestPage($role);

        $response->assertOk();
        // -top-3 cancels the container's mobile pt-3, so the image starts flush at the bar's
        // bottom edge instead of 160px above it.
        $response->assertSee('absolute -top-3 -bottom-3', false);
        $response->assertDontSee('absolute -top-40', false);
    }

    public function test_compact_bar_outranks_the_content_container(): void
    {
        $role = $this->createRoleWithBackground('compact');

        $response = $this->getGuestPage($role);

        $response->assertOk();
        // Same layer as the announcement bar, above the container's `relative z-10`.
        $response->assertSee('<div class="relative z-20 bg-white/95', false);
    }

    public function test_banner_header_keeps_the_upward_bleed(): void
    {
        $role = $this->createRoleWithBackground('banner');

        $response = $this->getGuestPage($role);

        $response->assertOk();
        $response->assertSee('absolute -top-40 -bottom-3', false);
        $response->assertDontSee('absolute -top-3', false);
    }
}
