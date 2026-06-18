<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DismissedTimezoneWarning extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'events_hash',
    ];

    /**
     * Hash of the set of off-timezone event ids the warning was dismissed for. Keying dismissal on
     * the set means the banner re-appears automatically when a new off-timezone event shows up.
     */
    public static function hashForEventIds(array $eventIds): string
    {
        $sorted = array_map('intval', $eventIds);
        sort($sorted, SORT_NUMERIC);

        return hash('sha256', implode(',', $sorted));
    }
}
