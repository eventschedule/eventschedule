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

    public function test_compact_header_paints_no_phone_banner(): void
    {
        $role = $this->createRoleWithBackground('compact');

        $response = $this->getGuestPage($role);

        $response->assertOk();
        // Since the 2026-11 redesign a phone shows the background as the glow at the top of the
        // page, like every other width: no picture painted behind the header (it made the name
        // unreadable).
        $response->assertDontSee('absolute -top-3 -bottom-3', false);
        $response->assertDontSee('absolute -top-40', false);
    }

    public function test_compact_bar_outranks_the_content_container(): void
    {
        $role = $this->createRoleWithBackground('compact');

        $response = $this->getGuestPage($role);

        $response->assertOk();
        // Same layer as the announcement bar, above the container's `relative z-10`. Matched from
        // the id rather than from `<div class=` so that adding another attribute to the bar does
        // not read as a z-index regression.
        $response->assertSee('id="gp-header" class="relative z-20 bg-white/95', false);
    }

    public function test_banner_header_paints_no_phone_banner(): void
    {
        $role = $this->createRoleWithBackground('banner');

        $response = $this->getGuestPage($role);

        $response->assertOk();
        $response->assertDontSee('absolute -top-40 -bottom-3', false);
        $response->assertDontSee('absolute -top-3', false);
    }
}
