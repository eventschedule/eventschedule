<?php

// Audits resources/lang/* against English. Run from the repo root:
//
//     php storage/check_translations.php
//
// Reports, per file and locale:
//   - missing / extra keys
//   - duplicate top-level keys (PHP keeps the LAST value, so the earlier line is dead but looks real)
//   - :placeholder mismatches (a translated or dropped placeholder renders literally or loses its value)
//   - values still identical to English, ignoring case (informational: some are genuine cognates or
//     brand names)
//
// Duplicates and placeholder mismatches are also enforced by tests/Feature/LanguageFileIntegrityTest.php.

$files = ['messages.php', 'marketing.php', 'accessibility.php'];
$locales = array_values(array_filter(
    array_map('basename', glob('resources/lang/*', GLOB_ONLYDIR)),
    fn ($locale) => $locale !== 'en'
));

$flatten = function (array $array, string $prefix = '') use (&$flatten): array {
    $out = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $out += $flatten($value, $prefix.$key.'.');
        } else {
            $out[$prefix.$key] = (string) $value;
        }
    }

    return $out;
};

$placeholders = function (string $value): array {
    preg_match_all('/:([a-zA-Z_]+)/', $value, $matches);
    $names = array_unique($matches[1]);
    sort($names);

    return $names;
};

$duplicateKeys = function (string $path): array {
    preg_match_all("/^    '([^']+)' =>/m", file_get_contents($path), $matches);

    return array_keys(array_filter(array_count_values($matches[1]), fn ($count) => $count > 1));
};

$problems = 0;

foreach ($files as $file) {
    $basePath = "resources/lang/en/$file";
    $base = $flatten(require $basePath);

    foreach (array_merge(['en'], $locales) as $locale) {
        $path = "resources/lang/$locale/$file";

        if (! file_exists($path)) {
            echo "\n$path: FILE NOT FOUND\n";
            $problems++;

            continue;
        }

        $report = [];

        if ($duplicates = $duplicateKeys($path)) {
            $report['Duplicate keys'] = $duplicates;
        }

        if ($locale !== 'en') {
            $translations = $flatten(require $path);

            if ($missing = array_keys(array_diff_key($base, $translations))) {
                $report['Missing keys'] = $missing;
            }

            if ($extra = array_keys(array_diff_key($translations, $base))) {
                $report['Extra keys'] = $extra;
            }

            $mismatched = [];
            $untranslated = [];

            foreach ($base as $key => $english) {
                if (! array_key_exists($key, $translations)) {
                    continue;
                }

                if ($placeholders($english) !== $placeholders($translations[$key])) {
                    $mismatched[] = "$key (en: :".implode(' :', $placeholders($english)).')';
                }

                // Case-insensitive: re-capitalising the English ("Promo code" to "Promo Code") must
                // not hide the copies that were made of the old spelling.
                if (mb_strtolower($translations[$key]) === mb_strtolower($english) && preg_match('/[a-z]/i', $english)) {
                    $untranslated[] = $key;
                }
            }

            if ($mismatched) {
                $report['Placeholder mismatches'] = $mismatched;
            }
        }

        $problems += array_sum(array_map('count', $report));

        if ($report) {
            echo "\n$path:\n";

            foreach ($report as $label => $keys) {
                echo "$label:\n- ".implode("\n- ", $keys)."\n";
            }
        }

        if (! empty($untranslated)) {
            echo "\n$path: ".count($untranslated)." value(s) identical to English (check they are cognates or names):\n- "
                .implode("\n- ", $untranslated)."\n";
        }

        $untranslated = [];
    }
}

echo $problems ? "\n$problems problem(s) found.\n" : "\nNo missing, extra, duplicate or placeholder problems.\n";

exit($problems ? 1 : 0);
