<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiCheckInController extends Controller
{
    /**
     * Record a check-in.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'attendee_name' => 'required|string|max:255',
            'attendee_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        // Verify user has access to this event
        $event = Event::findOrFail($validated['event_id']);
        
        if ($event->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $checkIn = CheckIn::create([
            'event_id' => $validated['event_id'],
            'attendee_name' => $validated['attendee_name'],
            'attendee_email' => $validated['attendee_email'] ?? null,
            'checked_in_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'id' => $checkIn->id,
            'event_id' => $checkIn->event_id,
            'attendee_name' => $checkIn->attendee_name,
            'attendee_email' => $checkIn->attendee_email,
            'checked_in_at' => $checkIn->checked_in_at->toIso8601String(),
            'notes' => $checkIn->notes,
        ], 201);
    }

    /**
     * List check-ins for an event.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
        ]);

        // Verify user has access to this event
        $event = Event::findOrFail($validated['event_id']);
        
        if ($event->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $checkIns = CheckIn::where('event_id', $validated['event_id'])
            ->orderByDesc('checked_in_at')
            ->get();

        return response()->json([
            'data' => $checkIns->map(function ($checkIn) {
                return [
                    'id' => $checkIn->id,
                    'event_id' => $checkIn->event_id,
                    'attendee_name' => $checkIn->attendee_name,
                    'attendee_email' => $checkIn->attendee_email,
                    'checked_in_at' => $checkIn->checked_in_at->toIso8601String(),
                    'notes' => $checkIn->notes,
                ];
            })->values()->all(),
        ]);
    }
}
