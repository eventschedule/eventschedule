<?php

namespace Tests\Unit;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RoleGeocodingTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_role_without_address_changes_does_not_trigger_geocode(): void
    {
        config(['services.google.backend' => 'test-key']);

        Http::fake([
            'https://maps.googleapis.com/maps/api/geocode/json*' => Http::response([
                'status' => 'OK',
                'results' => [
                    [
                        'geometry' => [
                            'location' => ['lat' => '1.23', 'lng' => '4.56'],
                        ],
                        'formatted_address' => '123 Main St, Town, CA 90210, US',
                        'place_id' => 'fake-place-id',
                    ],
                ],
            ], 200),
        ]);

        $role = new Role([
            'type' => 'venue',
            'name' => 'Test Venue',
            'email' => 'test-role@example.com',
            'address1' => '123 Main St',
            'city' => 'Town',
            'state' => 'CA',
            'postal_code' => '90210',
            'country_code' => 'US',
            'timezone' => 'UTC',
        ]);
        $role->subdomain = 'test-role';
        $role->save();

        Http::assertSentCount(1);

        Http::fake();

        $role->default_tickets = json_encode(['tickets' => []]);
        $role->save();

        Http::assertSentCount(0);
    }
}
