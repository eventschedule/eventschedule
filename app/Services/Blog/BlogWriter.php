<?php

namespace App\Services\Blog;

use App\Models\BlogPost;
use App\Services\UsageTrackingService;
use App\Utils\GeminiUtils;
use Illuminate\Support\Facades\Cache;

/**
 * Writes one blog post: a brief, a draft and an edit, then the check in code (BlogGate).
 *
 * Both scheduled generators and the admin's button come through here, so there is one way a
 * post is written and one check on it. What passes is published; what does not is kept as a
 * draft with the reason, where the old generators threw a rejected post away.
 *
 * Three calls, not one. A single call asked to plan, write and police itself did none of the
 * three well: the trial that led to this class ran the same topics through both, and the single
 * call linked the homepage twice, hedged about the product in every post and never named a plan.
 */
class BlogWriter
{
    public const SOURCE_DAILY = 'daily';

    public const SOURCE_AUDIENCE = 'audience';

    public const SOURCE_ADMIN = 'admin';

    /** Set while a post is being written, so two generators in one tick cannot both write. */
    public const BUSY_KEY = 'blog:writing';

    private const FORMATS = ['how-to', 'checklist', 'comparison', 'explainer', 'template'];

    private float $startedAt = 0.0;

    /** @var list<array{stage: string, seconds: float, ok: bool}> */
    private array $calls = [];

    /**
     * Write a post.
     *
     * $job:
     *   topic     what to write about; left out, the planner chooses (the daily post)
     *   audience  for an audience post: ['slug', 'name', 'page', 'title', 'features' => [...]]
     *   except    the slug of the post being rewritten, kept out of "already on the blog"
     *
     * Returns ['post' => fields or null, 'failures' => why it may not be published,
     * 'brief' => ..., 'draft' => ..., 'error' => why nothing was written, 'calls' => [...]].
     *
     * The three steps are public too (begin, draft, finish), each one model call: the admin's
     * Generate button runs them as three requests, because together they take longer than a
     * web request may, and a queued job would hold every ticket email behind it (the queue is
     * drained inside the scheduler's own process).
     *
     * @param  array<string, mixed>  $job
     * @return array<string, mixed>
     */
    public function write(array $job = []): array
    {
        $this->startedAt = microtime(true);
        $this->calls = [];

        $state = $this->begin($job);

        if ($state === null) {
            return $this->result(error: isset($job['topic']) ? 'no brief came back' : 'the planner returned no topic');
        }

        $draft = $this->draft($state);

        if ($draft === null) {
            return $this->result(brief: $state['brief'], error: 'no draft came back');
        }

        $finished = $this->finish($state, $draft);

        if ($finished === null) {
            return $this->result(brief: $state['brief'], draft: $draft, error: 'no edit came back');
        }

        UsageTrackingService::track(UsageTrackingService::GEMINI_BLOG);

        return $this->result(post: $finished['post'], failures: $finished['failures'], brief: $state['brief'], draft: $draft);
    }

    /**
     * Step one: the brief. Returns what the next two steps need, or null when none came back.
     *
     * @param  array<string, mixed>  $job
     * @return array{topic: string, brief: array<string, mixed>, audience: ?array<string, mixed>, except: ?string, rewriting: bool}|null
     */
    public function begin(array $job = []): ?array
    {
        $this->startedAt = $this->startedAt ?: microtime(true);
        $audience = $job['audience'] ?? null;
        $brief = isset($job['topic']) ? $this->brief((string) $job['topic'], $audience) : $this->plan();

        if ($brief === null) {
            return null;
        }

        return [
            'topic' => (string) ($job['topic'] ?? $brief['topic']),
            'brief' => $brief,
            'audience' => $audience,
            'except' => $job['except'] ?? ($audience['slug'] ?? null),
            'rewriting' => isset($job['except']),
        ];
    }

    /**
     * Step two: the draft, with any link that is not on the list already unwrapped.
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|null
     */
    public function draft(array $state): ?array
    {
        $this->startedAt = $this->startedAt ?: microtime(true);
        $handed = $this->handed($state);
        $draft = $this->ask('draft', $handed['user'], (string) config('ai_prompts.blog_writer_system'), $this->postSchema(), 0.7);

        if ($draft === null || trim((string) ($draft['content'] ?? '')) === '') {
            return null;
        }

        $draft['content'] = BlogGate::unwrapLinks((string) $draft['content'], $handed['allowed']);

        return $draft;
    }

    /**
     * Step three: the edit, the check, and one more edit with the check's own findings if it
     * still fails. Returns the post and why it may not be published (nothing, when it may).
     *
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $draft
     * @return array{post: array<string, mixed>, failures: list<string>}|null
     */
    public function finish(array $state, array $draft): ?array
    {
        $this->startedAt = $this->startedAt ?: microtime(true);
        $handed = $this->handed($state);
        $context = ['allowed' => $handed['allowed'], 'figures' => $handed['figures'], 'parent' => $handed['parent'], 'skip_existing' => ! empty($state['rewriting'])];

        $post = $this->edit($handed['user'], $draft);

        if ($post === null) {
            return null;
        }

        $post['content'] = BlogGate::unwrapLinks((string) $post['content'], $handed['allowed']);
        $failures = BlogGate::failures($post, $context);

        if ($failures !== []) {
            $again = $this->edit($handed['user'], $post, $failures);

            if ($again !== null) {
                $again['content'] = BlogGate::unwrapLinks((string) $again['content'], $handed['allowed']);
                $failuresAgain = BlogGate::failures($again, $context);

                if (count($failuresAgain) <= count($failures)) {
                    $post = $again;
                    $failures = $failuresAgain;
                }
            }
        }

        // The editor's own doubts hold a post too: it is the last reader.
        foreach ((array) ($post['remaining'] ?? []) as $doubt) {
            if (trim((string) $doubt) !== '') {
                $failures[] = 'the editor asks for a look: '.mb_substr(trim((string) $doubt), 0, 200);
            }
        }
        if (($post['verdict'] ?? 'publish') !== 'publish' && $failures === []) {
            $failures[] = 'the editor would not put its name to it';
        }

        $post['category'] = ! empty($state['audience']) ? 'by-event' : $this->categoryKey((string) ($post['category'] ?? ''));
        $post['primary_query'] = (string) $state['brief']['primary_query'];

        return ['post' => $post, 'failures' => array_values(array_unique($failures))];
    }

    /**
     * What the writer and the editor are both handed for this post: the filled prompt, the
     * addresses that may be linked, the audience's own page, and the outside figures allowed.
     *
     * @param  array<string, mixed>  $state
     * @return array{user: string, allowed: list<string>, parent: ?string, figures: string}
     */
    private function handed(array $state): array
    {
        $brief = $state['brief'];
        $audience = $state['audience'] ?? null;
        $topic = (string) $state['topic'];
        $facts = BlogFacts::known((array) ($brief['capabilities'] ?? []));
        $targets = BlogLinks::targets();
        $nearby = BlogLinks::nearby($topic.' '.$brief['primary_query'], $this->firstNearby($brief, $audience), $state['except'] ?? null);
        $parent = $audience ? BlogLinks::base().'/'.ltrim((string) $audience['page'], '/') : null;
        $figures = $this->figures($brief);

        $user = strtr((string) config('ai_prompts.blog_writer_user'), [
            ':topic' => $topic,
            ':primary_query' => (string) $brief['primary_query'],
            ':reader' => (string) $brief['reader'],
            ':format' => (string) $brief['format'],
            ':angle' => (string) $brief['angle'],
            ':questions' => $this->questions($brief, $facts),
            ':min_words' => (string) BlogPost::QUALITY_MIN_WORDS,
            ':max_words' => '1400',
            ':date' => now()->format('j F Y'),
            ':facts' => BlogFacts::forPrompt(),
            ':figures' => $figures !== '' ? $figures : '(none: use no outside numbers; a worked example must be plainly an example)',
            ':guide' => BlogGuide::excerpts($facts),
            ':parent_rule' => $parent ? 'One of them must be '.$parent.'. ' : '',
            ':links' => BlogLinks::forPrompt($targets),
            ':nearby' => $nearby === [] ? '(nothing close)' : BlogLinks::forPrompt($nearby),
            ':categories' => implode(', ', array_column(BlogPost::CATEGORIES, 'name')),
        ]);

        return [
            'user' => $user,
            'allowed' => array_merge(array_keys($targets), array_keys($nearby)),
            'parent' => $parent,
            'figures' => $figures,
        ];
    }

    /**
     * Save what write() returned: published when nothing failed, held as a draft otherwise.
     * An admin's own post is always a draft; publishing it is the admin's decision.
     *
     * @param  array<string, mixed>  $written
     */
    public function store(array $written, string $source, ?string $slug = null): ?BlogPost
    {
        $post = $written['post'] ?? null;

        if (! is_array($post)) {
            return null;
        }

        $failures = (array) ($written['failures'] ?? []);
        $publish = $failures === [] && $source !== self::SOURCE_ADMIN;
        $category = (string) ($post['category'] ?? 'planning');

        return BlogPost::create(array_filter([
            'title' => trim((string) $post['title']),
            'slug' => $slug,
            'content' => (string) $post['content'],
            'excerpt' => trim((string) ($post['excerpt'] ?? '')) ?: null,
            'tags' => [],
            'meta_title' => trim((string) $post['title']),
            'meta_description' => trim((string) ($post['description'] ?? '')) ?: null,
            'featured_image' => $this->picture($category),
            'category' => $category,
            'primary_query' => mb_substr((string) ($post['primary_query'] ?? ''), 0, 250) ?: null,
            'faq' => $this->faq($post),
            'source' => $source,
            'held_reason' => $failures === [] ? null : implode("\n", $failures),
            'is_published' => $publish,
            // Up to six hours back, as the generators always dated a post.
            'published_at' => $publish ? now()->subSeconds(random_int(0, 6 * 60 * 60)) : null,
        ], fn ($value) => $value !== null));
    }

    /** True while another process is writing a post (and for a quarter of an hour at most). */
    public static function busy(): bool
    {
        return Cache::has(self::BUSY_KEY);
    }

    /** Claim the writer; false when somebody else has it. */
    public static function claim(): bool
    {
        return Cache::add(self::BUSY_KEY, now()->timestamp, 900);
    }

    public static function release(): void
    {
        Cache::forget(self::BUSY_KEY);
    }

    /**
     * The daily post's topic: the planner chooses, from everything already published, the
     * subjects that are full, the searches product pages own, and today's direction.
     *
     * @return array<string, mixed>|null
     */
    public function plan(): ?array
    {
        $titles = BlogPost::query()->orderByDesc('created_at')->pluck('title')->filter()->values()->all();
        $directions = array_values((array) config('blog.directions', []));
        $direction = $directions === [] ? 'running small events' : $directions[now()->dayOfYear % count($directions)];

        $full = [];
        foreach ((array) config('blog.subjects', []) as $subject => $rule) {
            $count = count(array_filter($titles, fn ($title) => preg_match('~'.$rule['match'].'~i', (string) $title)));

            if ($count >= (int) ($rule['full_at'] ?? 2)) {
                $full[] = $subject.' ('.$count.' posts)';
            }
        }

        $owned = implode(', ', array_filter(array_column((array) config('marketing_keywords', []), 'keyword')));

        $prompt = "DIRECTION FOR TODAY\n".$direction
            ."\n\nFULL (do not add to these)\n".($full === [] ? '(none)' : '- '.implode("\n- ", $full))
            ."\n\nOWNED SEARCHES (product pages answer these)\n".$owned
            ."\n\nPRODUCT FACTS (each line starts with its id in square brackets)\n".BlogFacts::forPrompt()
            ."\n\nEVERY PUBLISHED TITLE (".count($titles).")\n".($titles === [] ? '(none yet)' : '- '.implode("\n- ", $titles));

        $schema = $this->briefSchema(['topic' => 'STRING', 'closest_existing' => ['STRING'], 'what_this_adds' => 'STRING']);

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $brief = $this->ask('plan', $prompt, (string) config('ai_prompts.blog_topic_system'), $schema, 0.9);
            UsageTrackingService::track(UsageTrackingService::GEMINI_BLOG_TOPIC);

            if (! $this->usable($brief) || trim((string) ($brief['topic'] ?? '')) === '') {
                return null;
            }

            $repeat = BlogGate::repeats((string) $brief['topic'], (string) $brief['primary_query'], $titles);

            if ($repeat === null) {
                // Only the posts that exist: the model is asked for two and may misremember one.
                $brief['closest_existing'] = array_values(array_intersect((array) ($brief['closest_existing'] ?? []), $titles));

                return $brief;
            }

            $prompt .= "\n\nYour last choice, \"".$brief['topic'].'", is the same post as "'.$repeat.'", which is already published. Choose a different search.';
        }

        return null;
    }

    /**
     * The brief for a topic somebody else chose (an audience's configured topic, an admin's own).
     *
     * @param  array<string, mixed>|null  $audience
     * @return array<string, mixed>|null
     */
    public function brief(string $topic, ?array $audience = null): ?array
    {
        $prompt = "TOPIC\n".$topic."\n";

        if ($audience) {
            $prompt .= "\nThis post is for: ".$audience['name'].'. It sits under the page '
                .BlogLinks::base().'/'.ltrim((string) $audience['page'], '/').' ('.$audience['title'].").\n"
                ."Features the page's card names for this audience: ".implode(', ', (array) ($audience['features'] ?? []))
                .' (the facts '.implode(', ', BlogFacts::forLabels((array) ($audience['features'] ?? []))).").\n";
        }

        $prompt .= "\nPRODUCT FACTS (each line starts with its id in square brackets)\n".BlogFacts::forPrompt()."\n";

        $brief = $this->ask('brief', $prompt, (string) config('ai_prompts.blog_brief_system'), $this->briefSchema(['notes' => 'STRING']), 0.6);

        return $this->usable($brief) ? $brief : null;
    }

    /**
     * @param  array<string, mixed>  $post
     * @param  list<string>  $failures
     * @return array<string, mixed>|null
     */
    private function edit(string $user, array $post, array $failures = []): ?array
    {
        $fields = array_intersect_key($post, array_flip(['title', 'description', 'excerpt', 'category', 'content', 'faq', 'product_claims']));
        $prompt = $user."\n\nDRAFT\n".json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($failures !== []) {
            $prompt .= "\n\nAn automatic check found these problems in your last version. Fix each one and return the whole post again:\n- ".implode("\n- ", $failures);
        }

        $schema = $this->postSchema();
        $schema['properties'] += [
            'verdict' => ['type' => 'STRING', 'enum' => ['publish', 'hold']],
            'fixed' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
            'remaining' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']],
        ];
        $schema['required'] = array_merge($schema['required'], ['verdict', 'fixed', 'remaining']);

        $edited = $this->ask($failures === [] ? 'edit' : 'edit-again', $prompt, (string) config('ai_prompts.blog_editor_system'), $schema, 0.2);

        return is_array($edited) && trim((string) ($edited['content'] ?? '')) !== '' && trim((string) ($edited['title'] ?? '')) !== '' ? $edited : null;
    }

    /** @return array<string, mixed>|null */
    private function ask(string $stage, string $prompt, string $system, array $schema, float $temperature): ?array
    {
        // A step called on its own (the planner in a test, one step of the admin's three requests).
        $this->startedAt = $this->startedAt ?: microtime(true);

        // Past the budget nothing more is asked: three slow calls must not hold the scheduler.
        if (microtime(true) - $this->startedAt > (int) config('blog.budget', 330)) {
            $this->calls[] = ['stage' => $stage, 'seconds' => 0.0, 'ok' => false];

            return null;
        }

        $started = microtime(true);
        $answer = GeminiUtils::structured($prompt, [
            'stage' => $stage,
            'system' => $system,
            'schema' => $schema,
            'temperature' => $temperature,
            'model' => config('services.google.gemini_blog_model'),
            'timeout' => (int) config('blog.call_timeout', 100),
        ]);
        $this->calls[] = ['stage' => $stage, 'seconds' => round(microtime(true) - $started, 1), 'ok' => $answer !== null];

        return $answer;
    }

    /** @param  array<string, mixed>|null  $brief */
    private function usable(?array $brief): bool
    {
        if ($brief === null || trim((string) ($brief['primary_query'] ?? '')) === '' || ! is_array($brief['questions'] ?? null) || $brief['questions'] === []) {
            return false;
        }

        return in_array($brief['format'] ?? null, self::FORMATS, true);
    }

    /**
     * @param  array<string, mixed>  $brief
     * @param  list<string>  $facts
     */
    private function questions(array $brief, array $facts): string
    {
        $lines = array_map(fn ($question) => '- '.trim((string) $question), (array) $brief['questions']);

        if (trim((string) ($brief['notes'] ?? '')) !== '') {
            $lines[] = "Planner's note: ".trim((string) $brief['notes']);
        }

        $lines[] = $facts === []
            ? 'The planner found no product fact that applies; the post may not need the product at all.'
            : 'Facts the planner thinks apply: '.implode(', ', $facts);

        return implode("\n", $lines);
    }

    /**
     * The slugs "already on the blog" starts with: an audience's other posts, or the two posts
     * the planner named as closest.
     *
     * @param  array<string, mixed>  $brief
     * @param  array<string, mixed>|null  $audience
     * @return list<string>
     */
    private function firstNearby(array $brief, ?array $audience): array
    {
        if ($audience) {
            foreach ((array) config('sub_audiences', []) as $group) {
                $slugs = array_column($group['sub_audiences'], 'slug');

                if (in_array($audience['slug'], $slugs, true)) {
                    return array_values(array_diff($slugs, [$audience['slug']]));
                }
            }

            return [];
        }

        $closest = (array) ($brief['closest_existing'] ?? []);

        return $closest === [] ? [] : BlogPost::whereIn('title', $closest)->pluck('slug')->all();
    }

    /**
     * Outside numbers a post about cost may state. A card processor's published rate is the one
     * such figure: every post about fees needs an example of one, and without a sanctioned one
     * the model supplied its own ("5% or even 10% on each ticket").
     *
     * @param  array<string, mixed>  $brief
     */
    private function figures(array $brief): string
    {
        $about = mb_strtolower(($brief['primary_query'] ?? '').' '.($brief['angle'] ?? '').' '.implode(' ', (array) ($brief['questions'] ?? [])));

        if (! preg_match('~\b(?:fee|fees|cost|costs|price|pricing|cheap|commission)\b~', $about)) {
            return '';
        }

        return "- Stripe's standard card rate in the United States is 2.9% + $0.30 per successful charge; rates differ by country and card type. Use it only as an example of a processor's fee, and say it is Stripe's US rate.\n"
            .'- Do not state any ticketing platform\'s fee. To compare platforms, send the reader to '.BlogLinks::base().'/ticket-fee-calculator.';
    }

    /** The section key for the name the model chose; "planning" when it chose none of them. */
    private function categoryKey(string $name): string
    {
        foreach (BlogPost::CATEGORIES as $key => $section) {
            if (strcasecmp($section['name'], trim($name)) === 0 && $key !== 'by-event') {
                return $key;
            }
        }

        return 'planning';
    }

    /**
     * @param  array<string, mixed>  $post
     * @return list<array{question: string, answer: string}>|null
     */
    private function faq(array $post): ?array
    {
        $items = [];

        foreach ((array) ($post['faq'] ?? []) as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question !== '' && $answer !== '') {
                $items[] = ['question' => $question, 'answer' => $answer];
            }
        }

        return $items === [] ? null : $items;
    }

    /**
     * A header picture for a section that none of the last twelve posts used. They are a stock
     * set of 27 shared by every post: avoiding the last two, as before, put one picture on three
     * of ten consecutive posts.
     */
    private function picture(string $category): ?string
    {
        $all = array_keys(BlogPost::getAvailableHeaderImages(false));
        $recent = BlogPost::whereNotNull('featured_image')->orderByDesc('created_at')->limit(12)->pluck('featured_image')->all();
        $fresh = array_values(array_diff($all, $recent));
        $forSection = array_values(array_intersect((array) config('blog.pictures.'.$category, []), $fresh));

        $pool = $forSection ?: ($fresh ?: $all);

        return $pool === [] ? null : $pool[array_rand($pool)];
    }

    /** @return array<string, mixed> */
    private function postSchema(): array
    {
        $string = ['type' => 'STRING'];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'title' => $string,
                'description' => $string,
                'excerpt' => $string,
                'category' => ['type' => 'STRING', 'enum' => array_column(BlogPost::CATEGORIES, 'name')],
                'content' => $string,
                'faq' => ['type' => 'ARRAY', 'items' => ['type' => 'OBJECT', 'properties' => ['question' => $string, 'answer' => $string], 'required' => ['question', 'answer']]],
                'product_claims' => ['type' => 'ARRAY', 'items' => ['type' => 'OBJECT', 'properties' => ['sentence' => $string, 'fact_id' => $string], 'required' => ['sentence', 'fact_id']]],
            ],
            'required' => ['title', 'description', 'excerpt', 'category', 'content', 'faq', 'product_claims'],
        ];
    }

    /**
     * @param  array<string, string|array<int, string>>  $extra  field => 'STRING', or ['STRING'] for a list
     * @return array<string, mixed>
     */
    private function briefSchema(array $extra): array
    {
        $string = ['type' => 'STRING'];
        $list = ['type' => 'ARRAY', 'items' => $string];
        $properties = [
            'primary_query' => $string,
            'reader' => $string,
            'format' => ['type' => 'STRING', 'enum' => self::FORMATS],
            'angle' => $string,
            'questions' => $list,
            'capabilities' => $list,
        ];

        foreach ($extra as $field => $type) {
            $properties[$field] = is_array($type) ? $list : $string;
        }

        return ['type' => 'OBJECT', 'properties' => $properties, 'required' => array_keys($properties)];
    }

    /** @return array<string, mixed> */
    private function result(?array $post = null, array $failures = [], ?array $brief = null, ?array $draft = null, ?string $error = null): array
    {
        return [
            'post' => $post,
            'failures' => $failures,
            'brief' => $brief,
            'draft' => $draft,
            'error' => $error,
            'calls' => $this->calls,
            'seconds' => round(microtime(true) - $this->startedAt, 1),
        ];
    }
}
