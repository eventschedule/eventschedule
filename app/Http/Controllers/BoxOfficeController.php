<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Services\BoxOfficeSeatingService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The staff-side seat map: who is sitting where, and the controls to change it.
 *
 * The payload here is deliberately a SEPARATE method from
 * SeatingPickerController::fullPayload(). This is the one surface that SHOULD carry hold_kind, the
 * internal hold note and the booker's name and email - and keeping the two apart is what stops the
 * guest collapse to available/mine/taken ever being widened by accident.
 */
class BoxOfficeController extends Controller
{
    public function __construct(
        private SeatingMapService $maps,
        private BoxOfficeSeatingService $boxOffice,
    ) {}

    public function show(Request $request, $subdomain, $hash)
    {
        [$role, $event, $map] = $this->resolve($request, $subdomain, $hash);

        return view('role.seating-box-office', [
            'role' => $role,
            'event' => $event,
            'map' => $map,
            'subdomain' => $subdomain,
        ]);
    }

    public function state(Request $request, $subdomain, $hash)
    {
        [, , $map] = $this->resolve($request, $subdomain, $hash);

        $since = (int) $request->input('since', 0);

        if ($since > 0) {
            $changed = SeatingSeat::with(['sale'])
                ->where('event_seating_map_id', $map->id)
                ->where('state_version', '>', $since)
                ->inLiveSection()
                ->get();

            return response()->json([
                'version' => (int) $map->version,
                'seats' => $changed->map(fn ($s) => $this->staffSeat($s))->values(),
            ]);
        }

        return response()->json($this->payload($map));
    }

    /**
     * The front-of-house sheet: who is sitting where, printable.
     *
     * Server-rendered on purpose - it is a print artifact, so it must not depend on JavaScript
     * having run, and there is no PDF library in this project (the house pattern is print CSS, as
     * in resources/views/ticket/view.blade.php).
     */
    public function report(Request $request, $subdomain, $hash)
    {
        [$role, $event, $map] = $this->resolve($request, $subdomain, $hash);

        return view('role.seating-report', [
            'role' => $role,
            'event' => $event,
            'map' => $map,
            'subdomain' => $subdomain,
        ] + $this->reportData($map));
    }

    /** The same sheet as a spreadsheet, so front of house can sort and filter it. */
    public function reportCsv(Request $request, $subdomain, $hash)
    {
        [, $event, $map] = $this->resolve($request, $subdomain, $hash);
        $data = $this->reportData($map);

        $filename = Str::slug($event->translatedName().'-'.$map->event_date).'-seating.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                __('messages.seating_section'), __('messages.seating_rows'), __('messages.seating_seats'),
                __('messages.status'), __('messages.name'), __('messages.email'),
                __('messages.seating_internal_note'),
            ]);

            foreach ($data['rows'] as $row) {
                fputcsv($handle, [
                    $row['section'], $row['row'], $row['seat'], $row['status'],
                    $row['name'], $row['email'], $row['note'],
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Everything the sheet needs, with seat coordinates already resolved.
     *
     * Positions are worked out here rather than in the view so the Blade stays dumb - including the
     * same fallback the picker and console use for a plan whose seats carry no coordinates, which
     * would otherwise draw the whole house stacked on one point.
     */
    private function reportData(EventSeatingMap $map): array
    {
        $sections = SeatingSection::where('event_seating_map_id', $map->id)
            ->where('is_deleted', false)->orderBy('position')->get();

        $seats = SeatingSeat::with('sale')
            ->where('event_seating_map_id', $map->id)
            ->whereIn('seating_section_id', $sections->pluck('id'))
            ->orderBy('row_position')->orderBy('position')
            ->get();

        $counts = ['total' => 0, 'sold' => 0, 'held' => 0, 'blocked' => 0, 'available' => 0];
        $rows = [];
        $drawn = [];

        foreach ($sections as $section) {
            $mine = $seats->where('seating_section_id', $section->id)->values();

            $degenerate = $mine->count() > 1
                && $mine->pluck('x')->unique()->count() === 1
                && $mine->pluck('y')->unique()->count() === 1;

            $rowOrder = $mine->pluck('row_position')->unique()->sort()->values()->all();
            $rowOrderIndex = array_flip($rowOrder);

            // Position within its own row, precomputed once per section.
            //
            // This used to be re-derived per seat with a where()->values()->search() chain, which
            // is two full passes over the section for every seat in it - 2N^2 closure calls. A
            // single 2,000-seat section took 8 million of them and pushed the report and its CSV
            // to several seconds; at the 6,000-seat cap it was 72 million and timed out. The CSV
            // is a streamDownload, so that timeout arrived after a 200 and produced a truncated
            // file rather than an error.
            $indexInRow = [];
            $seenPerRow = [];
            foreach ($mine as $seat) {
                $indexInRow[$seat->id] = $seenPerRow[$seat->row_position] = ($seenPerRow[$seat->row_position] ?? -1) + 1;
            }

            foreach ($mine as $seat) {
                $state = $this->seatState($seat);
                $counts['total']++;
                $counts[$state]++;

                $rows[] = [
                    'section' => $section->name,
                    'row' => $seat->row_label,
                    'seat' => $seat->seat_label,
                    'status' => $this->stateLabel($state),
                    'name' => $state === 'sold' ? ($seat->sale->name ?? '') : '',
                    'email' => $state === 'sold' ? ($seat->sale->email ?? '') : '',
                    'note' => $seat->hold_note ?? '',
                    'kind' => $seat->kind,
                ];

                $index = $indexInRow[$seat->id];

                $drawn[] = [
                    'x' => $section->x + ($degenerate ? $index * 26 : $seat->x),
                    'y' => $section->y + ($degenerate ? ($rowOrderIndex[$seat->row_position] ?? 0) * 30 : $seat->y),
                    'state' => $state,
                    'kind' => $seat->kind,
                    'label' => $seat->seat_label,
                ];
            }
        }

        $xs = array_column($drawn, 'x');
        $ys = array_column($drawn, 'y');
        $pad = 24;

        return [
            'counts' => $counts,
            'rows' => $rows,
            'drawn' => $drawn,
            'sections' => $sections,
            'viewBox' => $drawn
                ? sprintf('%d %d %d %d', min($xs) - $pad, min($ys) - $pad,
                    max(60, max($xs) - min($xs) + $pad * 2), max(60, max($ys) - min($ys) + $pad * 2))
                : '0 0 600 300',
        ];
    }

    public function block(Request $request, $subdomain, $hash)
    {
        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request) {
            $this->boxOffice->block(
                $map,
                $this->seatIds($request),
                (string) $request->input('kind', 'box_office'),
                $request->input('note'),
            );
        });
    }

    public function unblock(Request $request, $subdomain, $hash)
    {
        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request) {
            $this->boxOffice->unblock($map, $this->seatIds($request));
        });
    }

    public function releaseSeat(Request $request, $subdomain, $hash)
    {
        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request) {
            $this->boxOffice->releaseSeat($map, (int) $request->input('seat_id'));
        });
    }

    public function exchange(Request $request, $subdomain, $hash)
    {
        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request) {
            $this->boxOffice->exchange($map, (int) $request->input('from_seat_id'), (int) $request->input('to_seat_id'));
        });
    }

    /**
     * Sell the selected seats to a caller.
     *
     * The one place staff create a sale from the map. It goes through mutate() like every other
     * console action so a refusal (someone bought a seat while the operator was typing) comes back
     * with the current map rather than a stale one.
     */
    public function bookSeats(Request $request, $subdomain, $hash)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:paid,unpaid',
            'amount' => 'nullable|numeric|min:0|max:9999999',
        ]);

        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request, $validated, $subdomain) {
            $this->boxOffice->bookSeats($map, $this->seatIds($request), [
                'subdomain' => $subdomain,
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                // Blank means "list price"; an explicit 0 means comped, so they cannot collapse.
                'amount' => ($validated['amount'] ?? null) === null ? null : (float) $validated['amount'],
            ]);
        });
    }

    /**
     * Run a mutation and hand back the refreshed map, so the console never has to guess what
     * changed - and a refusal arrives with the current truth rather than a stale view.
     */
    private function mutate(Request $request, $subdomain, $hash, callable $action)
    {
        [, , $map] = $this->resolve($request, $subdomain, $hash);

        try {
            $action($map);
        } catch (BusinessException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'state' => $this->payload($map->fresh()),
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.error')], 500);
        }

        return response()->json($this->payload($map->fresh()));
    }

    private function seatIds(Request $request): array
    {
        $ids = $request->input('seat_ids', []);

        return is_array($ids) ? array_slice(array_map('intval', $ids), 0, 2000) : [];
    }

    private function payload(EventSeatingMap $map): array
    {
        $sections = SeatingSection::with('tables')
            ->where('event_seating_map_id', $map->id)->where('is_deleted', false)
            ->orderBy('position')->get();

        $seats = SeatingSeat::with(['sale'])
            ->where('event_seating_map_id', $map->id)
            ->whereIn('seating_section_id', $sections->pluck('id'))
            ->orderBy('row_position')->orderBy('position')
            ->get()->groupBy('seating_section_id');

        // Counted up front rather than accumulated during the map(). Threading a by-reference
        // total through the nested closures does not work: the enclosing fn() arrow functions
        // capture by VALUE, so the inner writes never reached the outer array and every count
        // rendered as zero however many seats were sold.
        $counts = ['total' => 0, 'sold' => 0, 'held' => 0, 'blocked' => 0, 'available' => 0];

        foreach ($seats as $group) {
            foreach ($group as $seat) {
                $counts['total']++;
                $counts[$this->seatState($seat)]++;
            }
        }

        return [
            'version' => (int) $map->version,
            'levels' => $map->levels()->get()->map(fn ($level) => [
                'id' => $level->id,
                'name' => $level->name,
                'sections' => $sections->where('seating_level_id', $level->id)->values()
                    ->map(fn ($s) => [
                        'id' => $s->id, 'name' => $s->name, 'color' => $s->color, 'kind' => $s->kind,
                        'capacity' => $s->capacity,
                        'accessibility_only' => (bool) $s->accessibility_only,
                        'x' => $s->x, 'y' => $s->y,
                        'tables' => $s->tables->map(fn ($t) => [
                            'id' => $t->id, 'label' => $t->label, 'shape' => $t->shape,
                            'x' => $t->x, 'y' => $t->y, 'width' => $t->width, 'height' => $t->height,
                        ])->all(),
                        'seats' => ($seats[$s->id] ?? collect())
                            ->map(fn ($seat) => $this->staffSeat($seat))
                            ->values()->all(),
                    ])->all(),
            ])->all(),
            'counts' => $counts,
        ];
    }

    /**
     * One seat, as staff need to see it: the internal note, why it is held, and who booked it.
     */
    /** Human label for a seat state, for the printed sheet and the CSV. */
    private function stateLabel(string $state): string
    {
        return match ($state) {
            'sold' => __('messages.seating_count_sold'),
            'blocked' => __('messages.seating_count_blocked'),
            'held' => __('messages.seating_count_held'),
            default => __('messages.seating_legend_available'),
        };
    }

    /** available | sold | blocked (staff hold, no expiry) | held (a guest's live cart). */
    private function seatState(SeatingSeat $seat): string
    {
        if ($seat->isAvailable()) {
            return 'available';
        }

        return $seat->status === 'sold' ? 'sold' : ($seat->isBlocked() ? 'blocked' : 'held');
    }

    private function staffSeat(SeatingSeat $seat): array
    {
        $state = $this->seatState($seat);

        return [
            'id' => $seat->id,
            'table_id' => $seat->seating_table_id,
            'row' => $seat->row_label,
            'seat' => $seat->seat_label,
            'x' => $seat->x, 'y' => $seat->y,
            'kind' => $seat->kind,
            'aisle_after' => (bool) $seat->aisle_after,
            'state' => $state,
            'hold_kind' => $seat->hold_kind,
            'hold_note' => $seat->hold_note,
            'booker' => $seat->sale && $seat->status === 'sold' ? [
                'name' => $seat->sale->name,
                'email' => $seat->sale->email,
                'status' => $seat->sale->status,
            ] : null,
        ];
    }

    /**
     * @return array{0: Role, 1: Event, 2: EventSeatingMap}
     */
    private function resolve(Request $request, $subdomain, $hash): array
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $request->user() || ! $request->user()->isEditor($subdomain)) {
            abort(403);
        }

        if (! $role->seatingEnabled()) {
            abort(403, __('messages.not_authorized'));
        }

        $event = Event::whereHas('roles', fn ($q) => $q->where('roles.id', $role->id))
            ->findOrFail(UrlUtils::decodeId($hash));

        if (! $request->user()->canEditEvent($event)) {
            abort(403);
        }

        $date = $request->input('date');

        if ($date !== null && ! Event::isOccurrenceDate($date)) {
            abort(404);
        }

        $map = $this->maps->materialize($event, $date);

        if (! $map) {
            abort(404);
        }

        return [$role, $event, $map];
    }
}
