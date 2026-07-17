<?php

namespace App\Models;

use App\Jobs\SyncEventToCalDAV;
use App\Jobs\SyncEventToGoogleCalendar;
use App\Jobs\SyncEventToMicrosoftCalendar;
use App\Services\TicketVolumeDiscount;
use App\Utils\EventTextGenerator;
use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    /**
     * Names that, on their own, mark an event as throwaway test data.
     * Matched against LOWER(TRIM(name)) by scopeExcludeLikelyTest().
     *
     * - LIKELY_TEST_NAME_REGEX: the "test" family (test, Test, test1, test 2,
     *   testing, test test, test event). Anchored so "testival"/"test kitchen"/
     *   "tester" are NOT matched. Used negatively for the event name and
     *   positively for the schedule (talent/venue) name.
     * - LIKELY_TEST_WEAK_REGEX: softer signals ("test concert", "sample sale",
     *   "demo day") that only hide an event when it is otherwise empty. The
     *   trailing [ _-]|$ requires a separator/end so "sampler"/"demos" are safe.
     * - REPEATED_CHAR_REGEX: a single character repeated 3+ times (aaaa, ...., 1111).
     *   Needs MySQL 8.0 regex backreferences.
     * - LIKELY_TEST_NAMES: non-"test" junk literals (keyboard mashing, placeholders).
     */
    public const LIKELY_TEST_NAME_REGEX = '^test([[:space:]_-]*[0-9]*|ing| test| event)?$';

    public const LIKELY_TEST_WEAK_REGEX = '^(test|my test|sample|example|demo)([ _-]|$)';

    public const REPEATED_CHAR_REGEX = '^(.)\\1{2,}$';

    public const LIKELY_TEST_NAMES = [
        'asdf', 'asdfasdf', 'fdsa', 'qwerty', 'qwertyuiop',
        'abc', 'abcd', 'xxx', 'xxxx', 'aaa', 'aaaa', 'zzz',
        '123', '1234', '12345',
        'untitled', 'untitled event', 'new event',
        'delete', 'delete me', 'ignore', 'please ignore',
        'lorem ipsum', 'na', 'n/a',
    ];

    /**
     * When true, the `deleting` hook skips dispatchCalendarSync('delete'). Set by
     * applyInboundDeletion() so a delete triggered by an inbound calendar sync does not echo a
     * redundant delete back out to the calendars. (Events with an active boost are separately kept
     * safe: applyInboundDeletion routes them to the cancel path, so the refund logic never runs.)
     */
    public bool $skipOutboundCalendarSync = false;

    protected $fillable = [
        'starts_at',
        'duration',
        'description',
        'description_en',
        'short_description',
        'short_description_en',
        'event_url',
        'event_password',
        'is_private',
        'is_draft',
        'is_internal',
        'name',
        'name_en',
        'slug',
        'tickets_enabled',
        'rsvp_enabled',
        'rsvp_limit',
        'rsvp_sold',
        'ticket_currency_code',
        'ticket_price',
        'coupon_code',
        'ticket_notes',
        'terms_url',
        'total_tickets_mode',
        'payment_method',
        'payment_instructions',
        'expire_unpaid_tickets',
        'registration_url',
        'category_id',
        'category_name',
        'creator_role_id',
        'timezone',
        'recurring_end_type',
        'recurring_end_value',
        'recurring_frequency',
        'recurring_interval',
        'custom_fields',
        'custom_field_values',
        'agenda_ai_prompt',
        'translation_attempts',
        'last_translated_at',
        'last_notified_fan_content_count',
        'feedback_enabled',
        'fan_comments_enabled',
        'fan_photos_enabled',
        'fan_videos_enabled',
        'ask_phone',
        'require_phone',
        'country_code_phone',
        'individual_tickets',
        'individual_ticket_fields',
        'sell_after_start',
        'show_unavailable_tickets',
        'sponsor_mode',
        'sponsor_logos',
    ];

    protected $hidden = ['event_password'];

    protected $casts = [
        'duration' => 'float',
        'is_private' => 'boolean',
        'is_draft' => 'boolean',
        'is_internal' => 'boolean',
        // Cancellation state is set only via EventController::cancel()/restore() (intentionally NOT in
        // $fillable, so the edit form's fill() can never toggle it via mass-assignment).
        'is_cancelled' => 'boolean',
        'cancelled_at' => 'datetime',
        'attendees_notified_at' => 'datetime',
        'ical_sequence' => 'integer',
        // Set only by the platform-admin discovery toggle (intentionally NOT in $fillable).
        'is_hidden_from_discovery' => 'boolean',
        'rsvp_enabled' => 'boolean',
        'custom_fields' => 'array',
        'custom_field_values' => 'array',
        'last_translated_at' => 'datetime',
        'ticket_price' => 'decimal:2',
        'recurring_include_dates' => 'array',
        'recurring_exclude_dates' => 'array',
        'feedback_enabled' => 'boolean',
        'fan_comments_enabled' => 'boolean',
        'fan_photos_enabled' => 'boolean',
        'fan_videos_enabled' => 'boolean',
        'ask_phone' => 'boolean',
        'require_phone' => 'boolean',
        'country_code_phone' => 'boolean',
        'individual_tickets' => 'boolean',
        'individual_ticket_fields' => 'boolean',
        'sell_after_start' => 'boolean',
        'show_unavailable_tickets' => 'boolean',
    ];

    /** Per-request memo of pass advance-booking seats reserved, keyed by occurrence date. */
    protected $passReservedSeatsCache = [];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->description_html = MarkdownUtils::convertToHtml($model->description);
            $model->description_html_en = MarkdownUtils::convertToHtml($model->description_en);
            $model->ticket_notes_html = MarkdownUtils::convertToHtml($model->ticket_notes);
            $model->payment_instructions_html = MarkdownUtils::convertToHtml($model->payment_instructions);

            // Cache category_name whenever category_id changes (and creator_role_id is known).
            if ($model->isDirty('category_id') && $model->creator_role_id) {
                $creator = $model->relationLoaded('creatorRole') ? $model->creatorRole : Role::find($model->creator_role_id);
                $model->category_name = ($creator && $model->category_id)
                    ? $creator->getCategoryName((int) $model->category_id)
                    : null;
            }

            if ($model->isDirty('starts_at') && ! $model->days_of_week) {
                $model->load(['tickets', 'addons', 'sales']);

                // The occurrence moved, so re-key everything that hangs off its date. That key is
                // the VENUE's calendar date (what checkout stores and the scanner reads back), not
                // the UTC one - they differ for any evening event west of UTC.
                $newDate = $model->saleEventDateFromStartsAt();

                if ($newDate) {
                    DB::transaction(function () use ($model, $newDate) {
                        $allTickets = $model->tickets->merge($model->addons);
                        $allTickets->each(function ($ticket) use ($newDate) {
                            // A pass's inventory lives in a single 'pass' bucket (Ticket::soldKey),
                            // never under a date. Re-keying it to a date zeroes every pass sold.
                            if ($ticket->is_pass) {
                                return;
                            }

                            if ($ticket->sold) {
                                $sold = json_decode($ticket->sold, true);
                                if ($oldDate = array_key_first($sold)) {
                                    $quantity = $sold[$oldDate];
                                    $sold = [$newDate => $quantity];
                                    $ticket->sold = json_encode($sold);
                                    $ticket->save();
                                }
                            }
                        });

                        $model->sales->each(function ($sale) use ($newDate) {
                            $sale->event_date = $newDate;
                            $sale->save();
                        });
                    });
                }
            }

            if ($model->isDirty('name') && $model->exists) {
                if (! $model->isDirty('name_en')) {
                    $model->name_en = null;
                }
                $model->translation_attempts = 0;

                $eventRoles = EventRole::where('event_id', $model->id)->get();
                foreach ($eventRoles as $eventRole) {
                    $eventRole->name_translated = null;
                    $eventRole->translation_attempts = 0;
                    $eventRole->save();
                }
            }

            if ($model->isDirty('description') && $model->exists) {
                if (! $model->isDirty('description_en')) {
                    $model->description_en = null;
                    $model->description_html_en = null;
                }
                $model->translation_attempts = 0;

                $eventRoles = EventRole::where('event_id', $model->id)->get();
                foreach ($eventRoles as $eventRole) {
                    $eventRole->description_translated = null;
                    $eventRole->description_html_translated = null;
                    $eventRole->translation_attempts = 0;
                    $eventRole->save();
                }
            }

            if ($model->isDirty('short_description') && $model->exists) {
                if (! $model->isDirty('short_description_en')) {
                    $model->short_description_en = null;
                }
                $model->translation_attempts = 0;

                $eventRoles = EventRole::where('event_id', $model->id)->get();
                foreach ($eventRoles as $eventRole) {
                    $eventRole->short_description_translated = null;
                    $eventRole->translation_attempts = 0;
                    $eventRole->save();
                }
            }
        });

        static::deleting(function ($event) {
            // Cancel active boost campaigns on Meta and issue refunds
            $activeCampaigns = $event->boostCampaigns()
                ->whereIn('status', ['active', 'paused', 'pending_payment'])
                ->get();

            foreach ($activeCampaigns as $campaign) {
                try {
                    if ($campaign->meta_campaign_id && \App\Services\MetaAdsService::isBoostConfigured()) {
                        $metaService = app()->make(\App\Services\MetaAdsService::class);
                        $metaService->deleteCampaign($campaign);
                    }

                    $campaign->update(['status' => 'cancelled', 'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null]);

                    if (config('app.hosted') && ! config('app.is_testing')) {
                        $billingService = new \App\Services\BoostBillingService;
                        if ($campaign->billing_status === 'charged') {
                            $campaign->actual_spend && $campaign->actual_spend > 0
                                ? $billingService->refundUnspent($campaign)
                                : $billingService->refundFull($campaign);
                        } elseif ($campaign->billing_status === 'pending' && $campaign->stripe_payment_intent_id) {
                            $billingService->cancelPaymentIntent($campaign);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to cancel boost campaign during event deletion', [
                        'campaign_id' => $campaign->id,
                        'event_id' => $event->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Eager load roles with events count and user relationship
            $event->load(['roles' => function ($query) {
                $query->withCount('events')->with('user');
            }]);

            foreach ($event->roles as $role) {
                if (($role->isTalent() || $role->isVenue()) && ! $role->isRegistered()) {
                    if ($role->events_count == 1) {
                        $role->delete();
                    }
                }
            }

            if ($event->registration_url) {
                DB::table('parsed_event_urls')
                    ->where('url', $event->registration_url)
                    ->delete();
            }

            // Clean up event sponsor logo files
            if ($event->sponsor_logos) {
                $sponsors = json_decode($event->sponsor_logos, true) ?: [];
                foreach ($sponsors as $sponsor) {
                    if (! empty($sponsor['logo']) && ! str_starts_with($sponsor['logo'], 'demo_')) {
                        $path = $sponsor['logo'];
                        if (config('filesystems.default') == 'local') {
                            $path = 'public/'.$path;
                        }
                        Storage::delete($path);
                    }
                }
            }

            // Sync deletion to Google Calendar and CalDAV for all roles that have sync enabled.
            // Skipped when the delete was itself triggered by an inbound calendar sync (the remote
            // event is already gone) to avoid an echo-delete loop.
            if (! $event->skipOutboundCalendarSync) {
                $event->dispatchCalendarSync('delete');
            }
        });
    }

    /**
     * Apply a remote calendar deletion to this event, honoring the schedule's calendar_delete_action.
     * Returns the outcome ('ignored', 'deleted', 'cancelled', or 'guarded_cancelled') so the caller
     * can audit-log it. Loop-safe: never dispatches an outbound calendar sync.
     *
     *  - 'ignore'  -> leaves the event untouched (caller drops the stale sync mapping).
     *  - 'delete'  -> hard delete, EXCEPT events with ticket sales or live ad spend, which are
     *                 hidden instead to protect revenue/refund data.
     *  - 'cancel'  -> hides the event via is_cancelled (reversible).
     */
    public function applyInboundDeletion(string $action, ?Role $triggeringRole = null): string
    {
        if ($action === 'ignore') {
            return 'ignored';
        }

        // A shared event (attached to several schedules) must not be destroyed or hidden for everyone
        // just because ONE schedule's calendar copy was removed. When the deletion is triggered by a
        // role that does not own the event, detach that role instead - the event stays intact for its
        // owner and the other schedules. Only the owner/creator role may delete or cancel it outright.
        if ($triggeringRole && ! $this->isOwnedByRole($triggeringRole) && $this->roles()->count() > 1) {
            $this->roles()->detach($triggeringRole->id);

            return 'detached';
        }

        // Guard events carrying revenue/spend history from a hard-delete cascade - hide them instead so
        // the sale/refund and ad-spend records survive. Covers refunded (soft-deleted) sales too, and
        // any boost that ever incurred spend (including completed/charged), not just cancelable ones.
        $guarded = Sale::where('event_id', $this->id)->exists()
            || $this->boostCampaigns()->whereIn('status', ['active', 'paused', 'pending_payment', 'completed'])->exists();

        if ($action === 'delete' && ! $guarded) {
            $this->skipOutboundCalendarSync = true;
            $this->delete();

            return 'deleted';
        }

        // 'cancel', or a guarded 'delete': hide the event without pushing the change back out.
        if (! $this->is_cancelled) {
            $this->forceFill(['is_cancelled' => true, 'cancelled_at' => now()])->save();
        }

        return $action === 'delete' ? 'guarded_cancelled' : 'cancelled';
    }

    /**
     * Whether the given role owns this event (its creator role, or a role belonging to the event's
     * owning user). Used by inbound delete-sync to decide detach-vs-delete for shared events.
     */
    public function isOwnedByRole(Role $role): bool
    {
        return ($this->creator_role_id && (int) $this->creator_role_id === (int) $role->id)
            || (int) $this->user_id === (int) $role->user_id;
    }

    /**
     * Dispatch the given calendar-sync action ('create' / 'update' / 'delete') to Google Calendar and
     * CalDAV for every role (and synced member) that has sync enabled. Shared by the delete hook and by
     * the soft-cancel / restore flows (cancel removes the synced entry, restore re-creates it).
     */
    public function dispatchCalendarSync(string $action): void
    {
        foreach ($this->roles as $role) {
            if ($role->syncsToGoogle()) {
                $user = $role->user;
                if ($user && $user->google_token) {
                    SyncEventToGoogleCalendar::dispatchSync($this, $role, $action);
                }
            }

            foreach ($role->getMembersWithCalendarSync() as $member) {
                if ($member->google_token) {
                    SyncEventToGoogleCalendar::dispatchSync(
                        $this, $role, $action, $member, $member->pivot->google_calendar_id
                    );
                }
            }

            if ($role->syncsToMicrosoft()) {
                $user = $role->user;
                if ($user && $user->microsoft_token) {
                    SyncEventToMicrosoftCalendar::dispatchSync($this, $role, $action);
                }
            }

            if ($role->syncsToCalDAV()) {
                SyncEventToCalDAV::dispatchSync($this, $role, $action);
            }
        }
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class)->where('is_deleted', false)->where('is_addon', false)->orderBy('price', 'desc');
    }

    public function addons()
    {
        return $this->hasMany(Ticket::class)->where('is_deleted', false)->where('is_addon', true)->orderBy('price', 'desc');
    }

    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * Whether a gift card could be redeemed at this event's checkout. Matches the
     * redemption guard: the card's schedule must belong to the event's owner
     * (cards are sold on the schedule owner's payment rails but redemption
     * reduces the event owner's payout).
     */
    public function acceptsGiftCards(): bool
    {
        return $this->roles->contains(
            fn ($role) => $role->user_id === $this->user_id && $role->hasRedeemableGiftCards()
        );
    }

    public function hasActivePromoCodes(): bool
    {
        return $this->promoCodes()->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('times_used', '<', 'max_uses');
            })
            ->exists();
    }

    public function parts()
    {
        return $this->hasMany(EventPart::class)->orderBy('sort_order');
    }

    public function videos()
    {
        return $this->hasMany(EventVideo::class);
    }

    public function approvedVideos()
    {
        return $this->hasMany(EventVideo::class)->where('is_approved', true);
    }

    public function pendingVideos()
    {
        return $this->hasMany(EventVideo::class)->where('is_approved', false);
    }

    public function comments()
    {
        return $this->hasMany(EventComment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(EventComment::class)->where('is_approved', true);
    }

    public function pendingComments()
    {
        return $this->hasMany(EventComment::class)->where('is_approved', false);
    }

    public function photos()
    {
        return $this->hasMany(EventPhoto::class);
    }

    public function approvedPhotos()
    {
        return $this->hasMany(EventPhoto::class)->where('is_approved', true);
    }

    public function pendingPhotos()
    {
        return $this->hasMany(EventPhoto::class)->where('is_approved', false);
    }

    public function polls()
    {
        return $this->hasMany(EventPoll::class)->orderBy('sort_order');
    }

    public function activePolls()
    {
        return $this->hasMany(EventPoll::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function venue()
    {
        // Load venue from event_role table where the role is a venue
        return $this->belongsToMany(Role::class, 'event_role', 'event_id', 'role_id')
            ->where('roles.type', 'venue')
            ->withPivot('id', 'name_translated', 'short_description_translated', 'description_translated', 'description_html_translated', 'is_accepted', 'group_id', 'google_event_id', 'caldav_event_uid', 'caldav_event_etag')
            ->using(EventRole::class);
    }

    public function getVenueAttribute()
    {
        if (! $this->relationLoaded('roles')) {
            $this->load('roles');
        }

        foreach ($this->roles as $role) {
            if ($role->isVenue()) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Get a role associated with this event that has email settings configured.
     * Prefers venue, then first role, then any role with settings.
     * Falls back to venue-or-first if none have settings.
     */
    public function getRoleWithEmailSettings(): ?Role
    {
        if (! $this->relationLoaded('roles')) {
            $this->load('roles');
        }

        $venue = $this->venue;
        $firstRole = $this->roles->first();

        // Prefer venue if it has email settings
        if ($venue && $venue->hasEmailSettings()) {
            return $venue;
        }

        // Then first role if it has email settings
        if ($firstRole && $firstRole->hasEmailSettings()) {
            return $firstRole;
        }

        // Then any role with email settings
        foreach ($this->roles as $role) {
            if ($role->hasEmailSettings()) {
                return $role;
            }
        }

        // Fallback to original logic (so downstream "not configured" error still fires)
        return $venue ?: $firstRole;
    }

    public function getGroupIdForSubdomain($subdomain)
    {
        if (! $this->relationLoaded('roles')) {
            $this->load('roles');
        }

        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role ? $role->pivot->group_id : null;
    }

    public function creatorRole()
    {
        return $this->belongsTo(Role::class, 'creator_role_id');
    }

    /**
     * Resolve the display name for this event's category.
     * Prefers the creator schedule's effective list (so renames are retroactive),
     * falls back to the cached snapshot, then to system defaults.
     */
    public function resolveCategoryName(?string $locale = null): ?string
    {
        if (! $this->category_id) {
            return null;
        }

        $creator = $this->relationLoaded('creatorRole') ? $this->creatorRole : null;
        if (! $creator && $this->creator_role_id) {
            $creator = Role::find($this->creator_role_id);
        }
        if ($creator) {
            $name = $creator->getCategoryName((int) $this->category_id, $locale);
            if ($name) {
                return $name;
            }
        }

        if ($this->category_name) {
            return $this->category_name;
        }

        $systemDefaults = config('app.event_categories', []);

        return $systemDefaults[$this->category_id] ?? null;
    }

    /**
     * Resolve the assigned color for this event's category, via the creator schedule.
     * Returns null when no color is set or no creator schedule is known.
     */
    public function resolveCategoryColor(): ?string
    {
        if (! $this->category_id) {
            return null;
        }

        $creator = $this->relationLoaded('creatorRole') ? $this->creatorRole : null;
        if (! $creator && $this->creator_role_id) {
            $creator = Role::find($this->creator_role_id);
        }

        return $creator ? $creator->getCategoryColor((int) $this->category_id) : null;
    }

    public function curator()
    {
        // Return the creator role if it's a curator, otherwise return null
        if ($this->creatorRole && $this->creatorRole->isCurator()) {
            return $this->creatorRole;
        }

        return null;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withPivot('id', 'name_translated', 'short_description_translated', 'description_translated', 'description_html_translated', 'is_accepted', 'group_id', 'google_event_id', 'caldav_event_uid', 'caldav_event_etag')
            ->using(EventRole::class);
    }

    /**
     * Exclude events that look like throwaway test data from the discovery query.
     *
     * Conservative by design (it must not hide real events). An event is dropped when:
     *   (a) its name is obvious junk on its own ("test", "asdf", "aaaa", "test 1", ...), or
     *   (b) its name is a softer test signal ("test concert", "sample sale", ...) AND the
     *       event has no real content (no description, image, ticket/RSVP or URL), or
     *   (c) any associated schedule (talent/venue/curator) is named like a test.
     *
     * Only affects discovery surfaces (homepage Discover, /browse, /search); the event's
     * own guest page is unaffected.
     */
    public function scopeExcludeLikelyTest(Builder $query): Builder
    {
        // (a) event name is NOT strong junk
        $query->whereRaw('LOWER(TRIM(name)) NOT REGEXP ?', [self::LIKELY_TEST_NAME_REGEX])
            ->whereRaw('TRIM(name) NOT REGEXP ?', [self::REPEATED_CHAR_REGEX])
            ->whereNotIn(DB::raw('LOWER(TRIM(name))'), self::LIKELY_TEST_NAMES);

        // (b) NOT ( weak-junk name AND empty content )  ==  ( NOT weak  OR  has content )
        $query->where(function ($q) {
            $q->whereRaw('LOWER(TRIM(name)) NOT REGEXP ?', [self::LIKELY_TEST_WEAK_REGEX])
                ->orWhere(function ($c) {
                    $c->where(fn ($x) => $x->whereNotNull('description')->where('description', '!=', ''))
                        ->orWhere(fn ($x) => $x->whereNotNull('short_description')->where('short_description', '!=', ''))
                        ->orWhere(fn ($x) => $x->whereNotNull('flyer_image_url')->where('flyer_image_url', '!=', ''))
                        ->orWhere(fn ($x) => $x->whereNotNull('agenda_image_url')->where('agenda_image_url', '!=', ''))
                        ->orWhere(fn ($x) => $x->whereNotNull('event_url')->where('event_url', '!=', ''))
                        ->orWhere('tickets_enabled', true)
                        ->orWhere('rsvp_enabled', true);
                });
        });

        // (c) no associated talent/venue/curator schedule is named like a test
        $query->whereDoesntHave('roles', function ($r) {
            $r->whereRaw('LOWER(TRIM(roles.name)) REGEXP ?', [self::LIKELY_TEST_NAME_REGEX]);
        });

        return $query;
    }

    public function curatorBySubdomain($subdomain)
    {
        return $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain && $role->isCurator();
        });
    }

    public function sales()
    {
        return $this->hasMany(Sale::class)->where('is_deleted', false);
    }

    public function ticketWaitlists()
    {
        return $this->hasMany(TicketWaitlist::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(EventFeedback::class);
    }

    public function carpoolOffers()
    {
        return $this->hasMany(CarpoolOffer::class);
    }

    public function isFeedbackEnabled(?Role $role = null)
    {
        if (! is_null($this->feedback_enabled)) {
            return (bool) $this->feedback_enabled;
        }

        if (! $role) {
            $role = $this->roles->first(fn ($role) => $role->isTalent()) ?? $this->roles->first();
        }

        return $role ? (bool) $role->feedback_enabled : false;
    }

    public function isFanCommentsEnabled()
    {
        if (! is_null($this->fan_comments_enabled)) {
            return (bool) $this->fan_comments_enabled;
        }

        $role = $this->roles->first(fn ($role) => $role->isTalent()) ?? $this->roles->first();

        return $role ? (bool) $role->fan_comments_enabled : true;
    }

    public function isFanPhotosEnabled()
    {
        if (! is_null($this->fan_photos_enabled)) {
            return (bool) $this->fan_photos_enabled;
        }

        $role = $this->roles->first(fn ($role) => $role->isTalent()) ?? $this->roles->first();

        return $role ? (bool) $role->fan_photos_enabled : true;
    }

    public function isFanVideosEnabled()
    {
        if (! is_null($this->fan_videos_enabled)) {
            return (bool) $this->fan_videos_enabled;
        }

        $role = $this->roles->first(fn ($role) => $role->isTalent()) ?? $this->roles->first();

        return $role ? (bool) $role->fan_videos_enabled : true;
    }

    public function isFanContentEnabled()
    {
        return $this->isFanCommentsEnabled() || $this->isFanPhotosEnabled() || $this->isFanVideosEnabled();
    }

    public function getAverageRating($eventDate = null)
    {
        $query = $this->feedbacks();
        if ($eventDate) {
            $query->where('event_date', $eventDate);
        }

        return round($query->avg('rating'), 1);
    }

    public function getFeedbackCount($eventDate = null)
    {
        $query = $this->feedbacks();
        if ($eventDate) {
            $query->where('event_date', $eventDate);
        }

        return $query->count();
    }

    public function members()
    {
        return $this->roles->filter(function ($role) {
            return $role->isTalent();
        });
    }

    public function role()
    {
        return $this->roles->first(function ($role) {
            return $role->isTalent();
        });
    }

    /**
     * Ticket notes rendered for display: the markdown HTML with template
     * variables ({event_name}, {venue}, {date}, ...) substituted for the given
     * occurrence date. Used in confirmation emails and on the guest ticket page.
     * Returns null when there are no notes. The role is resolved robustly
     * (NOT via role(), which is talent-only and can be null).
     */
    public function parsedTicketNotesHtml(?string $date = null, ?Role $role = null): ?string
    {
        if (empty($this->ticket_notes_html)) {
            return null;
        }

        $role = $role ?? $this->getRoleWithEmailSettings() ?? $this->roles->first();
        if (! $role) {
            return $this->ticket_notes_html;
        }

        return EventTextGenerator::parseInlineVariables($this->ticket_notes_html, $this, $role, [
            'date' => $date,
            'escapeHtml' => true,
        ]);
    }

    /**
     * Plain-text counterpart of parsedTicketNotesHtml() for text emails.
     */
    public function parsedTicketNotesText(?string $date = null, ?Role $role = null): ?string
    {
        if (empty($this->ticket_notes_html)) {
            return null;
        }

        $text = html_entity_decode(strip_tags($this->ticket_notes_html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $role = $role ?? $this->getRoleWithEmailSettings() ?? $this->roles->first();
        if (! $role) {
            return $text;
        }

        return EventTextGenerator::parseInlineVariables($text, $this, $role, [
            'date' => $date,
            'escapeHtml' => false,
        ]);
    }

    /**
     * Get a role that can be used to view this event publicly.
     * Priority: first claimed role, then creatorRole if claimed, then any role.
     */
    public function getViewableRole()
    {
        // First, try to find a claimed role
        $claimed = $this->roles->first(fn ($role) => $role->isClaimed());
        if ($claimed) {
            return $claimed;
        }

        // Fall back to creatorRole (which should be claimed if it's a curator)
        if ($this->creatorRole && $this->creatorRole->isClaimed()) {
            return $this->creatorRole;
        }

        // Last resort: return first role even if unclaimed
        return $this->roles->first();
    }

    public function isPro()
    {
        foreach ($this->roles as $role) {
            if ($role->isPro()) {
                return true;
            }
        }

        return false;
    }

    public function isPrivate()
    {
        return (bool) $this->is_private;
    }

    public function isInternal()
    {
        return (bool) $this->is_internal;
    }

    /**
     * Resolve the event's four-state visibility into a single label.
     * Mirrors the Vue getter on the event form. Internal is checked before draft
     * (internal implies draft), and draft before private, so a legacy row that has
     * both is_draft and is_private set safely reads as the more-hidden "draft".
     */
    public function visibilityState(): string
    {
        if ($this->is_internal) {
            return 'internal';
        }
        if ($this->is_draft) {
            return 'draft';
        }
        if ($this->is_private) {
            return 'unlisted';
        }

        return 'public';
    }

    /**
     * Apply a four-state visibility label to the underlying boolean columns.
     * Mirrors the Vue setter. The Enterprise gate + invariant in EventRepo::saveEvent
     * still run on save, so this never leaves an incoherent stored state.
     */
    public function setVisibilityState(string $state): void
    {
        $this->is_internal = $state === 'internal';
        $this->is_private = $state === 'unlisted';
        $this->is_draft = in_array($state, ['draft', 'internal']);

        if ($state !== 'unlisted') {
            $this->event_password = null;
        }
    }

    public function isPasswordProtected()
    {
        return ! empty($this->event_password);
    }

    public function isAtVenue($subdomain)
    {
        return $this->venue && $this->venue->subdomain == $subdomain;
    }

    public function isRoleAMember($subdomain, $includeCurators = false)
    {
        return $this->roles->contains(function ($role) use ($subdomain, $includeCurators) {
            return $role->subdomain == $subdomain && ($role->isTalent() || ($includeCurators && $role->isCurator()));
        });
    }

    public function curators()
    {
        return $this->belongsToMany(Role::class, 'event_role', 'event_id', 'role_id');
    }

    public function hashedId()
    {
        return UrlUtils::encodeId($this->id);
    }

    /**
     * $timezone pins the rendering to a specific zone (pass scheduleTimezone() to show the
     * event in the venue's local time); otherwise it follows the viewer's.
     */
    public function localStartsAt($pretty = false, $date = null, $endTime = false, ?string $timezone = null)
    {
        if (! $this->starts_at) {
            return '';
        }

        $subdomain = request()->subdomain;
        $role = false;
        $enable24 = false;

        if ($subdomain) {
            $role = $this->roles->first(function ($role) use ($subdomain) {
                return $role->subdomain == $subdomain;
            });

            if ($role) {
                $enable24 = $role->use_24_hour_time;
            }
        }

        if ($user = auth()->user()) {
            if ($user->use_24_hour_time !== null) {
                $enable24 = $user->use_24_hour_time;
            }
        }

        $startAt = $this->getStartDateTime($date, true, $timezone);

        // Multi-day events in pretty mode: show date range instead of single datetime
        if ($pretty && $this->is_multi_day) {
            $endAt = $startAt->copy()->addMinutes($this->durationInMinutes());

            if ($role && $role->language_code) {
                $startAt->setLocale($role->language_code);
                $endAt->setLocale($role->language_code);
                if ($startAt->year !== $endAt->year) {
                    return $startAt->translatedFormat('F j, Y').' - '.$endAt->translatedFormat('F j, Y');
                } elseif ($startAt->month !== $endAt->month) {
                    return $startAt->translatedFormat('F j').' - '.$endAt->translatedFormat('F j, Y');
                } else {
                    return $startAt->translatedFormat('F j').' - '.$endAt->translatedFormat('j, Y');
                }
            }

            if ($startAt->year !== $endAt->year) {
                return $startAt->format('M j, Y').' - '.$endAt->format('M j, Y');
            } elseif ($startAt->month !== $endAt->month) {
                return $startAt->format('M j').' - '.$endAt->format('M j, Y');
            } else {
                return $startAt->format('M j').' - '.$endAt->format('j, Y');
            }
        }

        $format = $pretty ? ($enable24 ? 'D, M jS • H:i' : 'D, M jS • g:i A') : 'Y-m-d H:i:s';

        // Set locale for date translation if pretty is true and role has language_code
        if ($pretty && $role && $role->language_code) {
            $startAt->setLocale($role->language_code);
            $localizedFormat = $enable24 ? 'l, j F • H:i' : 'l, j F • g:i A';
            $value = $startAt->translatedFormat($localizedFormat);
        } else {
            $value = $startAt->format($format);
        }

        if ($endTime && $this->duration > 0) {
            $startDate = $startAt->format('Y-m-d');
            $startAt->addMinutes($this->durationInMinutes());
            $endDate = $startAt->format('Y-m-d');

            if ($startDate == $endDate) {
                $value .= ' '.__('messages.to').' '.$startAt->format($enable24 ? 'H:i' : 'g:i A');
            } else {
                if ($pretty && $role && $role->language_code) {
                    $localizedFormat = $enable24 ? 'l, j F • H:i' : 'l, j F • g:i A';
                    $value = $value.'<br/>'.__('messages.to').'<br/>'.$startAt->translatedFormat($localizedFormat);
                } else {
                    $value = $value.'<br/>'.__('messages.to').'<br/>'.$startAt->format($format);
                }
            }
        }

        return $value;
    }

    public function scopeInMonth($query, $gridStartUtc)
    {
        return $query->where(function ($q) use ($gridStartUtc) {
            $q->where('starts_at', '>=', $gridStartUtc)
                ->orWhereNotNull('days_of_week')
                ->orWhere(function ($q2) use ($gridStartUtc) {
                    $q2->where('duration', '>=', 24)
                        ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$gridStartUtc]);
                });
        });
    }

    public function scopeUpcomingOrOngoing($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where(function ($q) use ($date) {
            $q->where('starts_at', '>=', $date)
                ->orWhere(function ($q2) use ($date) {
                    $q2->where('duration', '>=', 24)
                        ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$date]);
                });
        });
    }

    public function scopeFullyPast($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where('starts_at', '<', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('duration')
                    ->orWhere('duration', '<', 24)
                    ->orWhereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) < ?', [$date]);
            });
    }

    /**
     * The timezone this event's calendar dates are expressed in: the schedule's, not the
     * viewer's. An occurrence falls on a given day because of where the event happens, not
     * because of who is looking at it.
     */
    public function scheduleTimezone(): string
    {
        return $this->creatorRole?->timezone ?? config('app.timezone');
    }

    /**
     * Today's calendar date at the venue (Y-m-d).
     *
     * This is the date `sales.event_date` and `sale_tickets.pass_usages[].date` are keyed by,
     * so anything matching against those columns must use this rather than `now()`, which is
     * in the app timezone. For an evening event west of UTC the two disagree for exactly the
     * hours the doors are open.
     */
    public function scheduleToday(?Carbon $now = null): string
    {
        return ($now ? $now->copy() : now())->setTimezone($this->scheduleTimezone())->format('Y-m-d');
    }

    /**
     * The distinct venue-local "today" of each event in $events (Y-m-d).
     *
     * Use this to bound a query over `event_date` when the rows belong to schedules in
     * different timezones, then match per event with scheduleToday(). Exact by construction -
     * do not approximate it with a +/- 1 day window around the app timezone, which breaks once
     * APP_TIMEZONE is set to an extreme offset.
     *
     * @param  \Illuminate\Support\Collection<int, Event>  $events
     * @return string[]
     */
    public static function scheduleTodayDates($events, ?Carbon $now = null): array
    {
        return $events->map(fn (Event $event) => $event->scheduleToday($now))->unique()->values()->all();
    }

    /**
     * A UTC instant rendered the way localStartsAt() renders occurrence dates:
     * the authenticated viewer's timezone and 12/24-hour preference when set
     * (so it never disagrees with the date_label beside it), otherwise the
     * schedule's; language from the schedule. Used for instants that are not
     * occurrence starts (e.g. a booking's cancellation deadline). Queued mail
     * has no auth user, so emails render in the schedule's timezone.
     */
    public function localizedInstantLabel(Carbon $utcInstant): string
    {
        $tz = $this->scheduleTimezone();
        $role = $this->creatorRole;
        $enable24 = (bool) ($role?->use_24_hour_time);

        if ($user = auth()->user()) {
            $tz = $user->timezone ?? 'UTC';
            if ($user->use_24_hour_time !== null) {
                $enable24 = $user->use_24_hour_time;
            }
        }

        $local = $utcInstant->copy()->setTimezone($tz);

        if ($role && $role->language_code) {
            return $local->locale($role->language_code)
                ->translatedFormat($enable24 ? 'j F Y • H:i' : 'j F Y • g:i A');
        }

        return $local->format($enable24 ? 'M j, Y H:i' : 'M j, Y g:i A');
    }

    /**
     * The UTC instant at which this event's occurrence on $date starts.
     *
     * $date is a venue-local calendar date; the time of day comes from `starts_at`. Setting
     * the date on the raw UTC datetime instead would be off by a day whenever the venue's
     * date differs from the UTC date.
     */
    public function occurrenceStartUtc(string $date, ?string $timezone = null): Carbon
    {
        $tz = $timezone ?: $this->scheduleTimezone();

        $startsAt = strlen((string) $this->starts_at) === 10
            ? Carbon::createFromFormat('Y-m-d', $this->starts_at, 'UTC')->startOfDay()
            : Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC');

        $timeOfDay = $startsAt->copy()->setTimezone($tz)->format('H:i:s');

        return Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$timeOfDay, $tz)->setTimezone('UTC');
    }

    /**
     * The event's start as a naive Carbon holding local wall-clock time.
     *
     * Equivalent to Carbon::parse($this->localStartsAt()) when no timezone is passed. Callers
     * that care which day an occurrence falls on should pass scheduleTimezone(); otherwise
     * getStartDateTime() resolves against the authenticated user's timezone, and a late-evening
     * event lands on tomorrow's date for an operator in a different zone.
     */
    protected function localStartCarbon(?string $timezone = null): Carbon
    {
        // A date-only starts_at is already the schedule's calendar date (see
        // saleEventDateFromStartsAt()); treating it as midnight UTC and converting would
        // slide it back a day for any negative-offset schedule.
        if (strlen((string) $this->starts_at) === 10) {
            return Carbon::parse($this->starts_at)->startOfDay();
        }

        return Carbon::parse($this->getStartDateTime(null, true, $timezone)->format('Y-m-d H:i:s'));
    }

    public function matchesDate($date, ?string $timezone = null)
    {
        if (! $this->starts_at) {
            return false;
        }

        // Reduce $date to a bare calendar day before anything else. Callers pass a mix of naive
        // and timezone-aware Carbons; localStartCarbon() always yields a naive one. Any instant
        // comparison between the two silently mixes zones - matchesFrequency()'s every_n_weeks
        // branch measures `startOfWeek()->diffInDays()`, so a +09:00 $date turns 14 days into
        // 13.625 and floor(/7) loses a whole week, inverting every bi-weekly occurrence.
        $date = Carbon::parse(Carbon::parse($date)->format('Y-m-d'))->startOfDay();

        if ($this->days_of_week) {
            $startDate = $this->localStartCarbon($timezone)->startOfDay();
            $afterStartDate = $startDate->format('Y-m-d') <= $date->format('Y-m-d');

            if (! $afterStartDate) {
                return false;
            }

            $dateStr = Carbon::parse($date)->format('Y-m-d');

            // Exclude dates have highest priority
            if (! empty($this->recurring_exclude_dates) && in_array($dateStr, $this->recurring_exclude_dates)) {
                return false;
            }

            // Include dates bypass pattern and end checks (but still require on/after start date)
            if (! empty($this->recurring_include_dates) && in_array($dateStr, $this->recurring_include_dates)) {
                return true;
            }

            // Check if date matches the frequency pattern
            $frequency = $this->recurring_frequency ?? 'weekly';

            if (! $this->matchesFrequency($frequency, $date, $startDate)) {
                return false;
            }

            // Check recurring end conditions
            $recurringEndType = $this->recurring_end_type ?? 'never';

            if ($recurringEndType === 'on_date' && $this->recurring_end_value) {
                $endDate = Carbon::createFromFormat('Y-m-d', $this->recurring_end_value)->startOfDay();
                $checkDate = Carbon::parse($date)->startOfDay();
                if ($checkDate->greaterThan($endDate)) {
                    return false;
                }
            } elseif ($recurringEndType === 'after_events' && $this->recurring_end_value) {
                $maxOccurrences = (int) $this->recurring_end_value;
                $checkDate = Carbon::parse($date)->startOfDay();

                $occurrenceCount = $this->countOccurrences($frequency, $startDate, $checkDate);

                if ($occurrenceCount > $maxOccurrences) {
                    return false;
                }
            }

            return true;
        } else {
            $startDate = $this->localStartCarbon($timezone)->startOfDay();
            if ($this->duration && $this->duration >= 24) {
                $endDate = $this->localStartCarbon($timezone)->addMinutes($this->durationInMinutes())->startOfDay();
                $dateStr = Carbon::parse($date)->format('Y-m-d');

                // Calendar-date comparison, for the same reason as the recurring branch above.
                return $dateStr >= $startDate->format('Y-m-d') && $dateStr <= $endDate->format('Y-m-d');
            }

            return $startDate->isSameDay($date);
        }
    }

    protected function matchesFrequency(string $frequency, Carbon $date, Carbon $startDate): bool
    {
        switch ($frequency) {
            case 'daily':
                return true;

            case 'weekly':
                return $this->days_of_week[$date->dayOfWeek] === '1';

            case 'every_n_weeks':
                if ($this->days_of_week[$date->dayOfWeek] !== '1') {
                    return false;
                }
                $interval = $this->recurring_interval ?? 2;
                $daysDiff = $startDate->copy()->startOfWeek(Carbon::SUNDAY)->diffInDays($date->copy()->startOfWeek(Carbon::SUNDAY));
                $weeksDiff = (int) floor($daysDiff / 7);

                return $weeksDiff % $interval === 0;

            case 'monthly_date':
                return $date->day === $startDate->day;

            case 'monthly_weekday':
                // Match nth weekday (e.g., 2nd Tuesday)
                $nthWeekday = (int) ceil($startDate->day / 7);
                $targetDayOfWeek = $startDate->dayOfWeek;
                $dateNthWeekday = (int) ceil($date->day / 7);

                return $date->dayOfWeek === $targetDayOfWeek && $dateNthWeekday === $nthWeekday;

            case 'yearly':
                return $date->month === $startDate->month && $date->day === $startDate->day;

            default:
                return $this->days_of_week[$date->dayOfWeek] === '1';
        }
    }

    protected function countOccurrences(string $frequency, Carbon $startDate, Carbon $checkDate): int
    {
        switch ($frequency) {
            case 'daily':
                $count = $startDate->diffInDays($checkDate) + 1;
                break;

            case 'monthly_date':
                $count = 0;
                $current = $startDate->copy();
                while ($current->lte($checkDate)) {
                    $count++;
                    $current->addMonth();
                }
                break;

            case 'monthly_weekday':
                $count = 0;
                $nthWeekday = (int) ceil($startDate->day / 7);
                $targetDayOfWeek = $startDate->dayOfWeek;
                $current = $startDate->copy()->startOfMonth();
                while ($current->lte($checkDate)) {
                    // Find the nth target weekday in this month
                    $targetMonth = $current->month;
                    $found = 0;
                    $candidate = $current->copy();
                    while ($candidate->month === $targetMonth) {
                        if ($candidate->dayOfWeek === $targetDayOfWeek) {
                            $found++;
                            if ($found === $nthWeekday) {
                                if ($candidate->gte($startDate) && $candidate->lte($checkDate)) {
                                    $count++;
                                }
                                break;
                            }
                        }
                        $candidate->addDay();
                    }
                    // Move to next month
                    $current->addMonth()->startOfMonth();
                }
                break;

            case 'yearly':
                $count = 0;
                $current = $startDate->copy();
                while ($current->lte($checkDate)) {
                    $count++;
                    $current->addYear();
                }
                break;

            case 'every_n_weeks':
                $interval = $this->recurring_interval ?? 2;
                $count = 0;
                $currentDate = $startDate->copy();
                while ($currentDate->lte($checkDate)) {
                    $daysDiff = $startDate->copy()->startOfWeek(Carbon::SUNDAY)->diffInDays($currentDate->copy()->startOfWeek(Carbon::SUNDAY));
                    $weeksDiff = (int) floor($daysDiff / 7);
                    if ($weeksDiff % $interval === 0 && $this->days_of_week[$currentDate->dayOfWeek] === '1') {
                        $count++;
                    }
                    $currentDate->addDay();
                }
                break;

            case 'weekly':
            default:
                $count = 0;
                $currentDate = $startDate->copy();
                while ($currentDate->lte($checkDate)) {
                    if ($this->days_of_week[$currentDate->dayOfWeek] === '1') {
                        $count++;
                    }
                    $currentDate->addDay();
                }
                break;
        }

        // Adjust count for include/exclude dates
        if (! empty($this->recurring_exclude_dates)) {
            foreach ($this->recurring_exclude_dates as $excludeDateStr) {
                $excludeDate = Carbon::createFromFormat('Y-m-d', $excludeDateStr)->startOfDay();
                if ($excludeDate->gte($startDate) && $excludeDate->lte($checkDate)
                    && $this->matchesFrequency($frequency, $excludeDate, $startDate)) {
                    $count--;
                }
            }
        }

        if (! empty($this->recurring_include_dates)) {
            foreach ($this->recurring_include_dates as $includeDateStr) {
                $includeDate = Carbon::createFromFormat('Y-m-d', $includeDateStr)->startOfDay();
                if ($includeDate->gte($startDate) && $includeDate->lte($checkDate)
                    && ! $this->matchesFrequency($frequency, $includeDate, $startDate)) {
                    $count++;
                }
            }
        }

        return max(0, $count);
    }

    public function canSellTickets($date = null)
    {
        // A cancelled event never sells tickets or accepts RSVPs (covers the guest checkout/RSVP
        // flows and the API, which all gate on this method).
        if ($this->is_cancelled) {
            return false;
        }

        // Pass / subscription tickets are not tied to the container event's own
        // date (a "Subscriptions" event may be dateless or in the past); their
        // validity is governed by pass expiry, so don't block their sale on date.
        $hasPassTicket = $this->tickets->contains(fn ($t) => $t->is_pass);

        // For recurring events, check if the specific occurrence is in the past. Resolve the
        // occurrence in the venue's timezone: whether a ticket may be sold is a property of the
        // event, not of who is asking, and getStartDateTime() would otherwise use the viewer's.
        if ($this->days_of_week && $date) {
            $tz = $this->scheduleTimezone();
            if ($this->sell_after_start) {
                if ($this->getEndDateTime($date, true, $tz)->isPast()) {
                    return false;
                }
            } elseif ($this->getStartDateTime($date, true, $tz)->isPast()) {
                return false;
            }
        }

        // For non-recurring events, check if the event start time is in the past
        if (! $this->days_of_week && $this->starts_at && ! $hasPassTicket) {
            if ($this->sell_after_start) {
                if ($this->getEndDateTime(null, true)->isPast()) {
                    return false;
                }
            } elseif ($this->is_multi_day) {
                if ($this->getEndDateTime(null, true)->isPast()) {
                    return false;
                }
            } elseif (Carbon::parse($this->starts_at)->isPast()) {
                return false;
            }
        }

        if ($this->tickets->isNotEmpty() && $this->allTicketSalesEnded() && ! $this->show_unavailable_tickets) {
            return false;
        }

        if ($this->tickets->isNotEmpty() && $this->allTicketSalesNotStarted() && ! $this->show_unavailable_tickets) {
            return false;
        }

        return $this->tickets_enabled && $this->isPro();
    }

    public function allTicketSalesEnded()
    {
        if ($this->tickets->isEmpty()) {
            return false;
        }

        return $this->tickets->every(function ($ticket) {
            return $ticket->sales_end_at && $ticket->sales_end_at->isPast();
        });
    }

    public function allTicketSalesNotStarted()
    {
        if ($this->tickets->isEmpty()) {
            return false;
        }

        return $this->tickets->every(function ($ticket) {
            return $ticket->sales_start_at && $ticket->sales_start_at->isFuture();
        });
    }

    public function areTicketsFree()
    {
        return $this->tickets->every(function ($ticket) {
            return $ticket->price == 0;
        });
    }

    public function isFree()
    {
        if ($this->rsvp_enabled) {
            return true;
        }

        if ($this->tickets_enabled) {
            return $this->areTicketsFree();
        }

        return $this->ticket_price !== null && $this->ticket_price == 0;
    }

    public function canAcceptRsvp($date = null)
    {
        if ($this->is_cancelled) {
            return false;
        }

        if (! $this->rsvp_enabled) {
            return false;
        }

        // Check if event is past
        if ($this->recurring_frequency) {
            // End of the occurrence's day AT THE VENUE. Carbon::parse($date) would use the app
            // timezone, closing RSVP at UTC midnight - an hour before doors for a 9pm New York
            // show, and nine hours into the next venue day for Tokyo.
            if ($date && Carbon::parse($date, $this->scheduleTimezone())->endOfDay()->isPast()) {
                return false;
            }
        } else {
            if ($this->getEndDateTime(null, true)->endOfDay()->isPast()) {
                return false;
            }
        }

        return true;
    }

    public function rsvpSoldCount($date)
    {
        $sold = $this->rsvp_sold ? json_decode($this->rsvp_sold, true) : [];

        return $sold[$date] ?? 0;
    }

    public function rsvpRemaining($date)
    {
        if (! $this->rsvp_limit) {
            return null;
        }

        return max(0, $this->rsvp_limit - $this->rsvpSoldCount($date));
    }

    public function isRsvpFull($date)
    {
        if (! $this->rsvp_limit) {
            return false;
        }

        return $this->rsvpSoldCount($date) >= $this->rsvp_limit;
    }

    public function updateRsvpSold($date, $quantity)
    {
        DB::transaction(function () use ($date, $quantity) {
            $event = Event::lockForUpdate()->find($this->id);
            $sold = $event->rsvp_sold ? json_decode($event->rsvp_sold, true) : [];
            $sold[$date] = max(0, ($sold[$date] ?? 0) + $quantity);
            $event->rsvp_sold = json_encode($sold);
            $event->save();
        });
    }

    public function getImageUrl()
    {
        if ($this->flyer_image_url) {
            return $this->flyer_image_url;
        } elseif ($this->role() && $this->role()->profile_image_url) {
            return $this->role()->profile_image_url;
        } elseif ($this->venue && $this->venue->profile_image_url) {
            return $this->venue->profile_image_url;
        }

        return null;
    }

    public function getLanguageCode()
    {
        if ($this->venue && $this->venue->language_code) {
            return $this->venue->language_code;
        }

        $lang = 'en';

        foreach ($this->roles as $role) {
            if ($role->isTalent() && $role->language_code) {
                $lang = $role->language_code;
                break;
            }
        }

        return $lang;
    }

    public function getVenueDisplayName($translate = true)
    {
        if ($this->venue) {
            return $this->venue->shortVenue($translate);
        }

        return $this->getEventUrlDomain();
    }

    public function getEventUrlDomain()
    {
        if ($this->event_url) {
            $parsedUrl = parse_url($this->event_url);

            if (isset($parsedUrl['host'])) {
                return $parsedUrl['host'];
            } else {
                return $this->event_url;
            }
        }

        return '';
    }

    public function getSponsorLogos(): array
    {
        if (! $this->sponsor_logos) {
            return [];
        }

        $sponsors = json_decode($this->sponsor_logos, true);

        if (! is_array($sponsors)) {
            return [];
        }

        $useTranslation = session()->has('translate') || request()->lang == 'en';

        foreach ($sponsors as &$sponsor) {
            if (! empty($sponsor['logo'])) {
                $filename = $sponsor['logo'];

                if (str_starts_with($filename, 'demo_')) {
                    $sponsor['logo_url'] = url('/images/demo/'.$filename);
                } elseif (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                    $sponsor['logo_url'] = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
                } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
                    $sponsor['logo_url'] = url('/storage/'.$filename);
                } else {
                    $sponsor['logo_url'] = $filename;
                }
            } else {
                $sponsor['logo_url'] = '';
            }

            if ($useTranslation && ! empty($sponsor['name_en'])) {
                $sponsor['display_name'] = $sponsor['name_en'];
            } else {
                $sponsor['display_name'] = $sponsor['name'] ?? '';
            }
        }

        return $sponsors;
    }

    public function getEffectiveSponsorLogos($role): array
    {
        if ($this->sponsor_mode === 'none') {
            return [];
        }

        if ($this->sponsor_mode === 'custom') {
            return $this->getSponsorLogos();
        }

        return $role->getSponsorLogos();
    }

    public function getGuestUrl($subdomain = false, $date = null, $useCustomDomain = false, $includeId = true)
    {
        $data = $this->getGuestUrlData($subdomain, $date, $includeId);

        if (! $data['subdomain']) {
            \Log::error('No subdomain found for event '.$this->id);

            return '';
        }

        // Select the correct route name based on available data
        $routeName = 'event.view_guest';
        if (isset($data['date'])) {
            $routeName = 'event.view_guest_full';
        } elseif (isset($data['id'])) {
            $routeName = 'event.view_guest_with_id';
        }

        // Check if the role has a custom domain
        $role = $this->roles->first(function ($role) use ($data) {
            return $role->subdomain == $data['subdomain'];
        });

        if ($role && $role->custom_domain && $useCustomDomain) {
            if ($role->custom_domain_mode !== 'direct' || $role->custom_domain_status === 'active') {
                $url = route($routeName, $data, false);
                $url = $role->custom_domain.$url;

                return $url;
            }
        }

        return route($routeName, $data);
    }

    public function getPhotoGalleryUrl($subdomain = false, $date = null)
    {
        $url = $this->getGuestUrl($subdomain, $date);

        return $url ? $url.'/photos' : '';
    }

    public function getGuestUrlData($subdomain = false, $date = null, $includeId = true)
    {
        $venueSubdomain = $this->venue && $this->venue->isClaimed() ? $this->venue->subdomain : null;
        $roleSubdomain = $this->role() && $this->role()->isClaimed() ? $this->role()->subdomain : null;

        if (! $subdomain) {
            $subdomain = $roleSubdomain ? $roleSubdomain : $venueSubdomain;
        }

        if (! $subdomain) {
            $subdomain = $this->creatorRole ? $this->creatorRole->subdomain : null;

            // Temp fix - remove once curator_id is corrected
            // Check if the given subdomain matches any of the roles
            if ($subdomain) {
                $matchingRole = $this->roles->first(function ($role) use ($subdomain) {
                    return $role->subdomain == $subdomain;
                });

                // If no matching role, try to find the first claimed role
                if (! $matchingRole) {
                    $claimedRole = $this->roles->first(function ($role) {
                        return $role->isClaimed();
                    });

                    if ($claimedRole) {
                        $subdomain = $claimedRole->subdomain;
                    }
                }
            }
        }

        $slug = $this->slug;

        $isCurator = $this->creatorRole && $this->creatorRole->isCurator();
        if ($venueSubdomain && $roleSubdomain && ! $isCurator) {
            $slug = $venueSubdomain == $subdomain ? $roleSubdomain : $venueSubdomain;
        }

        if ($date === null && $this->starts_at) {
            // The venue's calendar date, not the UTC one: this becomes the date segment of a
            // recurring event's guest URL, and the UTC date of an evening show west of UTC is
            // the following day - a weekday the recurrence does not fall on. (It also handles a
            // date-only starts_at, which the raw createFromFormat('Y-m-d H:i:s', ...) would throw on.)
            $date = $this->saleEventDateFromStartsAt();
        }

        $data = [
            'subdomain' => $subdomain,
            'slug' => $slug,
        ];

        if ($includeId) {
            $data['id'] = UrlUtils::encodeId($this->id);
        }

        // Only include the date for recurring events, and only when the id is
        // also included. The dated guest route (event.view_guest_full) requires
        // the id segment, so a date without an id would be unroutable.
        if ($includeId && $date && $this->days_of_week) {
            $data['date'] = $date;
        }

        return $data;
    }

    public function getTitle()
    {
        $title = __('messages.event_title');

        return str_replace([':role', ':venue'], [$this->name, $this->venue ? $this->venue->getDisplayName() : $this->getEventUrlDomain()], $title);
    }

    public function getMetaDescription($date = null)
    {
        if ($this->short_description) {
            return \Illuminate\Support\Str::limit($this->translatedShortDescription(), 155);
        }

        if ($this->description_html) {
            return \Illuminate\Support\Str::limit(trim(strip_tags($this->translatedDescription())), 155);
        }

        $str = $this->translatedName();

        if ($this->venue) {
            $str .= ' '.__('messages.at').' '.$this->venue->getDisplayName();
        } elseif ($this->getEventUrlDomain()) {
            $str .= ' '.__('messages.at').' '.$this->getEventUrlDomain();
        }

        if ($this->is_multi_day) {
            $str .= ' | '.$this->getDateRangeDisplay($date);
        } else {
            $str .= ' | '.$this->localStartsAt(true, $date);
        }

        return \Illuminate\Support\Str::limit($str, 155);
    }

    public function getGoogleCalendarUrl($date = null)
    {
        $title = $this->getTitle();
        $description = $this->description_html ? strip_tags($this->description_html) : ($this->role() ? strip_tags($this->role()->description_html) : '');
        $location = $this->venue ? $this->venue->bestAddress() : '';
        $duration = $this->duration > 0 ? $this->duration : 2;
        // Stamped as UTC: a dated occurrence must be rebuilt from the venue's time-of-day, or the
        // link lands a day early for an evening event west of UTC. (Matches FeedController's iCal.)
        $startAt = $date ? $this->occurrenceStartUtc($date) : $this->getStartDateTime();
        $startDate = $startAt->format('Ymd\THis\Z');
        $endDate = $startAt->addMinutes(self::durationHoursToMinutes($duration))->format('Ymd\THis\Z');

        $url = 'https://calendar.google.com/calendar/r/eventedit?';
        $url .= 'text='.urlencode($title);
        $url .= '&dates='.$startDate.'/'.$endDate;
        $url .= '&details='.urlencode($description);
        $url .= '&location='.urlencode($location);

        return $url;
    }

    public function getAppleCalendarUrl($date = null)
    {
        $guestUrl = $this->getGuestUrl(false, $date);

        if (! $guestUrl) {
            return '';
        }

        return $guestUrl.'/ical';
    }

    public function getMicrosoftCalendarUrl($date = null)
    {
        $title = $this->getTitle();
        $description = $this->description_html ? strip_tags($this->description_html) : ($this->role() ? strip_tags($this->role()->description_html) : '');
        $location = $this->venue ? $this->venue->bestAddress() : '';
        $duration = $this->duration > 0 ? $this->duration : 2;
        // See getGoogleCalendarUrl(): the stamp is UTC, so a dated occurrence needs the venue's
        // time-of-day rather than the UTC one.
        $startAt = $date ? $this->occurrenceStartUtc($date) : $this->getStartDateTime();
        $startDate = $startAt->format('Y-m-d\TH:i:s\Z');
        $endDate = $startAt->addMinutes(self::durationHoursToMinutes($duration))->format('Y-m-d\TH:i:s\Z');

        $url = 'https://outlook.live.com/calendar/0/deeplink/compose?';
        $url .= 'subject='.urlencode($title);
        $url .= '&body='.urlencode($description);
        $url .= '&startdt='.$startDate;
        $url .= '&enddt='.$endDate;
        $url .= '&location='.urlencode($location);
        $url .= '&allday=false';

        return $url;
    }

    public function getStartDateTime($date = null, $locale = false, $timezoneOverride = null)
    {
        $timezone = 'UTC';

        if ($timezoneOverride) {
            $timezone = $timezoneOverride;
        } elseif ($user = auth()->user()) {
            $timezone = $user->timezone ?? 'UTC';
        } elseif ($this->creatorRole) {
            $timezone = $this->creatorRole->timezone ?? 'UTC';
        }

        if (strlen($this->starts_at) === 10) {
            // Date-only format (Y-m-d), assume midnight
            $startAt = Carbon::createFromFormat('Y-m-d', $this->starts_at, 'UTC')->startOfDay();
        } else {
            $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC');
        }

        // Convert before applying the occurrence date. $date is a calendar date in the
        // schedule's zone (that is how sales.event_date is stored), so setting it on the UTC
        // datetime and converting afterwards slides an evening event back a day.
        if ($locale) {
            $startAt->setTimezone($timezone);
        }

        if ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $customDate = Carbon::parse($date);
            $startAt->setDate($customDate->year, $customDate->month, $customDate->day);
        }

        return $startAt;
    }

    /**
     * The timezone this event's wall-clock is anchored to: the recorded capture timezone
     * (events.timezone) when known, otherwise the creator's account timezone for legacy rows —
     * which is exactly the timezone pre-fix capture used, so legacy detection stays accurate.
     */
    public function getEffectiveTimezone(): string
    {
        if ($this->timezone) {
            return $this->timezone;
        }

        return $this->user?->timezone ?? config('app.timezone');
    }

    /**
     * Whether this event is timed and its effective timezone differs from the given schedule's
     * timezone, i.e. it may publish at the wrong time in that schedule's graphics/emails. This is
     * the single source of truth for the "events in different timezones" warnings; every surface
     * must use it rather than re-implementing the comparison.
     */
    public function isOffTimezoneFor(Role $schedule): bool
    {
        // Date-only events (Y-m-d, no wall-clock) can't be made wrong by a timezone.
        if (! $this->starts_at || strlen((string) $this->starts_at) === 10) {
            return false;
        }

        // Without a schedule timezone there is nothing to compare against.
        if (! $schedule->timezone) {
            return false;
        }

        return $this->getEffectiveTimezone() !== $schedule->timezone;
    }

    public function use24HourTime()
    {
        if ($user = auth()->user()) {
            if ($user->use_24_hour_time !== null) {
                return (bool) $user->use_24_hour_time;
            }
        }

        return $this->creatorRole && $this->creatorRole->use_24_hour_time;
    }

    public function getTimeFormat()
    {
        return $this->use24HourTime() ? 'H:i' : 'g:i A';
    }

    public function getDateTimeFormat($includeYear = false)
    {
        $format = $this->getTimeFormat();

        if ($includeYear) {
            return 'F jS, Y '.$format;
        } else {
            return 'F jS '.$format;
        }
    }

    public function isMultiDay()
    {
        return ! $this->getStartDateTime(null, true)->isSameDay($this->getStartDateTime(null, true)->addMinutes($this->durationInMinutes()));
    }

    public function getIsMultiDayAttribute(): bool
    {
        return $this->duration >= 24;
    }

    /**
     * Canonical conversion of a duration in hours (float) to whole minutes.
     * Durations are stored as hours with limited precision (e.g. 50 min -> 0.83),
     * so rounding to the nearest minute recovers the exact minute the user entered
     * and avoids the end time displaying one minute early (e.g. 8:20 -> 8:19).
     */
    public static function durationHoursToMinutes($hours): int
    {
        return (int) round(((float) $hours) * 60);
    }

    public function durationInMinutes(): int
    {
        return static::durationHoursToMinutes($this->duration);
    }

    /**
     * The occurrence's clock time at the venue. An event's time is a property of where it happens,
     * not of who is looking - and these strings go out in emails, which render inside whichever
     * request dispatched them (EmailService sends synchronously from the admin portal).
     */
    public function getStartEndTime($date = null, $use24 = false)
    {
        $date = $this->getStartDateTime($date, true, $this->scheduleTimezone());

        if ($this->is_multi_day) {
            return $date->format($use24 ? 'H:i' : 'g:i A');
        }

        if ($this->duration > 0) {
            $endDate = $date->copy()->addMinutes($this->durationInMinutes());

            return $date->format($use24 ? 'H:i' : 'g:i A').' - '.$endDate->format($use24 ? 'H:i' : 'g:i A');
        } else {
            return $date->format($use24 ? 'H:i' : 'g:i A');
        }
    }

    public function getDateRangeDisplay($date = null)
    {
        // Venue-local, for the same reason as getStartEndTime().
        $start = $this->getStartDateTime($date, true, $this->scheduleTimezone());
        $end = $start->copy()->addMinutes($this->durationInMinutes());

        if ($start->year !== $end->year) {
            return $start->translatedFormat('F j, Y').' - '.$end->translatedFormat('F j, Y');
        } elseif ($start->month !== $end->month) {
            return $start->translatedFormat('F j').' - '.$end->translatedFormat('F j, Y');
        } else {
            return $start->translatedFormat('F j').' - '.$end->translatedFormat('j, Y');
        }
    }

    public function getShortDateRangeDisplay($fallbackFormat = 'M j, Y')
    {
        if (! $this->starts_at) {
            return '';
        }

        $s = $this->getStartDateTime(null, true);

        if ($this->is_multi_day) {
            $e = $s->copy()->addMinutes($this->durationInMinutes());

            if ($s->year !== $e->year) {
                return $s->format('M j, Y').' - '.$e->format('M j, Y');
            } elseif ($s->month !== $e->month) {
                return $s->format('M j').' - '.$e->format('M j, Y');
            } else {
                return $s->format('M j').' - '.$e->format('j, Y');
            }
        }

        return $s->format($fallbackFormat);
    }

    public function getFlyerImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        // Handle demo images in public/images/demo/
        if (str_starts_with($value, 'demo_')) {
            return url('/images/demo/'.$value);
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    public function getAgendaImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        // Handle demo images in public/images/demo/
        if (str_starts_with($value, 'demo_')) {
            return url('/images/demo/'.$value);
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    public function getOtherRole($subdomain)
    {
        if ($this->role() && $subdomain == $this->role()->subdomain) {
            return $this->venue;
        } else {
            return $this->role();
        }
    }

    public function translatedName()
    {
        $value = $this->name;

        if ($this->name_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->name_en;
        }

        $value = str_ireplace('fuck', 'F@#%', $value);

        return $value;
    }

    public function translatedDescription()
    {
        $value = $this->description_html;

        if ($this->description_html_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->description_html_en;
        }

        return $value;
    }

    public function translatedShortDescription()
    {
        $value = $this->short_description;

        if ($this->short_description_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->short_description_en;
        }

        return $value;
    }

    public function englishName()
    {
        return $this->name_en ?: $this->name;
    }

    public function englishDescriptionHtml()
    {
        return $this->description_html_en ?: $this->description_html;
    }

    public function englishShortDescription()
    {
        return $this->short_description_en ?: $this->short_description;
    }

    public function toApiData()
    {
        $data = new \stdClass;

        if (! $this->isPro()) {
            return $data;
        }

        $data->id = UrlUtils::encodeId($this->id);
        $data->url = $this->getGuestUrl();
        $data->name = $this->name;
        $data->short_description = $this->short_description;
        $data->description = $this->description;
        $data->starts_at = $this->starts_at;
        $data->duration = $this->duration;
        $data->category_id = $this->category_id;
        $data->category_name = $this->resolveCategoryName();
        $data->category_color = $this->resolveCategoryColor();
        $data->is_private = (bool) $this->is_private;
        $data->is_draft = (bool) $this->is_draft;
        $data->is_internal = (bool) $this->is_internal;
        $data->is_password_protected = $this->isPasswordProtected();
        $data->event_url = $this->event_url;
        $data->registration_url = $this->registration_url;
        $data->venue_id = $this->venue ? UrlUtils::encodeId($this->venue->id) : null;
        $data->venue_name = $this->venue?->name;
        $data->venue_address1 = $this->venue?->address1;
        $data->venue_subdomain = $this->venue?->subdomain;

        // Flyer image URL
        $rawFlyer = $this->getAttributes()['flyer_image_url'] ?? null;
        $data->flyer_image_url = $rawFlyer ? $this->flyer_image_url : null;

        // Recurring config
        $data->schedule_type = $this->recurring_frequency ? 'recurring' : 'single';
        if ($this->recurring_frequency) {
            $data->recurring_frequency = $this->recurring_frequency;
            $data->recurring_interval = $this->recurring_interval;
            $data->days_of_week = $this->days_of_week;
            $data->recurring_end_type = $this->recurring_end_type;
            $data->recurring_end_value = $this->recurring_end_value;
        }

        // RSVP
        $data->rsvp_enabled = (bool) $this->rsvp_enabled;
        $data->rsvp_limit = $this->rsvp_limit;

        // Tickets
        $data->tickets_enabled = (bool) $this->tickets_enabled;
        if ($this->tickets_enabled && $this->relationLoaded('tickets')) {
            $data->tickets = $this->tickets->map(function ($ticket) {
                $row = [
                    'id' => UrlUtils::encodeId($ticket->id),
                    'type' => $ticket->type,
                    'price' => $ticket->price,
                    'quantity' => $ticket->quantity,
                    'description' => $ticket->description,
                    'sales_start_at' => $ticket->sales_start_at ? $ticket->sales_start_at->toIso8601String() : null,
                    'sales_end_at' => $ticket->sales_end_at ? $ticket->sales_end_at->toIso8601String() : null,
                    'volume_discount' => TicketVolumeDiscount::toGuestPayload($ticket->volume_discount),
                    'is_pass' => (bool) $ticket->is_pass,
                ];

                if ($ticket->is_pass) {
                    $row['pass_usage_type'] = $ticket->pass_usage_type;
                    $row['pass_max_uses'] = $ticket->pass_max_uses ?: null;
                    $row['pass_valid_days'] = $ticket->pass_valid_days ?: null;
                    $row['pass_scope'] = $ticket->pass_scope;
                    $row['pass_allow_booking'] = (bool) $ticket->pass_allow_booking;
                    $row['pass_admits_per_event'] = $ticket->admitsPerEvent();
                    $row['pass_covered_count'] = $ticket->pass_scope === 'specific_events'
                        ? count($ticket->pass_event_ids ?? [])
                        : null;
                }

                return $row;
            })->values();

            if ($this->relationLoaded('addons')) {
                $data->addons = $this->addons->map(function ($addon) {
                    return [
                        'id' => UrlUtils::encodeId($addon->id),
                        'type' => $addon->type,
                        'price' => $addon->price,
                        'quantity' => $addon->quantity,
                        'description' => $addon->description,
                        'image_url' => $addon->image_url ?: null,
                        'url' => $addon->url ?: null,
                    ];
                })->values();
            }
        }

        $data->members = $this->members()->mapWithKeys(function ($member) {
            return [UrlUtils::encodeId($member->id) => [
                'name' => $member->name,
                'email' => $member->email,
                'youtube_url' => $member->getFirstVideoUrl(),
            ]];
        });

        $data->event_parts = $this->parts->map(function ($part) {
            return [
                'id' => UrlUtils::encodeId($part->id),
                'name' => $part->name,
                'description' => $part->description,
                'start_time' => $part->start_time,
                'end_time' => $part->end_time,
            ];
        })->values();

        $data->ticket_currency_code = $this->ticket_currency_code;
        $data->payment_method = $this->payment_method;
        $data->terms_url = $this->terms_url;

        // Schedules associated with this event
        if ($this->relationLoaded('roles')) {
            // Batch-load groups to avoid N+1 queries on pivot->group
            $groupIds = $this->roles->pluck('pivot.group_id')->filter()->unique()->values();
            $groups = $groupIds->isNotEmpty()
                ? Group::whereIn('id', $groupIds)->get()->keyBy('id')
                : collect();

            $data->schedules = $this->roles->map(function ($role) use ($groups) {
                $schedule = [
                    'id' => UrlUtils::encodeId($role->id),
                    'subdomain' => $role->subdomain,
                    'name' => $role->name,
                    'type' => $role->type,
                ];

                if ($role->pivot && $role->pivot->group_id) {
                    $group = $groups->get($role->pivot->group_id);
                    if ($group) {
                        $schedule['group'] = [
                            'id' => UrlUtils::encodeId($group->id),
                            'name' => $group->name,
                            'slug' => $group->slug,
                        ];
                    }
                }

                return $schedule;
            })->values();
        }

        $data->created_at = $this->created_at ? $this->created_at->toIso8601String() : null;
        $data->updated_at = $this->updated_at ? $this->updated_at->toIso8601String() : null;

        return $data;
    }

    public function allTicketsSoldOut($date)
    {
        // Sold out when the shared per-occurrence house (after both regular sales and
        // pass advance-bookings) has no seats left. Null = unlimited => never sold out.
        // Equivalent to the old per-ticket "every sold out" check when nothing is
        // reserved, and correctly accounts for pass reservations when they exist.
        $remaining = $this->occurrenceSeatsRemaining($date);

        return $remaining !== null && $remaining <= 0;
    }

    /**
     * Seat-defining tickets: sellable regular tickets only. tickets() already
     * excludes add-ons; this also excludes passes, which do not define a
     * per-occurrence seat capacity - they draw from it via advance booking.
     */
    public function seatTickets()
    {
        return $this->tickets->reject(fn ($ticket) => $ticket->is_pass)->values();
    }

    public function hasSameTicketQuantities()
    {
        $tickets = $this->seatTickets();
        if ($tickets->count() <= 1) {
            return false;
        }

        $quantities = $tickets->pluck('quantity')->filter(function ($qty) {
            return $qty > 0;
        })->unique();

        return $quantities->count() === 1;
    }

    public function getSameTicketQuantity()
    {
        if (! $this->hasSameTicketQuantities()) {
            return null;
        }

        return $this->seatTickets()->first()->quantity;
    }

    public function getTotalTicketQuantity()
    {
        // For combined mode, the total should be the same as the individual quantity
        if ($this->total_tickets_mode === 'combined' && $this->hasSameTicketQuantities()) {
            return $this->getSameTicketQuantity();
        }

        return $this->seatTickets()->sum('quantity');
    }

    /**
     * Single source of truth for how many seats remain at a given occurrence after
     * BOTH regular sales and pass advance-bookings, against one shared per-occurrence
     * house capacity. Null = unlimited (no defined ceiling). Used by the booking side
     * (PassBookingService::seatsLeft) and every regular-sale availability check, so the
     * two can never disagree and oversell. Safe for non-pass events: reserved is 0 and
     * the house equals the sum of per-ticket limits, so this never binds tighter than
     * the existing per-ticket checks.
     */
    public function occurrenceSeatsRemaining(?string $date): ?int
    {
        if (! $date) {
            return null;
        }

        $seatTickets = $this->seatTickets();

        // No seat tickets, or any unlimited seat ticket => no defined ceiling.
        if ($seatTickets->isEmpty() || $seatTickets->contains(fn ($t) => $t->quantity <= 0)) {
            return null;
        }

        $capacity = ($this->total_tickets_mode === 'combined' && $this->hasSameTicketQuantities())
            ? (int) $this->getSameTicketQuantity()
            : (int) $seatTickets->sum('quantity');

        $regularSold = $seatTickets->sum(fn ($t) => $t->soldCountFor($date));

        return max(0, $capacity - $regularSold - $this->passReservedSeats($date));
    }

    /**
     * Seats reserved by pass advance-bookings for this event's given occurrence
     * date. Counts committed pass_usages entries naming this event+date across
     * active (paid) pass sale-tickets in this event's schedule. Reservations live
     * only in pass_usages (the source of truth), so a refunded/cancelled/expired
     * pass drops out of "paid" and releases its seats automatically. Memoized per
     * date for the request; can be optimized with a materialized counter later.
     */
    public function passReservedSeats(string $date): int
    {
        if (! array_key_exists($date, $this->passReservedSeatsCache)) {
            $this->passReservedSeatsCache[$date] = $this->computePassReservedSeats($date);
        }

        return $this->passReservedSeatsCache[$date];
    }

    protected function computePassReservedSeats(string $date): int
    {
        // A pass is sold on its own home event, which may live on a DIFFERENT schedule than
        // this one - a curator's pass covering a venue event cross-listed on the curator's
        // schedule. Ticket::covers() resolves coverage within the pass's home schedule, and
        // that schedule must therefore list this event, so every schedule listing this event
        // bounds the search exactly. Scoping to creatorRole alone hides those reservations
        // and lets occurrenceSeatsRemaining() resell a seat that is already held.
        $schedules = $this->roles;
        if ($this->creatorRole && ! $schedules->contains('id', $this->creatorRole->id)) {
            $schedules = $schedules->concat([$this->creatorRole]);
        }

        $scheduleIds = $schedules->pluck('id')->filter()->unique();
        if ($scheduleIds->isEmpty()) {
            return 0;
        }

        // An event belongs to a schedule either through the event_role pivot or through
        // creator_role_id, and a pass's home event may be linked by only one of them. Cover both.
        //
        // Do NOT filter the schedules by Role::hasPass() first - that helper only looks through
        // the pivot, so it drops a schedule whose pass lives on an event linked to it solely by
        // creator_role_id, and the reservation goes uncounted.
        //
        // Kept as a subquery rather than a plucked id list: a large curator schedule can list
        // tens of thousands of events, and binding one placeholder each would be slow and can
        // exceed MySQL's prepared-statement placeholder limit. A fresh builder per call, since
        // whereIn() consumes it.
        $scheduleEventIds = fn () => static::query()
            ->select('id')
            ->where(fn ($query) => $query
                ->whereIn('creator_role_id', $scheduleIds)
                ->orWhereHas('roles', fn ($q) => $q->whereIn('roles.id', $scheduleIds)));

        // Cheap short-circuit for the overwhelming majority of schedules, which sell no passes.
        $hasPass = Ticket::query()
            ->where('is_pass', true)
            ->whereIn('event_id', $scheduleEventIds())
            ->exists();

        if (! $hasPass) {
            return 0;
        }

        $saleTickets = SaleTicket::query()
            ->whereNotNull('pass_usages')
            ->whereHas('ticket', fn ($q) => $q->where('is_pass', true))
            ->whereHas('sale', fn ($q) => $q->where('status', 'paid')->whereIn('event_id', $scheduleEventIds()))
            ->get(['id', 'pass_usages']);

        $count = 0;
        foreach ($saleTickets as $saleTicket) {
            foreach (($saleTicket->pass_usages ?? []) as $usage) {
                if ((int) ($usage['event_id'] ?? 0) === (int) $this->id
                    && ($usage['date'] ?? null) === $date
                    // A forfeited booking keeps the visit consumed but returns
                    // its seat(s) to the pool.
                    && SaleTicket::usageKind($usage) !== 'forfeited') {
                    // A single visit may admit more than one person (holder plus
                    // guests), so each occupies a seat in the shared pool.
                    $count += max(1, (int) ($usage['admits'] ?? 1));
                }
            }
        }

        return $count;
    }

    /**
     * Get Google event ID for a specific role (uses owner's sync record)
     */
    public function getGoogleEventIdForRole($roleId)
    {
        $role = $this->roles->first(function ($role) use ($roleId) {
            return $role->id == $roleId;
        });

        if (! $role) {
            return null;
        }

        return CalendarSync::where('user_id', $role->user_id)
            ->where('event_id', $this->id)
            ->where('role_id', $roleId)
            ->first()?->google_event_id;
    }

    /**
     * Set Google event ID for a specific role (uses owner's sync record)
     */
    public function setGoogleEventIdForRole($roleId, $googleEventId)
    {
        $role = $this->roles->first(function ($role) use ($roleId) {
            return $role->id == $roleId;
        });

        if (! $role) {
            return;
        }

        if ($googleEventId) {
            CalendarSync::updateOrCreate(
                ['user_id' => $role->user_id, 'event_id' => $this->id, 'role_id' => $roleId],
                ['google_event_id' => $googleEventId]
            );
        } else {
            CalendarSync::where('user_id', $role->user_id)
                ->where('event_id', $this->id)
                ->where('role_id', $roleId)
                ->delete();
        }
    }

    /**
     * Get Google event ID for the role defined by subdomain
     */
    public function getGoogleEventIdForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role ? $this->getGoogleEventIdForRole($role->id) : null;
    }

    /**
     * Set Google event ID for the role defined by subdomain
     */
    public function setGoogleEventIdForSubdomain($subdomain, $googleEventId)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        if ($role) {
            $this->setGoogleEventIdForRole($role->id, $googleEventId);
        }
    }

    /**
     * Sync this event to Google Calendar for all connected users
     */
    public function syncToGoogleCalendar($action = 'create')
    {
        // Owner sync
        foreach ($this->roles as $role) {
            if ($role->syncsToGoogle()) {
                $user = $role->user;
                if ($user && $user->google_token) {
                    SyncEventToGoogleCalendar::dispatchSync($this, $role, $action);
                }
            }
        }

        // Member sync (admins/followers with personal calendar sync enabled)
        foreach ($this->roles as $role) {
            foreach ($role->getMembersWithCalendarSync() as $member) {
                if ($member->google_token) {
                    SyncEventToGoogleCalendar::dispatchSync(
                        $this, $role, $action, $member, $member->pivot->google_calendar_id
                    );
                }
            }
        }
    }

    /**
     * Check if this event is synced to Google Calendar for a specific role
     */
    public function isSyncedToGoogleCalendarForRole($roleId)
    {
        return ! is_null($this->getGoogleEventIdForRole($roleId));
    }

    /**
     * Check if this event is synced to Google Calendar for the role defined by subdomain
     */
    public function isSyncedToGoogleCalendarForSubdomain($subdomain)
    {
        return ! is_null($this->getGoogleEventIdForSubdomain($subdomain));
    }

    /**
     * Check if this event is synced to Google Calendar for the role defined by subdomain
     */
    public function canBeSyncedToGoogleCalendarForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role && $role->hasGoogleCalendarIntegration() && $role->syncsToGoogle();
    }

    /**
     * Get Google Calendar sync status for a specific user and role
     */
    public function getGoogleCalendarSyncStatus(User $user, $roleId = null)
    {
        if (! $user->google_token) {
            return 'not_connected';
        }

        if ($roleId && $this->isSyncedToGoogleCalendarForRole($roleId)) {
            return 'synced';
        }

        return 'not_synced';
    }

    /**
     * Get Outlook / Microsoft event ID for a specific role (uses owner's sync record)
     */
    public function getMicrosoftEventIdForRole($roleId)
    {
        $role = $this->roles->first(function ($role) use ($roleId) {
            return $role->id == $roleId;
        });

        if (! $role) {
            return null;
        }

        return MicrosoftCalendarSync::where('user_id', $role->user_id)
            ->where('event_id', $this->id)
            ->where('role_id', $roleId)
            ->first()?->microsoft_event_id;
    }

    /**
     * Set Outlook / Microsoft event ID for a specific role (uses owner's sync record)
     */
    public function setMicrosoftEventIdForRole($roleId, $microsoftEventId)
    {
        $role = $this->roles->first(function ($role) use ($roleId) {
            return $role->id == $roleId;
        });

        if (! $role) {
            return;
        }

        if ($microsoftEventId) {
            MicrosoftCalendarSync::updateOrCreate(
                ['user_id' => $role->user_id, 'event_id' => $this->id, 'role_id' => $roleId],
                ['microsoft_event_id' => $microsoftEventId]
            );
        } else {
            MicrosoftCalendarSync::where('user_id', $role->user_id)
                ->where('event_id', $this->id)
                ->where('role_id', $roleId)
                ->delete();
        }
    }

    /**
     * Get Outlook / Microsoft event ID for the role defined by subdomain
     */
    public function getMicrosoftEventIdForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role ? $this->getMicrosoftEventIdForRole($role->id) : null;
    }

    /**
     * Sync this event to Outlook / Microsoft calendar for the schedule owner
     */
    public function syncToMicrosoftCalendar($action = 'create')
    {
        foreach ($this->roles as $role) {
            if ($role->syncsToMicrosoft()) {
                $user = $role->user;
                if ($user && $user->microsoft_token) {
                    SyncEventToMicrosoftCalendar::dispatchSync($this, $role, $action);
                }
            }
        }
    }

    /**
     * Check if this event is synced to Outlook / Microsoft calendar for a specific role
     */
    public function isSyncedToMicrosoftCalendarForRole($roleId)
    {
        return ! is_null($this->getMicrosoftEventIdForRole($roleId));
    }

    /**
     * Check if this event is synced to Outlook / Microsoft calendar for the role defined by subdomain
     */
    public function isSyncedToMicrosoftCalendarForSubdomain($subdomain)
    {
        return ! is_null($this->getMicrosoftEventIdForSubdomain($subdomain));
    }

    /**
     * Check if this event can be synced to Outlook / Microsoft calendar for the role defined by subdomain
     */
    public function canBeSyncedToMicrosoftCalendarForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role && $role->hasMicrosoftCalendarIntegration() && $role->syncsToMicrosoft();
    }

    /**
     * Get end date/time for the event
     */
    public function getEndDateTime($date = null, $locale = false, $timezoneOverride = null)
    {
        $startAt = $this->getStartDateTime($date, $locale, $timezoneOverride);
        $duration = $this->duration > 0 ? $this->duration : 2; // Default to 2 hours if no duration

        return $startAt->copy()->addMinutes(self::durationHoursToMinutes($duration));
    }

    /**
     * Calendar date (Y-m-d) in the creator schedule timezone for starts_at, for sale/RSVP event_date defaults.
     */
    public function saleEventDateFromStartsAt(): ?string
    {
        if (! $this->starts_at) {
            return null;
        }

        // Date-only starts_at already represents the calendar date in the schedule's view.
        if (strlen($this->starts_at) === 10) {
            return $this->starts_at;
        }

        $this->loadMissing('creatorRole');
        $tz = $this->creatorRole?->timezone ?? config('app.timezone');

        return Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC')->timezone($tz)->format('Y-m-d');
    }

    /**
     * Get location schema data for JSON-LD
     */
    public function getSchemaLocation()
    {
        // Always return a location object (required by Google)
        // Use venue if available, otherwise fallback to organizer or event name
        if ($this->venue) {
            $venueName = $this->venue->translatedName();
            if (empty($venueName)) {
                $venueName = $this->translatedName(); // Fallback to event name
            }

            $location = [
                '@type' => 'Place',
                'name' => $venueName,
            ];

            // Add address if available
            $address = [];
            if ($this->venue->translatedAddress1()) {
                $address['streetAddress'] = $this->venue->translatedAddress1();
                if ($this->venue->translatedAddress2()) {
                    $address['streetAddress'] .= ', '.$this->venue->translatedAddress2();
                }
            }
            if ($this->venue->translatedCity()) {
                $address['addressLocality'] = $this->venue->translatedCity();
            }
            if ($this->venue->translatedState()) {
                $address['addressRegion'] = $this->venue->translatedState();
            }
            if ($this->venue->postal_code) {
                $address['postalCode'] = $this->venue->postal_code;
            }
            if ($this->venue->country_code) {
                $address['addressCountry'] = strtoupper($this->venue->country_code);
            }

            // Always include address field (required by Google)
            // If we have address data, use it; otherwise provide minimal address
            if (! empty($address)) {
                $address['@type'] = 'PostalAddress';
                $location['address'] = $address;
            } else {
                // Provide minimal address object to satisfy Google's requirement
                $location['address'] = [
                    '@type' => 'PostalAddress',
                ];
            }

            // Add geo coordinates if available
            if ($this->venue->geo_lat && $this->venue->geo_lon) {
                $location['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => (float) $this->venue->geo_lat,
                    'longitude' => (float) $this->venue->geo_lon,
                ];
            }

            return $location;
        }

        // Fallback: use organizer name if available
        $organizer = $this->getSchemaOrganizer();
        $locationName = $organizer['name'] ?? $this->translatedName();

        $location = [
            '@type' => 'Place',
            'name' => $locationName,
        ];

        // Try to get address from organizer role if available
        $address = [];
        $organizerRole = null;

        // Check if organizer is a role with address information
        if ($this->role() && $this->role()->isClaimed()) {
            $organizerRole = $this->role();
        } elseif ($this->creatorRole) {
            $organizerRole = $this->creatorRole;
        }

        if ($organizerRole) {
            if ($organizerRole->translatedAddress1()) {
                $address['streetAddress'] = $organizerRole->translatedAddress1();
                if ($organizerRole->translatedAddress2()) {
                    $address['streetAddress'] .= ', '.$organizerRole->translatedAddress2();
                }
            }
            if ($organizerRole->translatedCity()) {
                $address['addressLocality'] = $organizerRole->translatedCity();
            }
            if ($organizerRole->translatedState()) {
                $address['addressRegion'] = $organizerRole->translatedState();
            }
            if ($organizerRole->postal_code) {
                $address['postalCode'] = $organizerRole->postal_code;
            }
            if ($organizerRole->country_code) {
                $address['addressCountry'] = strtoupper($organizerRole->country_code);
            }
        }

        // Always include address field (required by Google)
        // If we have address data, use it; otherwise provide minimal address
        if (! empty($address)) {
            $address['@type'] = 'PostalAddress';
            $location['address'] = $address;
        } else {
            // Provide minimal address object to satisfy Google's requirement
            $location['address'] = [
                '@type' => 'PostalAddress',
            ];
        }

        return $location;
    }

    /**
     * Get offers schema data for JSON-LD (tickets)
     * Always returns at least a default free offer if no tickets are available
     */
    public function getSchemaOffers()
    {
        $url = $this->getGuestUrl();
        $validFrom = $this->created_at ? $this->created_at->toIso8601String() : $this->getSchemaStartDate();

        if ($this->tickets_enabled && $this->isPro() && ! $this->tickets->isEmpty()) {
            $offers = [];
            $currency = $this->ticket_currency_code ?: 'USD';

            foreach ($this->tickets as $ticket) {
                $offer = [
                    '@type' => 'Offer',
                    'price' => (float) $ticket->price,
                    'priceCurrency' => $currency,
                    'url' => $url.(strpos($url, '?') !== false ? '&' : '?').'tickets=true',
                    'availability' => 'https://schema.org/InStock',
                    'validFrom' => $validFrom,
                ];

                if ($ticket->name) {
                    $offer['name'] = $ticket->name;
                }

                if ($ticket->quantity > 0) {
                    $offer['inventoryLevel'] = $ticket->quantity;
                }

                $offers[] = $offer;
            }

            return $offers;
        }

        // Return default free offer if no tickets
        return [
            [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
                'url' => $url,
                'availability' => 'https://schema.org/InStock',
                'validFrom' => $validFrom,
            ],
        ];
    }

    /**
     * Get performers schema data for JSON-LD
     */
    public function getSchemaPerformers()
    {
        $performers = [];
        $members = $this->members();

        foreach ($members as $member) {
            $performer = [
                '@type' => 'Person',
                'name' => $member->translatedName(),
            ];

            if ($member->getGuestUrl()) {
                $performer['url'] = $member->getGuestUrl();
            }

            $performers[] = $performer;
        }

        return ! empty($performers) ? $performers : null;
    }

    /**
     * Get event status for JSON-LD
     */
    public function getSchemaEventStatus()
    {
        if ($this->is_cancelled) {
            return 'https://schema.org/EventCancelled';
        }

        if (! $this->starts_at) {
            return 'https://schema.org/EventScheduled';
        }

        // EventScheduled is the appropriate status for all non-cancelled events.
        return 'https://schema.org/EventScheduled';
    }

    /**
     * Get organizer schema data for JSON-LD
     * Always returns an organizer (with fallback if needed)
     * Ensures both "name" and "url" fields are always present
     */
    public function getSchemaOrganizer()
    {
        $eventUrl = $this->getGuestUrl();
        $eventName = $this->translatedName() ?: 'Event Organizer';

        if ($this->venue && $this->venue->isClaimed()) {
            $name = $this->venue->translatedName();
            $url = $this->venue->getGuestUrl();

            return [
                '@type' => 'Organization',
                'name' => $name ?: $eventName,
                'url' => $url ?: $eventUrl,
            ];
        } elseif ($this->role() && $this->role()->isClaimed()) {
            $name = $this->role()->translatedName();
            $url = $this->role()->getGuestUrl();

            return [
                '@type' => 'Person',
                'name' => $name ?: $eventName,
                'url' => $url ?: $eventUrl,
            ];
        } elseif ($this->creatorRole) {
            // Fallback to creator role
            $name = $this->creatorRole->translatedName();
            $url = $this->creatorRole->getGuestUrl();

            return [
                '@type' => $this->creatorRole->isVenue() ? 'Organization' : 'Person',
                'name' => $name ?: $eventName,
                'url' => $url ?: $eventUrl,
            ];
        }

        // Final fallback - use event name as organizer
        return [
            '@type' => 'Organization',
            'name' => $eventName,
            'url' => $eventUrl,
        ];
    }

    /**
     * Get event attendance mode for JSON-LD
     */
    public function getSchemaAttendanceMode()
    {
        if ($this->event_url && $this->venue) {
            return 'https://schema.org/MixedEventAttendanceMode';
        } elseif ($this->event_url) {
            return 'https://schema.org/OnlineEventAttendanceMode';
        }

        return 'https://schema.org/OfflineEventAttendanceMode';
    }

    /**
     * Get description for JSON-LD
     * Always returns a description (with fallback if needed)
     */
    public function getSchemaDescription()
    {
        $description = $this->translatedDescription();
        $description = trim(strip_tags($description));

        if (empty($description)) {
            // Fallback description
            return $this->translatedName().' - '.__('messages.event');
        }

        return $description;
    }

    /**
     * Get ISO 8601 formatted date string for schema.
     *
     * Structured data must be absolute: pinned to the venue so a crawler and a signed-in owner
     * emit the same instant, not just the same wall-clock with a different offset.
     */
    public function getSchemaStartDate($date = null)
    {
        $startAt = $this->getStartDateTime($date, true, $this->scheduleTimezone());

        return $startAt->toIso8601String();
    }

    /**
     * Get ISO 8601 formatted end date string for schema
     */
    public function getSchemaEndDate($date = null)
    {
        $endAt = $this->getEndDateTime($date, true, $this->scheduleTimezone());

        return $endAt->toIso8601String();
    }

    /**
     * Get CalDAV event UID for a specific role
     */
    public function getCalDAVEventUidForRole($roleId)
    {
        $eventRole = $this->roles->first(function ($role) use ($roleId) {
            return $role->id == $roleId;
        });

        return $eventRole ? $eventRole->pivot->caldav_event_uid : null;
    }

    /**
     * Set CalDAV event UID for a specific role
     *
     * @return bool True if the pivot was updated, false if not found
     */
    public function setCalDAVEventUidForRole($roleId, $uid, $etag = null)
    {
        $pivotData = ['caldav_event_uid' => $uid];
        if ($etag !== null) {
            $pivotData['caldav_event_etag'] = $etag;
        }

        // Check if the pivot exists before updating
        $exists = $this->roles()->where('roles.id', $roleId)->exists();
        if (! $exists) {
            \Log::warning('Cannot set CalDAV UID: pivot record does not exist', [
                'event_id' => $this->id,
                'role_id' => $roleId,
                'uid' => $uid,
            ]);

            return false;
        }

        $this->roles()->updateExistingPivot($roleId, $pivotData);

        return true;
    }

    /**
     * Get CalDAV event UID for the role defined by subdomain
     */
    public function getCalDAVEventUidForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role ? $this->getCalDAVEventUidForRole($role->id) : null;
    }

    /**
     * Set CalDAV event UID for the role defined by subdomain
     */
    public function setCalDAVEventUidForSubdomain($subdomain, $uid, $etag = null)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        if ($role) {
            $this->setCalDAVEventUidForRole($role->id, $uid, $etag);
        }
    }

    /**
     * Sync this event to CalDAV for all connected roles
     */
    public function syncToCalDAV($action = 'create')
    {
        foreach ($this->roles as $role) {
            if ($role->syncsToCalDAV()) {
                SyncEventToCalDAV::dispatchSync($this, $role, $action);
            }
        }
    }

    /**
     * Check if this event is synced to CalDAV for a specific role
     */
    public function isSyncedToCalDAVForRole($roleId)
    {
        return ! is_null($this->getCalDAVEventUidForRole($roleId));
    }

    /**
     * Check if this event is synced to CalDAV for the role defined by subdomain
     */
    public function isSyncedToCalDAVForSubdomain($subdomain)
    {
        return ! is_null($this->getCalDAVEventUidForSubdomain($subdomain));
    }

    /**
     * Check if this event can be synced to CalDAV for the role defined by subdomain
     */
    public function canBeSyncedToCalDAVForSubdomain($subdomain)
    {
        $role = $this->roles->first(function ($role) use ($subdomain) {
            return $role->subdomain == $subdomain;
        });

        return $role && $role->hasCalDAVSettings() && $role->syncsToCalDAV();
    }

    /**
     * Get custom field values
     */
    public function getCustomFieldValues(): array
    {
        return $this->custom_field_values ?? [];
    }

    /**
     * Get a specific custom field value by key
     */
    public function getCustomFieldValue(string $key): ?string
    {
        $values = $this->getCustomFieldValues();

        return $values[$key] ?? null;
    }

    public function boostCampaigns()
    {
        return $this->hasMany(BoostCampaign::class);
    }

    public function activeBoostCampaign()
    {
        return $this->hasOne(BoostCampaign::class)->where('status', 'active')->latest();
    }

    /**
     * Set a specific custom field value
     */
    public function setCustomFieldValue(string $key, ?string $value): void
    {
        $values = $this->getCustomFieldValues();
        $values[$key] = $value;
        $this->custom_field_values = $values;
    }
}
