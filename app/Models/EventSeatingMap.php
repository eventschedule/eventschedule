<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * One occurrence's own seat map: the snapshot taken from a SeatingPlan template.
 *
 * Keyed by (event_id, event_date) where event_date is a Y-m-d STRING, matching
 * sales.event_date and ticket_waitlists. Always resolve a missing date through
 * SeatingMapService so the map and the oversell guard key identically.
 */
class EventSeatingMap extends Model
{
    protected $fillable = [
        'event_id',
        'event_date',
        'seating_plan_id',
        'orphan_rule_enabled',
        'orphan_rule_min_gap',
        'orphan_rule_lift_pct',
        'materialized_at',
    ];

    protected $casts = [
        'orphan_rule_enabled' => 'boolean',
        'orphan_rule_min_gap' => 'integer',
        'orphan_rule_lift_pct' => 'integer',
        'version' => 'integer',
        'materialized_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function seatingPlan()
    {
        return $this->belongsTo(SeatingPlan::class);
    }

    public function levels()
    {
        return $this->hasMany(SeatingLevel::class)->orderBy('position');
    }

    public function sections()
    {
        return $this->hasMany(SeatingSection::class)->where('is_deleted', false)->orderBy('position');
    }

    public function seats()
    {
        return $this->hasMany(SeatingSeat::class);
    }

    public function ownerAttributes(): array
    {
        return ['seating_plan_id' => null, 'event_seating_map_id' => $this->id];
    }

    /**
     * Claim the next state version for a batch of seat changes.
     *
     * A plain increment() followed by a SELECT is NOT safe: the two statements are separate, so
     * two callers can read back the same number and a client polling "> N" then misses a batch for
     * good. LAST_INSERT_ID(expr) stashes the new value in this CONNECTION's own slot as part of
     * the update, so the read below cannot see anybody else's. MySQL-only, which this app is.
     *
     * Callers should still stamp the returned value onto the seats they touch inside one
     * transaction - that is what makes a batch land atomically, and is separate from this
     * method's job of handing out a unique number.
     */
    /**
     * The owner-agnostic half of the concurrency protocol SeatingPlanController uses. A snapshot
     * already has a monotonic cursor, so this is just `version` under the shared name.
     */
    public function structureRevision(): int
    {
        return (int) ($this->version ?? 1);
    }

    public function bumpStructureRevision(): int
    {
        return $this->bumpVersion();
    }

    public function bumpVersion(): int
    {
        DB::update('update `'.$this->getTable().'` set `version` = LAST_INSERT_ID(`version` + 1) where `id` = ?', [$this->id]);

        $version = (int) DB::selectOne('select LAST_INSERT_ID() as v')->v;
        $this->setAttribute('version', $version);
        $this->syncOriginalAttribute('version');

        return $version;
    }
}
