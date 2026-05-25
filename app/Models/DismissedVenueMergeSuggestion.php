<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DismissedVenueMergeSuggestion extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'venue_ids_hash',
    ];

    public static function hashForVenueIds(array $venueIds): string
    {
        $sorted = array_map('intval', $venueIds);
        sort($sorted, SORT_NUMERIC);

        return hash('sha256', implode(',', $sorted));
    }
}
