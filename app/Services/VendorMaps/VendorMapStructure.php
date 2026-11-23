<?php

namespace App\Services\VendorMaps;

use App\Models\VendorMap;
use App\Models\VendorMapArea;
use App\Models\VendorMapLandmark;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The designer's JSON, in and out.
 *
 * Spot ids are stable across saves: a spot the designer still sends keeps its id, so the vendors
 * on it stay on it. A spot the designer no longer sends is deleted only if nobody has ever been
 * on it; otherwise it is RETIRED (kept, hidden), and retiring the spot of a season vendor is
 * refused until the vendor is moved, because their mail and their page say that stall.
 * A save names the revision it was made from, and a save from an older one is refused: two
 * people editing one map would otherwise undo each other without a word.
 */
class VendorMapStructure
{
    public const MAX_SPOTS = 400;

    public const MAX_AREAS = 30;

    public const MAX_LANDMARKS = 100;

    public static function toArray(VendorMap $map): array
    {
        $map->loadMissing(['areas', 'landmarks']);
        $spots = $map->spots()->orderBy('id')->get();

        // The season's name on each spot (a vendor page or a name only), so the designer can
        // show and edit names. One query, not one per spot.
        $season = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('event_date', VendorMapOccupant::SEASON)
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->with('vendor:id,name,name_en')
            ->get()
            ->keyBy('spot_id');

        return [
            'revision' => (int) $map->revision,
            'width' => (int) $map->width,
            'height' => (int) $map->height,
            'kind' => $map->kind(),
            'here' => $map->here_x !== null ? ['x' => $map->here_x, 'y' => $map->here_y] : null,
            'areas' => $map->areas->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'color' => $a->color])->values()->all(),
            'spots' => $spots->map(function ($s) use ($season) {
                $occ = $season->get($s->id);

                return [
                    'id' => $s->id,
                    'label' => $s->label,
                    'area_id' => $s->area_id,
                    'x' => $s->x, 'y' => $s->y, 'w' => $s->w, 'h' => $s->h,
                    'rotation' => $s->rotation,
                    'shape' => $s->shape,
                    'name' => $occ?->displayName(),
                    'category' => $occ?->categories[0] ?? null,
                    // A vendor's own page is theirs: the designer shows the name and does not edit it.
                    'name_locked' => $occ && ! $occ->isPlaceholder(),
                ];
            })->values()->all(),
            'landmarks' => $map->landmarks->map(fn ($l) => [
                'id' => $l->id, 'kind' => $l->kind, 'label' => $l->label,
                'x' => $l->x, 'y' => $l->y, 'w' => $l->w, 'h' => $l->h, 'rotation' => $l->rotation,
            ])->values()->all(),
        ];
    }

    /**
     * @return array{revision: int, renumbered: int} renumbered = spots with a vendor whose number changed
     *
     * @throws ValidationException
     */
    public static function save(VendorMap $map, array $payload): array
    {
        $areas = array_values((array) ($payload['areas'] ?? []));
        $spots = array_values((array) ($payload['spots'] ?? []));
        $landmarks = array_values((array) ($payload['landmarks'] ?? []));

        if (count($spots) > self::MAX_SPOTS || count($areas) > self::MAX_AREAS || count($landmarks) > self::MAX_LANDMARKS) {
            throw ValidationException::withMessages(['spots' => __('messages.vm_too_many', ['spots' => self::MAX_SPOTS])]);
        }

        return DB::transaction(function () use ($map, $payload, $areas, $spots, $landmarks) {
            $locked = VendorMap::whereKey($map->id)->lockForUpdate()->first();

            if ((int) ($payload['revision'] ?? -1) !== (int) $locked->revision) {
                throw ValidationException::withMessages(['revision' => __('messages.vm_revision_clash')]);
            }

            $width = self::clamp($payload['width'] ?? $locked->width, 200, 4000);
            $height = self::clamp($payload['height'] ?? $locked->height, 200, 4000);

            // Areas first: spots point at them, by id or by the temporary key a new one carries.
            $areaKeys = [];
            $keptAreas = [];
            foreach ($areas as $i => $a) {
                $name = trim((string) ($a['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $color = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($a['color'] ?? '')) ? $a['color'] : '#4E81FA';
                $area = isset($a['id']) && is_numeric($a['id'])
                    ? VendorMapArea::where('vendor_map_id', $map->id)->find((int) $a['id'])
                    : null;
                $area ??= new VendorMapArea(['vendor_map_id' => $map->id]);
                $area->fill(['name' => mb_substr($name, 0, 80), 'color' => $color, 'sort_order' => $i])->save();
                $areaKeys[(string) ($a['id'] ?? $a['key'] ?? '')] = $area->id;
                $keptAreas[] = $area->id;
            }
            VendorMapArea::where('vendor_map_id', $map->id)->whereNotIn('id', $keptAreas ?: [0])->delete();

            // Spots.
            $existing = VendorMapSpot::where('vendor_map_id', $map->id)->whereNull('retired_at')->get()->keyBy('id');
            $kept = [];
            $renumbered = 0;
            $placeholders = [];
            foreach ($spots as $s) {
                $label = mb_substr(trim((string) ($s['label'] ?? '')), 0, 20);
                if ($label === '') {
                    continue;
                }
                $spot = isset($s['id']) && is_numeric($s['id']) ? $existing->get((int) $s['id']) : null;
                $isNew = ! $spot;
                $spot ??= new VendorMapSpot(['vendor_map_id' => $map->id]);

                if (! $isNew && $spot->label !== $label && self::hasVendor($spot->id)) {
                    $renumbered++;
                }

                $areaRef = $s['area_id'] ?? $s['area_key'] ?? null;
                $spot->fill([
                    'label' => $label,
                    'area_id' => $areaRef !== null ? ($areaKeys[(string) $areaRef] ?? null) : null,
                    'x' => self::clamp($s['x'] ?? 0, -100, $width + 100),
                    'y' => self::clamp($s['y'] ?? 0, -100, $height + 100),
                    'w' => self::clamp($s['w'] ?? 40, 8, 600),
                    'h' => self::clamp($s['h'] ?? 30, 8, 600),
                    'rotation' => self::clamp($s['rotation'] ?? 0, -180, 180),
                    'shape' => self::shape($s['shape'] ?? null),
                ])->save();
                $kept[] = $spot->id;

                if (array_key_exists('name', $s)) {
                    $placeholders[$spot->id] = [
                        'name' => mb_substr(trim((string) $s['name']), 0, 120),
                        'category' => is_string($s['category'] ?? null) ? $s['category'] : null,
                        'sells' => isset($s['sells']) ? mb_substr(trim((string) $s['sells']), 0, 200) : null,
                    ];
                }
            }

            foreach ($existing->except($kept) as $gone) {
                self::removeSpot($gone);
            }

            self::syncPlaceholders($map, $placeholders);

            // Landmarks: small, nothing points at them, so replaced as sent.
            $keptLandmarks = [];
            foreach ($landmarks as $l) {
                $kind = in_array($l['kind'] ?? null, VendorMapLandmark::KINDS, true) ? $l['kind'] : 'text';
                $landmark = isset($l['id']) && is_numeric($l['id'])
                    ? VendorMapLandmark::where('vendor_map_id', $map->id)->find((int) $l['id'])
                    : null;
                $landmark ??= new VendorMapLandmark(['vendor_map_id' => $map->id]);
                $landmark->fill([
                    'kind' => $kind,
                    'label' => isset($l['label']) ? mb_substr(trim((string) $l['label']), 0, 80) : null,
                    'x' => self::clamp($l['x'] ?? 0, -100, $width + 100),
                    'y' => self::clamp($l['y'] ?? 0, -100, $height + 100),
                    'w' => self::clamp($l['w'] ?? 60, 8, 1000),
                    'h' => self::clamp($l['h'] ?? 40, 8, 1000),
                    'rotation' => self::clamp($l['rotation'] ?? 0, -180, 180),
                ])->save();
                $keptLandmarks[] = $landmark->id;
            }
            VendorMapLandmark::where('vendor_map_id', $map->id)->whereNotIn('id', $keptLandmarks ?: [0])->delete();

            $here = $payload['here'] ?? null;
            $locked->forceFill([
                'width' => $width,
                'height' => $height,
                'here_x' => is_array($here) ? self::clamp($here['x'] ?? 0, 0, $width) : null,
                'here_y' => is_array($here) ? self::clamp($here['y'] ?? 0, 0, $height) : null,
                'revision' => $locked->revision + 1,
            ])->save();

            return ['revision' => (int) $locked->revision, 'renumbered' => $renumbered];
        });
    }

    /** A spot has (or had) an approved vendor on it. */
    private static function hasVendor(int $spotId): bool
    {
        return VendorMapOccupant::where('spot_id', $spotId)->where('status', 'approved')->where('kind', 'present')->exists();
    }

    private static function removeSpot(VendorMapSpot $spot): void
    {
        $seasonVendor = VendorMapOccupant::where('spot_id', $spot->id)
            ->where('event_date', VendorMapOccupant::SEASON)
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->whereNotNull('vendor_role_id')
            ->exists();

        if ($seasonVendor) {
            throw ValidationException::withMessages(['spots' => __('messages.vm_cannot_remove_spot', ['number' => $spot->label])]);
        }

        // Names only (no vendor page) go with the spot; claims waiting on it are declined.
        VendorMapOccupant::where('spot_id', $spot->id)->whereNull('vendor_role_id')->delete();
        VendorMapOccupant::where('spot_id', $spot->id)->where('status', 'pending')
            ->update(['status' => 'declined', 'decided_at' => now()]);

        if (VendorMapOccupant::where('spot_id', $spot->id)->exists()) {
            $spot->forceFill(['retired_at' => now()])->save();
        } else {
            $spot->delete();
        }
    }

    /**
     * Names typed or confirmed in the designer become, or update, the spot's season name. A
     * vendor's own page is never renamed from here, and an empty name clears a name only.
     */
    private static function syncPlaceholders(VendorMap $map, array $placeholders): void
    {
        if ($placeholders === []) {
            return;
        }

        $season = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->whereIn('spot_id', array_keys($placeholders))
            ->where('event_date', VendorMapOccupant::SEASON)
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->get()
            ->keyBy('spot_id');

        foreach ($placeholders as $spotId => $p) {
            $row = $season->get($spotId);

            if ($row && $row->vendor_role_id) {
                continue;
            }

            $category = VendorMapKinds::isCategory($map->kind, $p['category']) ? [$p['category']] : null;

            if ($p['name'] === '') {
                $row?->delete();

                continue;
            }

            if ($row) {
                $row->fill(['placeholder_name' => $p['name']] + ($category ? ['categories' => $category] : []) + ($p['sells'] !== null ? ['sells' => $p['sells']] : []))->save();

                continue;
            }

            $new = new VendorMapOccupant([
                'vendor_map_id' => $map->id,
                'spot_id' => $spotId,
                'event_date' => VendorMapOccupant::SEASON,
                'kind' => 'present',
                'placeholder_name' => $p['name'],
                'categories' => $category,
                'sells' => $p['sells'] ?: null,
            ]);
            $new->status = 'approved';
            $new->decided_at = now();
            $new->save();
        }
    }

    private static function clamp($value, int $min, int $max): int
    {
        return max($min, min($max, (int) round((float) $value)));
    }

    private static function shape($shape): ?array
    {
        if (! is_array($shape) || count($shape) < 3 || count($shape) > 24) {
            return null;
        }

        $points = [];
        foreach ($shape as $p) {
            if (! is_array($p) || ! isset($p[0], $p[1])) {
                return null;
            }
            $points[] = [self::clamp($p[0], -1000, 5000), self::clamp($p[1], -1000, 5000)];
        }

        return $points;
    }
}
