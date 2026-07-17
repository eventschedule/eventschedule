<?php

namespace App\Repos;

use App\Jobs\NotifyEventChange;
use App\Jobs\SendQueuedEmail;
use App\Jobs\SendQueuedSms;
use App\Jobs\SyncEventToGoogleCalendar;
use App\Mail\ClaimRole;
use App\Mail\ClaimVenue;
use App\Models\Event;
use App\Models\EventPart;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EventChangeNotifier;
use App\Services\SmsService;
use App\Services\TicketVolumeDiscount;
use App\Services\WebhookService;
use App\Utils\ColorUtils;
use App\Utils\GeminiUtils;
use App\Utils\ImageUtils;
use App\Utils\SlugPatternUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventRepo
{
    /** Number of attendees notified by the most recent saveEvent() call (drives the AP toast); null if none. */
    public ?int $lastNotifiedCount = null;

    /**
     * Resolve the default category id to apply to a new event on this schedule.
     * Returns the role's default_category_id only if it's still in the schedule's
     * effective enabled list; otherwise null. Use this everywhere we auto-tag
     * imports/new-events with the schedule default.
     */
    public static function resolveDefaultCategoryId(?Role $role): ?int
    {
        if (! $role || ! $role->default_category_id) {
            return null;
        }
        $enabledIds = collect($role->getEventCategories())->pluck('id')->all();

        return in_array((int) $role->default_category_id, $enabledIds, true)
            ? (int) $role->default_category_id
            : null;
    }

    /**
     * Serialize an event into the `cloned_event` session payload shape consumed by
     * EventController::create(). Shared by the Clone action and Event Templates so
     * the two can't drift. The caller decides where the payload goes (session for a
     * one-off clone, a persisted EventTemplate row for a template).
     */
    public static function buildClonePayload(Event $event): array
    {
        $event->loadMissing(['tickets', 'addons', 'roles', 'curators', 'parts']);

        // Copy fillable fields (skip id/slug)
        $clonedEventData = [];
        foreach ($event->getFillable() as $field) {
            if (! in_array($field, ['id', 'slug'])) {
                $clonedEventData[$field] = $event->$field;
            }
        }

        // Recurrence columns are NOT in $fillable, so the getFillable() loop above
        // skips them - without this the day-of-week pattern and date exceptions would
        // be silently dropped (the form keys recurrence off $event->days_of_week).
        $clonedEventData['days_of_week'] = $event->days_of_week;
        $clonedEventData['recurring_include_dates'] = $event->recurring_include_dates;
        $clonedEventData['recurring_exclude_dates'] = $event->recurring_exclude_dates;

        // Reset fields that shouldn't be carried to a new event
        $clonedEventData['flyer_image_url'] = null;
        $clonedEventData['sponsor_logos'] = null;
        $clonedEventData['rsvp_sold'] = 0;

        // Capture the source flyer's raw filename (not the accessor URL) so it can be
        // copied to a new physical file when the new event is saved. Demo flyers live
        // in public/images/demo/ (not in Storage), so they're skipped.
        $rawFlyer = $event->getAttributes()['flyer_image_url'] ?? null;
        $clonedFlyer = ($rawFlyer && ! str_starts_with($rawFlyer, 'demo_')) ? $rawFlyer : null;

        // Clone tickets (reset sold quantities; field list lives in Ticket::toClonePayload)
        $clonedTickets = [];
        foreach ($event->tickets as $ticket) {
            $clonedTickets[] = $ticket->toClonePayload();
        }
        if (empty($clonedTickets)) {
            // Represent "no tickets" as a single empty-fields array (rehydrated into a
            // blank Ticket by create()) - never a model instance, so it round-trips as JSON.
            $clonedTickets = [[]];
        }

        // Clone add-ons (reset sold quantities)
        $clonedAddons = [];
        foreach ($event->addons as $addon) {
            $clonedAddons[] = [
                'type' => $addon->type,
                'quantity' => $addon->quantity,
                'price' => $addon->price,
                'description' => $addon->description,
            ];
        }

        // Prepare venue and members
        $venue = $event->venue;
        $selectedMembers = [];
        foreach ($event->roles as $each) {
            if ($each->isTalent()) {
                $selectedMembers[] = $each->toData();
            }
        }

        // Prepare curator data
        $curatorIds = [];
        $curatorGroups = [];
        foreach ($event->curators as $curator) {
            $curatorId = UrlUtils::encodeId($curator->id);
            $curatorIds[] = $curatorId;
            $groupId = $event->getGroupIdForSubdomain($curator->subdomain);
            if ($groupId) {
                $curatorGroups[$curatorId] = UrlUtils::encodeId($groupId);
            }
        }

        // Clone event parts
        $clonedParts = [];
        foreach ($event->parts as $part) {
            $clonedParts[] = [
                'name' => $part->name,
                'description' => $part->description,
                'start_time' => $part->start_time,
                'end_time' => $part->end_time,
            ];
        }

        return [
            'event' => $clonedEventData,
            'tickets' => $clonedTickets,
            'addons' => $clonedAddons,
            'venue_id' => $venue ? UrlUtils::encodeId($venue->id) : null,
            'selected_members' => $selectedMembers,
            'curators' => $curatorIds,
            'curator_groups' => $curatorGroups,
            'parts' => $clonedParts,
            'flyer_image_filename' => $clonedFlyer,
        ];
    }

    /**
     * Get UTC date range for a date in a given timezone
     */
    private function getUtcDateRange(Carbon $date): array
    {
        return [
            $date->copy()->startOfDay()->utc(),
            $date->copy()->endOfDay()->utc(),
        ];
    }

    /**
     * Find event attached to both roles on a specific date
     */
    private function findEventForBothRoles(Role $subdomainRole, Role $slugRole, Carbon $eventDate): ?Event
    {
        [$startOfDay, $endOfDay] = $this->getUtcDateRange($eventDate);

        return Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
            ->whereHas('roles', fn ($q) => $q->where('role_id', $subdomainRole->id)->where('is_accepted', true))
            ->whereHas('roles', fn ($q) => $q->where('role_id', $slugRole->id)->where('is_accepted', true))
            ->where('is_draft', false)
            ->where(function ($query) use ($startOfDay, $endOfDay, $eventDate) {
                $query->whereBetween('starts_at', [$startOfDay, $endOfDay])
                    ->orWhere(function ($query) use ($eventDate, $endOfDay) {
                        $query->whereNotNull('days_of_week')
                            ->whereRaw("SUBSTRING(days_of_week, ?, 1) = '1'", [$eventDate->dayOfWeek + 1])
                            ->where('starts_at', '<=', $endOfDay);
                    })
                    ->orWhere(function ($query) use ($startOfDay, $endOfDay) {
                        $query->where('duration', '>=', 24)
                            ->where('starts_at', '<', $endOfDay)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$startOfDay]);
                    });
            })
            ->orderBy('starts_at')
            ->first();
    }

    /**
     * Find event by slug on a specific date
     */
    private function findEventBySlug(string $subdomain, string $slug, Carbon $eventDate): ?Event
    {
        [$startOfDay, $endOfDay] = $this->getUtcDateRange($eventDate);

        return Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
            ->where('slug', $slug)
            ->where('is_draft', false)
            ->where(function ($query) use ($startOfDay, $endOfDay, $eventDate) {
                $query->whereBetween('starts_at', [$startOfDay, $endOfDay])
                    ->orWhere(function ($query) use ($eventDate) {
                        $query->whereNotNull('days_of_week')
                            ->whereRaw("SUBSTRING(days_of_week, ?, 1) = '1'", [$eventDate->dayOfWeek + 1]);
                    })
                    ->orWhere(function ($query) use ($startOfDay, $endOfDay) {
                        $query->where('duration', '>=', 24)
                            ->where('starts_at', '<', $endOfDay)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$startOfDay]);
                    });
            })
            ->where(function ($query) use ($subdomain) {
                $query->whereHas('roles', function ($q) use ($subdomain) {
                    $q->where('subdomain', $subdomain)->where('is_accepted', true);
                });
            })
            ->orderBy('starts_at')
            ->first();
    }

    /**
     * Find event by subdomain on a specific date (final fallback)
     */
    private function findEventBySubdomain(string $subdomain, Carbon $eventDate): ?Event
    {
        [$startOfDay, $endOfDay] = $this->getUtcDateRange($eventDate);

        return Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
            ->where('is_draft', false)
            ->where(function ($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('starts_at', [$startOfDay, $endOfDay])
                    ->orWhere(function ($query) use ($startOfDay, $endOfDay) {
                        $query->where('duration', '>=', 24)
                            ->where('starts_at', '<', $endOfDay)
                            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [$startOfDay]);
                    });
            })
            ->where(function ($query) use ($subdomain) {
                $query->whereHas('roles', function ($q) use ($subdomain) {
                    $q->where('subdomain', $subdomain)->where('is_accepted', true);
                });
            })
            ->first();
    }

    /**
     * Reject pass tickets that are configured but missing required fields, before
     * any DB writes - otherwise a misconfigured pass saves silently (e.g. a "visit
     * pass" with no limit behaves as unlimited; a sub-schedule / specific-events
     * pass with nothing selected covers no events and never redeems).
     */
    private function validatePassConfiguration($request): void
    {
        $usageTypes = ['per_occurrence', 'total', 'per_event', 'unlimited'];
        $scopes = ['this_event', 'all_events', 'sub_schedule', 'specific_events'];

        foreach ((array) $request->input('tickets', []) as $index => $data) {
            if (empty($data['is_pass'])) {
                continue;
            }

            $usageType = $data['pass_usage_type'] ?? 'per_occurrence';
            $scope = $data['pass_scope'] ?? 'this_event';

            if (! in_array($usageType, $usageTypes, true) || ! in_array($scope, $scopes, true)) {
                throw ValidationException::withMessages(["tickets.{$index}.pass" => __('messages.error')]);
            }

            // A season pass is always scoped to its own event (mirrors the
            // normalization below), so the coverage selectors don't apply.
            if ($usageType === 'per_occurrence') {
                $scope = 'this_event';
            }

            if ($usageType === 'total' && empty($data['pass_max_uses'])) {
                throw ValidationException::withMessages(["tickets.{$index}.pass_max_uses" => __('messages.pass_max_uses_required')]);
            }

            // '' = no deadline; 0 is a valid cutoff (credited until the event starts).
            if (isset($data['pass_cancel_cutoff_hours']) && $data['pass_cancel_cutoff_hours'] !== ''
                && (! is_numeric($data['pass_cancel_cutoff_hours']) || (int) $data['pass_cancel_cutoff_hours'] < 0)) {
                throw ValidationException::withMessages(["tickets.{$index}.pass_cancel_cutoff_hours" => __('messages.error')]);
            }

            if (isset($data['pass_late_cancel_policy'])
                && ! in_array($data['pass_late_cancel_policy'], ['forfeit', 'block'], true)) {
                throw ValidationException::withMessages(["tickets.{$index}.pass_late_cancel_policy" => __('messages.error')]);
            }

            if ($scope === 'sub_schedule' && empty($data['pass_scope_group_id'])) {
                throw ValidationException::withMessages(["tickets.{$index}.pass_scope_group_id" => __('messages.select_sub_schedule')]);
            }

            if ($scope === 'specific_events') {
                $raw = $data['pass_event_ids'] ?? null;
                $ids = is_string($raw) ? json_decode($raw, true) : $raw;
                $ids = is_array($ids) ? array_filter($ids, fn ($id) => $id !== null && $id !== '') : [];
                if (empty($ids)) {
                    throw ValidationException::withMessages(["tickets.{$index}.pass_event_ids" => __('messages.pass_no_events_warning')]);
                }
            }
        }
    }

    public function saveEvent($currentRole, $request, $event = null, $followNewRoles = true, ?string $timezoneOverride = null)
    {
        $this->validatePassConfiguration($request);

        $user = $request->user();
        $venue = null;

        // The timezone the entered wall-clock is anchored to, and the timezone any schedule created
        // along the way inherits. Normally the schedule being saved onto, but guest submissions save
        // onto the submitter's own talent schedule while the time they typed is the curator's local
        // time, so that caller overrides this with the curator's timezone.
        $captureTimezone = $timezoneOverride ?? $currentRole?->timezone;

        // Set creator_role_id to the current role
        $creatorRoleId = $currentRole ? $currentRole->id : null;

        if ($request->venue_id) {
            $venue = Role::where('is_deleted', false)->findOrFail(UrlUtils::decodeId($request->venue_id));
        }

        if (! $user) {
            $user = $currentRole->user;
        }

        if ($request->venue_name || $request->venue_address1 || $request->venue_address2 || $request->venue_city || $request->venue_state || $request->venue_postal_code || $request->venue_email || $request->venue_phone || $request->venue_website) {
            // Safety-net dedup: if no venue was selected, try a normalized lookup
            // before creating a new record. Catches dups regardless of how the
            // request arrived (AI import, manual create, guest import).
            $matchedExisting = false;
            if (! $venue && $request->venue_name) {
                $normName = GeminiUtils::normalizeForMatch($request->venue_name);
                $normCity = GeminiUtils::normalizeForMatch($request->venue_city);
                $countryCode = $request->venue_country_code
                    ? strtolower($request->venue_country_code)
                    : ($currentRole && $currentRole->country_code ? strtolower($currentRole->country_code) : null);

                if ($normName !== '') {
                    $venue = Role::where('type', 'venue')
                        ->where('is_deleted', false)
                        ->where('name_normalized', $normName)
                        ->when($normCity !== '', function ($q) use ($normCity) {
                            $q->where('city_normalized', $normCity);
                        })
                        ->when($countryCode, function ($q) use ($countryCode) {
                            $q->where('country_code', $countryCode);
                        })
                        ->withCount('events')
                        ->orderByRaw('CASE WHEN email IS NOT NULL THEN 0 ELSE 1 END')
                        ->orderBy('events_count', 'desc')
                        ->orderBy('id', 'asc')
                        ->first();

                    if ($venue) {
                        $matchedExisting = true;
                    }
                }
            }

            if (! $venue) {
                $venue = new Role;
                $venue->name = $request->venue_name ?? null;
                $venue->name_en = $request->venue_name_en ?? null;
                $venue->email = $request->venue_email ?? null;
                $venue->phone = $request->venue_phone ?: null;
                $venue->subdomain = Role::generateSubdomain($request->venue_name);
                $venue->type = 'venue';
                $venue->name = $request->venue_name ?? null;
                $venue->address1 = $request->venue_address1;
                $venue->address2 = $request->venue_address2;
                $venue->city = $request->venue_city;
                $venue->state = $request->venue_state;
                $venue->postal_code = $request->venue_postal_code;
                $countryCode = $request->venue_country_code ? $request->venue_country_code : $currentRole->country_code;
                $venue->country_code = $countryCode ? strtolower($countryCode) : null;
                $venue->language_code = $request->venue_language_code ? $request->venue_language_code : $currentRole->language_code;
                $venue->timezone = $captureTimezone;
                $venue->website = $request->venue_website;
                $venue->background_colors = ColorUtils::randomGradient();
                $venue->background_rotation = rand(0, 359);
                $venue->font_color = '#ffffff';
                $venue->save();
                $venue->refresh();

                $matchingUser = false;

                if ($request->boolean('claim_venue_ownership') && $request->user()) {
                    // Authenticated user explicitly claimed ownership via the AI import checkbox.
                    // Wins over email/phone auto-match below. Gated on $request->user() (not the
                    // schedule-owner fallback) so a crafted guest request can't grant ownership.
                    // Overwrite the AI-parsed venue contact with the auth user's own verified
                    // contact so isClaimed() returns true and the editability gate locks out
                    // unrelated followers.
                    $authUser = $request->user();
                    $matchingUser = $authUser;
                    $venue->user_id = $authUser->id;
                    if ($authUser->email) {
                        $venue->email = $authUser->email;
                        $venue->email_verified_at = $authUser->email_verified_at;
                    }
                    if ($authUser->phone) {
                        $venue->phone = $authUser->phone;
                        $venue->phone_verified_at = $authUser->phone_verified_at;
                    }
                    // Save quietly: the auth user's contact was copied verbatim (already
                    // lowercased/normalized on the User), so bypass the Role::updating hook
                    // that would otherwise null the just-copied email_verified_at /
                    // phone_verified_at and send a redundant verification email because the
                    // email/phone became dirty - defeating the claim.
                    $venue->saveQuietly();

                    $authUser->roles()->attach($venue->id, ['level' => 'owner', 'created_at' => now()]);

                    if (! $authUser->default_role_id) {
                        $authUser->default_role_id = $venue->id;
                        $authUser->save();
                    }
                } elseif ($venue->email && $matchingUser = User::whereEmail($venue->email)->first()) {
                    $venue->user_id = $matchingUser->id;
                    $venue->email_verified_at = $matchingUser->email_verified_at;
                    if ($venue->phone && $matchingUser->phone === $venue->phone) {
                        $venue->phone_verified_at = $matchingUser->phone_verified_at;
                    }
                    $venue->save();

                    $matchingUser->roles()->attach($venue->id, ['level' => 'owner', 'created_at' => now()]);

                    if (! $matchingUser->default_role_id) {
                        $matchingUser->default_role_id = $venue->id;
                        $matchingUser->save();
                    }
                } elseif ($venue->phone && $matchingUser = User::where('phone', $venue->phone)->whereNotNull('phone_verified_at')->first()) {
                    $venue->user_id = $matchingUser->id;
                    $venue->phone_verified_at = $matchingUser->phone_verified_at;
                    $venue->save();

                    $matchingUser->roles()->attach($venue->id, ['level' => 'owner', 'created_at' => now()]);

                    if (! $matchingUser->default_role_id) {
                        $matchingUser->default_role_id = $venue->id;
                        $matchingUser->save();
                    }
                }

                if ($followNewRoles && (! $matchingUser || $matchingUser->id != $user->id)) {
                    $user->roles()->attach($venue->id, ['level' => 'follower', 'created_at' => now()]);
                }
            } elseif ($matchedExisting) {
                // Venue was found via the normalized safety-net lookup (not via
                // a user-picked venue_id). Attach as follower so it appears in
                // the user's dropdown for future imports.
                if ($followNewRoles) {
                    $alreadyFollows = $user->roles()->where('roles.id', $venue->id)->exists();
                    if (! $alreadyFollows) {
                        $user->roles()->attach($venue->id, ['level' => 'follower', 'created_at' => now()]);
                    }
                }
            } elseif (! $venue->user_id) {
                // Venue was explicitly selected via venue_id and has no owner: persist the edits the
                // event edit form allows for unclaimed venues. The interactive form pre-loads each
                // field from the selected venue, so when it sets venue_details_editable (only while
                // the address fields are shown) a blank is an intentional clear -> has(). Programmatic
                // callers (API, the import preview, WhatsApp/curator AI imports) never set the flag and
                // may send partial/blank fields parsed from a source that merely matched this venue via
                // venue_id, so they keep filled() and a blank never wipes shared venue data. venue_name
                // (shared identity) and venue_country_code (the country selector cannot be cleared via
                // the UI) always stay on filled(). Unchanged values stay non-dirty (a true no-op, no
                // verification email) via Eloquent dirty checking.
                $clearBlanks = $request->boolean('venue_details_editable');
                $shouldSet = fn ($field) => $clearBlanks ? $request->has($field) : $request->filled($field);

                if ($request->filled('venue_name')) {
                    $venue->name = $request->venue_name;
                }
                if ($shouldSet('venue_email')) {
                    $venue->email = $request->venue_email;
                }
                if ($shouldSet('venue_phone')) {
                    $venue->phone = $request->venue_phone;
                }
                if ($shouldSet('venue_website')) {
                    $venue->website = $request->venue_website;
                }
                if ($shouldSet('venue_address1')) {
                    $venue->address1 = $request->venue_address1;
                }
                if ($shouldSet('venue_city')) {
                    $venue->city = $request->venue_city;
                }
                if ($shouldSet('venue_state')) {
                    $venue->state = $request->venue_state;
                }
                if ($shouldSet('venue_postal_code')) {
                    $venue->postal_code = $request->venue_postal_code;
                }
                if ($request->filled('venue_country_code')) {
                    $venue->country_code = strtolower($request->venue_country_code);
                }
                $venue->save();
            }
        }

        $roles = [];
        $roleIds = [];

        if ($request->members) {
            foreach ($request->members as $memberId => $member) {
                if (! $memberId || strpos($memberId, 'new_') === 0) {
                    $role = new Role;
                    $role->name = $member['name'];
                    $role->email = isset($member['email']) && $member['email'] !== '' ? $member['email'] : null;
                    $role->phone = isset($member['phone']) && $member['phone'] !== '' ? $member['phone'] : null;
                    $role->subdomain = Role::generateSubdomain($member['name']);
                    $role->type = $request->role_type ? $request->role_type : 'talent';
                    $role->timezone = $captureTimezone;
                    $role->language_code = $request->language_code ? $request->language_code : $currentRole->language_code;
                    $countryCode = $request->country_code ? $request->country_code : $currentRole->country_code;
                    $role->country_code = $countryCode ? strtolower($countryCode) : null;
                    $role->background_colors = ColorUtils::randomGradient();
                    $role->background_rotation = rand(0, 359);
                    $role->font_color = '#ffffff';

                    $links = [];
                    if (! empty($member['youtube_url'])) {
                        $urlInfo = UrlUtils::getUrlInfo($member['youtube_url']);
                        if ($urlInfo !== null) {
                            $links[] = $urlInfo;
                        }
                    }
                    if (count($links)) {
                        $role->youtube_links = json_encode($links);
                    }

                    $role->save();
                    $role->refresh();

                    $matchingUser = null;
                    if ($role->email) {
                        $matchingUser = User::whereEmail($role->email)->first();
                    }
                    if (! $matchingUser && $role->phone) {
                        $matchingUser = User::where('phone', $role->phone)->whereNotNull('phone_verified_at')->first();
                    }

                    if ($matchingUser) {
                        $role->user_id = $matchingUser->id;
                        if ($role->email && $matchingUser->email === $role->email) {
                            $role->email_verified_at = $matchingUser->email_verified_at;
                        }
                        if ($role->phone && $matchingUser->phone === $role->phone) {
                            $role->phone_verified_at = $matchingUser->phone_verified_at;
                        }
                        $role->save();
                        $matchingUser->roles()->attach($role->id, ['level' => 'owner', 'created_at' => now()]);

                        if (! $matchingUser->default_role_id) {
                            $matchingUser->default_role_id = $role->id;
                            $matchingUser->save();
                        }
                    }

                    if ($followNewRoles && (! $matchingUser || $matchingUser->id != $user->id)) {
                        $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
                    }
                } else {
                    $roleId = UrlUtils::decodeId($memberId);
                    $role = Role::findOrFail($roleId);

                    if (! $role->isClaimed()) {
                        if (! empty($member['name'])) {
                            $role->name = $member['name'];
                        }

                        if (! empty($member['email'])) {
                            $role->email = $member['email'];
                        }

                        if (! empty($member['phone'])) {
                            $role->phone = $member['phone'];
                        }

                        $links = $role->youtube_links ? json_decode($role->youtube_links, true) : [];

                        if (! empty($member['youtube_url'])) {
                            $urlInfo = UrlUtils::getUrlInfo($member['youtube_url']);
                            if ($urlInfo !== null) {
                                $links = [$urlInfo];
                            }

                            $role->youtube_links = json_encode($links);
                        }

                        $role->save();
                    }
                }

                $roles[] = $role;
                $roleIds[] = $role->id;
            }
        }

        // Ensure current role is included if it's not already in the list
        if ($currentRole && ! in_array($currentRole->id, $roleIds)) {
            $roles[] = $currentRole;
            $roleIds[] = $currentRole->id;
        }

        $venueId = $venue ? $venue->id : null;

        $isNewEvent = ! $event;
        if ($isNewEvent) {
            $event = new Event;
            $event->user_id = $user->id;
            $event->creator_role_id = $creatorRoleId;
        }

        // Decode event-level custom_fields from JSON string
        if ($request->has('custom_fields')) {
            $customFields = json_decode($request->input('custom_fields'), true);

            if (! empty($customFields)) {
                foreach ($customFields as $fieldKey => $fieldData) {
                    if (isset($fieldData['options'])) {
                        $customFields[$fieldKey]['options'] = implode(',', array_map('trim', explode(',', $fieldData['options'])));
                    }
                }
            }

            // Handle name_en for event-level custom fields
            if (! empty($customFields) && $currentRole && $currentRole->language_code !== 'en') {
                $existingCustomFields = $event && $event->custom_fields ? $event->custom_fields : [];
                $fieldsNeedingTranslation = [];

                foreach ($customFields as $fieldKey => $fieldData) {
                    if (empty($fieldData['name'])) {
                        continue;
                    }

                    if (! empty($fieldData['name_en'])) {
                        // User provided a manual English name - already set
                    } else {
                        // Check if name changed or name_en is missing
                        $existingField = $existingCustomFields[$fieldKey] ?? null;
                        $existingName = $existingField['name'] ?? null;
                        $existingNameEn = $existingField['name_en'] ?? null;

                        if ($existingNameEn && $existingName === $fieldData['name']) {
                            // Name unchanged, keep existing translation
                            $customFields[$fieldKey]['name_en'] = $existingNameEn;
                        } else {
                            // Name changed or no translation exists - mark for translation
                            $fieldsNeedingTranslation[$fieldKey] = $fieldData['name'];
                        }
                    }
                }

                // Batch translate field names that need translation
                if (! empty($fieldsNeedingTranslation)) {
                    try {
                        $translations = GeminiUtils::translateCustomFieldNames(
                            array_values($fieldsNeedingTranslation),
                            $currentRole->language_code
                        );

                        foreach ($fieldsNeedingTranslation as $fieldKey => $fieldName) {
                            if (isset($translations[$fieldName])) {
                                $customFields[$fieldKey]['name_en'] = $translations[$fieldName];
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to translate event custom field names: '.$e->getMessage());
                    }
                }
            }

            // De-duplicate indices as a safety net
            if (! empty($customFields)) {
                $usedIndices = [];
                foreach ($customFields as $fieldKey => $fieldData) {
                    $index = $fieldData['index'] ?? null;
                    if ($index && ! in_array($index, $usedIndices)) {
                        $usedIndices[] = $index;
                    } elseif ($index) {
                        // Duplicate index - reassign
                        for ($i = 1; $i <= 10; $i++) {
                            if (! in_array($i, $usedIndices)) {
                                $customFields[$fieldKey]['index'] = $i;
                                $usedIndices[] = $i;
                                break;
                            }
                        }
                    }
                }
            }

            $request->merge([
                'custom_fields' => $customFields,
            ]);
        }

        // Handle custom_field_values (event metadata fields defined at schedule level)
        if ($request->has('custom_field_values')) {
            $customFieldValues = $request->input('custom_field_values', []);
            // Filter out empty values
            $customFieldValues = array_filter($customFieldValues, function ($value) {
                return $value !== null && $value !== '';
            });
            // Validate dropdown and multiselect custom field values against allowed options
            $eventCustomFields = $currentRole->getEventCustomFields();
            foreach ($eventCustomFields as $fieldKey => $fieldConfig) {
                $fieldType = $fieldConfig['type'] ?? '';
                if ($fieldType === 'dropdown' && isset($customFieldValues[$fieldKey])) {
                    $allowedOptions = array_map('trim', explode(',', $fieldConfig['options'] ?? ''));
                    if (! in_array($customFieldValues[$fieldKey], $allowedOptions, true)) {
                        unset($customFieldValues[$fieldKey]);
                    }
                } elseif ($fieldType === 'multiselect' && isset($customFieldValues[$fieldKey])) {
                    $allowedOptions = array_map('trim', explode(',', $fieldConfig['options'] ?? ''));
                    $selectedValues = is_array($customFieldValues[$fieldKey])
                        ? array_map('trim', $customFieldValues[$fieldKey])
                        : array_map('trim', explode(',', $customFieldValues[$fieldKey]));
                    $validValues = array_filter($selectedValues, function ($v) use ($allowedOptions) {
                        return in_array($v, $allowedOptions, true);
                    });
                    $customFieldValues[$fieldKey] = ! empty($validValues) ? implode(', ', $validValues) : null;
                }
            }
            $request->merge([
                'custom_field_values' => ! empty($customFieldValues) ? $customFieldValues : null,
            ]);
        }

        $event->fill($request->all());

        // NOT NULL boolean flags are submitted as Vue-bound hidden inputs (`:value="..."`),
        // so a client that doesn't run the Vue bundle (old browser, JS disabled, bot) submits
        // them empty; ConvertEmptyStringsToNull turns "" into null, which violates the columns'
        // NOT NULL constraint on insert. Re-coerce any that were actually submitted back to a
        // concrete boolean. Guarding on has() leaves an omitted flag at its model/DB default
        // (false), so a partial update is never silently reset.
        foreach ([
            'tickets_enabled', 'rsvp_enabled',
            'ask_phone', 'require_phone', 'country_code_phone',
            'individual_tickets', 'individual_ticket_fields',
            'sell_after_start', 'show_unavailable_tickets',
            'is_draft', 'is_private', 'is_internal',
        ] as $boolField) {
            if ($request->has($boolField)) {
                $event->$boolField = $request->boolean($boolField);
            }
        }

        if ($isNewEvent && ! $event->category_id && $currentRole) {
            $defaultId = self::resolveDefaultCategoryId($currentRole);
            if ($defaultId) {
                $event->category_id = $defaultId;
            }
        }

        // Internal reuses the draft gate to hide from every public surface, and is mutually
        // exclusive with the unlisted/private state. Run this BEFORE the Enterprise gate below so a
        // non-Enterprise request for internal is first normalized to a hidden Draft (is_draft=true);
        // the gate then strips the Enterprise-only is_internal, leaving the event safely hidden
        // rather than flipping it public.
        if ($event->is_internal) {
            $event->is_draft = true;
            $event->is_private = false;
            $event->event_password = null;
        }

        if ($currentRole && ! $currentRole->isEnterprise()) {
            // Losing Enterprise strips the Enterprise-only is_private/is_internal states. An event that
            // was Unlisted must NOT silently become fully Public on the next edit - keep it hidden as a
            // Draft instead (mirrors how Internal degrades to Draft in the block above).
            if ($event->is_private) {
                $event->is_draft = true;
            }
            $event->is_private = false;
            $event->is_internal = false;
            $event->event_password = null;
        }

        // A password only applies to an Unlisted (is_private) event. Never leave a stale password on
        // a public/draft/internal event - isPasswordProtected() gates the guest page independently of
        // is_private, so a leftover password would keep a "published" event password-locked.
        if (! $event->is_private) {
            $event->event_password = null;
        }

        // Convert starts_at from the schedule's local time to UTC before slug generation.
        // Anchor to the schedule's timezone (not the editing user's personal account timezone):
        // an event belongs to a schedule, so an organizer whose account timezone differs from the
        // schedule's must not silently shift the saved time. Record the timezone used so the stored
        // UTC instant is self-describing and off-timezone events are detectable later.
        if ($event->starts_at) {
            $timezone = $captureTimezone ?? $venue?->timezone ?? $user?->timezone ?? config('app.timezone');
            $event->starts_at = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, $timezone)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
            $event->timezone = $timezone;
        } else {
            // No time means no meaningful capture timezone; never persist a client-supplied value
            // ('timezone' is fillable for backup round-tripping).
            $event->timezone = null;
        }

        // Handle slug update for existing events
        if (! $isNewEvent) {
            if ($request->filled('slug')) {
                $event->slug = Str::slug($request->slug) ?: $event->getOriginal('slug');
            } elseif ($currentRole?->slug_pattern
                && self::slugPatternFieldsChanged($currentRole->slug_pattern, $event)) {
                $event->slug = SlugPatternUtils::generateSlug(
                    $currentRole->slug_pattern,
                    $request->short_name ?: $request->name,
                    $request->short_name_en ?: $request->name_en,
                    $event,
                    $currentRole,
                    $venue
                );
            } else {
                $event->slug = $event->getOriginal('slug');
            }
        }

        // Generate slug after event data is populated (needs starts_at for date variables)
        if ($isNewEvent) {
            $event->slug = SlugPatternUtils::generateSlug(
                $currentRole?->slug_pattern,
                $request->short_name ?: $request->name,
                $request->short_name_en ?: $request->name_en,
                $event,
                $currentRole,
                $venue  // Pass venue directly since relationship isn't loaded yet
            );
        }

        // Handle recurring frequency and days_of_week
        if (request()->schedule_type == 'recurring') {
            $frequency = $request->input('recurring_frequency', 'weekly');
            $event->recurring_frequency = $frequency;

            if ($frequency === 'every_n_weeks') {
                $event->recurring_interval = max(2, (int) $request->input('recurring_interval', 2));
            } else {
                $event->recurring_interval = null;
            }

            if (in_array($frequency, ['weekly', 'every_n_weeks'])) {
                // Build days_of_week from checkboxes
                $days_of_week = '';
                $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
                foreach ($days as $index => $day) {
                    $days_of_week .= request()->has('days_of_week_'.$index) ? '1' : '0';
                }
                $event->days_of_week = $days_of_week;
            } else {
                // daily, monthly_date, monthly_weekday, yearly - set all days for query compatibility
                $event->days_of_week = '1111111';
            }
        } else {
            $event->days_of_week = null;
            $event->recurring_frequency = null;
            $event->recurring_interval = null;
        }

        // Handle recurring end configuration (only for recurring events)
        if (request()->schedule_type == 'recurring') {
            $event->recurring_end_type = $request->input('recurring_end_type', 'never');
            $event->recurring_end_value = $request->input('recurring_end_value');

            // Validate and clean up recurring_end_value based on type
            if ($event->recurring_end_type === 'never') {
                $event->recurring_end_value = null;
            } elseif ($event->recurring_end_type === 'on_date') {
                // Ensure it's a valid date format (YYYY-MM-DD)
                if ($event->recurring_end_value && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $event->recurring_end_value)) {
                    $event->recurring_end_value = null;
                }
            } elseif ($event->recurring_end_type === 'after_events') {
                // Ensure it's a positive integer
                $event->recurring_end_value = $event->recurring_end_value && is_numeric($event->recurring_end_value) && (int) $event->recurring_end_value > 0
                    ? (string) (int) $event->recurring_end_value
                    : null;
            }
        } else {
            // Clear recurring end fields for non-recurring events
            $event->recurring_end_type = 'never';
            $event->recurring_end_value = null;
        }

        // Handle recurring include/exclude dates
        if (request()->schedule_type == 'recurring') {
            $includeDates = array_filter(
                array_unique($request->input('recurring_include_dates', [])),
                fn ($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)
            );
            sort($includeDates);
            $event->recurring_include_dates = ! empty($includeDates) ? array_values($includeDates) : null;

            $excludeDates = array_filter(
                array_unique($request->input('recurring_exclude_dates', [])),
                fn ($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)
            );
            sort($excludeDates);
            $event->recurring_exclude_dates = ! empty($excludeDates) ? array_values($excludeDates) : null;
        } else {
            $event->recurring_include_dates = null;
            $event->recurring_exclude_dates = null;
        }

        /*
        if (auth()->user()->isMember($venue->subdomain) || !$venue->user_id) {
            $event->is_accepted = true;
            $message = __('messages.event_created');
        } else {
            //$subdomain = $role->subdomain;
            $message = __('messages.event_requested');

            $emails = $venue->members()->pluck('email');
            //Notification::route('mail', $emails)->notify(new EventRequestNotification($venue, $role));
        }
        */

        // Handle nullable feedback_enabled (empty string = null = use schedule default)
        if ($currentRole && $currentRole->isPro() && $request->has('feedback_enabled')) {
            $val = $request->input('feedback_enabled');
            $event->feedback_enabled = $val === '' || $val === null ? null : (bool) $val;
        }

        // Handle nullable fan content fields (empty string = null = use schedule default)
        foreach (['fan_comments_enabled', 'fan_photos_enabled', 'fan_videos_enabled'] as $fanField) {
            if ($request->has($fanField)) {
                $val = $request->input($fanField);
                $event->$fanField = $val === '' || $val === null ? null : (bool) $val;
            }
        }

        // Handle event sponsor logos (Pro feature)
        if ($currentRole && $currentRole->isPro() && ! is_demo_mode()) {
            $sponsorMode = $request->input('sponsor_mode', 'default');
            $event->sponsor_mode = in_array($sponsorMode, ['default', 'none', 'custom']) ? $sponsorMode : 'default';

            if ($sponsorMode === 'custom') {
                $oldSponsors = json_decode($event->getOriginal('sponsor_logos') ?? '[]', true) ?: [];
                $oldLogoFiles = array_filter(array_column($oldSponsors, 'logo'));

                // Process existing sponsors (reordered via drag-and-drop)
                $existingSponsorsJson = $request->input('existing_event_sponsors', '[]');
                $sponsors = json_decode($existingSponsorsJson, true) ?: [];

                // Process new sponsor uploads
                $newFiles = $request->file('event_sponsor_logos', []);
                $newNames = $request->input('event_sponsor_names', []);
                $newUrls = $request->input('event_sponsor_urls', []);
                $newTiers = $request->input('event_sponsor_tiers', []);

                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                foreach ($newFiles as $index => $file) {
                    if (count($sponsors) >= 12) {
                        break;
                    }

                    $extension = strtolower($file->getClientOriginalExtension());
                    if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                        continue;
                    }

                    $filename = strtolower('sponsor_'.Str::random(32).'.'.$extension);
                    $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

                    $sponsors[] = [
                        'name' => $newNames[$index] ?? '',
                        'logo' => $filename,
                        'url' => ! empty($newUrls[$index]) ? $newUrls[$index] : null,
                        'tier' => $newTiers[$index] ?? '',
                    ];
                }

                // Cap at 12
                $sponsors = array_slice($sponsors, 0, 12);

                // Delete orphaned logo files
                $currentLogoFiles = array_filter(array_column($sponsors, 'logo'));
                $orphanedFiles = array_diff($oldLogoFiles, $currentLogoFiles);
                foreach ($orphanedFiles as $orphanedFile) {
                    if (str_starts_with($orphanedFile, 'demo_')) {
                        continue;
                    }
                    $path = $orphanedFile;
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }

                $event->sponsor_logos = ! empty($sponsors) ? json_encode(array_values($sponsors)) : null;
            } else {
                // Switching away from custom: clean up old logo files
                $oldSponsors = json_decode($event->getOriginal('sponsor_logos') ?? '[]', true) ?: [];
                foreach ($oldSponsors as $sponsor) {
                    if (! empty($sponsor['logo']) && ! str_starts_with($sponsor['logo'], 'demo_')) {
                        $path = $sponsor['logo'];
                        if (config('filesystems.default') == 'local') {
                            $path = 'public/'.$path;
                        }
                        Storage::delete($path);
                    }
                }
                $event->sponsor_logos = null;
            }
        } else {
            $event->sponsor_mode = null;
            $event->sponsor_logos = null;
        }

        // Capture draft transition before save() resets dirty tracking
        $wasDraftBeforeSave = $event->isDirty('is_draft') && $event->getOriginal('is_draft');
        $wasJustUnpublished = $event->isDirty('is_draft') && $event->getOriginal('is_draft') === false && $event->is_draft;

        // Snapshot OLD values for attendee change notifications + the iCal sequence bump (issue #94).
        // Captured for any existing, non-cancelled event update so a material change still advances the
        // iCal SEQUENCE even when the organizer chooses not to notify; the notification itself is
        // dispatched below only when notify_attendees was set. The venue is read here, before
        // roles()->sync() runs below, so $event->venue is still the OLD venue.
        $notifyRequested = $request->boolean('notify_attendees');
        $notifyOld = null;
        if (! $isNewEvent && ! $event->is_cancelled) {
            $oldVenue = $event->venue;
            $notifyOld = [
                'starts_at' => $event->getOriginal('starts_at'),
                'duration' => $event->getOriginal('duration'),
                'timezone' => $event->getOriginal('timezone'),
                'days_of_week' => $event->getOriginal('days_of_week'),
                'event_url' => $event->getOriginal('event_url'),
                'venue_id' => $oldVenue?->id,
                'venue_name' => $oldVenue?->getDisplayName(),
            ];
        }

        $event->save();

        if ($venue) {
            $roles[] = $venue;
            $roleIds[] = $venue->id;
        }

        $selectedCurators = $request->input('curators', []);
        $selectedCurators = array_map(function ($id) {
            return UrlUtils::decodeId($id);
        }, $selectedCurators);

        $existingAttachedIds = ($event && $event->exists)
            ? $event->roles()->pluck('roles.id')->toArray()
            : [];

        $availableSchedules = $user->availableEventSchedules();
        $userVisibleIds = $availableSchedules->pluck('id')->toArray();

        // Roles owned by the dedicated venue field / members section. Empty for a brand-new
        // event; populated from the existing attachments just below.
        $previousVenueIds = [];
        $previousTalentIds = [];

        // The schedules tab pre-checks every attached schedule (including the
        // venue/talent on the event), but the dedicated venue field and members
        // section may have been changed by the user without touching the tab.
        // Treat those stale pre-checks as overridden by the dedicated sections.
        if ($event && $event->exists) {
            $previousVenueIds = $event->roles()
                ->where('roles.type', 'venue')
                ->pluck('roles.id')
                ->toArray();
            foreach ($previousVenueIds as $oldVenueId) {
                if ($venue && $venue->id === $oldVenueId) {
                    continue;
                }
                $selectedCurators = array_values(array_diff($selectedCurators, [$oldVenueId]));
            }

            $submittedMemberRoleIds = [];
            foreach ((array) ($request->members ?? []) as $memberId => $member) {
                if (! $memberId || strpos($memberId, 'new_') === 0) {
                    continue;
                }
                $submittedMemberRoleIds[] = UrlUtils::decodeId($memberId);
            }
            $previousTalentIds = $event->roles()
                ->where('roles.type', 'talent')
                ->pluck('roles.id')
                ->toArray();
            foreach ($previousTalentIds as $oldTalentId) {
                if (in_array($oldTalentId, $submittedMemberRoleIds)) {
                    continue;
                }
                $selectedCurators = array_values(array_diff($selectedCurators, [$oldTalentId]));
            }
        }

        // Only the interactive event form submits these flags; the API and importers never do, so
        // they fall through to the preservation below and never wipe attachments they did not
        // manage. When the form's participants section / venue field IS submitted, a previously-
        // attached talent/venue that is absent from this request was removed there on purpose.
        $membersSubmitted = $request->boolean('members_submitted');
        $venueSubmitted = $request->boolean('venue_submitted');

        // Preserve attachments the user has no visibility into. Anything visible
        // in the schedules tab is fully managed by this submission.
        foreach ($existingAttachedIds as $attachedId) {
            if (in_array($attachedId, $userVisibleIds)) {
                continue;
            }
            if (in_array($attachedId, $roleIds)) {
                continue;
            }
            if (in_array($attachedId, $selectedCurators)) {
                continue;
            }
            // Talents/venue the user removed in their dedicated form section (only when that
            // section was actually submitted) must not be resurrected here; otherwise preserve
            // them so programmatic callers that omit the section never lose attachments they did
            // not manage.
            if ($membersSubmitted && in_array($attachedId, $previousTalentIds)) {
                continue;
            }
            if ($venueSubmitted && in_array($attachedId, $previousVenueIds)) {
                continue;
            }
            $selectedCurators[] = $attachedId;
        }

        foreach ($selectedCurators as $curatorId) {
            $curator = Role::find($curatorId);
            if ($curator) {
                $roles[] = $curator;
                $roleIds[] = $curator->id;
            }
        }

        // Schedules tab is authoritative for previously-attached schedules: if a
        // schedule was attached before this save and is visible in the tab but
        // not in curators[], detach it even if a parallel section (talent
        // members, venue field) still has it selected. New attachments added via
        // those parallel sections are NOT affected.
        if ($currentRole && ! empty($existingAttachedIds)) {
            foreach ($availableSchedules as $schedule) {
                if ($schedule->subdomain === $currentRole->subdomain) {
                    continue;
                }
                if (! in_array($schedule->id, $existingAttachedIds)) {
                    continue;
                }
                if (in_array($schedule->id, $selectedCurators)) {
                    continue;
                }
                $key = array_search($schedule->id, $roleIds);
                if ($key !== false) {
                    unset($roleIds[$key]);
                    $roles = array_values(array_filter($roles, fn ($r) => $r->id !== $schedule->id));
                }
            }
            $roleIds = array_values(array_unique($roleIds));
        }

        $event->roles()->sync($roleIds);

        $curatorGroups = $request->input('curator_groups', []);

        foreach ($roles as $role) {
            if ((auth()->user() && $user->isMember($role->subdomain))
                || ($role->accept_requests && ! $role->require_approval)
                || ($currentRole && $role->approved_subdomains
                    && in_array($currentRole->subdomain, $role->approved_subdomains))) {
                $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
            }

            // If this is a curator and curator_groups is provided, add it to the pivot
            if ($role && $role->isCurator()) {
                $curatorEncodedId = UrlUtils::encodeId($role->id);
                if (isset($curatorGroups[$curatorEncodedId]) && $curatorGroups[$curatorEncodedId]) {
                    $groupId = UrlUtils::decodeId($curatorGroups[$curatorEncodedId]);
                    $event->roles()->updateExistingPivot($role->id, ['group_id' => $groupId]);
                }
            }

            // If this is the current role and current_role_group_id is provided, add it to the pivot
            if ($role && $role->id === $currentRole->id && $request->has('current_role_group_id') && $request->current_role_group_id) {
                $groupId = UrlUtils::decodeId($request->current_role_group_id);
                $event->roles()->updateExistingPivot($role->id, ['group_id' => $groupId]);
            }
        }

        if ($request->hasFile('flyer_image')) {
            $file = $request->file('flyer_image');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                throw ValidationException::withMessages([
                    'flyer_image' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp',
                ]);
            }

            if ($event->flyer_image_url) {
                $path = $event->getAttributes()['flyer_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            // Resize oversized flyers before storage. Graphic generation
            // decodes these via GD, which OOMs on multi-megapixel images on
            // small PHP-FPM workers (e.g. DigitalOcean App Platform's 128MB
            // cap). We resize the temp file in place so storeAs() uploads
            // the smaller version, which works for both local and S3 disks.
            ImageUtils::resizeImageToMax($file->getRealPath(), 2000);

            $filename = strtolower('flyer_'.Str::random(32).'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $event->flyer_image_url = $filename;
            $event->save();
        }

        if (! $request->hasFile('flyer_image') && $request->input('ai_flyer_image')) {
            $aiFilename = $request->input('ai_flyer_image');
            if (preg_match('/^flyer_[a-z0-9]+\.png$/', $aiFilename)) {
                if ($event->flyer_image_url) {
                    $path = $event->getAttributes()['flyer_image_url'];
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }
                $event->flyer_image_url = $aiFilename;
                $event->save();
            }
        }

        // Cloned events carry the source flyer's raw filename. Copy it to a fresh file so the
        // clone's flyer is independent: deleting or replacing it never touches the original's
        // file. Lowest priority - skipped if the user uploaded or AI-generated a flyer instead.
        // Read + re-write (not Storage::copy) so the destination inherits the disk's default
        // public visibility - the same write path as a normal upload. Storage::copy() would
        // instead retain the source ACL, which is unreliable on S3/Spaces and could 403.
        if (! $request->hasFile('flyer_image') && ! $request->input('ai_flyer_image') && $request->input('clone_flyer_image')) {
            $cloneFilename = $request->input('clone_flyer_image');
            if (is_string($cloneFilename) && preg_match('/^flyer_[a-z0-9]+\.(jpg|jpeg|png|gif|webp)$/', $cloneFilename)) {
                $sourcePath = config('filesystems.default') == 'local' ? 'public/'.$cloneFilename : $cloneFilename;
                if (Storage::exists($sourcePath)) {
                    $extension = strtolower(pathinfo($cloneFilename, PATHINFO_EXTENSION));
                    $newFilename = strtolower('flyer_'.Str::random(32).'.'.$extension);
                    $newPath = config('filesystems.default') == 'local' ? 'public/'.$newFilename : $newFilename;
                    $contents = Storage::get($sourcePath);
                    if ($contents !== null && Storage::put($newPath, $contents)) {
                        $event->flyer_image_url = $newFilename;
                        $event->save();
                    }
                }
            }
        }

        if (config('app.hosted') && ! $event->is_draft) {
            $sendEmailToMembers = $request->input('send_email_to_members', []);
            $sendSmsToMembers = $request->input('send_sms_to_members', []);

            foreach ($roles as $role) {
                if (! $role->isClaimed() && $role->is_subscribed) {
                    $shouldSendEmail = false;
                    $shouldSendSms = false;

                    if ($role->email) {
                        if ($role->isVenue()) {
                            $shouldSendEmail = ! empty($request->input('send_email_to_venue', false));
                        } elseif ($role->isTalent()) {
                            $shouldSendEmail = ! empty($sendEmailToMembers[$role->email]);
                        }
                    } elseif ($role->phone && SmsService::isConfigured()) {
                        if ($role->isVenue()) {
                            $shouldSendSms = ! empty($request->input('send_sms_to_venue', false));
                        } elseif ($role->isTalent()) {
                            $shouldSendSms = ! empty($sendSmsToMembers[$role->phone]);
                        }
                    }

                    if ($shouldSendEmail) {
                        if ($role->isVenue()) {
                            SendQueuedEmail::dispatch(
                                new ClaimVenue($event),
                                $role->email
                            );
                        } elseif ($role->isTalent()) {
                            SendQueuedEmail::dispatch(
                                new ClaimRole($event),
                                $role->email
                            );
                        }
                    } elseif ($shouldSendSms) {
                        $token = Str::random(40);
                        Cache::put('sms_signup_'.$token, $role->phone, now()->addDays(30));
                        $url = route('sign_up', ['sms_token' => $token]);
                        $eventName = str_replace(["\r", "\n"], ' ', Str::limit($event->name, 60));
                        $message = __('messages.sms_claim_message', ['event' => $eventName, 'url' => $url]);
                        SendQueuedSms::dispatch($role->phone, $message);
                    }
                }
            }
        }

        if ($event->tickets_enabled) {
            $ticketData = $request->input('tickets', []);
            $ticketIds = [];
            $hasPassTicket = false;

            foreach ($ticketData as $data) {
                // Process custom_fields with name_en translation
                $ticketCustomFields = isset($data['custom_fields']) ? json_decode($data['custom_fields'], true) : null;

                if (! empty($ticketCustomFields)) {
                    foreach ($ticketCustomFields as $fieldKey => $fieldData) {
                        if (isset($fieldData['options'])) {
                            $ticketCustomFields[$fieldKey]['options'] = implode(',', array_map('trim', explode(',', $fieldData['options'])));
                        }
                    }
                }

                if (! empty($ticketCustomFields) && $currentRole && $currentRole->language_code !== 'en') {
                    $existingTicket = ! empty($data['id']) ? Ticket::find($data['id']) : null;
                    $existingCustomFields = $existingTicket && $existingTicket->custom_fields ? $existingTicket->custom_fields : [];
                    $fieldsNeedingTranslation = [];

                    foreach ($ticketCustomFields as $fieldKey => $fieldData) {
                        if (empty($fieldData['name'])) {
                            continue;
                        }

                        if (empty($fieldData['name_en'])) {
                            $existingField = $existingCustomFields[$fieldKey] ?? null;
                            $existingName = $existingField['name'] ?? null;
                            $existingNameEn = $existingField['name_en'] ?? null;

                            if ($existingNameEn && $existingName === $fieldData['name']) {
                                $ticketCustomFields[$fieldKey]['name_en'] = $existingNameEn;
                            } else {
                                $fieldsNeedingTranslation[$fieldKey] = $fieldData['name'];
                            }
                        }
                    }

                    if (! empty($fieldsNeedingTranslation)) {
                        try {
                            $translations = GeminiUtils::translateCustomFieldNames(
                                array_values($fieldsNeedingTranslation),
                                $currentRole->language_code
                            );

                            foreach ($fieldsNeedingTranslation as $fieldKey => $fieldName) {
                                if (isset($translations[$fieldName])) {
                                    $ticketCustomFields[$fieldKey]['name_en'] = $translations[$fieldName];
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::error('Failed to translate ticket custom field names: '.$e->getMessage());
                        }
                    }
                }

                // De-duplicate indices as a safety net
                if (! empty($ticketCustomFields)) {
                    $usedIndices = [];
                    foreach ($ticketCustomFields as $fieldKey => $fieldData) {
                        $index = $fieldData['index'] ?? null;
                        if ($index && ! in_array($index, $usedIndices)) {
                            $usedIndices[] = $index;
                        } elseif ($index) {
                            for ($i = 1; $i <= 10; $i++) {
                                if (! in_array($i, $usedIndices)) {
                                    $ticketCustomFields[$fieldKey]['index'] = $i;
                                    $usedIndices[] = $i;
                                    break;
                                }
                            }
                        }
                    }
                }

                $salesStartAt = ! empty($data['sales_start_at']) ? $data['sales_start_at'] : null;
                $salesEndAt = ! empty($data['sales_end_at']) ? $data['sales_end_at'] : null;

                $volumeDiscount = null;
                if (array_key_exists('volume_discount', $data)) {
                    $volumeDiscountRaw = null;
                    if (! empty($data['volume_discount'])) {
                        $volumeDiscountRaw = is_string($data['volume_discount'])
                            ? json_decode($data['volume_discount'], true)
                            : $data['volume_discount'];
                    }
                    $volumeDiscount = TicketVolumeDiscount::normalizeRule(is_array($volumeDiscountRaw) ? $volumeDiscountRaw : null);
                } elseif (! empty($data['id'])) {
                    $existingVolTicket = Ticket::find($data['id']);
                    if ($existingVolTicket && $existingVolTicket->event_id == $event->id) {
                        $volumeDiscount = $existingVolTicket->volume_discount;
                    }
                }

                // Pass / subscription configuration. A pass is a single redeemable
                // unit valid across one or more events; it is now allowed on any
                // event (not only recurring), so subscriptions can be sold on a
                // dedicated "Subscriptions" event.
                $isPass = (bool) ($data['is_pass'] ?? false);
                $hasPassTicket = $hasPassTicket || $isPass;

                $passUsageType = $isPass ? ($data['pass_usage_type'] ?? 'per_occurrence') : 'per_occurrence';
                $passScope = $isPass ? ($data['pass_scope'] ?? 'this_event') : 'this_event';

                // per_occurrence (legacy season pass) only applies to recurring
                // events and is always scoped to this event.
                if ($passUsageType === 'per_occurrence' && empty($event->days_of_week)) {
                    $passUsageType = 'total';
                }
                if ($passUsageType === 'per_occurrence') {
                    $passScope = 'this_event';
                }

                $passMaxUses = ($isPass && $passUsageType === 'total' && ! empty($data['pass_max_uses']))
                    ? max(1, (int) $data['pass_max_uses'])
                    : null;
                $passValidDays = ($isPass && ! empty($data['pass_valid_days']))
                    ? max(1, (int) $data['pass_valid_days'])
                    : null;
                $passScopeGroupId = ($isPass && $passScope === 'sub_schedule' && ! empty($data['pass_scope_group_id']))
                    ? UrlUtils::decodeId($data['pass_scope_group_id'])
                    : null;
                $passEventIds = null;
                if ($isPass && $passScope === 'specific_events' && ! empty($data['pass_event_ids'])) {
                    $rawIds = is_string($data['pass_event_ids'])
                        ? json_decode($data['pass_event_ids'], true)
                        : $data['pass_event_ids'];
                    if (is_array($rawIds)) {
                        $decodedIds = array_values(array_filter(array_map(
                            fn ($id) => UrlUtils::decodeId($id),
                            $rawIds
                        )));

                        // Tenant isolation: a pass may only cover events in its own
                        // schedule. Drop ids that don't belong to the current
                        // schedule (defends against forged/cross-tenant ids). With no
                        // schedule context, keep the decoded ids - Ticket::covers()
                        // still scopes coverage to the home schedule at redemption.
                        if (! empty($decodedIds) && $currentRole) {
                            $passEventIds = $currentRole->events()
                                ->whereIn('events.id', $decodedIds)
                                ->pluck('events.id')
                                ->map(fn ($id) => (int) $id)
                                ->values()
                                ->all();
                        } else {
                            $passEventIds = $decodedIds;
                        }
                    }
                }

                // Advance booking: holders reserve a seat for a specific occurrence
                // ahead of time (drawing from the shared pool), with an optional
                // per-occurrence cap protecting walk-up inventory.
                $passAllowBooking = $isPass && ! empty($data['pass_allow_booking']);
                $passSeatsPerOccurrence = ($passAllowBooking && ! empty($data['pass_seats_per_occurrence']))
                    ? max(1, (int) $data['pass_seats_per_occurrence'])
                    : null;

                // Cancellation deadline for advance bookings. Not empty(): 0 is a
                // valid cutoff ("credited until the event starts"), '' means none.
                // Gated on $isPass, NOT $passAllowBooking: toggling advance
                // booking off must not erase the configured policy (it stays
                // stored, inert, and still governs leftover bookings).
                $passCancelCutoffHours = ($isPass
                    && isset($data['pass_cancel_cutoff_hours'])
                    && $data['pass_cancel_cutoff_hours'] !== '')
                    ? max(0, (int) $data['pass_cancel_cutoff_hours'])
                    : null;
                $passLateCancelPolicy = in_array($data['pass_late_cancel_policy'] ?? null, ['forfeit', 'block'], true)
                    ? $data['pass_late_cancel_policy']
                    : 'forfeit';

                // People admitted per event (holder plus any guests), always at
                // least 1. Lets the holder bring guests without consuming extra
                // visits; the QR is scanned once per admitted person.
                $passAdmitsPerEvent = $isPass ? max(1, (int) ($data['pass_admits_per_event'] ?? 1)) : null;

                // A pass is one redeemable unit per sale, so cap it at 1 per order.
                $maxPerOrder = $isPass
                    ? 1
                    : (! empty($data['max_per_order']) ? (int) $data['max_per_order'] : null);

                $passAttributes = [
                    'is_pass' => $isPass,
                    'pass_usage_type' => $passUsageType,
                    'pass_max_uses' => $passMaxUses,
                    'pass_valid_days' => $passValidDays,
                    'pass_scope' => $passScope,
                    'pass_scope_group_id' => $passScopeGroupId,
                    'pass_event_ids' => $passEventIds,
                    'pass_allow_booking' => $passAllowBooking,
                    'pass_seats_per_occurrence' => $passSeatsPerOccurrence,
                    'pass_cancel_cutoff_hours' => $passCancelCutoffHours,
                    'pass_late_cancel_policy' => $passLateCancelPolicy,
                    'pass_admits_per_event' => $passAdmitsPerEvent,
                ];

                if (! empty($data['id'])) {
                    $ticket = Ticket::find($data['id']);
                    $ticketIds[] = $ticket->id;
                    if ($ticket && $ticket->event_id == $event->id) {
                        $ticket->update(array_merge([
                            'type' => $data['type'] ?? null,
                            'quantity' => $data['quantity'] ?? null,
                            'max_per_order' => $maxPerOrder,
                            'price' => $data['price'] ?? null,
                            'description' => $data['description'] ?? null,
                            'sales_start_at' => $salesStartAt,
                            'sales_end_at' => $salesEndAt,
                            'custom_fields' => $ticketCustomFields,
                            'volume_discount' => $volumeDiscount,
                        ], $passAttributes));
                    }
                } else {
                    $ticket = Ticket::create(array_merge([
                        'event_id' => $event->id,
                        'type' => $data['type'] ?? null,
                        'quantity' => $data['quantity'] ?? null,
                        'max_per_order' => $maxPerOrder,
                        'price' => $data['price'] ?? null,
                        'description' => $data['description'] ?? null,
                        'sales_start_at' => $salesStartAt,
                        'sales_end_at' => $salesEndAt,
                        'custom_fields' => $ticketCustomFields,
                        'volume_discount' => $volumeDiscount,
                    ], $passAttributes));
                    $ticketIds[] = $ticket->id;
                }
            }

            $event->tickets()
                ->whereNotIn('id', $ticketIds)
                ->update(['is_deleted' => true]);

            // Subscriptions are single redeemable units; per-person individual
            // ticketing doesn't apply, so disable it when a pass is present.
            if ($hasPassTicket && $event->individual_tickets) {
                $event->individual_tickets = false;
                $event->save();
            }
        } else {
            $event->tickets()->update(['is_deleted' => true]);
        }

        // Save add-ons
        if ($event->tickets_enabled) {
            $addonData = $request->input('addons', []);
            $addonImageData = $request->input('addon_image_data', []);
            $addonIds = [];

            foreach ($addonData as $index => $data) {
                if (empty($data['type'])) {
                    continue;
                }

                if (! empty($data['id'])) {
                    $addon = Ticket::find($data['id']);
                    if ($addon && $addon->event_id == $event->id && $addon->is_addon) {
                        $addon->update([
                            'type' => $data['type'] ?? null,
                            'quantity' => $data['quantity'] ?? null,
                            'max_per_order' => ! empty($data['max_per_order']) ? (int) $data['max_per_order'] : null,
                            'price' => $data['price'] ?? null,
                            'description' => $data['description'] ?? null,
                            'url' => $data['url'] ?? null,
                        ]);
                        $addonIds[] = $addon->id;
                    }
                } else {
                    $addon = Ticket::create([
                        'event_id' => $event->id,
                        'type' => $data['type'] ?? null,
                        'quantity' => $data['quantity'] ?? null,
                        'max_per_order' => ! empty($data['max_per_order']) ? (int) $data['max_per_order'] : null,
                        'price' => $data['price'] ?? null,
                        'description' => $data['description'] ?? null,
                        'url' => $data['url'] ?? null,
                        'is_addon' => true,
                    ]);
                    $addonIds[] = $addon->id;
                }

                // Handle addon image removal
                if (! empty($data['remove_image'])) {
                    $rawImageUrl = $addon->getAttributes()['image_url'] ?? null;
                    if ($rawImageUrl) {
                        $path = $rawImageUrl;
                        if (config('filesystems.default') == 'local') {
                            $path = 'public/'.$path;
                        }
                        Storage::delete($path);
                    }
                    $addon->update(['image_url' => null]);
                }

                // Handle addon image from base64 data URL
                if (isset($addonImageData[$index]) && str_starts_with($addonImageData[$index], 'data:image/')) {
                    $dataUrl = $addonImageData[$index];
                    $commaPos = strpos($dataUrl, ',');

                    if ($commaPos !== false) {
                        $header = substr($dataUrl, 0, $commaPos);
                        $base64Data = substr($dataUrl, $commaPos + 1);

                        if (preg_match('/^data:image\/(jpe?g|png|gif|webp);base64$/', $header, $matches)) {
                            $extension = ($matches[1] === 'jpeg' || $matches[1] === 'jpg') ? 'jpg' : $matches[1];
                            $content = base64_decode($base64Data, true);

                            if ($content !== false) {
                                $imageInfo = @getimagesizefromstring($content);
                                if ($imageInfo !== false) {
                                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                                    if (in_array($imageInfo['mime'], $allowedMimeTypes)) {
                                        // Delete old image if exists
                                        $rawImageUrl = $addon->getAttributes()['image_url'] ?? null;
                                        if ($rawImageUrl) {
                                            $path = $rawImageUrl;
                                            if (config('filesystems.default') == 'local') {
                                                $path = 'public/'.$path;
                                            }
                                            Storage::delete($path);
                                        }

                                        $filename = strtolower('addon_'.Str::random(32).'.'.$extension);
                                        Storage::put(
                                            config('filesystems.default') == 'local' ? 'public/'.$filename : $filename,
                                            $content
                                        );
                                        $addon->update(['image_url' => $filename]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Clean up images for addons being soft-deleted
            $addonsToDelete = $event->addons()
                ->whereNotIn('id', $addonIds)
                ->whereNotNull('image_url')
                ->get();
            foreach ($addonsToDelete as $addonToDelete) {
                $rawImageUrl = $addonToDelete->getAttributes()['image_url'] ?? null;
                if ($rawImageUrl) {
                    $path = $rawImageUrl;
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }
            }

            $event->addons()
                ->whereNotIn('id', $addonIds)
                ->update(['is_deleted' => true]);
        } else {
            // Clean up images for all addons before soft-deleting
            $addonsWithImages = $event->addons()
                ->whereNotNull('image_url')
                ->get();
            foreach ($addonsWithImages as $addonToDelete) {
                $rawImageUrl = $addonToDelete->getAttributes()['image_url'] ?? null;
                if ($rawImageUrl) {
                    $path = $rawImageUrl;
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }
            }

            $event->addons()->update(['is_deleted' => true]);
        }

        // Save promo codes
        if ($event->tickets_enabled) {
            $promoData = $request->input('promo_codes', []);
            $promoIds = [];

            foreach ($promoData as $data) {
                if (empty($data['code'])) {
                    continue;
                }

                $ticketIds = null;
                if (! empty($data['ticket_ids'])) {
                    $decoded = is_string($data['ticket_ids']) ? json_decode($data['ticket_ids'], true) : $data['ticket_ids'];
                    $ticketIds = ! empty($decoded) ? $decoded : null;
                }

                $promoFields = [
                    'event_id' => $event->id,
                    'code' => strtoupper(trim($data['code'])),
                    'type' => $data['type'] ?? 'percentage',
                    'value' => $data['value'] ?? 0,
                    'max_uses' => ! empty($data['max_uses']) ? $data['max_uses'] : null,
                    'expires_at' => ! empty($data['expires_at']) ? $data['expires_at'] : null,
                    'is_active' => ! empty($data['is_active']),
                    'ticket_ids' => $ticketIds,
                ];

                if (! empty($data['id'])) {
                    $promoCode = PromoCode::find($data['id']);
                    if ($promoCode && $promoCode->event_id == $event->id) {
                        $promoCode->update($promoFields);
                        $promoIds[] = $promoCode->id;
                    }
                } else {
                    $promoCode = PromoCode::create($promoFields);
                    $promoIds[] = $promoCode->id;
                }
            }

            PromoCode::where('event_id', $event->id)
                ->whereNotIn('id', $promoIds)
                ->delete();

            // Clear cached IN subscription so it gets recreated with updated promo code
            if ($event->invoiceninja_subscription_id) {
                $event->invoiceninja_subscription_id = null;
                $event->invoiceninja_subscription_url = null;
                $event->save();
            }
        }

        // Save event parts
        $partsData = $request->input('event_parts', []);
        $partIds = [];
        foreach ($partsData as $index => $partData) {
            if (empty($partData['name'])) {
                continue;
            }
            if (! empty($partData['id'])) {
                $part = EventPart::find($partData['id']);
                if ($part && $part->event_id == $event->id) {
                    $part->update([
                        'name' => $partData['name'],
                        'description' => $partData['description'] ?? null,
                        'start_time' => $partData['start_time'] ?? null,
                        'end_time' => $partData['end_time'] ?? null,
                        'sort_order' => $index,
                    ]);
                    $partIds[] = $part->id;
                }
            } else {
                $part = EventPart::create([
                    'event_id' => $event->id,
                    'name' => $partData['name'],
                    'description' => $partData['description'] ?? null,
                    'start_time' => $partData['start_time'] ?? null,
                    'end_time' => $partData['end_time'] ?? null,
                    'sort_order' => $index,
                ]);
                $partIds[] = $part->id;
            }
        }
        // Delete removed parts
        $event->parts()->whereNotIn('id', $partIds)->delete();

        $event->load(['tickets', 'addons', 'roles']);

        // Skip external sync and webhooks for draft events
        if (! $event->is_draft) {
            $isNewOrJustPublished = $event->wasRecentlyCreated || $wasDraftBeforeSave;

            // Sync to Google Calendar for the current role
            if ($currentRole && $currentRole->syncsToGoogle()) {
                if ($isNewOrJustPublished) {
                    $event->syncToGoogleCalendar('create');
                } else {
                    $event->syncToGoogleCalendar('update');
                }
            } elseif ($currentRole) {
                // Sync for members even when owner sync is not enabled
                $memberAction = $isNewOrJustPublished ? 'create' : 'update';
                foreach ($currentRole->getMembersWithCalendarSync() as $member) {
                    if ($member->google_token) {
                        SyncEventToGoogleCalendar::dispatchSync(
                            $event, $currentRole, $memberAction, $member, $member->pivot->google_calendar_id
                        );
                    }
                }
            }

            // Sync to Outlook / Microsoft calendar for the current role
            if ($currentRole && $currentRole->syncsToMicrosoft()) {
                if ($isNewOrJustPublished) {
                    $event->syncToMicrosoftCalendar('create');
                } else {
                    $event->syncToMicrosoftCalendar('update');
                }
            }

            // Sync to CalDAV for the current role
            if ($currentRole && $currentRole->syncsToCalDAV()) {
                if ($isNewOrJustPublished) {
                    $event->syncToCalDAV('create');
                } else {
                    $event->syncToCalDAV('update');
                }
            }

            // Dispatch webhook
            WebhookService::dispatch(
                $isNewOrJustPublished ? 'event.created' : 'event.updated',
                $event
            );
        } elseif ($wasJustUnpublished) {
            // Remove from external calendars when un-publishing
            if ($currentRole && $currentRole->syncsToGoogle()) {
                $event->syncToGoogleCalendar('delete');
            } elseif ($currentRole) {
                // Delete from member calendars even when owner sync is not enabled
                foreach ($currentRole->getMembersWithCalendarSync() as $member) {
                    if ($member->google_token) {
                        SyncEventToGoogleCalendar::dispatchSync(
                            $event, $currentRole, 'delete', $member, $member->pivot->google_calendar_id
                        );
                    }
                }
            }
            if ($currentRole && $currentRole->syncsToMicrosoft()) {
                $event->syncToMicrosoftCalendar('delete');
            }
            if ($currentRole && $currentRole->syncsToCalDAV()) {
                $event->syncToCalDAV('delete');
            }
        }

        // Audit log: single chokepoint for every create/update path (web, API,
        // imports, webhooks). wasRecentlyCreated distinguishes insert from update.
        AuditService::log(
            $event->wasRecentlyCreated ? AuditService::EVENT_CREATE : AuditService::EVENT_UPDATE,
            $user?->id,
            'Event',
            $event->id,
            null,
            null,
            $event->name,
        );

        // Notify registered attendees of a material change (issue #94). $event is reloaded above, so
        // $event->venue reflects the NEW venue. Date changes only fire for non-recurring events; venue /
        // online-link changes fire for all (recurring scoped to upcoming occurrences in the notifier).
        if ($notifyOld && ! $event->is_draft && ! $wasDraftBeforeSave) {
            $changes = self::detectMaterialChanges($notifyOld, [
                'starts_at' => $event->starts_at,
                'duration' => $event->duration,
                'days_of_week' => $event->days_of_week,
                'event_url' => $event->event_url,
                'venue_id' => $event->venue?->id,
                'venue_name' => $event->venue?->getDisplayName(),
            ]);

            if (! empty($changes)) {
                // Bump the iCal sequence so subscribed calendars pick up the change.
                $event->forceFill(['ical_sequence' => (int) $event->ical_sequence + 1])->saveQuietly();

                $isPast = ! $event->days_of_week
                    && $event->starts_at
                    && Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->isPast();

                if ($notifyRequested && ! $isPast && EventChangeNotifier::hasRecipients($event)) {
                    $note = $request->input('notify_message');
                    NotifyEventChange::dispatch($event->id, $changes, $note ? Str::limit($note, 280, '') : null);
                    $this->lastNotifiedCount = EventChangeNotifier::recipientCount($event);
                }
            }
        }

        return $event;
    }

    /**
     * Compute which attendee-relevant details changed between the pre-save snapshot and the saved event.
     * Pure helper (no side effects) so it is unit-testable. Date/time fires only for non-recurring events
     * (mirrors the boot-hook cascade boundary, Event.php). Venue + online link collapse into a single
     * "location" change with a variant so an in-person<->online move reads as one coherent message.
     */
    public static function detectMaterialChanges(array $old, array $new): array
    {
        $changes = [];

        if (! $new['days_of_week']) {
            if ((string) $old['starts_at'] !== (string) $new['starts_at']
                || (float) $old['duration'] !== (float) $new['duration']) {
                $changes['date'] = [
                    'old_starts_at' => $old['starts_at'],
                    'old_duration' => $old['duration'],
                    'old_timezone' => $old['timezone'],
                ];
            }
        }

        $oldVenueId = $old['venue_id'] ?? null;
        $newVenueId = $new['venue_id'] ?? null;
        $oldUrl = self::normalizeUrl($old['event_url'] ?? null);
        $newUrl = self::normalizeUrl($new['event_url'] ?? null);

        $wasInPerson = (bool) $oldVenueId;
        $isInPerson = (bool) $newVenueId;
        $wasOnline = $oldUrl !== '';
        $isOnline = $newUrl !== '';

        $variant = null;
        if ($wasInPerson && $isOnline && ! $isInPerson) {
            $variant = 'moved_online';
        } elseif ($wasOnline && $isInPerson && ! $isOnline) {
            $variant = 'moved_in_person';
        } elseif ($isInPerson && $oldVenueId !== $newVenueId) {
            $variant = 'venue';
        } elseif ($isOnline && $oldUrl !== $newUrl) {
            $variant = 'online_updated';
        }

        if ($variant) {
            $changes['location'] = [
                'variant' => $variant,
                'old_venue' => $old['venue_name'] ?? null,
                'new_venue' => $new['venue_name'] ?? null,
            ];
        }

        return $changes;
    }

    private static function normalizeUrl(?string $url): string
    {
        return rtrim(strtolower(trim((string) $url)), '/');
    }

    public function getEvent($subdomain, $slug, $date = null, $eventId = null, ?Role $role = null)
    {
        $event = null;

        $subdomainRole = $role ?? Role::where('subdomain', $subdomain)->first();
        // Use explicit event ID if provided, otherwise try to decode from slug
        $lookupEventId = $eventId ?: UrlUtils::decodeId($slug);

        // Parse dates with timezone context - local timezone first, then UTC as fallback
        $roleTimezone = $subdomainRole?->timezone ?? config('app.timezone');
        $validDate = $date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
        $eventDateLocal = $validDate ? Carbon::parse($date, $roleTimezone) : null;
        $eventDateUtc = $validDate ? Carbon::parse($date, 'UTC') : null;

        if ($subdomainRole && $lookupEventId) {
            $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                ->where('id', $lookupEventId)
                ->where('is_draft', false)
                ->whereHas('roles', fn ($q) => $q->where('role_id', $subdomainRole->id)->where('is_accepted', true))
                ->first();

            if ($event) {
                return $event;
            }
        }

        // Only load $slugRole when actually needed (ID lookup failed)
        $slugRole = Role::where('subdomain', $slug)->first();

        // Find events attached to both roles (handles all combinations: venue+talent, curator+venue, curator+talent, etc.)
        if ($subdomainRole && $slugRole && $slugRole->isClaimed()) {
            // Try local timezone interpretation first
            if ($eventDateLocal) {
                $event = $this->findEventForBothRoles($subdomainRole, $slugRole, $eventDateLocal);
            }

            // Fallback to UTC interpretation if local didn't find anything
            if (! $event && $eventDateUtc && $eventDateLocal->format('Y-m-d') !== $eventDateUtc->format('Y-m-d')) {
                $event = $this->findEventForBothRoles($subdomainRole, $slugRole, $eventDateUtc);
            }

            // No date provided - find most recent/upcoming
            if (! $event && ! $date) {
                $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $subdomainRole->id)->where('is_accepted', true))
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $slugRole->id)->where('is_accepted', true))
                    ->where('is_draft', false)
                    ->where(function ($q) {
                        $q->where('starts_at', '>=', now()->subDay())
                            ->orWhere(function ($q2) {
                                $q2->where('duration', '>=', 24)
                                    ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                            });
                    })
                    ->orderBy('starts_at')
                    ->first();

                if (! $event) {
                    $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                        ->whereHas('roles', fn ($q) => $q->where('role_id', $subdomainRole->id)->where('is_accepted', true))
                        ->whereHas('roles', fn ($q) => $q->where('role_id', $slugRole->id)->where('is_accepted', true))
                        ->where('is_draft', false)
                        ->where('starts_at', '<', now())
                        ->orderBy('starts_at', 'desc')
                        ->first();
                }
            }

            if ($event) {
                return $event;
            }
        }

        // Try slug-based search with local timezone first
        if ($eventDateLocal) {
            $event = $this->findEventBySlug($subdomain, $slug, $eventDateLocal);

            // Fallback to UTC interpretation if local didn't find anything
            if (! $event && $eventDateLocal->format('Y-m-d') !== $eventDateUtc->format('Y-m-d')) {
                $event = $this->findEventBySlug($subdomain, $slug, $eventDateUtc);
            }
        } else {
            $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                ->where('slug', $slug)
                ->where('is_draft', false)
                ->where(function ($q) {
                    $q->where('starts_at', '>=', now()->subDay())
                        ->orWhere(function ($q2) {
                            $q2->where('duration', '>=', 24)
                                ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                        });
                })
                ->where(function ($query) use ($subdomain) {
                    $query->whereHas('roles', function ($q) use ($subdomain) {
                        $q->where('subdomain', $subdomain)->where('is_accepted', true);
                    });
                })
                ->orderBy('starts_at', 'desc')
                ->first();

            if (! $event) {
                $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                    ->where('slug', $slug)
                    ->where('is_draft', false)
                    ->where('starts_at', '<', now())
                    ->where(function ($query) use ($subdomain) {
                        $query->whereHas('roles', function ($q) use ($subdomain) {
                            $q->where('subdomain', $subdomain)->where('is_accepted', true);
                        });
                    })
                    ->orderBy('starts_at', 'desc')
                    ->first();
            }
        }

        if ($event) {
            return $event;
        }

        // Final fallback: try subdomain-based search with local timezone first
        if ($eventDateLocal) {
            $event = $this->findEventBySubdomain($subdomain, $eventDateLocal);

            // Fallback to UTC interpretation if local didn't find anything
            if (! $event && $eventDateLocal->format('Y-m-d') !== $eventDateUtc->format('Y-m-d')) {
                $event = $this->findEventBySubdomain($subdomain, $eventDateUtc);
            }
        }

        return $event;
    }

    private static function slugPatternFieldsChanged(string $pattern, Event $event): bool
    {
        // Date/time variables -> starts_at/ends_at
        if (preg_match('/\{(day|day_pad|month|month_pad|year|day_name|day_short|date_dmy|date_mdy|date_full_dmy|date_full_mdy|month_name|month_short|time|end_time|duration)\}/', $pattern)
            && $event->isDirty(['starts_at', 'ends_at'])) {
            return true;
        }

        // Event name variable -> name/short_name
        if (str_contains($pattern, '{event_name}')
            && $event->isDirty(['name', 'short_name', 'name_en', 'short_name_en'])) {
            return true;
        }

        // Venue variables -> venue_id
        if (preg_match('/\{(venue|city|address|state|country)\}/', $pattern)
            && $event->isDirty('venue_id')) {
            return true;
        }

        // Custom field variables -> custom_field_values
        if (str_contains($pattern, '{custom_')
            && $event->isDirty('custom_field_values')) {
            return true;
        }

        return false;
    }
}
