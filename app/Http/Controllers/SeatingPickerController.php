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
use App\Services\OrphanSeatRule;
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

            $payload = [
                'version' => (int) $map->version,
                'seats' => $changed->map(fn ($s) => [
                    'id' => $s->id,
                    'state' => $this->guestState($s, $token),
                ])->values(),
            ];

            // Only when something actually moved: an idle poll on a busy on-sale must not pay for
            // this. An ABSENT key means "unchanged" to the picker, not "no warning" - sending null
            // on every quiet tick would wipe a notice that is still true.
            if ($changed->isNotEmpty()) {
                $payload['warning'] = $this->advisoryFor($map, $token);
            }

            return response()->json($payload);
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
            // orphanAdvisory: a guest builds a selection one click at a time, so the single-seat
            // rule warns here and is enforced once, on the finished selection, at checkout.
            $result = $this->holds->acquire($map, $seatIds, $token, null, false, true);
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
            // Always present, null when the selection is fine - the picker replaces its notice from
            // every response, so a warning that has been fixed clears itself.
            'warning' => $result['warning'] ?? null,
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

    /**
     * Would AccessibleSeatingRule refuse this seat outright?
     *
     * Mirrors the rule rather than reimplementing its judgement: a wheelchair space is sellable only
     * from an accessibility_only section, and a companion may not be taken while the wheelchair
     * space beside it is still free.
     */
    private function ruleWouldRefuse(SeatingSeat $seat, ?SeatingSection $section): bool
    {
        if ($seat->kind === 'wheelchair') {
            return ! ($section->accessibility_only ?? false);
        }

        if ($seat->kind !== 'companion') {
            return false;
        }

        $row = SeatingSeat::where('event_seating_map_id', $seat->event_seating_map_id)
            ->where('seating_section_id', $seat->seating_section_id)
            ->where('row_position', $seat->row_position)
            ->orderBy('position')
            ->get();

        $index = $row->search(fn ($x) => $x->id === $seat->id);

        if ($index === false) {
            return false;
        }

        foreach ([-1, 1] as $step) {
            $neighbour = $row[$index + $step] ?? null;

            if (! $neighbour || $neighbour->kind !== 'wheelchair') {
                continue;
            }

            // A gangway between them means they are not neighbours.
            $between = $step === -1 ? $neighbour : $seat;

            if (! $between->aisle_after && $neighbour->isAvailable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * The advisory this token should be seeing for what it is currently HOLDING, or null.
     *
     * heldByToken(), not a raw hold_token match: a lapsed hold is already free, and reporting on it
     * would leave a buyer staring at a warning about seats they no longer have.
     */
    private function advisoryFor(EventSeatingMap $map, string $token): ?array
    {
        $held = SeatingSeat::where('event_seating_map_id', $map->id)
            ->heldByToken($token)
            ->inLiveSection()
            ->pluck('id')->all();

        return $held ? app(OrphanSeatRule::class)->advisoryFor($map, $held) : null;
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
            // Part of the map's state, not a one-off reply to a click: without it a reload restored
            // the buyer's seats and quietly dropped the reason they could not check out.
            'warning' => $this->advisoryFor($map, $token),
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
                        'state' => $this->guestState($seat, $token, $s),
                    ])->values()->all(),
                ])->all(),
            ])->all(),
        ];
    }

    /**
     * available | mine | taken | unavailable - all a guest may know about a seat.
     *
     * `unavailable` is a seat AccessibleSeatingRule will never sell to this buyer, however free it
     * looks. Before this it came back `available`, so the picker drew it as an ordinary seat, the
     * buyer clicked it, and the hold was refused - which on a plan with a wheelchair space drawn
     * mid-row is the most attractive seat on the map.
     *
     * Distinct from `taken` on purpose: nobody has it, and an organizer reading a support ticket
     * needs to be able to tell "sold" from "you drew this wrong".
     */
    private function guestState(SeatingSeat $seat, string $token, ?SeatingSection $section = null): string
    {
        if ($seat->isAvailable() && $this->ruleWouldRefuse($seat, $section)) {
            return 'unavailable';
        }

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
