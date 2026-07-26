<?php

namespace App\Models;

use App\Utils\ImageUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A listing received from a federated instance. Read-only display data: it is never
 * edited here, has no local owner, and every click on it leaves for the origin site.
 */
class FederatedEvent extends Model
{
    /**
     * Columns a push is allowed to write. `blocked_at` is deliberately absent so an
     * admin block survives a re-push, mirroring how Event keeps
     * `is_hidden_from_discovery` out of $fillable.
     */
    public const UPSERT_FIELDS = [
        'url',
        'name',
        'short_description',
        'language',
        'starts_at',
        'ends_at',
        'timezone',
        'occurrences',
        'next_occurrence_at',
        'occurrences_hash',
        'schedule_name',
        'schedule_url',
        'image_url',
        'event_url',
        'venue_name',
        'address',
        'city',
        'state',
        'postal_code',
        'country_code',
        'geo_lat',
        'geo_lon',
    ];

    protected $fillable = [
        'federated_instance_id',
        'external_id',
        ...self::UPSERT_FIELDS,
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'next_occurrence_at' => 'datetime',
        'occurrences' => 'array',
        'blocked_at' => 'datetime',
        'geo_lat' => 'float',
        'geo_lon' => 'float',
    ];

    protected static function booted(): void
    {
        // Covers $model->delete() only. Mass deletes bypass model events entirely, and
        // deleting an instance drops its rows through the FK cascade without PHP seeing
        // them at all - purge() below is what those two paths must use.
        static::deleting(function (self $row) {
            static::deleteStoredImage($row->image_path);
        });
    }

    public function instance()
    {
        return $this->belongsTo(FederatedInstance::class, 'federated_instance_id');
    }

    /**
     * Remove the locally stored copy of a listing's image.
     *
     * Static because the paths that destroy listings cannot all hold a model instance.
     * Failures are reported rather than thrown: a file we cannot unlink must not stop
     * the row going away, or the sweep would wedge on one bad object.
     */
    public static function deleteStoredImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            Storage::delete(config('filesystems.default') == 'local' ? '/public/'.$path : '/'.$path);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Delete everything the query matches, unlinking stored images first.
     *
     * The alternative - letting the rows go and leaving the files - leaks storage on
     * every hourly reconcile, because that sweep deletes through the query builder.
     * Chunked: an instance may hold thousands of rows and this runs inside a request.
     */
    public static function purge($query): int
    {
        (clone $query)->whereNotNull('image_path')
            ->select(['id', 'image_path'])
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    static::deleteStoredImage($row->image_path);
                }
            });

        return $query->delete();
    }

    /**
     * Block or unblock a listing.
     *
     * These exist because blocked_at is deliberately absent from $fillable, so
     * `update(['blocked_at' => ...])` silently does nothing. That absence is what
     * stops a re-push clearing a block, but it is an easy trap for calling code, so
     * go through these rather than mass assignment.
     */
    public function block(): void
    {
        $this->blocked_at = now();
        $this->save();
    }

    public function unblock(): void
    {
        $this->blocked_at = null;
        $this->save();
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    /**
     * The zone this listing's times should be rendered in, guaranteed safe to pass to
     * setTimezone().
     *
     * The intake validates `timezone` now, but this must not trust the column: rows
     * stored before that rule existed can hold anything, and Carbon throws
     * InvalidTimeZoneException on an unknown zone. Since the public browse page renders
     * these, one bad row would otherwise return a 500 for every visitor rather than
     * just breaking its own card.
     */
    public function safeTimezone(): string
    {
        $zone = (string) $this->timezone;

        return $zone !== '' && in_array($zone, timezone_identifiers_list(), true) ? $zone : 'UTC';
    }

    /**
     * Public URL of the locally-stored copy of this listing's image.
     *
     * Must go through ImageUtils rather than building '/storage/...': the hosted site
     * serves stored images from object storage, and that is the only deployment where
     * federated listings render at all.
     */
    public function imageUrl(): string
    {
        return ImageUtils::storedUrl($this->image_path);
    }

    /**
     * Rows eligible to appear publicly: not blocked, from an approved instance, still
     * upcoming, and holding a locally-stored image. The image requirement matches the
     * bar browse already applies to its own events (only cards that show a real
     * picture, never the letter-gradient placeholder) and keeps rendering off
     * third-party hosts.
     */
    public function scopeListable($query)
    {
        return $query->whereNull('blocked_at')
            ->whereNotNull('image_path')
            ->whereNotNull('next_occurrence_at')
            ->where('next_occurrence_at', '>=', now()->subDay())
            ->whereHas('instance', fn ($q) => $q->approved());
    }

    /**
     * Mirrors Event::getSchemaAttendanceMode(): an online link plus a venue is hybrid,
     * the link alone is online, neither is in-person.
     */
    public function isOnline(): bool
    {
        return ! empty($this->event_url) && empty($this->venue_name);
    }

    public function isHybrid(): bool
    {
        return ! empty($this->event_url) && ! empty($this->venue_name);
    }

    /**
     * Venue and city for the card subtitle, matching the local card's
     * "name · city" shape. Null for purely online events, which get a label instead.
     */
    public function locationLabel(): ?string
    {
        $parts = array_filter([$this->venue_name, $this->city]);

        return $parts ? implode(' · ', $parts) : null;
    }

    /**
     * The host shown on the source badge, so a visitor knows where the click goes.
     */
    public function sourceHost(): ?string
    {
        $host = parse_url((string) $this->url, PHP_URL_HOST);

        return $host ? preg_replace('/^www\./', '', strtolower($host)) : null;
    }
}
