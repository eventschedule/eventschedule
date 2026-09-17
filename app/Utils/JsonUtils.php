<?php

namespace App\Utils;

class JsonUtils
{
    /**
     * Decode a value that should be a JSON array or object, peeling off any extra layers of
     * encoding, and return null for anything that does not end up as an array.
     *
     * Exists for columns with an `array` cast. Eloquent JSON-encodes EVERY non-null value assigned
     * to such an attribute, including a string that is already JSON, so assigning a raw column value
     * (which is what a backup carries) stores `"{\"a\":1}"` and reads back a string. A row that has
     * been through that once, or more, still decodes here.
     *
     * $maxDepth bounds the loop against a pathological value; real data is at most a layer or two.
     */
    public static function decodeToArray(mixed $value, int $maxDepth = 5): ?array
    {
        for ($depth = 0; is_string($value) && $depth < $maxDepth; $depth++) {
            $value = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
        }

        return is_array($value) ? $value : null;
    }
}
