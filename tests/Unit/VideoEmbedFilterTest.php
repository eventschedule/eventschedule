<?php

namespace Tests\Unit;

use App\Utils\GeminiUtils;
use App\Utils\VideoUtils;
use PHPUnit\Framework\TestCase;

/**
 * The pieces that decide whether a YouTube video will actually play once embedded.
 *
 * These are pure so they can be exercised without an API call - the behaviour they encode is
 * otherwise only observable by loading a guest page and seeing YouTube's "Video unavailable" panel.
 */
class VideoEmbedFilterTest extends TestCase
{
    private function statusItem(string $id, array $status): array
    {
        return ['id' => $id, 'status' => $status];
    }

    private function playable(): array
    {
        return ['embeddable' => true, 'privacyStatus' => 'public', 'uploadStatus' => 'processed'];
    }

    public function test_it_drops_videos_that_refuse_to_embed(): void
    {
        $videos = [
            'aaaaaaaaaaa' => ['id' => 'aaaaaaaaaaa'],
            'bbbbbbbbbbb' => ['id' => 'bbbbbbbbbbb'],
        ];

        $surviving = GeminiUtils::rejectUnplayableVideos($videos, [
            $this->statusItem('aaaaaaaaaaa', ['embeddable' => false, 'privacyStatus' => 'public', 'uploadStatus' => 'processed']),
            $this->statusItem('bbbbbbbbbbb', $this->playable()),
        ]);

        $this->assertSame(['bbbbbbbbbbb'], array_keys($surviving));
    }

    public function test_it_drops_videos_missing_from_the_status_response(): void
    {
        // videos.list omits ids it cannot see, rather than reporting them - that is how a deleted
        // or private video presents.
        $videos = [
            'aaaaaaaaaaa' => ['id' => 'aaaaaaaaaaa'],
            'bbbbbbbbbbb' => ['id' => 'bbbbbbbbbbb'],
        ];

        $surviving = GeminiUtils::rejectUnplayableVideos($videos, [
            $this->statusItem('bbbbbbbbbbb', $this->playable()),
        ]);

        $this->assertSame(['bbbbbbbbbbb'], array_keys($surviving));
    }

    public function test_it_drops_videos_that_are_not_public_or_not_processed(): void
    {
        $videos = [
            'aaaaaaaaaaa' => ['id' => 'aaaaaaaaaaa'],
            'bbbbbbbbbbb' => ['id' => 'bbbbbbbbbbb'],
            'ccccccccccc' => ['id' => 'ccccccccccc'],
        ];

        $surviving = GeminiUtils::rejectUnplayableVideos($videos, [
            $this->statusItem('aaaaaaaaaaa', ['embeddable' => true, 'privacyStatus' => 'unlisted', 'uploadStatus' => 'processed']),
            $this->statusItem('bbbbbbbbbbb', ['embeddable' => true, 'privacyStatus' => 'public', 'uploadStatus' => 'rejected']),
            $this->statusItem('ccccccccccc', $this->playable()),
        ]);

        $this->assertSame(['ccccccccccc'], array_keys($surviving));
    }

    public function test_a_status_object_missing_keys_is_not_treated_as_playable(): void
    {
        $this->assertFalse(GeminiUtils::isPlayableStatus([]));
        $this->assertFalse(GeminiUtils::isPlayableStatus(['embeddable' => true]));
        $this->assertTrue(GeminiUtils::isPlayableStatus($this->playable()));
    }

    public function test_remove_by_url_matches_the_same_video_across_url_forms(): void
    {
        $links = [
            (object) ['url' => 'https://youtu.be/dQw4w9WgXcQ', 'title' => 'Short form'],
            (object) ['url' => 'https://www.youtube.com/watch?v=oHg5SJYRHA0', 'title' => 'Keep me'],
        ];

        $remaining = VideoUtils::removeByUrl($links, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertCount(1, $remaining);
        $this->assertSame('Keep me', $remaining[0]->title);
    }

    public function test_remove_by_url_leaves_survivors_untouched(): void
    {
        // The column holds two shapes: {url,title,type} from the matcher and {name,url,
        // thumbnail_url} from the schedule editor. Rebuilding entries would strip whichever keys
        // the removal code did not know about, breaking the owner's link list.
        $links = [
            (object) ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'title' => 'Go', 'type' => 'youtube'],
            (object) ['name' => 'Editor added', 'url' => 'https://www.youtube.com/watch?v=oHg5SJYRHA0', 'thumbnail_url' => 'https://i.ytimg.com/x.jpg'],
        ];

        $remaining = VideoUtils::removeByUrl($links, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertCount(1, $remaining);
        $this->assertSame('Editor added', $remaining[0]->name);
        $this->assertSame('https://i.ytimg.com/x.jpg', $remaining[0]->thumbnail_url);
    }

    public function test_remove_by_url_does_not_match_two_unparseable_urls_to_each_other(): void
    {
        // Both extract to null. A naive === would delete the wrong entry.
        $links = [
            (object) ['url' => 'https://vimeo.com/12345', 'title' => 'Keep me'],
        ];

        $remaining = VideoUtils::removeByUrl($links, 'https://example.com/not-a-video');

        $this->assertCount(1, $remaining);
        $this->assertSame('Keep me', $remaining[0]->title);
    }

    public function test_remove_by_url_falls_back_to_an_exact_match_for_non_youtube_entries(): void
    {
        $links = [
            (object) ['url' => 'https://vimeo.com/12345', 'title' => 'Remove me'],
            (object) ['url' => 'https://vimeo.com/67890', 'title' => 'Keep me'],
        ];

        $remaining = VideoUtils::removeByUrl($links, 'https://vimeo.com/12345');

        $this->assertCount(1, $remaining);
        $this->assertSame('Keep me', $remaining[0]->title);
    }

    public function test_remove_by_url_removes_every_duplicate_of_the_same_video(): void
    {
        // saveVideos() appends without deduping, so the same video really can be stored twice and
        // renders as two identical broken iframes.
        $links = [
            (object) ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'title' => 'One'],
            (object) ['url' => 'https://youtu.be/dQw4w9WgXcQ', 'title' => 'Two'],
        ];

        $remaining = VideoUtils::removeByUrl($links, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertSame([], $remaining);
    }

    public function test_encode_links_writes_null_rather_than_the_skip_tombstone(): void
    {
        // '[]' means "a person decided this act gets no video" and permanently removes it from the
        // matcher queue. An emptied list must not be confused with that.
        $this->assertNull(VideoUtils::encodeLinks([]));
        $this->assertSame(
            '[{"url":"https:\/\/youtu.be\/dQw4w9WgXcQ"}]',
            VideoUtils::encodeLinks([(object) ['url' => 'https://youtu.be/dQw4w9WgXcQ']])
        );
    }
}
