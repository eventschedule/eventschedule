<?php

namespace App\Traits;

use App\Models\EventSeatingMap;
use App\Models\SeatingPlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared by every row that belongs to EITHER a seating plan template or one
 * occurrence's snapshot: levels, sections and seats.
 *
 * Keeping both shapes in one set of tables is what lets a single designer component
 * and a single renderer serve the template and the per-date map. The cost is an
 * invariant the database does not express, so it is asserted here on save: exactly
 * one of seating_plan_id / event_seating_map_id is set, never both and never neither.
 * A row with both would be reachable from a template AND a live map, and editing the
 * template would silently mutate sold seats - the exact failure this whole design
 * exists to prevent.
 */
trait SeatingOwnable
{
    public static function bootSeatingOwnable(): void
    {
        static::saving(function (Model $model) {
            $hasPlan = ! empty($model->seating_plan_id);
            $hasMap = ! empty($model->event_seating_map_id);

            if ($hasPlan === $hasMap) {
                throw new \LogicException(
                    class_basename($model).' must belong to exactly one of a seating plan or an event seating map.'
                );
            }
        });
    }

    public function seatingPlan()
    {
        return $this->belongsTo(SeatingPlan::class);
    }

    public function eventSeatingMap()
    {
        return $this->belongsTo(EventSeatingMap::class);
    }

    /**
     * Scope to whichever owner was passed. Callers hold a SeatingPlan when editing the
     * template and an EventSeatingMap when editing one date, and should never have to
     * branch on which.
     */
    public function scopeForOwner(Builder $query, SeatingPlan|EventSeatingMap $owner): Builder
    {
        return $owner instanceof SeatingPlan
            ? $query->where('seating_plan_id', $owner->id)
            : $query->where('event_seating_map_id', $owner->id);
    }

    /**
     * The [column => id] pair naming this row's owner, for copying it onto children.
     */
    public function ownerAttributes(): array
    {
        return $this->seating_plan_id
            ? ['seating_plan_id' => $this->seating_plan_id, 'event_seating_map_id' => null]
            : ['seating_plan_id' => null, 'event_seating_map_id' => $this->event_seating_map_id];
    }

    public function isTemplate(): bool
    {
        return ! empty($this->seating_plan_id);
    }
}
