<?php

namespace Tests\Feature;

use App\Mail\ClaimRole;
use App\Mail\ClaimVenue;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventClaimMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.is_testing' => true,
            'app.hosted' => false,
            'mail.disable_delivery' => false,
        ]);
    }

    public function test_unclaimed_roles_receive_claim_emails_when_self_hosted(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'timezone' => 'UTC',
        ]);

        $organizerRole = new Role([
            'type' => 'venue',
            'name' => 'Organizer Role',
            'subdomain' => 'organizer-' . Str::random(8),
            'email' => 'organizer@example.com',
            'timezone' => 'UTC',
            'language_code' => 'en',
            'country_code' => 'US',
        ]);
        $organizerRole->user_id = $user->id;
        $organizerRole->email_verified_at = now();
        $organizerRole->save();
        $organizerRole->users()->attach($user->id, ['level' => 'owner']);

        $this->actingAs($user);

        $startsAt = now()->addDay()->setTime(18, 0);

        $payload = [
            'name' => 'Self Hosted Event',
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'ends_at' => $startsAt->copy()->addHours(2)->format('Y-m-d H:i:s'),
            'schedule_type' => 'single',
            'members' => [
                'new_1' => [
                    'name' => 'Unclaimed Talent',
                    'email' => 'talent@example.com',
                ],
            ],
            'venue_name' => 'Unclaimed Venue',
            'venue_email' => 'venue@example.com',
            'venue_address1' => '123 Main St',
            'venue_city' => 'Test City',
            'venue_state' => 'TS',
            'venue_postal_code' => '12345',
            'venue_country_code' => 'US',
            'timezone' => 'UTC',
        ];

        $response = $this->post(route('event.store', ['subdomain' => $organizerRole->subdomain]), $payload);

        $response->assertRedirect();

        Mail::assertSent(ClaimRole::class, function (ClaimRole $mail) {
            return $mail->hasTo('talent@example.com');
        });

        Mail::assertSent(ClaimVenue::class, function (ClaimVenue $mail) {
            return $mail->hasTo('venue@example.com');
        });
    }
}
