<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPart;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Ticket;
use App\Repos\EventRepo;
use App\Services\AuditService;
use App\Services\EventLifecycleService;
use App\Utils\GeminiUtils;
use App\Utils\ImportedTime;
use App\Utils\RemoteImage;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApiEventController extends Controller
{
    protected const MAX_PER_PAGE = 500;

    protected const DEFAULT_PER_PAGE = 100;

    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function index(Request $request)
    {
        try {
            $request->validate([
                'starts_after' => 'nullable|date_format:Y-m-d',
                'starts_before' => 'nullable|date_format:Y-m-d',
                'subdomain' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1|max:500',
                'venue_id' => 'nullable|string',
                'category_id' => 'nullable|integer',
                'name' => 'nullable|string',
                'schedule_type' => 'nullable|string|in:single,recurring',
                'tickets_enabled' => 'nullable|boolean',
                'group_id' => 'nullable|string',
                'external_id' => 'nullable|string|max:255',
                // Not `boolean`: that rule refuses the words "true" and "false", which is what a
                // query string carries.
                'is_cancelled' => 'nullable|in:0,1,true,false',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $events = Event::with(['roles', 'tickets', 'addons', 'parts'])
            ->whereNull('appointment_type_id') // appointment bookings are not real events
            ->whereHas('roles', function ($q) {
                $q->whereIn('roles.id', auth()->user()->roles()
                    ->wherePivotIn('level', ['owner', 'admin'])
                    ->pluck('roles.id'));
            });

        // Filter out non-Pro events
        $events->whereHas('roles', function ($q) {
            $q->wherePro();
        });

        // Filter by schedule subdomain
        if ($request->has('subdomain')) {
            $subdomain = $request->subdomain;
            $events->whereHas('roles', function ($q) use ($subdomain) {
                $q->where('subdomain', $subdomain);
            });
        }

        // Filter by start date range
        if ($request->has('starts_after')) {
            $events->where('starts_at', '>=', $request->starts_after.' 00:00:00');
        }

        if ($request->has('starts_before')) {
            $events->where('starts_at', '<=', $request->starts_before.' 23:59:59');
        }

        // Filter by venue
        if ($request->has('venue_id')) {
            $venueId = UrlUtils::decodeId($request->venue_id);
            $events->whereHas('roles', function ($q) use ($venueId) {
                $q->where('roles.id', $venueId)->where('roles.type', 'venue');
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $events->where('category_id', $request->category_id);
        }

        // Filter by name
        if ($request->has('name')) {
            $name = str_replace(['%', '_'], ['\\%', '\\_'], $request->name);
            $events->where('name', 'like', '%'.$name.'%');
        }

        // Filter by schedule type
        if ($request->has('schedule_type')) {
            if ($request->schedule_type === 'recurring') {
                $events->whereNotNull('recurring_frequency');
            } else {
                $events->whereNull('recurring_frequency');
            }
        }

        // Filter by tickets enabled
        if ($request->has('tickets_enabled')) {
            $events->where('tickets_enabled', $request->boolean('tickets_enabled'));
        }

        // Filter by RSVP enabled
        if ($request->has('rsvp_enabled')) {
            $events->where('rsvp_enabled', $request->boolean('rsvp_enabled'));
        }

        // Cancelled events are listed beside the rest unless the caller asks for one or the other
        if ($request->filled('is_cancelled')) {
            $events->where('is_cancelled', filter_var($request->is_cancelled, FILTER_VALIDATE_BOOLEAN));
        }

        // The event another system knows by this id. The id is unique per OWNING schedule, and
        // `subdomain` above matches every schedule an event is listed on, so on its own it could
        // answer with an event somebody else owns that carries the same id in their numbering
        // (and a sync would then write onto it). The lookup is of events the named schedule
        // owns; without a schedule, of events owned by schedules the caller runs.
        if ($request->filled('external_id')) {
            $events->where('external_id', (string) $request->external_id)
                ->whereIn('creator_role_id', $request->has('subdomain')
                    ? Role::where('subdomain', $request->subdomain)->select('id')
                    : auth()->user()->roles()->wherePivotIn('level', ['owner', 'admin'])->select('roles.id'));
        }

        // Filter by sub-schedule (group)
        if ($request->has('group_id')) {
            $groupId = UrlUtils::decodeId($request->group_id);
            $events->whereHas('roles', function ($q) use ($groupId) {
                $q->where('event_role.group_id', $groupId);
            });
        }

        $events = $events->orderBy('starts_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $events->map(function ($event) {
                return $event->toApiData();
            })->values(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'from' => $events->firstItem(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'to' => $events->lastItem(),
                'total' => $events->total(),
                'path' => $request->url(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function show(Request $request, $id)
    {
        $event = Event::with(['roles', 'tickets', 'addons', 'parts'])->find(UrlUtils::decodeId($id));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        // Auth: user must own the event or be owner/admin on one of its roles
        if (! auth()->user()->canEditEvent($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $event->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        return response()->json([
            'data' => $event->toApiData(),
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(Request $request, $subdomain)
    {
        $role = Role::with('groups')->subdomain($subdomain)->where('is_deleted', false)->first();

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        $encodedRoleId = UrlUtils::encodeId($role->id);

        // Check for owner/admin level
        $userRole = auth()->user()->roles()
            ->where('subdomain', $subdomain)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->first();

        if (! $userRole) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        // The id the caller's own system knows this record by. Read here, after the caller is
        // known to be allowed on this schedule (or the answer below would tell a stranger which
        // ids exist) and before anything is created: an id this schedule already has an event
        // for is a conflict, with the event that holds it.
        [$externalId, $refusal] = $this->externalIdFrom($request);
        if ($refusal) {
            return $refusal;
        }

        // "upsert": true turns that conflict into an update of the event that holds the id. It is
        // decided HERE, before any step below: those steps are a new event's (required fields,
        // the schedule's default visibility, the default payment method, the daily cap), and run
        // on an event that exists they would republish a draft the owner is holding or reset
        // what the request never mentioned. The update runs under the rules of PUT, through the
        // schedule in the address, which owns the event.
        [$upsert, $refusal] = $this->upsertAsked($request, $externalId);
        if ($refusal) {
            return $refusal;
        }
        if ($externalId !== null && ($holder = $this->eventWithExternalId($role->id, $externalId))) {
            return $upsert ? $this->applyUpdate($request, $holder, $role, false) : $this->externalIdConflict($holder);
        }

        // What was sent, before the steps below rewrite it for a new event: a request that
        // loses the race for its id further down is answered from this.
        $sent = $request->all();

        $allowedCategoryIds = collect($role->getEventCategories())->pluck('id')->all();

        // From the gateway registry, so a new gateway is accepted here without touching this file.
        // 'manual' is kept as the long-standing alias for cash and normalised below.
        $allowedPaymentMethods = array_merge(payment_gateways()->selectableKeys(), ['manual']);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'starts_at' => 'required|date_format:Y-m-d H:i:s',
                'duration' => 'nullable|numeric|min:0|max:8760',
                'ends_at' => 'nullable|date_format:Y-m-d H:i:s',
                'description' => 'nullable|string|max:10000',
                'short_description' => 'nullable|string|max:500',
                // 500, matching the column - see Event::CLAMPED_COLUMNS. A 400-character Teams
                // or Zoom join URL is legal on every other write path, so a stricter rule here
                // would 422 an ordinary read-modify-write round-trip of an untouched field.
                'event_url' => 'nullable|url|max:500',
                'event_password' => 'nullable|string|max:255',
                'is_private' => 'nullable|boolean',
                'is_draft' => 'nullable|boolean',
                'is_internal' => 'nullable|boolean',
                'rsvp_enabled' => 'nullable|boolean',
                'rsvp_limit' => 'nullable|integer|min:1',
                'registration_url' => 'nullable|url|max:2048',
                'category_id' => $allowedCategoryIds ? 'nullable|integer|in:'.implode(',', $allowedCategoryIds) : 'nullable|integer|in:'.implode(',', array_keys(config('app.event_categories', []))),
                'category' => 'nullable|string|max:255',
                'tickets_enabled' => 'nullable|boolean',
                'ticket_currency_code' => 'nullable|string|size:3',
                'payment_method' => 'nullable|string|in:'.implode(',', $allowedPaymentMethods),
                'payment_instructions' => 'nullable|string|max:5000',
                'schedule_type' => 'nullable|string|in:single,recurring',
                'recurring_frequency' => 'required_if:schedule_type,recurring|nullable|string|in:daily,weekly,every_n_weeks,monthly_date,monthly_weekday,yearly',
                'recurring_interval' => 'nullable|integer|min:2',
                'recurring_end_type' => 'nullable|string|in:never,on_date,after_events',
                'recurring_end_value' => 'nullable|string',
                'days_of_week' => 'required_if:recurring_frequency,weekly|required_if:recurring_frequency,every_n_weeks|nullable|string|size:7|regex:/^[01]{7}$/',
                'venue_id' => 'nullable',
                'venue_name' => 'nullable|string|max:255',
                'venue_address1' => 'nullable|string|max:255',
                'members' => 'nullable|array',
                'schedule' => 'nullable|string|max:255',
                'tickets' => 'nullable|array',
                'tickets.*.type' => 'required_with:tickets|string|max:255',
                'tickets.*.quantity' => 'nullable|integer|min:0',
                'tickets.*.price' => 'nullable|numeric|min:0',
                'tickets.*.description' => 'nullable|string|max:1000',
                'tickets.*.sales_start_at' => 'nullable|date',
                'tickets.*.sales_end_at' => 'nullable|date',
                'event_parts' => 'nullable|array',
                'event_parts.*.name' => 'required_with:event_parts|string|max:255',
                'event_parts.*.description' => 'nullable|string|max:1000',
                'event_parts.*.start_time' => 'nullable|string',
                'event_parts.*.end_time' => 'nullable|string',
                'addons' => 'nullable|array',
                'addons.*.type' => 'required_with:addons|string|max:255',
                'addons.*.quantity' => 'nullable|integer|min:0',
                'addons.*.price' => 'nullable|numeric|min:0',
                'addons.*.description' => 'nullable|string|max:1000',
                'addons.*.url' => 'nullable|url|max:2000',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($refusal = $this->refuseCancelledInBody($request, false)) {
            return $refusal;
        }

        if ($refusal = $this->takeEndsAt($request, null)) {
            return $refusal;
        }

        [$flyerUrl, , $refusal] = $this->flyerAddressFrom($request, null);
        if ($refusal) {
            return $refusal;
        }

        // 'manual' is a documented alias the API has always accepted, but events.payment_method is a
        // MySQL enum that has never contained it, so it could only ever fail on write. Normalise it
        // to the value it plainly means rather than leaving a rule that accepts an unstorable input.
        if ($request->input('payment_method') === 'manual') {
            $request->merge(['payment_method' => 'cash']);
        }

        // Strip request to only allowed fields to prevent mass assignment
        $request->replace($request->only([
            'name', 'starts_at', 'duration', 'description', 'short_description',
            'event_url', 'event_password', 'is_private', 'is_draft', 'is_internal', 'rsvp_enabled', 'rsvp_limit',
            'registration_url',
            'category_id', 'category', 'tickets_enabled', 'ticket_currency_code',
            'payment_method', 'payment_instructions',
            'schedule_type', 'recurring_frequency', 'recurring_interval',
            'recurring_end_type', 'recurring_end_value', 'days_of_week',
            'venue_id', 'venue_name', 'venue_address1',
            'members', 'schedule', 'tickets', 'event_parts', 'addons',
        ]));

        // On a new event every row is a new row. An id copied from a read of another event names
        // a row that is not this event's, and saveEvent() skips a row whose id it cannot find: the
        // ticket type was left out of the event it was posted to, under a 201. Custom fields are
        // the JSON string the form posts, as in update().
        foreach (['tickets', 'addons', 'event_parts'] as $list) {
            if (! is_array($request->input($list))) {
                continue;
            }

            $request->merge([$list => array_map(function ($row) use ($list) {
                if (! is_array($row)) {
                    return $row;
                }

                unset($row['id']);

                if ($list === 'tickets' && is_array($row['custom_fields'] ?? null)) {
                    $row['custom_fields'] = json_encode($row['custom_fields']);
                }

                return $row;
            }, $request->input($list))]);
        }

        // Honour DEFAULT_PAYMENT_METHOD when the caller said nothing, the same as the event form
        // does. Only on create: an update that omits the field must leave the stored value alone.
        // saveEvent() builds the row with fill($request->all()), so an absent key would otherwise
        // take the events.payment_method column default of 'cash'.
        //
        // The currency is whatever the caller sent; omitting it takes the column default of USD, so
        // a rand-only gateway like Payfast correctly does not apply and this resolves back to cash.
        // has(), not filled(): the rule at the top of this method is `nullable` and
        // events.payment_method is a nullable column, so an explicit "payment_method": null is a
        // caller saying "no method", not a caller saying nothing. Only an absent key takes the default.
        if (! $request->has('payment_method')) {
            $default = payment_gateways()->defaultMethodFor(
                auth()->user(),
                $request->input('ticket_currency_code'),
            );

            if ($default !== 'cash') {
                $request->merge(['payment_method' => $default]);
            }
        }

        // Convert the incoming UTC starts_at to the SCHEDULE's local time, because saveEvent()
        // interprets the submitted wall-clock in the schedule's timezone (not the user's account tz).
        // The same zone is handed to saveEvent() below as its $timezoneOverride, so the wall-clock
        // built here is read back in the zone it was written in.
        $scheduleTz = $this->scheduleTimezoneFor($role);
        if ($request->has('starts_at')) {
            $utcTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at, 'UTC');
            $localTime = $utcTime->setTimezone($scheduleTz)->format('Y-m-d H:i:s');
            $request->merge(['starts_at' => $localTime]);
        }

        // Convert days_of_week string to individual checkbox params for saveEvent()
        $this->convertDaysOfWeek($request);

        // Seed visibility from the schedule's default. If the caller sent NO visibility flag at all,
        // apply the full default state. If they sent some flag but omitted is_draft, still seed
        // is_draft from the default's hide-bit so a drafts-by-default schedule never silently
        // publishes (a present-but-false is_private must not defeat the draft default).
        $defaultVisibility = $role->defaultEventVisibility();
        if (! $request->has('is_draft') && ! $request->has('is_private') && ! $request->has('is_internal')) {
            $request->merge([
                'is_draft' => in_array($defaultVisibility, ['draft', 'internal']),
                'is_private' => $defaultVisibility === 'unlisted',
                'is_internal' => $defaultVisibility === 'internal',
            ]);
        } elseif (! $request->has('is_draft') && ! $request->boolean('is_private') && ! $request->boolean('is_internal')) {
            // Partial payload with no hidden-state choice (unlisted/internal are absent or false):
            // apply the draft default so a drafts-by-default schedule never silently publishes.
            // An explicit is_private/is_internal choice is left untouched (no incoherent draft+private).
            $request->merge(['is_draft' => in_array($defaultVisibility, ['draft', 'internal'])]);
        }

        // Pre-processing: venue, members, group, category
        $errorResponse = $this->preprocessEventRequest($request, $role, $encodedRoleId);
        if ($errorResponse) {
            return $errorResponse;
        }

        // The flyer, fetched before anything is saved and before the lock below is taken: a
        // picture that cannot be had refuses the request with nothing made, and nobody waits on
        // somebody else's server.
        [$flyer, $refusal] = $this->fetchFlyer($flyerUrl);
        if ($refusal) {
            return $refusal;
        }

        // Two requests carrying the same new id must not both pass the check above and both
        // create: the second waits for the first, then finds its event. The unique index is the
        // backstop, and it reads as the same conflict.
        $save = fn () => $this->eventRepo->saveEvent($role, $request, null, true, $scheduleTz, externalId: $externalId, flyer: $flyer);

        $holder = null;

        try {
            if ($externalId === null) {
                $event = $save();
            } else {
                $event = Cache::lock($this->externalIdLockKey($role->id, $externalId), 30)->block(10, function () use ($role, $externalId, $save) {
                    return $this->eventWithExternalId($role->id, $externalId) ?: $save();
                });

                if (! $event->wasRecentlyCreated) {
                    $holder = $event;
                }
            }
        } catch (LockTimeoutException $e) {
            return response()->json(['error' => 'Another request is writing an event with this external_id. Try again.'], 409);
        } catch (QueryException $e) {
            if (! $this->isDuplicateExternalId($e)) {
                throw $e;
            }

            $holder = $this->eventWithExternalId($role->id, (string) $externalId);

            if (! $holder) {
                return $this->externalIdConflict(null);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } finally {
            $this->forgetFlyer($flyer);
        }

        // Another request made the event while this one waited. Without upsert that is the
        // conflict; with it, this request is the update it would have been a moment later, from
        // what was sent rather than from what the steps above turned it into.
        if ($holder) {
            if (! $upsert) {
                return $this->externalIdConflict($holder);
            }

            $request->replace($sent);

            return $this->applyUpdate($request, $holder, $role, false);
        }

        if ($flyer) {
            $this->rememberFlyerSource($event, $flyerUrl);
        }

        $event->load(['roles', 'tickets', 'addons', 'parts']);

        return response()->json([
            'data' => $event->toApiData(),
            'meta' => ['message' => 'Event created successfully'] + ($upsert ? ['created' => true] : []),
        ], 201, [], JSON_PRETTY_PRINT);
    }

    public function update(Request $request, $id)
    {
        $event = Event::with(['roles', 'tickets', 'addons', 'parts'])->find(UrlUtils::decodeId($id));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (! auth()->user()->canEditEvent($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->applyUpdate($request, $event);
    }

    /**
     * Everything an update is, once the caller is known to be allowed: PUT /api/events/{id}, and
     * a POST that names an external_id the schedule already has an event for and asks to upsert.
     *
     * $through is the schedule the save runs through when the caller named one (the upsert's
     * address, which is the event's owner). Without it, it is the first of the event's schedules
     * the caller runs, as it has always been for a PUT. It decides the timezone a start is read
     * in, the sub-schedules a `schedule` slug is looked up in, and whether `members` is the
     * caller's list or the schedule's own (a talent schedule is its own performer).
     *
     * $created is what an upsert reports under meta.created; null leaves the key out.
     */
    private function applyUpdate(Request $request, Event $event, ?Role $through = null, ?bool $created = null)
    {
        $event->loadMissing(['roles', 'tickets', 'addons', 'parts']);

        if (! $event->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        $currentRoleForUpdate = $event->creatorRole ?? $event->roles->first();
        $allowedCategoryIdsForUpdate = $currentRoleForUpdate
            ? collect($currentRoleForUpdate->getEventCategories())->pluck('id')->all()
            : array_keys(config('app.event_categories', []));

        $allowedPaymentMethods = array_merge(payment_gateways()->selectableKeys(), ['manual']);

        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'starts_at' => 'sometimes|required|date_format:Y-m-d H:i:s',
                'duration' => 'nullable|numeric|min:0|max:8760',
                'ends_at' => 'nullable|date_format:Y-m-d H:i:s',
                'description' => 'nullable|string|max:10000',
                'short_description' => 'nullable|string|max:500',
                // 500, matching the column - see Event::CLAMPED_COLUMNS. A 400-character Teams
                // or Zoom join URL is legal on every other write path, so a stricter rule here
                // would 422 an ordinary read-modify-write round-trip of an untouched field.
                'event_url' => 'nullable|url|max:500',
                'event_password' => 'nullable|string|max:255',
                'is_private' => 'nullable|boolean',
                'is_draft' => 'nullable|boolean',
                'is_internal' => 'nullable|boolean',
                'rsvp_enabled' => 'nullable|boolean',
                'rsvp_limit' => 'nullable|integer|min:1',
                'registration_url' => 'nullable|url|max:2048',
                'category_id' => 'nullable|integer|in:'.implode(',', $allowedCategoryIdsForUpdate),
                'category' => 'nullable|string|max:255',
                'tickets_enabled' => 'nullable|boolean',
                'ticket_currency_code' => 'nullable|string|size:3',
                'payment_method' => 'nullable|string|in:'.implode(',', $allowedPaymentMethods),
                'payment_instructions' => 'nullable|string|max:5000',
                'schedule_type' => 'nullable|string|in:single,recurring',
                'recurring_frequency' => 'required_if:schedule_type,recurring|nullable|string|in:daily,weekly,every_n_weeks,monthly_date,monthly_weekday,yearly',
                'recurring_interval' => 'nullable|integer|min:2',
                'recurring_end_type' => 'nullable|string|in:never,on_date,after_events',
                'recurring_end_value' => 'nullable|string',
                'days_of_week' => 'required_if:recurring_frequency,weekly|required_if:recurring_frequency,every_n_weeks|nullable|string|size:7|regex:/^[01]{7}$/',
                'venue_id' => 'nullable',
                'venue_name' => 'nullable|string|max:255',
                'venue_address1' => 'nullable|string|max:255',
                'members' => 'nullable|array',
                'schedule' => 'nullable|string|max:255',
                'tickets' => 'nullable|array',
                // Nullable here, unlike on create: an event with one ticket type usually has no
                // name on it (the form only asks once there are two), a read returns
                // "type": null, and that row has to be able to come back.
                'tickets.*.type' => 'nullable|string|max:255',
                'tickets.*.quantity' => 'nullable|integer|min:0',
                'tickets.*.price' => 'nullable|numeric|min:0',
                'tickets.*.description' => 'nullable|string|max:1000',
                'tickets.*.sales_start_at' => 'nullable|date',
                'tickets.*.sales_end_at' => 'nullable|date',
                'event_parts' => 'nullable|array',
                'event_parts.*.name' => 'required_with:event_parts|string|max:255',
                'event_parts.*.description' => 'nullable|string|max:1000',
                'event_parts.*.start_time' => 'nullable|string',
                'event_parts.*.end_time' => 'nullable|string',
                'addons' => 'nullable|array',
                'addons.*.type' => 'required_with:addons|string|max:255',
                'addons.*.quantity' => 'nullable|integer|min:0',
                'addons.*.price' => 'nullable|numeric|min:0',
                'addons.*.description' => 'nullable|string|max:1000',
                'addons.*.url' => 'nullable|url|max:2000',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // An event that has taken money is DENOMINATED, and its currency stops being editable.
        //
        // events.ticket_currency_code is the only record of what a sale was taken in - `sales` has
        // no currency column of its own, unlike sale_installment_plans - so every reader downstream
        // resolves it through the event, live. Editing it re-denominates history: past sales are
        // reported in a currency nobody was ever charged, and a later refund is SCALED by it.
        // Stripe amounts are minor units, so a $10.00 refund on a USD sale whose event now says JPY
        // computes round(10 * 1) = 10 minor units - $0.10 goes back, the ledger records $10.00, and
        // the buyer is short $9.90 with nothing on the row to show it.
        //
        // Refused rather than fixed downstream because there is nowhere correct to fix it: with no
        // per-sale snapshot, the old currency is genuinely gone the moment this write lands. The
        // web editor holds the same line: EventController::update() refuses the change, and
        // edit() renders both Currency selects disabled once the event has taken money.
        if ($request->filled('ticket_currency_code')) {
            $requested = strtoupper((string) $request->input('ticket_currency_code'));
            $current = strtoupper((string) $event->ticket_currency_code);

            if ($current !== '' && $requested !== $current && $event->hasSettledMoney()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => ['ticket_currency_code' => [
                        'This event has already taken money in '.$current.', so its currency can no longer be changed.',
                    ]],
                ], 422);
            }
        }

        if ($refusal = $this->refuseCancelledInBody($request, (bool) $event->is_cancelled)) {
            return $refusal;
        }

        if ($refusal = $this->takeExternalId($request, $event)) {
            return $refusal;
        }

        if ($refusal = $this->takeEndsAt($request, $event)) {
            return $refusal;
        }

        [$flyerUrl, $removeFlyer, $refusal] = $this->flyerAddressFrom($request, $event);
        if ($refusal) {
            return $refusal;
        }

        // 'manual' is a documented alias the API has always accepted, but events.payment_method is a
        // MySQL enum that has never contained it, so it could only ever fail on write. Normalise it
        // to the value it plainly means rather than leaving a rule that accepts an unstorable input.
        if ($request->input('payment_method') === 'manual') {
            $request->merge(['payment_method' => 'cash']);
        }

        // Strip request to only allowed fields to prevent mass assignment
        $request->replace($request->only([
            'name', 'starts_at', 'duration', 'description', 'short_description',
            'event_url', 'event_password', 'is_private', 'is_draft', 'is_internal', 'rsvp_enabled', 'rsvp_limit',
            'registration_url',
            'category_id', 'category', 'tickets_enabled', 'ticket_currency_code',
            'payment_method', 'payment_instructions',
            'schedule_type', 'recurring_frequency', 'recurring_interval',
            'recurring_end_type', 'recurring_end_value', 'days_of_week',
            'venue_id', 'venue_name', 'venue_address1',
            'members', 'schedule', 'tickets', 'event_parts', 'addons',
        ]));

        // What the caller named, read before the blocks below start carrying over what it did
        // not. EventRepo::saveEvent() was written for the event form, which posts every section
        // on every save, so a section that is absent there means "emptied". Everything this
        // method does not carry over is therefore REMOVED by a request that never mentioned it.
        // ApiEventUpdateProtectionTest.
        //
        // filled(), not has(): a client that reads an event and writes the object back sends
        // "venue_id": null for an event that has no venue, and that names nothing.
        $sentVenue = $request->filled('venue_id') || $request->filled('venue_name') || $request->filled('venue_address1');

        // The schedule the save runs through: the one the caller named, when the event is on it
        // (store() has already checked the caller runs it), else the first of the event's
        // schedules where the caller is owner or admin.
        $currentRole = $through ? $event->roles->firstWhere('id', $through->id) : null;

        if (! $currentRole) {
            foreach ($event->roles as $role) {
                $pivot = auth()->user()->roles()
                    ->where('roles.id', $role->id)
                    ->wherePivotIn('level', ['owner', 'admin'])
                    ->first();
                if ($pivot) {
                    $currentRole = $role;
                    break;
                }
            }
        }

        if (! $currentRole) {
            return response()->json(['error' => 'No schedule found for this event that you have access to'], 422);
        }

        // On a talent schedule the schedule itself is the performer and preprocessEventRequest()
        // sets the sent list aside, so it is not the caller replacing the performers.
        $sentMembers = $request->has('members') && ! $currentRole->isTalent();

        $encodedRoleId = UrlUtils::encodeId($currentRole->id);

        // Preserve recurring config if not explicitly being changed
        if (! $request->has('schedule_type') && $event->recurring_frequency) {
            $request->merge([
                'schedule_type' => 'recurring',
                'recurring_frequency' => $event->recurring_frequency,
                'recurring_interval' => $event->recurring_interval,
                'recurring_end_type' => $event->recurring_end_type,
                'recurring_end_value' => $event->recurring_end_value,
            ]);
            // Preserve days_of_week if not explicitly provided
            if (! $request->has('days_of_week') && $event->days_of_week) {
                $request->merge(['days_of_week' => $event->days_of_week]);
            }
        }

        // The dates added to a series and the ones skipped from it. The API has no field for
        // either, and saveEvent() reads a recurring save that lacks them as "there are none", so
        // every update of a recurring event cleared both. An event that stops repeating loses
        // them in saveEvent(), as it does on the form.
        if ($request->input('schedule_type') === 'recurring') {
            $request->merge([
                'recurring_include_dates' => $event->recurring_include_dates ?? [],
                'recurring_exclude_dates' => $event->recurring_exclude_dates ?? [],
            ]);
        }

        // Express starts_at in the SCHEDULE's local time, because saveEvent() interprets the
        // submitted wall-clock in the schedule's timezone (not the user's account tz). Using the
        // schedule tz here makes the round-trip identity, so an update that doesn't change the time
        // doesn't shift it.
        //
        // The omitted-starts_at branch is not optional: saveEvent() does fill($request->all()) and
        // then re-reads whatever $event->starts_at holds as a schedule-local wall-clock, so leaving
        // the stored UTC value in place would shift it by the schedule's offset on every PATCH.
        // The zone is also passed to saveEvent() as $timezoneOverride so both halves of the round
        // trip resolve it identically - saveEvent()'s own fallback chain consults the venue, which
        // this controller cannot know before the save.
        $scheduleTz = $this->scheduleTimezoneFor($currentRole);
        if (! $request->has('starts_at')) {
            $request->merge(['starts_at' => $event->starts_at
                ? $event->getStartDateTime(null, true, $scheduleTz)->format('Y-m-d H:i:s')
                : '']);
        } else {
            $utcTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->starts_at, 'UTC');
            $localTime = $utcTime->setTimezone($scheduleTz)->format('Y-m-d H:i:s');
            $request->merge(['starts_at' => $localTime]);
        }

        // Preserve tickets_enabled if not being changed
        if (! $request->has('tickets_enabled')) {
            $request->merge(['tickets_enabled' => $event->tickets_enabled]);
        }

        // Rows the caller sent back. Each one that carries an id is laid over the stored row that
        // id names, so a row keeps what it did not send; a row with no id is a new one; a stored
        // row the list leaves out is retired by saveEvent(), as on the form.
        //
        // The ids are the ones this API returned, which are ENCODED, and saveEvent() looks a row
        // up by its raw id: a row sent back as it was read matched nothing, was neither updated
        // nor created, and every stored row was then retired for not being in the list.
        $passEventsSent = [];

        foreach ([
            'tickets' => [$event->tickets, fn (Ticket $ticket) => $this->ticketRow($ticket)],
            'addons' => [$event->addons, fn (Ticket $addon) => $this->addonRow($addon)],
            'event_parts' => [$event->parts, fn (EventPart $part) => $this->partRow($part)],
        ] as $list => [$stored, $asRow]) {
            if (! is_array($request->input($list))) {
                continue;
            }

            $rows = [];
            foreach ($request->input($list) as $index => $row) {
                if (! is_array($row) || empty($row['id'])) {
                    if (is_array($row)) {
                        unset($row['id']);
                    }
                    $rows[] = $row;

                    continue;
                }

                $id = is_scalar($row['id']) ? UrlUtils::decodeId((string) $row['id']) : null;
                $match = $id ? $stored->firstWhere('id', $id) : null;

                // Refused, not skipped: a row that is silently dropped takes its stored row with
                // it, and the caller is told the update worked.
                if (! $match) {
                    return response()->json([
                        'error' => 'Validation failed',
                        'errors' => [$list.'.'.$index.'.id' => ['No row with this id belongs to this event.']],
                    ], 422);
                }

                if ($list === 'tickets' && array_key_exists('pass_event_ids', $row)) {
                    $passEventsSent[] = $match->id;
                }

                unset($row['id']);
                $rows[] = array_merge($asRow($match), $row);
            }

            // saveEvent() reads a ticket type's custom fields as the JSON string the form posts.
            if ($list === 'tickets') {
                foreach ($rows as $index => $row) {
                    if (is_array($row) && is_array($row['custom_fields'] ?? null)) {
                        $rows[$index]['custom_fields'] = json_encode($row['custom_fields']);
                    }
                }
            }

            $request->merge([$list => $rows]);
        }

        // Preserve existing tickets if not being changed
        if (! $request->has('tickets') && $event->tickets_enabled) {
            $request->merge(['tickets' => $event->tickets->map(fn (Ticket $ticket) => $this->ticketRow($ticket))->all()]);
        }

        // Preserve existing add-ons if not being changed
        if (! $request->has('addons') && $event->tickets_enabled) {
            $request->merge(['addons' => $event->addons->map(fn (Ticket $addon) => $this->addonRow($addon))->all()]);
        }

        // Preserve existing event parts if not being changed
        if (! $request->has('event_parts')) {
            $request->merge(['event_parts' => $event->parts->map(fn (EventPart $part) => $this->partRow($part))->all()]);
        }

        // Promo codes have no field in the API at all, and saveEvent() deletes every code the
        // request does not list: each update, a rename included, deleted them all.
        $request->merge(['promo_codes' => PromoCode::where('event_id', $event->id)->get()->map(fn (PromoCode $promo) => [
            'id' => $promo->id,
            'code' => $promo->code,
            'type' => $promo->type,
            'value' => $promo->value,
            'max_uses' => $promo->max_uses,
            'expires_at' => $promo->expires_at?->format('Y-m-d H:i:s'),
            'is_active' => $promo->is_active,
            'ticket_ids' => $promo->ticket_ids,
        ])->all()]);

        // A pass counted per date only exists on a recurring event. saveEvent() turns one on any
        // other event into a pass with a number of visits and NO number, which its own
        // validatePassConfiguration() then refuses on every later save: making a series single
        // (or adding such a pass to a single event) locked the event out of this endpoint, for
        // a field the next caller never sent. Refused here, where the caller can still act on it.
        if ($request->input('schedule_type') !== 'recurring') {
            foreach ((array) $request->input('tickets', []) as $index => $row) {
                if (is_array($row) && ! empty($row['is_pass']) && ($row['pass_usage_type'] ?? 'per_occurrence') === 'per_occurrence') {
                    return response()->json([
                        'error' => 'Validation failed',
                        'errors' => ['tickets.'.$index.'.pass_usage_type' => [
                            'A pass counted per date (per_occurrence) needs a recurring event. Give the pass another pass_usage_type first, or keep the event recurring.',
                        ]],
                    ], 422);
                }
            }
        }

        // saveEvent() keeps only the covered events that belong to the schedule the save runs
        // through, which is right for what a person picks on the form and wrong for a list this
        // request never sent: an update through another of the event's schedules emptied the
        // pass, and the empty list then failed validation on the update after that. Put back
        // below, for every pass whose covered events the caller did not send.
        $passEventsKept = [];
        foreach ($event->tickets as $ticket) {
            if ($ticket->is_pass && $ticket->pass_scope === 'specific_events' && ! in_array($ticket->id, $passEventsSent, true)) {
                $passEventsKept[$ticket->id] = array_values(array_map('intval', $ticket->pass_event_ids ?? []));
            }
        }

        // Convert days_of_week string to individual checkbox params for saveEvent()
        $this->convertDaysOfWeek($request);

        // Pre-processing: venue, members, group, category
        $currentRole->loadMissing('groups');
        $errorResponse = $this->preprocessEventRequest($request, $currentRole, $encodedRoleId);
        if ($errorResponse) {
            return $errorResponse;
        }

        $this->keepAttachments($request, $event, $currentRole, $sentVenue, $sentMembers);

        // Last, when nothing above can still refuse the request, and before anything is saved.
        [$flyer, $refusal] = $this->fetchFlyer($flyerUrl);
        if ($refusal) {
            return $refusal;
        }

        try {
            $event = $this->eventRepo->saveEvent($currentRole, $request, $event, true, $scheduleTz, flyer: $flyer);
        } catch (QueryException $e) {
            if (! $this->isDuplicateExternalId($e)) {
                throw $e;
            }

            return response()->json([
                'error' => 'Validation failed',
                'errors' => ['external_id' => ['Another event on the same schedule already has this external_id.']],
            ], 422);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } finally {
            $this->forgetFlyer($flyer);
        }

        if ($removeFlyer) {
            $this->eventRepo->removeFlyer($event);
        }
        if ($flyer) {
            $this->rememberFlyerSource($event->fresh() ?? $event, $flyerUrl);
        }

        foreach ($passEventsKept as $ticketId => $ids) {
            $ticket = Ticket::where('event_id', $event->id)->where('is_deleted', false)->find($ticketId);

            if ($ticket && $ticket->is_pass && $ticket->pass_scope === 'specific_events'
                && array_values(array_map('intval', $ticket->pass_event_ids ?? [])) !== $ids) {
                Ticket::whereKey($ticketId)->update(['pass_event_ids' => json_encode($ids)]);
            }
        }

        $event->load(['roles', 'tickets', 'addons', 'parts']);

        return response()->json([
            'data' => $event->toApiData(),
            'meta' => ['message' => 'Event updated successfully'] + ($created === null ? [] : ['created' => $created]),
        ], 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Cancel an event: it keeps its row and its sales, stops being sold, leaves connected
     * calendars, and the people registered are told when the caller asks. An appointment booking
     * is cancelled through its sale, which frees the slot. EventLifecycleService::cancel() is the
     * whole of it, shared with the admin portal.
     */
    public function cancel(Request $request, $id)
    {
        [$event, $refusal] = $this->eventToActOn($id);
        if ($refusal) {
            return $refusal;
        }

        try {
            $request->validate([
                'notify_attendees' => 'nullable|boolean',
                'message' => 'nullable|string|max:'.EventLifecycleService::NOTE_LENGTH,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $outcome = app(EventLifecycleService::class)->cancel(
            $event,
            auth()->id(),
            notifyAttendees: $request->boolean('notify_attendees'),
            note: $request->input('message'),
        );

        return $this->lifecycleReply($event, match ($outcome) {
            EventLifecycleService::ALREADY_CANCELLED => 'Event was already cancelled',
            EventLifecycleService::BOOKING_CANCELLED => 'Appointment cancelled',
            default => 'Event cancelled successfully',
        });
    }

    /**
     * Undo a cancellation. Nobody is emailed, and a boost or an installment plan that the
     * cancellation stopped stays stopped.
     */
    public function restore(Request $request, $id)
    {
        [$event, $refusal] = $this->eventToActOn($id);
        if ($refusal) {
            return $refusal;
        }

        // A cancelled booking has given its slot back, and somebody else may hold it by now.
        if ($event->appointment_type_id) {
            return response()->json([
                'error' => 'An appointment booking cannot be restored. Its slot was freed when it was cancelled.',
            ], 422);
        }

        $outcome = app(EventLifecycleService::class)->restore($event, auth()->id());

        return $this->lifecycleReply(
            $event,
            $outcome === EventLifecycleService::NOT_CANCELLED ? 'Event was not cancelled' : 'Event restored successfully'
        );
    }

    /**
     * The event an action endpoint is about, or the refusal: the same three answers show(),
     * update() and destroy() give.
     *
     * @return array{0: ?Event, 1: ?\Illuminate\Http\JsonResponse}
     */
    private function eventToActOn($id): array
    {
        $event = Event::with('roles')->find(UrlUtils::decodeId($id));

        if (! $event) {
            return [null, response()->json(['error' => 'Event not found'], 404)];
        }

        if (! auth()->user()->canEditEvent($event)) {
            return [null, response()->json(['error' => 'Unauthorized'], 403)];
        }

        // Cancelling stops an event's installments, cancels its boosts and can mail every
        // buyer the caller's own words, and restoring undoes somebody's cancellation. Editing
        // rights reach every schedule the event is listed on, and any schedule can list a
        // public event (EventController::curate()). So these two are for the event's own
        // people: whoever made it, or an owner or admin of the schedule that owns it.
        if (! $this->runsTheEvent($event)) {
            return [null, response()->json(['error' => 'Only the event\'s own schedule can cancel or restore it. A schedule that lists the event can take it off its own list instead.'], 403)];
        }

        if (! $event->isPro()) {
            return [null, response()->json(['error' => 'API usage is limited to Pro accounts'], 403)];
        }

        return [$event, null];
    }

    /** The caller made the event, or is an owner or admin of the schedule that owns it. */
    private function runsTheEvent(Event $event): bool
    {
        $user = auth()->user();

        if ((int) $event->user_id === (int) $user->id) {
            return true;
        }

        return $event->creator_role_id !== null && $user->roles()
            ->where('roles.id', $event->creator_role_id)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->exists();
    }

    private function lifecycleReply(Event $event, string $message)
    {
        $event = $event->fresh(['roles', 'tickets', 'addons', 'parts']);

        return response()->json([
            'data' => $event->toApiData(),
            'meta' => ['message' => $message],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * The external_id a request carries: a number is taken as its digits (ids often are
     * numbers), and nothing or "" is no id. Spaces around it are already gone, and a blank one is
     * already null: the framework's TrimStrings and ConvertEmptyStringsToNull run on every
     * request, JSON bodies and query strings included. ApiEventExternalIdTest holds both, so an
     * API route taken out of either middleware fails there.
     *
     * @return array{0: ?string, 1: ?\Illuminate\Http\JsonResponse}
     */
    private function externalIdFrom(Request $request): array
    {
        $value = $request->input('external_id');

        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if ($value === null) {
            return [null, null];
        }

        if (! is_string($value) || mb_strlen($value) > 255) {
            return [null, response()->json([
                'error' => 'Validation failed',
                'errors' => ['external_id' => ['The external_id must be text of at most 255 characters.']],
            ], 422)];
        }

        return [$value === '' ? null : $value, null];
    }

    /**
     * Whether a create asked to update the event that already holds its external_id.
     *
     * Asked without an id it is refused, not ignored: there is nothing to match on, so a sync
     * that believes it is upserting would add every record again on every run.
     *
     * @return array{0: bool, 1: ?\Illuminate\Http\JsonResponse}
     */
    private function upsertAsked(Request $request, ?string $externalId): array
    {
        $value = $request->input('upsert');

        if ($value === null) {
            return [false, null];
        }

        $asked = is_scalar($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        $refuse = fn (string $field, string $message) => [false, response()->json([
            'error' => 'Validation failed',
            'errors' => [$field => [$message]],
        ], 422)];

        if ($asked === null) {
            return $refuse('upsert', 'The upsert field must be true or false.');
        }

        if ($asked && $externalId === null) {
            return $refuse('external_id', 'An upsert needs an external_id: it is what the event is matched on.');
        }

        return [$asked, null];
    }

    /** The event a schedule owns under this id, if it has one. Compared exactly, letter case included. */
    private function eventWithExternalId(int $roleId, string $externalId): ?Event
    {
        return Event::where('creator_role_id', $roleId)->where('external_id', $externalId)->first();
    }

    private function externalIdConflict(?Event $holder)
    {
        return response()->json(array_filter([
            'error' => 'An event with this external_id already exists on this schedule.',
            'data' => $holder ? ['id' => UrlUtils::encodeId($holder->id)] : null,
        ]), 409);
    }

    /** Hashed: the id can be 255 characters and a cache lock's key cannot. */
    private function externalIdLockKey(int $roleId, string $externalId): string
    {
        return 'api-event-external-id:'.$roleId.':'.sha1($externalId);
    }

    private function isDuplicateExternalId(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062
            && str_contains($e->getMessage(), 'events_creator_role_id_external_id_unique');
    }

    /**
     * An update that names external_id sets, changes or clears it on the event in hand (the
     * save that follows writes it). The value the event already has passes for anyone who may
     * update the event, because a client that reads the object and writes it back sends it.
     *
     * A different one is the owning schedule's to give. Whoever may edit an event includes an
     * admin of a schedule that merely lists it, and the id lives in the OWNER's namespace: a
     * curator that cleared or changed it would make the owner's next sync create a duplicate.
     */
    private function takeExternalId(Request $request, Event $event)
    {
        if (! $request->has('external_id')) {
            return null;
        }

        [$externalId, $refusal] = $this->externalIdFrom($request);
        if ($refusal) {
            return $refusal;
        }

        if ($externalId === $event->external_id) {
            return null;
        }

        $refuse = fn (string $message) => response()->json([
            'error' => 'Validation failed',
            'errors' => ['external_id' => [$message]],
        ], 422);

        if (! $event->creator_role_id) {
            return $refuse('This event has no owning schedule, so it cannot carry an external_id.');
        }

        $runsTheOwner = auth()->user()->roles()
            ->where('roles.id', $event->creator_role_id)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->exists();

        if (! $runsTheOwner) {
            return $refuse('Only an owner or admin of the schedule that owns this event can set its external_id.');
        }

        if ($externalId !== null) {
            $holder = $this->eventWithExternalId($event->creator_role_id, $externalId);

            if ($holder && $holder->id !== $event->id) {
                return $refuse('The event '.UrlUtils::encodeId($holder->id).' on the same schedule already has this external_id.');
            }
        }

        $event->external_id = $externalId;

        return null;
    }

    /**
     * `ends_at` is a second way to say how long an event is. An event stores a start and a
     * length, so the request leaves here with a `duration` and the save never sees `ends_at`.
     *
     * Both are UTC here, which is why this runs before the start is turned into the schedule's
     * wall-clock for saveEvent(): the length is the time that passes, and an event that runs
     * across a clock change must not gain or lose the hour.
     *
     * The event object carries both, so a client that reads an event and writes it back sends
     * both, and after it changed one of them they disagree. That is not an error: the one that
     * differs from what is stored is the one the caller changed. When neither differs (the
     * caller moved the start and sent the rest back) the length is kept, as it is for a start
     * moved on its own. Only when both were changed and say different things is there nothing
     * to choose between, and on a new event nothing is stored to compare with.
     *
     * A null `ends_at` says nothing: it is what an event with no length reads as. To take the
     * length off an event, send `duration` as null or 0.
     */
    private function takeEndsAt(Request $request, ?Event $event)
    {
        if (! $request->filled('ends_at')) {
            return null;
        }

        $refuse = fn (string $message) => response()->json([
            'error' => 'Validation failed',
            'errors' => ['ends_at' => [$message]],
        ], 422);

        $startsAt = $request->filled('starts_at') ? $request->input('starts_at') : $event?->starts_at;

        if (! $startsAt) {
            return $refuse('An end time needs a start. Send starts_at as well.');
        }

        $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->input('ends_at'), 'UTC');

        // The end as it was read, sent back untouched: it says nothing new. A client that reads
        // an event, moves its start to the next day and writes the object back sends the old end
        // with it, and the event moves whole. Asked before anything is measured, because that old
        // end is before the new start and would be refused as "must be after starts_at".
        if ($event && $event->endsAtUtc() === $end->format('Y-m-d H:i:s')) {
            return null;
        }

        $hours = ImportedTime::hoursBetween(Carbon::parse($startsAt, 'UTC'), $end);

        if ($hours === null) {
            return $refuse('The ends_at must be after starts_at.');
        }

        if ($hours > 8760) {
            return $refuse('An event can be at most 8760 hours long.');
        }

        if ($request->filled('duration')) {
            $duration = round((float) $request->input('duration'), 3);

            if (abs($duration - $hours) >= 0.0005) {
                $durationChanged = ! $event || abs(round((float) $event->duration, 3) - $duration) >= 0.0005;
                $endChanged = ! $event || $event->endsAtUtc() !== $end->format('Y-m-d H:i:s');

                if ($durationChanged && $endChanged) {
                    return $refuse('The ends_at and duration disagree: '.$request->input('ends_at').' is '.$hours.' hours after the start. Send one of them.');
                }

                if (! $endChanged) {
                    return null;
                }
            }
        }

        $request->merge(['duration' => $hours]);

        return null;
    }

    /**
     * The largest picture taken from an address. Under the 10 MB at which the guarded fetch cuts
     * a body off, so that a picture which is merely too big is told so: one past the fetch's own
     * cap is an aborted transfer, and reads as "could not be fetched".
     */
    private const FLYER_MAX_BYTES = 8 * 1024 * 1024;

    private const FLYER_FETCH_SECONDS = 10;

    /**
     * What a request says about the flyer through `flyer_image_url`: the address to fetch a new
     * one from, or that the flyer is to be removed, or nothing.
     *
     * The event object reports the flyer under this name, so a client that reads an event and
     * writes it back sends the address of the flyer the event already has. That is no change,
     * and must not be a download of our own file through the front door. Null removes the
     * flyer, which is also what an event without one reads as.
     *
     * Only judged here. The fetch waits until nothing else can refuse the request.
     *
     * @return array{0: ?string, 1: bool, 2: ?\Illuminate\Http\JsonResponse}
     */
    private function flyerAddressFrom(Request $request, ?Event $event): array
    {
        if (! $request->has('flyer_image_url')) {
            return [null, false, null];
        }

        $value = $request->input('flyer_image_url');

        if ($value === null || $value === '') {
            return [null, $event !== null, null];
        }

        $refuse = fn (string $message) => [null, false, response()->json([
            'error' => 'Validation failed',
            'errors' => ['flyer_image_url' => [$message]],
        ], 422)];

        if (! is_string($value) || strlen($value) > 2048 || ! preg_match('#^https?://#i', $value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return $refuse('The flyer_image_url must be an http or https address of at most 2048 characters.');
        }

        if ($event && $value === $event->flyer_image_url) {
            return [null, false, null];
        }

        // The address the flyer was last taken from, sent again: a sync that sends its own
        // picture address with every write. Nothing to fetch while the event still has the file
        // that fetch stored. Without this each call downloaded the picture again and replaced
        // the stored file, and a source picture that had since gone refused the whole write.
        $memory = $event ? Cache::get($this->flyerMemoryKey($event->id)) : null;
        if (is_array($memory)
            && hash_equals((string) ($memory['source'] ?? ''), sha1($value))
            && ($memory['file'] ?? null) !== null
            && $memory['file'] === ($event->getAttributes()['flyer_image_url'] ?? null)) {
            return [null, false, null];
        }

        return [$value, false, null];
    }

    private function flyerMemoryKey(int $eventId): string
    {
        return 'api-event-flyer-source:'.$eventId;
    }

    /**
     * Remember which address the event's flyer came from, and which file that made. Kept in the
     * cache, not on the event: losing it costs one more download, never a wrong picture, because
     * it only counts while the event still has that very file.
     */
    private function rememberFlyerSource(Event $event, ?string $url): void
    {
        $file = $event->getAttributes()['flyer_image_url'] ?? null;

        if ($url !== null && $file !== null) {
            Cache::put($this->flyerMemoryKey($event->id), ['source' => sha1($url), 'file' => $file], now()->addDays(90));
        }
    }

    /**
     * Fetch a flyer from an address and hand it back as a file saveEvent() can store. Through
     * the guard every outbound fetch uses, and judged by its bytes, not by what its address or
     * its server call it.
     *
     * @return array{0: ?UploadedFile, 1: ?\Illuminate\Http\JsonResponse}
     */
    private function fetchFlyer(?string $url): array
    {
        if ($url === null) {
            return [null, null];
        }

        $image = RemoteImage::read($url, self::FLYER_FETCH_SECONDS, self::FLYER_MAX_BYTES);

        if (isset($image['reason'])) {
            return [null, response()->json([
                'error' => 'Validation failed',
                'errors' => ['flyer_image_url' => [match ($image['reason']) {
                    RemoteImage::TOO_LARGE => 'The image is larger than 8 MB.',
                    RemoteImage::NOT_AN_IMAGE => 'The address did not return a JPEG, PNG, GIF or WebP image.',
                    default => 'The image could not be fetched. The address has to be public, answer within '.self::FLYER_FETCH_SECONDS.' seconds and send at most 8 MB.',
                }]],
            ], 422)];
        }

        $path = tempnam(sys_get_temp_dir(), 'flyer');

        if ($path === false || file_put_contents($path, $image['contents']) === false) {
            return [null, response()->json(['error' => 'The image could not be stored. Try again.'], 500)];
        }

        // "test" mode, as the import does for a picture it fetched: the file was never an HTTP
        // upload, and without it isValid() would say so.
        return [new UploadedFile($path, 'flyer.'.$image['extension'], null, null, true), null];
    }

    private function forgetFlyer(?UploadedFile $flyer): void
    {
        if ($flyer && is_file($flyer->getPathname())) {
            @unlink($flyer->getPathname());
        }
    }

    /**
     * `is_cancelled` is part of the event object, so a client that reads an event and writes it
     * back sends it. The stored value passes. A different one is refused: dropped in silence, a
     * script that set it would be told 200 and leave the event on, and applied here it would be a
     * cancellation with none of what a cancellation does.
     */
    private function refuseCancelledInBody(Request $request, bool $stored)
    {
        $sent = $request->input('is_cancelled');

        if ($sent === null || filter_var($sent, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === $stored) {
            return null;
        }

        return response()->json([
            'error' => 'Validation failed',
            'errors' => ['is_cancelled' => [
                'An event is cancelled with POST /api/events/{id}/cancel and brought back with POST /api/events/{id}/restore. It cannot be changed in the body of a create or an update.',
            ]],
        ], 422);
    }

    public function destroy(Request $request, $id)
    {
        $event = Event::with('roles')->find(UrlUtils::decodeId($id));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (! auth()->user()->canEditEvent($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $event->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        // Appointment bookings: cancel (frees the slot, keeps the Sale + refund trail) rather than
        // hard-delete, which would cascade-delete the Sale.
        if ($event->appointment_type_id) {
            app(EventLifecycleService::class)->cancelBooking($event);

            AuditService::log(AuditService::EVENT_CANCEL, auth()->id(), 'Event', $event->id, null, null, $event->name);

            return response()->json(['message' => 'Appointment cancelled']);
        }

        // sales.event_id cascades on delete, so this would destroy the buyers' sale rows outright:
        // no refund trail, no inventory released, nobody told. The admin portal has refused it
        // since EventController::delete() gained the same check; this endpoint went straight to
        // $event->delete(). Refunded and cancelled sales count too: they ARE the trail. One the
        // organizer removed from the Sales list does not (Event::sales() leaves those out), as
        // on the form.
        if ($event->sales()->exists()) {
            return response()->json([
                'error' => 'This event has sales and cannot be deleted. Cancel it instead with POST /api/events/{id}/cancel, so buyers keep their records and can be notified.',
            ], 422);
        }

        // The audit row, its boosts stopped, the webhook with what the event was, then the row.
        // Deleted on purpose: a feed that made it does not bring it back.
        \App\Models\EventFeedItem::dismissFor($event);
        app(EventLifecycleService::class)->delete($event, auth()->id());

        return response()->json([
            'data' => [
                'message' => 'Event deleted successfully',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function categories($subdomain = null)
    {
        if ($subdomain) {
            $role = Role::subdomain($subdomain)->where('is_deleted', false)->first();
            if (! $role) {
                return response()->json(['error' => 'Schedule not found'], 404);
            }
            $data = array_map(
                fn ($entry) => ['id' => $entry['id'], 'name' => $entry['name']],
                $role->getEventCategories()
            );

            return response()->json(['data' => $data], 200, [], JSON_PRETTY_PRINT);
        }

        $categories = config('app.event_categories', []);
        $data = [];
        foreach ($categories as $id => $name) {
            $data[] = ['id' => $id, 'name' => $name];
        }

        return response()->json([
            'data' => $data,
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function flyer(Request $request, $event_id)
    {
        $event = Event::with(['roles', 'tickets', 'addons', 'parts'])->find(UrlUtils::decodeId($event_id));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (! auth()->user()->canEditEvent($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $event->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        try {
            $request->validate([
                'flyer_image' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:10240',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // The same code the event form and a flyer given by address go through: the new file is
        // checked before the old one is deleted, and a poster larger than 2000px is resized.
        try {
            $this->eventRepo->storeFlyer($event, $request->file('flyer_image'));
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'data' => $event->toApiData(),
            'meta' => [
                'message' => 'Flyer uploaded successfully',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Pre-process request data for event create/update.
     * Handles venue resolution, member resolution, group/category name-to-ID conversion.
     */
    private function preprocessEventRequest(Request $request, Role $role, string $encodedRoleId)
    {
        // Set venue for venue schedules
        if ($role->isVenue()) {
            $request->merge(['venue_id' => $encodedRoleId]);
        }

        // Set member for talent schedules
        if ($role->isTalent()) {
            $request->merge(['members' => [$encodedRoleId => ['name' => $role->name]]]);
        }

        // Set curator
        if ($role->isCurator()) {
            $request->merge(['curators' => [$encodedRoleId]]);
        }

        // Handle group name to group_id conversion
        if ($request->has('schedule')) {
            $groupSlug = $request->schedule;
            $group = $role->groups()->where('slug', $groupSlug)->first();
            if ($group) {
                $request->merge(['current_role_group_id' => UrlUtils::encodeId($group->id)]);
            } else {
                return response()->json(['error' => 'Sub-schedule not found'], 422);
            }
        }

        // Handle category name to category_id conversion against this schedule's effective list.
        //
        // Matched on normalizeForMatch(), not Str::slug(): Str::slug returns "" for Hebrew, CJK and
        // anything else it cannot transliterate, so on a schedule with non-Latin categories every
        // name compared equal to every other and the first one always won - the API answered 201
        // with the event filed under a category the caller never asked for.
        if ($request->has('category') && ! $request->has('category_id')) {
            $wanted = GeminiUtils::normalizeForMatch($request->category);

            // An input that normalizes away entirely must not match anything.
            if ($wanted !== '') {
                foreach ($role->getEventCategories() as $entry) {
                    if (GeminiUtils::normalizeForMatch($entry['name']) === $wanted) {
                        $request->merge(['category_id' => $entry['id']]);
                        break;
                    }
                }
            }

            if (! $request->has('category_id')) {
                return response()->json(['error' => 'Category not found'], 422);
            }
        }

        // Resolve venue by name/address for non-venue schedules
        // filled(), not has(): a read-and-write-back sends both as null for an event with no venue,
        // and looking those up answered 422 "Venue not found: ".
        if (! $role->isVenue() && $request->filled('venue_address1') && $request->filled('venue_name')) {
            $roleIds = RoleUser::where('user_id', auth()->user()->id)
                ->whereIn('level', ['owner', 'follower'])
                ->orderBy('id')->pluck('role_id')->toArray();

            $venue = Role::where('name', $request->venue_name)
                ->where('address1', $request->venue_address1)
                ->where('type', 'venue')
                ->where('is_deleted', false)
                ->whereIn('id', $roleIds)
                ->orderBy('id')
                ->first();

            if ($venue) {
                $request->merge(['venue_id' => UrlUtils::encodeId($venue->id)]);
            } else {
                return response()->json([
                    'error' => 'Venue not found: '.$request->venue_name,
                ], 422);
            }
        }

        // Resolve members by name/email for non-talent schedules
        if (! $role->isTalent() && $request->has('members')) {
            $roleIds = RoleUser::where('user_id', auth()->user()->id)
                ->whereIn('level', ['owner', 'follower'])
                ->orderBy('id')->pluck('role_id')->toArray();

            $processedMembers = [];
            foreach ($request->members as $memberId => $memberData) {
                $talent = Role::where('is_deleted', false)
                    ->when(isset($memberData['name']), function ($q) use ($memberData) {
                        $q->where('name', $memberData['name']);
                    })
                    ->when(isset($memberData['email']), function ($q) use ($memberData) {
                        $q->where('email', $memberData['email']);
                    })
                    ->where('type', 'talent')
                    ->whereIn('id', $roleIds)
                    ->orderBy('id')
                    ->first();

                if ($talent) {
                    $processedMembers[UrlUtils::encodeId($talent->id)] = $memberData;
                } else {
                    return response()->json([
                        'error' => 'Talent member not found: '.($memberData['name'] ?? $memberData['email'] ?? 'unknown'),
                    ], 422);
                }
            }

            $request->merge(['members' => $processedMembers]);
        }
    }

    /**
     * A stored ticket type as the row saveEvent() needs to leave it exactly as it is.
     *
     * Every field saveEvent() writes has to be here. It reads an absent one as empty: Max Per
     * Order became unlimited and a pass became an ordinary ticket on any update that did not
     * mention tickets, because this row used to stop at the fields the API documents.
     */
    private function ticketRow(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'type' => $ticket->type,
            'quantity' => $ticket->quantity,
            // Omitted, EventRepo writes null - so a PATCH that only renames the event
            // would silently turn every allocated ticket into general admission while its
            // sold seats stayed bound to it.
            'seating_band' => $ticket->seating_band,
            'max_per_order' => $ticket->max_per_order,
            'price' => $ticket->price,
            'description' => $ticket->description,
            // The JSON string the form posts: saveEvent() json_decode()s it, and an array there
            // is a TypeError after the event row has already been written.
            'custom_fields' => $ticket->custom_fields ? json_encode($ticket->custom_fields) : null,
            'volume_discount' => $ticket->volume_discount,
            'sales_start_at' => $ticket->sales_start_at ? $ticket->sales_start_at->format('Y-m-d H:i:s') : null,
            'sales_end_at' => $ticket->sales_end_at ? $ticket->sales_end_at->format('Y-m-d H:i:s') : null,
            'is_pass' => (bool) $ticket->is_pass,
            'pass_usage_type' => $ticket->pass_usage_type,
            'pass_max_uses' => $ticket->pass_max_uses,
            'pass_valid_days' => $ticket->pass_valid_days,
            'pass_scope' => $ticket->pass_scope,
            // Encoded, both of them: saveEvent() decodes what the form sends.
            'pass_scope_group_id' => $ticket->pass_scope_group_id ? UrlUtils::encodeId($ticket->pass_scope_group_id) : null,
            'pass_event_ids' => array_map(fn ($id) => UrlUtils::encodeId($id), $ticket->pass_event_ids ?? []),
            'pass_allow_booking' => (bool) $ticket->pass_allow_booking,
            'pass_seats_per_occurrence' => $ticket->pass_seats_per_occurrence,
            'pass_cancel_cutoff_hours' => $ticket->pass_cancel_cutoff_hours,
            'pass_late_cancel_policy' => $ticket->pass_late_cancel_policy,
            'pass_admits_per_event' => $ticket->pass_admits_per_event,
        ];
    }

    /** The same for an add-on. Its picture is not in the row, so saveEvent() leaves it alone. */
    private function addonRow(Ticket $addon): array
    {
        return [
            'id' => $addon->id,
            'type' => $addon->type,
            'quantity' => $addon->quantity,
            'max_per_order' => $addon->max_per_order,
            'price' => $addon->price,
            'description' => $addon->description,
            'url' => $addon->url,
        ];
    }

    private function partRow(EventPart $part): array
    {
        return [
            'id' => $part->id,
            'name' => $part->name,
            'description' => $part->description,
            'start_time' => $part->start_time,
            'end_time' => $part->end_time,
        ];
    }

    /**
     * Keep the event on the venue, the performers and the schedules this request did not mention,
     * each in the state it was in.
     *
     * The event form posts all three on every save: the venue field, the participants, and the
     * "Also list on" boxes. saveEvent() then detaches whatever the saving user can see and the
     * request does not list. An API update that carried none of them took the event off its
     * venue, its other performers and the owner's own curator schedules, by renaming it.
     *
     * So this posts what is attached. Each venue and performer goes in curators[] as well as in
     * its own field, because without the form's venue_submitted / members_submitted markers
     * saveEvent() only keeps an attached schedule that curators[] names: naming the venue the
     * event already had used to detach it.
     *
     * Kept is not accepted. Every schedule saveEvent() is handed goes through its accept loop,
     * which answers for any schedule the caller manages, any nobody has claimed and any that
     * takes requests without approval, so carrying a venue that had turned the event down
     * accepted it. acceptance_kept_for names the schedules carried here, and saveEvent() leaves
     * their answer alone. The schedule the update runs through is not one of them: saving an
     * event from its own schedule has always accepted it there.
     *
     * A venue the caller NAMES is different. venue_submitted is the form's way of saying "the
     * venue field was on the page", and it is what takes the event off the venue it was at when
     * that one is not a schedule the caller can see (a venue typed into a form, which nobody
     * owns, is the usual kind): without it the event ended up at both.
     */
    private function keepAttachments(Request $request, Event $event, Role $currentRole, bool $sentVenue, bool $sentMembers): void
    {
        $listed = array_values((array) $request->input('curators', []));
        $kept = [];

        if (! $currentRole->isVenue()) {
            if ($sentVenue) {
                $request->merge(['venue_submitted' => 1]);
            } elseif ($venue = $event->roles->first(fn (Role $role) => $role->isVenue() && ! $role->is_deleted)) {
                $request->merge(['venue_id' => UrlUtils::encodeId($venue->id)]);
                $kept[] = $venue->id;
            }
        }

        if ($request->input('venue_id')) {
            $listed[] = $request->input('venue_id');
        }

        $members = (array) $request->input('members', []);

        if (! $sentMembers) {
            foreach ($event->members() as $member) {
                $memberId = UrlUtils::encodeId($member->id);

                if ($member->is_deleted || $member->id === $currentRole->id || array_key_exists($memberId, $members)) {
                    continue;
                }

                $members[$memberId] = ['name' => $member->name];
                $kept[] = $member->id;
            }

            $request->merge(['members' => $members]);
        }

        foreach (array_keys($members) as $memberId) {
            if ($memberId && ! str_starts_with((string) $memberId, 'new_')) {
                $listed[] = (string) $memberId;
            }
        }

        $visible = auth()->user()->availableEventSchedules()->pluck('id')->all();

        foreach ($event->roles as $role) {
            if (! $role->isCurator() || $role->id === $currentRole->id || ! in_array($role->id, $visible)) {
                continue;
            }

            $listed[] = UrlUtils::encodeId($role->id);
            $kept[] = $role->id;
        }

        $request->merge([
            'curators' => array_values(array_unique($listed)),
            'acceptance_kept_for' => array_values(array_unique($kept)),
        ]);
    }

    /**
     * The zone a starts_at wall-clock is expressed in for this schedule.
     *
     * ?: rather than ??: roles.timezone and users.timezone are nullable strings, and an empty one
     * is a DateTimeZone error rather than a fallback (same reasoning as Event::scheduleTimezone()).
     */
    private function scheduleTimezoneFor(Role $role): string
    {
        return $role->timezone ?: auth()->user()->timezone ?: config('app.timezone');
    }

    /**
     * Convert days_of_week string (e.g. "0101010") to individual checkbox params
     * that saveEvent() expects (days_of_week_0 through days_of_week_6).
     */
    private function convertDaysOfWeek(Request $request)
    {
        if ($request->has('days_of_week') && $request->days_of_week) {
            $days = str_split($request->days_of_week);
            foreach ($days as $index => $day) {
                if ($day === '1') {
                    $request->merge(['days_of_week_'.$index => 'on']);
                }
            }
        }
    }
}
