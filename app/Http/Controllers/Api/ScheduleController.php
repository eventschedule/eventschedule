<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Event;
use App\Repos\EventRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

class ScheduleController extends Controller
{
    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function index()
    {
        $schedules = Role::where('user_id', auth()->id())
                        ->where('type', 'schedule')
                        ->get();
        return response()->json([
            'schedules' => $schedules->map(function($schedule) {
                return [
                    'id' => $schedule->id,
                    'name' => $schedule->name,
                    'type' => $schedule->type,
                    'description' => $schedule->description,
                ];
            })
        ]);
    }

    public function events($scheduleId)
    {
        $schedule = Role::findOrFail($scheduleId);
        
        if ($schedule->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $events = Event::whereHas('roles', function($query) use ($scheduleId) {
            $query->where('role_id', $scheduleId);
        })->get();

        return response()->json([
            'events' => $events->map(function($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->name,
                    'description' => $event->description,
                    'start_time' => $event->starts_at,
                    'end_time' => $event->ends_at,
                    'location' => $event->location,
                ];
            })
        ]);
    }

    public function storeEvent(Request $request, $scheduleId)
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
            'role_type' => 'schedule'
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
            'message' => 'Event created successfully',
            'event' => [
                'id' => $event->id,
                'title' => $event->name,
                'description' => $event->description,
                'start_time' => $event->starts_at,
                'end_time' => $event->ends_at,
                'location' => $event->location,
            ]
        ], 201);
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