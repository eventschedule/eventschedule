<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Services\SeatingStructureService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Owner-side CRUD for seating plan TEMPLATES, plus the designer's read/save endpoints.
 *
 * Enterprise-gated on every write. The tab itself stays reachable below the plan so a locked
 * schedule sees the pitch rather than a dead link, matching how the promo-codes tab behaves.
 */
class SeatingPlanController extends Controller
{
    /** seating_plans.name is varchar(255) and MySQL runs strict, so anything longer is a 1406. */
    private const MAX_NAME = 255;

    public function __construct(private SeatingStructureService $structure) {}

    /**
     * The only way a name reaches the column.
     *
     * Guards two separate 500s: a name over 255 characters is a 1406 under MySQL strict mode, and
     * `?name[]=x` makes the input an ARRAY, which throws a TypeError inside trim() before any
     * validator sees it. The view's maxlength is client-side and proves nothing.
     */
    private function cleanName($value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, self::MAX_NAME);
    }

    public function store(Request $request, $subdomain)
    {
        $role = $this->gate($request, $subdomain);

        $plan = SeatingPlan::create([
            'role_id' => $role->id,
            'name' => $this->cleanName($request->input('name')) ?: __('messages.seating_untitled_plan'),
        ]);

        return redirect()->route('seating.design', [
            'subdomain' => $subdomain,
            'hash' => UrlUtils::encodeId($plan->id),
        ]);
    }

    public function update(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain);
        $plan = $this->resolvePlan($role, $hash);

        $plan->name = $this->cleanName($request->input('name')) ?: $plan->name;
        $description = $request->input('description');
        $plan->description = is_scalar($description) ? (trim((string) $description) ?: null) : null;
        $plan->save();

        return $this->back($subdomain, __('messages.seating_plan_saved'));
    }

    public function destroy(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain);
        $plan = $this->resolvePlan($role, $hash);

        // Soft only. Occurrences that already snapshotted this template are self-contained, but
        // they keep seating_plan_id as provenance and the report reads the name back through it.
        $plan->is_deleted = true;
        $plan->save();

        return $this->back($subdomain, __('messages.seating_plan_deleted'));
    }

    public function duplicate(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain);
        $plan = $this->resolvePlan($role, $hash);

        $copy = DB::transaction(function () use ($role, $plan) {
            $copy = SeatingPlan::create([
                'role_id' => $role->id,
                // Clamped AFTER interpolation: "Copy of " is 8 characters, so duplicating a
                // plan already at the column limit would otherwise overflow it.
                'name' => $this->cleanName(__('messages.seating_copy_of', ['name' => $plan->name])),
                'description' => $plan->description,
            ]);

            $this->structure->save($copy, $this->structure->toArray($plan));

            return $copy;
        });

        return redirect()->route('seating.design', [
            'subdomain' => $subdomain,
            'hash' => UrlUtils::encodeId($copy->id),
        ]);
    }

    /** The designer page. Hosts the Vue mount point and nothing else. */
    public function design(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain);
        $plan = $this->resolvePlan($role, $hash);

        return view('role.seating-designer', [
            'role' => $role,
            'plan' => $plan,
            'subdomain' => $subdomain,
            'usage' => $this->planUsage($plan),
        ]);
    }

    /**
     * How much of this plan is already committed, for the designer's warning strip.
     *
     * Restructuring a room that is on sale is the one edit that can be refused, because a sold seat
     * cannot be deleted. Without this the organizer only finds out on Save, after the work.
     */
    private function planUsage(SeatingPlan $plan): array
    {
        $eventIds = Event::where('seating_plan_id', $plan->id)->pluck('id');

        if ($eventIds->isEmpty()) {
            return ['events' => 0, 'sold' => 0];
        }

        // Sold seats live on the per-date snapshots, never on the template itself.
        $sold = SeatingSeat::whereIn(
            'event_seating_map_id',
            EventSeatingMap::whereIn('event_id', $eventIds)->select('id')
        )->where('status', 'sold')->count();

        return ['events' => $eventIds->count(), 'sold' => $sold];
    }

    public function structure(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain, json: true);
        $plan = $this->resolvePlan($role, $hash);

        return response()->json($this->structure->toArray($plan));
    }

    public function saveStructure(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain, json: true);
        $plan = $this->resolvePlan($role, $hash);

        $data = ['levels' => $request->input('levels', [])];

        try {
            $this->structure->assertWithinLimits($data);
            $this->structure->save($plan, $data);
        } catch (BusinessException $e) {
            // Intentional business rules (a sold seat, an oversized plan), so the message IS the
            // point and is safe to show.
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            // Never the message: it carries SQL and column names. Sentry gets the detail, the
            // designer gets something it can actually render.
            report($e);

            return response()->json(['error' => __('messages.seating_save_failed')], 500);
        }

        if ($name = $this->cleanName($request->input('name'))) {
            $plan->name = $name;
            $plan->save();
        }

        return response()->json($this->structure->toArray($plan));
    }

    // ---------------------------------------------------------------- per-occurrence editing

    /**
     * "Modify this date only" - the same designer, pointed at one occurrence's SNAPSHOT.
     *
     * This is the payoff for making SeatingStructureService owner-agnostic: editing a single date
     * is nothing more exotic than editing the copy, so it needs no override or tombstone machinery
     * and reuses the sold-seat and held-seat guards unchanged.
     */
    public function designOccurrence(Request $request, $subdomain, $hash)
    {
        [$role, $event, $map] = $this->resolveOccurrence($request, $subdomain, $hash);

        return view('role.seating-designer', [
            'role' => $role,
            'plan' => $map->seatingPlan ?? new SeatingPlan(['name' => $event->translatedName()]),
            'subdomain' => $subdomain,
            'occurrence' => $map,
            'occurrenceEvent' => $event,
            // Scoped to THIS date: editing one occurrence cannot disturb any other.
            'usage' => [
                'events' => 0,
                'sold' => SeatingSeat::where('event_seating_map_id', $map->id)->where('status', 'sold')->count(),
            ],
        ]);
    }

    public function occurrenceStructure(Request $request, $subdomain, $hash)
    {
        [, , $map] = $this->resolveOccurrence($request, $subdomain, $hash);

        return response()->json($this->structure->toArray($map));
    }

    public function saveOccurrenceStructure(Request $request, $subdomain, $hash)
    {
        [, , $map] = $this->resolveOccurrence($request, $subdomain, $hash);

        try {
            $this->structure->save($map, ['levels' => $request->input('levels', [])]);
        } catch (BusinessException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.seating_save_failed')], 500);
        }

        return response()->json($this->structure->toArray($map));
    }

    /**
     * Throw this date's own layout away and fall back to the template on next use.
     *
     * All of the logic already lives in SeatingMapService::revertToTemplate(), including its
     * refusals - it will not discard a map holding a sold seat, nor one where a guest is mid
     * checkout, because both would destroy something somebody is relying on.
     */
    public function revertOccurrence(Request $request, $subdomain, $hash)
    {
        [, $event, $map] = $this->resolveOccurrence($request, $subdomain, $hash);

        $reverted = app(\App\Services\SeatingMapService::class)->revertToTemplate($map);

        return redirect()->route('event.edit', ['subdomain' => $subdomain, 'hash' => $hash])
            ->with($reverted ? 'message' : 'error', $reverted
                ? __('messages.seating_reverted_to_template')
                : __('messages.seating_cannot_revert'));
    }

    /**
     * @return array{0: Role, 1: \App\Models\Event, 2: \App\Models\EventSeatingMap}
     */
    protected function resolveOccurrence(Request $request, $subdomain, $hash): array
    {
        $role = $this->gate($request, $subdomain);

        $event = \App\Models\Event::whereHas('roles', fn ($q) => $q->where('roles.id', $role->id))
            ->findOrFail(UrlUtils::decodeId($hash));

        if (! $request->user()->canEditEvent($event)) {
            abort(403);
        }

        $date = $request->input('date');

        if ($date !== null && ! \App\Models\Event::isOccurrenceDate($date)) {
            abort(404);
        }

        $map = app(\App\Services\SeatingMapService::class)->materialize($event, $date);

        if (! $map) {
            abort(404);
        }

        return [$role, $event, $map];
    }

    /**
     * Editor rights on the schedule, then the Enterprise gate. Writes are refused outright rather
     * than silently scrubbed: unlike a checkbox on a bigger form, every one of these endpoints
     * exists only to edit a seat map, so there is nothing left to save.
     */
    protected function gate(Request $request, $subdomain, bool $json = false): Role
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $request->user() || ! $request->user()->isEditor($subdomain)) {
            abort(403);
        }

        if (! $role->seatingEnabled()) {
            abort(403, __('messages.not_authorized'));
        }

        return $role;
    }

    /**
     * Scoped to the schedule, so an id belonging to another venue's plan 404s rather than
     * resolving. The designer save path re-checks every nested id for the same reason.
     */
    protected function resolvePlan(Role $role, $hash): SeatingPlan
    {
        return SeatingPlan::where('role_id', $role->id)
            ->where('is_deleted', false)
            ->findOrFail(UrlUtils::decodeId($hash));
    }

    protected function back($subdomain, string $message)
    {
        return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'seating'])
            ->with('message', $message);
    }
}
