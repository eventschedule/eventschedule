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
use Illuminate\Http\JsonResponse;
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

        // The tab has no name field: this is a one-click create. The flash tells the designer to
        // focus and select its name box so the first keystroke replaces "Untitled plan". A flash
        // rather than a ?new=1 query param, so it fires once and does not survive a refresh.
        return redirect()->route('seating.design', [
            'subdomain' => $subdomain,
            'hash' => UrlUtils::encodeId($plan->id),
        ])->with('seating_plan_created', true);
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
            'usage' => $plan->usage(),
        ]);
    }

    public function structure(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain, json: true);
        $plan = $this->resolvePlan($role, $hash);

        return response()->json($this->structurePayload($plan));
    }

    public function saveStructure(Request $request, $subdomain, $hash)
    {
        $role = $this->gate($request, $subdomain, json: true);
        $plan = $this->resolvePlan($role, $hash);

        if ($stale = $this->staleRevision($request, $plan)) {
            return $stale;
        }

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
        }

        $plan->forceFill($this->orphanRuleInput($request));
        $plan->save();

        // Always, even when nothing else on the row changed: without it a second editor's stale
        // payload still looks current and overwrites this one.
        $plan->bumpStructureRevision();

        return response()->json($this->structurePayload($plan));
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

        return response()->json($this->structurePayload($map));
    }

    public function saveOccurrenceStructure(Request $request, $subdomain, $hash)
    {
        [, , $map] = $this->resolveOccurrence($request, $subdomain, $hash);

        if ($stale = $this->staleRevision($request, $map)) {
            return $stale;
        }

        try {
            // The same ceiling the template path enforces. Without it a per-date edit could grow a
            // room past every cap the designer, the report and the box office are sized for.
            $data = ['levels' => $request->input('levels', [])];
            $this->structure->assertWithinLimits($data);
            $this->structure->save($map, $data);
        } catch (BusinessException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.seating_save_failed')], 500);
        }

        if ($rules = $this->orphanRuleInput($request)) {
            $map->forceFill($rules)->save();
        }

        // Doubles as the poll cursor: nothing else bumped a snapshot's version on a structural
        // edit, so a picker or box office console left open on this date never learned that the
        // layout under it had changed.
        $map->bumpStructureRevision();

        return response()->json($this->structurePayload($map));
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
    /**
     * The single-seat rule's settings, clamped, or [] when the request did not send them.
     *
     * "Not posted = leave alone", the same rule seating_plan_id and seating_band already follow: an
     * older client, or a payload built by hand, must not silently reset a venue's decision to the
     * column defaults.
     */
    private function orphanRuleInput(Request $request): array
    {
        if (! $request->has('orphan_rule_enabled')) {
            return [];
        }

        return [
            'orphan_rule_enabled' => $request->boolean('orphan_rule_enabled'),
            // 1 = never leave a single seat alone. Above 4 the rule starts refusing ordinary
            // selections, so the input is bounded rather than free.
            'orphan_rule_min_gap' => max(1, min(4, (int) $request->input('orphan_rule_min_gap', 1))),
            // Percent sold past which the rule lifts, so it cannot block the last few seats.
            'orphan_rule_lift_pct' => max(0, min(100, (int) $request->input('orphan_rule_lift_pct', 90))),
        ];
    }

    protected function resolveOccurrence(Request $request, $subdomain, $hash): array
    {
        $role = $this->gate($request, $subdomain);

        $event = Event::whereHas('roles', fn ($q) => $q->where('roles.id', $role->id))
            ->findOrFail(UrlUtils::decodeId($hash));

        if (! $request->user()->canEditEvent($event)) {
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
    /**
     * The structure, plus a revision the designer hands back when it saves.
     *
     * A counter, never updated_at: SeatingStructureService::save() writes only the CHILD rows, so
     * the owner row does not move on its own - and a MySQL timestamp is second-resolution, so a
     * read and a save inside the same second would compare equal and let the overwrite through.
     */
    private function structurePayload(SeatingPlan|EventSeatingMap $owner): array
    {
        return $this->structure->toArray($owner) + [
            'revision' => $owner->structureRevision(),
            // Same three keys whichever owner this is: the designer edits a template's defaults or
            // one date's own settings through one panel, exactly as it does the layout.
            'rules' => [
                'orphan_rule_enabled' => (bool) $owner->orphan_rule_enabled,
                'orphan_rule_min_gap' => (int) $owner->orphan_rule_min_gap,
                'orphan_rule_lift_pct' => (int) $owner->orphan_rule_lift_pct,
            ],
        ];
    }

    /**
     * Refuse a save built on a structure that somebody else has since replaced.
     *
     * The designer posts the WHOLE structure and `removeMissing` deletes anything not in it, so
     * two admins with the same plan open would otherwise silently erase each other's work: last
     * writer wins, no warning, and nothing on either screen to notice it happened.
     *
     * A payload with no revision at all is allowed through - it is an older client or a caller
     * that never read one, and refusing those would break them for no gain.
     */
    private function staleRevision(Request $request, SeatingPlan|EventSeatingMap $owner): ?JsonResponse
    {
        $sent = $request->input('revision');

        if ($sent === null || ! is_scalar($sent) || (int) $sent === $owner->structureRevision()) {
            return null;
        }

        return response()->json(['error' => __('messages.seating_stale_revision')], 409);
    }

    protected function gate(Request $request, $subdomain, bool $json = false): Role
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $request->user() || ! $request->user()->isEditor($subdomain)) {
            abort(403);
        }

        if (! $role->seatingEnabled()) {
            abort(403, __('messages.not_authorized'));
        }

        // Venues only. A plan is a drawing of a room, and only a venue has one - the tab is
        // hidden everywhere else, so reaching any of these is a hand-typed URL. resolveOccurrence()
        // calls through here too, which is what closes the four per-date editor routes as well.
        //
        // Deliberately NOT folded into seatingEnabled(): BoxOfficeController resolves its role from
        // the URL subdomain, so a curator cross-listing a venue's seated show must still be able to
        // sell from the map and run the door. Owning the drawing is venue work; the door is not.
        if (! $role->isVenue()) {
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
