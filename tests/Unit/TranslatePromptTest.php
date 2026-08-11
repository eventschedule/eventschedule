<?php

namespace Tests\Unit;

use App\Utils\GeminiUtils;
use Tests\TestCase;

/**
 * The translation prompt, assembled without a network call.
 *
 * The prompt is the root-cause half of the fix: the old one-liner never said "do not explain" and
 * never said what kind of field it was looking at, so a bare venue name reads much like a request
 * to describe the venue. These assertions pin the parts that matter and the interpolation traps.
 */
class TranslatePromptTest extends TestCase
{
    private function build(string $text, string $from = 'nl', string $to = 'en', array $glossary = [], array $options = []): string
    {
        return GeminiUtils::buildTranslatePrompt($text, $from, $to, $glossary, $options);
    }

    public function test_the_rules_that_stop_the_model_answering_from_world_knowledge_are_present(): void
    {
        $prompt = $this->build('Vakantiepark Sandur');

        $this->assertStringContainsString('Never explain', $prompt);
        $this->assertStringContainsString('Never add information that is not in the text below', $prompt);
        $this->assertStringContainsString('Never expand', $prompt);
        $this->assertStringContainsString('Never describe what it is', $prompt);
    }

    /** The parser's canonical key has to be the one the prompt names, or the two drift apart. */
    public function test_the_prompt_names_the_key_the_parser_reads_first(): void
    {
        $this->assertStringContainsString('{"translation": "..."}', $this->build('Vakantiepark Sandur'));
    }

    public function test_language_names_are_used_not_iso_codes(): void
    {
        $prompt = $this->build('Vakantiepark Sandur', 'nl', 'en');

        $this->assertStringContainsString('from Dutch into English', $prompt);
        $this->assertStringNotContainsString('from nl into en', $prompt);
    }

    /** 'auto' is a real value at two call sites, and "translate from auto" reads as nonsense. */
    public function test_an_unknown_source_language_is_described_not_named(): void
    {
        $this->assertStringContainsString('from its original language into English', $this->build('שלום', 'auto'));
    }

    public function test_the_kind_hint_describes_only_the_field_being_translated(): void
    {
        $name = $this->build('Vakantiepark Sandur', options: ['kind' => 'name']);
        $this->assertStringContainsString('The text is a NAME', $name);
        $this->assertStringNotContainsString('written in markdown', $name);

        $body = $this->build('Een lang verhaal.', options: ['kind' => 'body']);
        $this->assertStringContainsString('written in markdown', $body);
        $this->assertStringNotContainsString('The text is a NAME', $body);

        $short = $this->build('Amsterdam', options: ['kind' => 'short']);
        $this->assertStringContainsString('short field value', $short);

        $none = $this->build('Vakantiepark Sandur');
        $this->assertStringNotContainsString('The text is a', $none);

        $unknown = $this->build('Vakantiepark Sandur', options: ['kind' => 'nonsense']);
        $this->assertStringNotContainsString('The text is a', $unknown, 'an unknown kind degrades, it does not break');
    }

    /**
     * The length budget is derived from the SOURCE, not from the column. Handing a 19-character
     * venue name a 255-character allowance invites it to use them.
     */
    public function test_the_length_hint_is_derived_from_the_source_not_the_column(): void
    {
        $prompt = $this->build('Vakantiepark Sandur', options: ['max_length' => 255]);

        $this->assertStringContainsString('at most 60 characters', $prompt);
        $this->assertStringNotContainsString('at most 255 characters', $prompt);
    }

    public function test_a_long_source_is_still_capped_by_the_column(): void
    {
        $prompt = $this->build(str_repeat('a', 250), options: ['max_length' => 255]);

        $this->assertStringContainsString('at most 255 characters', $prompt);
    }

    /** Descriptions target TEXT columns, so they get no budget and no noise about one. */
    public function test_no_length_sentence_without_a_ceiling(): void
    {
        $this->assertStringNotContainsString('at most', $this->build('Een lang verhaal.'));
    }

    public function test_the_glossary_renders_without_mangling_the_layout(): void
    {
        $plain = $this->build('Vakantiepark Sandur');
        $this->assertStringNotContainsString('  ', $plain, 'no double spaces where a fragment was left empty');
        $this->assertStringNotContainsString("\n ", $plain, 'no line starting with a stray space');

        $withGlossary = $this->build('Vakantiepark Sandur', glossary: ['Sandur' => 'Sandur']);
        $this->assertStringContainsString('Use these exact translations', $withGlossary);
        $this->assertStringContainsString('- "Sandur" => "Sandur"', $withGlossary);
        $this->assertStringNotContainsString("\n ", $withGlossary);
    }

    /**
     * str_replace applies its pairs in order, so whatever is substituted first is rescanned by the
     * later passes. The glossary carries user-authored schedule names, so it has to go last or a
     * name containing a placeholder gets rewritten.
     */
    public function test_a_glossary_term_containing_a_placeholder_survives_verbatim(): void
    {
        $prompt = $this->build('x', glossary: ['The :to Club' => 'The :from Bar']);

        $this->assertStringContainsString('- "The :to Club" => "The :from Bar"', $prompt);
    }

    public function test_the_source_text_is_last_and_untouched(): void
    {
        $this->assertStringEndsWith("\nVakantiepark Sandur", $this->build('Vakantiepark Sandur'));
    }
}
