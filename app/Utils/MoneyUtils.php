<?php

namespace App\Utils;

class MoneyUtils {
    public static function format($amount, $currencyCode) {
        return number_format($amount, 2, '.', ',') . ' ' . $currencyCode;
    }

}
