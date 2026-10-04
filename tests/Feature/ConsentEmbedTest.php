<?php

namespace Tests\Feature;

use App\Http\Controllers\AppController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * YouTube and Google Maps on guest pages load only once the visitor wants them
 * (components/consent-embed.blade.php, resources/js/components/ConsentEmbed.vue). Both see every
 * visitor's IP address and can set cookies, so a bare <iframe> on a guest page would contact
 * Google for everyone who opens it, before anyone was asked.
 */
class ConsentEmbedTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const GUEST_VIEWS = [
        'role/show-guest.blade.php',
        'event/show-guest.blade.php',
        'layouts/app-guest.blade.php',
    ];

    public function test_guest_views_have_no_bare_third_party_iframe(): void
    {
        foreach (self::GUEST_VIEWS as $view) {
            $source = file_get_contents(resource_path('views/'.$view));

            $this->assertDoesNotMatchRegularExpression('/<iframe\b/i', $source,
                "{$view}: a YouTube or Maps embed goes through <x-consent-embed>, never a bare iframe");
        }
    }

    public function test_a_schedule_video_renders_as_a_placeholder_with_a_local_thumbnail(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', [
            'youtube_links' => json_encode([['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']]),
        ]);

        $html = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertStringContainsString('data-consent-embed=', $html);
        $this->assertDoesNotMatchRegularExpression('/<iframe[^>]+youtube/i', $html);
        $this->assertStringNotContainsString('i.ytimg.com', $html, 'the thumbnail comes from this install');
        $this->assertStringContainsString('yt-thumb/dQw4w9WgXcQ', $html);
    }

    public function test_the_thumbnail_proxy_accepts_only_a_video_id(): void
    {
        $this->get('/yt-thumb/not-an-id')->assertNotFound();
        $this->get('/yt-thumb/'.str_repeat('a', 12))->assertNotFound();
    }

    /**
     * A signed-out request fetches only a video some schedule, event or newsletter links to: the
     * route must not make this server fetch, and cache to disk, any id it is handed.
     */
    public function test_the_thumbnail_proxy_fetches_only_videos_the_install_links_to(): void
    {
        $this->forgetThumbnails(['Unlinked_0a', 'Linked_0001']);
        Http::fake(['i.ytimg.com/*' => Http::response('jpeg-bytes', 200, ['Content-Type' => 'image/jpeg'])]);

        $this->get('/yt-thumb/Unlinked_0a')->assertNotFound();
        Http::assertNothingSent();

        $this->createRole($this->createOwner(), 'talent', [
            'youtube_links' => json_encode([['url' => 'https://www.youtube.com/watch?v=Linked_0001']]),
        ]);

        $this->get('/yt-thumb/Linked_0001')->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        Http::assertSent(fn ($request) => $request->url() === 'https://i.ytimg.com/vi/Linked_0001/mqdefault.jpg');

        $this->forgetThumbnails(['Linked_0001']);
    }

    /**
     * On a schedule's own host the route sits inside Route::domain('{subdomain}...'), which is not
     * registered under phpunit. A route with the subdomain parameter first stands in for it: read
     * by position, the subdomain arrived as the video id and every thumbnail 404'd.
     */
    public function test_the_thumbnail_proxy_reads_the_video_id_by_name(): void
    {
        $this->forgetThumbnails(['Linked_0002']);
        Http::fake(['i.ytimg.com/*' => Http::response('jpeg-bytes', 200, ['Content-Type' => 'image/jpeg'])]);
        Route::get('/tenant-probe/{subdomain}/yt-thumb/{id}', [AppController::class, 'youtubeThumbnail']);

        $this->createRole($this->createOwner(), 'talent', [
            'youtube_links' => json_encode([['url' => 'https://youtu.be/Linked_0002']]),
        ]);

        $this->get('/tenant-probe/somevenue/yt-thumb/Linked_0002')->assertOk();
        Http::assertSent(fn ($request) => str_contains($request->url(), '/vi/Linked_0002/'));

        $this->forgetThumbnails(['Linked_0002']);
    }

    /** @param  array<int, string>  $ids */
    private function forgetThumbnails(array $ids): void
    {
        foreach ($ids as $id) {
            foreach (['mq', 'hq'] as $quality) {
                @unlink(storage_path('app/'.AppController::YOUTUBE_THUMB_CACHE_DIR.'/'.$id.'_'.$quality.'.jpg'));
                Cache::forget('yt-thumb-missing:'.$id.':'.$quality);
            }
        }
    }
}
