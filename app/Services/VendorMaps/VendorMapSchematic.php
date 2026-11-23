<?php

namespace App\Services\VendorMaps;

use App\Models\VendorMap;
use App\Models\VendorMapLandmark;
use App\Models\VendorMapOccupant;

/**
 * The market as people read it at a glance: each area a block of equal stalls in rows, labelled
 * with its stall numbers and what is sold there, the areas placed roughly where they are.
 *
 * The entrance sign and the visitors' phone map draw THIS, not the plan as drawn: a plan's stalls
 * come in every size and angle, and on a phone or a sign that read as noise ("put the vendors in
 * rows and columns"). The designer and the organizer's tab still draw the plan itself.
 *
 * Geography is kept where it matters: an area that spans most of the market is a full-width band,
 * the others fall into two columns by which half of the market they are in, and each column runs
 * top to bottom. Left stays left in a right-to-left language: it is a place, not a sentence.
 */
class VendorMapSchematic
{
    /** A row of more stalls than this wraps, so a sign's cells stay legible. */
    public const MAX_COLS = 8;

    /**
     * @param  array  $payload  VendorMapPresenter::publicPayload()
     * @return array{sections: array, amenities: array, entrance: ?string, here: bool, open: int}
     */
    public static function build(VendorMap $map, array $payload): array
    {
        $spots = collect($payload['spots'] ?? []);
        $areas = collect($payload['areas'] ?? [])->keyBy('id');
        $kind = $payload['kind'] ?? $map->kind();

        // What each stall sells over the season, so a sign printed with no date still says it.
        $seasonCats = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('event_date', VendorMapOccupant::SEASON)
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->get(['spot_id', 'categories'])
            ->keyBy('spot_id');

        $groups = $spots->groupBy(fn ($s) => $s['area'] ?? 0);
        $zones = [];
        foreach ($groups as $areaId => $list) {
            $area = $areas->get($areaId);
            $zones[] = self::zone($list->values()->all(), $area, $seasonCats, $kind);
        }

        if ($zones === []) {
            return ['sections' => [], 'amenities' => self::amenities($payload), 'entrance' => null, 'here' => false, 'open' => 0, 'places' => [], 'hereAt' => null];
        }

        // Which areas span the market, and which half the others are in.
        $minX = min(array_column($zones, 'x0'));
        $maxX = max(array_column($zones, 'x1'));
        $width = max(1, $maxX - $minX);
        $mid = $minX + $width / 2;
        foreach ($zones as &$z) {
            $z['span'] = ($z['x1'] - $z['x0']) >= $width * 0.55 ? 'full' : (($z['x0'] + $z['x1']) / 2 < $mid ? 'a' : 'b');
        }
        unset($z);

        usort($zones, fn ($p, $q) => [$p['y0'], $p['x0']] <=> [$q['y0'], $q['x0']]);

        // Full-width bands in order, and between them a block of two columns.
        $sections = [];
        foreach ($zones as $z) {
            if ($z['span'] === 'full') {
                $sections[] = ['type' => 'full', 'zone' => self::public($z)];

                continue;
            }
            $last = count($sections) - 1;
            if ($last < 0 || $sections[$last]['type'] !== 'cols') {
                $sections[] = ['type' => 'cols', 'a' => [], 'b' => []];
                $last++;
            }
            $sections[$last][$z['span']][] = self::public($z);
        }

        // The two columns' widths follow their widest blocks, so equal stalls stay equal.
        foreach ($sections as &$sec) {
            if ($sec['type'] === 'cols') {
                $sec['fa'] = max(array_merge([1], array_column($sec['a'], 'cols')));
                $sec['fb'] = max(array_merge([1], array_column($sec['b'], 'cols')));
                if ($sec['a'] === [] || $sec['b'] === []) {
                    // One column only: it is a full-width band after all.
                    $sec = ['type' => 'stack', 'zones' => array_merge($sec['a'], $sec['b'])];
                }
            }
        }
        unset($sec);

        $hasEntrance = collect($payload['landmarks'] ?? [])->contains('kind', 'entrance');

        return [
            'sections' => $sections,
            'amenities' => self::amenities($payload),
            'entrance' => $hasEntrance ? __('messages.vm_main_entrance') : null,
            'here' => ! empty($payload['here']),
            'places' => self::places($payload),
            // The owner's picture of the market under the overview, laid over the whole plan.
            'picture' => $map->background_public && $map->background_path && $map->role
                ? route('vendor_map.public_picture', ['subdomain' => $map->role->subdomain]).'?v='.substr(md5($map->background_path), 0, 8)
                : null,
            'frame' => [(int) ($map->width ?: 1000), (int) ($map->height ?: 700)],
            'hereAt' => ! empty($payload['here']) ? [(int) $payload['here']['x'], (int) $payload['here']['y']] : null,
            'open' => $spots->where('state', 'present')->count(),
        ];
    }

    /** One area as a grid: rows found from the stalls' own positions, left to right in each. */
    private static function zone(array $spots, ?array $area, $seasonCats, string $kind): array
    {
        $heights = array_map(fn ($s) => max(1, (int) $s['h']), $spots);
        sort($heights);
        $tolerance = max(6, $heights[intdiv(count($heights), 2)] * 0.5);

        usort($spots, fn ($p, $q) => ($p['y'] + $p['h'] / 2) <=> ($q['y'] + $q['h'] / 2));
        $rows = [];
        foreach ($spots as $s) {
            $cy = $s['y'] + $s['h'] / 2;
            $placed = false;
            foreach ($rows as &$row) {
                if (abs($row['cy'] - $cy) <= $tolerance) {
                    $row['spots'][] = $s;
                    $placed = true;
                    break;
                }
            }
            unset($row);
            if (! $placed) {
                $rows[] = ['cy' => $cy, 'spots' => [$s]];
            }
        }

        // Each row left to right; a row longer than a sign can read wraps.
        $grid = [];
        foreach ($rows as $row) {
            usort($row['spots'], fn ($p, $q) => $p['x'] <=> $q['x']);
            foreach (array_chunk($row['spots'], self::MAX_COLS) as $chunk) {
                $grid[] = $chunk;
            }
        }
        $cols = max(1, ...array_map('count', $grid));

        // Stalls placed in columns by where they stand, so a row with a gap keeps its stalls
        // opposite their neighbours (a missing stall is a gap, not a shift to the left).
        $reference = array_reduce($grid, fn ($best, $line) => count($line) > count($best) ? $line : $best, []);
        $centres = array_map(fn ($s) => $s['x'] + $s['w'] / 2, $reference);
        $matrix = [];
        foreach ($grid as $line) {
            $placed = array_fill(0, $cols, null);
            foreach ($line as $s) {
                $cx = $s['x'] + $s['w'] / 2;
                $order = array_keys($centres);
                usort($order, fn ($a, $b) => abs($centres[$a] - $cx) <=> abs($centres[$b] - $cx));
                foreach ($order as $c) {
                    if ($placed[$c] === null) {
                        $placed[$c] = $s;
                        break;
                    }
                }
            }
            $matrix[] = $placed;
        }

        // An area much taller than wide (a line of stalls down one side, two aisles of a hall) is
        // laid on its side: these are blocks, not geography, and a column of single stalls would
        // take a sign's whole height. The plan keeps the area's real shape (its box).
        if (count($matrix) > $cols * 2 && count($matrix) <= self::MAX_COLS) {
            $turned = [];
            for ($c = 0; $c < $cols; $c++) {
                $line = [];
                foreach ($matrix as $r => $row) {
                    $line[$r] = $row[$c];
                }
                $turned[] = $line;
            }
            $matrix = $turned;
            $cols = count($matrix[0] ?? []) ?: 1;
        }

        // Rows that hold nothing at all are dropped; gaps inside a row stay.
        $cells = [];
        foreach ($matrix as $line) {
            if (count(array_filter($line)) === 0) {
                continue;
            }
            foreach ($line as $s) {
                $cells[] = $s ? self::cell($s) : null;
            }
        }

        $labels = array_map(fn ($s) => (string) $s['label'], $spots);
        $numeric = array_filter($labels, fn ($l) => ctype_digit($l));
        $range = '';
        if (count($numeric) === count($labels) && $labels) {
            $range = min(array_map('intval', $labels)).'–'.max(array_map('intval', $labels));
        } elseif ($labels) {
            sort($labels, SORT_NATURAL);
            $range = $labels[0].'–'.end($labels);
        }

        // What is sold here: the two categories most stalls carry, today or over the season.
        $count = [];
        foreach ($spots as $s) {
            $cats = $s['vendor']['categories'] ?? ($seasonCats->get($s['id'])?->categories ?? []);
            foreach (array_slice((array) $cats, 0, 2) as $c) {
                if (VendorMapKinds::isCategory($kind, $c) && $c !== 'other') {
                    $count[$c] = ($count[$c] ?? 0) + 1;
                }
            }
        }
        arsort($count);
        $sells = implode(', ', array_map(fn ($c) => mb_strtolower(__('messages.vm_cat_'.$c)), array_slice(array_keys($count), 0, 2)));

        return [
            'id' => $area['id'] ?? null,
            'name' => $area['name'] ?? '',
            'color' => $area['color'] ?? '#64748B',
            'range' => $range,
            'sells' => $sells,
            'cols' => $cols,
            'cells' => $cells,
            'x0' => min(array_map(fn ($s) => $s['x'], $spots)),
            'x1' => max(array_map(fn ($s) => $s['x'] + $s['w'], $spots)),
            'y0' => min(array_map(fn ($s) => $s['y'], $spots)),
            'y1' => max(array_map(fn ($s) => $s['y'] + $s['h'], $spots)),
        ];
    }

    private static function cell(array $s): array
    {
        return [
            'id' => $s['id'],
            'label' => (string) $s['label'],
            'state' => $s['state'] ?? 'free',
            'has_vendor' => ! empty($s['vendor']),
        ];
    }

    private static function public(array $z): array
    {
        // Where the area is on the plan, for the phone's overview of areas (AreaMap.vue).
        $z['box'] = [(int) $z['x0'], (int) $z['y0'], (int) $z['x1'], (int) $z['y1']];
        unset($z['x0'], $z['x1'], $z['y0'], $z['y1']);

        return $z;
    }

    /** The landmarks where they are, named, for the overview of areas. */
    private static function places(array $payload): array
    {
        $out = [];
        foreach ($payload['landmarks'] ?? [] as $l) {
            $label = trim((string) ($l['label'] ?? ''));
            if ($label === '' && in_array($l['kind'] ?? '', VendorMapLandmark::KINDS, true) && $l['kind'] !== 'text') {
                $label = __('messages.vm_landmark_'.$l['kind']);
            }
            if ($label === '') {
                continue;
            }
            $out[] = ['kind' => $l['kind'], 'label' => $label, 'box' => [(int) $l['x'], (int) $l['y'], (int) ($l['x'] + $l['w']), (int) ($l['y'] + $l['h'])]];
        }

        return $out;
    }

    /** Parking, restrooms, a stage, named places: small labels under the map. */
    private static function amenities(array $payload): array
    {
        $out = [];
        foreach ($payload['landmarks'] ?? [] as $l) {
            if (($l['kind'] ?? '') === 'entrance') {
                continue;
            }
            $label = trim((string) ($l['label'] ?? ''));
            if ($label === '' && in_array($l['kind'], VendorMapLandmark::KINDS, true) && $l['kind'] !== 'text') {
                $label = __('messages.vm_landmark_'.$l['kind']);
            }
            if ($label !== '' && ! in_array($label, array_column($out, 'label'), true)) {
                $out[] = ['kind' => $l['kind'], 'label' => $label];
            }
        }

        return $out;
    }
}
