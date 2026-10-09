<?php

namespace App\Services\Blog;

use App\Models\BlogPost;

/**
 * The pages a blog post may link to.
 *
 * The old prompt asked for "exactly 2 internal links" and got them: every one of the twenty
 * newest posts linked the homepage and at most one audience page, and nothing else, with about
 * 170 marketing pages and the whole user guide to choose from. The model is now handed the list,
 * and BlogGate removes any link that is not on it: told to copy addresses character for
 * character it still wrote https://eventschedule.eventschedule.com/ticket-fee-calculator.
 */
class BlogLinks
{
    /** Marketing pages that are not destinations for a reader of a how-to post. */
    private const SKIP = ['/', '/faq', '/contact', '/why-create-account', '/examples', '/browse', '/saas', '/replace', '/compare', '/use-cases', '/about'];

    /**
     * Guide pages, and what each is about. Pages of their own only: /docs/polls and
     * /docs/fan-content are redirects into the event guide, and a post does not link a hop.
     */
    private const GUIDE = [
        'getting-started' => 'guide: create an account and a first schedule',
        'creating-events' => 'guide: the event form, recurring events, visibility',
        'tickets' => 'guide: ticket types, payment, promo codes, add-ons, check-in',
        'sharing' => 'guide: sharing a schedule, embedding the calendar, QR codes',
        'newsletters' => 'guide: newsletters and subscribers',
        'subscriptions' => 'guide: passes and memberships',
        'gift-cards' => 'guide: gift cards',
        'appointments' => 'guide: appointment booking',
        'allocated-seating' => 'guide: reserved seating and the box office',
        'event-graphics' => 'guide: event graphics for social media',
        'analytics' => 'guide: analytics',
        'ai-import' => 'guide: importing events from text, links and flyers',
        'boost' => 'guide: Facebook and Instagram ads',
    ];

    /** The marketing site's address, without a trailing slash or a www. (a redirect hop). */
    public static function base(): string
    {
        return (string) preg_replace('~^(https?://)www\.~i', '$1', rtrim(marketing_url(), '/'));
    }

    /**
     * Every page a post may link to: address => what the page is about.
     *
     * @return array<string, string>
     */
    public static function targets(): array
    {
        $base = self::base();
        $targets = [];

        foreach ((array) config('marketing_keywords', []) as $path => $entry) {
            // A "replace Calendly" page is a comparison for someone leaving that tool, not a
            // place a post about running events sends its reader.
            if (in_array($path, self::SKIP, true) || str_ends_with($path, '-replacement')) {
                continue;
            }

            $targets[$base.$path] = (string) ($entry['keyword'] ?? '');
        }

        foreach (self::GUIDE as $page => $about) {
            if (BlogGuide::exists($page)) {
                $targets[$base.'/docs/'.$page] = $about;
            }
        }

        $targets[$base] = 'home page';

        return $targets;
    }

    /** @param  array<string, string>  $targets */
    public static function forPrompt(array $targets): string
    {
        $lines = [];

        foreach ($targets as $url => $about) {
            $lines[] = $url.'  |  '.$about;
        }

        return implode("\n", $lines);
    }

    public static function isHome(string $url): bool
    {
        return self::normalise($url) === self::normalise(self::base());
    }

    /** An address as it is compared: no trailing slash, no www., lower-case scheme and host. */
    public static function normalise(string $url): string
    {
        $url = rtrim(trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')), '/');

        return (string) preg_replace_callback('~^(https?://)(www\.)?([^/]+)~i', fn ($m) => strtolower($m[1].$m[3]), $url);
    }

    /**
     * Published posts close to a topic, for "already on the blog": the posts named first (an
     * audience's other posts, the planner's two closest), then the titles sharing the most
     * words with the topic. Titles only: a summary shares "event" and "schedule" with everything.
     *
     * @param  list<string>  $firstSlugs
     * @return array<string, string> address => title
     */
    public static function nearby(string $topic, array $firstSlugs = [], ?string $exceptSlug = null, int $limit = 6): array
    {
        $posts = BlogPost::published()->where('noindex', false)->get(['id', 'title', 'slug']);
        $words = self::significantWords($topic);
        $ranked = [];

        foreach ($posts as $post) {
            if ($post->slug === $exceptSlug) {
                continue;
            }

            $first = array_search($post->slug, $firstSlugs, true);
            $shared = count(array_intersect($words, self::significantWords((string) $post->title)));

            if ($first !== false || $shared >= 2) {
                $ranked[] = [$first === false ? 1 : 0, $first === false ? -$shared : $first, $post];
            }
        }

        usort($ranked, fn ($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        $nearby = [];

        foreach (array_slice($ranked, 0, $limit) as [, , $post]) {
            $nearby[blog_url('/'.$post->slug)] = (string) $post->title;
        }

        return $nearby;
    }

    /**
     * The words of a title that say what it is about.
     *
     * @return list<string>
     */
    public static function significantWords(string $text): array
    {
        static $common = ['the', 'and', 'for', 'with', 'your', 'you', 'how', 'can', 'from', 'that', 'this', 'are', 'event', 'events', 'schedule', 'schedules', 'using', 'use', 'their', 'into', 'more', 'ways', 'tips', 'guide', 'online', 'easily', 'best', 'without', 'what', 'when', 'one'];

        preg_match_all('~[a-z][a-z-]{2,}~', mb_strtolower($text), $found);

        $words = [];

        foreach ($found[0] as $word) {
            // A crude stem, so "tickets" meets "ticket" and "selling" meets "sell".
            $stem = (string) preg_replace('~(?:ing|ers|er|es|s)$~', '', $word);
            $stem = strlen($stem) >= 3 ? $stem : $word;

            if (! in_array($word, $common, true)) {
                $words[$stem] = true;
            }
        }

        return array_keys($words);
    }
}
