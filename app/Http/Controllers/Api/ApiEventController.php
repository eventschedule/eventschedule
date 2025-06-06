<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use App\Repos\EventRepo;
use Illuminate\Http\Request;
use App\Utils\UrlUtils;
use Illuminate\Support\Str;

class ApiEventController extends Controller
{
    protected const MAX_PER_PAGE = 1000;
    protected const DEFAULT_PER_PAGE = 100;

    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function index(Request $request)
    {
        /*
        $schedule = Role::findOrFail($scheduleId);
        
        if ($schedule->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        */

        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $events = Event::with(['roles', 'venue'])
                    ->where('user_id', auth()->id())
                    ->paginate($perPage);

        /*
        $events = Event::whereHas('roles', function($query) use ($scheduleId) {
            $query->where('role_id', $scheduleId);
        })->paginate($perPage);
        */

        return response()->json([
            'data' => $events->map(function($event) {
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
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(Request $request, $subdomain)
    {
        $role = Role::with('groups')->subdomain($subdomain)->firstOrFail();
        $encodedRoleId = UrlUtils::encodeId($role->id);
        
        if ($role->isVenue()) {
            $request->merge(['venue_id' => $encodedRoleId]);
        } else if ($role->isTalent()) {
            $request->merge(['members' => [$encodedRoleId => ['name' => $role->name]]]);
        }   

        $curatorId = $role->isCurator() ? $role->id : null;
            
        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'starts_at' => 'required|date_format:Y-m-d H:i:s',
            'venue_id' => 'required_without_all:venue_address1,event_url',
            'venue_address1' => 'required_without_all:venue_id,event_url',
            'event_url' => 'required_without_all:venue_id,venue_address1',
            'members' => 'array',
            'schedule' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
        ]);

        // Handle group name to group_id conversion
        if ($request->has('schedule')) {
            $groupSlug = $request->schdule;
            $group = $role->groups()->where('slug', $groupSlug)->first();
            if ($group) {
                $request->merge(['group_id' => $group->id]);
            } else {
                return response()->json(['error' => 'Schedule not found: ' . $request->schedule], 422);
            }
        }

        // Handle category name to category_id conversion
        if ($request->has('category_name') && !$request->has('category_id')) {
            $categorySlug = Str::slug($request->category_name);
            $categories = config('app.event_categories', []);
            $categoryId = null;
            
            foreach ($categories as $id => $name) {
                if (Str::slug($name) === $categorySlug) {
                    $categoryId = $id;
                    break;
                }
            }
            
            if ($categoryId) {
                $request->merge(['category_id' => $categoryId]);
            } else {
                return response()->json(['error' => 'Category not found: ' . $request->category_name], 422);
            }
        }

        $roleIds = RoleUser::where('user_id', auth()->user()->id)
                        ->whereIn('level', ['owner', 'follower'])
                        ->orderBy('id')->pluck('role_id')->toArray();

        if ($role->isVenue()) {
            $request->merge(['venue_id' => $encodedRoleId]);
        } else if ($request->has('venue_address1') && $request->has('venue_name')) {
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

        if ($role->isTalent()) {
            $request->merge(['members' => [$encodedRoleId => ['name' => $role->name]]]);
        } else if ($request->has('members')) {

            $processedMembers = [];
            foreach ($request->members as $memberId => $memberData) {

                $talent = Role::where('is_deleted', false)
                    ->where(function($query) use ($memberData) {
                        $query->when(isset($memberData['name']), function($q) use ($memberData) {
                                $q->where('name', $memberData['name']);
                            })
                            ->when(isset($memberData['email']), function($q) use ($memberData) {
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

        if ($role->isCurator()) {
            $request->merge(['curators' => [$encodedRoleId]]);
        }

        $event = $this->eventRepo->saveEvent($request, null, $curatorId);
                
        return response()->json([
            'data' => $event->toApiData(),
            'meta' => [
                'message' => 'Event created successfully'
            ]
        ], 201, [], JSON_PRETTY_PRINT);
    }
}