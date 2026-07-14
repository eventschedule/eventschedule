<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A translation improvement shared by another EventSchedule install.
 * Only populated on the nexus app (eventschedule.com), where an admin
 * reviews suggestions and approves the ones that should ship with the app.
 */
class TranslationSuggestion extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'instance_id',
        'locale',
        'group',
        'key',
        'suggested_value',
        'shipped_value',
        'app_version',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
