<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
use App\Services\Blog\BlogReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The blog's pages after their 2026-10 review. Each of these failed before it: the feed escaped
 * titles twice, page two of a tag forgot the tag, ?tag[]=x was a 500, the newest post could not
 * be saved with the picture it had, the index read the whole table twice a request, the card on
 * an audience post built class names the stylesheet never held (white text on a white page), a
 * post slugged for-* lit "Use Cases" in the header, and nothing a post can contain but a
 * paragraph and a bullet list had any style.
 */
class BlogPagesTest extends TestCase
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

    public function test_the_feed_escapes_a_title_once(): void
    {
        $this->makePost(['title' => 'Tours & Memberships', 'excerpt' => 'Bands & venues']);

        $xml = $this->get('/blog/feed')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Tours &amp; Memberships</title>', $xml);
        $this->assertStringNotContainsString('&amp;amp;', $xml);
        $this->assertNotFalse(simplexml_load_string($xml), 'the feed is not well-formed XML');
    }

    public function test_page_two_of_a_filter_keeps_the_filter(): void
    {
        foreach (range(1, 15) as $i) {
            $this->makePost(['tags' => ['door sales'], 'published_at' => now()->subDays($i)]);
        }

        $html = $this->get('/blog?tag='.urlencode('door sales'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~href="[^"]*\?(?:[^"]*&amp;)?tag=door[^"]*page=2|href="[^"]*page=2[^"]*tag=door~', $html, 'the link to page 2 dropped the tag');
    }

    public function test_an_array_for_the_tag_is_not_a_server_error(): void
    {
        $this->makePost();

        $status = $this->get('/blog?tag[]=x')->getStatusCode();

        $this->assertLessThan(500, $status);
    }

    public function test_the_newest_post_can_be_saved_with_the_picture_it_has(): void
    {
        $admin = User::factory()->create();
        DB::table('users')->where('id', $admin->id)->update(['is_admin' => 1]);
        $admin->refresh();
        $this->assertTrue($admin->isAdmin(), 'fixture: the user is a platform admin');

        $post = $this->makePost(['featured_image' => 'Synergy.png', 'published_at' => now()->subHour()]);

        $response = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin)
            ->from(route('blog.edit', $post->encodeId()))
            ->put(route('blog.update', $post->encodeId()), [
                'title' => 'A Post, edited',
                'content' => '<p>Body copy.</p>',
                'featured_image' => 'Synergy.png',
                'is_published' => 1,
            ]);

        $errors = session('errors')?->getBag('default')->toArray() ?? [];
        $this->assertSame([], $errors, 'the save was refused: '.json_encode($errors).' (went to '.$response->headers->get('Location').')');
        $response->assertRedirect(route('blog.admin.index'));

        $this->assertSame('A Post, edited', $post->fresh()->title);
    }

    public function test_the_index_does_not_read_every_post_for_its_sidebar(): void
    {
        foreach (range(1, 15) as $i) {
            $this->makePost(['tags' => ['t'.$i], 'published_at' => now()->subDays($i)]);
        }

        $wholeTable = 0;
        DB::listen(function ($query) use (&$wholeTable) {
            // A read of blog_posts that neither limits nor aggregates hydrates every post.
            if (preg_match('~^select \* from `blog_posts`~i', $query->sql) && ! preg_match('~\blimit\b~i', $query->sql)) {
                $wholeTable++;
            }
        });

        $this->get('/blog')->assertOk();

        $this->assertSame(0, $wholeTable, 'the index hydrated the whole table '.$wholeTable.' time(s)');
    }

    public function test_an_audience_post_does_not_build_class_names_for_its_card(): void
    {
        $this->makePost(['slug' => 'for-heritage-sites', 'title' => 'Heritage sites']);

        $html = $this->get('/blog/for-heritage-sites')->assertOk()->getContent();

        // Tailwind cannot see a class assembled at run time, so these never reach the stylesheet.
        $this->assertDoesNotMatchRegularExpression('~class="[^"]*\bfrom-(?:teal|cyan|fuchsia|violet|indigo|purple|pink|rose|amber|orange|emerald|sky)-\d00 to-~', $html);
        $this->assertStringContainsString('https://eventschedule.test/for-museums', str_replace(config('app.url'), 'https://eventschedule.test', $html), 'the card still leads to the audience page');
    }

    public function test_use_cases_is_not_lit_on_a_post_whose_slug_starts_with_for(): void
    {
        $this->makePost(['slug' => 'for-heritage-sites', 'title' => 'Heritage sites']);

        $html = $this->get('/blog/for-heritage-sites')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~<a href="[^"]*/use-cases"[^>]*>~', $html);
        preg_match('~<a href="[^"]*/use-cases" class="([^"]*)"~', $html, $m);
        $this->assertStringNotContainsString('!border-blue-600', $m[1] ?? '', 'Use Cases is highlighted on a blog post');
    }

    public function test_the_article_styles_what_a_post_can_contain(): void
    {
        $this->makePost(['slug' => 'styled', 'content' => '<p>One <a href="https://eventschedule.com/pricing">link</a>.</p><ol><li>Step</li></ol><table><thead><tr><th>A</th></tr></thead><tbody><tr><td>1</td></tr></tbody></table><blockquote><p>Copy me</p></blockquote>']);

        $html = $this->get('/blog/styled')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~\.blog-prose ol\s*\{[^}]*list-style(?:-type)?:\s*decimal~', $html, 'numbered lists have no numbers');
        $this->assertMatchesRegularExpression('~\.blog-prose a\s*\{[^}]*text-decoration(?:-line)?:\s*underline~', $html, 'links in the article are not marked');
        $this->assertMatchesRegularExpression('~\.blog-prose (?:table|th)[^{]*\{~', $html, 'tables are unstyled');
        $this->assertMatchesRegularExpression('~\.blog-prose blockquote\s*\{~', $html, 'quotes are unstyled');
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        DB::table('users')->where('id', $admin->id)->update(['is_admin' => 1]);

        return $admin->refresh();
    }

    public function test_the_first_paragraph_comes_before_the_picture_and_the_contents(): void
    {
        // Live, the first sentence of a post was 1,465px down a laptop screen: a hero, a card
        // and a stock picture stood in front of it.
        $this->makePost(['slug' => 'ordered', 'featured_image' => 'Synergy.png', 'content' => '<p>The answer, first.</p><h2>One</h2><p>a</p><h2>Two</h2><p>b</p><h2>Three</h2><p>c</p><h2>Four</h2><p>d</p>']);

        $html = $this->get('/blog/ordered')->assertOk()->getContent();

        $answer = strpos($html, 'The answer, first.');
        // The markup, not the class names: the page's own stylesheet names both too.
        $picture = strpos($html, '<figure class="blog-figure">');
        $contents = strpos($html, '<details class="blog-toc blog-toc-inline">');
        $this->assertNotFalse($picture);
        $this->assertTrue($answer < $picture && $picture < $contents && $contents < strpos($html, '<h2 id="s-one">One</h2>'));

        // The contents list links the ids the body's own headings carry.
        $this->assertStringContainsString('<a href="#s-three">Three</a>', $html);
    }

    public function test_a_section_is_a_page_once_it_has_posts_enough(): void
    {
        foreach (range(1, 2) as $i) {
            $this->makePost(['category' => 'selling-tickets', 'title' => 'Selling '.$i]);
        }
        $this->makePost(['category' => 'venues', 'title' => 'A venue post']);

        $thin = $this->get(route('blog.category', 'selling-tickets'))->assertOk()->getContent();
        $this->assertStringContainsString('noindex, follow', $thin, 'two posts are not yet a page for the index');
        $this->assertStringContainsString('Selling 1', $thin);
        $this->assertStringNotContainsString('A venue post', $thin);

        $this->makePost(['category' => 'selling-tickets', 'title' => 'Selling 3']);
        $full = $this->get(route('blog.category', 'selling-tickets'))->assertOk()->getContent();
        $this->assertStringNotContainsString('noindex', $full);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('blog.category', 'selling-tickets').'">', $full);
        $this->assertStringContainsString('<h1>Selling tickets', $full);

        // The front page offers the section as a chip only from then on, and no tag cloud at all.
        $front = $this->get('/blog')->assertOk()->getContent();
        $this->assertStringContainsString('href="'.route('blog.category', 'selling-tickets').'" class="blog-chip', $front);
        $this->assertStringNotContainsString('href="'.route('blog.category', 'venues').'" class="blog-chip', $front);
        $this->assertStringNotContainsString('?tag=', $front, 'the front page linked 641 tag pages');

        $this->get(route('blog.category', 'not-a-section'))->assertNotFound();
    }

    public function test_a_filtered_list_names_itself_and_stays_out_of_the_index(): void
    {
        $this->makePost(['title' => 'Door prices', 'tags' => ['pricing']]);

        foreach (['/blog?q=door', '/blog?tag=pricing'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('noindex, follow', $html, $url);
            // It used to be noindex AND canonical to the front page, two signals that disagree.
            $this->assertStringNotContainsString('<link rel="canonical" href="'.route('blog.index').'">', $html, $url);
            $this->assertStringContainsString('Door prices', $html, $url);
        }

        $this->get('/blog?q=zzzz')->assertOk()->assertSee(__('messages.no_posts_found'));
    }

    public function test_a_post_the_check_held_waits_in_the_admins_list_with_its_reason(): void
    {
        $this->makePost(['title' => 'A live post']);
        $this->makePost(['title' => 'A held post', 'is_published' => false, 'published_at' => null, 'held_reason' => "an em dash or an en dash\n2 links (wants 3 to 6)"]);

        $as = fn () => $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin());

        $all = $as()->get(route('blog.admin.index'))->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.blog_held_because').': an em dash or an en dash; 2 links (wants 3 to 6)', $all);
        $this->assertStringContainsString(trans_choice('messages.admin_alert_blog_posts_held', 1, ['count' => 1]), $all);

        $held = $as()->get(route('blog.admin.index', ['held' => 1]))->assertOk()->getContent();
        $this->assertStringContainsString('A held post', $held);
        $this->assertStringNotContainsString('A live post', $held);

        // A held post is not a public page, and publishing it is the admin's answer to the check.
        $post = BlogPost::where('title', 'A held post')->first();
        $this->get('/blog/'.$post->slug)->assertNotFound();

        $as()->put(route('blog.update', $post->encodeId()), ['title' => 'A held post', 'content' => '<p>Fixed.</p>', 'is_published' => 1])->assertSessionHasNoErrors();
        $this->assertNull($post->fresh()->held_reason);
        $this->assertSame(0, BlogPost::heldForReview()->count());
    }

    public function test_a_merged_post_redirects_and_leaves_every_list(): void
    {
        $kept = $this->makePost(['title' => 'The kept post', 'slug' => 'kept']);
        $gone = $this->makePost(['title' => 'The merged post', 'slug' => 'merged']);
        $admin = $this->admin();
        $as = fn () => $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        // Not into itself, and not into a post nobody can read.
        $as()->post(route('blog.merge', $gone->encodeId()), ['into' => 'merged'])->assertSessionHas('error');
        $as()->post(route('blog.merge', $gone->encodeId()), ['into' => 'no-such-post'])->assertSessionHas('error');
        $this->assertNull($gone->fresh()->redirect_slug);

        $stamp = $gone->fresh()->updated_at;
        $as()->post(route('blog.merge', $gone->encodeId()), ['into' => 'kept'])->assertSessionHas('message');
        $this->assertSame('kept', $gone->fresh()->redirect_slug);

        auth()->logout();
        $this->get('/blog/merged')->assertStatus(301)->assertRedirect(route('blog.show', 'kept'));
        $this->assertStringNotContainsString('The merged post', $this->get('/blog')->getContent());
        $this->assertStringNotContainsString('The merged post', $this->get('/blog/feed')->getContent());
        $this->assertStringContainsString('<p>Body copy.</p>', $gone->fresh()->content, 'merging keeps the text');

        // And back.
        $as()->post(route('blog.merge', $gone->encodeId()), ['into' => ''])->assertSessionHas('message');
        auth()->logout();
        $this->get('/blog/merged')->assertOk();
    }

    public function test_the_review_proposes_and_changes_no_post(): void
    {
        $words = fn (int $n) => '<p>'.trim(str_repeat('door ', $n)).'</p>';
        $read = $this->makePost(['title' => '5 Ways to Recruit & Retain Event Volunteers', 'slug' => 'volunteers-a', 'view_count' => 90, 'content' => $words(900)]);
        $twin = $this->makePost(['title' => 'Boost Event Success: Recruit, Train & Retain Volunteers', 'slug' => 'volunteers-b', 'view_count' => 4, 'content' => $words(300)]);
        $thin = $this->makePost(['title' => 'A short post about parking', 'slug' => 'parking', 'content' => $words(120)]);
        $before = BlogPost::orderBy('id')->get(['id', 'content', 'is_published', 'noindex', 'redirect_slug', 'updated_at'])->toArray();

        $admin = $this->admin();
        $as = fn () => $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        $as()->get(route('blog.review'))->assertOk()->assertSee(__('messages.blog_review_none'));
        $as()->post(route('blog.review.run'))->assertRedirect(route('blog.review'));

        // Of two posts on one subject the more read is kept and rewritten, the other merged into it.
        $this->assertSame(BlogReview::REWRITE, $read->fresh()->review['action']);
        $this->assertSame(BlogReview::MERGE, $twin->fresh()->review['action']);
        $this->assertSame('volunteers-a', $twin->fresh()->review['merge_into']);
        $this->assertSame(['volunteers-a' => '5 Ways to Recruit & Retain Event Volunteers'], $twin->fresh()->review['same_as']);
        // A thin post on its own is proposed for a rewrite, and the reason is on the page.
        $this->assertSame(BlogReview::REWRITE, $thin->fresh()->review['action']);
        $this->assertStringContainsString('too short', implode(' | ', $thin->fresh()->review['findings']));

        $page = $as()->get(route('blog.review'))->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.blog_review_same_subject').': 5 Ways to Recruit &amp; Retain Event Volunteers', $page);
        $this->assertStringContainsString('name="into" value="volunteers-a"', $page);

        // Nothing but the review itself was written: not the text, not what a visitor can reach,
        // not the date a search engine reads.
        $this->assertSame($before, BlogPost::orderBy('id')->get(['id', 'content', 'is_published', 'noindex', 'redirect_slug', 'updated_at'])->toArray());
    }

    public function test_the_models_reading_of_a_posts_claims_changes_the_proposal_only(): void
    {
        $post = $this->makePost(['title' => 'Speaker management for small events', 'content' => '<p>'.trim(str_repeat('door ', 900)).' Event Schedule manages speaker contracts.</p>']);
        BlogReview::run();
        $before = $post->fresh();

        \App\Utils\GeminiUtils::fakeResponses(fn ($prompt, $image, $purpose, $options) => ($options['stage'] ?? '') === 'claims'
            ? [['unsupported' => ['Event Schedule manages speaker contracts.'], 'product_can_help' => false]]
            : null);

        try {
            $this->assertTrue(BlogReview::checkClaims($post->fresh()));
        } finally {
            \App\Utils\GeminiUtils::fakeResponses(null);
        }

        $after = $post->fresh();
        $this->assertSame(['Event Schedule manages speaker contracts.'], $after->review['unsupported']);
        $this->assertSame(BlogReview::HIDE, $after->review['action'], 'a post the product can do nothing for is proposed for hiding');
        $this->assertFalse($after->noindex, 'proposing is not doing');
        $this->assertEquals($before->updated_at, $after->updated_at);

        // A second review keeps what the model said.
        BlogReview::run();
        $this->assertSame(BlogReview::HIDE, $post->fresh()->review['action']);
    }

    public function test_the_audience_posts_are_a_directory_by_kind_of_event(): void
    {
        $this->makePost(['slug' => 'for-solo-artists', 'title' => 'Solo artists: a title', 'category' => 'by-event']);
        $this->makePost(['slug' => 'for-jazz-musicians', 'title' => 'Jazz: a title', 'category' => 'by-event']);

        $html = $this->get(route('blog.category', 'by-event'))->assertOk()->getContent();

        // One line a post, under the page it belongs to: 150 of these were reachable only
        // through 23 pages of pagination.
        $this->assertStringContainsString('<h2>Musicians</h2>', $html);
        $this->assertStringContainsString('href="'.route('blog.show', 'for-solo-artists').'">Solo Artists</a>', $html);
        $this->assertStringNotContainsString('class="blog-pager"', $html);

        // An audience post is followed by its own audience's posts, and its card names its page.
        $post = $this->get('/blog/for-solo-artists')->assertOk()->getContent();
        $this->assertStringContainsString('More for Musicians', $post);
        $this->assertStringContainsString('Jazz: a title', $post);
        $this->assertStringContainsString('For Musicians', $post);
    }

    public function test_a_crawler_reading_a_post_is_not_a_view(): void
    {
        $post = $this->makePost(['slug' => 'counted']);
        $browser = [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0 Safari/537.36',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
        ];

        // Every fetch used to count, so the admin's "views" said how often a post was crawled.
        $this->get('/blog/counted', ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'] + $browser)->assertOk();
        $this->get('/blog/counted', ['HTTP_ACCEPT_LANGUAGE' => ''] + $browser)->assertOk();
        $this->assertSame(0, (int) $post->fresh()->view_count);

        $this->get('/blog/counted', $browser)->assertOk();
        $this->assertSame(1, (int) $post->fresh()->view_count);
    }

    public function test_a_section_page_is_in_the_blogs_sitemap_once_it_is_a_page(): void
    {
        \Illuminate\Support\Facades\Cache::flush();

        foreach (range(1, 3) as $i) {
            $this->makePost(['category' => 'selling-tickets', 'title' => 'Selling '.$i]);
        }
        $this->makePost(['category' => 'venues', 'title' => 'One venue post']);

        $xml = $this->get('/sitemap-blog-1.xml')->assertOk()->streamedContent();

        $this->assertStringContainsString('<loc>'.route('blog.category', 'selling-tickets').'</loc>', $xml);
        $this->assertStringNotContainsString(route('blog.category', 'venues'), $xml, 'a section the page itself keeps out of the index is listed');
        $this->assertSame(1, substr_count($xml, route('blog.category', 'selling-tickets')));
    }
}
