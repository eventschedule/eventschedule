<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Repos\EventRepo;

class ApiEventController extends Controller
{
    protected const MAX_PER_PAGE = 1000;
    protected const DEFAULT_PER_PAGE = 100;

    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function index(Request $request, $scheduleId)
    {
        $schedule = Role::findOrFail($scheduleId);
        
        if ($schedule->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $events = Event::whereHas('roles', function($query) use ($scheduleId) {
            $query->where('role_id', $scheduleId);
        })->paginate($perPage);

        return response()->json([
            'data' => $events->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'description' => $event->description,
                    'start_time' => $event->starts_at,
                    'end_time' => $event->ends_at,
                    'location' => $event->location,
                ];
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

    public function store(Request $request, $scheduleId)
    {
        $schedule = Role::findOrFail($scheduleId);
        
        if ($schedule->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $eventData = [
            'members' => [
                'new_1' => [
                    'name' => $schedule->name,
                    'email' => $schedule->email
                ]
            ],
            'role_type' => 'talent'
        ];

        if ($request->hasFile('flyer')) {
            $eventData['flyer_image_url'] = $request->file('flyer');
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'starts_at' => 'required|date',
                'ends_at' => 'required|date|after:starts_at',
                'location' => 'required|string',
            ]);

            $eventData = array_merge($eventData, $validated);
        }

        $event = $this->eventRepo->saveEvent($request, null, null);

        return response()->json([
            'data' => [
                'id' => $event->id,
                'title' => $event->name,
                'description' => $event->description,
                'start_time' => $event->starts_at,
                'end_time' => $event->ends_at,
                'location' => $event->location,
            ],
            'meta' => [
                'message' => 'Event created successfully'
            ]
        ], 201, [], JSON_PRETTY_PRINT);
    }

    private function extractEventDetails($text)
    {
        // This is a placeholder - implement actual text parsing logic
        return [
            'title' => 'Event Title',
            'description' => 'Event Description',
            'start_time' => now(),
            'end_time' => now()->addHours(2),
            'location' => 'Event Location',
        ];
    }
}