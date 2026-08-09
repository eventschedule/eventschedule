<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The six theme palettes (Sand/Mist/Paper, Espresso/Midnight/Carbon) are admin portal
 * plus auth ONLY.
 *
 * The trap this pins: layouts/app.blade.php is the shell for BOTH portals, because
 * layouts/app-guest.blade.php opens with <x-app-layout> exactly like
 * layouts/app-admin.blade.php does. So the opt-in has to be a per-layout prop
 * (AppLayout::$themeVariants), never something the shell decides for itself. If it
 * ever moves into the shell, every public schedule page starts stamping data-theme on
 * <html> and the palettes override the colours the schedule owner configured.
 *
 * partials/theme-script.blade.php renders `var VARIANTS = <bool>` and that flag is the
 * only thing gating the data-theme attribute at runtime, so asserting on it pins the
 * gate itself rather than a downstream symptom. (The attribute is written by JS before
 * first paint, so it is not in the server response and cannot be asserted here.)
 */
class ThemeVariantGatingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_admin_portal_opts_in_to_theme_variants(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue');

        $content = $this->actingAs($owner)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('var VARIANTS = true', $content);
    }

    public function test_auth_pages_opt_in_to_theme_variants(): void
    {
        $content = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('var VARIANTS = true', $content);
    }

    public function test_guest_portal_never_opts_in_despite_sharing_the_shell(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Gated Venue']);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('var VARIANTS = false', $content);
        $this->assertStringNotContainsString('var VARIANTS = true', $content);
    }

    /**
     * The guest page still gets light/dark, just not the palettes - the shared script
     * is included either way, only its $variants argument differs.
     */
    public function test_guest_portal_keeps_plain_light_dark_support(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString("classList.add('dark')", $content);
    }
}
