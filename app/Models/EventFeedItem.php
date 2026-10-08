<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One thing a feed has shown us. See the migration for the states and the columns.
 */
class EventFeedItem extends Model
{
    public const STATE_NEW = 'new';

    public const STATE_IMPORTED = 'imported';

    public const STATE_MATCHED = 'matched';

    public const STATE_SKIPPED = 'skipped';

    public const STATE_DISMISSED = 'dismissed';

    public const STATE_REMOVED = 'removed';

    public const STATE_DECIDE = 'decide';

    public const STATES = [
        self::STATE_NEW, self::STATE_IMPORTED, self::STATE_MATCHED, self::STATE_SKIPPED,
        self::STATE_DISMISSED, self::STATE_REMOVED, self::STATE_DECIDE,
    ];

    protected $guarded = [];

    protected $casts = [
        // Addresses at the source, which can carry its token as the feed's own does.
        'detail_url' => 'encrypted',
        'image_source' => 'encrypted',
        'imported' => 'array',
        'pending' => 'array',
        'cancelled_by_feed' => 'boolean',
        'image_pending' => 'boolean',
        'missing_reads' => 'integer',
        'starts_at' => 'datetime',
        'detail_checked_at' => 'datetime',
        'publish_requested_at' => 'datetime',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    protected $hidden = ['detail_url', 'image_source'];

    /**
     * The key an item is found by. The source's own id can be as long as the source likes and
     * in any letters; its sha256 is 64 characters of ASCII, compared exactly.
     */
    public static function keyFor(string $externalId): string
    {
        return hash('sha256', $externalId);
    }

    /**
     * The owner is deleting this event on purpose (the admin portal's Delete, the API's DELETE).
     * If a feed made it, that is a decision about the feed too: the event is not to come back
     * on the next read. Called BEFORE the delete, which nulls event_id.
     *
     * Deliberately not part of deleting an event in general: an event that goes any other way
     * (the import's Undo, a schedule being deleted, another account) was not decided about by
     * anybody, and the next read brings it back as a draft for the owner to look at.
     */
    public static function dismissFor(Event $event): void
    {
        if (! $event->isFromFeed() || ! EventFeed::tablesReady()) {
            return;
        }

        static::where('event_id', $event->id)
            ->whereIn('state', [self::STATE_IMPORTED, self::STATE_DECIDE])
            ->update(['state' => self::STATE_DISMISSED, 'pending' => null, 'publish_requested_at' => null]);
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(EventFeed::class, 'event_feed_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
