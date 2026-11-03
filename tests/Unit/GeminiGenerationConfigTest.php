<?php

namespace Tests\Unit;

use App\Utils\GeminiUtils;
use Tests\TestCase;

/**
 * gemini-2.5-flash is redirected to 3.5 Flash-Lite on 2026-12-01. From Gemini 3 a model ignores
 * temperature and is steered by a thinking level, which 2.x refuses with a 400, so what is sent
 * depends on the model it goes to.
 */
class GeminiGenerationConfigTest extends TestCase
{
    public function test_the_default_model_is_one_that_is_not_being_retired(): void
    {
        $this->assertSame('gemini-3.5-flash', GeminiUtils::DEFAULT_MODEL);
        $this->assertNotSame('gemini-2.5-flash', config('services.google.gemini_content_model'));
        $this->assertNotSame('gemini-2.5-flash', config('services.google.gemini_translation_model'));
    }

    public function test_a_2x_model_gets_exactly_what_the_caller_asked_for(): void
    {
        $asked = ['temperature' => 0, 'response_schema' => ['type' => 'OBJECT']];

        $this->assertSame($asked, GeminiUtils::generationConfigFor('gemini-2.5-flash', $asked, 'translation'));
        $this->assertSame($asked, GeminiUtils::generationConfigFor('gpt-5-mini', $asked, 'content'), 'a name that is not gemini-<n> is left alone');
    }

    public function test_a_3x_model_drops_sampling_and_thinks_at_the_purposes_level(): void
    {
        $sent = GeminiUtils::generationConfigFor('gemini-3.5-flash', ['temperature' => 0, 'topP' => 0.9, 'top_k' => 5, 'response_schema' => ['type' => 'OBJECT']], 'translation');

        $this->assertSame(['response_schema' => ['type' => 'OBJECT'], 'thinkingConfig' => ['thinkingLevel' => 'low']], $sent);
        $this->assertSame('low', GeminiUtils::generationConfigFor('gemini-3-flash-preview', [], 'content')['thinkingConfig']['thinkingLevel']);
        $this->assertSame('low', GeminiUtils::generationConfigFor('gemini-3.5-flash', [], 'something-new')['thinkingConfig']['thinkingLevel'], 'an unknown purpose thinks as content does');
    }

    public function test_a_caller_can_choose_the_level_or_leave_it_to_the_model(): void
    {
        $this->assertSame('high', GeminiUtils::generationConfigFor('gemini-3.5-flash', [], 'content', 'high')['thinkingConfig']['thinkingLevel']);
        $this->assertSame([], GeminiUtils::generationConfigFor('gemini-3.5-flash', ['temperature' => 0.7], 'content', GeminiUtils::THINKING_MODEL_DEFAULT));

        $own = ['thinkingConfig' => ['thinkingLevel' => 'medium']];
        $this->assertSame($own, GeminiUtils::generationConfigFor('gemini-3.5-flash', $own, 'content'));
    }
}
