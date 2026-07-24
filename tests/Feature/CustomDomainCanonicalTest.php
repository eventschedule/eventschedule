<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule served directly on its custom domain (DigitalOcean direct + active) must advertise
 * that custom domain as the SEO canonical even when the page is loaded on the plain
 * {subdomain}.eventschedule.com URL. Redirect-mode domains 301 to the subdomain, so they keep
 * the subdomain canonical. The decision lives in Role/Event::getCanonicalUrl(), so it holds on
 * the subdomain request without the custom-domain middleware running.
 */
class CustomDomainCanonicalTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_direct_active_custom_domain_is_canonical_on_subdomain_request(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Canonical Venue',
            'custom_domain' => 'https://canonical-direct.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        // Canonical, og:url, and JSON-LD url all point at the custom domain...
        $this->assertStringContainsString('<link rel="canonical" href="https://canonical-direct.test">', $content);
        $this->assertStringContainsString('<meta property="og:url" content="https://canonical-direct.test">', $content);
        // ...and the subdomain URL is never advertised as canonical.
        $this->assertStringNotContainsString('<link rel="canonical" href="'.$role->getGuestUrl().'">', $content);
    }

    public function test_redirect_mode_custom_domain_keeps_subdomain_canonical(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Redirect Venue',
            'custom_domain' => 'https://canonical-redirect.test',
            'custom_domain_mode' => 'redirect',
            'custom_domain_status' => null,
        ]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        // Redirect-mode domains 301 to the subdomain, so the subdomain stays canonical and the
        // custom domain must not appear anywhere in the head URLs.
        $this->assertStringContainsString('<link rel="canonical" href="'.$role->getGuestUrl().'">', $content);
        $this->assertStringNotContainsString('canonical-redirect.test', $content);
    }

    public function test_direct_pending_custom_domain_keeps_subdomain_canonical(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Pending Venue',
            'custom_domain' => 'https://canonical-pending.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'pending',
        ]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        // Not provisioned yet: canonical stays on the subdomain until the domain goes active.
        $this->assertStringContainsString('<link rel="canonical" href="'.$role->getGuestUrl().'">', $content);
        $this->assertStringNotContainsString('canonical-pending.test', $content);
    }

    public function test_direct_active_custom_domain_is_canonical_on_event_page(): void
    {
        $owner = $this->createOwner();
        // Talent so the event's home schedule (Event::role() = first talent) resolves to this role.
        $role = $this->createRole($owner, 'talent', [
            'name' => 'Canonical Talent',
            'custom_domain' => 'https://canonical-event.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $event = $this->createEvent($role, ['name' => 'Canonical Event']);

        $content = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        // The event canonical uses the custom-domain host (the path follows).
        $this->assertStringContainsString('<link rel="canonical" href="https://canonical-event.test/', $content);
    }

    public function test_schedule_without_custom_domain_is_unaffected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Plain Venue']);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.$role->getGuestUrl().'">', $content);
    }
}
