<?php

namespace App\Traits;

use App\Utils\ImageUtils;
use Illuminate\Support\Facades\DB;

/**
 * Reads and writes the resized WebP derivatives of a model's stored images, one SLOT per image.
 *
 * A slot is a stored-filename column, the JSON column that records its derivatives, and the widths
 * they are built at (imageVariantSlots()). Most models have one - an event's flyer, the 'default'
 * slot - and a schedule has three: its profile photo ('default'), and the header and background it
 * can upload ('header', 'background'). Every method here takes the slot and defaults to 'default',
 * so a one-image model never has to name it.
 *
 * A variants column holds {"w480": "flyer_abc_w480.webp", "w960": ..., "src": {"w": 1600,
 * "h": 2133}}, or a recorded skip such as {"w480": null, "w960": null, "skipped": "too_large",
 * "src": {...}}. `src` is the original's displayed size (ImageUtils::generateStoredVariants()); rows
 * built before it was recorded simply lack it until `images:backfill-variants --dimensions` runs.
 * `"animated": true` marks an original that moves, whose derivatives are still pictures of its
 * first frame (imageIsAnimated()); rows built before it was recorded lack it until
 * `images:backfill-variants --animated` runs.
 *
 * The using model casts each variants column to array, keeps it out of $fillable (it is derived
 * state, written only by the generation job and `images:backfill-variants` through
 * recordImageVariants()), and names its default source column in imageVariantSourceColumn().
 */
trait HasImageVariants
{
    /** The stored-filename column the 'default' slot's derivatives are built from. */
    abstract public function imageVariantSourceColumn(): string;

    /**
     * slot => [source column, variants column, widths].
     *
     * One column per slot, never a shared one: recordImageVariants() rewrites the whole column,
     * guarded on its one source, so two slots sharing a column would overwrite each other whenever
     * one save replaced both images.
     *
     * @return array<string, array{0: string, 1: string, 2: int[]}>
     */
    public function imageVariantSlots(): array
    {
        return ['default' => [$this->imageVariantSourceColumn(), 'image_variants', ImageUtils::VARIANT_WIDTHS]];
    }

    /**
     * @return array{0: string, 1: string, 2: int[]}
     */
    protected function imageVariantSlot(string $slot): array
    {
        $slots = $this->imageVariantSlots();

        if (! isset($slots[$slot])) {
            throw new \InvalidArgumentException('Unknown image variant slot "'.$slot.'" on '.static::class);
        }

        return $slots[$slot];
    }

    /** The column a slot's derivatives are recorded in. */
    public function imageVariantColumn(string $slot = 'default'): string
    {
        return $this->imageVariantSlot($slot)[1];
    }

    /** The widths a slot's derivatives are built at, smallest first. */
    public function imageVariantWidths(string $slot = 'default'): array
    {
        return $this->imageVariantSlot($slot)[2];
    }

    /** The raw stored filename, bypassing the column's URL accessor. */
    public function imageVariantSource(string $slot = 'default'): ?string
    {
        $raw = $this->getAttributes()[$this->imageVariantSlot($slot)[0]] ?? null;

        return (is_string($raw) && $raw !== '') ? $raw : null;
    }

    /**
     * Everything recorded for a slot, [] when nothing is - including a narrowed get() that did not
     * select the column at all.
     */
    public function imageVariants(string $slot = 'default'): array
    {
        $value = $this->getAttribute($this->imageVariantColumn($slot));

        return is_array($value) ? $value : [];
    }

    /**
     * The stored filename of the derivative at the given width, or null.
     *
     * Null covers all three "no derivative" cases at once: never generated, deliberately skipped
     * (the value is null beside a `skipped` reason), and a narrowed get() that did not select the
     * column at all.
     */
    public function imageVariantFilename(int $width = ImageUtils::VARIANT_WIDTH, string $slot = 'default'): ?string
    {
        $name = $this->imageVariants($slot)['w'.$width] ?? null;

        return (is_string($name) && $name !== '') ? $name : null;
    }

    /**
     * Whether the slot's original moves: an animated GIF, WebP or PNG, as the pipeline recorded it
     * (ImageUtils::isAnimated()). Its derivatives are still pictures of the first frame.
     */
    public function imageIsAnimated(string $slot = 'default'): bool
    {
        return $this->imageVariantSource($slot) !== null
            && ($this->imageVariants($slot)['animated'] ?? false) === true;
    }

    /**
     * The public URL of the derivative at the given width, or null when there is none.
     *
     * $pageWidth is for a surface that shows the image as the page itself - an event's flyer on its
     * page, a schedule's banner header or its background - where an animated original is shown as
     * it is: null here, so the caller falls back to the original, which moves. A card, the homepage
     * wall and an avatar never pass it and keep the still thumbnail.
     */
    public function imageVariantUrl(int $width, string $slot = 'default', bool $pageWidth = false): ?string
    {
        if (! $this->imageVariantSource($slot) || ($pageWidth && $this->imageIsAnimated($slot))) {
            return null;
        }

        $filename = $this->imageVariantFilename($width, $slot);

        return $filename ? ImageUtils::variantUrl($filename) : null;
    }

    /**
     * The original's size as a browser displays it, [width, height], or null when it was never
     * recorded. The shape SeoUtils::imageObject() takes, so og:image:width and an ImageObject's
     * width can describe a file on object storage.
     *
     * @return array{0: int, 1: int}|null
     */
    public function imageSourceDimensions(string $slot = 'default'): ?array
    {
        if (! $this->imageVariantSource($slot)) {
            return null;
        }

        $src = $this->imageVariants($slot)['src'] ?? null;
        $width = is_array($src) ? (int) ($src['w'] ?? 0) : 0;
        $height = is_array($src) ? (int) ($src['h'] ?? 0) : 0;

        return ($width > 0 && $height > 0) ? [$width, $height] : null;
    }

    /**
     * The size of the derivative at $width, [width, height]: the original scaled to that width and
     * never up, which is exactly the arithmetic ImageUtils::writeStoredVariant() resamples with.
     * Null unless the derivative exists and the original's size is recorded.
     *
     * @return array{0: int, 1: int}|null
     */
    public function imageVariantDimensions(int $width, string $slot = 'default'): ?array
    {
        if (! $this->imageVariantFilename($width, $slot) || ! ($src = $this->imageSourceDimensions($slot))) {
            return null;
        }

        [$srcWidth, $srcHeight] = $src;
        $destWidth = min($width, $srcWidth);

        return [$destWidth, max(1, (int) round($srcHeight * ($destWidth / $srcWidth)))];
    }

    /**
     * A `srcset` of every generated width of a slot, or null when the set is incomplete.
     *
     * All-or-nothing on purpose: a srcset listing one width is just a slower way of writing
     * `src`, and a card that offered only the 480 would tell a 2x screen that 480 is the best
     * available and stop it falling back to the (sharper) original.
     *
     * $withOriginal appends the original itself at its recorded width, for a surface shown large
     * enough that a 2x screen can use more than the widest derivative (the event page's flyer).
     * Only when that width is known and wider than every derivative: a small original is already
     * re-encoded whole at its own width, so it would add nothing.
     *
     * $pageWidth, as for imageVariantUrl(): null for an animated original, whose page-width surface
     * shows the original alone, since a browser would pick a still derivative from the set.
     */
    public function imageVariantSrcset(string $slot = 'default', bool $withOriginal = false, bool $pageWidth = false): ?string
    {
        if (! $this->imageVariantSource($slot) || ($pageWidth && $this->imageIsAnimated($slot))) {
            return null;
        }

        $widths = $this->imageVariantWidths($slot);
        $parts = [];

        foreach ($widths as $width) {
            $filename = $this->imageVariantFilename($width, $slot);

            if (! $filename) {
                return null;
            }

            $parts[] = ImageUtils::variantUrl($filename).' '.$width.'w';
        }

        $src = $withOriginal ? $this->imageSourceDimensions($slot) : null;

        if ($parts && $src && $src[0] > max($widths)) {
            // The column's own accessor, so the original resolves exactly as the page's src does.
            $parts[] = $this->getAttribute($this->imageVariantSlot($slot)[0]).' '.$src[0].'w';
        }

        return $parts ? implode(', ', $parts) : null;
    }

    /**
     * Record the result of a derivative build.
     *
     * Written with the query builder, guarded on the source filename it was built from: resizing
     * a multi-megabyte original takes seconds, and if the owner replaced the image in that window
     * the saving hook already cleared this column and queued a fresh job. An unguarded write here
     * would file the OLD image's thumbnail under the NEW one, and every card would show the wrong
     * picture until someone edited the row again. Going around Eloquent also keeps updated_at
     * (and the federation re-publish check that reads it) out of a purely derived write, and
     * fires no model events, so it cannot re-enter the hooks that queued the job.
     *
     * Returns whether the row still matched.
     */
    public function recordImageVariants(array $variants, string $slot = 'default'): bool
    {
        [$source, $column] = $this->imageVariantSlot($slot);
        $raw = $this->getAttributes()[$source] ?? null;

        if (! $raw) {
            return false;
        }

        $encoded = json_encode($variants);

        $affected = DB::table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->where($source, $raw)
            ->update([$column => $encoded]);

        if ($affected) {
            $this->attributes[$column] = $encoded;
            $this->syncOriginalAttribute($column);
        }

        return $affected > 0;
    }

    /**
     * Record only the original's size, leaving every recorded derivative as it is - for
     * `images:backfill-variants --dimensions`, over rows built before sizes were recorded.
     *
     * JSON_SET inside the UPDATE rather than a read-modify-write of the whole column, so a
     * generation job that records its filenames in between keeps them. Guarded on the source like
     * recordImageVariants(), for the same reason.
     *
     * @param  array{w: int, h: int}  $src
     */
    public function recordImageSourceDimensions(array $src, string $slot = 'default'): bool
    {
        [$source, $column] = $this->imageVariantSlot($slot);
        $raw = $this->getAttributes()[$source] ?? null;
        $width = (int) ($src['w'] ?? 0);
        $height = (int) ($src['h'] ?? 0);

        if (! $raw || $width < 1 || $height < 1) {
            return false;
        }

        $wrapped = DB::connection()->getQueryGrammar()->wrap($column);

        // Both numbers are cast to int above, so interpolating them cannot carry anything else.
        $affected = DB::table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->where($source, $raw)
            ->update([$column => DB::raw(
                "JSON_SET(COALESCE({$wrapped}, JSON_OBJECT()), '$.src', JSON_OBJECT('w', {$width}, 'h', {$height}))"
            )]);

        if ($affected) {
            $this->attributes[$column] = json_encode(['src' => ['w' => $width, 'h' => $height]] + $this->imageVariants($slot));
            $this->syncOriginalAttribute($column);
        }

        return $affected > 0;
    }
}
