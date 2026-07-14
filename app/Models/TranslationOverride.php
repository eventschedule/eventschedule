<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single customized UI translation string, edited by an instance admin in the
 * admin panel. Overrides are the source of truth in the database and are
 * published as sparse PHP files under config('app.lang_overrides_path'),
 * where the translator loader merges them over resources/lang.
 */
class TranslationOverride extends Model
{
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
        'shared_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'shared_at' => 'datetime',
        ];
    }

    public function scopeUnshared($query)
    {
        return $query->whereNull('shared_at');
    }
}
