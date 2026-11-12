<?php

namespace App\Support;

use Illuminate\Translation\MessageSelector;

/**
 * Laravel's plural rules are keyed by underscore locales ('pt_PT'), and a regional code this app
 * uses with a hyphen ('pt-PT', the form <html lang>, hreflang and the browser expect) matched none
 * of them, so every plural without explicit {n} / [a,b] conditions took its first form under
 * pt-PT: "Tem 3 bilhete". The rule is looked up under the underscore spelling instead.
 */
class RegionalMessageSelector extends MessageSelector
{
    public function getPluralIndex($locale, $number)
    {
        return parent::getPluralIndex(str_replace('-', '_', (string) $locale), $number);
    }
}
