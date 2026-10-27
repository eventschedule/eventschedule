<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What a schedule's guest page shows (Style > Events). One row per schedule whose owner has saved
 * the form since it existed; no row reads as the defaults (Role::guestPageSetting()). See the
 * migration (2026_10_27_000000) for why this is not on `roles`.
 */
class GuestPageSetting extends Model
{
    public const DEFAULTS = ['show_next_event' => true];

    protected $guarded = [];

    protected $casts = [
        'show_next_event' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Store a schedule's choices, by key of DEFAULTS. */
    public static function store(Role $role, array $values): void
    {
        $values = array_intersect_key($values, self::DEFAULTS);

        if ($values === []) {
            return;
        }

        try {
            self::updateOrCreate(['role_id' => $role->id], $values);
        } catch (\Illuminate\Database\QueryException $e) {
            // Two first saves at once (two tabs): the other one made the row between this one's
            // look and its insert. This one's values still win.
            if (($e->errorInfo[1] ?? null) !== 1062) {
                throw $e;
            }
            self::where('role_id', $role->id)->update($values);
        }

        $role->unsetRelation('guestPageSetting');
    }
}
