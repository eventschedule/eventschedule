<?php

namespace Tests\Feature;

use App\Models\EventComment;
use App\Models\EventPhoto;
use App\Models\EventVideo;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Fan content submitted without an account (issue #108).
 *
 * A signed-out visitor on a schedule that accepts guest submissions supplies a name and
 * email instead of registering; the row is stored with a null user_id and still waits for
 * approval. Schedules that opt into fan_content_require_account keep the old behavior.
 */
class GuestFanContentTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function submitCommentUrl($role, $event): string
    {
        return route('event.submit_comment', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]);
    }

    public function test_guest_can_submit_a_comment_without_an_account(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $this->assertFalse((bool) $role->fan_content_require_account);

        $this->post($this->submitCommentUrl($role, $event), [
            'comment' => 'Loved every minute',
            'guest_name' => 'Dana Guest',
            'guest_email' => 'Dana@Example.com',
        ]);

        $comment = EventComment::where('event_id', $event->id)->first();

        $this->assertNotNull($comment);
        $this->assertNull($comment->user_id);
        $this->assertSame('Dana Guest', $comment->guest_name);
        $this->assertSame('dana@example.com', $comment->guest_email, 'email should be normalized to lowercase');
        $this->assertFalse((bool) $comment->is_approved, 'guest content must still be moderated');
        $this->assertSame('Dana Guest', $comment->submitterName());
        $this->assertTrue($comment->isGuestSubmission());
    }

    public function test_guest_can_submit_a_video_without_an_account(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $this->post(route('event.submit_video', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'guest_name' => 'Dana Guest',
            'guest_email' => 'dana@example.com',
        ]);

        $video = EventVideo::where('event_id', $event->id)->first();

        $this->assertNotNull($video);
        $this->assertNull($video->user_id);
        $this->assertSame('Dana Guest', $video->guest_name);
        $this->assertFalse((bool) $video->is_approved);
    }

    public function test_guest_can_submit_a_photo_without_an_account(): void
    {
        Storage::fake(config('filesystems.default'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $this->post(route('event.submit_photo', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]), [
            'photo' => UploadedFile::fake()->image('crowd.jpg', 400, 300),
            'guest_name' => 'Dana Guest',
            'guest_email' => 'dana@example.com',
        ]);

        $photo = EventPhoto::where('event_id', $event->id)->first();

        $this->assertNotNull($photo);
        $this->assertNull($photo->user_id);
        $this->assertSame('Dana Guest', $photo->guest_name);
        $this->assertFalse((bool) $photo->is_approved);
    }

    public function test_guest_submission_is_rejected_without_name_and_email(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $this->from('/')
            ->post($this->submitCommentUrl($role, $event), ['comment' => 'No details supplied'])
            ->assertSessionHas('error', __('messages.fan_content_guest_details_required'));

        $this->assertDatabaseCount('event_comments', 0);
    }

    public function test_guest_submission_is_rejected_with_a_malformed_email(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        $this->from('/')
            ->post($this->submitCommentUrl($role, $event), [
                'comment' => 'Bad email',
                'guest_name' => 'Dana Guest',
                'guest_email' => 'not-an-email',
            ])
            ->assertSessionHas('error', __('messages.fan_content_guest_details_required'));

        $this->assertDatabaseCount('event_comments', 0);
    }

    public function test_schedule_can_require_an_account_and_sends_guests_to_sign_up(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $role->update(['fan_content_require_account' => true]);
        $event = $this->createEvent($role);

        // Registration is open here, so the visitor is offered the sign-up page.
        config(['app.hosted' => true]);

        $response = $this->post($this->submitCommentUrl($role, $event), [
            'comment' => 'Should not be stored yet',
            'guest_name' => 'Dana Guest',
            'guest_email' => 'dana@example.com',
        ]);

        $response->assertRedirect(app_url(route('sign_up', [], false)));
        $this->assertDatabaseCount('event_comments', 0);
        $this->assertSame('comment', session('pending_fan_content')['type'] ?? null);
    }

    public function test_require_account_sends_guests_to_login_when_registration_is_closed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $role->update(['fan_content_require_account' => true]);
        $event = $this->createEvent($role);

        // A plain selfhost without ALLOW_REGISTRATION: /sign_up would bounce to /login, so the
        // visitor is sent straight there rather than through a dead end (issue #108).
        config(['app.hosted' => false, 'app.allow_registration' => false]);

        $this->post($this->submitCommentUrl($role, $event), [
            'comment' => 'Should not be stored yet',
        ])->assertRedirect(app_url(route('login', [], false)));

        $this->assertDatabaseCount('event_comments', 0);
        $this->assertSame('comment', session('pending_fan_content')['type'] ?? null);
    }

    public function test_submitter_name_falls_back_when_no_name_is_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        // Restored backups drop the author entirely (user_id and guest_name both null).
        $comment = EventComment::create([
            'event_id' => $event->id,
            'comment' => 'Imported comment',
            'is_approved' => true,
        ]);

        $this->assertSame(__('messages.user'), $comment->submitterName());
        $this->assertNull($comment->submitterEmail());
    }

    public function test_signed_in_submissions_still_record_the_user(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $submitter = $this->createOwner();

        $this->actingAs($submitter)->post($this->submitCommentUrl($role, $event), [
            'comment' => 'Signed in comment',
        ]);

        $comment = EventComment::where('event_id', $event->id)->first();

        $this->assertNotNull($comment);
        $this->assertSame($submitter->id, $comment->user_id);
        $this->assertNull($comment->guest_name);
        $this->assertFalse($comment->isGuestSubmission());
        $this->assertSame($submitter->name, $comment->submitterName());
    }
}
