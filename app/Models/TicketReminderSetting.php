<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

/**
 * When a schedule's ticket holders are reminded (Schedule settings > Tickets > Reminders). One row
 * per schedule whose owner has saved the row; no row reads as the defaults (Role::ticketReminderSetting()).
 * See the migration (2026_11_11_000000) for why this is not on `roles`.
 *
 * `enabled` NULL means the owner has not chosen, and the schedule follows
 * Role::optionalMailOnByDefault(): never read the column as the answer, ask Role::ticketRemindersOn().
 */
class TicketReminderSetting extends Model
{
    public const DEFAULTS = ['enabled' => null, 'hours_before' => 24];

    /** The choices the schedule form offers, in hours. Anything else posted is refused. */
    public const HOURS_OPTIONS = [3, 24, 48, 72];

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'hours_before' => 'integer',
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
        } catch (QueryException $e) {
            // Two first saves at once (two tabs): the other one made the row between this one's
            // look and its insert. This one's values still win.
            if (($e->errorInfo[1] ?? null) !== 1062) {
                throw $e;
            }
            self::where('role_id', $role->id)->update($values);
        }

        $role->unsetRelation('ticketReminderSetting');
    }
}
