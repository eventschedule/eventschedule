<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

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
        $html = \App\Utils\MarkdownUtils::sanitizeHtml($this->content);

        return preg_replace('~<(/?)h1(?=[\s>])~i', '<$1h2', $html) ?? $html;
    }

    public function getFeaturedImageUrlAttribute()
    {
        if (! $this->featured_image) {
            return null;
        }

        // Return the URL to the header image
        return url('/images/headers/'.$this->featured_image);
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
