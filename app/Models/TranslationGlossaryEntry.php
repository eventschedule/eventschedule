<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One term of a schedule's translation glossary: a name automatic translation keeps exactly as
 * written (translation NULL), or an expression with a fixed translation. Written only through
 * App\Services\TranslationGlossary::save(), which caps and cleans the list. See the migration
 * (2026_11_12_000004) for why this is not on `roles`.
 */
class TranslationGlossaryEntry extends Model
{
    protected $fillable = ['role_id', 'term', 'translation', 'position'];

    protected $casts = [
        'position' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** Whether the term is kept as written rather than given a translation. */
    public function keepsAsIs(): bool
    {
        return $this->translation === null || $this->translation === '';
    }
}
