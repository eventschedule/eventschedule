<?php

namespace App\Utils;

use App\Exceptions\BusinessException;
use App\Jobs\DeleteGalleryFiles;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\GalleryImage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The organizer photo gallery's write paths: storing an upload, committing an edit form's gallery
 * on Save, and removing rows together with their files.
 *
 * The edit forms upload each photo the moment it is added (GalleryController), as a DRAFT row
 * tagged with a token the page minted, and post the gallery's final order as JSON with the rest
 * of the form. sync() then commits the listed rows and deletes the rest, so nothing a guest can
 * see changes until the organizer saves - the same contract as every other field on the form.
 */
class GalleryUtils
{
    /** The longest side, in pixels, of a stored gallery photo. */
    public const MAX_DIMENSION = 2000;

    /** How many unsaved uploads one user may have outstanding, across every form. */
    public const MAX_DRAFTS_PER_USER = 200;

    /** Above this many rows, purge() hands the file deletes to the queue. */
    public const INLINE_PURGE_LIMIT = 200;

    public const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public const MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /** The "New" pill on the schedule form's Gallery nav item shows until this date. */
    public const NEW_UNTIL = '2026-12-31';

    public static function maxImages(): int
    {
        return max(1, (int) config('app.max_gallery_images', 50));
    }

    /**
     * The largest file one upload can be: 10MB, or less when PHP's own upload_max_filesize is
     * lower (5M on the hosted deploy, via public/.user.ini; often 2M on a selfhost install).
     * Past that PHP drops the file before the app sees it, so the editor shrinks photos to fit
     * this number in the browser rather than promise one the server cannot take.
     */
    public static function maxUploadBytes(): int
    {
        $php = (int) UploadedFile::getMaxFilesize();

        return $php > 0 ? min(10 * 1024 * 1024, $php) : 10 * 1024 * 1024;
    }

    /** A draft token as the editor mints it: 32 lowercase hex characters. */
    public static function isValidToken(mixed $token): bool
    {
        return is_string($token) && preg_match('/^[a-f0-9]{32}$/', $token) === 1;
    }

    /**
     * Validate, normalise and store an uploaded photo.
     *
     * JPEG and WebP are always re-encoded, even when already small enough: the gallery publishes
     * the original (the lightbox and the no-JS tile link both open it), and the re-encode is what
     * strips the EXIF block - a phone photo's GPS position included. EXIF orientation is applied
     * first, so the stored pixels are upright. Animated images are kept whole.
     *
     * @return array{filename: string, width: ?int, height: ?int, color: ?string}
     *
     * @throws BusinessException with a message fit for the uploader when the file is refused
     * @throws \Exception from ImageUtils::validateUploadedFile(), in English, for the caller to map
     */
    public static function storeUpload(UploadedFile $file): array
    {
        ImageUtils::validateUploadedFile($file, self::maxUploadBytes());

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::EXTENSIONS, true) || ! in_array($file->getMimeType(), self::MIME_TYPES, true)) {
            throw new BusinessException(__('messages.gallery_invalid_type'));
        }

        if (@getimagesize($file->getRealPath()) === false) {
            throw new BusinessException(__('messages.gallery_invalid_type'));
        }

        return self::storeLocalFile($file->getRealPath(), $extension);
    }

    /**
     * Copy an approved fan photo into a new gallery file of its own, so rejecting or deleting the
     * fan photo later can never break the gallery.
     *
     * @return array{filename: string, width: ?int, height: ?int, color: ?string}|null
     */
    public static function copyFanPhoto(EventPhoto $photo): ?array
    {
        $raw = $photo->getAttributes()['photo_url'] ?? null;

        if (! is_string($raw) || $raw === '' || str_starts_with($raw, 'http')) {
            return null;
        }

        $extension = strtolower(pathinfo($raw, PATHINFO_EXTENSION));

        if (! in_array($extension, self::EXTENSIONS, true)) {
            return null;
        }

        $bytes = Storage::get(ImageUtils::storagePathFor($raw));

        if (! is_string($bytes) || $bytes === '') {
            return null;
        }

        $temp = tempnam(sys_get_temp_dir(), 'gallery_');

        try {
            file_put_contents($temp, $bytes);

            if (@getimagesize($temp) === false) {
                return null;
            }

            return self::storeLocalFile($temp, $extension);
        } finally {
            @unlink($temp);
        }
    }

    /**
     * @return array{filename: string, width: ?int, height: ?int, color: ?string}
     */
    private static function storeLocalFile(string $path, string $extension): array
    {
        $mime = (@getimagesize($path))['mime'] ?? null;
        $isPhoto = in_array($mime, ['image/jpeg', 'image/webp'], true);

        // The re-encode is what strips a phone photo's EXIF block, GPS position included, and
        // this file is published as it is. So a JPEG or WebP that could not be re-encoded - too
        // large to decode within the memory budget, unreadable to GD, or an animated WebP, which
        // the resizer keeps whole - is refused rather than stored with its metadata intact.
        // (A PNG can carry an eXIf chunk too; the editor re-encodes every PNG in the browser,
        // which drops it, and GD's PNG writer never adds one.)
        if ($isPhoto && ImageUtils::isAnimated($path, $mime)) {
            throw new BusinessException(__('messages.gallery_invalid_type'));
        }

        if (! ImageUtils::resizeImageToMax($path, self::MAX_DIMENSION, reencodePhotos: true) && $isPhoto) {
            throw new BusinessException(__('messages.gallery_too_large', ['size' => round(self::maxUploadBytes() / 1048576, 1).' MB']));
        }

        $size = ImageUtils::orientedImageSize($path);
        $color = ImageUtils::averageColor($path);

        $filename = strtolower('gallery_'.Str::random(32).'.'.($extension === 'jpeg' ? 'jpg' : $extension));

        // Storage::put() rather than Storage::copy(): the S3 ACL on a copied object is unreliable
        // (see EventRepo's clone path), and every other image write in the app goes through put.
        Storage::put(ImageUtils::storagePathFor($filename), file_get_contents($path));

        return [
            'filename' => $filename,
            'width' => $size['w'] ?? null,
            'height' => $size['h'] ?? null,
            'color' => $color,
        ];
    }

    /**
     * The committed rows of one gallery: an event's, or a schedule's own (event_id null).
     */
    public static function committedQuery(Role $owner, ?Event $event): Builder
    {
        $query = GalleryImage::query()->whereNull('draft_token');

        return $event
            ? $query->where('event_id', $event->id)
            : $query->where('role_id', $owner->id)->whereNull('event_id');
    }

    /**
     * What the edit form's gallery editor starts from: the committed gallery, or - when the form
     * is being shown again after a failed save - exactly what that submission posted, so the
     * photos it had just uploaded are not lost. The same ownership rule as sync(): only this
     * gallery's rows and $user's own drafts under the posted token can come back.
     *
     * `known` is the committed rows it was rendered with, posted back as gallery_known_ids so a
     * save only removes photos this form actually showed (sync()).
     *
     * @return array{images: array<int, array<string, mixed>>, token: string, known: string[]}
     */
    public static function editorState(Role $owner, ?Event $event, User $user): array
    {
        $committed = ($event && ! $event->exists) ? collect() : self::committedQuery($owner, $event)->get();
        $oldPayload = old('gallery_images');
        $oldToken = old('gallery_draft_token');

        if (is_string($oldPayload) && self::isValidToken($oldToken)) {
            $items = json_decode($oldPayload, true);
            $rows = $committed->keyBy('id');
            $drafts = GalleryImage::where('draft_token', $oldToken)->where('user_id', $user->id)->get()->keyBy('id');
            $images = [];

            foreach (is_array($items) ? $items : [] as $item) {
                $id = is_array($item) ? self::decodeOne($item['id'] ?? null) : null;
                $row = $id ? ($rows[$id] ?? $drafts[$id] ?? null) : null;

                if ($row) {
                    $images[] = [
                        'caption' => self::cleanText($item['caption'] ?? null, 255) ?? '',
                        'credit' => self::cleanText($item['credit'] ?? null, 100) ?? '',
                    ] + $row->toEditorArray();
                }
            }

            // What the ORIGINAL form knew, so the re-shown form still leaves alone a photo that
            // somebody else added in between.
            $known = self::decodeIdList(old('gallery_known_ids'));
            $known = $known === null
                ? $committed->pluck('id')->all()
                : array_values(array_intersect($known, $committed->pluck('id')->all()));

            return [
                'images' => $images,
                'token' => $oldToken,
                'known' => array_map(fn ($id) => UrlUtils::encodeId($id), $known),
            ];
        }

        return [
            'images' => $committed->map(fn (GalleryImage $image) => $image->toEditorArray())->values()->all(),
            'token' => bin2hex(random_bytes(16)),
            'known' => $committed->map(fn (GalleryImage $image) => UrlUtils::encodeId($image->id))->values()->all(),
        ];
    }

    /**
     * Commit an edit form's gallery.
     *
     * $payload is the form's `gallery_images` field: a JSON list of {id, caption, credit} in
     * display order. Only two kinds of row can be named in it - this gallery's committed rows,
     * and $user's own drafts under $token - and any other id is ignored, which is the whole
     * authorization story for the ids a browser posts.
     *
     * $knownIds is the form's `gallery_known_ids`: the committed rows the editor was rendered with.
     * Only those can be removed by leaving them out. A photo somebody else added while this form
     * was open is not in it, so a save from the stale form keeps it rather than deleting it. A
     * form that does not send the field (an older page still open across a deploy) is treated as
     * knowing every committed row, as before.
     *
     * The limit applies to what is ADDED: every listed committed row is kept even past it (a
     * merge or a lowered MAX_GALLERY_IMAGES can leave a gallery over the limit, and a save must
     * never delete photos for that), and new drafts are taken only into the room that is left.
     *
     * While $owner is not on a paid plan only the removals apply: a downgraded schedule keeps its
     * photos (hidden from guests) and can still remove them, but cannot add or rearrange.
     *
     * Null when the form carried no gallery at all (an API client, a form without the section),
     * which leaves the gallery untouched. Otherwise the counts the controllers' messages are built
     * from (errorMessage()): before/after for the first-publish panel, missing for drafts that
     * were pruned while the form was open, refused_limit and refused_plan for drafts that could
     * not be published.
     *
     * @return array{before: int, after: int, missing: int, refused_limit: int, refused_plan: int, changed: bool}|null
     */
    public static function sync(Role $owner, ?Event $event, mixed $payload, mixed $token, User $user, mixed $knownIds = null): ?array
    {
        if (! is_string($payload) || is_demo_mode()) {
            return null;
        }

        $items = json_decode($payload, true);

        if (! is_array($items)) {
            return null;
        }

        $committed = self::committedQuery($owner, $event)->get()->keyBy('id');
        $drafts = self::isValidToken($token)
            ? GalleryImage::where('draft_token', $token)->where('user_id', $user->id)->get()->keyBy('id')
            : collect();

        $known = self::decodeIdList($knownIds);
        $removable = $known === null ? $committed : $committed->only($known);

        // Resolve the posted list once: [row, caption, credit] in order, duplicates dropped.
        $listed = [];
        $missing = 0;

        foreach (array_values($items) as $item) {
            $id = is_array($item) ? self::decodeOne($item['id'] ?? null) : null;
            $row = $id ? ($committed[$id] ?? $drafts[$id] ?? null) : null;

            if (! $row) {
                // A draft that is gone was pruned while the page was open. A committed photo that
                // is gone was removed by somebody else since, which is not this form's loss.
                if ($id && ($known === null || ! in_array($id, $known, true))) {
                    $missing++;
                }

                continue;
            }

            if (isset($listed[$row->id])) {
                continue;
            }

            $listed[$row->id] = [
                'row' => $row,
                'caption' => self::cleanText($item['caption'] ?? null, 255),
                'credit' => self::cleanText($item['credit'] ?? null, 100),
            ];
        }

        $canArrange = $owner->isPro();
        $committedListed = count(array_filter($listed, fn ($entry) => ! $entry['row']->isDraft()));
        $draftRoom = $canArrange ? max(0, self::maxImages() - $committedListed) : 0;
        $keep = [];
        $refusedLimit = 0;
        $refusedPlan = 0;

        foreach ($listed as $rowId => $entry) {
            if ($entry['row']->isDraft()) {
                if (! $canArrange) {
                    $refusedPlan++;

                    continue;
                }

                if ($draftRoom <= 0) {
                    $refusedLimit++;

                    continue;
                }

                $draftRoom--;
            }

            $keep[$rowId] = $entry;
        }

        $changed = false;

        if ($canArrange) {
            DB::transaction(function () use ($keep, $owner, $event, $committed, $removable, &$changed) {
                $position = 0;

                foreach ($keep as $entry) {
                    $row = $entry['row'];
                    $wasDraft = $row->isDraft();

                    $row->fill([
                        'role_id' => $owner->id,
                        'event_id' => $event?->id,
                        'draft_token' => null,
                        'sort_order' => $position++,
                        'caption' => $entry['caption'],
                        'credit' => $entry['credit'],
                    ]);

                    if ($row->isDirty()) {
                        $row->save();
                        $changed = true;
                    }

                    if ($wasDraft) {
                        $changed = true;
                    }
                }

                // Photos this form never knew about (added elsewhere while it was open) keep
                // their place after the ones it arranged.
                foreach ($committed as $row) {
                    if (! isset($keep[$row->id]) && ! isset($removable[$row->id])) {
                        $row->sort_order = $position++;
                        $row->save();
                    }
                }
            });
        }

        // Outside the transaction: each delete removes files from the disk, which may be object
        // storage, and no network I/O belongs inside one.
        foreach ($removable as $row) {
            if (! isset($keep[$row->id]) && ! isset($listed[$row->id])) {
                $row->delete();
                $changed = true;
            }
        }

        foreach ($drafts as $row) {
            if (! isset($keep[$row->id])) {
                $row->delete();
            }
        }

        if ($changed) {
            // updated_at is what the sitemap's lastmod and the federation re-publish check read.
            ($event ?? $owner)->touch();
        }

        return [
            'before' => $committed->count(),
            'after' => self::committedQuery($owner, $event)->count(),
            'missing' => $missing,
            'refused_limit' => $refusedLimit,
            'refused_plan' => $refusedPlan,
            'changed' => $changed,
        ];
    }

    /**
     * The error a save should show for what sync() could not publish, or null when everything the
     * form listed went through. One string, because the layout toasts one message.
     */
    public static function errorMessage(?array $result): ?string
    {
        if (! $result) {
            return null;
        }

        $parts = [];

        if ($result['missing'] > 0) {
            $parts[] = trans_choice('messages.gallery_drafts_lost', $result['missing'], ['count' => $result['missing']]);
        }

        if ($result['refused_limit'] > 0) {
            $parts[] = __('messages.gallery_over_limit', ['count' => $result['refused_limit'], 'max' => self::maxImages()]);
        }

        if ($result['refused_plan'] > 0) {
            $parts[] = __('messages.gallery_requires_pro');
        }

        return $parts ? implode(' ', $parts) : null;
    }

    /** A posted encoded id, or null for anything that is not one (an array included). */
    private static function decodeOne(mixed $value): ?int
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $id = UrlUtils::decodeId($value);

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * A posted JSON list of encoded ids as ints, or null when the field is absent or unreadable
     * (then the caller falls back to its old behaviour).
     *
     * @return int[]|null
     */
    private static function decodeIdList(mixed $value): ?array
    {
        if (! is_string($value)) {
            return null;
        }

        $list = json_decode($value, true);

        if (! is_array($list)) {
            return null;
        }

        return array_values(array_filter(array_map(fn ($v) => self::decodeOne($v), $list)));
    }

    /**
     * Delete gallery rows together with their files.
     *
     * The event and schedule foreign keys cascade in the database, which never fires
     * GalleryImage's deleting hook, so every path that deletes an event or a schedule calls this
     * first. A large schedule can hold thousands of photos, so above INLINE_PURGE_LIMIT the rows
     * are deleted here and the files handed to the queue.
     */
    public static function purge(Builder $query): void
    {
        $count = (clone $query)->count();

        if ($count === 0) {
            return;
        }

        if ($count <= self::INLINE_PURGE_LIMIT) {
            $query->get()->each->delete();

            return;
        }

        $filenames = (clone $query)->pluck('filename')->filter()->values()->all();
        $query->delete();

        foreach (array_chunk($filenames, 500) as $chunk) {
            DeleteGalleryFiles::dispatch($chunk);
        }
    }

    private static function cleanText(mixed $value, int $max): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
