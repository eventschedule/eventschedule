<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homepage showcase loops the showreel (resources/promo/showreel) under the click-to-play
 * YouTube overview, in a dark and a light cut that follow the site theme.
 *
 * Every failure here is silent in the browser. A renamed or deleted file under public/videos does
 * not error: the <video> just falls back to its poster, or to a blank frame if the poster went too,
 * and the page still returns 200. A light cut that points at the dark files looks like a working
 * light mode to anyone testing in dark mode. And the reel is 16MB, so losing preload="none" or
 * muted quietly turns it into a download every homepage visitor pays for, or into a video no
 * browser will autoplay.
 */
class MarketingShowreelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The markup of each cut: its <video> element and its poster <img>. Fails unless both themes
     * are present, so no test below can pass by looping over nothing.
     *
     * @return array<string, array{video: string, poster: string}> theme => markup
     */
    private function showreels(string $body): array
    {
        preg_match_all('/<video\b[^>]*\bdata-showreel="(\w+)"[^>]*>.*?<\/video>/s', $body, $videos, PREG_SET_ORDER);
        preg_match_all('/<img\b[^>]*\bdata-showreel-poster="(\w+)"[^>]*>/', $body, $posters, PREG_SET_ORDER);

        $reels = [];
        foreach ($videos as [$tag, $theme]) {
            $reels[$theme]['video'] = $tag;
        }
        foreach ($posters as [$tag, $theme]) {
            $reels[$theme]['poster'] = $tag;
        }
        ksort($reels);

        $this->assertSame(['dark', 'light'], array_keys($reels), 'expected a dark and a light showreel');
        foreach ($reels as $theme => $parts) {
            $this->assertArrayHasKey('video', $parts, "the {$theme} cut has a poster but no <video>");
            $this->assertArrayHasKey('poster', $parts, "the {$theme} cut has no poster <img>");
        }

        return $reels;
    }

    /**
     * @return list<string> every public/ path the markup references
     */
    private function files(string $markup): array
    {
        preg_match_all('#/(videos/[^"?]+)(?:\?[^"]*)?"#', $markup, $m);

        return $m[1];
    }

    public function test_there_is_one_reel_per_theme(): void
    {
        $body = $this->get('/')->assertOk()->getContent();
        $this->showreels($body);

        $this->assertSame(2, preg_match_all('/<video\b[^>]*\bdata-showreel=/', $body), 'expected exactly two showreels');
        $this->assertSame(2, preg_match_all('/<img\b[^>]*\bdata-showreel-poster=/', $body), 'expected exactly two posters');
    }

    public function test_each_reel_waits_to_be_seen_and_can_autoplay(): void
    {
        foreach ($this->showreels($this->get('/')->assertOk()->getContent()) as $theme => ['video' => $tag, 'poster' => $poster]) {
            $open = substr($tag, 0, strpos($tag, '>') + 1);

            $this->assertStringContainsString('preload="none"', $open, "without preload=\"none\" every visitor downloads the {$theme} reel");
            $this->assertMatchesRegularExpression('/\smuted[\s>]/', $open, 'browsers only autoplay muted video');
            $this->assertMatchesRegularExpression('/\splaysinline[\s>]/', $open, 'without playsinline iOS opens the reel fullscreen');
            $this->assertMatchesRegularExpression('/<source[^>]*-mobile\.mp4[^>]*media="\(max-width: \d+px\)"/', $tag, "phones should get the 540p {$theme} cut, not the 1080p60 one");
            $this->assertMatchesRegularExpression('/<source(?![^>]*\bmedia=)[^>]*type="video\/mp4"/', $tag, "Safari and iOS need an H.264 source for the {$theme} reel on desktop");

            // A poster attribute is fetched on page load even on the hidden cut; the lazy <img>
            // waits for the frame and is skipped while display:none.
            $this->assertStringNotContainsString(' poster=', $open, "the {$theme} reel's poster attribute downloads on every page load");
            $this->assertStringContainsString('loading="lazy"', $poster, "the {$theme} poster should load only when the frame is near");
        }
    }

    public function test_every_file_the_showreel_references_exists(): void
    {
        foreach ($this->showreels($this->get('/')->assertOk()->getContent()) as $theme => $parts) {
            $files = $this->files($parts['poster'].$parts['video']);
            $this->assertGreaterThanOrEqual(4, count($files), "expected a poster and three sources for the {$theme} reel");

            foreach ($files as $path) {
                $this->assertFileExists(public_path($path), "the {$theme} showreel points at {$path}, which is not in public/");
            }
        }
    }

    public function test_the_light_reel_is_not_the_dark_one_twice(): void
    {
        $reels = $this->showreels($this->get('/')->assertOk()->getContent());

        $shared = array_intersect(
            $this->files($reels['light']['poster'].$reels['light']['video']),
            $this->files($reels['dark']['poster'].$reels['dark']['video']),
        );

        $this->assertSame([], array_values($shared), 'the light and dark reels share files, so one theme is showing the other cut');
    }

    public function test_the_reel_can_be_paused_without_opening_the_overview(): void
    {
        $body = $this->get('/')->assertOk()->getContent();

        // WCAG 2.2.2: a looping reel needs a pause control, and it must not sit inside the facade
        // link, where every click opens the YouTube overview instead.
        $this->assertMatchesRegularExpression('/<button\b[^>]*\bdata-showreel-toggle\b/', $body, 'the showreel has no pause control');
        $this->assertSame(1, preg_match('/<a\b[^>]*\bdata-video-facade\b.*?<\/a>/s', $body, $facade), 'the overview facade is gone');
        $this->assertStringNotContainsString('data-showreel-toggle', $facade[0], 'the pause control is inside the facade link');
    }

    public function test_the_showcase_no_longer_loads_a_youtube_thumbnail(): void
    {
        $this->get('/')->assertOk()->assertDontSee('i.ytimg.com', false);
    }
}
