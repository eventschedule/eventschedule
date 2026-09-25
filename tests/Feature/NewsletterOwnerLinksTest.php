<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\Role;
use App\Services\NewsletterService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A newsletter's blocks are JSON the builder posts, and the links in them - social links, button
 * and offer links, image links, the video link - were printed straight into href, in the mail and
 * in the builder's preview inside the app. The only check was a save-time list of schemes
 * (SanitizesNewsletterContent::parseBlocks()), which "java\tscript:" gets past, and which a block
 * restored from a backup never meets. A text block printed any contentHtml it was stored with.
 *
 * Each link now goes through UrlUtils::safeHref(), or safeActionHref() for a call to action, which
 * also keeps a mailto: or tel: link. One with no safe href leaves a social icon out, as the sponsor
 * block does, and leaves a button, offer or image showing unlinked.
 */
class NewsletterOwnerLinksTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const TEMPLATES = ['modern', 'classic', 'minimal', 'bold', 'compact'];

    private function render(Role $role, array $blocks, string $template = 'modern'): string
    {
        $newsletter = new Newsletter([
            'role_id' => $role->id,
            'subject' => 'This week',
            'blocks' => $blocks,
            'template' => $template,
            'style_settings' => Newsletter::defaultStyleSettings(),
        ]);
        $newsletter->setRelation('role', $role);

        return app(NewsletterService::class)->renderHtml($newsletter);
    }

    /** Every href in $html, as a browser reads it: spaces and control characters trimmed, tabs and line breaks dropped. */
    private function hrefs(string $html): array
    {
        preg_match_all('/<a\s[^>]*?href\s*=\s*"([^"]*)"/i', $html, $matches);

        return array_map(
            fn ($href) => strtolower(preg_replace('/[\t\n\r]/', '', trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5), "\x00..\x20"))),
            $matches[1]
        );
    }

    private function assertLinksOnlyToPagesAndAddresses(string $html, string $where): void
    {
        foreach ($this->hrefs($html) as $href) {
            // '#' is the preview's own unsubscribe and manage links (NewsletterService::renderHtml()).
            $this->assertMatchesRegularExpression('/^(?:https?:\/\/|mailto:|tel:|#$)/', $href, "{$where}: links to {$href}");
        }
    }

    public function test_a_social_link_is_linked_only_when_it_is_a_web_link(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        foreach (self::TEMPLATES as $template) {
            $html = $this->render($role, [[
                'id' => 'social',
                'type' => 'social_links',
                'data' => ['links' => [
                    ['platform' => 'facebook', 'url' => 'javascript:alert(1)'],
                    ['platform' => 'instagram', 'url' => "java\tscript:alert(2)"],
                    ['platform' => 'youtube', 'url' => "\x01javascript:alert(3)"],
                    ['platform' => 'website', 'url' => 'https://harbourhall.example.com/about'],
                ]],
            ]], $template);

            $this->assertLinksOnlyToPagesAndAddresses($html, $template);
            $this->assertStringNotContainsString('alert(', $html, "{$template}: prints a scripted link");
            $this->assertStringContainsString('href="https://harbourhall.example.com/about"', $html, "{$template}: keeps the web link");

            // A link with no safe href leaves its icon, or in the minimal template its name, out.
            foreach (['Facebook', 'Instagram', 'Youtube'] as $label) {
                $this->assertStringNotContainsString('alt="'.$label.'"', $html, "{$template}: {$label}");
                $this->assertStringNotContainsString('>'.$label.'</a>', $html, "{$template}: {$label}");
            }
        }
    }

    /** What the builder's live preview does with what it posts: parseBlocks() first, then the render. */
    public function test_the_builder_preview_links_no_script_its_save_check_let_through(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $html = $this->actingAs($owner)->postJson(route('newsletter.preview_draft'), [
            'role_id' => UrlUtils::encodeId($role->id),
            'subject' => 'This week',
            'template' => 'modern',
            'blocks' => json_encode([[
                'id' => 'social',
                'type' => 'social_links',
                'data' => ['links' => [
                    ['platform' => 'facebook', 'url' => "java\tscript:alert(1)"],
                    ['platform' => 'website', 'url' => 'https://harbourhall.example.com'],
                ]],
            ]]),
        ])->assertOk()->json('html');

        $this->assertLinksOnlyToPagesAndAddresses($html, 'preview');
        $this->assertStringNotContainsString('alt="Facebook"', $html);
        $this->assertStringContainsString('href="https://harbourhall.example.com"', $html);
    }

    public function test_a_button_offer_or_image_links_only_to_a_web_page_or_an_address(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        foreach (self::TEMPLATES as $template) {
            $html = $this->render($role, [
                ['id' => 'b1', 'type' => 'button', 'data' => ['text' => 'Scripted Button', 'url' => 'javascript:alert(1)']],
                ['id' => 'b2', 'type' => 'button', 'data' => ['text' => 'Email Us', 'url' => 'mailto:hello@harbourhall.example.com?subject=Tickets']],
                ['id' => 'b3', 'type' => 'button', 'data' => ['text' => 'Call To Book', 'url' => 'tel:+15551234567']],
                ['id' => 'b4', 'type' => 'button', 'data' => ['text' => 'Buy Tickets', 'url' => 'https://tickets.example.com/night']],
                ['id' => 'o1', 'type' => 'offer', 'data' => ['title' => 'Early Bird', 'buttonText' => 'Scripted Offer', 'buttonUrl' => "java\tscript:alert(2)"]],
                ['id' => 'i1', 'type' => 'image', 'data' => ['layout' => 'row', 'images' => [
                    ['url' => 'https://cdn.example.com/poster.png', 'link' => 'javascript:alert(3)', 'alt' => 'Scripted Poster'],
                    ['url' => 'https://cdn.example.com/lineup.png', 'link' => 'https://harbourhall.example.com/lineup', 'alt' => 'Lineup'],
                ]]],
            ], $template);

            $this->assertLinksOnlyToPagesAndAddresses($html, $template);
            $this->assertStringNotContainsString('alert(', $html, "{$template}: prints a scripted link");

            // Unlinked, but still there to read.
            foreach (['Scripted Button', 'Scripted Offer', 'alt="Scripted Poster"'] as $text) {
                $this->assertStringContainsString($text, $html, "{$template}: {$text}");
            }

            foreach ([
                'https://tickets.example.com/night',
                'mailto:hello@harbourhall.example.com?subject=Tickets',
                'tel:+15551234567',
                'https://harbourhall.example.com/lineup',
            ] as $href) {
                $this->assertStringContainsString('href="'.$href.'"', $html, "{$template}: keeps {$href}");
            }
        }
    }

    public function test_a_video_links_only_to_its_youtube_page(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $html = $this->render($role, [
            // The id is found inside the scripted url, and only the id is kept.
            ['id' => 'v1', 'type' => 'video', 'data' => ['url' => 'javascript:alert(1)//youtu.be/dQw4w9WgXcQ']],
            // No YouTube link at all, with an id and a thumbnail of its own.
            ['id' => 'v2', 'type' => 'video', 'data' => ['url' => 'javascript:alert(2)', 'videoId' => 'aaaaaaaaaaa', 'thumbnailUrl' => 'https://tracker.example.com/pixel.png']],
        ]);

        $this->assertLinksOnlyToPagesAndAddresses($html, 'video');
        $this->assertStringNotContainsString('alert(', $html);
        $this->assertStringContainsString('href="https://www.youtube.com/watch?v=dQw4w9WgXcQ"', $html);
        $this->assertStringContainsString('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $html);
        $this->assertStringNotContainsString('tracker.example.com', $html, 'a video with no YouTube link is left out');
    }

    public function test_a_text_block_prints_only_the_html_its_markdown_renders(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $html = $this->render($role, [
            ['id' => 't1', 'type' => 'text', 'data' => ['content' => '', 'contentHtml' => '<a href="javascript:alert(1)">Stored Html</a>']],
            ['id' => 't2', 'type' => 'text', 'data' => ['content' => 'See the [lineup](https://harbourhall.example.com/lineup).', 'contentHtml' => '<a href="javascript:alert(2)">Stale Html</a>']],
        ]);

        $this->assertLinksOnlyToPagesAndAddresses($html, 'text');
        $this->assertStringNotContainsString('Stored Html', $html);
        $this->assertStringNotContainsString('Stale Html', $html);
        $this->assertStringContainsString('href="https://harbourhall.example.com/lineup"', $html);
    }
}
