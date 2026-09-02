<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The blog's crawl signals used to be self-defeating:
 *
 * - Every anonymous page view bumped view_count through Eloquent's increment(), which appends
 *   updated_at = now() to the UPDATE. Googlebot's own fetch therefore marked the post modified,
 *   so <lastmod>, dateModified and article:modified_time all reported the crawl time and the
 *   freshness signal was worthless for the whole corpus.
 * - Page 2 and beyond were noindex WITH a canonical pointing at page 1, a contradictory pair.
 *   Only the 10 posts on page 1 were reachable from indexable HTML.
 * - Every post rendered two H1s: the template's headline plus a raw <h1> in the stored body.
 * - The BlogPosting block was interpolated with {{ }}, which HTML-escapes but does not
 *   JSON-escape, so a double quote in a title invalidated the whole block.
 *
 * In this suite the blog is served path-based at /blog (routes/web.php registers that branch for
 * hosted+testing) rather than on blog.{domain}, so every expected URL is built from
 * route('blog.index') instead of a hardcoded host.
 */
class BlogSeoTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(array $attributes = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'title' => 'A Post',
            'slug' => 'a-post-'.strtolower(Str::random(6)),
            'content' => '<p>Body copy.</p>',
            'is_published' => true,
            'published_at' => now()->subMonth(),
        ], $attributes));
    }

    public function test_a_public_view_counts_without_touching_updated_at(): void
    {
        $stamp = Carbon::parse('2026-01-15 09:30:00');

        $post = $this->makePost([
            'content' => '<p>Body.</p>',
            'published_at' => Carbon::parse('2026-01-10 08:00:00'),
        ]);
        BlogPost::withoutTimestamps(fn () => $post->forceFill(['updated_at' => $stamp])->save());
        $post->refresh();

        // Any later "now" will do: the point is that the view must not stamp it.
        $this->travelTo(Carbon::parse('2026-06-01 12:00:00'));

        $body = $this->get('/blog/'.$post->slug)->assertOk()->getContent();

        $fresh = BlogPost::find($post->id);

        $this->assertSame(
            $stamp->toDateTimeString(),
            $fresh->updated_at->toDateTimeString(),
            'a page view rewrote updated_at, which is what poisons every lastmod on the blog'
        );
        $this->assertSame(1, (int) $fresh->view_count, 'the view was not counted');

        // The rendered page must agree with the row: the view reads the in-memory model, which
        // Eloquent's increment() used to leave holding a fresh timestamp.
        $this->assertSame(
            $stamp->toISOString(),
            $this->jsonLdOfType($body, 'BlogPosting')['dateModified'],
            'dateModified drifted from the stored updated_at'
        );
        $this->assertStringContainsString(
            '<meta property="article:modified_time" content="'.$stamp->toISOString().'">',
            $body
        );
    }

    public function test_the_repair_migration_replaces_crawl_stamps_with_publish_dates(): void
    {
        $published = $this->makePost(['published_at' => Carbon::parse('2026-02-03 10:00:00')]);
        $backdated = $this->makePost(['published_at' => Carbon::parse('2019-01-01 00:00:00')]);
        $draft = $this->makePost(['is_published' => false, 'published_at' => null]);
        // Never publicly viewed, so the bug never restamped it and its updated_at is a real edit
        // date. The repair must leave it alone - it is the same statement that counts a view.
        $neverViewed = $this->makePost(['published_at' => Carbon::parse('2026-01-05 10:00:00')]);

        // What years of view counting left behind.
        DB::table('blog_posts')->update(['updated_at' => Carbon::parse('2026-09-01 23:59:00'), 'view_count' => 812]);
        DB::table('blog_posts')->where('id', $published->id)->update(['created_at' => Carbon::parse('2026-02-03 09:55:00')]);
        DB::table('blog_posts')->where('id', $backdated->id)->update(['created_at' => Carbon::parse('2024-06-01 00:00:00')]);
        DB::table('blog_posts')->where('id', $neverViewed->id)->update([
            'view_count' => 0,
            'updated_at' => Carbon::parse('2026-08-20 12:00:00'),
        ]);

        $migration = require database_path('migrations/2026_09_02_000000_reset_blog_post_updated_at.php');
        $migration->up();
        $migration->up(); // idempotent

        $this->assertSame(
            '2026-02-03 10:00:00',
            BlogPost::find($published->id)->updated_at->toDateTimeString()
        );

        // published_at predates the row, so created_at is the floor.
        $this->assertSame(
            '2024-06-01 00:00:00',
            BlogPost::find($backdated->id)->updated_at->toDateTimeString()
        );

        // Unpublished posts are nobody's crawl target and are left alone.
        $this->assertSame(
            '2026-09-01 23:59:00',
            BlogPost::find($draft->id)->updated_at->toDateTimeString()
        );

        // And a post the bug never reached keeps its genuine edit date. Without this guard the
        // repair destroyed the very signal it exists to protect, on the rows that were correct.
        $this->assertSame(
            '2026-08-20 12:00:00',
            BlogPost::find($neverViewed->id)->updated_at->toDateTimeString()
        );
    }

    public function test_a_second_view_counts_again(): void
    {
        $post = $this->makePost();

        $this->get('/blog/'.$post->slug)->assertOk();
        $this->get('/blog/'.$post->slug)->assertOk();

        $this->assertSame(2, (int) BlogPost::find($post->id)->view_count);
    }

    public function test_page_two_is_indexable_and_canonicalizes_to_itself(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->makePost(['title' => 'Post '.$i, 'published_at' => now()->subDays($i + 1)]);
        }

        $body = $this->get('/blog?page=2')->assertOk()->getContent();
        $base = route('blog.index');

        $this->assertStringNotContainsString('noindex', $body, 'page 2 is still noindex');
        $this->assertStringContainsString('<link rel="canonical" href="'.$base.'?page=2">', $body);

        // rel=prev, and the paginator's own rendered links, must use the identical URL form.
        $this->assertStringContainsString('<link rel="prev" href="'.$base.'?page=1">', $body);
        $this->assertStringContainsString('href="'.$base.'?page=1"', $body);

        // The Blog entity stays at page 1; only mainEntityOfPage follows the page being served.
        $node = $this->jsonLdOfType($body, 'Blog');
        $this->assertSame($base, $node['url']);
        $this->assertSame($base.'?page=2', $node['mainEntityOfPage']['@id']);
    }

    /**
     * Now that page 2+ is indexable, all 19 pages would otherwise ship the byte-identical title
     * and description of page 1, which is a set of duplicates Google picks one of.
     */
    public function test_a_paginated_page_names_itself_in_the_title_and_description(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->makePost(['title' => 'Post '.$i, 'published_at' => now()->subDays($i + 1)]);
        }

        $pageOne = $this->get('/blog')->assertOk()->getContent();
        $pageTwo = $this->get('/blog?page=2')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Blog | Event Schedule</title>', $pageOne);
        $this->assertStringContainsString('<title>Blog - Page 2 | Event Schedule</title>', $pageTwo);

        preg_match('~<meta name="description" content="(.*?)">~s', $pageOne, $one);
        preg_match('~<meta name="description" content="(.*?)">~s', $pageTwo, $two);

        $this->assertNotSame($one[1], $two[1], 'page 2 repeats page 1 description');
        $this->assertStringEndsWith('Page 2.', $two[1]);
    }

    public function test_page_one_keeps_the_clean_canonical(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->makePost(['title' => 'Post '.$i, 'published_at' => now()->subDays($i + 1)]);
        }

        $body = $this->get('/blog')->assertOk()->getContent();
        $base = route('blog.index');

        $this->assertStringContainsString('<link rel="canonical" href="'.$base.'">', $body);
        $this->assertStringNotContainsString('noindex', $body);
        $this->assertStringContainsString('<link rel="next" href="'.$base.'?page=2">', $body);
    }

    public function test_a_page_past_the_last_one_stays_out_of_the_index(): void
    {
        $this->makePost();

        $body = $this->get('/blog?page=9')->assertOk()->getContent();

        $this->assertStringContainsString('noindex', $body, 'an empty overflow page is indexable');
    }

    public function test_tag_and_month_archives_stay_noindex(): void
    {
        $this->makePost(['tags' => ['ticketing']]);

        foreach (['/blog?tag=ticketing', '/blog?month=1&year=2026'] as $url) {
            $this->assertStringContainsString(
                'noindex, follow',
                $this->get($url)->assertOk()->getContent(),
                $url.' should stay out of the index'
            );
        }
    }

    public function test_a_post_renders_exactly_one_h1(): void
    {
        // The AI generator used to be told to emit <h1>, and sanitizeHtml allows it through.
        $post = $this->makePost([
            'title' => 'Selling Tickets',
            'content' => '<h1>Selling Tickets</h1><p>Body.</p><h1>Second</h1><h2>Sub</h2>',
        ]);

        $body = $this->get('/blog/'.$post->slug)->assertOk()->getContent();

        $this->assertSame(1, substr_count($body, '<h1'), 'the stored body contributes extra H1s');
        $this->assertStringContainsString('<h2>Selling Tickets</h2>', $body, 'the body heading was dropped, not demoted');
        $this->assertStringContainsString('<h2>Second</h2>', $body);
        $this->assertStringContainsString('<h2>Sub</h2>', $body, 'an existing H2 was disturbed');

        // The RSS body is the same content through the same seam.
        $this->assertStringNotContainsString('<h1', $this->get('/blog/feed')->assertOk()->getContent());
    }

    public function test_the_ai_prompt_no_longer_asks_for_an_h1(): void
    {
        $prompt = config('ai_prompts.blog_post.base');

        $this->assertStringNotContainsString('<h1>', $prompt);
        $this->assertStringContainsString('<h2>', $prompt);
    }

    public function test_the_blog_posting_block_survives_a_quote_in_the_title(): void
    {
        $post = $this->makePost([
            'title' => 'The "Best" Way to Sell Tickets',
            'excerpt' => 'She said "yes" to the venue.',
            'tags' => ['ticketing', 'venues'],
        ]);

        $node = $this->jsonLdOfType($this->get('/blog/'.$post->slug)->assertOk()->getContent(), 'BlogPosting');

        $this->assertNotNull($node, 'the BlogPosting block did not decode');
        $this->assertSame('The "Best" Way to Sell Tickets', $node['headline']);
        $this->assertSame('Organization', $node['author']['@type']);
        $this->assertSame('Event Schedule', $node['author']['name']);
        $this->assertSame('Organization', $node['publisher']['@type']);
        $this->assertSame(route('blog.show', $post->slug), $node['mainEntityOfPage']['@id']);
        $this->assertSame('ticketing, venues', $node['keywords']);
        $this->assertArrayHasKey('wordCount', $node);
    }

    public function test_the_index_blog_block_decodes_and_describes_each_post(): void
    {
        $post = $this->makePost([
            'title' => 'Quote " in a listing',
            'featured_image' => 'Literature.png',
        ]);

        $node = $this->jsonLdOfType($this->get('/blog')->assertOk()->getContent(), 'Blog');

        $this->assertNotNull($node, 'the Blog block did not decode');
        $this->assertCount(1, $node['blogPost']);

        $item = $node['blogPost'][0];
        $this->assertSame('Quote " in a listing', $item['headline']);
        $this->assertSame(route('blog.show', $post->slug), $item['url']);
        $this->assertSame($post->updated_at->toISOString(), $item['dateModified']);
        $this->assertSame('Organization', $item['author']['@type']);

        // The 1200x600 JPEG twin, not the 1.9 MB PNG the card renders: WhatsApp shows no preview
        // at all above roughly 300 KB. See BlogPost::socialImageUrl().
        $this->assertSame($post->socialImageUrl(), $item['image']);
        $this->assertSame(url('/images/headers/social/Literature.jpg'), $item['image']);
    }

    /**
     * json_encode HTML-escapes nothing, so the rewrite from {{ }} to an encoder traded one
     * injection for another: {{ }} could not produce a literal "</script>", and json_encode with
     * only the UNESCAPED flags can. A <script type="application/ld+json"> element is raw text, so
     * that string closes it and everything after it is markup the browser runs.
     *
     * The writers here are admins, so this is robustness rather than privilege escalation - but a
     * post title is quoted into three separate blocks (BlogPosting, the layout's BreadcrumbList,
     * and the index's Blog), and one of them is enough.
     */
    public function test_a_closing_script_tag_in_a_post_title_cannot_break_out_of_the_json_ld(): void
    {
        $payload = 'Tickets </script><script>alert(1)</script> Explained';

        $control = $this->makePost(['title' => 'Tickets Explained']);
        $hostile = $this->makePost(['title' => $payload]);

        $controlBody = $this->get('/blog/'.$control->slug)->assertOk()->getContent();
        $body = $this->get('/blog/'.$hostile->slug)->assertOk()->getContent();

        // The count is the whole point: an escaped payload adds no element, an unescaped one adds
        // the <script> it smuggled in.
        $this->assertSame(
            substr_count($controlBody, '<script'),
            substr_count($body, '<script'),
            'the title opened a script element of its own'
        );
        $this->assertStringNotContainsString('<script>alert(1)</script>', $body);

        // And the block still says what it is supposed to say, escaped rather than mangled.
        $posting = $this->jsonLdOfType($body, 'BlogPosting');
        $this->assertNotNull($posting, 'the BlogPosting block did not decode');
        $this->assertSame($payload, $posting['headline']);

        $breadcrumb = $this->jsonLdOfType($body, 'BreadcrumbList');
        $this->assertNotNull($breadcrumb, 'the BreadcrumbList did not decode');
        $this->assertSame($payload, end($breadcrumb['itemListElement'])['name']);

        // The listing quotes every title too, and it is a different payload builder.
        $indexBody = $this->get('/blog')->assertOk()->getContent();
        $blog = $this->jsonLdOfType($indexBody, 'Blog');
        $this->assertNotNull($blog, 'the Blog block did not decode');
        $this->assertContains($payload, array_column($blog['blogPost'], 'headline'));
    }

    /**
     * og:image:width and og:image:height used to be hardcoded 1200x630 for every page, but a blog
     * post shares its own 1200x600 twin. A declared size the bytes do not have gets the image
     * re-cropped by every scraper that trusts the tags.
     */
    public function test_the_og_image_tags_report_the_blog_twins_real_size(): void
    {
        $post = $this->makePost(['featured_image' => 'Literature.png']);

        $body = $this->get('/blog/'.$post->slug)->assertOk()->getContent();

        $this->assertStringContainsString(
            '<meta property="og:image" content="'.url('/images/headers/social/Literature.jpg').'">',
            $body
        );

        [$width, $height] = getimagesize(public_path('images/headers/social/Literature.jpg'));
        $this->assertSame([1200, 600], [$width, $height], 'the blog social twins are no longer 1200x600');

        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $body);
        $this->assertStringContainsString('<meta property="og:image:height" content="600">', $body);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function jsonLdOfType(string $html, string $type): ?array
    {
        preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $html, $matches);

        foreach ($matches[1] as $raw) {
            $block = json_decode($raw, true);

            if (is_array($block) && ($block['@type'] ?? null) === $type) {
                return $block;
            }
        }

        return null;
    }
}
