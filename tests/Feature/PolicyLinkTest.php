<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Keeps every link to a legal document going through policy_url().
 *
 * The whole point of issue #116 is that a selfhost install stops sending its users
 * to eventschedule.com's privacy policy and terms. Every consent checkbox in the
 * app is a copy of its neighbour, so the next one will be pasted from an existing
 * view - and a stray marketing_url('/terms-of-service') silently re-breaks the
 * feature with nothing failing. This test is the thing that fails.
 */
class PolicyLinkTest extends TestCase
{
    /** The paths that must resolve through policy_url() rather than marketing_url(). */
    private const POLICY_PATHS = ['/privacy', '/terms-of-service', '/cookie-policy'];

    /**
     * Views that legitimately link the marketing site's own copies.
     *
     * Keyed file => why, so an exemption cannot be added without stating a reason.
     * test_the_allow_list_has_no_stale_entries() fails if one stops matching.
     */
    private const ALLOWED = [
        // The bundled documents themselves, and their cross-links to each other.
        'marketing/privacy.blade.php' => 'the bundled document',
        'marketing/terms.blade.php' => 'the bundled document',
        'marketing/self-hosting-terms.blade.php' => 'the bundled document',
        'marketing/accessibility.blade.php' => 'a sibling of the bundled documents',
    ];

    private function viewFiles(): array
    {
        return File::allFiles(resource_path('views'));
    }

    private function relativePath(\SplFileInfo $file): string
    {
        return str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file->getPathname());
    }

    public function test_no_view_links_a_policy_through_marketing_url(): void
    {
        $offenders = [];

        foreach ($this->viewFiles() as $file) {
            $relative = $this->relativePath($file);

            if (array_key_exists($relative, self::ALLOWED)) {
                continue;
            }

            $contents = File::get($file->getPathname());

            foreach (self::POLICY_PATHS as $path) {
                if (str_contains($contents, "marketing_url('".$path."')")) {
                    $offenders[] = $relative.' -> '.$path;
                }
            }
        }

        $this->assertSame([], $offenders, implode("\n", array_merge(
            ['Link legal documents with policy_url(), not marketing_url(), so the policy an'],
            ['operator configured is the one that gets used. Offending views:'],
            $offenders,
        )));
    }

    public function test_the_allow_list_has_no_stale_entries(): void
    {
        foreach (self::ALLOWED as $relative => $reason) {
            $path = resource_path('views').DIRECTORY_SEPARATOR.$relative;

            $this->assertFileExists($path, "Allow-listed view no longer exists: {$relative} ({$reason})");
        }
    }

    /**
     * The consent surfaces are the reason the helper exists, so they are asserted
     * by name rather than left to the sweep above.
     */
    public function test_every_consent_surface_uses_the_helper(): void
    {
        $surfaces = [
            'auth/register.blade.php',
            'event/tickets.blade.php',
            'event/rsvp.blade.php',
            'event/booking-request.blade.php',
            'event/guest-submit.blade.php',
            'event/import.blade.php',
            'partials/guest-cart.blade.php',
            'partials/cookie-banner.blade.php',
            'ticket/view.blade.php',
            'layouts/app-admin.blade.php',
        ];

        foreach ($surfaces as $relative) {
            $contents = File::get(resource_path('views').DIRECTORY_SEPARATOR.$relative);

            $this->assertStringContainsString('policy_url(', $contents, "{$relative} no longer links a policy through policy_url()");
        }
    }
}
