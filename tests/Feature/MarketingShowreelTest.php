<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homepage showcase loops the showreel (resources/promo/showreel) under the click-to-play
 * YouTube overview.
 *
 * Two failures here are silent in the browser. A renamed or deleted file under public/videos does
 * not error: the <video> just falls back to its poster, or to a black frame if the poster went too,
 * and the page still returns 200. And the reel is 14MB, so losing preload="none" or muted quietly
 * turns it into a download every homepage visitor pays for, or into a video no browser will autoplay.
 */
class MarketingShowreelTest extends TestCase
{
    use RefreshDatabase;

    private function showreelTag(string $body): string
    {
        $this->assertSame(1, preg_match('/<video\b[^>]*\bdata-showreel\b[^>]*>.*?<\/video>/s', $body, $m), 'the showreel <video> is gone from the homepage');

        return $m[0];
    }

    public function test_the_showreel_waits_to_be_seen_and_can_autoplay(): void
    {
        $tag = $this->showreelTag($this->get('/')->assertOk()->getContent());
        $open = substr($tag, 0, strpos($tag, '>') + 1);

        $this->assertStringContainsString('preload="none"', $open, 'without preload="none" every visitor downloads the reel');
        $this->assertMatchesRegularExpression('/\smuted[\s>]/', $open, 'browsers only autoplay muted video');
        $this->assertMatchesRegularExpression('/\splaysinline[\s>]/', $open, 'without playsinline iOS opens the reel fullscreen');
        $this->assertStringContainsString('type="video/mp4"', $tag, 'Safari and iOS need the H.264 source');
    }

    public function test_every_file_the_showreel_references_exists(): void
    {
        $tag = $this->showreelTag($this->get('/')->assertOk()->getContent());

        preg_match_all('#/(videos/[^"?]+)(?:\?[^"]*)?"#', $tag, $m);
        $this->assertGreaterThanOrEqual(3, count($m[1]), 'expected a poster and two sources');

        foreach ($m[1] as $path) {
            $this->assertFileExists(public_path($path), "the showreel points at {$path}, which is not in public/");
        }
    }

    public function test_the_showcase_no_longer_loads_a_youtube_thumbnail(): void
    {
        $this->get('/')->assertOk()->assertDontSee('i.ytimg.com', false);
    }
}
