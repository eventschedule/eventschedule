<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\MediaAsset;
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
        $value = $this->attributes['featured_image'] ?? null;

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        if (str_contains($value, '/') || str_contains($value, '\\')) {
            return storage_asset_url($value);
        }

        $asset = $this->resolveLegacyFeaturedImageAsset($value);

        return $asset?->url;
    }

    public function getUrlAttribute()
    {
        return url('/blog/' . $this->slug);
    }

    protected function resolveLegacyFeaturedImageAsset(string $legacy): ?MediaAsset
    {
        static $cache = [];

        $legacy = trim($legacy);

        if ($legacy === '') {
            return null;
        }

        if (! array_key_exists($legacy, $cache)) {
            $candidates = [$legacy];

            if (! str_contains($legacy, '.')) {
                $candidates[] = $legacy . '.png';
                $candidates[] = $legacy . '.jpg';
                $candidates[] = $legacy . '.jpeg';
            }

            $cache[$legacy] = MediaAsset::query()
                ->where('folder', 'headers')
                ->whereIn('original_filename', $candidates)
                ->orderByDesc('id')
                ->first();
        }

        return $cache[$legacy];
    }

    public function featuredImageAsset(): ?MediaAsset
    {
        $value = $this->attributes['featured_image'] ?? null;

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (str_contains($value, '/') || str_contains($value, '\\')) {
            $normalized = storage_normalize_path($value);

            return MediaAsset::where('path', $normalized)->first();
        }

        return $this->resolveLegacyFeaturedImageAsset($value);
    }
}
