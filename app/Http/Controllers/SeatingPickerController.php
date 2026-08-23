<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\Ticket;
use App\Services\BestAvailableService;
use App\Services\SeatHoldService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

/**
 * Guest-facing seat map: read the plan, hold seats, let them go.
 *
 * PRIVACY: a guest is never told who holds a seat, why it is blocked, or an internal hold note.
 * Every seat collapses to available / mine / taken before it leaves here.
 */
class SeatingPickerController extends Controller
{
    public function __construct(
        private SeatingMapService $maps,
        private SeatHoldService $holds,
        private BestAvailableService $best,
    ) {}

    /**
     * The whole map, or - with ?since= - only what has changed.
     *
     * The diff is what makes a few-second poll cheap enough to leave running on a busy on-sale:
     * a 2,000-seat house is a large payload to resend every five seconds when usually nothing or
     * almost nothing has moved.
     */
    public function state(Request $request, $subdomain)
    {
        [$event, $date] = $this->resolveEvent($request, $subdomain);

        // Gated like hold(). Without this an unauthenticated GET could MATERIALIZE a map, so a
        // crawler walking a recurring event's dates would create hundreds of snapshots for dates
        // that will never sell. Materializing here is still right for a sellable date - that is the
        // lazy path, and a real buyer has turned up - it just must not happen for anything else.
        if (! $event->canSellTickets($date)) {
            return response()->json(['error' => __('messages.tickets_not_available')], 422);
        }

        $map = $this->maps->materialize($event, $date);

        if (! $map) {
            return response()->json(['error' => __('messages.seating_no_map')], 404);
        }

        $token = $this->holds->tokenFor($request);
        $since = (int) $request->input('since', 0);

        if ($since > 0) {
            // inLiveSection() so the poll cannot report seats from a section the organizer has
            // removed - the full payload filters them out, so the two used to disagree.
            $changed = SeatingSeat::where('event_seating_map_id', $map->id)
                ->where('state_version', '>', $since)
                ->inLiveSection()
                ->get(['id', 'status', 'hold_token', 'hold_expires_at']);

            return response()->json([
                'version' => (int) $map->version,
                'seats' => $changed->map(fn ($s) => [
                    'id' => $s->id,
                    'state' => $this->guestState($s, $token),
                ])->values(),
            ]);
        }

        return response()->json($this->fullPayload($map, $token));
    }

    /**
     * Hold exactly the posted seats, or the best available block for a ticket.
     *
     * Replaces whatever the caller was holding, because the picker posts the whole selection every
     * time it changes.
     */
    public function hold(Request $request, $subdomain)
    {
        [$event, $date] = $this->resolveEvent($request, $subdomain);

        if (! $event->canSellTickets($date)) {
            return response()->json(['error' => __('messages.tickets_not_available')], 422);
        }

        $map = $this->maps->materialize($event, $date);
        if (! $map) {
            return response()->json(['error' => __('messages.seating_no_map')], 404);
        }

        $token = $this->holds->tokenFor($request);
        $seatIds = $this->requestedSeatIds($request, $event, $map);

        if ($seatIds === null) {
            return response()->json(['error' => __('messages.seating_seat_unavailable')], 422);
        }

        try {
            $result = $this->holds->acquire($map, $seatIds, $token);
        } catch (BusinessException $e) {
            // The seat's own name is in the message on purpose: the picker drops that one seat and
            // keeps the rest of the selection rather than failing the whole thing.
            return response()->json([
                'error' => $e->getMessage(),
                'state' => $this->fullPayload($map->fresh(), $token),
            ], 409);
        } catch (\Illuminate\Database\QueryException $e) {
            // Two requests from the same session (the picker posts on every click, so a
            // double-click is ordinary) can deadlock on the seat rows. That is a retryable
            // conflict, not a server fault - a 500 left the picker showing a hard error for
            // something the next click would have fixed.
            report($e);

            return response()->json([
                'error' => __('messages.seating_hold_failed'),
                'state' => $this->fullPayload($map->fresh(), $token),
            ], 409);
        }

        return response()->json([
            'held' => $result['seat_ids'],
            'expires_at' => $result['expires_at']->toIso8601String(),
            'version' => $result['version'],
        ]);
    }

    /**
     * Either an explicit seat list or a best-available request. Explicit ids are filtered to this
     * map, so a hand-posted id from another event resolves to nothing rather than being held.
     */
    private function requestedSeatIds(Request $request, Event $event, EventSeatingMap $map): ?array
    {
        if ($request->filled('ticket_id') && $request->filled('quantity')) {
            $ticket = $event->tickets->firstWhere('id', UrlUtils::decodeId($request->input('ticket_id')));
            if (! $ticket) {
                return null;
            }

            $ticket->setRelation('event', $event);
            $qty = max(0, min((int) $request->input('quantity'), $this->perOrderCap($ticket)));

            return $this->best->pick($map, $ticket, $qty);
        }

        $posted = $request->input('seat_ids', []);
        if (! is_array($posted)) {
            return null;
        }

        $ids = array_slice(array_map('intval', $posted), 0, 200);

        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->whereIn('id', $ids)
            ->inLiveSection()
            ->orderBy('id')
            ->get(['id', 'seating_section_id']);

        if ($seats->isEmpty()) {
            return [];
        }

        // Clamp per price band, exactly as the best-available path already does. Without this the
        // explicit path had no cap at all beyond a flat request size - and because holds key on the
        // session, a fresh cookie was a fresh budget, so a handful of sessions could hold a whole
        // house and keep renewing it.
        $sectionTicket = SeatingSection::where('event_seating_map_id', $map->id)
            ->pluck('ticket_id', 'id');
        $ticketsById = $event->tickets->keyBy('id');

        $kept = [];
        $perTicket = [];

        foreach ($seats as $seat) {
            $ticketId = $sectionTicket[$seat->seating_section_id] ?? null;
            $ticket = $ticketId ? ($ticketsById[$ticketId] ?? null) : null;

            if (! $ticket) {
                continue;
            }

            $cap = $this->perOrderCap($ticket);
            $used = $perTicket[$ticketId] ?? 0;

            if ($used >= $cap) {
                continue;
            }

            $perTicket[$ticketId] = $used + 1;
            $kept[] = $seat->id;
        }

        return $kept;
    }

    private function perOrderCap(Ticket $ticket): int
    {
        return (int) ($ticket->max_per_order ?: (config('app.max_tickets_per_order') ?: 20));
    }

    private function fullPayload(EventSeatingMap $map, string $token): array
    {
        $sections = SeatingSection::with('tables')
            ->where('event_seating_map_id', $map->id)
            ->where('is_deleted', false)
            ->orderBy('position')->get();

        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->whereIn('seating_section_id', $sections->pluck('id'))
            ->orderBy('row_position')->orderBy('position')
            ->get()->groupBy('seating_section_id');

        $levels = $map->levels()->get();

        return [
            'version' => (int) $map->version,
            'levels' => $levels->map(fn ($level) => [
                'id' => $level->id,
                'name' => $level->name,
                'width' => $level->width,
                'height' => $level->height,
                'sections' => $sections->where('seating_level_id', $level->id)->values()->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'color' => $s->color,
                    'kind' => $s->kind,
                    'capacity' => $s->capacity,
                    'accessibility_only' => (bool) $s->accessibility_only,
                    // Encoded to match the ids already in the guest form's ticket payload.
                    'ticket_id' => $s->ticket_id ? UrlUtils::encodeId($s->ticket_id) : null,
                    'x' => $s->x, 'y' => $s->y, 'rotation' => $s->rotation,
                    'tables' => $s->tables->map(fn ($t) => [
                        'id' => $t->id, 'label' => $t->label, 'shape' => $t->shape,
                        'booking_mode' => $t->booking_mode,
                        'x' => $t->x, 'y' => $t->y, 'width' => $t->width, 'height' => $t->height,
                    ])->all(),
                    'seats' => ($seats[$s->id] ?? collect())->map(fn ($seat) => [
                        'id' => $seat->id,
                        'table_id' => $seat->seating_table_id,
                        'row' => $seat->row_label,
                        'seat' => $seat->seat_label,
                        'x' => $seat->x, 'y' => $seat->y,
                        'kind' => $seat->kind,
                        'aisle_after' => (bool) $seat->aisle_after,
                        // NOT the raw status: hold_note, hold_kind and the buying customer are
                        // organizer-only. A guest learns nothing beyond "can I sit here".
                        'state' => $this->guestState($seat, $token),
                    ])->values()->all(),
                ])->all(),
            ])->all(),
        ];
    }

    /** available | mine | taken - the only three things a guest may know about a seat. */
    private function guestState(SeatingSeat $seat, string $token): string
    {
        // Order matters: a LAPSED hold of my own is available again, to me and to everybody else.
        if ($seat->isAvailable()) {
            return 'available';
        }

        return ($seat->status === 'held' && $seat->hold_token === $token) ? 'mine' : 'taken';
    }

    /**
     * @return array{0: Event, 1: string}
     */
    private function resolveEvent(Request $request, $subdomain): array
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $event = Event::whereHas('roles', fn ($q) => $q->where('roles.id', $role->id))
            ->findOrFail(UrlUtils::decodeId($request->input('event_id')));

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());

        // The SAME rule checkout applies. Guarding only is_draft here left an unlisted,
        // password-protected event's entire seating plan - every section, every seat reference,
        // and its exact sold/free pattern - readable by anyone with the event id.
        if ($event->is_cancelled || $event->guestVisibilityFailure($role, $isMemberOrAdmin) !== null) {
            abort(404);
        }

        $requested = $request->input('date');

        // A seat map is CREATED on demand, so an unvalidated date is an unauthenticated write:
        // every distinct string is a distinct map keyed (event_id, event_date), and each one costs
        // thousands of rows. canSellTickets() does not cover this - it only asks whether the
        // occurrence is past, and ignores the date entirely for a non-recurring event.
        // isOccurrenceDate() rather than a bare regex: "2026-13-45" matches the pattern and then
        // makes Carbon::parse() throw inside matchesDate(), turning a 404 into a 500. The helper
        // pairs the regex with checkdate() for exactly that reason.
        if ($requested !== null) {
            if (! Event::isOccurrenceDate($requested)
                || ! $event->matchesDate($requested, $event->scheduleTimezone())) {
                abort(404);
            }
        }

        $date = $this->maps->resolveDate($event, $requested);

        if (! $date) {
            abort(404);
        }

        return [$event, $date];
    }
}
