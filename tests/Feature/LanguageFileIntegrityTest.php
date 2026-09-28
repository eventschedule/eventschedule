<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Structural checks on resources/lang, of the kind PHP will not make for you.
 *
 * A duplicate array key is silently legal: the later entry wins and the earlier one becomes dead
 * text. That is exactly how a key added by hand - one that already existed a few lines above -
 * shipped in all twelve locales, with the German file carrying two different translations of the
 * same string and the wrong one winning. Every messages.php carried 25 to 48 of them until they were
 * removed, so there is no allowlist: a duplicate is always new.
 *
 * A translated placeholder is just as silent. Estonian had `:loenda` for `:count`, so the page printed
 * the word and never the number, and Hebrew printed a literal `"name:"`.
 *
 * `php storage/check_translations.php` reports both, plus values still identical to English. That
 * last one is not failed here: German "Status" and French "Description" are correct, so it would need
 * a hand-kept allowlist per locale.
 */
class LanguageFileIntegrityTest extends TestCase
{
    private const FILES = ['messages.php', 'marketing.php', 'accessibility.php'];

    /** @return array<int, string> */
    private function languages(): array
    {
        return array_keys(config('app.supported_languages'));
    }

    public function test_no_language_file_declares_the_same_key_twice(): void
    {
        $offenders = [];

        foreach (self::FILES as $file) {
            foreach ($this->languages() as $lang) {
                $path = resource_path("lang/{$lang}/{$file}");

                if (! file_exists($path)) {
                    continue;
                }

                $seen = [];

                foreach (explode("\n", file_get_contents($path)) as $index => $line) {
                    // Top-level entries only: one indent level, a quoted key, then =>. Nested array
                    // values are indented further and are not part of the same key space.
                    if (! preg_match("~^ {4}'([a-z0-9_.]+)'\s*=>~i", $line, $m)) {
                        continue;
                    }

                    $key = $m[1];

                    if (isset($seen[$key])) {
                        $offenders[] = "{$lang}/{$file}:".($index + 1).": '{$key}' (first declared on line {$seen[$key]})";

                        continue;
                    }

                    $seen[$key] = $index + 1;
                }
            }
        }

        $this->assertSame([], $offenders,
            'Duplicate translation key. PHP keeps the LAST one, so the earlier entry is dead text '
            ."and editing it changes nothing:\n".implode("\n", $offenders));
    }

    public function test_every_translation_keeps_the_english_placeholders(): void
    {
        $mismatches = [];

        foreach (self::FILES as $file) {
            $english = $this->flatten(require resource_path("lang/en/{$file}"));

            foreach ($this->languages() as $lang) {
                if ($lang === 'en') {
                    continue;
                }

                $translations = $this->flatten(require resource_path("lang/{$lang}/{$file}"));

                foreach ($english as $key => $value) {
                    if (! array_key_exists($key, $translations)) {
                        continue;
                    }

                    if ($this->placeholders($value) !== $this->placeholders($translations[$key])) {
                        $mismatches[] = "{$lang}/{$file} {$key}: expected [".implode(', ', $this->placeholders($value))
                            .'] got ['.implode(', ', $this->placeholders($translations[$key])).']';
                    }
                }
            }
        }

        $this->assertSame([], $mismatches, 'A translation renamed, dropped or added a :placeholder, so it '
            ."renders the literal word or loses the value:\n".implode("\n", $mismatches));
    }

    /** @return array<int, string> */
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
