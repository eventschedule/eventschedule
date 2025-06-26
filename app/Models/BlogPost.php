<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BlogPost extends Model
{
    use HasFactory;

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
        $slug = Str::slug($this->title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
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
        
        return $readingTime . ' min read';
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

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function getFeaturedImageUrlAttribute()
    {
        if (!$this->featured_image) {
            return null;
        }

        // Return the URL to the header image
        return url('/images/headers/' . $this->featured_image);
    }

    public static function getAvailableHeaderImages()
    {
        return self::$availableHeaderImages;
    }
}
