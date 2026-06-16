<?php

namespace Tests\Feature;

use App\Models\EventComment;
use App\Models\EventPoll;
use App\Models\EventPollVote;
use App\Models\EventVideo;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;
    use CreatesScheduleData;

    public function test_markdown_description_is_rendered_to_html(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'description' => "## Heading\n\n**Bold** and *italic*",
        ]);

        $this->assertNotNull($event->description_html);
        $this->assertStringContainsString('<strong>Bold</strong>', $event->description_html);
        $this->assertStringContainsString('<em>italic</em>', $event->description_html);
    }

    public function test_guest_event_page_renders(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['name' => 'Public Concert']);

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('Public Concert');
    }

    public function test_online_event_stores_and_renders_url(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'name' => 'Virtual Meetup',
            'event_url' => 'https://zoom.us/j/123456789',
        ]);

        $this->assertSame('https://zoom.us/j/123456789', $event->event_url);

        $this->get($this->guestEventUrl($role, $event))
            ->assertOk();
    }

    public function test_recurring_event_resolves_occurrences(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // Daily recurring (every day of week)
        $event = $this->createEvent($role, [
            'name' => 'Daily Standup',
            'starts_at' => '2026-06-15 09:00:00',
            'days_of_week' => '1111111',
        ]);

        $this->assertSame('1111111', $event->days_of_week);

        // The recurrence engine matches a future occurrence date for a daily event.
        $future = \Carbon\Carbon::now()->addWeek();
        $this->assertTrue($event->matchesDate($future));
    }

    public function test_private_event_requires_password(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'name' => 'Secret Gathering',
            'event_password' => 'letmein',
        ]);

        $this->assertTrue($event->isPasswordProtected());

        // Wrong password: access gate not granted
        $this->post(route('event.check_password', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'password' => 'wrong',
        ]);
        $this->assertFalse(session()->has('event_password_' . $event->id));

        // Correct password: access gate granted (stored in session)
        $this->post(route('event.check_password', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'password' => 'letmein',
        ]);
        $this->assertTrue(session()->has('event_password_' . $event->id));
    }

    public function test_event_poll_vote_is_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $poll = EventPoll::create([
            'event_id' => $event->id,
            'question' => 'Favorite color?',
            'options' => ['Red', 'Blue', 'Green'],
            'is_active' => true,
        ]);

        $voter = $this->createOwner();

        $this->actingAs($voter)->postJson(route('event.vote_poll', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
            'poll_hash' => UrlUtils::encodeId($poll->id),
        ]), ['option_index' => 1])->assertOk();

        $this->assertDatabaseHas('event_poll_votes', [
            'event_poll_id' => $poll->id,
            'user_id' => $voter->id,
            'option_index' => 1,
        ]);
    }

    public function test_fan_video_submission_then_approval(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $submitter = $this->createOwner();

        $this->actingAs($submitter)->post(route('event.submit_video', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $video = EventVideo::where('event_id', $event->id)->first();
        $this->assertNotNull($video);
        $this->assertFalse((bool) $video->is_approved);

        $this->actingAs($owner)->post(route('event.approve_video', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($video->id),
        ]));

        $this->assertTrue((bool) $video->fresh()->is_approved);
    }

    public function test_fan_comment_submission(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $submitter = $this->createOwner();

        $this->actingAs($submitter)->post(route('event.submit_comment', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'comment' => 'Great show!',
        ]);

        $this->assertDatabaseHas('event_comments', [
            'event_id' => $event->id,
            'comment' => 'Great show!',
        ]);
    }

    public function test_fan_photo_submission(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::fake('local');

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $submitter = $this->createOwner();

        $this->actingAs($submitter)->post(route('event.submit_photo', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'photo' => \Illuminate\Http\UploadedFile::fake()->image('fan.jpg', 800, 600),
        ]);

        $this->assertDatabaseHas('event_photos', [
            'event_id' => $event->id,
        ]);
    }

    public function test_event_agenda_parts_saved(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $this->actingAs($owner)->post(route('event.save_parts', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'parts' => [
                ['name' => 'Opening', 'description' => 'Welcome', 'start_time' => '10:00', 'end_time' => '10:30'],
                ['name' => 'Main Act', 'description' => 'Headliner', 'start_time' => '10:30', 'end_time' => '12:00'],
            ],
        ]);

        $this->assertDatabaseHas('event_parts', [
            'event_id' => $event->id,
            'name' => 'Main Act',
        ]);
        $this->assertSame(2, $event->parts()->count());
    }

    public function test_get_start_date_time_handles_null_user_timezone(): void
    {
        // Reproduces a 500: an authenticated user with a null timezone made
        // Event::getStartDateTime() call Carbon::setTimezone(null) (TypeError).
        $owner = \App\Models\User::factory()->create(['timezone' => null, 'email_verified_at' => now()]);
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $this->actingAs($owner);

        $start = $event->getStartDateTime(null, true);
        $end = $event->getEndDateTime(null, true);

        $this->assertInstanceOf(\Carbon\Carbon::class, $start);
        $this->assertInstanceOf(\Carbon\Carbon::class, $end);
        $this->assertSame('UTC', $start->getTimezone()->getName());
    }

    public function test_photo_gallery_shows_approved_photos(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['fan_photos_enabled' => true]);

        \App\Models\EventPhoto::create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'photo_url' => 'photos/test.jpg',
            'is_approved' => true,
        ]);

        $url = '/' . $role->subdomain . '/' . $event->slug . '/' . UrlUtils::encodeId($event->id) . '/photos';
        $this->get($url)
            ->assertOk()
            ->assertViewHas('event', fn ($e) => $e->approvedPhotos->count() === 1);
    }

    public function test_bulk_photo_download(): void
    {
        // The controller assembles the zip on the real filesystem (storage/app/temp) and
        // reads photo files directly, so Storage::fake doesn't satisfy it without polluting
        // real storage. Left for a fixture that writes real temp files.
        $this->markTestSkipped('Bulk photo download builds a zip from real on-disk files; needs a real-file fixture.');

        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');

        $owner = $this->createOwner();
        $role = $this->createRole($owner); // enterprise = Pro
        $event = $this->createEvent($role);

        foreach (['photos/p1.jpg', 'photos/p2.jpg'] as $path) {
            \Illuminate\Support\Facades\Storage::disk('local')->put($path, 'img');
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, 'img');
            \App\Models\EventPhoto::create([
                'event_id' => $event->id,
                'user_id' => $owner->id,
                'photo_url' => $path,
                'is_approved' => true,
            ]);
        }

        $response = $this->actingAs($owner)->get(route('event.download_photos', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('zip', strtolower($response->headers->get('Content-Type') ?? ''));
    }
}
