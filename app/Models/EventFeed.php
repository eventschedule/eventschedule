<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

/**
 * An address a schedule keeps reading for events. See the migration for what each column is,
 * and App\Services\Feeds for what reads them.
 */
class EventFeed extends Model
{
    /** What an address turned out to be, which decides the reader. */
    public const KIND_CALENDAR = 'calendar';

    public const KIND_PAGE = 'page';

    public const KIND_ITEMS = 'items';

    public const KIND_JOLIOO = 'jolioo';

    public const KINDS = [self::KIND_CALENDAR, self::KIND_PAGE, self::KIND_ITEMS, self::KIND_JOLIOO];

    /** New events are public at once, or held as drafts to be looked over. */
    public const PUBLISH = 'publish';

    public const DRAFT = 'draft';

    public const PUBLISH_MODES = [self::PUBLISH, self::DRAFT];

    /** What happens to an event the source no longer lists. */
    public const LEFT_KEEP = 'keep';

    public const LEFT_CANCEL = 'cancel';

    public const LEFT_DELETE = 'delete';

    public const LEFT_ACTIONS = [self::LEFT_KEEP, self::LEFT_CANCEL, self::LEFT_DELETE];

    /** Why a feed is not being read. Keys: the page says them in the reader's language. */
    public const PAUSED_BY_OWNER = 'owner';

    public const PAUSED_FAILING = 'failing';

    public const PAUSED_TRANSFER = 'transfer';

    public const PAUSED_MEMBER_LEFT = 'member_left';

    public const PAUSED_UNDO = 'undo';

    /** How many feeds one schedule may have. */
    public const PER_SCHEDULE = 10;

    protected $guarded = [];

    protected $casts = [
        // The address is the credential for a private calendar or a provider's feed.
        'url' => 'encrypted',
        'add_organizer' => 'boolean',
        'can_see_leaving' => 'boolean',
        'paused_at' => 'datetime',
        'next_check_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'last_success_at' => 'datetime',
        'baseline_done_at' => 'datetime',
        'held_leaving' => 'array',
        'stats' => 'array',
        'failure_count' => 'integer',
        'waiting_count' => 'integer',
        'decide_count' => 'integer',
    ];

    /** Never in an array or JSON form of the model: a log line or an API reply is not the place for it. */
    protected $hidden = ['url', 'etag', 'last_modified'];

    private static ?bool $tablesReady = null;

    /**
     * Whether the feed tables exist, for the two callers that run with no person in front of
     * them (the every-minute read and the subscriber digest) on an install that has the code
     * and has not yet migrated. Remembered only once they do, so a deploy's migrate is seen by
     * a process that was already running.
     */
    public static function tablesReady(): bool
    {
        if (self::$tablesReady) {
            return true;
        }

        try {
            return self::$tablesReady = Schema::hasTable('event_feeds') && Schema::hasTable('event_feed_items');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** What two addresses are compared by: an encrypted column cannot be. */
    public static function hashOf(string $url): string
    {
        return hash('sha256', $url);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EventFeedItem::class);
    }

    /**
     * Whether a schedule's plan includes feeds: every selfhost install has them, as it has every
     * Enterprise feature, and on the hosted service they are Enterprise.
     */
    public static function allowedFor(Role $role): bool
    {
        return ! config('app.hosted') || $role->isEnterprise();
    }

    /**
     * Stop reading these feeds until somebody who runs the schedule now says to go on.
     *
     * A feed makes events as the schedule's owner, from an address somebody chose. When the
     * schedule changes hands, or the member who added a feed leaves, nobody who is there now has
     * chosen it. Paused rather than removed: its events stay, and one press resumes it.
     *
     * Whatever was asked to be published is dropped with it: a "Publish all" pressed by someone
     * who has left must not fire later.
     */
    public static function pauseWhere(\Closure $which, string $reason): void
    {
        if (! self::tablesReady()) {
            return;
        }

        $ids = self::query()->whereNull('paused_at')->where($which)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        self::whereIn('id', $ids)->update(['paused_at' => now(), 'pause_reason' => $reason]);
        EventFeedItem::whereIn('event_feed_id', $ids)->whereNotNull('publish_requested_at')->update(['publish_requested_at' => null]);
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    public function publishes(): bool
    {
        return $this->publish_mode === self::PUBLISH;
    }
}
