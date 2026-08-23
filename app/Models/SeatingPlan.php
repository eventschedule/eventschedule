<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A reusable seating plan template owned by a schedule. Never sold from directly -
 * SeatingMapService::materialize() copies it into an EventSeatingMap per occurrence.
 */
class SeatingPlan extends Model
{
    protected $fillable = [
        'role_id',
        'name',
        'description',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
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

    public function maps()
    {
        return $this->hasMany(EventSeatingMap::class);
    }

    /**
     * The [column => id] pair to stamp onto rows belonging to this owner. Mirrored by
     * EventSeatingMap so the copy in SeatingMapService never branches on owner type.
     */
    public function ownerAttributes(): array
    {
        return ['seating_plan_id' => $this->id, 'event_seating_map_id' => null];
    }

    /**
     * Seats defined by the template. Standing sections contribute their capacity
     * instead, since they have no seat rows.
     */
    public function seatCount(): int
    {
        // inLiveSection() so this agrees with what materialize() actually copies - copyStructure()
        // iterates sections(), which excludes soft-deleted ones.
        return $this->seats()->inLiveSection()->count();
    }

    /**
     * Seats this template defines for one price band, which is what a banded ticket's quantity is
     * set from on save so every existing quantity reader keeps working unchanged.
     */
    public function seatCountForBand(?string $band): int
    {
        if (! $band) {
            return 0;
        }

        $sectionIds = $this->sections()->where('band', $band)->pluck('id');

        return $sectionIds->isEmpty() ? 0 : $this->seats()->whereIn('seating_section_id', $sectionIds)->count();
    }

    /** Standing capacity for one band, for the same reason. */
    public function standingCapacityForBand(?string $band): int
    {
        return $band ? (int) $this->sections()->where('band', $band)->where('kind', 'standing')->sum('capacity') : 0;
    }

    public function standingCapacity(): int
    {
        return (int) $this->sections()->where('kind', 'standing')->sum('capacity');
    }
}
