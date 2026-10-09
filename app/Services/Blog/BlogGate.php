<?php

namespace App\Services\Blog;

use App\Models\BlogPost;

/**
 * The check a written post must pass before it is published with nobody reading it.
 *
 * Everything here used to be a sentence in the prompt, and a sentence in a prompt is a request:
 * "Never use em dashes" left em dashes in 4 of the 20 newest posts, "50-60 characters" left 47
 * of 222 titles over 60, and a list of banned phrases moved the habit to the phrases beside
 * them. A rule that matters is checked here, and a post that fails is held as a draft with the
 * reason (BlogWriter::store()), where an admin can fix it or throw it away.
 */
class BlogGate
{
    /**
     * Words that mark copy written on autopilot, as the patterns they are counted by. The same
     * list is in the prompts (config/ai_prompts.php); this is the half that is enforced. It is
     * a density, not a ban: one "vital" in a thousand words is a word, six are a habit.
     */
    public const STOCK = [
        'seamless', 'robust', 'streamlin', 'leverag', 'elevat', 'unlock', 'unleash', 'empower', 'supercharg',
        'effortless', 'game-chang', 'holistic', 'vibrant', 'thriving', 'foster', 'delve', 'landscape',
        'journey', 'tapestry', 'crucial', 'vital', 'pivotal', 'transform', 'ensure', 'in today', 'imagine',
        'whether you', 'in conclusion', 'furthermore', 'moreover', 'it\'s important to', 'it is important to',
        'when it comes to', 'next level',
    ];

    /** Stock words allowed in a thousand. */
    public const STOCK_PER_THOUSAND = 3.0;

    public const MIN_LINKS = 3;

    public const MAX_LINKS = 6;

    /** The product named as a category: the mark of a post written without its facts. */
    private const HEDGE = '~(?:platforms?|tools?|systems?|software|solutions?)\s*,?\s*(?:like|such as)\s+Event Schedule|Event Schedule platforms~i';

    /** A sentence that is plainly a worked example may carry any number it likes. */
    private const EXAMPLE = '~\b(?:say|suppose|example|for instance|if you|imagine you)\b~i';

    /**
     * Remove every link whose address is not on the list, keeping its words.
     *
     * The post is the model's own HTML, not yet through the purifier, so the walk is over whole
     * <a ...>...</a> elements and never inside another tag's attributes.
     *
     * @param  list<string>  $allowed
     */
    public static function unwrapLinks(string $html, array $allowed): string
    {
        $ok = array_flip(array_map([BlogLinks::class, 'normalise'], $allowed));

        return preg_replace_callback('~<a\b[^>]*>(.*?)</a>~is', function ($match) use ($ok) {
            if (preg_match('~\bhref="([^"]*)"~i', $match[0], $href) && isset($ok[BlogLinks::normalise($href[1])])) {
                return $match[0];
            }

            return $match[1];
        }, $html) ?? $html;
    }

    /**
     * Why this post may not be published as it stands. An empty list means it may.
     *
     * $context: allowed (list of addresses), figures (the outside numbers the brief supplied),
     * parent (an address that must be linked), min_words, skip_existing (true when rewriting a
     * post that is already in the table, which would otherwise be a duplicate of itself).
     *
     * @param  array<string, mixed>  $post
     * @param  array<string, mixed>  $context
     * @return list<string>
     */
    public static function failures(array $post, array $context = []): array
    {
        $html = (string) ($post['content'] ?? '');
        $text = self::text($html);
        $title = trim((string) ($post['title'] ?? ''));
        $description = trim((string) ($post['description'] ?? $post['meta_description'] ?? ''));
        $failures = [];

        // Links
        $allowed = array_flip(array_map([BlogLinks::class, 'normalise'], (array) ($context['allowed'] ?? array_keys(BlogLinks::targets()))));
        preg_match_all('~<a\b[^>]*\bhref="([^"]*)"~i', $html, $found);
        $links = array_map([BlogLinks::class, 'normalise'], $found[1]);
        $stray = array_values(array_filter($links, fn ($url) => ! isset($allowed[$url])));

        if ($stray !== []) {
            $failures[] = 'links to an address that is not on the list: '.implode(', ', array_slice($stray, 0, 3));
        }

        $home = array_filter($links, [BlogLinks::class, 'isHome']);
        $pages = array_unique(array_diff($links, $home));

        if (count($links) < self::MIN_LINKS || count($links) > self::MAX_LINKS) {
            $failures[] = count($links).' links (wants '.self::MIN_LINKS.' to '.self::MAX_LINKS.')';
        }
        if (count($home) > 1) {
            $failures[] = count($home).' links to the home page';
        }
        if (count($pages) < 2) {
            $failures[] = 'fewer than two pages linked besides the home page';
        }
        if (! empty($context['parent']) && ! in_array(BlogLinks::normalise((string) $context['parent']), $links, true)) {
            $failures[] = 'does not link '.$context['parent'];
        }

        // Shape
        if (preg_match('~[\x{2014}\x{2013}]~u', $text)) {
            $failures[] = 'an em dash or an en dash';
        }
        if (preg_match('~\*\*|\]\(https?:~', $html)) {
            $failures[] = 'Markdown inside the HTML';
        }
        if (stripos($html, '<h1') !== false) {
            $failures[] = 'an h1 in the body';
        }
        if (substr_count(strtolower($html), '<h2') < 3) {
            $failures[] = 'fewer than three sections';
        }

        $firstH2 = stripos($html, '<h2');
        $firstH3 = stripos($html, '<h3');

        if ($firstH3 !== false && ($firstH2 === false || $firstH3 < $firstH2)) {
            $failures[] = 'an h3 before the first h2';
        }
        if (! preg_match('~^\s*<p\b~i', $html)) {
            $failures[] = 'does not open on a paragraph';
        }

        // Title and description
        $titleLength = mb_strlen($title);

        if ($titleLength < 20 || $titleLength > 60) {
            $failures[] = 'the title is '.$titleLength.' characters';
        }
        if (preg_match('~^\s*(?:Mastering|Master|Ultimate|Boost|Unlock|Unleash|Supercharge|Effortless)\b~i', $title)) {
            $failures[] = 'the title opens on a stock word';
        }

        $descriptionLength = mb_strlen($description);

        if ($descriptionLength < 110 || $descriptionLength > 160) {
            $failures[] = 'the description is '.$descriptionLength.' characters';
        }

        // Language
        $words = max(1, BlogPost::wordCountOf($html));
        $stock = 0;

        foreach (self::STOCK as $pattern) {
            $stock += preg_match_all('~'.preg_quote($pattern, '~').'~i', $text);
        }

        if ($stock * 1000 / $words > self::STOCK_PER_THOUSAND) {
            $failures[] = $stock.' stock words in '.$words;
        }
        if (preg_match(self::HEDGE, $text)) {
            $failures[] = 'names the product as a category ("platforms like Event Schedule")';
        }

        // Invented evidence: a percentage the brief did not supply, outside a worked example.
        preg_match_all('~\d+(?:\.\d+)?\s?%~', (string) ($context['figures'] ?? ''), $given);
        $given = array_flip(array_map(fn ($figure) => str_replace(' ', '', $figure), $given[0]));

        // A width in a snippet the post quotes (width="100%") is markup, not a statistic: the
        // first real run was sent back to its editor over an embed code.
        $prose = preg_replace('~=\s*["\']?\d+(?:\.\d+)?%~', '=', $text) ?? $text;

        foreach (preg_split('~(?<=[.!?])\s+~', $prose) ?: [] as $sentence) {
            if (! preg_match_all('~\d+(?:\.\d+)?\s?%~', $sentence, $figures) || preg_match(self::EXAMPLE, $sentence)) {
                continue;
            }

            foreach ($figures[0] as $figure) {
                if (! isset($given[str_replace(' ', '', $figure)])) {
                    $failures[] = 'a percentage nobody supplied: '.mb_substr(trim($sentence), 0, 110);
                    break 2;
                }
            }
        }

        // What it says about the product
        $facts = array_flip(BlogFacts::ids());

        foreach ((array) ($post['product_claims'] ?? []) as $claim) {
            $cited = strtolower(trim((string) ($claim['fact_id'] ?? ''), "[] \t\n"));

            if ($cited !== 'guide' && ! isset($facts[$cited])) {
                $failures[] = 'a claim about the product cites no fact: '.mb_substr(trim((string) ($claim['sentence'] ?? '')), 0, 90);
                break;
            }
        }

        // Length, and a title the blog already has (the check both generators already ran).
        if (empty($context['skip_existing'])) {
            $existing = BlogPost::qualityGateFailure(['title' => $title, 'content' => $html]);

            if ($existing !== null) {
                $failures[] = $existing;
            }
        } elseif ($words < BlogPost::QUALITY_MIN_WORDS) {
            $failures[] = "too short ({$words} words, minimum ".BlogPost::QUALITY_MIN_WORDS.')';
        }

        return $failures;
    }

    /** The post as running text, tags gone and entities decoded. */
    public static function text(string $html): string
    {
        $text = html_entity_decode(preg_replace('~<[^>]*>~', ' ', $html) ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('~\s+~u', ' ', $text) ?? '');
    }

    /**
     * Whether a planned topic is one the blog has already written, by the words its title and
     * search share with a published title. similar_text() on titles could not see this: the six
     * posts about recruiting volunteers are all under 80% alike as strings.
     *
     * @param  iterable<string>  $existingTitles
     */
    public static function repeats(string $topic, string $query, iterable $existingTitles): ?string
    {
        $mine = BlogLinks::significantWords($topic.' '.$query);

        if (count($mine) < 2) {
            return null;
        }

        foreach ($existingTitles as $title) {
            $theirs = BlogLinks::significantWords((string) $title);
            $shared = count(array_intersect($mine, $theirs));
            $either = count(array_unique(array_merge($mine, $theirs)));

            // Most of the shorter title's words, and at least three of them.
            if ($shared >= 3 && $either > 0 && $shared / min(count($mine), max(1, count($theirs))) >= 0.75) {
                return (string) $title;
            }
        }

        return null;
    }
}
