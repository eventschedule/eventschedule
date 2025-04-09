<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Role;
use App\Repos\EventRepo;
use Illuminate\Http\Request;
use App\Utils\UrlUtils;

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

        $events = Event::where('user_id', auth()->id())->paginate($perPage);

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
        $role = Role::subdomain($subdomain)->firstOrFail();
        $encodedRoleId = UrlUtils::encodeId($role->id);
        
        if ($role->isVenue()) {
            $request->merge(['venue_id' => $encodedRoleId]);
        } else if ($role->isTalent()) {
            $request->merge(['members' => [$encodedRoleId => ['name' => $role->name]]]);
        }   

        $curatorId = $role->isCurator() ? $role->id : null;
                        
        if ($role->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'starts_at' => 'required|date',
            'venue_id' => 'required_without_all:venue_address1,event_url',
            'venue_address1' => 'required_without_all:venue_id,event_url',
            'event_url' => 'required_without_all:venue_id,venue_address1'
        ]);
        
        $event = $this->eventRepo->saveEvent($request, null, $curatorId);
                
        return response()->json([
            'data' => $event->toApiData(),
            'meta' => [
                'message' => 'Event created successfully'
            ]
        ], 201, [], JSON_PRETTY_PRINT);
    }
}