<?php

namespace App\Traits;

use App\Utils\ImageUtils;
use Illuminate\Support\Facades\DB;

/**
 * Reads and writes the `image_variants` column: the resized WebP derivatives of ONE stored image
 * column on the model (an event's flyer, a schedule's profile photo).
 *
 * The using model casts `image_variants` to array, keeps it out of $fillable (it is derived
 * state, written only by the generation job and `images:backfill-variants` through
 * recordImageVariants()), and names its source column in imageVariantSourceColumn().
 */
trait HasImageVariants
{
    /** The stored-filename column the derivatives are built from. */
    abstract public function imageVariantSourceColumn(): string;

    /** The raw stored filename, bypassing the column's URL accessor. */
    public function imageVariantSource(): ?string
    {
        $raw = $this->getAttributes()[$this->imageVariantSourceColumn()] ?? null;

        return (is_string($raw) && $raw !== '') ? $raw : null;
    }

    /**
     * The stored filename of the derivative at the given width, or null.
     *
     * Null covers all three "no derivative" cases at once: never generated, deliberately skipped
     * (the value is null beside a `skipped` reason), and a narrowed get() that did not select the
     * column at all.
     */
    public function imageVariantFilename(int $width = ImageUtils::VARIANT_WIDTH): ?string
    {
        $variants = $this->image_variants;

        if (! is_array($variants)) {
            return null;
        }

        $name = $variants['w'.$width] ?? null;

        return (is_string($name) && $name !== '') ? $name : null;
    }

    /**
     * A `srcset` of every generated width, or null when the set is incomplete.
     *
     * All-or-nothing on purpose: a srcset listing one width is just a slower way of writing
     * `src`, and a card that offered only the 480 would tell a 2x screen that 480 is the best
     * available and stop it falling back to the (sharper) original.
     */
    public function imageVariantSrcset(): ?string
    {
        if (! $this->imageVariantSource()) {
            return null;
        }

        $parts = [];

        foreach (ImageUtils::VARIANT_WIDTHS as $width) {
            $filename = $this->imageVariantFilename($width);

            if (! $filename) {
                return null;
            }

            $parts[] = ImageUtils::variantUrl($filename).' '.$width.'w';
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
    public function recordImageVariants(array $variants): bool
    {
        $column = $this->imageVariantSourceColumn();
        $raw = $this->getAttributes()[$column] ?? null;

        if (! $raw) {
            return false;
        }

        $encoded = json_encode($variants);

        $affected = DB::table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->where($column, $raw)
            ->update(['image_variants' => $encoded]);

        if ($affected) {
            $this->attributes['image_variants'] = $encoded;
            $this->syncOriginalAttribute('image_variants');
        }

        return $affected > 0;
    }
}
