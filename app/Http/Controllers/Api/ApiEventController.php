<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use App\Repos\EventRepo;
use App\Services\AuditService;
use App\Utils\UrlUtils;
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
        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $events = Event::with(['roles', 'tickets', 'parts'])
            ->where('user_id', auth()->id());

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
        $event = Event::with(['roles', 'tickets', 'parts'])->find(UrlUtils::decodeId($id));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        // Auth: user must own the event or be owner/admin on one of its roles
        if (! auth()->user()->canEditEvent($event)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'data' => $event->toApiData(),
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(Request $request, $subdomain)
    {
        $role = Role::with('groups')->subdomain($subdomain)->first();

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        $encodedRoleId = UrlUtils::encodeId($role->id);

        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        // Check for owner/admin level
        $userRole = auth()->user()->roles()
            ->where('subdomain', $subdomain)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->first();

        if (! $userRole) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'starts_at' => 'required|date_format:Y-m-d H:i:s',
                'duration' => 'nullable|numeric|min:0|max:24',
                'description' => 'nullable|string|max:10000',
                'short_description' => 'nullable|string|max:500',
                'event_url' => 'nullable|url|max:255',
                'event_password' => 'nullable|string|max:255',
                'registration_url' => 'nullable|url|max:255',
                'category_id' => 'nullable|integer',
                'category' => 'nullable|string|max:255',
                'tickets_enabled' => 'nullable|boolean',
                'ticket_currency_code' => 'nullable|string|size:3',
                'payment_method' => 'nullable|string|in:stripe,invoiceninja,payment_url,manual',
                'payment_instructions' => 'nullable|string|max:5000',
                'schedule_type' => 'nullable|string|in:single,recurring',
                'recurring_frequency' => 'nullable|string|in:daily,weekly,every_n_weeks,monthly_date,monthly_weekday,yearly',
                'recurring_interval' => 'nullable|integer|min:2',
                'recurring_end_type' => 'nullable|string|in:never,on_date,after_events',
                'recurring_end_value' => 'nullable|string',
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
                'event_parts' => 'nullable|array',
                'event_parts.*.name' => 'required_with:event_parts|string|max:255',
                'event_parts.*.description' => 'nullable|string|max:1000',
                'event_parts.*.start_time' => 'nullable|string',
                'event_parts.*.end_time' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Strip request to only allowed fields to prevent mass assignment
        $request->replace($request->only([
            'name', 'starts_at', 'duration', 'description', 'short_description',
            'event_url', 'event_password', 'registration_url',
            'category_id', 'category', 'tickets_enabled', 'ticket_currency_code',
            'payment_method', 'payment_instructions',
            'schedule_type', 'recurring_frequency', 'recurring_interval',
            'recurring_end_type', 'recurring_end_value',
            'venue_id', 'venue_name', 'venue_address1',
            'members', 'schedule', 'tickets', 'event_parts',
        ]));

        // Pre-processing: venue, members, group, category
        $this->preprocessEventRequest($request, $role, $encodedRoleId);

        $event = $this->eventRepo->saveEvent($role, $request, null);

        $event->load(['roles', 'tickets', 'parts']);

        return response()->json([
            'data' => $event->toApiData(),
            'meta' => [
                'message' => 'Event created successfully',
            ],
        ], 201, [], JSON_PRETTY_PRINT);
    }

    public function update(Request $request, $id)
    {
        $event = Event::with(['roles', 'tickets', 'parts'])->find(UrlUtils::decodeId($id));

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
                'name' => 'sometimes|required|string|max:255',
                'starts_at' => 'sometimes|required|date_format:Y-m-d H:i:s',
                'duration' => 'nullable|numeric|min:0|max:24',
                'description' => 'nullable|string|max:10000',
                'short_description' => 'nullable|string|max:500',
                'event_url' => 'nullable|url|max:255',
                'event_password' => 'nullable|string|max:255',
                'registration_url' => 'nullable|url|max:255',
                'category_id' => 'nullable|integer',
                'category' => 'nullable|string|max:255',
                'tickets_enabled' => 'nullable|boolean',
                'ticket_currency_code' => 'nullable|string|size:3',
                'payment_method' => 'nullable|string|in:stripe,invoiceninja,payment_url,manual',
                'payment_instructions' => 'nullable|string|max:5000',
                'schedule_type' => 'nullable|string|in:single,recurring',
                'recurring_frequency' => 'nullable|string|in:daily,weekly,every_n_weeks,monthly_date,monthly_weekday,yearly',
                'recurring_interval' => 'nullable|integer|min:2',
                'recurring_end_type' => 'nullable|string|in:never,on_date,after_events',
                'recurring_end_value' => 'nullable|string',
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
                'event_parts' => 'nullable|array',
                'event_parts.*.name' => 'required_with:event_parts|string|max:255',
                'event_parts.*.description' => 'nullable|string|max:1000',
                'event_parts.*.start_time' => 'nullable|string',
                'event_parts.*.end_time' => 'nullable|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Strip request to only allowed fields to prevent mass assignment
        $request->replace($request->only([
            'name', 'starts_at', 'duration', 'description', 'short_description',
            'event_url', 'event_password', 'registration_url',
            'category_id', 'category', 'tickets_enabled', 'ticket_currency_code',
            'payment_method', 'payment_instructions',
            'schedule_type', 'recurring_frequency', 'recurring_interval',
            'recurring_end_type', 'recurring_end_value',
            'venue_id', 'venue_name', 'venue_address1',
            'members', 'schedule', 'tickets', 'event_parts',
        ]));

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
            $currentRole = $event->roles->first();
        }

        if (! $currentRole) {
            return response()->json(['error' => 'No schedule found for this event that you have access to'], 422);
        }

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
            // Preserve days_of_week checkboxes
            if ($event->days_of_week) {
                $days = str_split($event->days_of_week);
                foreach ($days as $index => $day) {
                    if ($day === '1') {
                        $request->merge(['days_of_week_'.$index => 'on']);
                    }
                }
            }
        }

        // Preserve starts_at in user's local format if not being changed
        if (! $request->has('starts_at')) {
            $request->merge(['starts_at' => $event->localStartsAt(false)]);
        }

        // Preserve tickets_enabled if not being changed
        if (! $request->has('tickets_enabled')) {
            $request->merge(['tickets_enabled' => $event->tickets_enabled]);
        }

        // Preserve existing tickets if not being changed
        if (! $request->has('tickets') && $event->tickets_enabled) {
            $existingTickets = $event->tickets->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'type' => $ticket->type,
                    'quantity' => $ticket->quantity,
                    'price' => $ticket->price,
                    'description' => $ticket->description,
                ];
            })->toArray();
            $request->merge(['tickets' => $existingTickets]);
        }

        // Preserve existing event parts if not being changed
        if (! $request->has('event_parts')) {
            $existingParts = $event->parts->map(function ($part) {
                return [
                    'id' => $part->id,
                    'name' => $part->name,
                    'description' => $part->description,
                    'start_time' => $part->start_time,
                    'end_time' => $part->end_time,
                ];
            })->toArray();
            $request->merge(['event_parts' => $existingParts]);
        }

        // Pre-processing: venue, members, group, category
        $currentRole->loadMissing('groups');
        $this->preprocessEventRequest($request, $currentRole, $encodedRoleId);

        $event = $this->eventRepo->saveEvent($currentRole, $request, $event);

        $event->load(['roles', 'tickets', 'parts']);

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

        AuditService::log(AuditService::EVENT_DELETE, auth()->id(), 'Event', $event->id, null, null, $event->name);

        $event->delete();

        return response()->json([
            'data' => [
                'message' => 'Event deleted successfully',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function categories()
    {
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
        $event = Event::find(UrlUtils::decodeId($event_id));

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if ($event->user_id !== auth()->id()) {
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
        $filename = strtolower('flyer_'.Str::random(32).'.'.$file->getClientOriginalExtension());
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
            }
        }

        // Handle category name to category_id conversion
        if ($request->has('category') && ! $request->has('category_id')) {
            $categorySlug = Str::slug($request->category);
            $categories = config('app.event_categories', []);

            foreach ($categories as $id => $name) {
                if (Str::slug($name) === $categorySlug) {
                    $request->merge(['category_id' => $id]);
                    break;
                }
            }
        }

        // Resolve venue by name/address for non-venue schedules
        if (! $role->isVenue() && $request->has('venue_address1') && $request->has('venue_name')) {
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
                    ->where(function ($query) use ($memberData) {
                        $query->when(isset($memberData['name']), function ($q) use ($memberData) {
                            $q->where('name', $memberData['name']);
                        })
                            ->when(isset($memberData['email']), function ($q) use ($memberData) {
                                $q->orWhere('email', $memberData['email']);
                            });
                    })
                    ->where('type', 'talent')
                    ->whereIn('id', $roleIds)
                    ->orderBy('id')
                    ->first();

                if ($talent) {
                    $processedMembers[UrlUtils::encodeId($talent->id)] = $memberData;
                } else {
                    $processedMembers[] = $memberData;
                }
            }

            $request->merge(['members' => $processedMembers]);
        }
    }
}
