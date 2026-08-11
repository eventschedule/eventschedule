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
}
