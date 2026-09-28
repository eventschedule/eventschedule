<?php

namespace App\Models;

use App\Jobs\GenerateGalleryImageVariants;
use App\Traits\HasImageVariants;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * One photo in an organizer's gallery: on an event when event_id is set, else on the schedule
 * (role_id). Not a fan photo - those are EventPhoto, submitted by guests and moderated.
 *
 * A row carrying a draft_token was uploaded from an edit form that has not been saved yet. It is
 * never shown anywhere until GalleryUtils::sync() commits it (draft_token back to null) on Save;
 * app:prune-gallery-drafts deletes the ones that never are.
 *
 * `filename` is the bare stored name, as for every other image column in the app. width and
 * height are the displayed size recorded at upload (after EXIF orientation), and color is the
 * photo's average colour, the placeholder a tile shows while the image loads.
 */
class GalleryImage extends Model
{
    use HasImageVariants;

    protected $fillable = [
        'role_id',
        'event_id',
        'user_id',
        'filename',
        'caption',
        'credit',
        'width',
        'height',
        'color',
        'sort_order',
        'draft_token',
    ];

    protected $hidden = [
        'draft_token',
    ];

    protected $casts = [
        'image_variants' => 'array',
        'width' => 'integer',
        'height' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::created(function (GalleryImage $image) {
            $raw = $image->imageVariantSource();

            if ($raw) {
                GenerateGalleryImageVariants::dispatch($image->id, $raw);
            }
        });

        // Every delete goes through the model (GalleryUtils::purge() exists because the DB
        // cascades on events and roles would skip this), so the file and its derivatives go too.
        static::deleting(function (GalleryImage $image) {
            $raw = $image->imageVariantSource();

            if (! $raw || str_starts_with($raw, 'demo_')) {
                return;
            }

            try {
                Storage::delete(ImageUtils::storagePathFor($raw));
            } catch (\Throwable $e) {
                report($e);
            }

            ImageUtils::deleteStoredVariants($raw);
        });
    }

    public function imageVariantSourceColumn(): string
    {
        return 'filename';
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCommitted(Builder $query): Builder
    {
        return $query->whereNull('draft_token');
    }

    public function isDraft(): bool
    {
        return $this->draft_token !== null;
    }

    public function url(): string
    {
        return ImageUtils::storedUrl($this->imageVariantSource());
    }

    /**
     * The derivative at $width, or the original while the queue has not built it yet (up to a
     * minute after upload on the hosted deploy) or when it never will (an animated GIF keeps its
     * frames on the page, so its lightbox view falls back here too).
     */
    public function thumbUrl(int $width = ImageUtils::VARIANT_WIDTH): string
    {
        return $this->imageVariantUrl($width) ?: $this->url();
    }

    /**
     * The srcset of every derivative plus the original at its recorded width, or null while the
     * derivatives are missing - a srcset of one width would stop a 2x screen from reaching the
     * sharper original - and for an animated original.
     */
    public function srcset(): ?string
    {
        // An animated original's derivatives are stills of its first frame; the page shows the
        // original alone so it keeps moving.
        if ($this->imageIsAnimated()) {
            return null;
        }

        $parts = [];

        foreach ($this->imageVariantWidths() as $width) {
            $name = $this->imageVariantFilename($width);

            if (! $name) {
                return null;
            }

            $parts[] = ImageUtils::variantUrl($name).' '.$width.'w';
        }

        if ($this->width && $this->width > max($this->imageVariantWidths())) {
            $parts[] = $this->url().' '.$this->width.'w';
        }

        return implode(', ', $parts);
    }

    /**
     * The shape the admin editor and the guest lightbox both read. Captions and credits are the
     * owner's text, so they travel as JSON and are bound with Vue's :attr / {{ }} - never
     * rendered server-side inside a Vue mount.
     */
    public function toEditorArray(): array
    {
        return [
            'id' => UrlUtils::encodeId($this->id),
            'url' => $this->url(),
            'thumb_url' => $this->thumbUrl(),
            'width' => $this->width,
            'height' => $this->height,
            'color' => $this->color,
            'caption' => $this->caption ?? '',
            'credit' => $this->credit ?? '',
        ];
    }

    public function toLightboxArray(): array
    {
        return [
            'src' => $this->url(),
            'srcset' => $this->srcset(),
            'thumb' => $this->thumbUrl(960),
            'w' => $this->width,
            'h' => $this->height,
            'color' => $this->color,
            'caption' => $this->caption ?? '',
            'credit' => $this->credit ?? '',
        ];
    }
}
