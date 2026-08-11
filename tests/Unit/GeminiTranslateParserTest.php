<?php

namespace Tests\Unit;

use App\Utils\GeminiUtils;
use PHPUnit\Framework\TestCase;

/**
 * Reading a translation out of whatever the provider actually returned.
 *
 * The prompt asks for a bare JSON string and Gemini can honour it, but OpenAI's response_format is
 * json_object, which forbids one - so on that path the model is forced to wrap the answer in a key
 * of its own choosing. That is why this is a search rather than a lookup, and why the old code had
 * a catch-all that imploded every string in the object together.
 *
 * Pure by design (language codes are passed in, not read from config) so it runs with no network
 * and no framework boot.
 */
class GeminiTranslateParserTest extends TestCase
{
    private function extract(mixed $response, string $to = 'en', array $languageCodes = []): ?string
    {
        return GeminiUtils::extractTranslation($response, $to, $languageCodes);
    }

    public function test_a_bare_string_is_the_translation(): void
    {
        $this->assertSame('Residential Park Sandur', $this->extract('Residential Park Sandur'));
        $this->assertSame('padded', $this->extract('   padded   '));
    }

    /** "0" is falsy in PHP but is a perfectly good translation of "0". */
    public function test_a_falsy_but_real_translation_survives(): void
    {
        $this->assertSame('0', $this->extract('0'));
        $this->assertSame('0', $this->extract(['translation' => '0']));
    }

    public function test_nothing_usable_is_null(): void
    {
        $this->assertNull($this->extract(''));
        $this->assertNull($this->extract('    '));
        $this->assertNull($this->extract([]));
        $this->assertNull($this->extract(null));
        $this->assertNull($this->extract(42));
        $this->assertNull($this->extract(true));
    }

    public function test_the_canonical_key_is_read(): void
    {
        $this->assertSame('x', $this->extract(['translation' => 'x']));
    }

    /**
     * The target language is per-schedule (roles.translation_language_code), but the old code
     * looked for a hardcoded 'en' key, so a French schedule could only ever be read by accident.
     */
    public function test_the_target_language_key_is_read_whatever_the_target_is(): void
    {
        $this->assertSame('x', $this->extract(['en' => 'x'], 'en'));
        $this->assertSame('x', $this->extract(['fr' => 'x'], 'fr'));
    }

    /**
     * The precedence regression. The old code assigned from 'translation' and then let an 'en' key
     * overwrite it unconditionally, so the canonical key lost to the incidental one.
     */
    public function test_the_canonical_key_beats_the_language_key(): void
    {
        $this->assertSame('x', $this->extract(['translation' => 'x', 'en' => 'y'], 'en'));
    }

    public function test_commentary_alongside_a_canonical_key_is_ignored_not_glued_on(): void
    {
        $this->assertSame('x', $this->extract(['translation' => 'x', 'note' => 'y']));
    }

    /**
     * The incident shape. Two prose strings and no key we recognise: the old code returned them
     * space-joined, which is indistinguishable downstream from a real translation. Null is the
     * honest answer, and every caller already treats it as "leave the column alone".
     */
    public function test_two_unlabelled_strings_are_refused_rather_than_imploded(): void
    {
        $glued = $this->extract([
            'name' => 'Residential Park Sandur',
            'info' => 'is a quiet park in Drenthe with 308 homes.',
        ]);

        $this->assertNull($glued);
    }

    /** The reason the catch-all was narrowed rather than deleted: OpenAI-style wrappers still work. */
    public function test_a_single_unlabelled_string_is_still_read(): void
    {
        $this->assertSame('x', $this->extract(['translated_text' => 'x']));
        $this->assertSame('x', $this->extract(['some_unexpected_key' => 'x', 'confidence' => 0.9]));
    }

    public function test_one_level_of_nesting_resolves_but_deeper_does_not(): void
    {
        $this->assertSame('x', $this->extract(['translation' => ['en' => 'x']], 'en'));
        $this->assertNull($this->extract(['translation' => ['a' => ['b' => ['c' => 'x']]]]));
    }

    /**
     * A response keyed by a language that is not the target is the WRONG language, not a wrapper.
     * Writing it would silently store Dutch in the French column.
     */
    public function test_a_wrong_language_key_is_refused(): void
    {
        $this->assertNull($this->extract(['nl' => 'Vakantiepark Sandur'], 'fr', ['nl', 'en', 'fr']));
        $this->assertSame('Vakantiepark Sandur', $this->extract(['nl' => 'Vakantiepark Sandur'], 'nl', ['nl', 'en', 'fr']));
    }
}
