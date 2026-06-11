<?php

namespace App\Utils;

class CsvUtils
{
    /**
     * Neutralize CSV / formula injection: prefix a leading =, +, -, @, TAB or CR
     * with a single quote so spreadsheet apps treat the cell as text rather than
     * evaluating it as a formula. Non-string values pass through unchanged.
     */
    public static function sanitizeCell($value)
    {
        if (is_string($value) && $value !== '' && preg_match('/^[\=\+\-\@\t\r]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
