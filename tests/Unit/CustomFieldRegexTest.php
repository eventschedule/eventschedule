<?php

namespace Tests\Unit;

use App\Utils\CustomFieldUtils;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Schedule owners author these patterns by hand, so the compiler has to be forgiving about what it
 * accepts and strict about what it lets through to a validation rule.
 */
class CustomFieldRegexTest extends TestCase
{
    public function test_an_empty_pattern_compiles_to_null(): void
    {
        $this->assertNull(CustomFieldUtils::compilePattern(null));
        $this->assertNull(CustomFieldUtils::compilePattern(''));
        $this->assertNull(CustomFieldUtils::compilePattern('   '));
    }

    public function test_a_pattern_is_anchored_so_a_partial_match_fails(): void
    {
        $pattern = CustomFieldUtils::compilePattern('[A-Z]{3}');

        $this->assertSame(1, preg_match($pattern, 'ABC'));
        // Without the anchors this substring would match.
        $this->assertSame(0, preg_match($pattern, 'xABCx'));
    }

    public function test_alternation_is_grouped_so_the_anchors_apply_to_every_branch(): void
    {
        // A naive '/^'.$p.'$/' would anchor only the first and last branch here.
        $pattern = CustomFieldUtils::compilePattern('cat|dog');

        $this->assertSame(1, preg_match($pattern, 'dog'));
        $this->assertSame(0, preg_match($pattern, 'hotdog'));
    }

    public function test_a_bare_delimiter_is_escaped_and_an_existing_escape_is_preserved(): void
    {
        $bare = CustomFieldUtils::compilePattern('https?://\S+');
        $this->assertNotNull($bare);
        $this->assertSame(1, preg_match($bare, 'https://example.com'));

        // A pattern that already escapes the delimiter must not be double-escaped into a literal
        // backslash followed by the delimiter.
        $escaped = CustomFieldUtils::compilePattern('a\/b');
        $this->assertNotNull($escaped);
        $this->assertSame(1, preg_match($escaped, 'a/b'));
    }

    public function test_an_uncompilable_pattern_returns_null(): void
    {
        $this->assertNull(CustomFieldUtils::compilePattern('[unterminated'));
        $this->assertNull(CustomFieldUtils::compilePattern('a{2,1}'));
        $this->assertNull(CustomFieldUtils::compilePattern('*'));
    }

    public function test_a_trailing_modifier_cannot_be_smuggled_in(): void
    {
        // Escaping the delimiter means "abc/i" is read as the literal text abc/i, not as pattern
        // "abc" with the case-insensitive flag - an owner cannot append their own modifiers.
        $pattern = CustomFieldUtils::compilePattern('abc/i');

        $this->assertSame(1, preg_match($pattern, 'abc/i'));
        $this->assertSame(0, preg_match($pattern, 'ABC'));
        $this->assertSame(0, preg_match($pattern, 'abc'));
    }

    public function test_is_valid_pattern_accepts_empty_and_rejects_broken(): void
    {
        $this->assertTrue(CustomFieldUtils::isValidPattern(''));
        $this->assertTrue(CustomFieldUtils::isValidPattern('[0-9]+'));
        $this->assertFalse(CustomFieldUtils::isValidPattern('[unterminated'));
    }

    public function test_every_shipped_preset_compiles(): void
    {
        foreach (CustomFieldUtils::regexPresets() as $label => $pattern) {
            $this->assertNotNull(
                CustomFieldUtils::compilePattern($pattern),
                "Preset \"{$label}\" does not compile"
            );
        }
    }

    public function test_the_shipped_presets_accept_and_reject_the_obvious_cases(): void
    {
        $presets = array_values(CustomFieldUtils::regexPresets());
        [$email, $phone, $url, $digits, $alphanumeric] = $presets;

        $this->assertSame(1, preg_match(CustomFieldUtils::compilePattern($email), 'sam@example.com'));
        $this->assertSame(0, preg_match(CustomFieldUtils::compilePattern($email), 'not an email'));
        $this->assertSame(1, preg_match(CustomFieldUtils::compilePattern($phone), '+49 561 1234567'));
        $this->assertSame(0, preg_match(CustomFieldUtils::compilePattern($phone), 'call me'));
        $this->assertSame(1, preg_match(CustomFieldUtils::compilePattern($url), 'https://example.com/x'));
        $this->assertSame(0, preg_match(CustomFieldUtils::compilePattern($url), 'example.com'));
        $this->assertSame(1, preg_match(CustomFieldUtils::compilePattern($digits), '12345'));
        $this->assertSame(0, preg_match(CustomFieldUtils::compilePattern($digits), '12a'));
        $this->assertSame(1, preg_match(CustomFieldUtils::compilePattern($alphanumeric), 'Room 12'));
        $this->assertSame(0, preg_match(CustomFieldUtils::compilePattern($alphanumeric), 'Room-12'));
    }

    /**
     * Laravel's ValidationRuleParser special-cases the regex rule and does not split its parameter
     * on commas. A quantifier like {2,4} would otherwise be torn in half.
     */
    public function test_a_pattern_containing_a_comma_survives_the_rule_parser(): void
    {
        $rule = 'regex:'.CustomFieldUtils::compilePattern('[A-Z]{2,4}');

        $this->assertTrue(Validator::make(['code' => 'ABC'], ['code' => [$rule]])->passes());
        $this->assertFalse(Validator::make(['code' => 'A'], ['code' => [$rule]])->fails() === false);
        $this->assertTrue(Validator::make(['code' => 'ABCDE'], ['code' => [$rule]])->fails());
    }

    /**
     * A catastrophic pattern must not hang the request: PCRE gives up at pcre.backtrack_limit and
     * preg_match returns false, which Laravel treats as a failed field.
     */
    public function test_a_backtracking_pattern_fails_rather_than_hangs(): void
    {
        $rule = 'regex:'.CustomFieldUtils::compilePattern('(a+)+b');

        $start = microtime(true);
        $fails = Validator::make(['code' => str_repeat('a', 40).'c'], ['code' => [$rule]])->fails();
        $elapsed = microtime(true) - $start;

        $this->assertTrue($fails);
        $this->assertLessThan(5, $elapsed, 'Pattern evaluation should be bounded by pcre.backtrack_limit');
    }
}
