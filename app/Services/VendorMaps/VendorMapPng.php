<?php

namespace App\Services\VendorMaps;

use App\Models\Role;
use App\Models\VendorMap;
use App\Utils\ImageUtils;
use App\Utils\QrCodeUtils;
use GdImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pictures of a vendor map, drawn with GD: the share card behind the vendor link's preview, the
 * map in a vendor's approval mail with their stall marked, and the printable entrance sign.
 *
 * Text goes through text(), which lays right-to-left runs out itself (GD draws every string left
 * to right, so Hebrew came out backwards) and picks Noto Sans Hebrew for Hebrew letters and Noto
 * Sans for the rest, as the event graphics do. GD does not join Arabic letters, so a string with
 * Arabic in it is not drawn at all: an Arabic map shows its stall numbers and no words.
 *
 * Geometry follows the designer: a spot or landmark is x and y of its top-left corner, w and h,
 * turned by `rotation` degrees about its centre; a spot with a `shape` is that polygon, in map
 * units. The sign never names a vendor: who stands where changes, a printed sign does not.
 */
class VendorMapPng
{
    private const BRAND = [78, 129, 250];

    private const BRAND_INK = [37, 99, 235];

    private const HERE = [220, 38, 38];

    private const INK = [15, 23, 42];

    private const QUIET = [100, 116, 139];

    /**
     * Render the 1200x630 share card and store it under a new public name, then forget the old
     * one. Never throws: a picture that cannot be made leaves the previous one in place.
     */
    public static function refreshShareImage(VendorMap $map): void
    {
        try {
            $role = $map->role;
            if (! $role || ! self::available() || ! $map->spots()->exists()) {
                return;
            }

            $png = self::inScheduleLocale($role, fn () => self::renderShare($role, $map));
            if (! $png) {
                return;
            }

            $disk = Storage::disk(config('filesystems.default'));
            $filename = 'vmshare_'.strtolower(Str::random(32)).'.png';
            $disk->put(ImageUtils::storagePathFor($filename), $png, ['visibility' => 'public']);

            $old = $map->share_image_path;
            $map->forceFill(['share_image_path' => $filename])->save();

            if ($old && $old !== $filename && preg_match('/^vmshare_[a-z0-9]{32}\.png$/', $old)) {
                try {
                    $disk->delete(ImageUtils::storagePathFor($old));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * The whole map, about 640x400, with one spot in brand blue and its label large: the picture
     * in a vendor's "you're at stall 14" mail. Always light, because it is mail.
     */
    public static function stallImage(VendorMap $map, int $spotId): ?string
    {
        try {
            if (! self::available()) {
                return null;
            }

            $plan = self::plan($map);
            if (! collect($plan['spots'])->contains('id', $spotId)) {
                return null;
            }

            $k = 2;
            [$w, $h] = [640, 400];
            $im = self::canvas($w * $k, $h * $k, [255, 255, 255]);

            self::drawPlan($im, $plan, [20 * $k, 20 * $k, ($w - 40) * $k, ($h - 40) * $k], [
                'k' => $k,
                'spotFill' => fn ($s) => $s['id'] === $spotId ? self::BRAND : [229, 231, 235],
                'spotStroke' => fn ($s) => $s['id'] === $spotId ? [29, 78, 216] : [203, 213, 225],
                'labelInk' => fn ($s) => $s['id'] === $spotId ? [255, 255, 255] : [100, 116, 139],
                'labelMin' => 11 * $k,
                'labelMax' => 18 * $k,
                'areaTint' => 0.94,
                'areaNames' => true,
                'areaNameMin' => 11 * $k,
                'areaNameMax' => 16 * $k,
                'landmarkLabels' => false,
                'highlight' => $spotId,
                'here' => null,
            ]);

            return self::png(self::downscale($im, $w, $h));
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * The entrance sign as an A4 portrait PNG at 300 dpi (2480x3508): headline, the map with
     * numbers, areas and landmarks, "You are here", and a QR code to the visitors' map.
     */
    public static function sign(Role $role, VendorMap $map): string
    {
        $map->setRelation('role', $role);

        return self::inScheduleLocale($role, function () use ($role, $map) {
            [$W, $H] = [2480, 3508];
            $M = 160;
            $rtl = self::scheduleRtl($role);
            $im = self::canvas($W, $H, [255, 255, 255]);
            $start = $rtl ? $W - $M : $M;
            $align = $rtl ? 'right' : 'left';

            // The schedule's name, small, above the headline.
            $name = self::ellipsize((string) $role->name, 64, false, $W - 2 * $M);
            self::text($im, $name, 64, $start, $M + 64, self::color($im, [71, 85, 105]), false, $align);

            // The headline, up to two lines.
            $headline = VendorMapKinds::say($map->kind(), 'sign_headline');
            [$size, $lines] = self::wrapToFit($headline, true, $W - 2 * $M, 2, 150, 96);
            $y = $M + 64 + 40;
            $ink = self::color($im, self::INK);
            foreach ($lines as $line) {
                $y += (int) round($size * 1.15);
                self::text($im, $line, $size, $start, $y, $ink, true, $align);
            }

            // The market as blocks of equal stalls (VendorMapSchematic), in a light panel: the
            // same layout as the printable page, so the print shop's copy says the same thing.
            $panelTop = $y + 70;
            $payload = VendorMapPresenter::publicPayload($role, $map, null);
            $schematic = VendorMapSchematic::build($map, $payload);
            // The panel is as tall as the blocks need, up to the room above the code.
            $inner = [$M + 70, $panelTop + 70, $W - 2 * $M - 140, 2560 - $panelTop - 140];
            $panelBottom = (int) min(2560, $panelTop + 140 + self::drawSchematic(null, $schematic, $inner, $rtl));
            $inner[3] = $panelBottom - $panelTop - 140;
            self::fillPoly($im, self::roundedRect($M, $panelTop, $W - 2 * $M, $panelBottom - $panelTop, 50), self::color($im, [246, 248, 251]));
            self::strokePoly($im, self::roundedRect($M, $panelTop, $W - 2 * $M, $panelBottom - $panelTop, 50), self::color($im, [226, 232, 240]), 4);

            self::drawSchematic($im, $schematic, $inner, $rtl);

            // The code in a solid band, the instruction beside it.
            $bandTop = $panelBottom + 90;
            $bandH = $H - $M - $bandTop;
            self::fillPoly($im, self::roundedRect($M, $bandTop, $W - 2 * $M, $bandH, 60), self::color($im, [29, 78, 216]));
            $tile = (int) min(820, $bandH - 140);
            $tileX = $rtl ? $W - $M - 70 - $tile : $M + 70;
            $tileY = (int) ($bandTop + ($bandH - $tile) / 2);
            self::fillPoly($im, self::roundedRect($tileX, $tileY, $tile, $tile, 32), self::color($im, [255, 255, 255]));
            $url = VendorMapPresenter::signQrUrl($role, $map);
            $qr = @imagecreatefromstring(QrCodeUtils::png($url, 760, 0));
            if ($qr) {
                imagecopyresampled($im, $qr, $tileX + 34, $tileY + 34, 0, 0, $tile - 68, $tile - 68, imagesx($qr), imagesy($qr));
                imagedestroy($qr);
            }

            $textW = $W - 2 * $M - 140 - $tile - 90;
            $textX = $rtl ? $tileX - 90 : $tileX + $tile + 90;
            $white = self::color($im, [255, 255, 255]);
            [$tSize, $tLines] = self::wrapToFit(VendorMapKinds::say($map->kind(), 'scan_title'), true, $textW, 2, 104, 72);
            [$bSize, $bLines] = self::wrapToFit(__('messages.vm_sign_scan_body'), false, $textW, 4, 58, 44);
            $short = self::ellipsize(preg_replace('#^https?://#', '', VendorMapPresenter::publicUrl($role, true)), 50, true, $textW);
            $blockH = count($tLines) * $tSize * 1.15 + 36 + count($bLines) * $bSize * 1.4 + 40 + 50;
            $ty = $bandTop + ($bandH - $blockH) / 2;
            foreach ($tLines as $line) {
                $ty += $tSize * 1.15;
                self::text($im, $line, $tSize, $textX, $ty, $white, true, $align);
            }
            $ty += 36;
            $soft = self::color($im, [219, 234, 254]);
            foreach ($bLines as $line) {
                $ty += $bSize * 1.4;
                self::text($im, $line, $bSize, $textX, $ty, $soft, false, $align);
            }
            self::text($im, $short, 50, $textX, $ty + 40 + 50, $white, true, $align);

            return self::png($im, 6);
        });
    }

    /**
     * The blocks of VendorMapSchematic::build() into $rect (x, y, w, h): the largest stalls that
     * fit, every stall drawn the same (a sign is read for months, not on the day it was printed).
     * The frame never mirrors; only the headings follow the sign's direction.
     */
    private static function drawSchematic(?GdImage $im, array $schematic, array $rect, bool $rtl): float
    {
        [$rx, $ry, $rw, $rh] = $rect;
        $zoneGap = 80;
        $head = 76;
        $pad = 30;
        $gap = 24;
        $foot = ($schematic['amenities'] || $schematic['here']) ? 130 : 0;

        $zoneH = fn ($z, $ch) => $head + 2 * $pad + intdiv(count($z['cells']), max(1, $z['cols'])) * ($ch + $gap) - $gap;
        $height = function ($ch) use ($schematic, $zoneGap, $zoneH, $foot) {
            $total = 0;
            foreach ($schematic['sections'] as $sec) {
                $h = match ($sec['type']) {
                    'full' => $zoneH($sec['zone'], $ch),
                    'stack' => array_sum(array_map(fn ($z) => $zoneH($z, $ch) + $zoneGap, $sec['zones'])) - $zoneGap,
                    default => max(
                        array_sum(array_map(fn ($z) => $zoneH($z, $ch) + $zoneGap, $sec['a'])) - $zoneGap,
                        array_sum(array_map(fn ($z) => $zoneH($z, $ch) + $zoneGap, $sec['b'])) - $zoneGap,
                    ),
                };
                $total += $h + $zoneGap;
            }

            return $total - $zoneGap + $foot;
        };

        // The tallest stall that fits the panel, at most a square's worth.
        $ch = 180;
        while ($ch > 34 && $height($ch) > $rh) {
            $ch -= 5;
        }
        if ($im === null) {
            // Measuring only: the height the blocks take at the stall size that fits.
            return $height($ch);
        }
        $y = $ry + max(0, ($rh - $height($ch)) / 2);

        $ink = self::color($im, self::INK);
        $ink2 = self::color($im, [71, 85, 105]);

        $drawZone = function (array $z, float $x, float $top, float $w) use ($im, $ch, $head, $pad, $gap, $rtl, $ink, $ink2) {
            $base = self::hex($z['color']);
            $nameEnd = $rtl ? $x + $w : $x;
            $align = $rtl ? 'right' : 'left';
            $name = (string) $z['name'];
            $nameW = $name !== '' ? self::measure($name, 54, true) : 0;
            if ($name !== '') {
                self::text($im, $name, 54, $nameEnd, $top + 54, $ink, true, $align);
            }
            $metaW = max(0, $w - $nameW - 28);
            $meta = implode(' · ', array_filter([$z['range'], $z['sells']]));
            if (self::measure($meta, 42, true) > $metaW) {
                // What is sold is dropped before anything is cut: a half word helps nobody.
                $meta = (string) $z['range'];
            }
            if ($meta !== '') {
                $meta = self::ellipsize($meta, 42, true, $metaW);
                self::text($im, $meta, 42, $rtl ? $nameEnd - $nameW - 28 : $nameEnd + $nameW + 28, $top + 52, $ink2, true, $align);
            }

            $gy = $top + $head;
            $rows = intdiv(count($z['cells']), max(1, $z['cols']));
            $gh = 2 * $pad + $rows * ($ch + $gap) - $gap;
            self::fillPoly($im, self::roundedRect($x, $gy, $w, $gh, 34), self::color($im, self::mix($base, [255, 255, 255], 0.84)));
            self::strokePoly($im, self::roundedRect($x, $gy, $w, $gh, 34), self::color($im, self::mix($base, [255, 255, 255], 0.5)), 3);

            $cw = ($w - 2 * $pad - ($z['cols'] - 1) * $gap) / max(1, $z['cols']);
            $stroke = self::color($im, $base);
            $label = self::color($im, self::mix($base, self::INK, 0.4));
            $white = self::color($im, [255, 255, 255]);
            foreach ($z['cells'] as $i => $c) {
                if ($c === null) {
                    continue;
                }
                $cx = $x + $pad + ($i % $z['cols']) * ($cw + $gap);
                $cy = $gy + $pad + intdiv($i, $z['cols']) * ($ch + $gap);
                $cell = self::roundedRect($cx, $cy, $cw, $ch, 20);
                self::fillPoly($im, $cell, $white);
                self::strokePoly($im, $cell, $stroke, 5);
                $size = min($ch * 0.42, $cw * 0.82 / max(1, mb_strlen($c['label']) * 0.62));
                self::text($im, $c['label'], $size, $cx + $cw / 2, $cy + $ch / 2 + $size * 0.36, $label, true, 'center');
            }

            return $head + $gh;
        };

        foreach ($schematic['sections'] as $sec) {
            if ($sec['type'] === 'full') {
                $y += $drawZone($sec['zone'], $rx, $y, $rw) + $zoneGap;

                continue;
            }
            if ($sec['type'] === 'stack') {
                foreach ($sec['zones'] as $z) {
                    $y += $drawZone($z, $rx, $y, $rw) + $zoneGap;
                }

                continue;
            }
            $wa = ($rw - $zoneGap) * $sec['fa'] / ($sec['fa'] + $sec['fb']);
            $wb = $rw - $zoneGap - $wa;
            $ya = $y;
            foreach ($sec['a'] as $z) {
                $ya += $drawZone($z, $rx, $ya, $wa) + $zoneGap;
            }
            $yb = $y;
            foreach ($sec['b'] as $z) {
                $yb += $drawZone($z, $rx + $wa + $zoneGap, $yb, $wb) + $zoneGap;
            }
            $y = max($ya, $yb);
        }

        if ($foot) {
            $fy = $y + 30;
            imagesetthickness($im, 3);
            imageline($im, (int) $rx, (int) ($fy - 40), (int) ($rx + $rw), (int) ($fy - 40), self::color($im, [203, 213, 225]));
            imagesetthickness($im, 1);
            $hereEnd = $rtl ? $rx + $rw : $rx;
            if ($schematic['here']) {
                $red = self::color($im, [220, 38, 38]);
                $dot = $rtl ? $hereEnd - 22 : $hereEnd + 22;
                imagefilledellipse($im, (int) $dot, (int) ($fy + 10), 44, 44, $red);
                $label = __('messages.vm_you_are_here');
                $lx = $rtl ? $hereEnd - 66 : $hereEnd + 66;
                self::text($im, $label, 46, $lx, $fy + 26, $red, true, $rtl ? 'right' : 'left');
                if ($schematic['entrance']) {
                    $lw = self::measure($label, 46, true) + 24;
                    self::text($im, '· '.$schematic['entrance'], 42, $rtl ? $lx - $lw : $lx + $lw, $fy + 24, $ink2, false, $rtl ? 'right' : 'left');
                }
            }
            // The amenities at the other end of the line.
            $ax = $rtl ? $rx : $rx + $rw;
            foreach (array_reverse($schematic['amenities']) as $a) {
                $tw = self::measure($a['label'], 40, true) + 48;
                $bx = $rtl ? $ax : $ax - $tw;
                self::strokePoly($im, self::roundedRect($bx, $fy - 30, $tw, 76, 14), self::color($im, [148, 163, 184]), 3);
                self::text($im, $a['label'], 40, $bx + $tw / 2, $fy + 22, $ink2, true, 'center');
                $ax = $rtl ? $ax + $tw + 20 : $ax - $tw - 20;
            }
        }

        return $height($ch);
    }

    /**
     * Where a plan's drawing sits in map units, for the PNGs and the printable page alike:
     * the box around everything drawn, each area's outline, and a typical spot size (which sets
     * padding and type sizes). Takes the shape publicPayload() gives.
     *
     * @return array{bounds: array{0: float, 1: float, 2: float, 3: float}, areas: array<int, array{0: float, 1: float, 2: float, 3: float}>, unit: float}
     */
    public static function layout(array $plan): array
    {
        $sizes = [];
        foreach ($plan['spots'] ?? [] as $s) {
            $sizes[] = max(4, min((int) $s['w'], (int) $s['h']));
        }
        sort($sizes);
        $unit = $sizes ? (float) $sizes[intdiv(count($sizes), 2)] : 40.0;

        $areaPoints = [];
        $all = [];
        foreach ($plan['spots'] ?? [] as $s) {
            $poly = self::outline($s);
            array_push($all, ...$poly);
            if (! empty($s['area'])) {
                $areaPoints[$s['area']] = array_merge($areaPoints[$s['area']] ?? [], $poly);
            }
        }

        $areas = [];
        $pad = $unit * 0.35;
        foreach ($areaPoints as $id => $points) {
            [$x, $y, $w, $h] = self::box($points);
            $areas[$id] = [$x - $pad, $y - $pad, $w + 2 * $pad, $h + 2 * $pad];
            // Room for the area's name above its outline.
            $all[] = [$x - $pad, $y - $pad - $unit * 0.75];
            $all[] = [$x + $w + $pad, $y + $h + $pad];
        }

        foreach ($plan['landmarks'] ?? [] as $l) {
            array_push($all, ...self::outline($l));
            if (! empty($l['label']) && ($l['kind'] ?? '') !== 'text') {
                $all[] = [$l['x'] + $l['w'] / 2, $l['y'] + $l['h'] + $unit * 0.6];
            }
        }

        if (! empty($plan['here'])) {
            $all[] = [$plan['here']['x'] - $unit * 0.6, $plan['here']['y'] - $unit * 0.6];
            $all[] = [$plan['here']['x'] + $unit * 0.6, $plan['here']['y'] + $unit * 0.6];
        }

        if (! $all) {
            return ['bounds' => [0.0, 0.0, (float) max(1, $plan['width'] ?? 1000), (float) max(1, $plan['height'] ?? 700)], 'areas' => [], 'unit' => $unit];
        }

        [$x, $y, $w, $h] = self::box($all);
        $margin = $unit * 0.5;

        return [
            'bounds' => [$x - $margin, $y - $margin, max(1, $w + 2 * $margin), max(1, $h + 2 * $margin)],
            'areas' => $areas,
            'unit' => $unit,
        ];
    }

    /**
     * The corners of a spot or landmark in map units, turned about its centre; a spot's own
     * polygon when it has one. A radius rounds a rectangle's corners.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    public static function outline(array $item, float $radius = 0): array
    {
        if (! empty($item['shape']) && is_array($item['shape']) && count($item['shape']) >= 3) {
            return array_map(fn ($p) => [(float) $p[0], (float) $p[1]], $item['shape']);
        }

        $points = self::roundedRect((float) $item['x'], (float) $item['y'], (float) $item['w'], (float) $item['h'], $radius);
        $rotation = (float) ($item['rotation'] ?? 0);
        if (abs($rotation) < 0.01) {
            return $points;
        }

        $cx = $item['x'] + $item['w'] / 2;
        $cy = $item['y'] + $item['h'] / 2;
        $rad = deg2rad($rotation);
        [$cos, $sin] = [cos($rad), sin($rad)];

        return array_map(fn ($p) => [
            $cx + ($p[0] - $cx) * $cos - ($p[1] - $cy) * $sin,
            $cy + ($p[0] - $cx) * $sin + ($p[1] - $cy) * $cos,
        ], $points);
    }

    /** Whether GD can draw a string: anything with Arabic letters, which GD does not join, is left out. */
    public static function drawable(string $text): bool
    {
        return trim($text) !== '' && ! preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    }

    // ---------------------------------------------------------------------------------------
    // The share card

    private static function renderShare(Role $role, VendorMap $map): ?string
    {
        $k = 2;
        [$W, $H] = [1200, 630];
        $rtl = self::scheduleRtl($role);
        $im = self::canvas($W * $k, $H * $k, [244, 247, 255]);

        // The map panel on the side a reader ends on, the words on the side they start from.
        $panel = $rtl ? [48, 48, 540, 534] : [$W - 48 - 540, 48, 540, 534];
        $col = $rtl ? [$W - 64 - 500, 500] : [64, 500];
        $rect = self::roundedRect($panel[0] * $k, $panel[1] * $k, $panel[2] * $k, $panel[3] * $k, 28 * $k);
        self::fillPoly($im, $rect, self::color($im, [255, 255, 255]));
        self::strokePoly($im, $rect, self::color($im, [221, 228, 240]), 2 * $k);

        self::drawPlan($im, self::plan($map), [($panel[0] + 28) * $k, ($panel[1] + 28) * $k, ($panel[2] - 56) * $k, ($panel[3] - 56) * $k], [
            'k' => $k,
            'spotFill' => fn ($s) => self::mix($s['color'], [255, 255, 255], 0.25),
            'spotStroke' => fn ($s) => self::mix($s['color'], [15, 23, 42], 0.2),
            'labelInk' => fn ($s) => self::luminance(self::mix($s['color'], [255, 255, 255], 0.25)) > 0.45 ? self::INK : [255, 255, 255],
            'labelMin' => 10 * $k,
            'labelMax' => 22 * $k,
            'areaTint' => 0.9,
            'areaNames' => false,
            'landmarkLabels' => false,
            'highlight' => null,
            'here' => null,
        ]);

        $start = ($rtl ? $col[0] + $col[1] : $col[0]) * $k;
        $align = $rtl ? 'right' : 'left';
        $maxW = $col[1] * $k;

        $headline = VendorMapKinds::say($map->kind(), 'find_title');
        $headSize = 36 * $k;
        $headline = self::ellipsize($headline, $headSize, true, $maxW);
        [$nameSize, $nameLines] = self::wrapToFit((string) $role->name, true, $maxW, 3, 76 * $k, 44 * $k);

        $lineGap = (int) round($nameSize * 1.12);
        $block = $headSize * 1.2 + 22 * $k + $lineGap * count($nameLines);
        $y = (int) (($H - 40) * $k / 2 - $block / 2);

        $y += (int) round($headSize);
        self::text($im, $headline, $headSize, $start, $y, self::color($im, self::BRAND_INK), true, $align);
        $y += (int) round($headSize * 0.2 + 22 * $k);
        $ink = self::color($im, self::INK);
        foreach ($nameLines as $line) {
            $y += $lineGap;
            self::text($im, $line, $nameSize, $start, $y - (int) round($nameSize * 0.12), $ink, true, $align);
        }

        $short = preg_replace('#^https?://#', '', VendorMapPresenter::publicUrl($role, true));
        $short = self::ellipsize($short, 24 * $k, false, $maxW);
        self::text($im, $short, 24 * $k, $start, ($H - 64) * $k, self::color($im, self::QUIET), false, $align);

        return self::png(self::downscale($im, $W, $H));
    }

    // ---------------------------------------------------------------------------------------
    // The map

    /** The map's drawing in the shape publicPayload() gives, read from its own rows. */
    private static function plan(VendorMap $map): array
    {
        $map->loadMissing(['areas', 'landmarks']);
        $areas = [];
        foreach ($map->areas as $a) {
            $areas[] = ['id' => $a->id, 'name' => (string) $a->name, 'color' => $a->color];
        }

        $spots = [];
        foreach ($map->spots()->orderBy('id')->get(['id', 'label', 'area_id', 'x', 'y', 'w', 'h', 'rotation', 'shape']) as $s) {
            $spots[] = [
                'id' => (int) $s->id, 'label' => (string) $s->label, 'area' => $s->area_id,
                'x' => (int) $s->x, 'y' => (int) $s->y, 'w' => max(1, (int) $s->w), 'h' => max(1, (int) $s->h),
                'rotation' => (int) $s->rotation, 'shape' => $s->shape,
            ];
        }

        return [
            'width' => (int) $map->width,
            'height' => (int) $map->height,
            'areas' => $areas,
            'spots' => $spots,
            'landmarks' => $map->landmarks->map(fn ($l) => [
                'kind' => $l->kind, 'label' => $l->label, 'x' => (int) $l->x, 'y' => (int) $l->y,
                'w' => max(1, (int) $l->w), 'h' => max(1, (int) $l->h), 'rotation' => (int) $l->rotation,
            ])->all(),
            'here' => $map->here_x !== null ? ['x' => (int) $map->here_x, 'y' => (int) $map->here_y] : null,
        ];
    }

    /**
     * Draw a plan into a box of the image. The style says the colours (each spot gets its area's
     * colour as `color`), the label sizes, whether area and landmark names are written, which
     * spot is marked, and the "You are here" words (null for no marker).
     */
    private static function drawPlan(GdImage $im, array $plan, array $rect, array $style): void
    {
        $layout = self::layout($plan);
        [$bx, $by, $bw, $bh] = $layout['bounds'];
        $s = min($rect[2] / $bw, $rect[3] / $bh);
        $ox = $rect[0] + ($rect[2] - $bw * $s) / 2 - $bx * $s;
        $oy = $rect[1] + ($rect[3] - $bh * $s) / 2 - $by * $s;
        $at = fn (array $p) => [$ox + $p[0] * $s, $oy + $p[1] * $s];
        $k = $style['k'];
        $unitPx = $layout['unit'] * $s;

        $areas = [];
        foreach ($plan['areas'] ?? [] as $a) {
            $areas[$a['id']] = $a + ['rgb' => self::hex($a['color'] ?? null)];
        }

        // Areas: a light outline round their spots, the name above it.
        foreach ($layout['areas'] as $id => [$x, $y, $w, $h]) {
            $rgb = $areas[$id]['rgb'] ?? self::BRAND;
            $poly = self::roundedRect($ox + $x * $s, $oy + $y * $s, $w * $s, $h * $s, min(18 * $k, $unitPx * 0.3));
            self::fillPoly($im, $poly, self::color($im, self::mix($rgb, [255, 255, 255], $style['areaTint'])));
            self::strokePoly($im, $poly, self::color($im, self::mix($rgb, [255, 255, 255], 0.45)), max(1, (int) round(1.5 * $k)));

            if ($style['areaNames'] && ! empty($areas[$id]['name'])) {
                $size = max($style['areaNameMin'], min($style['areaNameMax'], $unitPx * 0.42));
                $name = self::ellipsize($areas[$id]['name'], $size, true, max($w * $s, $unitPx * 3));
                $rtl = self::containsHebrew($name);
                self::text($im, $name, $size, $rtl ? $ox + ($x + $w) * $s : $ox + $x * $s, $oy + $y * $s - $size * 0.35, self::color($im, self::mix($rgb, [15, 23, 42], 0.55)), true, $rtl ? 'right' : 'left');
            }
        }

        foreach ($plan['landmarks'] ?? [] as $l) {
            self::drawLandmark($im, $l, array_map($at, self::outline($l, min($l['w'], $l['h']) * 0.15)), $style, $unitPx);
        }

        // Spots, then their numbers on top.
        $highlight = null;
        foreach ($plan['spots'] ?? [] as $spot) {
            $spot['color'] = isset($spot['area'], $areas[$spot['area']]) ? $areas[$spot['area']]['rgb'] : self::BRAND;
            $poly = array_map($at, self::outline($spot, min($spot['w'], $spot['h']) * 0.16));
            self::fillPoly($im, $poly, self::color($im, ($style['spotFill'])($spot)));
            self::strokePoly($im, $poly, self::color($im, ($style['spotStroke'])($spot)), max(1, (int) round(1.2 * $k)));

            [$px, $py, $pw, $ph] = self::box($poly);
            $cx = $px + $pw / 2;
            $cy = $py + $ph / 2;
            if (abs(fmod((float) $spot['rotation'], 90)) > 5 && empty($spot['shape'])) {
                [$pw, $ph] = [$pw * 0.72, $ph * 0.72];
            }
            if ($spot['id'] === $style['highlight']) {
                $highlight = [$spot, $cx, $cy, $pw, $ph, $py];

                continue;
            }

            $size = min($style['labelMax'], $ph * 0.5);
            while ($size >= $style['labelMin'] && self::measure($spot['label'], $size, true) > $pw * 0.82) {
                $size *= 0.9;
            }
            if ($size >= $style['labelMin']) {
                self::text($im, $spot['label'], $size, $cx, $cy + $size * 0.36, self::color($im, ($style['labelInk'])($spot)), true, 'center');
            }
        }

        if ($highlight) {
            self::drawHighlight($im, $highlight, $k, $style);
        }

        if (! empty($plan['here']) && $style['here'] !== null) {
            [$hx, $hy] = $at([$plan['here']['x'], $plan['here']['y']]);
            self::drawHere($im, $hx, $hy, $style['here'], $k, $rect);
        }
    }

    private static function drawHighlight(GdImage $im, array $h, int $k, array $style): void
    {
        [$spot, $cx, $cy, $pw, $ph, $top] = $h;
        $white = self::color($im, [255, 255, 255]);

        $size = min(44 * $k, $ph * 0.62);
        while ($size >= 20 * $k && self::measure($spot['label'], $size, true) > $pw * 0.86) {
            $size *= 0.92;
        }

        if ($size >= 20 * $k) {
            self::text($im, $spot['label'], $size, $cx, $cy + $size * 0.36, $white, true, 'center');

            return;
        }

        // Too small to hold its number: a bubble above it carries the number instead.
        $size = 26 * $k;
        $tw = self::measure($spot['label'], $size, true);
        $bw = $tw + 28 * $k;
        $bh = $size + 22 * $k;
        $above = $top - 14 * $k - $bh > 4 * $k;
        $by = $above ? $top - 12 * $k - $bh : $cy + $ph / 2 + 12 * $k;
        $bx = max(4 * $k, min(imagesx($im) - $bw - 4 * $k, $cx - $bw / 2));
        $brand = self::color($im, self::BRAND);
        self::fillPoly($im, self::roundedRect($bx, $by, $bw, $bh, 10 * $k), $brand);
        $tip = $above
            ? [[$cx - 8 * $k, $by + $bh - 1], [$cx + 8 * $k, $by + $bh - 1], [$cx, $by + $bh + 9 * $k]]
            : [[$cx - 8 * $k, $by + 1], [$cx + 8 * $k, $by + 1], [$cx, $by - 9 * $k]];
        self::fillPoly($im, $tip, $brand);
        self::text($im, $spot['label'], $size, $bx + $bw / 2, $by + $bh / 2 + $size * 0.36, $white, true, 'center');
    }

    private static function drawHere(GdImage $im, float $x, float $y, string $label, int $k, array $rect): void
    {
        $r = 16 * $k;
        imagefilledellipse($im, (int) $x, (int) $y, (int) (2 * $r + 8 * $k), (int) (2 * $r + 8 * $k), self::color($im, [255, 255, 255]));
        imagefilledellipse($im, (int) $x, (int) $y, (int) (2 * $r), (int) (2 * $r), self::color($im, self::HERE));
        imagefilledellipse($im, (int) $x, (int) $y, (int) ($r * 0.7), (int) ($r * 0.7), self::color($im, [255, 255, 255]));

        if (! self::drawable($label)) {
            return;
        }

        $size = 30 * $k;
        $w = self::measure($label, $size, true);
        $pad = 12 * $k;
        // Beside the dot, on whichever side has room.
        $right = $x + $r + $pad + $w + 2 * $pad <= $rect[0] + $rect[2] + 40 * $k;
        $bx = $right ? $x + $r + 8 * $k : $x - $r - 8 * $k - $w - 2 * $pad;
        $bh = $size + 2 * $pad;
        $box = self::roundedRect($bx, $y - $bh / 2, $w + 2 * $pad, $bh, 10 * $k);
        self::fillPoly($im, $box, self::color($im, [255, 255, 255]));
        self::strokePoly($im, $box, self::color($im, self::HERE), max(1, (int) round(1.5 * $k)));
        self::text($im, $label, $size, $bx + $pad, $y + $size * 0.36, self::color($im, self::HERE), true, 'left');
    }

    private static function drawLandmark(GdImage $im, array $l, array $poly, array $style, float $unitPx): void
    {
        $k = $style['k'];
        [$x, $y, $w, $h] = self::box($poly);
        $cx = $x + $w / 2;
        $cy = $y + $h / 2;
        $slate = self::color($im, [71, 85, 105]);
        $kind = $l['kind'] ?? 'text';
        $label = trim((string) ($l['label'] ?? ''));

        if ($kind === 'text') {
            if ($label !== '') {
                $size = min(max($unitPx * 0.4, 10 * $k), $h * 0.7, 48 * $k);
                $label = self::ellipsize($label, $size, true, max($w * 1.6, $unitPx * 3.5));
                if ($size >= 9 * $k) {
                    self::text($im, $label, $size, $cx, $cy + $size * 0.36, $slate, true, 'center');
                }
            }

            return;
        }

        self::fillPoly($im, $poly, self::color($im, [238, 242, 246]));
        self::strokePoly($im, $poly, self::color($im, [148, 163, 184]), max(1, (int) round(1.2 * $k)));

        $icon = min($w, $h) * 0.62;
        if ($icon >= 8 * $k) {
            match ($kind) {
                'parking' => self::text($im, 'P', $icon, $cx, $cy + $icon * 0.36, $slate, true, 'center'),
                'restroom' => self::text($im, 'WC', $icon * 0.6, $cx, $cy + $icon * 0.6 * 0.36, $slate, true, 'center'),
                'entrance' => self::fillPoly($im, self::arrow($cx, $cy, $icon), $slate),
                'stage' => self::fillPoly($im, self::star($cx, $cy, $icon / 2), $slate),
                default => null,
            };
        }

        if ($style['landmarkLabels'] && $label !== '') {
            $size = min(max($unitPx * 0.32, 10 * $k), 40 * $k);
            $label = self::ellipsize($label, $size, false, max($w * 1.6, $unitPx * 3));
            if ($size >= 9 * $k) {
                self::text($im, $label, $size, $cx, $y + $h + $size * 1.15, $slate, false, 'center');
            }
        }
    }

    /** A down-pointing arrow, the way in. */
    private static function arrow(float $cx, float $cy, float $size): array
    {
        $hw = $size * 0.16;
        $head = $size * 0.42;

        return [
            [$cx - $hw, $cy - $size / 2], [$cx + $hw, $cy - $size / 2], [$cx + $hw, $cy + $size / 2 - $head],
            [$cx + $size * 0.38, $cy + $size / 2 - $head], [$cx, $cy + $size / 2],
            [$cx - $size * 0.38, $cy + $size / 2 - $head], [$cx - $hw, $cy + $size / 2 - $head],
        ];
    }

    private static function star(float $cx, float $cy, float $r): array
    {
        $points = [];
        for ($i = 0; $i < 10; $i++) {
            $radius = $i % 2 ? $r * 0.45 : $r;
            $a = -M_PI / 2 + $i * M_PI / 5;
            $points[] = [$cx + cos($a) * $radius, $cy + sin($a) * $radius];
        }

        return $points;
    }

    // ---------------------------------------------------------------------------------------
    // Text

    private static function fontPath(string $script, bool $bold): string
    {
        $dir = resource_path('fonts');

        return $script === 'hebrew'
            ? $dir.($bold ? '/NotoSansHebrew-Bold.ttf' : '/NotoSansHebrew-Regular.ttf')
            : $dir.($bold ? '/NotoSans-Bold.ttf' : '/NotoSans-Regular.ttf');
    }

    private static function containsHebrew(string $text): bool
    {
        return preg_match('/[\x{0590}-\x{05FF}\x{FB1D}-\x{FB4F}]/u', $text) === 1;
    }

    private static function clean(string $text): string
    {
        // Direction marks and other format characters, and emoji, have no glyph in these fonts.
        $text = preg_replace('/[\p{Cc}\p{Cf}\x{1F000}-\x{1FFFF}\x{2600}-\x{27BF}\x{FE00}-\x{FE0F}]/u', '', $text) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    /**
     * A line of text in the order GD should draw it, left to right, as pieces of one font each.
     * A small form of the Unicode bidi rules: the first strong letter sets the line's direction,
     * spaces and punctuation take the direction of the letters around them (or the line's), a
     * Hebrew run is reversed with its brackets mirrored, and numbers and Latin stay as written.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private static function visual(string $text): array
    {
        $chars = mb_str_split($text);
        if (! $chars) {
            return [];
        }

        $cls = [];
        foreach ($chars as $c) {
            $cls[] = self::containsHebrew($c) ? 'R' : (preg_match('/[\p{L}\p{N}]/u', $c) ? 'L' : 'N');
        }

        $base = 'L';
        foreach ($cls as $c) {
            if ($c !== 'N') {
                $base = $c;
                break;
            }
        }

        $n = count($cls);
        for ($i = 0; $i < $n; $i++) {
            if ($cls[$i] !== 'N') {
                continue;
            }
            $j = $i;
            while ($j < $n && $cls[$j] === 'N') {
                $j++;
            }
            $prev = $i > 0 ? $cls[$i - 1] : $base;
            $next = $j < $n ? $cls[$j] : $base;
            for ($t = $i; $t < $j; $t++) {
                $cls[$t] = $prev === $next ? $prev : $base;
            }
            $i = $j - 1;
        }

        $runs = [];
        foreach ($chars as $i => $c) {
            $last = array_key_last($runs);
            if ($last !== null && $runs[$last][0] === $cls[$i]) {
                $runs[$last][1][] = $c;
            } else {
                $runs[] = [$cls[$i], [$c]];
            }
        }

        $mirror = ['(' => ')', ')' => '(', '[' => ']', ']' => '[', '{' => '}', '}' => '{', '<' => '>', '>' => '<', '«' => '»', '»' => '«'];
        foreach ($runs as $i => $run) {
            if ($run[0] === 'R') {
                $runs[$i][1] = array_map(fn ($c) => $mirror[$c] ?? $c, array_reverse($run[1]));
            }
        }
        if ($base === 'R') {
            $runs = array_reverse($runs);
        }

        $pieces = [];
        foreach ($runs as $run) {
            foreach ($run[1] as $c) {
                $script = self::containsHebrew($c) ? 'hebrew' : 'latin';
                $last = array_key_last($pieces);
                if ($last !== null && $pieces[$last][1] === $script) {
                    $pieces[$last][0] .= $c;
                } else {
                    $pieces[] = [$c, $script];
                }
            }
        }

        return $pieces;
    }

    /** Width in pixels of a line drawn at $px pixels. */
    private static function measure(string $text, float $px, bool $bold): float
    {
        $text = self::clean($text);
        $width = 0.0;
        foreach (self::visual($text) as [$piece, $script]) {
            $box = imagettfbbox($px * 0.75, 0, self::fontPath($script, $bold), $piece);
            $width += $box ? $box[2] - $box[0] : 0;
        }

        return $width;
    }

    /**
     * Draw one line with its baseline at $baseline, aligned at $x. False when it holds nothing
     * GD can draw (Arabic).
     */
    private static function text(GdImage $im, string $text, float $px, float $x, float $baseline, int $color, bool $bold = false, string $align = 'left'): bool
    {
        $text = self::clean($text);
        if ($text === '' || ! self::drawable($text)) {
            return false;
        }

        $width = self::measure($text, $px, $bold);
        $cursor = match ($align) {
            'center' => $x - $width / 2,
            'right' => $x - $width,
            default => $x,
        };

        foreach (self::visual($text) as [$piece, $script]) {
            $font = self::fontPath($script, $bold);
            $box = imagettfbbox($px * 0.75, 0, $font, $piece);
            imagettftext($im, $px * 0.75, 0, (int) round($cursor), (int) round($baseline), $color, $font, $piece);
            $cursor += $box ? $box[2] - $box[0] : 0;
        }

        return true;
    }

    /** The text, cut at the end of what it says (not of what is drawn) with an ellipsis, to fit. */
    private static function ellipsize(string $text, float $px, bool $bold, float $maxWidth): string
    {
        $text = self::clean($text);
        if ($text === '' || self::measure($text, $px, $bold) <= $maxWidth) {
            return $text;
        }

        $chars = mb_str_split($text);
        while (count($chars) > 1) {
            array_pop($chars);
            $try = rtrim(implode('', $chars)).'…';
            if (self::measure($try, $px, $bold) <= $maxWidth) {
                return $try;
            }
        }

        return '…';
    }

    /**
     * The largest size from $max down to $min at which the text wraps into $maxLines lines, and
     * those lines; at $min the last line is cut with an ellipsis.
     *
     * @return array{0: float, 1: string[]}
     */
    private static function wrapToFit(string $text, bool $bold, float $maxWidth, int $maxLines, float $max, float $min): array
    {
        $text = self::clean($text);
        for ($size = $max; $size > $min; $size *= 0.92) {
            $lines = self::wrap($text, $size, $bold, $maxWidth);
            if (count($lines) <= $maxLines) {
                return [$size, $lines];
            }
        }

        $lines = self::wrap($text, $min, $bold, $maxWidth);
        if (count($lines) > $maxLines) {
            $rest = implode(' ', array_slice($lines, $maxLines - 1));
            $lines = array_slice($lines, 0, $maxLines - 1);
            $lines[] = self::ellipsize($rest, $min, $bold, $maxWidth);
        }

        return [$min, $lines];
    }

    /** @return string[] */
    private static function wrap(string $text, float $px, bool $bold, float $maxWidth): array
    {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) as $word) {
            $try = $line === '' ? $word : $line.' '.$word;
            if ($line !== '' && self::measure($try, $px, $bold) > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $try;
            }
        }
        if ($line !== '') {
            $lines[] = self::ellipsize($line, $px, $bold, $maxWidth);
        }

        return $lines;
    }

    // ---------------------------------------------------------------------------------------
    // GD plumbing

    private static function available(): bool
    {
        return function_exists('imagecreatetruecolor') && function_exists('imagettftext');
    }

    private static function canvas(int $w, int $h, array $bg): GdImage
    {
        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, true);
        imagefilledrectangle($im, 0, 0, $w, $h, self::color($im, $bg));

        return $im;
    }

    /** Drawn at twice the size and scaled down, which is how GD's polygons get smooth edges. */
    private static function downscale(GdImage $im, int $w, int $h): GdImage
    {
        $out = imagecreatetruecolor($w, $h);
        imagecopyresampled($out, $im, 0, 0, 0, 0, $w, $h, imagesx($im), imagesy($im));
        imagedestroy($im);

        return $out;
    }

    private static function png(GdImage $im, int $level = 9): string
    {
        ob_start();
        imagepng($im, null, $level);
        $bytes = (string) ob_get_clean();
        imagedestroy($im);

        return $bytes;
    }

    private static function color(GdImage $im, array $rgb): int
    {
        return imagecolorallocate($im, (int) $rgb[0], (int) $rgb[1], (int) $rgb[2]);
    }

    private static function hex(?string $hex): array
    {
        if (! is_string($hex) || ! preg_match('/^#?([0-9a-f]{6})$/i', $hex, $m)) {
            return self::BRAND;
        }

        return [hexdec(substr($m[1], 0, 2)), hexdec(substr($m[1], 2, 2)), hexdec(substr($m[1], 4, 2))];
    }

    /** $a moved $t of the way toward $b. */
    private static function mix(array $a, array $b, float $t): array
    {
        return [
            (int) round($a[0] + ($b[0] - $a[0]) * $t),
            (int) round($a[1] + ($b[1] - $a[1]) * $t),
            (int) round($a[2] + ($b[2] - $a[2]) * $t),
        ];
    }

    private static function luminance(array $rgb): float
    {
        $c = array_map(function ($v) {
            $v /= 255;

            return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
    }

    /** @return array<int, array{0: float, 1: float}> */
    private static function roundedRect(float $x, float $y, float $w, float $h, float $r): array
    {
        $r = max(0, min($r, $w / 2, $h / 2));
        if ($r < 0.5) {
            return [[$x, $y], [$x + $w, $y], [$x + $w, $y + $h], [$x, $y + $h]];
        }

        $points = [];
        $corners = [[$x + $w - $r, $y + $r, -90], [$x + $w - $r, $y + $h - $r, 0], [$x + $r, $y + $h - $r, 90], [$x + $r, $y + $r, 180]];
        foreach ($corners as [$cx, $cy, $from]) {
            for ($i = 0; $i <= 4; $i++) {
                $a = deg2rad($from + $i * 22.5);
                $points[] = [$cx + cos($a) * $r, $cy + sin($a) * $r];
            }
        }

        return $points;
    }

    /** @return array{0: float, 1: float, 2: float, 3: float} x, y, width, height */
    private static function box(array $points): array
    {
        $xs = array_column($points, 0);
        $ys = array_column($points, 1);

        return [min($xs), min($ys), max($xs) - min($xs), max($ys) - min($ys)];
    }

    private static function fillPoly(GdImage $im, array $points, int $color): void
    {
        if (count($points) < 3) {
            return;
        }
        imagefilledpolygon($im, self::flat($points), $color);
    }

    private static function strokePoly(GdImage $im, array $points, int $color, int $thickness): void
    {
        if (count($points) < 3) {
            return;
        }
        imagesetthickness($im, $thickness);
        imagepolygon($im, self::flat($points), $color);
        imagesetthickness($im, 1);
    }

    private static function flat(array $points): array
    {
        $out = [];
        foreach ($points as $p) {
            $out[] = (int) round($p[0]);
            $out[] = (int) round($p[1]);
        }

        return $out;
    }

    private static function scheduleRtl(Role $role): bool
    {
        return in_array($role->language_code, ['ar', 'he'], true);
    }

    /** Run $work in the schedule's language, and put the viewer's back whatever happens. */
    private static function inScheduleLocale(Role $role, callable $work): mixed
    {
        $original = app()->getLocale();

        try {
            if (is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }

            return $work();
        } finally {
            app()->setLocale($original);
        }
    }
}
