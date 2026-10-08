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
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        $allowedCategoryIds = collect($role->getEventCategories())->pluck('id')->all();

        // From the gateway registry, so a new gateway is accepted here without touching this file.
        // 'manual' is kept as the long-standing alias for cash and normalised below.
        $allowedPaymentMethods = array_merge(payment_gateways()->selectableKeys(), ['manual']);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'starts_at' => 'required|date_format:Y-m-d H:i:s',
                'duration' => 'nullable|numeric|min:0|max:8760',
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

        $event = $this->eventRepo->saveEvent($role, $request, null, true, $scheduleTz);

        $event->load(['roles', 'tickets', 'addons', 'parts']);

        return response()->json([
            'data' => $event->toApiData(),
            'meta' => [
                'message' => 'Event created successfully',
            ],
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

        // Determine the current role from the event's roles (first where user is owner/admin)
        $currentRole = null;
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

        $event = $this->eventRepo->saveEvent($currentRole, $request, $event, true, $scheduleTz);

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
            'meta' => [
                'message' => 'Event updated successfully',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
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
                'error' => 'This event has sales and cannot be deleted. Cancel it in the admin portal instead, so buyers keep their records and can be notified.',
            ], 422);
        }

        // The audit row, its boosts stopped, the webhook with what the event was, then the row.
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

        if ($event->flyer_image_url) {
            $path = $event->getAttributes()['flyer_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/'.$path;
            }
            Storage::delete($path);
        }

        $file = $request->file('flyer_image');
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions)) {
            return response()->json(['error' => 'Invalid file type'], 422);
        }
        $filename = strtolower('flyer_'.Str::random(32).'.'.$extension);
        $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

        $event->flyer_image_url = $filename;
        $event->save();

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
