<?php

namespace App\Utils;

class TextUtils
{
    /**
     * Cut a string down to what its column can hold.
     *
     * For AI output, which is the only reason this exists: nothing constrains what a model returns,
     * and the app's short columns are varchar(255) under a strict-mode connection, so an over-long
     * value is a QueryException (MySQL 1406) rather than a silent truncation.
     *
     * mb_substr, not substr: the connection is utf8mb4, so a varchar(255) holds 255 CHARACTERS.
     * Cutting on bytes would both under-fill the column and split a multi-byte character.
     *
     * Returns null rather than '' for a value that is nothing but trimmed-away whitespace: the
     * translation columns treat '' as "source language equals target, nothing to do", which no
     * whereNull() can see, so writing one by accident parks a row permanently.
     */
    public static function clamp(?string $value, int $maxLength): ?string
    {
        if ($value === null || mb_strlen($value) <= $maxLength) {
            return $value;
        }

        // Compared against '' rather than `?: null`, which would also discard "0".
        $clamped = rtrim(mb_substr($value, 0, $maxLength));

        return $clamped === '' ? null : $clamped;
    }

    /**
     * Collapse CRLF and lone CR to LF.
     *
     * A textarea's maxlength constrains its API value, where a line break is a single LF, but form
     * submission serializes the value with CRLF. So a textarea capped at maxlength="500" submits up
     * to 500 + N characters, where N is its newline count, and the extra bytes land in the column
     * as a QueryException (MySQL 1406) rather than a truncation. A 500-character AI prompt with six
     * line breaks arrived as 506 and 500'd an event save that had not touched the field at all.
     *
     * Normalizing is what makes such a value fit, and it stores exactly what the user typed, so it
     * belongs in front of clamp() rather than instead of it.
     *
     * Apply it only to genuinely multi-line fields. A single-line value that is compared elsewhere
     * against its raw submitted form - events.event_password, which hash_equals() checks against an
     * un-normalized request value - must not be rewritten here, or it stops matching.
     */
    public static function normalizeNewlines(?string $value): ?string
    {
        return $value === null ? null : str_replace(["\r\n", "\r"], "\n", $value);
    }
}
