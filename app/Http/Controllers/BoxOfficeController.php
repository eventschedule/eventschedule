<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingDecoration;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Services\AuditService;
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
            $changed = SeatingSeat::with(['sale', 'saleTicket.ticket'])
                ->where('event_seating_map_id', $map->id)
                ->where('state_version', '>', $since)
                ->inLiveSection()
                ->get();

            return response()->json([
                'version' => (int) $map->version,
                'seats' => $changed->map(fn ($s) => $this->staffSeat($s))->values(),
                // Only when something moved. The poll updated seat state and nothing else, so a
                // console left open at the door showed a live map above frozen numbers - and those
                // numbers are what staff read to decide whether to release a hold.
                'counts' => $changed->isEmpty() ? null : $this->counts($map),
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
    /**
     * Which rows the printed sheet lists.
     *
     * `all` is what it always did - every seat in the house, so a 2,000-seat room on a 40%-sold
     * night printed 2,000 lines to find 800. `taken` is the front-of-house sheet: only seats that
     * are sold, held back or in a basket. `names` is the same set ordered by surname, which is how
     * somebody at the door looks a walk-up customer up and was previously only possible by
     * re-sorting the CSV in a spreadsheet.
     */
    private function reportView(Request $request): string
    {
        $view = (string) $request->input('view', 'all');

        return in_array($view, ['all', 'taken', 'names'], true) ? $view : 'all';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function reportRows(array $rows, string $view): array
    {
        if ($view === 'all') {
            return $rows;
        }

        // Anything not on sale: sold, held back by the box office, or in somebody's basket. Those
        // are the rows a front-of-house sheet exists for.
        $rows = array_values(array_filter($rows, fn ($row) => ($row['state'] ?? '') !== 'available'));

        if ($view !== 'names') {
            return $rows;
        }

        // By the name somebody at the door will be given, not by where they are sitting.
        usort($rows, fn ($a, $b) => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        return $rows;
    }

    public function report(Request $request, $subdomain, $hash)
    {
        [$role, $event, $map] = $this->resolve($request, $subdomain, $hash);

        $data = $this->reportData($map);
        $view = $this->reportView($request);
        $data['run'] = $this->runOccupancy($event, $map->event_date);

        // The MAP always draws the whole house - a plan with holes in it is not a plan. Only the
        // list narrows, because that is the part that runs to thousands of lines.
        $data['rows'] = $this->reportRows($data['rows'], $view);

        return view('role.seating-report', [
            'role' => $role,
            'event' => $event,
            'map' => $map,
            'subdomain' => $subdomain,
            'view' => $view,
        ] + $data);
    }

    /** The same sheet as a spreadsheet, so front of house can sort and filter it. */
    public function reportCsv(Request $request, $subdomain, $hash)
    {
        [, $event, $map] = $this->resolve($request, $subdomain, $hash);
        $data = $this->reportData($map);

        // The same rows as the screen. "Download as CSV" sitting under a sheet filtered to 40 taken
        // seats used to hand back all 2,000, which is not the same document.
        $rows = $this->reportRows($data['rows'], $this->reportView($request));

        $filename = Str::slug($event->translatedName().'-'.$map->event_date).'-seating.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                __('messages.seating_section'), __('messages.seating_rows'), __('messages.seating_seats'),
                __('messages.status'), __('messages.name'), __('messages.email'),
                __('messages.seating_internal_note'),
                // The two columns the printed sheet gained. Without them the spreadsheet is a
                // different document from the one on screen.
                __('messages.ticket'), __('messages.seating_arrived'),
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['section'], $row['row'], $row['seat'], $row['status'],
                    $row['name'], $row['email'], $row['note'],
                    $row['ticket'] ?? '',
                    ! empty($row['arrived']) ? __('messages.yes') : '',
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

        // saleTicket.ticket eager-loaded with the rest: the sheet now names what each seat holder
        // bought, and doing that per row is an N+1 over the whole house.
        $seats = SeatingSeat::with(['sale', 'saleTicket.ticket'])
            ->where('event_seating_map_id', $map->id)
            ->whereIn('seating_section_id', $sections->pluck('id'))
            ->orderBy('row_position')->orderBy('position')
            ->get();

        $counts = ['total' => 0, 'sold' => 0, 'held' => 0, 'blocked' => 0, 'available' => 0];
        $rows = [];
        // Drawn seats, keyed by level. Levels are separate spaces and every level's first section
        // starts at the same origin, so drawing them on one canvas put the balcony on top of the
        // stalls. Paper has no level switcher, so the report stacks them with a heading each.
        $drawnByLevel = [];
        $levelNames = $map->levels()->get()->pluck('name', 'id');
        $decorationsByLevel = SeatingDecoration::where('event_seating_map_id', $map->id)
            ->orderBy('position')->get()->groupBy('seating_level_id');

        $occupancyPerSection = [];

        foreach ($sections as $section) {
            $levelKey = $section->seating_level_id ?? 0;
            $drawn = &$drawnByLevel[$levelKey];
            $drawn = $drawn ?? [];
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

            $sectionSold = 0;
            $sectionTotal = 0;

            foreach ($mine as $seat) {
                $state = $this->seatState($seat);
                $counts['total']++;
                $counts[$state]++;

                // Tallied HERE, in the pass that was walking every seat anyway. occupancy() used to
                // redo it with a where() and a filter() per section, which is the same two-passes-
                // per-section shape the comment above says it cost us once already.
                $sectionTotal++;
                $sectionSold += $state === 'sold' ? 1 : 0;

                $rows[] = [
                    'section' => $section->name,
                    'row' => $seat->row_label,
                    'seat' => $seat->seat_label,
                    'status' => $this->stateLabel($state),
                    'name' => $state === 'sold' ? ($seat->sale->name ?? '') : '',
                    'email' => $state === 'sold' ? ($seat->sale->email ?? '') : '',
                    'note' => $seat->hold_note ?? '',
                    'kind' => $seat->kind,
                    // The raw state, so the sheet can narrow to what is actually taken. `status` is
                    // a translated label and is no use as a filter key.
                    'state' => $state,
                    // What they bought, which the sheet never carried - at the door "Stalls" and
                    // "Circle" is the difference between letting someone through and not.
                    'ticket' => $state === 'sold' ? ($seat->saleTicket?->ticket?->type ?? '') : '',
                    // A tick when they are already inside, and an empty BOX when they are not, so
                    // the printed sheet can be marked by hand. It had no column for this at all.
                    // Gated on sold like name and email above: a row released after its holder
                    // scanned in would otherwise print somebody else's tick.
                    'arrived' => $state === 'sold' && $seat->checked_in_at !== null,
                ];

                $index = $indexInRow[$seat->id];

                // Rotation included: this sheet resolves absolute positions rather than drawing a
                // transformed group, so it is the one renderer that has to apply it by hand. The
                // seat LABELS stay upright, which is what you want on paper.
                [$cx, $cy] = $section->canvasPoint(
                    $degenerate ? $index * 26 : $seat->x,
                    $degenerate ? ($rowOrderIndex[$seat->row_position] ?? 0) * 30 : $seat->y,
                );

                $drawn[] = [
                    'x' => $cx,
                    'y' => $cy,
                    'state' => $state,
                    'kind' => $seat->kind,
                    'label' => $seat->seat_label,
                ];
            }

            if ($sectionTotal > 0) {
                $occupancyPerSection[] = [
                    'name' => $section->name,
                    'sold' => $sectionSold,
                    'total' => $sectionTotal,
                    'percent' => (int) round($sectionSold / $sectionTotal * 100),
                ];
            }

            unset($drawn);
        }

        $pad = 24;
        $levels = [];

        // In LEVEL order, which is what $levelNames carries - EventSeatingMap::levels() orders by
        // position. Walking $drawnByLevel instead would print the levels in the order each one's
        // first section happens to sit, so a balcony whose section came first printed above the
        // stalls. Anything with no level at all goes last rather than being dropped.
        $levelKeys = $levelNames->keys()->all();
        foreach (array_keys($drawnByLevel) as $key) {
            if (! in_array($key, $levelKeys, true)) {
                $levelKeys[] = $key;
            }
        }

        foreach ($levelKeys as $levelKey) {
            $drawn = $drawnByLevel[$levelKey] ?? [];

            // A level holding only a stage still has nothing to sell, so it stays off the sheet.
            if (! $drawn) {
                continue;
            }

            $xs = array_column($drawn, 'x');
            $ys = array_column($drawn, 'y');

            // The stage frames with the seats, or a sheet fitted to the seats alone crops it off
            // the top of the page - which is where a stage always is.
            $decorations = ($decorationsByLevel[$levelKey] ?? collect())->map(function ($d) use (&$xs, &$ys) {
                [$x1, $y1, $x2, $y2] = $d->bounds();
                $xs[] = $x1;
                $xs[] = $x2;
                $ys[] = $y1;
                $ys[] = $y2;

                return [
                    'kind' => $d->kind, 'label' => $d->label,
                    'x' => $d->x, 'y' => $d->y,
                    'width' => $d->width, 'height' => $d->height, 'rotation' => $d->rotation,
                ];
            })->values()->all();

            $levels[] = [
                'name' => $levelNames[$levelKey] ?? null,
                'drawn' => $drawn,
                'decorations' => $decorations,
                'viewBox' => sprintf('%d %d %d %d', min($xs) - $pad, min($ys) - $pad,
                    max(60, max($xs) - min($xs) + $pad * 2), max(60, max($ys) - min($ys) + $pad * 2)),
            ];
        }

        return [
            'counts' => $counts,
            'rows' => $rows,
            'levels' => $levels,
            'sections' => $sections,
            // How full the house is, and which part of it is soft. OrphanSeatRule::soldPercent()
            // has computed the first number on every guest selection since the feature shipped and
            // rendered it to nobody; the per-section split answers the question a producer actually
            // asks, which is not "how many" but "where are the empty seats".
            'occupancy' => $this->occupancy($counts, $occupancyPerSection),
        ];
    }

    public function block(Request $request, $subdomain, $hash)
    {
        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request) {
            $ids = $this->seatIds($request);

            $held = $this->boxOffice->block(
                $map,
                $ids,
                (string) $request->input('kind', 'box_office'),
                $request->input('note'),
            );

            // Only when something moved. inLiveSection() can filter every id out, and a log that
            // records a mutation which never happened is worse than no log at all.
            if ($held > 0) {
                $this->audit($request, AuditService::SALE_SEAT_BLOCKED, $map, $ids, (string) $request->input('kind', 'box_office'));
            }
        });
    }

    public function unblock(Request $request, $subdomain, $hash)
    {
        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request) {
            $ids = $this->seatIds($request);

            if ($this->boxOffice->unblock($map, $ids) > 0) {
                $this->audit($request, AuditService::SALE_SEAT_UNBLOCKED, $map, $ids);
            }
        });
    }

    public function releaseSeat(Request $request, $subdomain, $hash)
    {
        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request) {
            // Both shapes, because the console can now select more than one seat: a party
            // refunding together was six click-confirm cycles at the counter.
            $ids = $request->has('seat_ids') ? $this->seatIds($request) : [(int) $request->input('seat_id')];

            // Read the buyer BEFORE, write the row AFTER. sale_id is cleared by the release, so
            // afterwards there is nothing left to say whose seats these were - which is the entire
            // question this record answers. But mutate() is not transactional, so auditing first
            // left a permanent "released" row behind every refusal, and a mixed selection is now
            // refused by design. The labels survive either way: they belong to the seat.
            $buyers = $this->buyersOf($map, $ids);

            $this->boxOffice->releaseSeats($map, $ids);

            $this->audit($request, AuditService::SALE_SEAT_RELEASED, $map, $ids, $buyers);
        });
    }

    public function exchange(Request $request, $subdomain, $hash)
    {
        return $this->mutate($request, $subdomain, $hash, function (EventSeatingMap $map) use ($request) {
            $from = (int) $request->input('from_seat_id');
            $to = (int) $request->input('to_seat_id');

            $this->boxOffice->exchange($map, $from, $to);
            $this->audit($request, AuditService::SALE_SEAT_EXCHANGED, $map, [$from, $to]);
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
            $ids = $this->seatIds($request);

            $this->boxOffice->bookSeats($map, $ids, [
                'subdomain' => $subdomain,
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                // Blank means "list price"; an explicit 0 means comped, so they cannot collapse.
                'amount' => ($validated['amount'] ?? null) === null ? null : (float) $validated['amount'],
            ]);

            $this->audit($request, AuditService::SALE_SEAT_BOOKED, $map, $ids, $validated['name']);
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

        $seats = SeatingSeat::with(['sale', 'saleTicket.ticket'])
            ->where('event_seating_map_id', $map->id)
            ->whereIn('seating_section_id', $sections->pluck('id'))
            ->orderBy('row_position')->orderBy('position')
            ->get()->groupBy('seating_section_id');

        $decorations = SeatingDecoration::where('event_seating_map_id', $map->id)
            ->orderBy('position')->get()->groupBy('seating_level_id');

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
                'decorations' => ($decorations[$level->id] ?? collect())->map(fn ($d) => [
                    'id' => $d->id, 'kind' => $d->kind, 'label' => $d->label,
                    'x' => $d->x, 'y' => $d->y,
                    'width' => $d->width, 'height' => $d->height, 'rotation' => $d->rotation,
                ])->values()->all(),
                'sections' => $sections->where('seating_level_id', $level->id)->values()
                    ->map(fn ($s) => [
                        'id' => $s->id, 'name' => $s->name, 'color' => $s->color, 'kind' => $s->kind,
                        'capacity' => $s->capacity,
                        'accessibility_only' => (bool) $s->accessibility_only,
                        // The console draws a transformed group, like the designer and the picker;
                        // without this a rotated section rendered straight to the one person who has
                        // to find a physical seat from it.
                        'x' => $s->x, 'y' => $s->y, 'rotation' => (int) $s->rotation,
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
            // The figure a producer actually reads. `counts` says how many; this says how full.
            'percent' => $counts['total'] ? (int) round($counts['sold'] / $counts['total'] * 100) : 0,
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

    /**
     * The summary figures, for the diff poll.
     *
     * payload() counts them while it walks the seats it is already loading; this is the same tally
     * for a poll that loads nothing else. Cheap enough at a few thousand rows, and it only runs on
     * a tick where something actually changed.
     */
    /**
     * The distinct names currently holding these seats.
     *
     * Read before a release, because releasing clears sale_id and takes the answer with it.
     *
     * @param  array<int, int>  $seatIds
     */
    private function buyersOf(EventSeatingMap $map, array $seatIds): string
    {
        // Scoped to the map, like every mutation is. $seatIds is raw request input, so an unscoped
        // read here would answer with buyer names from any other event on the install - and the
        // audit row that quotes them renders on the caller's own audit-log page.
        return SeatingSeat::with('sale')
            ->where('event_seating_map_id', $map->id)
            ->whereIn('id', $seatIds)
            ->whereNotNull('sale_id')
            ->get()
            ->map(fn (SeatingSeat $seat) => $seat->sale?->name)
            ->filter()
            ->unique()
            ->implode(', ');
    }

    /**
     * Record who did what to which seats.
     *
     * Nothing recorded any of this: neither the controller nor BoxOfficeSeatingService wrote an
     * audit row for block, unblock, release, exchange or a counter booking, so "who released whose
     * seat" had no answer at all - which is the first question after a refund dispute.
     *
     * The `event_id:` suffix is load-bearing. RoleController::auditLog() surfaces `sale.%` rows to
     * a schedule owner only when the metadata ends with it, so without it these would be written
     * and then be invisible to the one person who needs them.
     *
     * @param  array<int, int>  $seatIds
     */
    private function audit(Request $request, string $action, EventSeatingMap $map, array $seatIds, string $extra = ''): void
    {
        $labels = SeatingSeat::where('event_seating_map_id', $map->id)
            ->whereIn('id', $seatIds)
            ->orderBy('row_position')->orderBy('position')
            ->get()
            ->map(fn (SeatingSeat $seat) => $seat->fullLabel() ?: $seat->id)
            ->all();

        AuditService::log(
            $action,
            $request->user()?->id,
            'Event',
            $map->event_id,
            null,
            null,
            trim(implode(' ', array_filter([
                $map->event_date,
                $labels ? implode(', ', array_slice($labels, 0, 12)).(count($labels) > 12 ? ' +'.(count($labels) - 12) : '') : '',
                $extra,
            ]))).' event_id:'.$map->event_id,
        );
    }

    /**
     * Percentage sold overall and per section.
     *
     * Not OrphanSeatRule::soldPercent(): that counts "not available", which folds a box office hold
     * in with a sale. A producer reading this needs SOLD - seats that brought money in - separately
     * from seats staff are sitting on.
     *
     * @param  array<string, int>  $counts
     */
    /**
     * How every night of the run is selling.
     *
     * Everything in this feature is keyed (event_id, event_date), so there was no cross-date view at
     * all - the docs name it as a limitation, "the report is per date, not per run". A producer
     * deciding where to put marketing money needs to see which nights are soft, and comparing them
     * meant opening thirty consoles.
     *
     * Counted from the SNAPSHOTS that exist. A date nobody has opened has no map yet, which is
     * correct rather than missing: nothing has been sold on it, so it is 0%.
     *
     * @return array<int, array<string, mixed>>
     */
    private function runOccupancy(Event $event, string $current): array
    {
        $dates = $event->adminOccurrenceDates();

        if (count($dates) < 2) {
            return [];
        }

        $maps = EventSeatingMap::where('event_id', $event->id)
            ->whereIn('event_date', $dates)
            ->pluck('id', 'event_date');

        // One grouped query for the whole run rather than one per night.
        $sold = $maps->isEmpty() ? collect() : SeatingSeat::whereIn('event_seating_map_id', $maps->values())
            ->inLiveSection()
            ->selectRaw('event_seating_map_id, count(*) as total, sum(status = ?) as sold', ['sold'])
            ->groupBy('event_seating_map_id')
            ->get()
            ->keyBy('event_seating_map_id');

        $template = $event->seatingPlanModel();
        $planSeats = $template ? $template->seatCount() : 0;

        return collect($dates)->map(function (string $date) use ($maps, $sold, $planSeats, $current) {
            $mapId = $maps[$date] ?? null;
            $row = $mapId ? ($sold[$mapId] ?? null) : null;
            // No snapshot yet means nothing sold, and the template says how big the room is.
            $total = (int) ($row->total ?? $planSeats);
            $count = (int) ($row->sold ?? 0);

            return [
                'date' => $date,
                'sold' => $count,
                'total' => $total,
                'percent' => $total ? (int) round($count / $total * 100) : 0,
                'current' => $date === $current,
            ];
        })->all();
    }

    /**
     * How full the house is, section by section.
     *
     * $perSection is accumulated by reportData()'s own pass over the seats. Deriving it here meant
     * a where() and a filter() over the WHOLE house per section - 200 sections against 6,000 seats
     * is over a million comparisons for a figure the CSV does not even print.
     */
    private function occupancy(array $counts, array $perSection): array
    {
        $total = max(1, (int) $counts['total']);

        return [
            'percent' => (int) round($counts['sold'] / $total * 100),
            'sold' => (int) $counts['sold'],
            'total' => (int) $counts['total'],
            'sections' => $perSection,
        ];
    }

    private function counts(EventSeatingMap $map): array
    {
        $counts = ['total' => 0, 'sold' => 0, 'held' => 0, 'blocked' => 0, 'available' => 0];

        SeatingSeat::where('event_seating_map_id', $map->id)
            ->inLiveSection()
            ->select(['id', 'status', 'hold_kind', 'hold_expires_at'])
            ->chunkById(1000, function ($seats) use (&$counts) {
                foreach ($seats as $seat) {
                    $counts['total']++;
                    $counts[$this->seatState($seat)]++;
                }
            });

        return $counts;
    }

    private function staffSeat(SeatingSeat $seat): array
    {
        $state = $this->seatState($seat);

        return [
            'id' => $seat->id,
            'table_id' => $seat->seating_table_id,
            'row' => $seat->row_label,
            'seat' => $seat->seat_label,
            // The row as a NUMBER, not just its label. "Select row" compares on this; without it
            // every seat compared undefined to undefined and the whole section came back selected.
            'row_position' => (int) $seat->row_position,
            'x' => $seat->x, 'y' => $seat->y,
            'kind' => $seat->kind,
            'aisle_after' => (bool) $seat->aisle_after,
            'state' => $state,
            // Separate from `state`: an arrived seat is still sold, and the door needs both.
            // Gated on sold, so a stale stamp on a freed seat cannot draw a tick over its next
            // buyer - belt and braces alongside clearing the column on release.
            'arrived' => $state === 'sold' && $seat->checked_in_at !== null,
            'hold_kind' => $seat->hold_kind,
            'hold_note' => $seat->hold_note,
            // Name, email and paid/unpaid was everything the console knew about a seat's buyer,
            // so reaching them meant leaving for the Sales page and finding the order by name.
            // Still deliberately narrow: no payment detail, and nothing a guest payload carries.
            'booker' => $seat->sale && $seat->status === 'sold' ? [
                'name' => $seat->sale->name,
                'email' => $seat->sale->email,
                'phone' => $seat->sale->phone,
                'status' => $seat->sale->status,
                'ticket' => $seat->saleTicket?->ticket?->type,
                'band' => $seat->saleTicket?->ticket?->seating_band,
                'bought_at' => $seat->sale->created_at?->toIso8601String(),
                // The sales LIST, filtered to this buyer. There is no per-sale page to link to -
                // sales() takes a `filter` and nothing else - so this is as close as the console
                // can get to "show me this order", and it beats leaving the screen and searching
                // by name, which is what staff do today.
                'sales_url' => route('sales', ['subdomain' => $seat->sale->subdomain], false)
                    .'?filter='.urlencode((string) $seat->sale->email),
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

        // canViewEventData(), not canEditEvent(): the report renders buyer names and emails, and
        // canEditEvent() has no curator exception - so a curator's admin could read the buyer list
        // for an event the curator merely lists. It also keeps this in step with the Sales page:
        // on the edit rule that admin could BOOK a seat here and then not be able to refund it.
        if (! $request->user()->canViewEventData($event)) {
            abort(403);
        }

        $date = $request->input('date');

        // Format AND membership, the same pair SeatingPickerController::resolveEvent() applies, and
        // for the reason its comment gives: materialize() below CREATES the map, so an unvalidated
        // date is a write. Every distinct string is a distinct row keyed (event_id, event_date),
        // each costing up to SeatingStructureService::MAX_SEATS seat rows - and these endpoints are
        // GETs with no throttle. isOccurrenceDate() alone only proves "2099-01-01" is a real
        // calendar day, not that this event ever happens on it, so without matchesDate() every
        // well-formed string snapshots a house nobody will ever sell from, which then shows up in
        // the box-office report and in BackupService::exportSeatingMaps().
        if ($date !== null
            && (! Event::isOccurrenceDate($date) || ! $event->matchesDate($date, $event->scheduleTimezone()))) {
            abort(404);
        }

        // Tonight rather than the series anchor. saleEventDateFromStartsAt() - what
        // SeatingMapService::resolveDate() falls back to - is the date the RUN began, so on a
        // recurring event every AP screen opened on night one, usually already in the past, and
        // there was no way to reach any other night. Defaulted here and not in resolveDate(), whose
        // null-date fallback is shared with the guest picker and with Event::seatingMapCache.
        $date = $date ?? $event->defaultAdminOccurrenceDate();

        $map = $this->maps->materialize($event, $date);

        if (! $map) {
            abort(404);
        }

        return [$role, $event, $map];
    }
}
