<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use App\Models\TicketWaitlist;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function join(Request $request, $subdomain)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'event_id' => 'required|string',
            'event_date' => 'required|date_format:Y-m-d',
        ]);

        $eventId = UrlUtils::decodeId($request->event_id);
        $event = Event::with('tickets')->findOrFail($eventId);

        // Verify event belongs to this schedule
        $role = Role::subdomain($subdomain)->firstOrFail();
        if (! $event->roles()->wherePivot('role_id', $role->id)->exists()) {
            abort(403);
        }

        // Only allow joining waitlist if actually sold out
        if ($event->rsvp_enabled) {
            if (! $event->isRsvpFull($request->event_date)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.registration_available'),
                ]);
            }
        } else {
            if (! $event->allTicketsSoldOut($request->event_date)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.tickets_available'),
                ]);
            }
        }

        // Check for existing active entry (waiting or notified)
        $existing = TicketWaitlist::where('event_id', $eventId)
            ->where('event_date', $request->event_date)
            ->where('email', $request->email)
            ->whereIn('status', ['waiting', 'notified'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => __('messages.waitlist_already_joined'),
            ]);
        }

        // Delete any expired or purchased entries for this email/event/date so re-joining works
        TicketWaitlist::where('event_id', $eventId)
            ->where('event_date', $request->event_date)
            ->where('email', $request->email)
            ->whereIn('status', ['expired', 'purchased'])
            ->delete();

        try {
            TicketWaitlist::create([
                'event_id' => $eventId,
                'event_date' => $request->event_date,
                'name' => strip_tags($request->name),
                'email' => $request->email,
                'subdomain' => $subdomain,
                'status' => 'waiting',
                'locale' => app()->getLocale(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'success' => true,
                    'message' => __('messages.waitlist_already_joined'),
                ]);
            }
            throw $e;
        }

        $message = $event->rsvp_enabled
            ? __('messages.waitlist_rsvp_joined')
            : __('messages.waitlist_joined');

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $filter = strtolower($request->filter ?? '');
        $includePast = $request->query('include_past') == 1;

        $query = TicketWaitlist::with('event')
            ->whereHas('event', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereIn('status', ['waiting', 'notified']);

        if (! $includePast) {
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('event_date', '>=', now()->subDay()->startOfDay())
                        ->whereHas('event', function ($eq) {
                            $eq->where('starts_at', '>=', now()->subDay()->startOfDay());
                        });
                })->orWhereHas('event', function ($eq) {
                    $eq->where('duration', '>=', 24)
                        ->whereRaw('DATE_ADD(starts_at, INTERVAL duration HOUR) >= ?', [now()]);
                });
            });
        }

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'LIKE', "%{$filter}%")
                    ->orWhere('email', 'LIKE', "%{$filter}%")
                    ->orWhereHas('event', function ($q) use ($filter) {
                        $q->where('name', 'LIKE', "%{$filter}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSortColumns = ['name', 'email', 'status', 'created_at', 'event_date', 'event_name'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        if ($sortBy === 'event_name') {
            $query->orderBy(
                Event::select('name')->whereColumn('events.id', 'ticket_waitlists.event_id'),
                $sortDir
            );
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $entries = $query->paginate(50, ['*'], 'page')->withQueryString();

        if ($request->ajax()) {
            return view('ticket.waitlist_table', compact('entries', 'sortBy', 'sortDir'));
        }

        return view('ticket.waitlist_table', compact('entries', 'sortBy', 'sortDir'));
    }

    public function remove($id)
    {
        $id = UrlUtils::decodeId($id);
        $user = auth()->user();

        $entry = TicketWaitlist::whereHas('event', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $entry->delete();

        return response()->json(['success' => true]);
    }
}
