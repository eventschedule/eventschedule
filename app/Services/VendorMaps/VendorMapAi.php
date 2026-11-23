<?php

namespace App\Services\VendorMaps;

use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapDraft;
use App\Services\UsageTrackingService;
use App\Utils\GeminiUtils;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * A vendor map drafted from photos.
 *
 * Two requests, never one: the photos are uploaded (and kept privately, VendorMapImages), then a
 * second request asks the AI. A parse can take a minute and Cloudflare cuts a request at 100
 * seconds, so the parse runs inline with its own time limit rather than on the queue, which the
 * scheduler drains up to a minute late. The answer is saved on the draft, so a phone that slept
 * through it comes back to "Your map is ready". Nothing reaches the map until the organizer
 * presses Save in the designer.
 */
class VendorMapAi
{
    /**
     * A big market is photographed area by area (an indoor hall, each outdoor section), each area
     * read by the AI on its own and the areas then laid side by side as blocks the organizer drags
     * into place. The photos arrive a few per request.
     */
    public const MAX_AREA_PHOTOS = 20;

    /** Photos in all, across the areas. */
    public const MAX_PHOTOS = 60;

    public const MAX_PHOTO_AREAS = 10;

    /** What one area's photos may weigh, so its request to the AI stays under the inline limit. */
    public const MAX_DRAFT_BYTES = 14 * 1024 * 1024;

    public const MAX_BYTES = 8 * 1024 * 1024;

    public static function available(VendorMap $map): bool
    {
        return GeminiUtils::canReadSeveralImages() && $map->role && $map->role->isPro();
    }

    /**
     * @param  UploadedFile[]  $files
     *
     * @throws ValidationException
     */
    public static function upload(VendorMap $map, ?User $user, array $files, ?VendorMapDraft $append = null, ?int $area = null, ?string $areaName = null): VendorMapDraft
    {
        // A later batch of the same photos joins the draft the first one made.
        $images = [];
        $kept = $append ? array_values((array) $append->images) : [];
        $pdfs = count(array_filter($kept, fn ($p) => str_ends_with((string) $p, '.pdf')));

        // Photos sent for an area ("the covered hall") are read with that area's other photos only.
        $groups = $append ? (array) (($append->result ?? [])['groups'] ?? []) : [];
        $areaKey = null;
        if ($area !== null) {
            $areaKey = (string) max(0, min(self::MAX_PHOTO_AREAS - 1, $area));
            if (! isset($groups[$areaKey]) && count($groups) >= self::MAX_PHOTO_AREAS) {
                throw ValidationException::withMessages(['photos' => __('messages.vm_photo_count', ['count' => self::MAX_AREA_PHOTOS])]);
            }
            $groups[$areaKey] ??= ['name' => '', 'images' => []];
            if ($areaName !== null && trim($areaName) !== '') {
                $groups[$areaKey]['name'] = mb_substr(trim($areaName), 0, 80);
            }
        }
        $sameRequest = $areaKey !== null ? (array) $groups[$areaKey]['images'] : array_keys($kept);
        $bytesSoFar = 0;
        foreach ($sameRequest as $index) {
            $bytesSoFar += VendorMapImages::size($kept[$index] ?? null);
        }

        // A refusal part way through a batch leaves none of its files behind.
        $refuse = function (string $message) use (&$images) {
            VendorMapImages::deletePaths($images);
            throw ValidationException::withMessages(['photos' => $message]);
        };

        foreach (array_slice($files, 0, self::MAX_PHOTOS + 1) as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid() || $file->getSize() > self::MAX_BYTES) {
                $refuse(__('messages.vm_photo_invalid'));
            }

            $bytes = (string) file_get_contents($file->getRealPath());
            $extension = self::extensionOf($bytes);

            if ($extension === null) {
                $refuse(__('messages.vm_photo_invalid'));
            }

            if ($extension === 'pdf' && ++$pdfs > 1) {
                $refuse(__('messages.vm_one_pdf'));
            }

            $bytesSoFar += strlen($bytes);
            if ($bytesSoFar > self::MAX_DRAFT_BYTES) {
                $refuse(__('messages.vm_photos_too_big'));
            }

            $images[] = VendorMapImages::storePrivate($bytes, $extension);
        }

        // Photos, not the one PDF a draft may hold, count against the limits: this area's, then all.
        $isPdf = fn ($path) => str_ends_with((string) $path, '.pdf');
        $newPhotos = count(array_filter($images, fn ($p) => ! $isPdf($p)));
        $areaPhotos = $areaKey !== null
            ? count(array_filter((array) $groups[$areaKey]['images'], fn ($i) => ! $isPdf($kept[$i] ?? '')))
            : count(array_filter($kept, fn ($p) => ! $isPdf($p)));
        $allPhotos = count(array_filter($kept, fn ($p) => ! $isPdf($p))) + $newPhotos;
        if ($images === [] || $areaPhotos + $newPhotos > self::MAX_AREA_PHOTOS) {
            $refuse(__('messages.vm_photo_count', ['count' => self::MAX_AREA_PHOTOS]));
        }
        if ($allPhotos > self::MAX_PHOTOS) {
            $refuse(__('messages.vm_photo_count', ['count' => self::MAX_PHOTOS]));
        }

        if ($areaKey !== null) {
            foreach (array_keys($images) as $k) {
                $groups[$areaKey]['images'][] = count($kept) + $k;
            }
        }
        $all = array_merge($kept, $images);

        if ($append) {
            $append->forceFill(['images' => $all] + ($areaKey !== null ? ['result' => ['groups' => $groups]] : []))->save();

            return $append;
        }

        return VendorMapDraft::create([
            'result' => $areaKey !== null ? ['groups' => $groups] : null,
            'vendor_map_id' => $map->id,
            'user_id' => $user?->id,
            'images' => $all,
            'status' => 'uploaded',
        ]);
    }

    /**
     * Ask the AI. Returns the normalized draft, or null when the AI could not answer (the draft
     * is marked failed and keeps its photos for Try again).
     */
    public static function parse(VendorMapDraft $draft, ?string $notes = null): ?array
    {
        // One read of a draft at a time: a Try again pressed while the first request still runs
        // waits for it and then answers from what it found, instead of paying for a second read.
        try {
            return Cache::lock('vendor-map-draft:'.$draft->id, 200)->block(95, function () use ($draft, $notes) {
                $draft->refresh();

                return self::parseNow($draft, $notes);
            });
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['photos' => __('messages.vm_ai_failed')]);
        }
    }

    private static function parseNow(VendorMapDraft $draft, ?string $notes): ?array
    {
        $map = $draft->map;
        $role = $map->role;

        if (! $role->canMakeAiParseRequest()) {
            throw ValidationException::withMessages(['photos' => __('messages.vm_ai_limit')]);
        }

        // Already read (a Try again after the answer arrived): no second request, no second charge.
        if ($draft->status === 'ready' && ! empty(($draft->result ?? [])['spots'])) {
            return $draft->result;
        }

        if (! empty(($draft->result ?? [])['groups'])) {
            return self::parseNextArea($draft, $notes);
        }

        $images = [];
        foreach ((array) $draft->images as $path) {
            $bytes = VendorMapImages::readPrivate($path);
            if ($bytes !== null) {
                $images[] = ['data' => $bytes, 'mime' => VendorMapImages::mimeFor($path)];
            }
        }

        if ($images === []) {
            $draft->forceFill(['status' => 'failed'])->save();

            return null;
        }

        $draft->forceFill(['status' => 'reading'])->save();

        $kind = $map->kind();
        $kindName = __('messages.vm_'.$kind.'_kind_name', [], 'en');
        $categories = VendorMapKinds::categoriesFor($kind);

        $prompt = str_replace(
            [':count', ':kind', ':categories'],
            [(string) count($images), mb_strtolower($kindName), implode(', ', $categories)],
            config('ai_prompts.vendor_map.base')
        );
        if ($notes = trim((string) $notes)) {
            $prompt .= str_replace(':notes', mb_substr($notes, 0, 500), config('ai_prompts.vendor_map.notes_line'));
        }

        $answer = GeminiUtils::structured($prompt, [
            'system' => str_replace(':kind', mb_strtolower($kindName), config('ai_prompts.vendor_map.system')),
            'schema' => self::schema($categories),
            'images' => $images,
            'thinking_level' => 'low',
            'timeout' => 90,
            'stage' => 'vendor_map',
        ]);

        // One use per request to the AI (an area's photos go in one request), as an AI import
        // counts one: counted per photo, a market shot area by area ran out of the day's allowance
        // before its last area.
        UsageTrackingService::track(UsageTrackingService::GEMINI_PARSE_VENDOR_MAP, $role->id);

        if (! is_array($answer) || empty($answer['spots'])) {
            $draft->forceFill(['status' => 'failed'])->save();

            return null;
        }

        $result = self::normalize($answer, $kind);
        $draft->forceFill(['status' => 'ready', 'result' => $result])->save();

        return $result;
    }

    /**
     * One area of a market photographed area by area: the next area not read yet, with its own
     * photos only (one request each, so a big market never runs into the 100-second limit, and a
     * closed tab carries on where it stopped). Returns ['pending' => true, ...] while areas remain,
     * then the areas assembled side by side.
     */
    private static function parseNextArea(VendorMapDraft $draft, ?string $notes): ?array
    {
        $map = $draft->map;
        $role = $map->role;
        $result = (array) $draft->result;
        $groups = (array) $result['groups'];
        ksort($groups, SORT_NUMERIC);
        $parts = (array) ($result['parts'] ?? []);

        $next = null;
        foreach ($groups as $key => $group) {
            if (! isset($parts[$key]) && ! empty($group['images'])) {
                $next = (string) $key;
                break;
            }
        }

        if ($next !== null) {
            $paths = (array) $draft->images;
            $images = [];
            foreach ((array) $groups[$next]['images'] as $index) {
                $bytes = VendorMapImages::readPrivate($paths[$index] ?? null);
                if ($bytes !== null) {
                    $images[] = ['data' => $bytes, 'mime' => VendorMapImages::mimeFor($paths[$index])];
                }
            }

            $answer = $images === [] ? null : self::ask($role, $map->kind(), $images, $notes, self::areaName($groups, $next));
            if (! is_array($answer) || empty($answer['spots'])) {
                $draft->forceFill(['status' => 'failed'])->save();

                return null;
            }

            $parts[$next] = self::normalize($answer, $map->kind());
            $result['parts'] = $parts;
            $draft->forceFill(['status' => 'reading', 'result' => $result])->save();
        }

        $read = array_filter(array_keys($groups), fn ($k) => isset($parts[$k]));
        $left = array_values(array_filter(array_keys($groups), fn ($k) => ! isset($parts[$k]) && ! empty($groups[$k]['images'])));
        if ($left !== []) {
            return [
                'pending' => true,
                'done' => count($read),
                'total' => count($read) + count($left),
                'next' => self::areaName($groups, (string) $left[0]),
            ];
        }

        $assembled = self::assemble($groups, $parts);
        $draft->forceFill(['status' => 'ready', 'result' => $assembled + ['groups' => $groups]])->save();

        return $assembled;
    }

    private static function areaName(array $groups, string $key): string
    {
        $name = trim((string) ($groups[$key]['name'] ?? ''));

        return $name !== '' ? $name : __('messages.vm_area_n', ['number' => (int) $key + 1]);
    }

    /** One request to the AI. */
    private static function ask($role, string $kind, array $images, ?string $notes, ?string $area = null): ?array
    {
        $kindName = __('messages.vm_'.$kind.'_kind_name', [], 'en');
        $categories = VendorMapKinds::categoriesFor($kind);

        $prompt = str_replace(
            [':count', ':kind', ':categories'],
            [(string) count($images), mb_strtolower($kindName), implode(', ', $categories)],
            config('ai_prompts.vendor_map.base')
        );
        if ($area !== null) {
            $prompt .= str_replace(':area', $area, config('ai_prompts.vendor_map.area_line'));
        }
        if ($notes = trim((string) $notes)) {
            $prompt .= str_replace(':notes', mb_substr($notes, 0, 500), config('ai_prompts.vendor_map.notes_line'));
        }

        $answer = GeminiUtils::structured($prompt, [
            'system' => str_replace(':kind', mb_strtolower($kindName), config('ai_prompts.vendor_map.system')),
            'schema' => self::schema($categories),
            'images' => $images,
            'thinking_level' => 'low',
            'timeout' => 90,
            'stage' => 'vendor_map',
        ]);

        // One use per request to the AI (an area's photos go in one request), as an AI import
        // counts one: counted per photo, a market shot area by area ran out of the day's allowance
        // before its last area.
        UsageTrackingService::track(UsageTrackingService::GEMINI_PARSE_VENDOR_MAP, $role->id);

        return is_array($answer) ? $answer : null;
    }

    /**
     * The areas read one by one, laid side by side as blocks (up to four across) on one plan. Where
     * each block really is, the organizer shows by dragging it; the shoppers' map and the sign draw
     * areas as blocks anyway. Stall numbers stay unique: an area numbered 1, 2, 3 by the AI after
     * another that was continues from the highest number so far.
     */
    public static function assemble(array $groups, array $parts): array
    {
        ksort($parts, SORT_NUMERIC);

        // Each area at its own size (shrunk only when it is bigger than a frame), packed in rows of
        // up to $cols with a gap between blocks, so a small area does not float in an empty cell.
        $blocks = [];
        foreach ($parts as $key => $part) {
            $partSpots = (array) ($part['spots'] ?? []);
            if ($partSpots === []) {
                continue;
            }
            $things = array_merge($partSpots, (array) ($part['landmarks'] ?? []));
            $minX = min(array_map(fn ($t) => $t['x'], $things));
            $minY = min(array_map(fn ($t) => $t['y'], $things));
            $maxX = max(array_map(fn ($t) => $t['x'] + $t['w'], $things));
            $maxY = max(array_map(fn ($t) => $t['y'] + $t['h'], $things));
            $scale = min(1, 880 / max(1, $maxX - $minX), 600 / max(1, $maxY - $minY));
            $blocks[] = ['key' => (string) $key, 'part' => $part, 'minX' => $minX, 'minY' => $minY,
                'w' => ($maxX - $minX) * $scale, 'h' => ($maxY - $minY) * $scale, 'scale' => $scale];
        }
        $cols = max(1, min(4, (int) ceil(sqrt(count($blocks)))));
        $gap = 90;
        $x = $gap;
        $y = $gap;
        $rowH = 0;
        $width = 0;
        foreach ($blocks as $i => &$b) {
            if ($i > 0 && $i % $cols === 0) {
                $x = $gap;
                $y += $rowH + $gap;
                $rowH = 0;
            }
            $b['ox'] = $x;
            $b['oy'] = $y;
            $x += $b['w'] + $gap;
            $rowH = max($rowH, $b['h']);
            $width = max($width, $x);
        }
        unset($b);
        $height = $y + $rowH + $gap;

        $spots = [];
        $areas = [];
        $landmarks = [];
        $used = [];
        $highest = 0;
        $names = [];

        foreach ($blocks as $b) {
            $partSpots = (array) ($b['part']['spots'] ?? []);
            $name = self::areaName($groups, $b['key']);
            $base = $name;
            for ($n = 2; isset($names[mb_strtolower($name)]); $n++) {
                $name = $base.' '.$n;
            }
            $names[mb_strtolower($name)] = true;

            $at = fn ($t) => [
                'x' => (int) round($b['ox'] + ($t['x'] - $b['minX']) * $b['scale']),
                'y' => (int) round($b['oy'] + ($t['y'] - $b['minY']) * $b['scale']),
                'w' => max(12, (int) round($t['w'] * $b['scale'])),
                'h' => max(12, (int) round($t['h'] * $b['scale'])),
            ];

            $labels = array_map(fn ($sp) => (string) $sp['label'], $partSpots);
            $numeric = $labels !== [] && count(array_filter($labels, 'ctype_digit')) === count($labels);
            $clash = (bool) array_intersect(array_map('mb_strtolower', $labels), array_keys($used));
            $offset = $numeric && $clash ? $highest : 0;

            foreach ($partSpots as $sp) {
                $label = $offset ? (string) ((int) $sp['label'] + $offset) : (string) $sp['label'];
                $n = $highest;
                while (isset($used[mb_strtolower($label)])) {
                    $label = (string) (++$n);
                }
                $used[mb_strtolower($label)] = true;
                if (ctype_digit($label)) {
                    $highest = max($highest, (int) $label);
                }
                $spots[] = array_merge($sp, $at($sp), ['label' => $label, 'area' => $name]);
            }
            foreach ((array) ($b['part']['landmarks'] ?? []) as $l) {
                $landmarks[] = array_merge($l, $at($l));
            }
            $areas[] = ['name' => $name, 'x' => (int) $b['ox'] - 20, 'y' => (int) $b['oy'] - 30, 'w' => (int) $b['w'] + 40, 'h' => (int) $b['h'] + 50];
        }

        return [
            'width' => (int) min(4000, max(1000, ceil($width))),
            'height' => (int) min(4000, max(700, ceil($height))),
            'spots' => array_slice($spots, 0, VendorMapStructure::MAX_SPOTS),
            'areas' => $areas,
            'landmarks' => array_slice($landmarks, 0, 30),
            'names_read' => count(array_filter($spots, fn ($s) => ($s['name'] ?? '') !== '')),
            'blocks' => count($areas),
        ];
    }

    /**
     * The AI's answer, made safe to lay on a map: bounded counts, coordinates inside the frame,
     * labels unique, names trimmed, categories from the list.
     */
    public static function normalize(array $answer, string $kind): array
    {
        $spots = [];
        $seenLabels = [];
        $n = 0;
        foreach (array_slice((array) ($answer['spots'] ?? []), 0, VendorMapStructure::MAX_SPOTS) as $s) {
            if (! is_array($s)) {
                continue;
            }
            $n++;
            $label = mb_substr(trim((string) ($s['label'] ?? '')), 0, 20) ?: (string) $n;
            while (isset($seenLabels[mb_strtolower($label)])) {
                $label = (string) (++$n);
            }
            $seenLabels[mb_strtolower($label)] = true;

            $w = self::clamp($s['w'] ?? 40, 12, 300);
            $h = self::clamp($s['h'] ?? 30, 12, 300);
            $name = mb_substr(trim((string) ($s['name_seen'] ?? '')), 0, 120);
            $category = (string) ($s['category'] ?? '');

            $spots[] = [
                'label' => $label,
                'x' => self::clamp($s['x'] ?? 0, 0, 1000 - $w),
                'y' => self::clamp($s['y'] ?? 0, 0, 700 - $h),
                'w' => $w,
                'h' => $h,
                'rotation' => self::clamp($s['rotation'] ?? 0, -180, 180),
                'name' => $name,
                'sells' => mb_substr(trim((string) ($s['sells_seen'] ?? '')), 0, 200),
                'category' => VendorMapKinds::isCategory($kind, $category) ? $category : null,
                'confidence' => max(0, min(1, (float) ($s['confidence'] ?? ($name === '' ? 0 : 0.5)))),
                'area' => mb_substr(trim((string) ($s['area'] ?? '')), 0, 80),
            ];
        }

        $areas = [];
        foreach (array_slice((array) ($answer['areas'] ?? []), 0, VendorMapStructure::MAX_AREAS) as $a) {
            $name = mb_substr(trim((string) ($a['name'] ?? '')), 0, 80);
            if ($name === '' || ! is_array($a)) {
                continue;
            }
            $areas[] = [
                'name' => $name,
                'x' => self::clamp($a['x'] ?? 0, 0, 1000), 'y' => self::clamp($a['y'] ?? 0, 0, 700),
                'w' => self::clamp($a['w'] ?? 100, 10, 1000), 'h' => self::clamp($a['h'] ?? 100, 10, 700),
            ];
        }

        $landmarks = [];
        foreach (array_slice((array) ($answer['landmarks'] ?? []), 0, 30) as $l) {
            if (! is_array($l) || ! in_array($l['kind'] ?? null, ['entrance', 'restroom', 'parking', 'stage'], true)) {
                continue;
            }
            $landmarks[] = [
                'kind' => $l['kind'],
                'label' => mb_substr(trim((string) ($l['label'] ?? '')), 0, 80),
                'x' => self::clamp($l['x'] ?? 0, 0, 1000), 'y' => self::clamp($l['y'] ?? 0, 0, 700),
                'w' => self::clamp($l['w'] ?? 60, 10, 400), 'h' => self::clamp($l['h'] ?? 40, 10, 400),
            ];
        }

        return [
            'width' => 1000,
            'height' => 700,
            'spots' => $spots,
            'areas' => $areas,
            'landmarks' => $landmarks,
            'names_read' => count(array_filter($spots, fn ($s) => $s['name'] !== '')),
        ];
    }

    /** Gemini's response_schema for the answer. */
    private static function schema(array $categories): array
    {
        $int = ['type' => 'INTEGER'];
        $box = ['x' => $int, 'y' => $int, 'w' => $int, 'h' => $int];

        return [
            'type' => 'OBJECT',
            'properties' => [
                'spots' => ['type' => 'ARRAY', 'items' => [
                    'type' => 'OBJECT',
                    'properties' => $box + [
                        'label' => ['type' => 'STRING'],
                        'rotation' => $int,
                        'name_seen' => ['type' => 'STRING'],
                        'sells_seen' => ['type' => 'STRING'],
                        'category' => ['type' => 'STRING', 'enum' => array_values($categories)],
                        'confidence' => ['type' => 'NUMBER'],
                        'area' => ['type' => 'STRING'],
                    ],
                    'required' => ['x', 'y', 'w', 'h', 'label'],
                ]],
                'areas' => ['type' => 'ARRAY', 'items' => [
                    'type' => 'OBJECT',
                    'properties' => $box + ['name' => ['type' => 'STRING']],
                    'required' => ['name', 'x', 'y', 'w', 'h'],
                ]],
                'landmarks' => ['type' => 'ARRAY', 'items' => [
                    'type' => 'OBJECT',
                    'properties' => $box + [
                        'kind' => ['type' => 'STRING', 'enum' => ['entrance', 'restroom', 'parking', 'stage']],
                        'label' => ['type' => 'STRING'],
                    ],
                    'required' => ['kind', 'x', 'y'],
                ]],
            ],
            'required' => ['spots'],
        ];
    }

    /** jpg | png | webp | pdf by the file's own first bytes, or null. HEIC is refused (as the gallery does). */
    public static function extensionOf(string $bytes): ?string
    {
        $head = substr($bytes, 0, 12);

        return match (true) {
            str_starts_with($head, "\xFF\xD8\xFF") => 'jpg',
            str_starts_with($head, "\x89PNG") => 'png',
            str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP' => 'webp',
            str_starts_with($head, '%PDF') => 'pdf',
            default => null,
        };
    }

    private static function clamp($value, int $min, int $max): int
    {
        return max($min, min($max, (int) round((float) $value)));
    }
}
