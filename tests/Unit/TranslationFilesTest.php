<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * The two translation defects no rendering test can see.
 *
 * `storage/check_translations.php` used to check only that a key EXISTS, so these shipped for
 * years. Estonian had translated the placeholder names themselves (`:loenda` for `:count`), so
 * the page printed the word and never the number; Hebrew printed a literal `"name:"`. And every
 * messages.php defined dozens of keys twice. PHP keeps the last value, so the earlier line was
 * dead but read as the real one, which is how he carried two different words for 'schedule'.
 *
 * Values still identical to English are reported by the script, not failed here: German "Status"
 * and French "Description" are correct, so that check would need a hand-kept allowlist per locale.
 */
class TranslationFilesTest extends TestCase
{
    private const FILES = ['messages.php', 'marketing.php', 'accessibility.php'];

    public function test_every_translation_keeps_the_english_placeholders(): void
    {
        $mismatches = [];

        foreach (self::FILES as $file) {
            $english = $this->flatten(require lang_path('en/'.$file));

            foreach ($this->locales() as $locale) {
                $translations = $this->flatten(require lang_path($locale.'/'.$file));

                foreach ($english as $key => $value) {
                    if (! array_key_exists($key, $translations)) {
                        continue;
                    }

                    if ($this->placeholders($value) !== $this->placeholders($translations[$key])) {
                        $mismatches[] = "$locale/$file $key: expected [".implode(', ', $this->placeholders($value))
                            .'] got ['.implode(', ', $this->placeholders($translations[$key])).']';
                    }
                }
            }
        }

        $this->assertSame([], $mismatches, 'a translation renamed, dropped or added a :placeholder');
    }

    /** `require` hides a duplicate, so this reads the source text. */
    public function test_no_language_file_defines_a_key_twice(): void
    {
        $duplicates = [];

        foreach (self::FILES as $file) {
            foreach (array_merge(['en'], $this->locales()) as $locale) {
                preg_match_all("/^    '([^']+)' =>/m", file_get_contents(lang_path($locale.'/'.$file)), $matches);

                foreach (array_count_values($matches[1]) as $key => $count) {
                    if ($count > 1) {
                        $duplicates[] = "$locale/$file $key ($count times)";
                    }
                }
            }
        }

        $this->assertSame([], $duplicates, 'a key is defined twice, so only its last value is live');
    }

    /** @return list<string> */
    private function locales(): array
    {
        return array_values(array_filter(
            array_keys(config('app.supported_languages')),
            fn ($locale) => $locale !== 'en'
        ));
    }

    /** @return list<string> */
    private function placeholders(string $value): array
    {
        preg_match_all('/:([a-zA-Z_]+)/', $value, $matches);
        $names = array_values(array_unique($matches[1]));
        sort($names);

        return $names;
    }

    /** @return array<string, string> */
    private function flatten(array $array, string $prefix = ''): array
    {
        $out = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $out += $this->flatten($value, $prefix.$key.'.');
            } else {
                $out[$prefix.$key] = (string) $value;
            }
        }

        return $out;
    }
}
