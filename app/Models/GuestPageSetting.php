<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

/**
 * What a schedule's guest page shows (Style > Events). One row per schedule whose owner has saved
 * the form since it existed; no row reads as the defaults (Role::guestPageSetting()). See the
 * migration (2026_10_27_000000) for why this is not on `roles`.
 */
class GuestPageSetting extends Model
{
    public const DEFAULTS = ['show_next_event' => true, 'page_mode' => 'auto', 'list_image_size' => 'small'];

    /**
     * list_image_size: how large each event's picture is drawn in the list (Style > Events), the
     * stage's .gk-img-m and .gk-img-l. One or two coming events are large cards whatever it says.
     */
    public const LIST_IMAGE_SIZES = ['small', 'medium', 'large'];

    /**
     * page_mode: how the guest pages are drawn (Style > Background > Page style): 'auto' (light or
     * dark by the background, GuestTheme::pageMode()), or one of the portal's six palettes
     * (GuestTheme::PALETTES), which sets the brightness and the page's own colours.
     */
    public const MODES = ['auto', 'sand', 'mist', 'paper', 'espresso', 'midnight', 'carbon'];

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
        } catch (QueryException $e) {
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
