<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use App\Utils\GeminiUtils;
use Illuminate\Support\Collection;

/**
 * The review of the posts already published: what the check finds in each, which posts answer
 * the same question, and what is proposed for it. It reads and proposes; it changes no post's
 * text, address or visibility. What it finds is kept on the post (`review`) for the list at
 * /admin/blog/review, where merging, rewriting and hiding are each somebody's own decision.
 *
 * Why it exists: the 222 posts written before 2026-10 came from a prompt that knew one fact
 * about the product, so most carry two links, several carry claims the product cannot back,
 * and whole subjects were written six times over.
 */
class BlogReview
{
    public const KEEP = 'keep';

    public const REWRITE = 'rewrite';

    public const MERGE = 'merge';

    public const HIDE = 'noindex';

    /**
     * Review every published post and store what was found. Returns how many were reviewed.
     */
    public static function run(): int
    {
        $posts = BlogPost::published()->get();
        $allowed = array_merge(array_keys(BlogLinks::targets()), $posts->map(fn ($post) => blog_url('/'.$post->slug))->all());
        $clusters = self::clusters($posts);

        foreach ($posts as $post) {
            $found = self::assess($post, $allowed, $clusters[$post->id] ?? [], $posts->keyBy('id'));

            // The model's reading of the post's claims, when it has one, survives a re-run.
            if (isset($post->review['unsupported'])) {
                $found['unsupported'] = $post->review['unsupported'];
                $found['helps'] = $post->review['helps'] ?? null;
                $found = self::propose($found, $post);
            }

            // Not an edit: updated_at is the post's dateModified and its sitemap lastmod.
            BlogPost::withoutTimestamps(fn () => $post->forceFill(['review' => $found])->save());
        }

        return $posts->count();
    }

    /**
     * What the check finds in one post, the posts it repeats, and what is proposed.
     *
     * @param  list<string>  $allowed
     * @param  list<int>  $sameAs  ids of the posts on the same subject
     * @param  Collection<int, BlogPost>  $byId
     * @return array<string, mixed>
     */
    public static function assess(BlogPost $post, array $allowed, array $sameAs, Collection $byId): array
    {
        $findings = BlogGate::failures([
            'title' => $post->title,
            'content' => $post->renderedContent(),
            'description' => $post->meta_description,
        ], ['allowed' => $allowed, 'skip_existing' => true]);

        $same = [];
        foreach ($sameAs as $id) {
            if (isset($byId[$id])) {
                $same[$byId[$id]->slug] = $byId[$id]->title;
            }
        }

        return self::propose([
            'words' => $post->wordCount(),
            'findings' => $findings,
            'same_as' => $same,
            'reviewed_at' => now()->toIso8601String(),
        ], $post, $byId, $sameAs);
    }

    /**
     * The proposal for a post, from what was found.
     *
     * - A post the product can do nothing for is proposed for hiding from search (noindex).
     * - Of several open-topic posts on one subject, the most read is kept and rewritten and the
     *   others are merged into it. An audience post is never merged: each has its own audience's
     *   card on a marketing page pointing at its address.
     * - A thin post, or one with claims the facts do not support, is proposed for a rewrite.
     *
     * @param  array<string, mixed>  $found
     * @param  Collection<int, BlogPost>|null  $byId
     * @param  list<int>  $sameAs
     * @return array<string, mixed>
     */
    private static function propose(array $found, BlogPost $post, ?Collection $byId = null, array $sameAs = []): array
    {
        $found['action'] = $found['action'] ?? self::KEEP;
        $found['merge_into'] = $found['merge_into'] ?? null;

        if ($byId !== null) {
            $found['action'] = self::KEEP;
            $found['merge_into'] = null;
            $audience = $post->categoryKey() === 'by-event';

            if ($sameAs !== [] && ! $audience) {
                $rivals = collect($sameAs)->map(fn ($id) => $byId[$id] ?? null)->filter()
                    ->reject(fn ($other) => $other->categoryKey() === 'by-event');
                $keeper = $rivals->push($post)->sortByDesc(fn ($p) => [(int) $p->view_count, $p->wordCount(), -$p->id])->first();

                if ($keeper->id !== $post->id) {
                    $found['action'] = self::MERGE;
                    $found['merge_into'] = $keeper->slug;
                } else {
                    $found['action'] = self::REWRITE;
                }
            } elseif ($sameAs !== [] || $found['words'] < BlogPost::QUALITY_MIN_WORDS || count($found['findings']) >= 3) {
                $found['action'] = self::REWRITE;
            }
        }

        if (($found['helps'] ?? null) === false && $found['action'] !== self::MERGE) {
            $found['action'] = self::HIDE;
        } elseif (! empty($found['unsupported']) && $found['action'] === self::KEEP) {
            $found['action'] = self::REWRITE;
        }

        return $found;
    }

    /**
     * Posts on the same subject, by the words their titles share: post id => ids of the others.
     *
     * @param  Collection<int, BlogPost>  $posts
     * @return array<int, list<int>>
     */
    public static function clusters(Collection $posts): array
    {
        $clusters = [];
        $list = $posts->values();

        foreach ($list as $i => $post) {
            foreach ($list as $j => $other) {
                if ($i !== $j && BlogGate::repeats((string) $post->title, '', [(string) $other->title]) !== null) {
                    $clusters[$post->id][] = $other->id;
                    $clusters[$other->id][] = $post->id;
                }
            }
        }

        return array_map(fn ($ids) => array_values(array_unique($ids)), $clusters);
    }

    /**
     * Ask the model which of a post's sentences about the product the facts do not support, and
     * whether the product can help with the post's subject at all. One call a post; the answer
     * is stored on the post and the proposal redrawn. Returns false when no answer came back.
     */
    public static function checkClaims(BlogPost $post): bool
    {
        $prompt = "PRODUCT FACTS (each line starts with its id in square brackets)\n".BlogFacts::forPrompt()
            ."\n\nPOST\nTitle: ".$post->title."\n\n".BlogGate::text($post->renderedContent());

        $system = 'You check a blog post of Event Schedule against what the product really does. '
            .'List every sentence of the post that says, or clearly implies, that Event Schedule does or includes something PRODUCT FACTS does not state, quoting each sentence exactly. '
            .'A sentence about events in general is not a claim about the product. '
            .'Then say whether the product, as PRODUCT FACTS describes it, can do anything useful for the subject the post is about.';

        $answer = GeminiUtils::structured($prompt, [
            'stage' => 'claims',
            'system' => $system,
            'temperature' => 0.1,
            'model' => config('services.google.gemini_blog_model'),
            'timeout' => 60,
            'schema' => [
                'type' => 'OBJECT',
                'properties' => [
                    'unsupported' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
                    'product_can_help' => ['type' => 'BOOLEAN'],
                ],
                'required' => ['unsupported', 'product_can_help'],
            ],
        ]);

        if ($answer === null || ! is_array($answer['unsupported'] ?? null)) {
            return false;
        }

        $review = (array) ($post->review ?? []) + ['words' => $post->wordCount(), 'findings' => [], 'same_as' => [], 'action' => self::KEEP, 'merge_into' => null];
        $review['unsupported'] = array_values(array_map(fn ($sentence) => mb_substr(trim((string) $sentence), 0, 300), array_slice($answer['unsupported'], 0, 12)));
        $review['helps'] = (bool) ($answer['product_can_help'] ?? true);
        $review['claims_checked_at'] = now()->toIso8601String();

        BlogPost::withoutTimestamps(fn () => $post->forceFill(['review' => self::propose($review, $post)])->save());

        return true;
    }
}
