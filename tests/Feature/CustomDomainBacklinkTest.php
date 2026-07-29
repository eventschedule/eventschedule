<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A custom domain implies an Enterprise plan, and Role::showBranding() is false above the free
 * tier, so a schedule served on its own domain used to render neither guest footer - leaving
 * eventschedule.com with no link at all from the one place a backlink carries real SEO weight
 * (ResolveCustomDomain rewrites every other URL in the body to the custom host). The subtle chip
 * in layouts/app-guest.blade.php now stands in as that backlink, but only on the host that needs
 * it and only when the black bar is not already carrying the link.
 */
class CustomDomainBacklinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The chip's tagged URL, which no other footer emits. */
    private const BACKLINK = 'utm_source=custom-domain';

    /** Unique to the black bar's nexus variant. */
    private const BLACK_BAR = 'Invoice Ninja';

    protected function setUp(): void
    {
        parent::setUp();

        // ResolveCustomDomain no-ops unless hosted, and compares the request host against
        // _base_domain(), which reads app.url - left empty by CI. Pinning it also fixes the host
        // that relative test URLs resolve to, so those keep bypassing the middleware.
        config([
            'app.hosted' => true,
            'app.is_nexus' => true,
            'app.url' => 'https://eventschedule.test',
        ]);
    }

    private function createCustomDomainRole(array $attrs = [])
    {
        return $this->createRole($this->createOwner(), 'venue', array_merge([
            'name' => 'Backlink Venue',
            'custom_domain' => 'https://backlink.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ], $attrs));
    }

    public function test_enterprise_custom_domain_page_carries_the_backlink(): void
    {
        $role = $this->createCustomDomainRole();

        $this->assertFalse($role->showBranding(), 'createRole defaults to Enterprise, so the black bar is off.');

        $content = $this->get('https://backlink.test/'.$role->subdomain)->assertOk()->getContent();

        // Blade escapes the separator, which is the correct HTML encoding - browsers send a literal
        // "&" - so pin the rendered form.
        $this->assertStringContainsString(
            'href="https://eventschedule.com?utm_source=custom-domain&amp;utm_medium=referral"',
            $content
        );
        $this->assertStringContainsString('>Event Schedule</span>', $content);
        // The chip stands in for the black bar rather than joining it.
        $this->assertStringNotContainsString(self::BLACK_BAR, $content);
    }

    public function test_same_schedule_on_its_subdomain_stays_unbranded(): void
    {
        $role = $this->createCustomDomainRole();

        // A link to our own domain from our own subdomain buys nothing, so the page they paid to
        // keep unbranded stays unbranded.
        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::BACKLINK, $content);
        $this->assertStringNotContainsString(self::BLACK_BAR, $content);
    }

    public function test_lapsed_enterprise_keeps_only_the_black_bar(): void
    {
        // A downgrade does not revoke the domain (RoleController only blocks CHANGING it), so this
        // schedule is back on the free tier while still serving on its custom host. The black bar
        // returns and already links us - showing the chip too would double the branding.
        $role = $this->createCustomDomainRole([
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->assertTrue($role->showBranding());

        $content = $this->get('https://backlink.test/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString(self::BLACK_BAR, $content);
        $this->assertStringNotContainsString(self::BACKLINK, $content);
    }

    public function test_backlink_is_absent_from_embeds_and_graphics(): void
    {
        $role = $this->createCustomDomainRole();

        // An embed renders inside a third party's iframe and ?graphic=1 is a noindex render meant
        // to be captured as an image, so neither is worth a badge.
        $embed = $this->get('https://backlink.test/'.$role->subdomain.'?embed=1')->assertOk()->getContent();
        $this->assertStringNotContainsString(self::BACKLINK, $embed);

        $graphic = $this->get('https://backlink.test/'.$role->subdomain.'?graphic=1')->assertOk()->getContent();
        $this->assertStringNotContainsString(self::BACKLINK, $graphic);
    }
}
