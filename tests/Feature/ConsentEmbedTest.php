<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
