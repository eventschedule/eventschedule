<?php

namespace App\Models;

use App\Http\Controllers\MarketingController;
use App\Jobs\GenerateEventImageVariants;
use App\Jobs\SyncEventToCalDAV;
use App\Jobs\SyncEventToGoogleCalendar;
use App\Jobs\SyncEventToMicrosoftCalendar;
use App\Services\TicketVolumeDiscount;
use App\Traits\HasImageVariants;
use App\Utils\EventTextGenerator;
use App\Utils\ImageUtils;
use App\Utils\MarkdownUtils;
use App\Utils\MoneyUtils;
use App\Utils\SeoUtils;
use App\Utils\TextUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasImageVariants;

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
        'coupon_discount',
        'coupon_discount_type',
        'ticket_notes',
        'terms_url',
        'total_tickets_mode',
        'seating_plan_id',
        'payment_method',
        'payment_instructions',
        'expire_unpaid_tickets',
        'installments_enabled',
        'installment_count',
        'installment_final_days_before',
        'installment_min_order_amount',
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

    // contact_* are the booking-request submitter's own details and are owner-facing only. Defence
    // in depth, not the guard: $hidden only reaches toArray()/toJson(), so it does NOT cover
    // BackupService::exportEvent() or EventRepo::buildClonePayload(), which read attributes
    // directly. Keeping the three columns out of $fillable is what actually protects them.
    protected $hidden = ['event_password', 'contact_name', 'contact_email', 'contact_phone'];

    protected $casts = [
        'tickets_grandfathered_at' => 'datetime',
        'duration' => 'float',
        'is_private' => 'boolean',
        'is_draft' => 'boolean',
        // Written by the saving hook in boot(), read by SendEventAnnouncements. Cast but
        // deliberately NOT fillable: it records what the app observed, not what a caller asked
        // for, and BackupService walks getFillable() in both directions.
        'published_at' => 'datetime',
        'is_internal' => 'boolean',
        // Cancellation state is set only via EventController::cancel()/restore() (intentionally NOT in
        // $fillable, so the edit form's fill() can never toggle it via mass-assignment).
        'is_cancelled' => 'boolean',
        'cancelled_at' => 'datetime',
        'attendees_notified_at' => 'datetime',
        // When an appointment booking was last moved. Written only by AppointmentService::reschedule()
        // via forceFill, so deliberately NOT in $fillable - the event edit form must not touch it.
        'rescheduled_at' => 'datetime',
        'ical_sequence' => 'integer',
        // Set only by the platform-admin discovery toggle (intentionally NOT in $fillable).
        'is_hidden_from_discovery' => 'boolean',
        // True when user_id is the receiving schedule's owner standing in for an anonymous
        // submitter (EventRepo::saveEvent). Written once at creation, so deliberately NOT in
        // $fillable - the edit form must never flip it.
        'is_guest_submission' => 'boolean',
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
        // {"w480": "flyer_abc123_w480.webp"} or {"w480": null, "skipped": "too_large"}.
        // Written only by GenerateEventImageVariants / the backfill command through
        // recordImageVariants(), so deliberately NOT in $fillable.
        'image_variants' => 'array',
    ];

    /** Per-request memo of pass advance-booking seats reserved, keyed by occurrence date. */
    protected $passReservedSeatsCache = [];

    /** Per-request memo for seatingMapFor(). */
    protected $seatingMapCache = [];

    /** Per-request memo for seatedBands(). Null means "not looked up yet". */
    protected $seatedBandsCache = null;

    /** Per-request memo for the snapshot-backed answer, keyed by map. */
    protected $seatedBandsByMap = [];

    /** Per-request memo for seatingPlanModel(). False means "not looked up yet" - null is a
     * legitimate result (no plan, or a deleted one). */
    protected $seatingPlanCache = false;

    /**
     * Columns whose change makes a federated listing stale, either because the listing
     * displays them, because they change its URL, or because they change whether it
     * qualifies at all. Keep this in sync with FederationService::buildPayload().
     *
     * `event_url` belongs here even though buildPayload() no longer sends it: the
     * payload carries `is_online`, which is derived from this column, so an event
     * switching between in-person and online has to re-push. Removing it because the
     * name no longer appears in buildPayload() would strand exactly that change.
     */
    public const FEDERATION_FIELDS = [
        'name',
        'short_description',
        'slug',
        'starts_at',
        'duration',
        'timezone',
        'days_of_week',
        'recurring_frequency',
        'recurring_interval',
        'recurring_end_type',
        'recurring_end_value',
        'recurring_include_dates',
        'recurring_exclude_dates',
        'event_url',
        'flyer_image_url',
        'is_private',
        'is_draft',
        'is_cancelled',
        'event_password',
    ];

    /**
     * varchar columns that EventRepo::saveEvent()'s blanket fill($request->all()) funnels
     * straight from the POST body into the database, with no FormRequest rule in between for
     * most write paths. Under a strict connection an over-long value is a QueryException
     * (MySQL 1406), not a truncation, so the save fails and the user loses the whole edit.
     *
     * Each width must equal the real column - EventFieldLengthGuardTest asserts that against
     * the live schema so this cannot drift away from a future migration.
     */
    public const CLAMPED_COLUMNS = [
        'agenda_ai_prompt' => 500,
        'event_url' => 500,
        'terms_url' => 255,
        'coupon_code' => 255,
        'event_password' => 255,
    ];

    /**
     * What a missing coupon_discount_type means. The column is nullable with no DB default
     * and no backfill (see the 2026_08_21 migration), so every read site has to resolve it
     * independently - the validator's ceiling and this model's rendering have to agree, or a
     * value accepted under one reading renders under the other.
     */
    public const DEFAULT_COUPON_DISCOUNT_TYPE = 'fixed';

    /** How long an event stays in the sitemaps after it ends. See constrainSitemapWindow(). */
    public const SITEMAP_GRACE_DAYS = 30;

    /**
     * Slugs an event URL that carries the id cannot hold as they are.
     *
     * Each is the literal first segment of a route registered ahead of the event routes, whose
     * next segment takes an encoded event id, so /{slug}/{id} - or the dated, gallery or .ics URL
     * after it - reached that route instead: an event slugged "carpool" opened its carpool board,
     * one slugged "curate-event" a GET that curates it, and a venue's event with an act called
     * "book" (the other schedule's subdomain is the slug there) the booking form. guestUrlSlug()
     * writes such a slug as "{slug}-event". With the id in the URL the slug is decoration - every
     * guest route resolves the event by its id - so existing events are fixed with no migration,
     * and their old URLs never reached them anyway.
     *
     * book, carpool, curate-event and promo are guest routes on both kinds of install, map-image a
     * hosted one. The rest are signed-in app routes, which selfhost registers under
     * /{subdomain}/... ahead of its guest routes. ShadowedEventSlugTest reads both route tables and
     * fails when this list and they drift apart.
     */
    public const SHADOWED_SLUGS = [
        'book',
        'carpool',
        'clear-videos',
        'clone-event',
        'curate-event',
        'download-photos',
        'edit-event',
        'generate-flyer',
        'generate-style-image',
        'map-image',
        'promo',
        'verify',
    ];

    /**
     * Columns that decide whether - or how - an event shows on the homepage poster wall, which
     * MarketingController caches for `marketing.wall_cache_seconds`.
     *
     * The first four are the wall's own visibility filters; the last three are what its cards
     * print. Anything else (a description edit, a ticket price) leaves the wall identical, and
     * busting the cache for those would cost the site's most-hit page five correlated subqueries
     * on every publish. `image_variants` is deliberately absent: the job writes it around
     * Eloquent, and a cached model without it simply renders the (correct, larger) original
     * until the TTL turns over.
     */
    private const WALL_CACHE_FIELDS = [
        'is_hidden_from_discovery',
        'is_draft',
        'is_private',
        'is_cancelled',
        'flyer_image_url',
        'name',
        'starts_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // When this event first became publicly visible.
            //
            // The column has existed since 2024_07_13_184927_setup_database and nothing outside
            // blog posts ever wrote it. SendEventAnnouncements needs it: keying "new since the
            // last announcement" on created_at alone silently skipped every event written as a
            // draft before the watermark and published after it, which is the ordinary
            // write-it-up-now-publish-on-the-day workflow.
            //
            // Stamped on the transition out of draft, once, and never revised - unpublishing and
            // republishing does not make an event new again, and re-stamping would re-announce it.
            // is_draft covers both draft and internal (Event::setVisibilityState sets it for each);
            // is_private is deliberately NOT considered, because an unlisted event IS published,
            // just not listed, and the announcement query filters it out on its own terms.
            //
            // 2026_09_04_000000 backfills created_at onto every already-public row. That is not a
            // publication date we know, but it is exactly what newEventsFor()'s
            // COALESCE(published_at, created_at) already reads for those rows, so it changes no
            // behaviour - and it stops the column being a landmine: without it, a legacy public
            // event toggled to draft and back would satisfy the isDirty() guard below and stamp
            // today, announcing an event that has been on sale for months.
            // saveQuietly() fires no events, so a BackupService restore leaves it null and falls
            // into that same COALESCE - which is right, since the restore date is not a
            // publication date either.
            // The transition, not the state. Without the isDirty() half this is a state test on a
            // column nothing has ever written, so the FIRST save of any kind - an RSVP through
            // updateRsvpSold(), an inbound calendar sync, a typo fix - stamps a years-old event as
            // published today and SendEventAnnouncements mails it out as new. saving() also runs
            // before Eloquent's dirty check and the assignment itself dirties the model, so that
            // fires even on a save that would otherwise have been a no-op.
            if (! $model->is_draft && ! $model->published_at && (! $model->exists || $model->isDirty('is_draft'))) {
                $model->published_at = now();
            }

            // MUST come before the federation check below, which reads isDirty(FEDERATION_FIELDS)
            // and event_url and event_password are both in that list. Clamping can cancel a change
            // out - an over-long event_url that cuts back to the value already stored - and a
            // federation check run first would have nulled federated_at and re-published an event
            // that did not actually change. Deciding on the values that will really be written is
            // what keeps that check honest.
            //
            // agenda_ai_prompt is the one multi-line field of the five, so it is the only one CRLF
            // can reach: its textarea caps typing at maxlength="500", but a form serializes a line
            // break as CRLF, so a 500-character prompt with six of them arrives as 506 and 1406s a
            // save that never touched the field - it rides along in a hidden input, so the user was
            // editing tickets. Normalizing is what makes the value fit and stores exactly what was
            // typed; the clamp below is only the last resort.
            //
            // Deliberately NOT applied to the other four: event_password is stored in plaintext and
            // checked with hash_equals() against a raw, un-normalized request value, so rewriting
            // the stored side alone would make a password containing a CR unmatchable.
            if (! $model->exists || $model->isDirty('agenda_ai_prompt')) {
                $model->agenda_ai_prompt = TextUtils::normalizeNewlines($model->agenda_ai_prompt);
            }

            // Guarded on dirty, like the roles.website clamp this mirrors: the column can only
            // overflow on a fresh assignment, and re-clamping an untouched legacy value would
            // rewrite stored data during an unrelated save. clamp() is identity below the ceiling,
            // so an in-range password or coupon code is returned byte-for-byte. saveQuietly() fires
            // no events and so skips all of this - see BackupService::importEvent().
            foreach (self::CLAMPED_COLUMNS as $column => $width) {
                if ($model->exists && ! $model->isDirty($column)) {
                    continue;
                }
                $model->{$column} = TextUtils::clamp($model->{$column}, $width);
            }

            // registration_url is an href on the event page and a window.open() target in the
            // calendar, and nothing validates it on the web form, guest import, the AI import,
            // WhatsApp or the curator scraper - so a stored javascript: value ran on the page for
            // any visitor who clicked it. It is stored the way registrationHref() reads it: an
            // http(s) link, a scheme-less one given https://, or null. Guarded on dirty like the
            // clamp above, so an untouched legacy value is never rewritten by an unrelated save;
            // registrationHref() covers that row, and a saveQuietly() restore, where it renders.
            if (! $model->exists || $model->isDirty('registration_url')) {
                $model->registration_url = UrlUtils::safeHref($model->registration_url);
            }

            // Re-queue for federation when something a federated listing actually shows
            // changes. Hooked here rather than in EventRepo::saveEvent() because that is
            // not the only write path - inbound Google and Microsoft calendar sync call
            // $event->save() directly, and those edits must re-publish too.
            //
            // Scoped to FEDERATION_FIELDS on purpose: rsvp_sold is rewritten on every
            // RSVP, and translation_attempts / last_translated_at / attendees_notified_at
            // / ical_sequence all churn without changing the listing. Invalidating on any
            // save would turn an hourly sync into a continuous one.
            if ($model->exists && ! $model->isDirty('federated_at') && $model->isDirty(self::FEDERATION_FIELDS)) {
                $model->federated_at = null;
                // Also clear the "network refused this" marker: the usual reason is a
                // missing flyer or a name that tripped the junk filter, so an edit is
                // exactly the signal that it is worth trying again.
                $model->federated_skipped_at = null;
            }

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

                // The seat map is keyed on that same date, so it has to move too. This lives HERE
                // rather than only in EventRepo because inbound Google, Microsoft and CalDAV sync
                // all write starts_at straight onto the model - so the sales below followed the
                // event to the new night while the map stayed on the old one, materialize() built
                // a fresh all-available map, and the buyer's ticket still named a seat that was
                // back on sale. Two people, one seat.
                $oldSeatingDate = $model->seating_plan_id
                    ? $model->saleEventDateFor($model->getOriginal('starts_at'))
                    : null;

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

                    if ($oldSeatingDate && $oldSeatingDate !== $newDate) {
                        app(\App\Services\SeatingMapService::class)
                            ->rekeyOccurrence($model, $oldSeatingDate, $newDate);
                    }
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

            // A new flyer invalidates every derivative of the old one. Cleared here rather
            // than in the job so the wall falls straight back to the (correct) original in the
            // up-to-a-minute window before the queue rebuilds the thumbnail, instead of showing
            // the previous flyer's WebP under the new event image.
            if ($model->exists && $model->isDirty('flyer_image_url')) {
                // And delete the files, not just the record of them: a derivative's NAME is
                // derived from the original's, so once this row stops holding that filename
                // nothing can ever address them again. getRawOriginal(), because
                // getOriginal() runs the flyer accessor and would hand back a URL.
                // Safe for the clone path, which copies the bytes to a NEW filename and so has
                // derivatives of its own.
                $previousFlyer = $model->getRawOriginal('flyer_image_url');
                if (is_string($previousFlyer) && $previousFlyer !== '') {
                    ImageUtils::deleteStoredVariants($previousFlyer);
                }

                $model->image_variants = null;
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

        // Every flyer write path in the app ends in an Eloquent save(), so hooking the model
        // covers the web upload, the API, guest submit, guest import, the AI flyer, Eventbrite,
        // WhatsApp, the curator import and clone in one place. The exception is
        // BackupService::importEventImages(), which uses saveQuietly() by design - restored rows
        // are picked up by `php artisan images:backfill-variants`.
        static::created(function ($model) {
            self::queueImageVariants($model);
        });

        static::updated(function ($model) {
            // performInsert() never calls syncChanges(), so wasChanged() is meaningless on a
            // fresh insert - which is why the create case above is a separate hook rather than
            // one shared `saved` listener.
            if ($model->wasChanged('flyer_image_url')) {
                self::queueImageVariants($model);
            }
        });

        static::saved(function ($model) {
            // wasRecentlyCreated for the same reason as above: an insert never syncs changes, so
            // wasChanged() is blind to it - and a brand new event is exactly the thing most
            // likely to belong on the wall.
            if ($model->wasRecentlyCreated || $model->wasChanged(self::WALL_CACHE_FIELDS)) {
                MarketingController::forgetWallCache();
            }
        });

        static::deleted(function ($model) {
            // Nothing can address this row's derivatives once it is gone, so they would sit on
            // object storage forever.
            $raw = $model->getAttributes()['flyer_image_url'] ?? null;
            if (is_string($raw) && $raw !== '') {
                ImageUtils::deleteStoredVariants($raw);
            }

            MarketingController::forgetWallCache();
        });

        static::deleting(function ($event) {
            // Cancel active boost campaigns on Meta and issue refunds
            $activeCampaigns = $event->boostCampaigns()
                ->unsettled()
                ->get();

            foreach ($activeCampaigns as $campaign) {
                try {
                    if ($campaign->meta_campaign_id && \App\Services\MetaAdsService::isBoostConfigured()) {
                        $metaService = app()->make(\App\Services\MetaAdsService::class);
                        $metaService->deleteCampaign($campaign);
                    }

                    $campaign->update(['status' => 'cancelled', 'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null]);

                    $billingService = new \App\Services\BoostBillingService;

                    // Gate the STRIPE call, not the refund. settlePayment()'s credit branch debits
                    // boost_credit regardless of mode, so gating the whole block meant deleting a
                    // schedule on selfhost destroyed the advertiser's wallet balance outright.
                    // refundOnCancellation() reaches Stripe only when there is an intent, which a
                    // selfhost campaign never has. BoostController::cancel() already works this way.
                    if ($campaign->billing_status === 'charged') {
                        $billingService->refundOnCancellation($campaign);
                    } elseif (config('app.hosted') && ! config('app.is_testing')
                        && $campaign->billing_status === 'pending' && $campaign->stripe_payment_intent_id) {
                        $billingService->cancelPaymentIntent($campaign);
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
     * Queue the resized WebP derivative of this event's flyer, if it has one worth resizing.
     *
     * afterCommit() because inbound calendar sync and the seat-map rekey both save events inside
     * a transaction, and on the `sync` queue (the selfhost default) the job would otherwise run
     * S3 reads and writes inside that open transaction - the exact shape that has produced a live
     * deadlock in this codebase before.
     */
    protected static function queueImageVariants(self $model): void
    {
        $raw = $model->getAttributes()['flyer_image_url'] ?? null;

        // demo_ flyers ship in the repo as small WebPs already; a legacy http value is not ours
        // to resize.
        if (! $raw || str_starts_with($raw, 'demo_') || str_starts_with($raw, 'http')) {
            return;
        }

        GenerateEventImageVariants::dispatch($model->id, $raw)->afterCommit();
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

    public function seatingPlan()
    {
        return $this->belongsTo(SeatingPlan::class);
    }

    public function seatingMaps()
    {
        return $this->hasMany(EventSeatingMap::class);
    }

    /**
     * Whether this event sells from a seat map at all. A NULL seating_plan_id IS the
     * flag - there is no separate boolean that could disagree with it.
     */
    public function hasAllocatedSeating(): bool
    {
        return ! empty($this->seating_plan_id);
    }

    /**
     * This occurrence's seat map, memoized for the request.
     *
     * Ticket::toData() asks per ticket, so an event with six bands would otherwise re-query the
     * map six times per page. Mirrors the passReservedSeatsCache pattern below.
     */
    public function seatingMapFor(?string $date)
    {
        if (! $this->hasAllocatedSeating()) {
            return null;
        }

        $key = (string) $date;
        if (! array_key_exists($key, $this->seatingMapCache)) {
            $this->seatingMapCache[$key] = app(\App\Services\SeatingMapService::class)->mapFor($this, $date);
        }

        return $this->seatingMapCache[$key];
    }

    /**
     * The plan's bands that actually hold seats.
     *
     * A STANDING section has a band and a ticket like any other - so the map can label and price
     * it - but no seat rows, so its ticket must keep the ordinary quantity path. Without this a
     * standing ticket would be treated as seat-allocated and report zero seats available, i.e.
     * permanently sold out. One query per event, memoized, because toData() asks per ticket.
     *
     * @return string[]
     */
    public function seatedBands(?string $date = null): array
    {
        // Answer from the SNAPSHOT when this occurrence has one. The snapshot is deliberately
        // frozen against later template edits, so reading the template here made a ticket stop
        // being "allocated" the moment somebody renamed a band on the plan - which silently
        // skipped the checkout balance check and sold seats that were never claimed.
        if ($date !== null && ($map = $this->seatingMapFor($date))) {
            $key = 'map:'.$map->id;

            if (! array_key_exists($key, $this->seatedBandsByMap)) {
                $this->seatedBandsByMap[$key] = \App\Models\SeatingSection::where('event_seating_map_id', $map->id)
                    ->where('is_deleted', false)
                    ->where('kind', '!=', 'standing')
                    ->whereNotNull('band')
                    ->whereHas('seats')
                    ->pluck('band')->unique()->values()->all();
            }

            return $this->seatedBandsByMap[$key];
        }

        if ($this->seatedBandsCache === null) {
            $plan = $this->seating_plan_id
                ? \App\Models\SeatingPlan::with('sections')->find($this->seating_plan_id)
                : null;

            // whereHas('seats') matters: a section drawn but not yet given rows would otherwise
            // make its ticket "allocated" while the map holds nothing, and the picker would offer
            // the per-order cap for a band with no seats at all.
            $this->seatedBandsCache = $plan
                ? \App\Models\SeatingSection::where('seating_plan_id', $plan->id)
                    ->where('is_deleted', false)
                    ->where('kind', '!=', 'standing')
                    ->whereNotNull('band')
                    ->whereHas('seats')
                    ->pluck('band')->unique()->values()->all()
                : [];
        }

        return $this->seatedBandsCache;
    }

    /** The attached plan, loaded at most once per request. */
    public function seatingPlanModel()
    {
        if ($this->seatingPlanCache === false) {
            $this->seatingPlanCache = $this->seating_plan_id
                ? \App\Models\SeatingPlan::where('is_deleted', false)->find($this->seating_plan_id)
                : null;
        }

        return $this->seatingPlanCache;
    }

    /** Seats still sellable for one allocated ticket at this occurrence. */
    public function allocatedSeatsRemaining(?string $date, Ticket $ticket): ?int
    {
        $map = $this->seatingMapFor($date);

        // Not snapshotted yet, so nobody can have bought a seat and the template's own count is
        // exact. Read it from the PLAN rather than from tickets.quantity: EventRepo skips the
        // derivation when it computes to zero, so a band removed from the plan keeps whatever
        // quantity was posted and this would report seats that do not exist.
        if (! $map) {
            $plan = $this->seatingPlanModel();

            return $plan ? $plan->seatCountForBand($ticket->seating_band) : 0;
        }

        return app(\App\Services\SeatingMapService::class)->availableSeatCount($map, $ticket->id);
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
            ->withPivot('id', 'name_translated', 'short_description_translated', 'description_translated', 'description_html_translated', 'is_accepted', 'is_auto_sourced', 'group_id', 'google_event_id', 'caldav_event_uid', 'caldav_event_etag')
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

        // No schedule on the event has its own email settings. Return the venue or the first schedule
        // anyway: transactional mail (ticket confirmations) then goes out through the platform
        // mailer, and the strict gates (sale notifications, gift cards) still refuse on their own.
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

    public function appointmentType()
    {
        return $this->belongsTo(AppointmentType::class);
    }

    /** Whether this event is a Calendly-style appointment booking (vs a regular event). */
    public function isAppointment(): bool
    {
        return ! is_null($this->appointment_type_id);
    }

    /**
     * Whether only the schedule's own people - its members, and admins - may reach this event on a
     * guest surface, which answers anybody else as it answers an event that is not there.
     *
     * A draft, Draft and Internal alike (both set is_draft). And an appointment booking: it is
     * named after its guest, who manages it through the booking's own secret link
     * (AppointmentController::manage()), so the public event page is no part of anybody's flow,
     * and an event id is no secret. Unlisted was never enough: anybody holding the link may open
     * an unlisted event.
     */
    public function isMembersOnly(): bool
    {
        return (bool) $this->is_draft || $this->isAppointment();
    }

    /**
     * Whether the creator schedule has neither accepted nor declined this event yet - the state an
     * appointment booking sits in while `requires_approval` is pending.
     *
     * Reads the already-loaded `roles` relation when it is there, so callers rendering a list do not
     * fire a query per row. Null-safe: detached or drifted rows must not crash the caller.
     */
    public function isAwaitingCreatorApproval(): bool
    {
        $pivot = $this->relationLoaded('roles')
            ? $this->roles->firstWhere('id', $this->creator_role_id)?->pivot
            : $this->roles()->where('roles.id', $this->creator_role_id)->first()?->pivot;

        return $pivot && is_null($pivot->is_accepted);
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
            ->withPivot('id', 'name_translated', 'short_description_translated', 'description_translated', 'description_html_translated', 'is_accepted', 'is_auto_sourced', 'group_id', 'google_event_id', 'caldav_event_uid', 'caldav_event_etag')
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

    /**
     * Only events without a password: the rows isPasswordProtected() calls unprotected, for the
     * surfaces that show an event to people who have not unlocked it.
     *
     * Most older queries use whereNull('event_password') instead, which also drops a row whose
     * password is ''. An `= ''` test would not do either: the column's collation pads with spaces,
     * so it also matches a password of only spaces, which isPasswordProtected() counts as a
     * password. CHAR_LENGTH() counts the spaces. The one value the two still disagree on is '0',
     * which empty() calls no password and this keeps out, on the side of hiding.
     *
     * The column is table-qualified because most of these queries join.
     */
    public function scopeNotPasswordProtected(Builder $query): Builder
    {
        return static::constrainNotPasswordProtected($query);
    }

    /**
     * scopeNotPasswordProtected() for a query that is not an Event builder: a schedule query that
     * joins events, or a DB::table() query that aliases the table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function constrainNotPasswordProtected($query, string $table = 'events')
    {
        $column = $table.'.event_password';

        return $query->where(fn ($q) => $q->whereNull($column)
            ->orWhereRaw('CHAR_LENGTH('.$q->getGrammar()->wrap($column).') = 0'));
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

    /**
     * Every attached schedule, not just curators, and with no is_accepted filter - callers that
     * care about visibility have to read the pivot. It is loaded here so they can: the event
     * form uses it to avoid ticking a schedule that declined the event.
     */
    public function curators()
    {
        return $this->belongsToMany(Role::class, 'event_role', 'event_id', 'role_id')
            ->withPivot('is_accepted', 'is_auto_sourced');
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

    /**
     * Events whose private data (sales, revenue, check-ins) this user may read.
     *
     * A deliberate SUBSET of User::canViewEventData(), never a mirror of it. That is the whole
     * invariant: a row that is listed is always actionable, and a row this misses is only ever
     * one the check would have allowed anyway. Equality is what produced both of the bugs below.
     *
     * Two tightenings on top of the check's own rule, both load-bearing:
     *
     * 1. A pivot row is REQUIRED. canViewEventData() walks $event->roles, so granting on the
     *    events.creator_role_id column alone would list rows whose every action button 403s.
     *    roles()->sync() in EventRepo detaches pivot rows while creator_role_id is write-once,
     *    so that state is real - CheckData::checkEventCreatorRoles() exists to find it.
     *
     * 2. Someone ELSE's event needs an ACCEPTED pivot. A decline does not detach: both
     *    EventController::decline() and ::uncurate() leave the row at is_accepted = false, so
     *    without this a venue that turned an event down keeps its buyer names, emails and revenue
     *    on the Sales page. Your OWN schedule's event (er.role_id = creator_role_id) is exempt,
     *    because an appointment booking sits at is_accepted null while it awaits approval and
     *    false once cancelled, and paid bookings are meant to appear on the Sales page.
     *
     *    SCOPED CLAIM: this is a LIST-side rule only. canViewEventData() does not read
     *    is_accepted, so a declined schedule that still holds an event or sale id can read the
     *    same data through BoxOfficeController::resolve(), CheckInController::stats() and
     *    Api/ApiSaleController::show(). That gap predates this scope and closing it means teaching
     *    canEditEvent() the rule too (canScanEvent delegates to it), which the Requests tab's Edit
     *    button relies on staying open while a pivot is pending. Its own change.
     *
     * Curators are the third rule, inherited from the check: a curator that only LISTS an event
     * does not own the creator's private data, so it qualifies only for what it created.
     */
    /**
     * Has this event ever taken money, in any state that still counts as history?
     *
     * `paid` and `amount_mismatch` are live money; `refunded` is money that was taken and given
     * back, which reporting still denominates. `unpaid`, `cancelled` and `expired` never moved
     * anything, so they leave the event free to be re-denominated.
     *
     * Used to freeze events.ticket_currency_code once it means something. That column is the ONLY
     * record of what a sale was charged in - `sales` carries no currency of its own - so an edit
     * after the fact silently re-denominates every past row and misscales any later refund.
     */
    public function hasSettledMoney(): bool
    {
        return $this->sales()
            ->whereIn('status', ['paid', 'amount_mismatch', 'refunded'])
            ->where('is_deleted', false)
            ->exists();
    }

    public function scopeManagedBy(Builder $query, User $user): Builder
    {
        $roles = $user->manageableRoles();

        return $query->managedThrough(
            $user,
            $roles->pluck('id')->all(),
            $roles->where('type', '!=', 'curator')->pluck('id')->all(),
        );
    }

    /**
     * Events this user may scan tickets for: a subset of User::canScanEvent().
     *
     * Wider than managedBy() in exactly one way, and narrower in one. Viewers are included,
     * because a viewer is who you hand the door to. And there is NO curator exception, because
     * canScanEvent() builds on canEditEvent(), which has none - a curator's staff can already
     * scan for an event the curator lists. The accepted-pivot rule from managedBy() still applies,
     * so a declined schedule's staff are not offered the event in the picker - but the same scoped
     * claim holds as there: canScanEvent() does not read is_accepted either, so the POST at
     * TicketController::scanned() would still admit their ticket holders.
     */
    public function scopeScannableBy(Builder $query, User $user): Builder
    {
        $roleIds = $user->scannableRoles()->pluck('id')->all();

        return $query->managedThrough($user, $roleIds, $roleIds);
    }

    /**
     * Shared body of managedBy()/scannableBy(). $curatorExemptRoleIds are the roles allowed to
     * qualify through an accepted pivot on somebody else's event - i.e. every non-curator role
     * for managedBy(), and every role for scannableBy().
     */
    public function scopeManagedThrough(
        Builder $query,
        User $user,
        array $roleIds,
        array $curatorExemptRoleIds
    ): Builder {
        return $query->where(function ($q) use ($user, $roleIds, $curatorExemptRoleIds) {
            $q->where('events.user_id', $user->id);

            if (! $roleIds) {
                return;
            }

            $q->orWhereExists(function ($sub) use ($roleIds, $curatorExemptRoleIds) {
                $sub->selectRaw('1')
                    ->from('event_role')
                    ->whereColumn('event_role.event_id', 'events.id')
                    ->whereIn('event_role.role_id', $roleIds)
                    ->where(function ($w) use ($curatorExemptRoleIds) {
                        // My own schedule's event: mine whatever the pivot says.
                        $w->whereColumn('event_role.role_id', 'events.creator_role_id');

                        // Somebody else's: only once this schedule accepted it.
                        if ($curatorExemptRoleIds) {
                            $w->orWhere(fn ($a) => $a->where('event_role.is_accepted', true)
                                ->whereIn('event_role.role_id', $curatorExemptRoleIds));
                        }
                    });
            });
        });
    }

    public function scopeInMonth($query, $gridStartUtc, $gridEndUtc = null)
    {
        return $query->where(function ($q) use ($gridStartUtc, $gridEndUtc) {
            $q->where(function ($q2) use ($gridStartUtc, $gridEndUtc) {
                $q2->where('starts_at', '>=', $gridStartUtc);
                if ($gridEndUtc) {
                    // Upper-bound one-off events to the visible grid window so a schedule with
                    // thousands of future events doesn't hydrate its entire event table at once.
                    // Recurring and still-ongoing multi-day events keep their own clauses below.
                    $q2->where('starts_at', '<=', $gridEndUtc);
                }
            })
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

    /** See constrainSitemapWindow(). */
    public function scopeInSitemapWindow($query, ?Carbon $nowUtc = null)
    {
        return static::constrainSitemapWindow($query, $nowUtc ?? Carbon::now('UTC'), $this->getTable());
    }

    /**
     * Constrain an events query to the rows the sitemaps still list: a one-off event until
     * SITEMAP_GRACE_DAYS after it ends, a recurring series while it is still running.
     *
     * Sitemap hygiene only. The pages stay indexable - an old event still answers "index, follow" -
     * the sitemap just stops asking Google to recrawl them: 86% of the event URLs it submitted were
     * past, 966 of them by more than a month.
     *
     * $nowUtc bounds a grace period and nothing else. It never decides which DAY an event falls on
     * (that is the schedule's zone), so a UTC day either way is immaterial.
     *
     * No JSON and no window functions: selfhost promises MySQL 5.7. That leaves two things SQL can
     * only bound, and both are bounded so that a running series is never dropped:
     *  - recurring_include_dates bypass the pattern and the end in matchesDate(), and their dates
     *    are unreadable without JSON_*, so a series that has any is kept.
     *  - an 'after_events' series ends on its Nth occurrence, which SQL cannot enumerate. The bound
     *    is starts_at plus N + E periods, where a period is the longest gap between two occurrences
     *    of that frequency and E counts the excluded dates, each of which pushes the Nth occurrence
     *    one period later (countOccurrences() subtracts them). E is read off the JSON's length: a
     *    stored list of n Y-m-d strings is 13n + 1 characters, and any looser encoding only
     *    overestimates.
     *
     * '0000000' never occurs on a weekly or every_n_weeks series, whose matchesFrequency() reads the
     * days, so that series is over unless it has include dates. The other frequencies ignore
     * days_of_week (saveEvent() writes '1111111' for them).
     *
     * SELECT ONLY. afterEventsEndSql() casts a free-text column, which only warns on a malformed
     * value inside a SELECT; strict mode turns that same warning into an error inside an UPDATE or
     * DELETE, so never use this window to pick the rows a write touches.
     *
     * $table names the events table in $query, for a caller that reaches it through a join.
     */
    public static function constrainSitemapWindow($query, Carbon $nowUtc, string $table = 'events')
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            throw new \InvalidArgumentException('Not a table name: '.$table);
        }

        $cut = $nowUtc->copy()->setTimezone('UTC')->subDays(self::SITEMAP_GRACE_DAYS);
        $cutAt = $cut->format('Y-m-d H:i:s');
        $cutDate = $cut->format('Y-m-d');
        $c = fn (string $column) => $table.'.'.$column;

        return $query->where(fn ($window) => $window
            // One-off: until the grace period after it ends. Only an event of a day or more ends
            // meaningfully later than it starts - the scopeUpcomingOrOngoing() shape.
            ->where(fn ($oneOff) => $oneOff->whereNull($c('days_of_week'))
                ->where(fn ($ends) => $ends->where($c('starts_at'), '>=', $cutAt)
                    ->orWhere(fn ($long) => $long->where($c('duration'), '>=', 24)
                        ->whereRaw("DATE_ADD({$table}.starts_at, INTERVAL {$table}.duration HOUR) >= ?", [$cutAt]))))
            // A series: while it has an occurrence to come, or had one within the grace period.
            ->orWhere(fn ($series) => $series->whereNotNull($c('days_of_week'))
                ->where(fn ($running) => $running
                    ->where(fn ($include) => $include->whereNotNull($c('recurring_include_dates'))
                        ->whereNotIn($c('recurring_include_dates'), ['', '[]']))
                    ->orWhere(fn ($pattern) => $pattern
                        ->where(fn ($days) => $days->where($c('days_of_week'), '<>', '0000000')
                            ->orWhereIn($c('recurring_frequency'), ['daily', 'monthly_date', 'monthly_weekday', 'yearly']))
                        ->where(fn ($end) => $end->whereNull($c('recurring_end_type'))
                            ->orWhereNotIn($c('recurring_end_type'), ['on_date', 'after_events'])
                            // matchesDate() tests the value for truthiness: '' and '0' mean no end.
                            ->orWhereNull($c('recurring_end_value'))
                            ->orWhereIn($c('recurring_end_value'), ['', '0'])
                            // A Y-m-d, so it compares as a string. Its last occurrence is on or
                            // before it.
                            ->orWhere(fn ($onDate) => $onDate->where($c('recurring_end_type'), 'on_date')
                                ->where($c('recurring_end_value'), '>=', $cutDate))
                            ->orWhere(fn ($after) => $after->where($c('recurring_end_type'), 'after_events')
                                ->whereRaw(self::afterEventsEndSql($table).' >= ?', [$cutAt])))))));
    }

    /**
     * SQL for the latest date an 'after_events' series can still occur on. See
     * constrainSitemapWindow(). Capped at a century: DATE_ADD past 9999-12-31 is NULL, which would
     * read as "ended" for a series that in practice never does.
     *
     * SELECT ONLY: the cast below warns on malformed text, and strict mode makes that warning an
     * error inside an UPDATE or DELETE.
     *
     * The count is clamped to 0..100000 through DECIMAL(65,0). CAST AS UNSIGNED overflowed the
     * product (ERROR 1690) for -1, and for 9223372036854775807, which is what the event form
     * stores for any huge number because saveEvent() keeps (string)(int). That error came out of
     * the series query on every visit to the schedule page. SIGNED is no fix: it wraps a count
     * past the BIGINT range, such as twenty nines, to -1. DECIMAL reads text and '' as 0 and '1e5'
     * as 100000, as PHP's (int) does, and rounds '3.7' up, which can only keep a series longer.
     *
     * A period is the longest gap one counted occurrence can stand for. countOccurrences() counts
     * addMonth() and addYear() steps, so 31 and 366 are exact bounds even on the 31st or on Feb 29.
     * monthly_weekday counts only the months that have its nth weekday: the 1st to 4th recur
     * within 35 days, but a 5th (a local 29th to 31st) can be 119 days after the one before.
     * starts_at is UTC and the local date can run from 12 hours behind it to 14 hours ahead, so
     * every date the start could fall on locally is checked: 9pm on the 31st in New York is
     * already the 1st in UTC.
     */
    private static function afterEventsEndSql(string $table): string
    {
        $occurrences = "LEAST(GREATEST(CAST({$table}.recurring_end_value AS DECIMAL(65,0)), 0), 100000)"
            ." + COALESCE(FLOOR(CHAR_LENGTH({$table}.recurring_exclude_dates) / 13), 0)";

        $fifthWeekday = "GREATEST(DAY(DATE_SUB({$table}.starts_at, INTERVAL 12 HOUR)), DAY({$table}.starts_at),"
            ." DAY(DATE_ADD({$table}.starts_at, INTERVAL 14 HOUR))) >= 29";

        $period = "CASE {$table}.recurring_frequency"
            ." WHEN 'daily' THEN 1"
            ." WHEN 'monthly_date' THEN 31"
            ." WHEN 'monthly_weekday' THEN CASE WHEN {$fifthWeekday} THEN 124 ELSE 35 END"
            ." WHEN 'yearly' THEN 366"
            ." WHEN 'every_n_weeks' THEN 7 * GREATEST(COALESCE({$table}.recurring_interval, 2), 1)"
            .' ELSE 7 END';

        return "DATE_ADD({$table}.starts_at, INTERVAL LEAST(({$occurrences}) * ({$period}), 36600) DAY)";
    }

    /**
     * The timezone this event's calendar dates are expressed in: the schedule's, not the
     * viewer's. An occurrence falls on a given day because of where the event happens, not
     * because of who is looking at it.
     */
    public function scheduleTimezone(): string
    {
        // ?: not ??: roles.timezone is a nullable string, and an empty one is a DateTimeZone
        // error rather than a fallback.
        return $this->creatorRole?->timezone ?: config('app.timezone');
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
     * A UTC instant rendered the way localStartsAt() renders occurrence dates: the schedule's
     * timezone, the viewer's 12/24-hour preference when set, language from the schedule. Used for
     * instants that are not occurrence starts (e.g. a booking's cancellation deadline).
     *
     * The timezone deliberately does NOT follow the viewer, for the reason this method always
     * gave for following it - it must never disagree with the date_label printed beside it, and
     * that label is the schedule's clock.
     */
    public function localizedInstantLabel(Carbon $utcInstant): string
    {
        $tz = $this->scheduleTimezone();
        $role = $this->creatorRole;
        $enable24 = (bool) ($role?->use_24_hour_time);

        if (($user = auth()->user()) && $user->use_24_hour_time !== null) {
            $enable24 = $user->use_24_hour_time;
        }

        $local = $utcInstant->copy()->setTimezone($tz);

        if ($role && $role->language_code) {
            return $local->locale($role->language_code)
                ->translatedFormat($enable24 ? 'j F Y • H:i' : 'j F Y • g:i A');
        }

        return $local->format($enable24 ? 'M j, Y H:i' : 'M j, Y g:i A');
    }

    /**
     * Is $date a real calendar date in the Y-m-d shape occurrence dates use?
     *
     * The shape check alone is not enough: '2026-13-45' matches it, and Carbon::parse() then
     * throws on it while createFromFormat() silently rolls it over to 2027-02-14.
     *
     * Deliberately untyped: guest views pass request input straight in, and ?date[]=x makes that
     * an array, which is a TypeError for both a string type hint and preg_match().
     */
    public static function isOccurrenceDate($date): bool
    {
        if (! is_string($date) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
            return false;
        }

        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    /**
     * The occurrence dates an operator can act on in the admin portal (Y-m-d, ascending).
     *
     * The box office, the printed report and the one-date designer are all keyed to a single
     * occurrence, and none of them had any way to reach a second one: with no ?date= they fall back
     * to saleEventDateFromStartsAt(), the series anchor, so a thirty-night run only ever exposed
     * its first night. This is what their date pickers list.
     *
     * The window reaches BACKWARDS as well as forwards on purpose. Recording a walk-up after the
     * doors opened is ordinary desk work - BoxOfficeSeatingService::bookSeats() deliberately allows
     * a past date, refusing only a cancelled event - and a front-of-house sheet is often reprinted
     * for the night that has just finished.
     *
     * Deliberately NOT canSellTickets()-filtered, for the same reason.
     *
     * @return string[]
     */
    public function adminOccurrenceDates(int $daysBack = 30, int $daysForward = 365, int $limit = 60): array
    {
        if (! $this->starts_at) {
            return [];
        }

        if (! $this->days_of_week) {
            $date = $this->saleEventDateFromStartsAt();

            return $date ? [$date] : [];
        }

        $tz = $this->scheduleTimezone();
        $today = Carbon::parse($this->scheduleToday())->startOfDay();
        $cursor = $today->copy()->subDays(max(0, $daysBack));

        // Never walk from before the run begins: matchesDate() rejects those anyway, and on a
        // series starting next year it would burn the whole day budget before the first hit.
        $start = $this->localStartCarbon($tz)->startOfDay();
        if ($cursor->lt($start)) {
            $cursor = $start->copy();
        }

        $end = $today->copy()->addDays(max(1, $daysForward));
        $dates = [];

        while ($cursor->lte($end) && count($dates) < $limit) {
            if ($this->matchesDate($cursor->copy(), $tz)) {
                $dates[] = $cursor->format('Y-m-d');
            }
            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * The occurrence an admin screen should open on when no ?date= was given.
     *
     * Tonight or the next night of the run, falling back to the most recent past one so a finished
     * run still opens on something real, and finally to the anchor. Scoped to the two AP screens by
     * their controllers rather than changed inside SeatingMapService::resolveDate(), whose null-date
     * fallback is shared with the guest picker and with the memoized availability read behind
     * "sold out" on every card.
     */
    public function defaultAdminOccurrenceDate(): ?string
    {
        $dates = $this->adminOccurrenceDates();

        if (! $dates) {
            return $this->saleEventDateFromStartsAt();
        }

        $today = $this->scheduleToday();

        foreach ($dates as $date) {
            if ($date >= $today) {
                return $date;
            }
        }

        return end($dates);
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

        // Match getStartDateTime(): a malformed date is ignored, not fatal. Guest URLs are
        // visitor-supplied and every add-to-calendar link on the event page flows through here.
        // Re-deriving the date from $startsAt is an identity, so this means "as if no date".
        if (! self::isOccurrenceDate($date)) {
            $date = $startsAt->copy()->setTimezone($tz)->format('Y-m-d');
        }

        return Carbon::createFromFormat('Y-m-d H:i:s', $date.' '.$timeOfDay, $tz)->setTimezone('UTC');
    }

    /**
     * The event's start as a naive Carbon holding local wall-clock time.
     *
     * Equivalent to Carbon::parse($this->localStartsAt()) when no timezone is passed, which
     * resolves to the schedule's own zone - so which day an occurrence falls on is a property of
     * the event. $timezone is for the rare caller that must answer the question somewhere else.
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

    /**
     * The next calendar day this event actually occurs on, at or after $from, or null if none.
     *
     * Scans forward with matchesDate() rather than reading days_of_week directly, and that is the
     * whole point. EventRepo::saveEvent() writes days_of_week = '1111111' for daily, monthly_date,
     * monthly_weekday and yearly "for query compatibility" (:1066), so a weekday-only scan answers
     * TODAY for a monthly event - a day it does not occur on. matchesDate() consults the frequency,
     * the exclude and include lists and the end conditions, so it answers the real question.
     *
     * Bounded, and the bound is load-bearing: a yearly event's next occurrence is up to a year out,
     * and an event whose recurrence has already ended has none at all. The guest backfill this
     * replaces was an unbounded `while (true)` over days_of_week, which spins forever on
     * '0000000' - reachable by unchecking every day on a weekly event.
     */
    public function nextOccurrenceFrom($from = null, int $withinDays = 366): ?string
    {
        if (! $this->starts_at) {
            return null;
        }

        $timezone = $this->scheduleTimezone();
        $cursor = ($from ? Carbon::parse($from) : Carbon::now($timezone))->startOfDay();

        for ($i = 0; $i <= $withinDays; $i++) {
            if ($this->matchesDate($cursor, $timezone)) {
                return $cursor->format('Y-m-d');
            }

            $cursor->addDay();
        }

        return null;
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

    /**
     * Why a guest may not buy or see this event on $role's schedule, or null if they may.
     *
     * Extracted so the seat-map endpoints cannot drift from checkout: they used to guard only
     * is_draft, which left an unlisted password-protected event's entire seating plan readable
     * anonymously. Returns a reason rather than a bool so checkout can keep telling 403
     * (not on this schedule) apart from 404 (hidden).
     *
     * @return null|'not_on_schedule'|'hidden'
     */
    public function guestVisibilityFailure(Role $role, bool $isMemberOrAdmin): ?string
    {
        if (! $this->roles()->wherePivot('role_id', $role->id)->exists()) {
            return 'not_on_schedule';
        }

        if ($this->isMembersOnly() && ! $isMemberOrAdmin) {
            return 'hidden';
        }

        if ($this->is_private && ! $isMemberOrAdmin
            && ! ($this->isPasswordProtected() && session()->has('event_password_'.$this->id))) {
            return 'hidden';
        }

        return null;
    }

    /**
     * Whether this event offers the "Notify me" card ("Tell me when tickets go on sale") at all.
     *
     * The one rule for the card, its Add to Calendar and buy-button links
     * (event/show-guest.blade.php) and EventInterestController::store(). Those used to keep
     * separate copies that had already drifted: the page refused every private and every
     * password-protected event while a direct POST did not, so rows were created for events whose
     * page never offered the form - and some of them were then mailed.
     *
     * The switch that counts is the CREATOR's (roles.show_event_interest, off by default), not the
     * switch of whichever schedule's page is showing the event. An interest row belongs to the
     * event, and SendEventInterestMail and EventChangeNotifier write to the list as
     * $event->creatorRole - so a curator or talent that turned the card on must not be able to
     * start a list the creator will then email. The reverse matters as much: an event's canonical,
     * share and Boost URLs resolve to a claimed talent's subdomain (getGuestUrlData()), so a
     * page-schedule rule would hide the card from a venue that switched it on.
     *
     * Visitor-side conditions (embeds, the graphic render, the page schedule being a demo) and the
     * date check stay with the callers, which know the request and the occurrence.
     */
    public function offersInterestCapture(): bool
    {
        $creator = $this->creatorRole;

        return ! $this->is_draft
            && ! $this->is_private
            && ! $this->is_cancelled
            && ! $this->is_hidden_from_discovery
            && ! $this->isPasswordProtected()
            && $creator
            && $creator->show_event_interest
            && ! is_demo_role($creator);
    }

    public function canSellTickets($date = null)
    {
        // Two questions, deliberately separated. passesSellingWindow() is the date, window and
        // cancellation half and carries no plan logic; canOfferTickets() is the plan half and
        // carries no date logic. blockedByPlanOnly() is the complement of this method built from
        // the same two pieces, so the guest page cannot disagree with the write path about why an
        // event is not selling.
        return $this->passesSellingWindow($date)
            && $this->tickets_enabled
            && $this->canOfferTickets();
    }

    /**
     * Whether the PLAN is the only thing stopping this event from selling.
     *
     * Same date, window and cancellation checks as canSellTickets(), with canOfferTickets()
     * inverted. Exists so the guest page can offer an external registration_url to an event that
     * would be selling on Pro, WITHOUT also offering it for an event that has simply finished -
     * canOfferTickets() deliberately carries no date logic, so asking it alone cannot tell those
     * two apart.
     */
    public function blockedByPlanOnly($date = null): bool
    {
        return $this->tickets_enabled
            && ! $this->canOfferTickets()
            && $this->passesSellingWindow($date);
    }

    /** The date, window and cancellation half of canSellTickets(), with no plan question. */
    public function passesSellingWindow($date = null): bool
    {
        if ($this->is_cancelled) {
            return false;
        }

        $hasPassTicket = $this->tickets->contains(fn ($t) => $t->is_pass);

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

        return ! ($this->tickets->isNotEmpty() && $this->allTicketSalesNotStarted() && ! $this->show_unavailable_tickets);
    }

    /**
     * Whether this event may sell ANYTHING right now.
     *
     * Two ways this is true:
     *   - The event still has a sellable FREE ticket. Load-bearing: show-guest.blade.php gates the
     *     whole buy CTA on canSellTickets(), so without this an event mixing a $0 tier with paid
     *     ones would lose its buy button and take its free tier down with it - breaking the promise
     *     that free registration is unlimited on every plan.
     *   - Its paid rows may be sold, per canSellPaidTickets().
     */
    public function canOfferTickets(): bool
    {
        // A surviving free tier is enough, so the buy button and the ticket form keep rendering
        // even where every paid row is gated. It deliberately does NOT imply the paid rows are
        // sellable - that is canSellPaidTickets(), which Ticket::isSellable() asks per row.
        if ($this->tickets->contains(fn ($ticket) => ! $ticket->is_addon && (float) $ticket->price <= 0)) {
            return true;
        }

        return $this->canSellPaidTickets();
    }

    /**
     * Whether a PAID ticket on this event may be sold right now. Pro/Enterprise only.
     *
     * Separate from canOfferTickets() on purpose: an event that keeps selling because it has a free
     * tier must not carry its paid tiers through with it.
     *
     * Whose plan decides is ticketingRole() - the event's CREATOR schedule - NOT Event::isPro(),
     * which ORs over every attached role and does not filter the is_accepted pivot. Using that
     * would mean a free organizer could attach any Pro venue or talent and open paid selling on
     * their own subdomain, and a Pro curator auto-sourcing events would silently grant it to every
     * free schedule it sources from. It would also disagree with the grandfather arm below, which
     * is stamped per creator. Money already works this way: Stripe Connect routes by events.user_id.
     */
    public function canSellPaidTickets(): bool
    {
        // Selfhost resolves to the top tier, so it short-circuits before anything can deny it.
        // Every other gate in the codebase opens this way; without it an event with no pivot rows
        // would be refused on a selfhosted install, where there is no plan to sell.
        if (! config('app.hosted')) {
            return true;
        }

        $role = $this->ticketingRole();

        // Column reads, no query - canSellTickets() runs about seven times per guest render and
        // Ticket::isSellable() calls it once per row. The grandfather is a one-time stamp (see the
        // 2026_09_20 migration) rather than a live "has this event sold?" lookup, because a
        // restored backup carries paid sales.
        if ($role?->isPro() || $this->tickets_grandfathered_at !== null) {
            return true;
        }

        // The demo schedule sits on the FREE plan and is seeded with paid sales precisely to show
        // ticketing working. Live rather than stamped, because reseeding the demo creates new
        // events. Checked last: is_demo_role() short-circuits on a subdomain compare for the demo
        // itself but lazy-loads $role->user for everyone else.
        return is_demo_role($role);
    }

    /**
     * The schedule whose plan decides whether a sale of this event may happen.
     *
     * The event's OWNING schedule, not whichever subdomain the guest happened to buy through.
     * Attributing to the storefront let a free account create events on one schedule, list them on
     * a curator schedule, and sell through the curator. It also let the guest page and the checkout
     * guard disagree, rendering a buy button that the write path then refused.
     *
     * Money already works this way: Stripe Connect routes by events.user_id, not by subdomain.
     */
    public function ticketingRole(): ?Role
    {
        // Memoized: canOfferTickets() runs several times per guest page render, and
        // Ticket::isSellable() calls it once per ticket row, so an unmemoized lookup here is a
        // query per row.
        if ($this->ticketingRoleCache !== false) {
            return $this->ticketingRoleCache;
        }

        if ($this->creator_role_id) {
            $role = $this->relationLoaded('creatorRole') && $this->creatorRole
                ? $this->creatorRole
                : $this->creatorRole()->first();

            return $this->ticketingRoleCache = $role;
        }

        // creator_role_id is backfilled but still nullable on older rows. CheckData REPORTS those
        // (CheckData::225-233 collects them and continues, with "Do not add a heuristic here") - it
        // does not repair them - so this fallback is load-bearing rather than transitional.
        //
        // Ordered, because roles() carries no orderBy: for a legacy event listed on both a Pro and a
        // free schedule an unordered first() can return either, and the gate would flip between
        // requests. Lowest id is the earliest attachment, which is the closest thing to "whose
        // event this is" available once creator_role_id is gone.
        return $this->ticketingRoleCache = $this->roles->sortBy('id')->first();
    }

    /** false = not resolved yet (null is a legitimate resolved value). */
    protected $ticketingRoleCache = false;

    /**
     * Whether an ADD-ON on this event may be sold. Add-ons are a Pro feature in their own right.
     *
     * Resolved through ticketingRole(), the same schedule canSellPaidTickets() uses, and NOT
     * Event::isPro(): that ORs over every attached role without filtering the is_accepted pivot, so
     * keying add-ons on it let a free creator attach any Pro venue and sell an arbitrarily priced
     * add-on beside a $0 admission row.
     *
     * Deliberately does NOT honour tickets_grandfathered_at. That stamp restores paid TICKET
     * selling to events that were already doing it; add-ons have been Pro throughout, including
     * during the free-allowance era, so there is nothing to restore. The consequence is real and
     * intended: a grandfathered event on a lapsed schedule keeps selling its tickets while its
     * add-on rows stop, which reads as a partial outage from the organizer's seat.
     */
    /**
     * The plan question the Pro ticketing EXTRAS share: add-ons, the waitlist, the ticket embed.
     *
     * One definition on purpose. Each of these was separately keyed on something looser at some
     * point - Event::isPro() (which ORs over every attached role) or canSellTickets() (which
     * short-circuits true on any surviving $0 row) - and each time that handed a Pro feature to a
     * free schedule. Resolved through ticketingRole(), the same schedule canSellPaidTickets() uses.
     *
     * Deliberately does NOT honour tickets_grandfathered_at: that stamp restores paid TICKET
     * selling to events that were already doing it, and these extras have been Pro throughout.
     */
    public function hasProTicketingPlan(): bool
    {
        if (! config('app.hosted')) {
            return true;
        }

        return (bool) $this->ticketingRole()?->isPro();
    }

    /** Whether the ticket WAITLIST may be offered. Pair with canSellTickets() at call sites. */
    public function canOfferWaitlist(): bool
    {
        return $this->hasProTicketingPlan();
    }

    public function canSellAddons(): bool
    {
        return $this->hasProTicketingPlan();
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
            // A malformed date skips the check rather than throwing, same as the null case.
            if (self::isOccurrenceDate($date) && Carbon::parse($date, $this->scheduleTimezone())->endOfDay()->isPast()) {
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

    /**
     * The image a card should render for this event: its own flyer, else the talent schedule's
     * profile photo, else the venue's.
     *
     * Pass $width to ask for a resized derivative (see ImageUtils::VARIANT_WIDTH). It applies to
     * whichever image wins: the flyer's derivatives live on the event, the fallbacks' on the
     * schedule (Role::getProfileImageUrl()). Which image is chosen never depends on the width -
     * an event with no flyer resolves to the same schedule at every width. A derivative that has
     * not been generated (or was skipped) falls through to that image's original, so a caller
     * never has to check first - the URL is always renderable.
     *
     * $pageWidth is the event page's flyer, which shows an animated flyer as it is, the original,
     * rather than a still derivative of its first frame (HasImageVariants::imageVariantUrl()).
     * Cards and the homepage wall never pass it. It only concerns the flyer: a schedule's profile
     * photo standing in for one is never shown at page width.
     */
    public function getImageUrl(?int $width = null, bool $pageWidth = false)
    {
        if ($this->flyer_image_url) {
            if ($width && ($variant = $this->imageVariantUrl($width, pageWidth: $pageWidth))) {
                return $variant;
            }

            return $this->flyer_image_url;
        }

        if ($fallback = $this->fallbackImageRole()) {
            return $fallback->getProfileImageUrl($width);
        }

        return null;
    }

    /**
     * The picture a link preview of this event shows (og:image, twitter:image): the flyer's
     * original, else the performer's photo, else the venue's, else the photo of the schedule that
     * created it. Null when the owners have uploaded nothing - never a stock image and never one
     * of ours (docs/BRANDING_MATRIX.md rule 6, pinned by GuestSocialImageTest): a card with no
     * image degrades to the owner's own text and page.
     *
     * The original, not a card derivative: previews are shown large, and scrapers downscale.
     * width and height are present only when known: the size the image pipeline recorded for the
     * chosen image (HasImageVariants::imageSourceDimensions(), the flyer's on the event and a
     * photo's on its schedule), else whatever SeoUtils::imageDimensions() can read from a file
     * this app serves itself. Every page that renders og:image:width, and the Event JSON-LD's
     * ImageObject, picks it up from here.
     *
     * @return array{url: string, width?: int, height?: int}|null
     */
    public function shareImage(): ?array
    {
        if ($this->flyer_image_url) {
            return SeoUtils::imageObject($this->flyer_image_url, $this->imageSourceDimensions());
        }

        foreach ([$this->role(), $this->venue, $this->creatorRole] as $role) {
            if ($role && $role->profile_image_url) {
                return SeoUtils::imageObject($role->profile_image_url, $role->imageSourceDimensions());
            }
        }

        return null;
    }

    /**
     * The schedule whose profile photo stands in for a missing flyer: the talent, else the venue.
     * Null when the event has a flyer of its own or neither schedule has a photo.
     */
    protected function fallbackImageRole(): ?Role
    {
        if ($this->flyer_image_url) {
            return null;
        }

        $talent = $this->role();
        if ($talent && $talent->profile_image_url) {
            return $talent;
        }

        $venue = $this->venue;
        if ($venue && $venue->profile_image_url) {
            return $venue;
        }

        return null;
    }

    /**
     * A `srcset` of every generated width of the image getImageUrl() resolves to, or null when
     * that image's set is incomplete (see HasImageVariants::imageVariantSrcset()).
     *
     * Callers pair this with a `sizes` attribute matching their own CSS width and keep `src`
     * pointed at getImageUrl(480), so a row with no derivatives at all renders exactly as it did
     * before. Both calls resolve the same image, so the srcset never describes a different
     * picture from the src beside it.
     */
    public function imageSrcset(): ?string
    {
        if ($this->flyer_image_url) {
            return $this->imageVariantSrcset();
        }

        return $this->fallbackImageRole()?->imageVariantSrcset();
    }

    public function imageVariantSourceColumn(): string
    {
        return 'flyer_image_url';
    }

    /**
     * The image fields of a calendar card's payload, one definition for the three builders that
     * serialize an event for the Vue calendar (CalendarDataTrait::calendarEventToVueArray(),
     * RoleController::eventToVueArray() for past events, and the ?graphic=1 builder in
     * role/partials/calendar.blade.php) - which is how the list, the agenda and the popups came
     * to request originals while every other card used derivatives.
     *
     * - image_url: the card image at full size (getImageUrl()), for anything that has no better
     *   option. image_thumb_url and image_srcset are the same image resized, for the 160px
     *   mobile card and the popup.
     * - flyer_*: the event's own flyer, which the desktop list shows in a column of its own, with
     *   the original's recorded size so the column keeps its shape while the file loads.
     * - venue_profile_image: the venue's photo at card size, for a 44px avatar.
     * - venue_header_image: the header the venue's page shows (Role::headerImageUrl()), at 960.
     *   This used to hand a built-in header's NAME to the storage URL accessor, which made every
     *   one of them a dead link that the card then hid on error.
     *
     * Every key belongs to the image set. For a password-protected event,
     * calendarEventToVueArray() and eventToVueArray() null exactly these keys, so a key added here
     * cannot leak past them. The ?graphic=1 builder nulls nothing: its queries never hand it a
     * password-protected event, for anyone, members included.
     *
     * @return array<string, string|int|null>
     */
    public function cardImageFields(): array
    {
        $hasFlyer = (bool) $this->flyer_image_url;
        $flyerSize = $hasFlyer ? $this->imageSourceDimensions() : null;
        $venue = $this->venue;

        return [
            'image_url' => $this->getImageUrl() ?: null,
            'image_thumb_url' => $this->getImageUrl(ImageUtils::VARIANT_WIDTH) ?: null,
            'image_srcset' => $this->imageSrcset(),
            'flyer_url' => $this->flyer_image_url ?: null,
            'flyer_thumb_url' => $hasFlyer ? ($this->imageVariantUrl(ImageUtils::VARIANT_WIDTH) ?: $this->flyer_image_url) : null,
            'flyer_srcset' => $hasFlyer ? $this->imageVariantSrcset() : null,
            'flyer_width' => $flyerSize[0] ?? null,
            'flyer_height' => $flyerSize[1] ?? null,
            'venue_profile_image' => $venue?->getProfileImageUrl(ImageUtils::VARIANT_WIDTH) ?: null,
            'venue_header_image' => $venue?->headerImageUrl(960),
        ];
    }

    /**
     * Memoized: the resolver below asks for these once per field per event, and both walk the
     * `venue` accessor and the `roles` collection.
     */
    protected ?string $languageCodeCache = null;

    protected ?string $translationLanguageCodeCache = null;

    public function getLanguageCode()
    {
        if ($this->languageCodeCache !== null) {
            return $this->languageCodeCache;
        }

        if ($this->venue && $this->venue->language_code) {
            return $this->languageCodeCache = $this->venue->language_code;
        }

        $lang = 'en';

        foreach ($this->roles as $role) {
            if ($role->isTalent() && $role->language_code) {
                $lang = $role->language_code;
                break;
            }
        }

        return $this->languageCodeCache = $lang;
    }

    /**
     * The TARGET language this event's content is translated INTO (stored in the `_en` columns),
     * mirroring getLanguageCode()'s source resolution: the venue drives it when set, otherwise the
     * first talent role. Defaults to 'en', which reproduces the original English-only behavior.
     */
    public function getTranslationLanguageCode()
    {
        if ($this->translationLanguageCodeCache !== null) {
            return $this->translationLanguageCodeCache;
        }

        if ($this->venue && $this->venue->translation_language_code) {
            return $this->translationLanguageCodeCache = $this->venue->translation_language_code;
        }

        $lang = 'en';

        foreach ($this->roles as $role) {
            if ($role->isTalent() && $role->translation_language_code) {
                $lang = $role->translation_language_code;
                break;
            }
        }

        return $this->translationLanguageCodeCache = $lang;
    }

    /**
     * Where the event is, for a guest: the venue, else the domain of its online link, else
     * "Online" for an online link whose domain cannot be shown (free-text join instructions, an
     * IP address), else ''. The one place that fallback lives, so the calendar, the noscript list,
     * the carousel, the share graphic, the emails and the ticket list all say the same thing.
     */
    public function getVenueDisplayName($translate = true, ?string $want = null)
    {
        if ($this->venue) {
            return $this->venue->shortVenue($translate, false, $want);
        }

        return $this->getEventUrlDomain() ?: ($this->event_url ? __('messages.online', [], $want) : '');
    }

    /**
     * The domain of the event's online link: the only part of it a guest surface may show, because
     * the link itself is the private way in (messages.event_url_help). It names the location of
     * an online event on the page, in the calendar feed and in email.
     *
     * '' unless the link is a web link on a real public domain - see UrlUtils::linkHost(). The web
     * form validates event_url only as a string, so free text such as "Zoom 884 1234 pw 998877"
     * is stored, and parse_url() handed all of it back as the "domain" that printed on the public
     * page. A caller that needs a label wants getVenueDisplayName(), which says "Online" instead.
     */
    public function getEventUrlDomain()
    {
        return UrlUtils::linkHost($this->event_url);
    }

    /**
     * The external registration link as an href, or null when there is none a browser should
     * open (UrlUtils::safeHref()). The saving hook stores that form already, so this only differs
     * from registration_url on a row written around the hook - a restore uses saveQuietly() - or
     * before it existed. Every guest surface reads this, never the column.
     */
    public function registrationHref(): ?string
    {
        return UrlUtils::safeHref($this->registration_url);
    }

    /**
     * The online join link as an href, for the people entitled to the whole of it: ticket holders
     * and booked guests. Null when event_url is not a web link, and the caller then shows it as
     * text, so free-text join instructions ("Zoom 884 1234 pw 998877") stay readable for them. A
     * public surface shows getEventUrlDomain() at most.
     */
    public function eventUrlHref(): ?string
    {
        return UrlUtils::safeHref($this->event_url);
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

        $useTranslation = showing_translation($this);

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

        return $this->buildGuestUrl($data, $useCustomDomain);
    }

    /**
     * This event's guest URL with no date segment, recurring or not.
     *
     * getGuestUrl($subdomain) is NOT that for a recurring event: handed a null date,
     * getGuestUrlData() re-adds the series' first date, so it names one specific occurrence - and
     * that date need not be an occurrence at all (an excluded first date, or a starts_at weekday
     * outside days_of_week). Redirecting a non-occurrence there sent 25 of the 159 dated recurring
     * URLs in the sitemap to themselves forever. Anything that has to land on the series rather
     * than on its first night uses this. For a one-off event the two are the same URL.
     */
    public function getUndatedGuestUrl($subdomain = false, $useCustomDomain = false): string
    {
        return $this->getGuestUrl($subdomain, false, $useCustomDomain);
    }

    /**
     * Build the guest URL from already-resolved getGuestUrlData() output. Split out so callers
     * that hold the data can reuse it instead of resolving it a second time.
     */
    private function buildGuestUrl(array $data, $useCustomDomain)
    {
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

    /**
     * Where a fan's video, comment or photo sends them back to: the occurrence the form posted
     * (event_date), or its gallery.
     *
     * The content is stored under that date, and the page lists the poster's pending items by
     * date, so this has to be that occurrence. getGuestUrl($subdomain), which every one of these
     * redirects used, names the series' FIRST date instead: the item just posted was not listed
     * there, and on a series whose first date is gone the URL bounced once more. A date that is no
     * occurrence of this event - excluded since, or none posted - gives the undated series or
     * gallery (false), never the first date. A one-off event's URL has no date either way.
     */
    public function fanContentReturnUrl($subdomain, $date, bool $gallery = false): string
    {
        $date = $this->days_of_week && self::isOccurrenceDate($date)
            && $this->matchesDate($date, $this->scheduleTimezone()) ? $date : false;

        return $gallery ? $this->getPhotoGalleryUrl($subdomain, $date) : $this->getGuestUrl($subdomain, $date);
    }

    /**
     * The event's canonical guest URL: its undated URL on its home schedule, which for a recurring
     * event is the series URL. See canonicalTarget().
     */
    public function getCanonicalUrl()
    {
        $url = $this->getCanonicalUrlOrNull();

        if ($url === null) {
            \Log::error('No subdomain found for event '.$this->id);

            return '';
        }

        return $url;
    }

    /**
     * getCanonicalUrl() without the log-and-empty-string fallback: returns null when the event
     * has no routable subdomain. Stays silent, so bulk callers that walk every event in the
     * database (the sitemap) cannot flood the log.
     */
    public function getCanonicalUrlOrNull()
    {
        return $this->canonicalTarget()[0];
    }

    /**
     * The photo gallery's canonical: the gallery of $date, a recurring event's occurrence, on the
     * home host - or with no date, the series gallery.
     *
     * Unlike the event page, a dated gallery is its own canonical: it shows that night's photos,
     * which no other gallery of the series does. It used to canonicalize to the undated gallery,
     * whose next-occurrence fill-in shows a different night every week. canonicalTarget() drops
     * the date of a one-off event, whose one gallery is the canonical whatever the URL.
     */
    public function getCanonicalPhotoGalleryUrl(?string $date = null): string
    {
        $url = $date ? $this->canonicalTarget($date)[0] : $this->getCanonicalUrl();

        return $url ? $url.'/photos' : '';
    }

    /**
     * The event's canonical URL, and the schedule it is canonical on.
     *
     * No date. Every occurrence of a recurring series canonicalizes to the series URL
     * /{slug}/{id}: the dated URLs all carry the same page, and a weekly event made 52 of them a
     * year, each self-canonical. The undated URL used to canonicalize to "today's" occurrence
     * instead, a target that moved every day. The dated URLs still render - sales are keyed by
     * event_date, and tickets, email, the Stripe cancel URL and waitlist mail all link to them -
     * they just stop competing with the series. A one-off event's URL never had a date, so it is
     * unchanged. $date names one occurrence on the same home host, for two callers: og:url, the
     * share target of a dated event page (which Google ignores for canonicalization), and a dated
     * photo gallery's canonical (getCanonicalPhotoGalleryUrl()), since each night's gallery shows
     * photos no other one does. Never pass one for an event page's canonical.
     *
     * The home schedule is the one getGuestUrlData() picks - the claimed performer, then the
     * claimed venue, then the creator - but only while that schedule SERVES the event: its pivot
     * accepted, claimed, and not deleted. That pick never looked at the pivot, and every guest
     * lookup does (EventRepo::getEvent() requires is_accepted on the host), while saveEvent()
     * leaves a claimed performer on a venue's or curator's event pending until they accept - so
     * the canonical, and the sitemap built from it, named a host where the event 404s. When the
     * pick does not serve it, the first schedule that does takes over: a performer, then a venue,
     * then anything else, lowest id first so the choice cannot drift between requests. Its URL is
     * rebuilt with getGuestUrlData(), whose venue/performer slug rule is exactly what that
     * schedule's own calendar links to; the id resolves it either way.
     *
     * The custom domain is used only when the home schedule is served directly on it (direct +
     * active); redirect mode keeps the subdomain canonical. getGuestUrl() is deliberately not
     * this: email, graphics and every in-app link go through it and keep their dates and hosts.
     *
     * When no schedule serves the event yet (a member previewing one that is still pending), the
     * URL is the one getGuestUrlData() picked and there is no home.
     *
     * @return array{0: ?string, 1: ?Role} the URL (null when the event has no routable subdomain)
     *                                     and its home schedule (null when none serves the event)
     */
    public function canonicalTarget($date = null): array
    {
        // `?: false`, never null: handed null, getGuestUrlData() re-adds the series' first date.
        $date = $date ?: false;
        $data = $this->getGuestUrlData(false, $date);

        $picked = $data['subdomain']
            ? $this->roles->first(fn ($role) => $role->subdomain == $data['subdomain'])
            : null;

        if ($picked && self::servesGuestPage($picked)) {
            return [$this->buildGuestUrl($data, $picked->servesOnCustomDomain()), $picked];
        }

        $home = $this->servingHome();

        if ($home) {
            return [
                $this->buildGuestUrl($this->getGuestUrlData($home->subdomain, $date), $home->servesOnCustomDomain()),
                $home,
            ];
        }

        if (! $data['subdomain']) {
            return [null, null];
        }

        return [$this->buildGuestUrl($data, $picked && $picked->servesOnCustomDomain()), null];
    }

    /**
     * Whether $role, one of this event's roles, renders the event on its own host: the three
     * things RoleController::viewGuest() and EventRepo::getEvent() check before answering 200.
     * Needs the pivot, so only for a role that came through the roles relation.
     */
    private static function servesGuestPage(Role $role): bool
    {
        return $role->pivot
            && $role->pivot->is_accepted
            && $role->isClaimed()
            && ! $role->is_deleted;
    }

    /**
     * The first of this event's schedules that serves it (servesGuestPage()): a performer, then a
     * venue, then anything else, lowest id first so the choice cannot drift between requests.
     * Null when none does yet.
     */
    private function servingHome(): ?Role
    {
        $serving = $this->roles->filter(fn ($role) => self::servesGuestPage($role))->sortBy('id');

        return $serving->first(fn ($role) => $role->isTalent())
            ?? $serving->first(fn ($role) => $role->isVenue())
            ?? $serving->first();
    }

    /**
     * The route parameters of this event's guest URL.
     *
     * Only a recurring event's URL carries a date segment, and $date decides it:
     *  - null: the anchor date, the schedule-local date of starts_at - the series' FIRST date,
     *    which is not necessarily an occurrence. This is what every getGuestUrl($subdomain) call
     *    gets, including the links in email, sales and graphics.
     *  - false or '': no date, the undated series URL. See getUndatedGuestUrl().
     *  - a Y-m-d string: that occurrence.
     *
     * With the id included, a slug a route owns is written "{slug}-event". See SHADOWED_SLUGS.
     */
    public function getGuestUrlData($subdomain = false, $date = null, $includeId = true)
    {
        $venueSubdomain = $this->venue && $this->venue->isClaimed() ? $this->venue->subdomain : null;
        $roleSubdomain = $this->role() && $this->role()->isClaimed() ? $this->role()->subdomain : null;
        $namedBySchedule = (bool) $subdomain;

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

        // A link that names no schedule - every mail, notification and card that asks for "the"
        // event URL - goes where the event is shown. The pick above never looked at the pivot,
        // while every guest lookup does, so a performer who had not yet accepted a venue's or a
        // curator's event (or a deleted one) got the link, and it 404'd. The same serving schedule
        // canonicalTarget() falls back to; a schedule the caller names is the caller's choice, and
        // with none serving yet the pick stands.
        if (! $namedBySchedule && $subdomain) {
            $picked = $this->roles->first(fn ($role) => $role->subdomain == $subdomain);

            if (! $picked || ! self::servesGuestPage($picked)) {
                $subdomain = $this->servingHome()?->subdomain ?? $subdomain;
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
            // Beside the id only: a bare /{slug} is looked up by the slug, so it has to stay as is.
            'slug' => $includeId ? self::guestUrlSlug($slug) : $slug,
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

    /**
     * The slug segment of an event URL that also carries the event's id: $slug, or "{slug}-event"
     * when a route owns that word. See SHADOWED_SLUGS. For the few places that build such a URL
     * without getGuestUrlData().
     */
    public static function guestUrlSlug(?string $slug): ?string
    {
        return $slug !== null && in_array($slug, self::SHADOWED_SLUGS, true) ? $slug.'-event' : $slug;
    }

    /**
     * $slug as an event may store it: "{slug}-event" when a route under a schedule's address owns
     * the word (UrlUtils::reservedPathSlugs()). The event's short link, /{slug} with no id, is
     * looked up by its slug, so an event slugged "book", "request" or "follow" was never reached
     * by it: the appointment booking page, the event submission flow or a follow answered instead.
     *
     * Applied wherever a slug is made or typed (SlugPatternUtils::generateSlug(), and a slug typed
     * into the editor). An event that already holds such a slug keeps it, and its editor shows the
     * link with the id instead (getShortGuestUrl()).
     */
    public static function storableSlug(string $slug): string
    {
        return in_array($slug, UrlUtils::reservedPathSlugs(), true) ? $slug.'-event' : $slug;
    }

    /**
     * The event's shortest guest URL, the one its editor shows and copies: /{slug} with no id, or
     * the URL with the id when a route owns that slug (see storableSlug()). The slug here can be
     * another schedule's subdomain - on a venue, its event with a claimed act takes the act's - so
     * any event can meet this, whatever it is slugged itself.
     */
    public function getShortGuestUrl($subdomain = false, $useCustomDomain = false): string
    {
        $slug = $this->getGuestUrlData($subdomain, false, false)['slug'];

        return $this->getGuestUrl($subdomain, false, $useCustomDomain, ! is_string($slug) || self::storableSlug($slug) !== $slug);
    }

    /**
     * "{name} at {where}" for calendar entries and feeds: the venue's name, else the domain of the
     * online link. Just the name when there is neither - never a dangling "{name} at", and never
     * "at Online", which reads as a place.
     */
    public function getTitle()
    {
        $where = ($this->venue ? $this->venue->getDisplayName() : '') ?: $this->getEventUrlDomain();

        if ($where === '') {
            return $this->name;
        }

        return str_replace([':role', ':venue'], [$this->name, $where], __('messages.event_title'));
    }

    /**
     * The event's meta description: GuestSeo::eventDescription(), which is where the rules live.
     *
     * $want/$viewingRole let the guest layout resolve by language instead of by the translate
     * boolean; a caller without them gets the viewing schedule's language, else the event's own.
     * $date is the occurrence the page is about - see GuestSeo::eventDescription().
     */
    public function getMetaDescription($date = null, ?string $want = null, ?Role $viewingRole = null)
    {
        $want ??= $viewingRole
            ? $viewingRole->displayLanguageCode()
            : (showing_translation($this) ? $this->getTranslationLanguageCode() : $this->getLanguageCode());

        return \App\Utils\GuestSeo::eventDescription($this, is_string($date) ? $date : null, $want, $viewingRole);
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

    /**
     * The .ics download, on $subdomain when given. The event page passes the schedule it is showing:
     * the canonical schedule (false) can be an act that has not accepted the event yet, and the
     * download answers only where the event is accepted, as that schedule's page does.
     */
    public function getAppleCalendarUrl($date = null, $subdomain = false)
    {
        $guestUrl = $this->getGuestUrl($subdomain, $date);

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

    /**
     * The occurrence as a Carbon. With $locale it is rendered in the SCHEDULE's timezone - an
     * event falls on a given day, at a given clock time, because of where it happens and not
     * because of who is looking at it. $timezoneOverride is the only way to render it anywhere
     * else - AppointmentTimeUtils uses it to show a guest their own local time, and a few callers
     * pass scheduleTimezone() explicitly where the pinning is the point being documented.
     *
     * This used to prefer the authenticated viewer's account timezone, which quietly made every
     * unpinned read site viewer-dependent: a 7:30pm London show read as the next day at 00:00 for
     * a signed-in viewer at UTC+5:30, on the calendar payload, the day cells, the guest list and
     * the feeds, while the edit form (which always pinned the schedule) disagreed.
     */
    public function getStartDateTime($date = null, $locale = false, $timezoneOverride = null)
    {
        if (strlen($this->starts_at) === 10) {
            // Date-only format (Y-m-d), assume midnight
            $startAt = Carbon::createFromFormat('Y-m-d', $this->starts_at, 'UTC')->startOfDay();
        } else {
            $startAt = Carbon::createFromFormat('Y-m-d H:i:s', $this->starts_at, 'UTC');
        }

        // Convert before applying the occurrence date. $date is a calendar date in the
        // schedule's zone (that is how sales.event_date is stored), so setting it on the UTC
        // datetime and converting afterwards slides an evening event back a day.
        //
        // Resolved here rather than above so a $locale=false caller - ICS export, Google and
        // CalDAV sync, the isPast() gates - never lazy-loads creatorRole for a zone it discards.
        if ($locale) {
            $startAt->setTimezone($timezoneOverride ?: $this->scheduleTimezone());
        }

        // isOccurrenceDate() rather than the bare shape regex: guest views hand this raw request
        // input (role/show-guest.blade.php passes request()->date straight through), and
        // '2026-13-45' matches the shape but throws in Carbon::parse() below.
        if (self::isOccurrenceDate($date)) {
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
     * The coupon's discount formatted for display, with no surrounding words: '15%' for a
     * percentage, or the amount in the event's ticket currency for a fixed discount. Empty
     * when no discount is set. This is what {coupon_discount} substitutes.
     */
    public function getFormattedCouponDiscountAttribute(): string
    {
        // Covers null, '' and a stored 0 - "0% off" is noise, not information.
        if ($this->coupon_discount === null || (float) $this->coupon_discount <= 0) {
            return '';
        }

        // A null type means the row predates the column, or a client wrote the amount without
        // one; both read as a fixed amount, matching what the forms open on. ?: not ??, so an
        // empty string resolves the same way the blade seed already resolves it.
        if (($this->coupon_discount_type ?: self::DEFAULT_COUPON_DISCOUNT_TYPE) === 'percentage') {
            // Cast first so the stored decimal(13,3) reads as '15' rather than '15.000'.
            return ((float) $this->coupon_discount).'%';
        }

        return MoneyUtils::format($this->coupon_discount, $this->ticket_currency_code);
    }

    /**
     * The same value as a display phrase ('15% off'), for the guest-facing surfaces that
     * show it beside the coupon code. Empty when no discount is set.
     */
    public function couponDiscountLabel(): string
    {
        $formatted = $this->formatted_coupon_discount;

        return $formatted === '' ? '' : __('messages.discount_off', ['amount' => $formatted]);
    }

    /**
     * The price the coupon comes off, or null when there is nothing to discount. Shared by
     * {discounted_price} and {original_price} so the pair can never name different bases.
     */
    private function couponDiscountBasePrice(): ?float
    {
        // The coupon points at an OUTSIDE ticket platform and nothing here redeems it -
        // internal discounts are the separate promo_codes table. The field is hidden but
        // never cleared when an owner switches to internal tickets or RSVP (the form uses
        // v-show, which still submits, and neither the watcher nor EventRepo scrubs it), so
        // a stale value survives the switch. Quoting a discounted PRICE off that would put a
        // number on a flyer that our own checkout will not charge. Same condition the guest
        // page gates its coupon block on (event/show-guest.blade.php), so a graphic and the
        // event page can never disagree about the price.
        //
        // Deliberately narrower than {coupon_discount}, which is not mode-gated: changing
        // that would rewrite output for templates already in use, and "15% off" is a vague
        // claim where "119" is a specific one.
        if ($this->tickets_enabled || $this->rsvp_enabled) {
            return null;
        }

        // Nothing to take off, so there is no before-and-after to show.
        if ($this->formatted_coupon_discount === '') {
            return null;
        }

        // getPrice() is the source {price} renders from, so a was/now line can never
        // contradict it. It returns '' for the free cases it catches, but still returns 0.0
        // when a free ticket sits beside a paid one and min() picks the zero - so test the
        // number, not the empty string, or a free event with a stale discount renders '0'.
        $base = (float) EventTextGenerator::getPrice($this);

        return $base > 0 ? $base : null;
    }

    /**
     * The price after the coupon, rounded to the currency's decimals. Null when there is no
     * discount, or no price for it to come off.
     */
    private function couponDiscountedPrice(): ?float
    {
        $base = $this->couponDiscountBasePrice();

        if ($base === null) {
            return null;
        }

        $value = (float) $this->coupon_discount;

        // Must resolve the missing TYPE exactly as getFormattedCouponDiscountAttribute()
        // does. If the two ever drift, one token says '30 off' while the other subtracts
        // 30 percent, on the same line of the same post. They agree on the type only: the
        // clamp below has no counterpart there, so a legacy row storing 150 percent still
        // renders '150% off' beside a floored '0'. Validation blocks new rows like that.
        $isPercentage = ($this->coupon_discount_type ?: self::DEFAULT_COUPON_DISCOUNT_TYPE) === 'percentage';

        // The validator already bounds a percentage at 100, but it keys off the request and
        // an older row may predate it; clamping here costs nothing and keeps the floor at 0.
        $off = $isPercentage ? $base * min(100, max(0, $value)) / 100 : $value;

        return round(max(0, $base - $off), MoneyUtils::decimalsFor($this->ticket_currency_code));
    }

    /**
     * The discounted price as a bare figure - '119', '126.65', '4,250' - with no currency
     * symbol, so a template can put its own wording around it. Empty when no discount
     * applies. This is what {discounted_price} substitutes.
     */
    public function getDiscountedPriceAttribute(): string
    {
        $price = $this->couponDiscountedPrice();

        return $price === null ? '' : MoneyUtils::formatNumber($price, $this->ticket_currency_code);
    }

    /**
     * The list price the discount comes off, formatted to match {discounted_price}. Empty
     * unless a discount actually applies, so a 'was/now' pair renders in full or collapses;
     * {price} is the unconditional one. Unrelated to Eloquent's getOriginal().
     */
    public function getOriginalPriceAttribute(): string
    {
        $base = $this->couponDiscountBasePrice();

        return $base === null ? '' : MoneyUtils::formatNumber($base, $this->ticket_currency_code);
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

        if ($this->name_en && (showing_translation($this))) {
            $value = $this->name_en;
        }

        $value = str_ireplace('fuck', 'F@#%', $value);

        return $value;
    }

    public function translatedDescription()
    {
        $value = $this->description_html;

        if ($this->description_html_en && (showing_translation($this))) {
            $value = $this->description_html_en;
        }

        return $value;
    }

    public function translatedShortDescription()
    {
        $value = $this->short_description;

        if ($this->short_description_en && (showing_translation($this))) {
            $value = $this->short_description_en;
        }

        return $value;
    }

    /**
     * The best stored text for `$field` in the language `$want`, for a guest viewing this event on
     * `$viewingRole`'s page.
     *
     * translatedName() and friends answer "show the translation, yes or no", which only works while
     * the event's language pair matches the viewing schedule's. On a curator aggregating other
     * schedules' events it does not: `name` is in the EVENT's authored language (the venue's, via
     * getLanguageCode()) and `name_en` is in the EVENT's target (getTranslationLanguageCode()), so a
     * `he`->`en` curator showing an `en`->`he` venue's event served Hebrew to the English view and
     * English to the Hebrew view. Naming the language instead of a direction is correct either way,
     * and needs no new columns: for the mismatched case both strings are already stored, just under
     * the labels the other schedule assigned them.
     *
     * Resolution order, first hit wins:
     *   1. the curator pivot, which holds the translation into the curator's OWN authored language
     *   2. `{$field}_en`, when the event's target language is what we want
     *   3. `{$field}` - either it is already the language we want, or nothing stored is, and the
     *      authored original beats surfacing a surprise third language
     */
    public function textInLanguage(string $field, string $want, ?Role $viewingRole = null): ?string
    {
        $authored = $this->{$field};

        if ($viewingRole && $viewingRole->isCurator() && $viewingRole->language_code === $want) {
            $pivot = $this->roles->where('id', $viewingRole->id)->first()?->pivot;
            // description resolves to description_html_translated: the callers of the description
            // wrapper render HTML, and the pivot stores both the source and the derived HTML.
            $pivotField = $field === 'description_html' ? 'description_html_translated' : $field.'_translated';
            if ($pivot && ! empty($pivot->{$pivotField})) {
                return $pivot->{$pivotField};
            }
        }

        $translated = $this->{$field === 'description_html' ? 'description_html_en' : $field.'_en'};

        if ($translated && $this->getTranslationLanguageCode() === $want) {
            return $translated;
        }

        return $authored;
    }

    public function nameInLanguage(string $want, ?Role $viewingRole = null): string
    {
        return str_ireplace('fuck', 'F@#%', (string) $this->textInLanguage('name', $want, $viewingRole));
    }

    public function shortDescriptionInLanguage(string $want, ?Role $viewingRole = null): ?string
    {
        return $this->textInLanguage('short_description', $want, $viewingRole);
    }

    public function descriptionHtmlInLanguage(string $want, ?Role $viewingRole = null): ?string
    {
        return $this->textInLanguage('description_html', $want, $viewingRole);
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
        // Encoded like every other user-visible id. Present but null on a general-admission event
        // so a subscriber can branch on it without special-casing a missing key.
        $data->seating_plan_id = $this->seating_plan_id ? UrlUtils::encodeId($this->seating_plan_id) : null;
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

                // Allocated bands. `quantity` above is a derived mirror of the seat count, so a
                // subscriber reading it alone cannot tell an 80-seat band from an 80-cap standing
                // one, nor that the seats must be picked rather than counted.
                if ($ticket->seating_band) {
                    $row['seating_band'] = $ticket->seating_band;
                    $row['is_allocated'] = $ticket->isAllocated();
                }

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
        // Allocated events need their own answer. occurrenceSeatsRemaining() returns null for
        // them on purpose (each band owns its seats, so there is no single house number), and null
        // reads as "unlimited, never sold out" - which would leave a completely full house showing
        // as available, and would keep guests off the waitlist forever, since WaitlistController
        // only opens it once this returns true.
        if ($this->hasAllocatedSeating()) {
            return $this->allocatedSoldOut($date);
        }

        // Sold out when the shared per-occurrence house (after both regular sales and
        // pass advance-bookings) has no seats left. Null = unlimited => never sold out.
        // Equivalent to the old per-ticket "every sold out" check when nothing is
        // reserved, and correctly accounts for pass reservations when they exist.
        $remaining = $this->occurrenceSeatsRemaining($date);

        return $remaining !== null && $remaining <= 0;
    }

    /**
     * Every sellable ticket on an allocated event is exhausted.
     *
     * Allocated bands are measured against the seat map; standing and general-admission tickets on
     * the same event keep the quantity path they have always used, so both have to agree before the
     * event counts as full.
     */
    protected function allocatedSoldOut(?string $date): bool
    {
        $tickets = $this->seatTickets();

        if ($tickets->isEmpty()) {
            return false;
        }

        foreach ($tickets as $ticket) {
            // Share this instance so isAllocated() and the map lookup memoize once, not per ticket.
            $ticket->setRelation('event', $this);

            if ($ticket->isAllocated($date)) {
                if ((int) $this->allocatedSeatsRemaining($date, $ticket) > 0) {
                    return false;
                }

                continue;
            }

            // Unlimited quantity is never sold out.
            if ($ticket->quantity <= 0) {
                return false;
            }

            if (($ticket->quantity - $ticket->soldCountFor($date)) > 0) {
                return false;
            }
        }

        return true;
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

    /**
     * Whether every seat ticket draws on one shared pool of the same size - the
     * precondition for combined mode meaning anything.
     *
     * EVERY seat ticket must carry the same positive quantity. This used to ignore the
     * unlimited ones, so an event with [unlimited, 40] counted as "combined" and nothing
     * downstream agreed what that meant: occurrenceSeatsRemaining() reported no ceiling
     * (so the guest picker was unbounded), while TicketController capped the whole order
     * at 40 and counted the unlimited ticket's sales against that 40 - once it had sold
     * 40, the limited ticket was rejected forever. Matching the admin form's own
     * hasSameTicketQuantities (event/edit.blade.php), which has always been this strict,
     * makes such an event plainly individual instead.
     */
    public function hasSameTicketQuantities()
    {
        // Combined mode is meaningless on an allocated event, and actively harmful: quantities are
        // DERIVED from the plan, so two equal-sized sections make this true by accident, and the
        // checkout guard then caps the whole house at one band's size. A 100+100 house stopped
        // selling at 100 while a hundred seats sat empty and the picker went on offering them.
        if ($this->hasAllocatedSeating()) {
            return false;
        }

        $tickets = $this->seatTickets();
        if ($tickets->count() <= 1) {
            return false;
        }

        $quantities = $tickets->pluck('quantity')->map(fn ($qty) => (int) $qty)->unique();

        return $quantities->count() === 1 && $quantities->first() > 0;
    }

    public function getSameTicketQuantity()
    {
        if (! $this->hasSameTicketQuantities()) {
            return null;
        }

        // The one distinct positive quantity hasSameTicketQuantities() just validated.
        // Belt and braces now that the predicate is strict: first()->quantity would be
        // fine, but this cannot return the null of an unlimited ticket even if the
        // predicate is ever loosened again, and a null here reaches the checkout guards
        // as a capacity of zero.
        return $this->seatTickets()->pluck('quantity')->first(fn ($qty) => $qty > 0);
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

        // Allocated seating has no shared house: every band owns its own seats, and a standing
        // section keeps the ordinary quantity path. Bounding the order by one pooled number would
        // be wrong in both directions - it would cap a 400-seat stalls order at the smallest band,
        // and it would let a band oversell as long as the total still fit. The real bound is
        // per-ticket, via allocatedSeatsRemaining(), enforced seat by seat at checkout.
        if ($this->hasAllocatedSeating()) {
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
     * Seats remaining for the occurrence the ticket form is selling. Resolves a
     * missing date the same way TicketController::assertLegTicketsAvailable() does,
     * so the form and the guard it will be checked against can never disagree - a
     * one-time event page reaches the form with no date, and occurrenceSeatsRemaining()
     * would then report "unlimited" and drop the shared-pool bound. Null = unlimited.
     */
    public function seatsRemainingForSale(?string $date): ?int
    {
        return $this->occurrenceSeatsRemaining($date ?: $this->saleEventDateFromStartsAt());
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
        return $this->saleEventDateFor($this->starts_at);
    }

    /**
     * The same derivation for an ARBITRARY starts_at, so a caller can work out the occurrence key
     * an event used to have. Moving a one-time event otherwise strands its seat map on the old
     * date, where nothing will ever look for it again.
     */
    public function saleEventDateFor($startsAt): ?string
    {
        if (! $startsAt) {
            return null;
        }

        // Date-only starts_at already represents the calendar date in the schedule's view.
        if (strlen($startsAt) === 10) {
            return (string) $startsAt;
        }

        $this->loadMissing('creatorRole');
        $tz = $this->creatorRole?->timezone ?? config('app.timezone');

        return Carbon::createFromFormat('Y-m-d H:i:s', (string) $startsAt, 'UTC')->timezone($tz)->format('Y-m-d');
    }

    /*
     * Structured data: the schema.org Event node on the guest pages (layouts/app-guest.blade.php)
     * and the entries a schedule's node lists (Role::schemaNode()).
     *
     * Every value describes what the page itself shows and offers, and nothing it does not. A crawl
     * of 1,322 production event pages found an invented price-0 offer on 90% of them (1,057 beside
     * isAccessibleForFree: false), an address of only a country on 18%, events with no venue
     * located at their ORGANIZER, online events that were never a VirtualLocation, and a quarter
     * described as "{name} - Event". Google reads all of it as fact. An event whose page states no
     * place or price says none here: honestly ineligible for event results beats eligible on
     * invented data.
     *
     * events.event_url is the PRIVATE join link of an online event (messages.event_url_help): the
     * page prints only its domain and federation stopped sending it. It never appears here. An
     * online event's VirtualLocation is its own page, which is where people register for the link.
     */

    private const SCHEMA_IN_STOCK = 'https://schema.org/InStock';

    private const SCHEMA_SOLD_OUT = 'https://schema.org/SoldOut';

    /** The longest description a node carries: more than any consumer reads, less than a novel. */
    public const SCHEMA_DESCRIPTION_MAX = 5000;

    /**
     * This event as a schema.org Event node, for the guest page of $viewingRole shown in $lang.
     *
     * $date is the occurrence the page shows - the dated URL's, the next one on a series page, a
     * one-off event's own day - and it dates the node. The url is the page's canonical, which for a
     * recurring event is the SERIES (canonicalTarget()), so a dated page and the series page
     * describe different occurrences under one URL. That is why the node has no @id: one id would
     * name one entity with two start dates.
     *
     * Other schedules' names are resolved in $lang (Role::nameInLanguage()), never through
     * translatedName(), which asks the viewer's translate flag about a schedule whose language pair
     * may be the reverse of the page's (TranslationLanguageTargetTest).
     *
     * $compact is an entry in a schedule node's upcoming list: when and where, what it looks like
     * and who organizes it. No description, performers or offers - those cost ticket queries per
     * event and belong on the event's own page - and an organizer that IS the listing schedule is a
     * bare {"@id"} pointing at the node the page already carries.
     *
     * @return array<string, mixed>
     */
    public function schemaNode(?string $date, Role $viewingRole, string $lang, bool $compact = false): array
    {
        $url = $this->schemaUrl($viewingRole, $lang);

        $node = $compact ? [] : ['@context' => 'https://schema.org'];
        $node['@type'] = 'Event';
        $node['name'] = SeoUtils::cleanText($this->nameInLanguage($lang, $viewingRole));

        if (! $compact && ($description = $this->getSchemaDescription($lang, $viewingRole)) !== null) {
            $node['description'] = $description;
        }

        if ($url !== '') {
            $node['url'] = $url;
        }

        $node['startDate'] = $this->getSchemaStartDate($date);

        if (($endDate = $this->getSchemaEndDate($date)) !== null) {
            $node['endDate'] = $endDate;
        }

        $node['eventStatus'] = $this->getSchemaEventStatus();

        if ($location = $this->getSchemaLocation($lang, $viewingRole)) {
            $node['eventAttendanceMode'] = self::schemaAttendanceModeOf($location);
            $node['location'] = $location;
        }

        if ($image = SeoUtils::schemaImageObject($this->shareImage())) {
            $node['image'] = $image;
        }

        if ($organizer = $this->getSchemaOrganizer($lang, $compact ? $viewingRole : null)) {
            $node['organizer'] = $organizer;
        }

        if ($compact) {
            return $node;
        }

        if ($performers = $this->getSchemaPerformers($lang)) {
            $node['performer'] = count($performers) === 1 ? $performers[0] : $performers;
        }

        [$offers, $free] = $this->schemaOffersAndAccess($date, $url);

        if ($offers) {
            $node['offers'] = count($offers) === 1 ? $offers[0] : $offers;
        }

        if ($free !== null) {
            $node['isAccessibleForFree'] = $free;
        }

        $node['inLanguage'] = $lang;

        return $node;
    }

    /**
     * The event's description as plain text in $lang: the long description (block-aware and
     * entity-decoded, SeoUtils::plainText()), else the short one, at most SCHEMA_DESCRIPTION_MAX
     * characters. Null when the owner wrote neither - never a stand-in such as "{name} - Event",
     * which a quarter of production's event pages published as their description.
     */
    public function getSchemaDescription(string $lang, ?Role $viewingRole = null): ?string
    {
        $text = SeoUtils::plainText($this->descriptionHtmlInLanguage($lang, $viewingRole))
            ?: SeoUtils::cleanText($this->shortDescriptionInLanguage($lang, $viewingRole));

        return $text === '' ? null : SeoUtils::excerpt($text, self::SCHEMA_DESCRIPTION_MAX);
    }

    /**
     * Where the event happens, as the page shows it: the venue as a Place, the event's own page as a
     * VirtualLocation when it is online, both for a hybrid, and null for neither.
     *
     * Null is the honest answer for an event with no venue and no link. The old fallback made its
     * ORGANIZER the place - a comedy show "located" at the promoter's schedule, with that
     * schedule's country - and filled the rest with an empty PostalAddress. The VirtualLocation's
     * url is the page, never event_url, which is the private join link.
     *
     * @return array<string, mixed>|array<int, array<string, mixed>>|null
     */
    public function getSchemaLocation(string $lang, ?Role $viewingRole = null): ?array
    {
        $place = $this->venue?->schemaPlace($lang);
        $pageUrl = $this->event_url ? $this->schemaUrl($viewingRole, $lang) : '';
        $virtual = $pageUrl !== '' ? ['@type' => 'VirtualLocation', 'url' => $pageUrl] : null;

        if ($place && $virtual) {
            return [$place, $virtual];
        }

        return $place ?? $virtual;
    }

    /**
     * In person, online or both, for a caller that needs only the mode (the Meta Pixel's
     * content_category); null for an event with neither. The JSON-LD derives the same mode from
     * the location it actually publishes (schemaAttendanceModeOf()), which also drops a venue that
     * has nothing at all to say about itself (Role::schemaPlace()).
     */
    public function getSchemaAttendanceMode(): ?string
    {
        return match (true) {
            $this->venue && $this->event_url => 'https://schema.org/MixedEventAttendanceMode',
            (bool) $this->event_url => 'https://schema.org/OnlineEventAttendanceMode',
            (bool) $this->venue => 'https://schema.org/OfflineEventAttendanceMode',
            default => null,
        };
    }

    /** The attendance mode a getSchemaLocation() result describes. */
    private static function schemaAttendanceModeOf(array $location): string
    {
        if (array_is_list($location)) {
            return 'https://schema.org/MixedEventAttendanceMode';
        }

        return ($location['@type'] ?? null) === 'VirtualLocation'
            ? 'https://schema.org/OnlineEventAttendanceMode'
            : 'https://schema.org/OfflineEventAttendanceMode';
    }

    /**
     * Who organizes the event: the first CLAIMED schedule of its creator, its venue and its
     * performers - a Person for a talent schedule, an Organization otherwise - named in $lang, with
     * its canonical url and "{canonical}#schedule" @id, the @id the schedule's own page gives its
     * node (Role::schemaNode()), so the two describe one entity.
     *
     * Null when none of them is claimed. An unclaimed placeholder has no page of its own to point
     * at, and the old last resort - an Organization named after the EVENT - was invented.
     *
     * $listedOn: the schedule whose page lists this event (a compact entry). When the organizer IS
     * that schedule, a bare {"@id"} points at the node the page already carries.
     *
     * @return array<string, string>|null
     */
    public function getSchemaOrganizer(string $lang, ?Role $listedOn = null): ?array
    {
        $candidates = collect([$this->creatorRole, $this->venue])->concat($this->members());

        foreach ($candidates as $candidate) {
            if (! $candidate || $candidate->schemaCanonicalUrl() === null) {
                continue;
            }

            if (! $agent = $candidate->schemaAgent($lang)) {
                continue;
            }

            if ($listedOn && $candidate->id === $listedOn->id) {
                return ['@id' => $agent['@id']];
            }

            return $agent;
        }

        return null;
    }

    /**
     * The talent on the bill, each a Person named in $lang, linked (url and @id) only when claimed:
     * an unclaimed act's name is still the page's lineup, it just has no page of its own.
     *
     * @return array<int, array<string, string>>
     */
    public function getSchemaPerformers(string $lang): array
    {
        return $this->members()
            ->map(fn (Role $member) => $member->schemaAgent($lang, 'Person'))
            ->filter()
            ->values()
            ->all();
    }

    /** Cancelled or scheduled: the app records no postponement to report, and no completion. */
    public function getSchemaEventStatus(): string
    {
        return $this->is_cancelled
            ? 'https://schema.org/EventCancelled'
            : 'https://schema.org/EventScheduled';
    }

    /**
     * The offers the page makes for the occurrence on $date, each at the page's canonical $url.
     * See schemaOffersAndAccess().
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSchemaOffers(?string $date, string $lang, ?Role $viewingRole = null): array
    {
        return $this->schemaOffersAndAccess($date, $this->schemaUrl($viewingRole, $lang))[0];
    }

    /**
     * The offers the page actually makes, and whether getting in costs nothing.
     *
     * In the order the page decides its call to action (event/show-guest.blade.php):
     *  - Cancelled, or a series with no occurrence left to date them: no offers.
     *  - RSVP: one free Offer at the registration form (?rsvp=true) while it takes registrations,
     *    SoldOut once this date's RSVP limit is reached (the form then offers the waitlist).
     *  - Tickets: none while the PLAN stops the event selling (canOfferTickets()) - deliberately
     *    not SoldOut, which would claim a sell-out that never happened. While it sells, or before
     *    every type has gone on sale, one Offer per type the ticket form offers: a pass only when
     *    passes are all a buyer can get now (or, before anything is on sale, all there will be),
     *    never a paid type the plan cannot sell (Ticket::isSellable()), never one whose sales have
     *    ended. SoldOut only where the form prints "Sold out": that type's available quantity for
     *    this date (Ticket::availableQuantity()), or the whole house. No inventoryLevel, which used
     *    to publish the TOTAL quantity as if it were what remained.
     *  - An external registration with a price (the page's price badge): one Offer at that price,
     *    at the registration link, until the event is over.
     *  - Anything else: none. The old default, a price-0 in-stock Offer on every event, sat on 90%
     *    of production's event pages.
     *
     * isAccessibleForFree comes from the pricing, not the availability: true for RSVP; for
     * tickets, whether a type the event may sell costs nothing; for an external registration,
     * whether its price is zero; null (omitted) where the page states no price.
     *
     * Prices are in the event's currency, else the installation's - never a hardcoded USD.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: ?bool}
     */
    private function schemaOffersAndAccess(?string $date, string $url): array
    {
        // The key sales and RSVPs are counted under: the occurrence, or a one-off event's own day.
        $occurrence = self::isOccurrenceDate($date) ? $date : $this->saleEventDateFromStartsAt();

        // A series with no occurrence left falls back to its FIRST date for the page's dates, and
        // the sale and RSVP checks skip a missing date - which would offer seats at a past date.
        $closed = $this->is_cancelled || ($this->days_of_week && ! self::isOccurrenceDate($date));
        $currency = $this->ticket_currency_code ?: platform_currency();

        if ($this->rsvp_enabled) {
            if ($closed || ! $this->canAcceptRsvp($occurrence)) {
                return [[], true];
            }

            return [[self::schemaOffer([
                'price' => 0,
                'priceCurrency' => $currency,
                'url' => self::withSchemaQuery($url, 'rsvp=true'),
                'availability' => $this->isRsvpFull($occurrence) ? self::SCHEMA_SOLD_OUT : self::SCHEMA_IN_STOCK,
                'validFrom' => $this->schemaPublishedAt(),
            ])], true];
        }

        if ($this->tickets_enabled && $this->tickets->isNotEmpty()) {
            // The rows the ticket form offers (event/tickets.blade.php), whatever the date. Sharing
            // this instance keeps isSellable() and the quantity lookups from reloading the event
            // once per row.
            $tickets = $this->tickets->each(fn (Ticket $ticket) => $ticket->setRelation('event', $this));

            // Whether passes are all there is is asked of the rows a buyer can get: those on sale
            // now, else - before anything is - those still to come. Asked of every row, a regular
            // ticket whose sales had ended hid the pass still on sale, and one not yet on sale hid
            // the only thing a buyer could get today. show_unavailable_tickets only changes what
            // the form displays, never what is offered.
            $candidates = $tickets->filter(fn (Ticket $ticket) => $ticket->isSellable() && ! $ticket->isSalesEnded());
            $buyableNow = $candidates->reject(fn (Ticket $ticket) => $ticket->isSalesNotStarted());
            $passesOnly = ($buyableNow->isNotEmpty() ? $buyableNow : $candidates)->every(fn (Ticket $ticket) => $ticket->is_pass);
            $offered = $candidates->filter(fn (Ticket $ticket) => $passesOnly || ! $ticket->is_pass);

            $free = $offered->isEmpty() ? null : $offered->contains(fn (Ticket $ticket) => (float) $ticket->price <= 0);

            $onSale = ! $closed
                && $this->canOfferTickets()
                && ($this->canSellTickets($occurrence)
                    || ($this->allTicketSalesNotStarted() && ! $this->schemaOccurrenceOver($occurrence)));

            if (! $onSale) {
                return [[], $free];
            }

            $houseFull = $this->allTicketsSoldOut($occurrence);

            $offers = $offered->map(function (Ticket $ticket) use ($currency, $url, $houseFull, $occurrence) {
                $offer = [];

                if (($name = SeoUtils::cleanText($ticket->type)) !== '') {
                    $offer['name'] = $name;
                }

                return self::schemaOffer($offer + [
                    'price' => round((float) $ticket->price, 2),
                    'priceCurrency' => $currency,
                    'url' => self::withSchemaQuery($url, 'tickets=true'),
                    'availability' => ($houseFull || (int) $ticket->availableQuantity($occurrence) <= 0)
                        ? self::SCHEMA_SOLD_OUT
                        : self::SCHEMA_IN_STOCK,
                    'validFrom' => $ticket->sales_start_at?->toIso8601String() ?? $this->schemaPublishedAt(),
                    'validThrough' => $ticket->sales_end_at?->toIso8601String(),
                ]);
            })->values()->all();

            return [$offers, $free];
        }

        // registrationHref(), as the badge reads it: a legacy value that is no link shows no price.
        $registrationHref = $this->registrationHref();

        if ($registrationHref && $this->ticket_price !== null && ! $this->tickets_enabled) {
            $price = round((float) $this->ticket_price, 2);

            if ($closed || $this->schemaOccurrenceOver($occurrence)) {
                return [[], $price <= 0];
            }

            return [[self::schemaOffer([
                'price' => $price,
                'priceCurrency' => $currency,
                'url' => self::isHttpUrl($registrationHref) ? $registrationHref : $url,
                'availability' => self::SCHEMA_IN_STOCK,
                'validFrom' => $this->schemaPublishedAt(),
            ])], $price <= 0];
        }

        return [[], null];
    }

    /** An Offer node from $fields, leaving out the ones that have no value. */
    private static function schemaOffer(array $fields): array
    {
        return ['@type' => 'Offer'] + array_filter($fields, fn ($value) => $value !== null && $value !== '');
    }

    /** When the event went public, as an offer's default validFrom. */
    private function schemaPublishedAt(): ?string
    {
        return ($this->published_at ?? $this->created_at)?->toIso8601String();
    }

    /**
     * Whether the occurrence on $date (a schedule-local Y-m-d) is over: its end - two hours after
     * the start when it has no duration, getEndDateTime()'s assumption - is behind us. A date-only
     * event lasts to the end of its last day at the venue.
     */
    private function schemaOccurrenceOver(?string $date): bool
    {
        if (! $this->starts_at) {
            return false;
        }

        $timezone = $this->scheduleTimezone();

        if ($this->hasDateOnlyStart()) {
            return Carbon::parse($this->schemaDay($date), $timezone)
                ->addMinutes(max($this->durationInMinutes(), 24 * 60))
                ->isPast();
        }

        return $this->getEndDateTime($date, true, $timezone)->isPast();
    }

    /** $url with $query appended, after any query it already carries (the page's ?lang=). */
    private static function withSchemaQuery(string $url, string $query): string
    {
        return $url.(str_contains($url, '?') ? '&' : '?').$query;
    }

    private static function isHttpUrl(string $url): bool
    {
        return (bool) preg_match('~^https?://~i', $url) && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * The URL the node names: exactly the page's <link rel="canonical"> - the event's undated URL
     * on its home schedule (canonicalTarget()), plus ?lang= on a page shown in the viewing
     * schedule's second language (Role::langQuerySuffix()). Empty when the event has no routable
     * schedule.
     */
    private function schemaUrl(?Role $viewingRole, string $lang): string
    {
        $canonical = $this->getCanonicalUrl();

        if ($canonical === '') {
            return '';
        }

        return $canonical.($viewingRole ? $viewingRole->langQuerySuffix($lang) : '');
    }

    /** Whether starts_at is a bare date (Y-m-d): an all-day event, already the schedule's calendar day. */
    public function hasDateOnlyStart(): bool
    {
        return strlen((string) $this->starts_at) === 10;
    }

    /**
     * The calendar day a date-only event's occurrence on $date falls on. Untyped like
     * isOccurrenceDate(), which is what vets it.
     */
    private function schemaDay($date): string
    {
        return self::isOccurrenceDate($date) ? $date : substr((string) $this->starts_at, 0, 10);
    }

    /**
     * The start of the occurrence on $date as schema.org wants it.
     *
     * An absolute instant pinned to the schedule's timezone, so a crawler and a signed-in owner
     * emit the same value. A date-only starts_at is a bare Y-m-d: it already IS the schedule's
     * calendar day (saleEventDateFromStartsAt()), and reading it as midnight UTC before converting
     * put an all-day event on the previous day everywhere west of Greenwich.
     */
    public function getSchemaStartDate($date = null)
    {
        if ($this->hasDateOnlyStart()) {
            return $this->schemaDay($date);
        }

        return $this->getStartDateTime($date, true, $this->scheduleTimezone())->toIso8601String();
    }

    /**
     * The end of that occurrence, or null when the event has no duration: getEndDateTime() assumes
     * two hours for those, a guess the page never shows. A date-only event ends on the last
     * calendar day it covers.
     */
    public function getSchemaEndDate($date = null): ?string
    {
        if (! ($this->duration > 0)) {
            return null;
        }

        if ($this->hasDateOnlyStart()) {
            return Carbon::parse($this->schemaDay($date))
                ->addMinutes(max(0, $this->durationInMinutes() - 1))
                ->format('Y-m-d');
        }

        return $this->getEndDateTime($date, true, $this->scheduleTimezone())->toIso8601String();
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

    /**
     * The event's active Meta Ads campaign, if any.
     *
     * Scoped to channel='meta' deliberately. This relation gates two things that talk to
     * Facebook: the Meta Pixel in app-guest.blade.php, and StripeController::sendMetaConversion(),
     * which POSTs the buyer's hashed email to Meta's Conversions API. An on-network promotion
     * has nothing to do with Meta, so without this filter promoting an event on this platform
     * would silently start sending its purchase data to Facebook.
     */
    public function activeBoostCampaign()
    {
        return $this->hasOne(BoostCampaign::class)
            ->where('channel', 'meta')
            ->where('status', 'active')
            ->latest();
    }

    /**
     * The event's active on-network promotion, if any.
     */
    public function activeNetworkPromotion()
    {
        return $this->hasOne(BoostCampaign::class)
            ->where('channel', 'network')
            ->where('status', 'active')
            ->latest();
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
