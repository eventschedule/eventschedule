<?php

namespace App\Utils;

class DateUtils
{
    /**
     * Returns a valid month (1-12), defaulting to the current month when the
     * input is missing, non-numeric, or out of range.
     */
    public static function normalizeMonth($value): int
    {
        $month = is_numeric($value) ? (int) $value : 0;

        return ($month >= 1 && $month <= 12) ? $month : now()->month;
    }

    /**
     * Returns a sane year (1970-9999), defaulting to the current year when the
     * input is missing, non-numeric, or out of range.
     */
    public static function normalizeYear($value): int
    {
        $year = is_numeric($value) ? (int) $value : 0;

        return ($year >= 1970 && $year <= 9999) ? $year : now()->year;
    }
}
