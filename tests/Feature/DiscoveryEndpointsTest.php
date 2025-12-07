<?php

namespace Tests\Feature;

use Tests\TestCase;

class DiscoveryEndpointsTest extends TestCase
{
    public function test_manifest_is_available_at_well_known_path(): void
    {
        $response = $this->getJson('/.well-known/eventschedule.json');

        $response->assertOk()
            ->assertJson([
                'apiBaseURL' => url('/api'),
                'brandingEndpoint' => url('/branding.json'),
            ])
            ->assertJsonStructure([
                'apiBaseURL',
                'brandingEndpoint',
                'features',
            ]);
    }

    public function test_branding_endpoint_returns_resolved_branding_settings(): void
    {
        config()->set('branding', [
            'logo_url' => 'https://example.com/logo.png',
            'logo_alt' => 'Example Logo',
            'colors' => [
                'primary' => '#123456',
                'secondary' => '#654321',
                'tertiary' => '#abcdef',
                'on_primary' => '#0F0F0F',
                'on_secondary' => '#111111',
                'on_tertiary' => '#222222',
            ],
            'default_language' => 'fr',
        ]);

        $response = $this->getJson('/branding.json');

        $response->assertOk()->assertJson([
            'logoUrl' => 'https://example.com/logo.png',
            'logoAlt' => 'Example Logo',
            'colors' => [
                'primary' => '#123456',
                'secondary' => '#654321',
                'tertiary' => '#abcdef',
                'onPrimary' => '#0F0F0F',
                'onSecondary' => '#111111',
                'onTertiary' => '#222222',
            ],
            'defaultLanguage' => 'fr',
        ]);
    }
}
