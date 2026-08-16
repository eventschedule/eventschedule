<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class RecheckVideoEmbedsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const BROKEN = 'dQw4w9WgXcQ';

    private const GOOD = 'oHg5SJYRHA0';

    protected function setUp(): void
    {
        parent::setUp();

        // The command no-ops without a key, which would make every assertion below vacuous.
        config(['services.google.backend' => 'test-key']);
    }

    private function roleWithBothVideos($owner = null): Role
    {
        return $this->createRole($owner ?? $this->createOwner(), 'talent', [
            'youtube_links' => json_encode([
                ['url' => 'https://www.youtube.com/watch?v='.self::BROKEN, 'title' => 'Broken', 'type' => 'youtube'],
                ['url' => 'https://www.youtube.com/watch?v='.self::GOOD, 'title' => 'Good', 'type' => 'youtube'],
            ]),
        ]);
    }

    private function fakeStatuses(array $items): void
    {
        Http::fake([
            'www.googleapis.com/youtube/v3/videos*' => Http::response(['items' => $items], 200),
        ]);
    }

    private function statusItem(string $id, array $status): array
    {
        return ['id' => $id, 'status' => $status];
    }

    private function playable(string $id): array
    {
        return $this->statusItem($id, ['embeddable' => true, 'privacyStatus' => 'public', 'uploadStatus' => 'processed']);
    }

    public function test_it_removes_a_video_that_can_no_longer_be_embedded(): void
    {
        $role = $this->roleWithBothVideos();

        $this->fakeStatuses([
            $this->statusItem(self::BROKEN, ['embeddable' => false, 'privacyStatus' => 'public', 'uploadStatus' => 'processed']),
            $this->playable(self::GOOD),
        ]);

        $this->artisan('app:recheck-video-embeds')->assertExitCode(0);

        $links = json_decode($role->fresh()->youtube_links, true);

        $this->assertCount(1, $links);
        $this->assertStringContainsString(self::GOOD, $links[0]['url']);
    }

    public function test_it_removes_a_video_missing_from_the_response(): void
    {
        // videos.list omits ids it cannot see - that is how a deleted or private video presents.
        $role = $this->roleWithBothVideos();

        $this->fakeStatuses([$this->playable(self::GOOD)]);

        $this->artisan('app:recheck-video-embeds')->assertExitCode(0);

        $this->assertCount(1, json_decode($role->fresh()->youtube_links, true));
    }

    public function test_it_keeps_unlisted_videos(): void
    {
        // Unlisted embeds perfectly well, and a schedule owner may have pasted one deliberately.
        // The stricter search-time filter rejects unlisted; this sweep must not delete it.
        $role = $this->roleWithBothVideos();

        $this->fakeStatuses([
            $this->statusItem(self::BROKEN, ['embeddable' => true, 'privacyStatus' => 'unlisted', 'uploadStatus' => 'processed']),
            $this->playable(self::GOOD),
        ]);

        $this->artisan('app:recheck-video-embeds')->assertExitCode(0);

        $this->assertCount(2, json_decode($role->fresh()->youtube_links, true));
    }

    public function test_a_failed_request_removes_nothing(): void
    {
        // The single most important behaviour here: "no answer" must never be read as
        // "not embeddable", or one bad night wipes every video in the install.
        $role = $this->roleWithBothVideos();

        Http::fake([
            'www.googleapis.com/youtube/v3/videos*' => Http::response('', 500),
        ]);

        $this->artisan('app:recheck-video-embeds')->assertExitCode(0);

        $this->assertCount(2, json_decode($role->fresh()->youtube_links, true));
    }

    public function test_removing_the_last_video_writes_null_not_the_skip_tombstone(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', [
            'youtube_links' => json_encode([
                ['url' => 'https://www.youtube.com/watch?v='.self::BROKEN, 'title' => 'Broken', 'type' => 'youtube'],
            ]),
        ]);

        $this->fakeStatuses([
            $this->statusItem(self::BROKEN, ['embeddable' => false, 'privacyStatus' => 'public', 'uploadStatus' => 'processed']),
        ]);

        $this->artisan('app:recheck-video-embeds')->assertExitCode(0);

        $this->assertNull($role->fresh()->youtube_links);
    }

    public function test_dry_run_writes_nothing(): void
    {
        $role = $this->roleWithBothVideos();

        $this->fakeStatuses([
            $this->statusItem(self::BROKEN, ['embeddable' => false, 'privacyStatus' => 'public', 'uploadStatus' => 'processed']),
            $this->playable(self::GOOD),
        ]);

        $this->artisan('app:recheck-video-embeds', ['--dry-run' => true])->assertExitCode(0);

        $this->assertCount(2, json_decode($role->fresh()->youtube_links, true));
    }

    public function test_it_does_not_wipe_derived_html_columns(): void
    {
        // Regression guard. Role's saving hook rewrites description_html from $model->description
        // with no dirty check, so writing back a partially-selected model nulls it. The command
        // uses the query builder to stay clear of that.
        $role = $this->createRole($this->createOwner(), 'talent', [
            'description' => 'Hello **world**',
            'youtube_links' => json_encode([
                ['url' => 'https://www.youtube.com/watch?v='.self::BROKEN, 'title' => 'Broken', 'type' => 'youtube'],
                ['url' => 'https://www.youtube.com/watch?v='.self::GOOD, 'title' => 'Good', 'type' => 'youtube'],
            ]),
        ]);

        $this->assertNotEmpty($role->description_html);
        $before = $role->description_html;

        $this->fakeStatuses([
            $this->statusItem(self::BROKEN, ['embeddable' => false, 'privacyStatus' => 'public', 'uploadStatus' => 'processed']),
            $this->playable(self::GOOD),
        ]);

        $this->artisan('app:recheck-video-embeds')->assertExitCode(0);

        $this->assertSame($before, $role->fresh()->description_html);
    }

    public function test_it_does_nothing_without_a_youtube_key(): void
    {
        config(['services.google.backend' => null]);

        $role = $this->roleWithBothVideos();

        Http::fake();

        $this->artisan('app:recheck-video-embeds')->assertExitCode(0);

        Http::assertNothingSent();
        $this->assertCount(2, json_decode($role->fresh()->youtube_links, true));
    }
}
