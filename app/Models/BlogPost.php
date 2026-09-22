<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    /** The quality gate's floor: see qualityGateFailure(). */
    public const QUALITY_MIN_WORDS = 800;

    /** similar_text() percent at or above which a title counts as a repeat. */
    public const QUALITY_MAX_SIMILARITY = 80.0;

    public function encodeId()
    {
        return UrlUtils::encodeId($this->id);
    }

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'tags',
        'published_at',
        'meta_title',
        'meta_description',
        'featured_image',
        'author_name',
        'is_published',
        'view_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'noindex' => 'boolean',
    ];

    // Generic header images that work well for blog posts
    public static $availableHeaderImages = [
        'Literature.png' => 'Literature & Writing',
        'Lets_do_Business.png' => 'Business & Professional',
        'Network_Summit.png' => 'Networking & Events',
        'Synergy.png' => 'Collaboration & Teamwork',
        'People_of_the_World.png' => 'Community & Diversity',
        'All_Hands_on_Deck.png' => 'Team Building',
        'Tradeshow_Expo.png' => 'Exhibitions & Shows',
        'Yoga_and_Wellness.png' => 'Wellness & Health',
        'Peaceful_Studio.png' => 'Mindfulness & Peace',
        'Nature_Calls.png' => 'Nature & Outdoors',
        'Flowerful_Life.png' => 'Life & Growth',
        'Sports_Centre.png' => 'Sports & Fitness',
        'Meditation.png' => 'Meditation & Spirituality',
        'Mindful.png' => 'Mindfulness & Awareness',
        'Fitness_Morning.png' => 'Fitness & Motivation',
        'Chess_Vibrancy.png' => 'Strategy & Thinking',
        'Summer_Events.png' => 'Seasonal Events',
        'Chill_Evening.png' => 'Relaxation & Leisure',
        'Arena.png' => 'Competition & Performance',
        'Sports_and_Youth.png' => 'Youth & Sports',
        'Kids_Bonanza.png' => 'Family & Children',
        'Music_Potential.png' => 'Music & Arts',
        'The_Stage_Awaits.png' => 'Performance & Entertainment',
        'Ready_to_Dance.png' => 'Dance & Movement',
        'Warming_Up.png' => 'Preparation & Warm-up',
        'Networking_and_Bagels.png' => 'Networking & Social',
        '5am_Club.png' => 'Productivity & Early Bird',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = $post->generateSlug();
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = $post->generateSlug();
            }
        });
    }

    public function generateSlug()
    {
        // The uniqueness loop below cannot save an empty slug - "" is a perfectly unique first
        // value - and this column is unique() and is the /blog/{slug} URL.
        $slug = \App\Utils\SlugUtils::slugOrRomanize($this->title, 'post-'.Str::random(6));
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }

        return $slug;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }

    public function scopeByTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('published_at', $year)
            ->whereMonth('published_at', $month);
    }

    public function getFormattedPublishedAtAttribute()
    {
        return $this->published_at ? $this->published_at->format('F j, Y') : null;
    }

    public function getReadingTimeAttribute()
    {
        $wordsPerMinute = 200;
        $wordCount = str_word_count(strip_tags($this->content));
        $readingTime = ceil($wordCount / $wordsPerMinute);

        return $readingTime.' min read';
    }

    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Generate excerpt from content if not provided
        $content = strip_tags($this->content);

        return Str::limit($content, 160);
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->title;
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: $this->excerpt;
    }

    /**
     * Count a public read of this post WITHOUT restamping updated_at.
     *
     * Eloquent's Builder::increment() runs its values through addUpdatedAtColumn(), so a bare
     * $this->increment('view_count') writes updated_at = now() alongside the counter. Every
     * anonymous page view - Googlebot's included - therefore marked the post modified, and
     * updated_at is what feeds the sitemap's <lastmod>, the BlogPosting dateModified and
     * article:modified_time. The whole corpus reported "changed at the moment you crawled it".
     *
     * withoutTimestamps() flips usesTimestamps() off for the duration, which is what
     * addUpdatedAtColumn() consults, so the UPDATE carries view_count only. The in-memory model
     * still gets the new count (Model::incrementOrDecrement sets the column before it queries)
     * and keeps the stored updated_at, which is what the view renders.
     */
    public function incrementViewCount()
    {
        static::withoutTimestamps(fn () => $this->increment('view_count'));
    }

    /**
     * The post body as it should be rendered on a page.
     *
     * The template already renders the title as the page's one <h1>. sanitizeHtml() allows h1
     * through and the AI generator used to be told to emit it, so most stored bodies open with a
     * second (and sometimes third) <h1>. Demote them rather than editing stored content.
     */
    public function renderedContent(): string
    {
        $html = \App\Utils\MarkdownUtils::sanitizeHtml(self::repairMarkdownHrefs((string) $this->content));

        $html = preg_replace('~<(/?)h1(?=[\s>])~i', '<$1h2', $html) ?? $html;

        return self::followFirstPartyLinks($html);
    }

    /**
     * Undo the old prompt's markdown-inside-an-attribute links.
     *
     * config/ai_prompts.php used to show the model `href="[https://www.eventschedule.com/x](https://www.eventschedule.com/x)"`,
     * a markdown link pasted into an HTML attribute, and the model copied it faithfully. The
     * purifier then percent-encodes the brackets into a RELATIVE path, so on blog.{domain} the
     * link 404s. The first URL is the one that was meant. Stored bodies are never rewritten.
     */
    public static function repairMarkdownHrefs(string $html): string
    {
        return preg_replace(
            '~href=(["\'])\s*\[\s*(https?://[^\]\s"\']+)\s*\]\([^)"\']*\)\s*\1~i',
            'href=$1$2$1',
            $html
        ) ?? $html;
    }

    /**
     * Let the blog pass link equity to the product it writes about.
     *
     * MarkdownUtils::sanitizeHtml() stamps every anchor with rel="nofollow noreferrer noopener"
     * and target="_blank". That is right for user content, which is why it is not changed there,
     * but the blog lives on blog.{domain}, so its links to {domain} looked external and every
     * one of them was nofollow. The stored bodies also link www.{domain}, a redirect hop.
     *
     * For a first-party anchor only - the base domain, www. plus it, or any subdomain of it -
     * this drops nofollow/noopener/noreferrer and target="_blank", and rewrites www. to the apex.
     * Everything else keeps exactly what the purifier gave it.
     */
    public static function followFirstPartyLinks(string $html): string
    {
        $bases = self::firstPartyBaseHosts();

        if ($bases === []) {
            return $html;
        }

        return preg_replace_callback('~<a\s[^>]*>~i', function ($match) use ($bases) {
            $tag = $match[0];

            if (! preg_match('~\shref="([^"]*)"~i', $tag, $href)) {
                return $tag;
            }

            $host = strtolower((string) parse_url(html_entity_decode($href[1]), PHP_URL_HOST));

            if ($host === '') {
                return $tag;
            }

            $base = null;
            foreach ($bases as $candidate) {
                if ($host === $candidate || str_ends_with($host, '.'.$candidate)) {
                    $base = $candidate;
                    break;
                }
            }

            if ($base === null) {
                return $tag;
            }

            if ($host === 'www.'.$base) {
                $newHref = preg_replace('~^(https?://)www\.~i', '$1', $href[1], 1);
                $tag = str_replace($href[0], ' href="'.$newHref.'"', $tag);
            }

            $tag = preg_replace('~\starget="_blank"~i', '', $tag);

            return preg_replace_callback('~\srel="([^"]*)"~i', function ($rel) {
                $kept = array_filter(
                    preg_split('~\s+~', trim($rel[1])) ?: [],
                    fn ($token) => $token !== '' && ! in_array(strtolower($token), ['nofollow', 'noopener', 'noreferrer'], true)
                );

                return $kept === [] ? '' : ' rel="'.implode(' ', $kept).'"';
            }, $tag);
        }, $html) ?? $html;
    }

    /**
     * The registrable host(s) the blog counts as its own: _base_domain(), plus the marketing
     * site's host when an operator points APP_MARKETING_URL somewhere else.
     *
     * @return list<string>
     */
    private static function firstPartyBaseHosts(): array
    {
        $hosts = [strtolower(_base_domain())];

        $marketing = strtolower((string) parse_url((string) config('app.marketing_url'), PHP_URL_HOST));
        if ($marketing !== '') {
            $hosts[] = preg_replace('~^www\.~', '', $marketing);
        }

        return array_values(array_unique(array_filter($hosts, fn ($host) => $host !== '' && $host !== 'localhost')));
    }

    /**
     * The document <title>: the brand suffix only when it still fits in 60 characters.
     *
     * The AI generator writes a 50 to 60 character meta_title of its own, so a blanket
     * " | Event Schedule" pushed most posts to 70 to 80 characters and Google truncated them.
     */
    public function pageTitle(): string
    {
        $title = trim((string) $this->meta_title);
        $suffix = ' | Event Schedule';

        if (mb_strlen($title.$suffix) <= 60) {
            return $title.$suffix;
        }

        return Str::limit($title, 59, '…', true);
    }

    /**
     * The meta description, cut at a word boundary to at most 160 characters (the ellipsis
     * included). The generator is asked for 150 to 160 and regularly returns 180.
     */
    public function pageDescription(): string
    {
        return Str::limit(trim((string) $this->meta_description), 159, '…', true);
    }

    /**
     * Words in an HTML body. Tags become spaces first, so "</p><p>" cannot glue two words into
     * one the way a bare strip_tags() does.
     */
    public static function wordCountOf(?string $html): int
    {
        $text = html_entity_decode(preg_replace('~<[^>]*>~', ' ', (string) $html) ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return (int) preg_match_all('~[\p{L}\p{N}][\p{L}\p{N}\'’-]*~u', $text);
    }

    public function wordCount(): int
    {
        return self::wordCountOf($this->content);
    }

    /**
     * Why a generated post should not be published, or null when it may be.
     *
     * The two AI generators publish unattended, and a thin or near-repeat post is exactly what
     * Google's scaled-content policy demotes a whole host for. This is the one check both run
     * before BlogPost::create():
     *
     * - fewer than QUALITY_MIN_WORDS words once the markup is stripped, or
     * - a title within QUALITY_MAX_SIMILARITY percent (similar_text, after normalising case and
     *   punctuation) of an existing post's title or slug.
     *
     * An explicitly configured slug (the sub-audience posts) is not compared: config chose it and
     * the generator only runs for slugs that do not exist yet, and similar audience slugs
     * ("for-jazz-bands", "for-jam-bands") would otherwise block one another forever.
     *
     * @param  array{title?: ?string, content?: ?string}  $data
     */
    public static function qualityGateFailure(array $data): ?string
    {
        $words = self::wordCountOf($data['content'] ?? '');

        if ($words < self::QUALITY_MIN_WORDS) {
            return "too short ({$words} words, minimum ".self::QUALITY_MIN_WORDS.')';
        }

        $title = self::normaliseForComparison($data['title'] ?? '');

        if ($title === '') {
            return 'missing title';
        }

        foreach (static::query()->select(['id', 'title', 'slug'])->cursor() as $existing) {
            foreach (['title', 'slug'] as $field) {
                $other = self::normaliseForComparison((string) $existing->{$field});

                if ($other === '') {
                    continue;
                }

                similar_text($title, $other, $percent);

                if ($percent >= self::QUALITY_MAX_SIMILARITY) {
                    return sprintf('near-duplicate of post #%d %s "%s" (%.0f%% similar)', $existing->id, $field, $existing->{$field}, $percent);
                }
            }
        }

        return null;
    }

    private static function normaliseForComparison(string $value): string
    {
        $value = mb_strtolower($value);
        $value = preg_replace('~[^\p{L}\p{N}]+~u', ' ', $value) ?? $value;

        return trim($value);
    }

    public function getFeaturedImageUrlAttribute()
    {
        if (! $this->featured_image) {
            return null;
        }

        // Return the URL to the header image
        return url('/images/headers/'.$this->featured_image);
    }

    /**
     * The image a link preview should use, which is not the one the page renders.
     *
     * public/images/headers/*.png are 1536x768 and about 1.9 MB each. WhatsApp renders no link
     * preview at all for an image over roughly 300 KB, so the PNG is unusable as an og:image and
     * the 384px thumbnail is far too small for one. `php artisan app:generate-thumbnails` writes
     * 1200x600 JPEG twins under public/images/headers/social/ for exactly this. Falls back to the
     * PNG when a twin has not been generated yet.
     */
    public function socialImageUrl(): ?string
    {
        if (! $this->featured_image) {
            return null;
        }

        $name = pathinfo(basename($this->featured_image), PATHINFO_FILENAME);

        if ($name !== '' && is_file(public_path('images/headers/social/'.$name.'.jpg'))) {
            return url('/images/headers/social/'.$name.'.jpg');
        }

        return $this->featured_image_url;
    }

    public function getUrlAttribute()
    {
        return url('/blog/'.$this->slug);
    }

    public static function getAvailableHeaderImages($filter = true)
    {
        if (! $filter) {
            return self::$availableHeaderImages;
        }

        // Get the last 2 used featured images from the database
        $recentlyUsedImages = self::whereNotNull('featured_image')
            ->where('featured_image', '!=', '')
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->pluck('featured_image')
            ->toArray();

        // Filter out the recently used images from available options
        $availableImages = self::$availableHeaderImages;
        foreach ($recentlyUsedImages as $usedImage) {
            unset($availableImages[$usedImage]);
        }

        return $availableImages;
    }
}
