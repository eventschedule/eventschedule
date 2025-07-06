<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AccountSetupTrait;
use App\Models\User;

class ApiTest extends DuskTestCase
{
    use DatabaseTruncation;
    use AccountSetupTrait;

    /**
     * Test API functionality with cURL requests
     */
    public function testApiFunctionality(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Set up account using the trait
            $this->setupTestAccount($browser, $name, $email, $password);
            
            // Create test data
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);
            
            // Enable API and get the API key
            $apiKey = $this->enableApi($browser);
            
            // Test API endpoints using cURL
            $this->testApiEndpoints($apiKey);
        });
    }

    /**
     * Test various API endpoints using cURL
     */
    private function testApiEndpoints(string $apiKey): void
    {
        $baseUrl = config('app.url');
        
        // Test 1: Get schedules (should return user's schedules)
        $this->testGetSchedules($baseUrl, $apiKey);
        
        // Test 2: Get events (should return user's events)
        $this->testGetEvents($baseUrl, $apiKey);
        
        // Test 3: Create a new event via API
        $this->testCreateEvent($baseUrl, $apiKey);
        
        // Test 4: Test authentication failure
        $this->testAuthenticationFailure($baseUrl);
    }

    /**
     * Test GET /api/schedules endpoint
     */
    private function testGetSchedules(string $baseUrl, string $apiKey): void
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/api/schedules',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $apiKey,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode, 'GET /api/schedules should return 200');
        
        $data = json_decode($response, true);
        $this->assertIsArray($data, 'Response should be JSON array');
        $this->assertArrayHasKey('data', $data, 'Response should have data key');
        $this->assertArrayHasKey('meta', $data, 'Response should have meta key');
        
        // Should have at least 2 schedules (venue and talent)
        $this->assertGreaterThanOrEqual(2, count($data['data']), 'Should have at least 2 schedules');
    }

    /**
     * Test GET /api/events endpoint
     */
    private function testGetEvents(string $baseUrl, string $apiKey): void
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/api/events',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $apiKey,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode, 'GET /api/events should return 200');
        
        $data = json_decode($response, true);
        $this->assertIsArray($data, 'Response should be JSON array');
        $this->assertArrayHasKey('data', $data, 'Response should have data key');
        $this->assertArrayHasKey('meta', $data, 'Response should have meta key');
        
        // Should have at least 1 event
        $this->assertGreaterThanOrEqual(1, count($data['data']), 'Should have at least 1 event');
    }

    /**
     * Test POST /api/events/{subdomain} endpoint
     */
    private function testCreateEvent(string $baseUrl, string $apiKey): void
    {
        $eventData = [
            'name' => 'API Created Event',
            'starts_at' => date('Y-m-d H:i:s', strtotime('+5 days')),
            'venue_address1' => '456 API Street',
            'venue_name' => 'API Venue'
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/api/events/talent',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($eventData),
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $apiKey,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            fwrite(STDERR, "POST /api/events response: $response\n");
        }
        $this->assertEquals(201, $httpCode, 'POST /api/events should return 201');
        
        $data = json_decode($response, true);
        $this->assertIsArray($data, 'Response should be JSON array');
        $this->assertArrayHasKey('data', $data, 'Response should have data key');
        $this->assertArrayHasKey('meta', $data, 'Response should have meta key');
        $this->assertEquals('API Created Event', $data['data']['name'], 'Event name should match');
    }

    /**
     * Test authentication failure
     */
    private function testAuthenticationFailure(string $baseUrl): void
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/api/schedules',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: invalid_key',
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(401, $httpCode, 'Invalid API key should return 401');
        
        $data = json_decode($response, true);
        $this->assertIsArray($data, 'Response should be JSON array');
        $this->assertArrayHasKey('error', $data, 'Response should have error key');
        $this->assertEquals('Invalid API key', $data['error'], 'Error message should match');
    }

    /**
     * Test missing API key
     */
    private function testMissingApiKey(string $baseUrl): void
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/api/schedules',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(401, $httpCode, 'Missing API key should return 401');
        
        $data = json_decode($response, true);
        $this->assertIsArray($data, 'Response should be JSON array');
        $this->assertArrayHasKey('error', $data, 'Response should have error key');
        $this->assertEquals('API key is required', $data['error'], 'Error message should match');
    }
} 