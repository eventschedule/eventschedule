<?php

namespace Tests\Feature;

use App\Models\EventComment;
use App\Models\EventFeedback;
use App\Models\EventPhoto;
use App\Models\EventVideo;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * GET /api/feedback and GET /api/fan-content (issue #108).
 */
class ApiFeedbackTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Configure an API key on the user and return the raw key for the X-API-Key header. */
    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    public function test_feedback_endpoint_returns_ratings_for_owned_schedules(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $sale = $this->createSale($event, $role, ['name' => 'Alex Attendee', 'email' => 'alex@example.com'], $ticket);

        EventFeedback::create([
            'event_id' => $event->id,
            'sale_id' => $sale->id,
            'event_date' => $sale->event_date,
            'rating' => 5,
            'comment' => 'Best night out all year',
        ]);

        $response = $this->getJson('/api/feedback', ['X-API-Key' => $this->apiKey($owner)])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rating', 5)
            ->assertJsonPath('data.0.comment', 'Best night out all year')
            ->assertJsonPath('data.0.attendee_name', 'Alex Attendee')
            ->assertJsonPath('data.0.attendee_email', 'alex@example.com')
            ->assertJsonPath('meta.total', 1);

        $this->assertSame(UrlUtils::encodeId($event->id), $response->json('data.0.event_id'));
    }

    public function test_feedback_endpoint_hides_other_users_schedules(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $sale = $this->createSale($event, $role, [], $ticket);

        EventFeedback::create([
            'event_id' => $event->id,
            'sale_id' => $sale->id,
            'event_date' => $sale->event_date,
            'rating' => 4,
        ]);

        $stranger = $this->createOwner();

        $this->getJson('/api/feedback', ['X-API-Key' => $this->apiKey($stranger)])
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_feedback_endpoint_filters_by_minimum_rating(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);

        foreach ([2, 5] as $rating) {
            $sale = $this->createSale($event, $role, ['email' => "r{$rating}@example.com"], $ticket);
            EventFeedback::create([
                'event_id' => $event->id,
                'sale_id' => $sale->id,
                'event_date' => $sale->event_date,
                'rating' => $rating,
            ]);
        }

        $this->getJson('/api/feedback?min_rating=4', ['X-API-Key' => $this->apiKey($owner)])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rating', 5);
    }

    public function test_feedback_endpoint_requires_an_api_key(): void
    {
        $this->getJson('/api/feedback')->assertStatus(401);
    }

    public function test_feedback_endpoint_rejects_a_bad_rating_filter(): void
    {
        $owner = $this->createOwner();

        $this->getJson('/api/feedback?min_rating=9', ['X-API-Key' => $this->apiKey($owner)])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Validation failed');
    }

    public function test_fan_content_endpoint_returns_approved_items_of_every_type(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        EventComment::create([
            'event_id' => $event->id,
            'guest_name' => 'Dana Guest',
            'comment' => 'What a set',
            'is_approved' => true,
        ]);
        EventVideo::create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_approved' => true,
        ]);
        EventPhoto::create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'photo_url' => 'crowd.jpg',
            'is_approved' => true,
        ]);
        EventComment::create([
            'event_id' => $event->id,
            'guest_name' => 'Pending Person',
            'comment' => 'Still in the queue',
            'is_approved' => false,
        ]);

        $response = $this->getJson('/api/fan-content', ['X-API-Key' => $this->apiKey($owner)])
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);

        $types = collect($response->json('data'))->pluck('type')->sort()->values()->all();
        $this->assertSame(['comment', 'photo', 'video'], $types);

        // Submitter emails are never part of this payload.
        foreach ($response->json('data') as $row) {
            $this->assertArrayNotHasKey('guest_email', $row);
            $this->assertArrayNotHasKey('email', $row);
        }
    }

    public function test_fan_content_endpoint_filters_by_type(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        EventComment::create([
            'event_id' => $event->id,
            'guest_name' => 'Dana Guest',
            'comment' => 'Comment row',
            'is_approved' => true,
        ]);
        EventVideo::create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'is_approved' => true,
        ]);

        $this->getJson('/api/fan-content?type=comment', ['X-API-Key' => $this->apiKey($owner)])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'comment')
            ->assertJsonPath('data.0.submitted_by', 'Dana Guest')
            ->assertJsonPath('data.0.is_guest_submission', true);
    }

    public function test_fan_content_endpoint_can_return_the_moderation_queue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        EventComment::create([
            'event_id' => $event->id,
            'guest_name' => 'Dana Guest',
            'comment' => 'Awaiting approval',
            'is_approved' => false,
        ]);

        $this->getJson('/api/fan-content?is_approved=0', ['X-API-Key' => $this->apiKey($owner)])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_approved', false);
    }

    public function test_fan_content_endpoint_hides_other_users_schedules(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        EventComment::create([
            'event_id' => $event->id,
            'guest_name' => 'Dana Guest',
            'comment' => 'Private to the owner',
            'is_approved' => true,
        ]);

        $stranger = $this->createOwner();

        $this->getJson('/api/fan-content', ['X-API-Key' => $this->apiKey($stranger)])
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
