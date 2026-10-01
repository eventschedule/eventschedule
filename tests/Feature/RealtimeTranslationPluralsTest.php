<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The /admin/realtime count phrases are rendered with trans_choice() at counts that include 0.
 *
 * Interval syntax ({1}|[2,*]) once left 0 uncovered, so Laravel fell back to a locale form that was
 * wrong there (Arabic printed "one visitor" at 0, Russian a singular), and it cannot express the
 * Russian, Romanian or Arabic rules at all (21, 103). Those three locales now use plain plural forms;
 * the rest carry an explicit {0}.
 */
class RealtimeTranslationPluralsTest extends TestCase
{
    private const KEYS = ['realtime_visitors_count', 'realtime_views_count', 'realtime_signed_in_count', 'realtime_anonymous_count'];

    public function test_every_locale_shows_the_number_at_zero_and_beyond(): void
    {
        foreach (array_keys(config('app.supported_languages')) as $locale) {
            app()->setLocale($locale);

            foreach (self::KEYS as $key) {
                foreach ([0, 5, 21, 103] as $count) {
                    $text = trans_choice('messages.'.$key, $count, ['count' => $count]);

                    $this->assertStringContainsString((string) $count, $text, "{$locale} {$key} at {$count}: {$text}");
                    $this->assertStringNotContainsString('|', $text, "{$locale} {$key} at {$count} leaked plural syntax");
                }
            }
        }
    }

    public function test_russian_romanian_and_arabic_follow_their_own_rules_past_twenty(): void
    {
        // Each pair must use the same grammatical form, so swapping the number makes them equal.
        $sameForm = [
            'ru' => [[1, 21], [2, 22], [5, 25], [5, 111]],
            'ro' => [[3, 103], [20, 120]],
            'ar' => [[3, 103], [11, 111]],
        ];

        foreach ($sameForm as $locale => $pairs) {
            app()->setLocale($locale);

            foreach (['realtime_visitors_count', 'realtime_views_count'] as $key) {
                foreach ($pairs as [$small, $large]) {
                    $expected = str_replace((string) $small, (string) $large, trans_choice('messages.'.$key, $small, ['count' => $small]));

                    $this->assertSame($expected, trans_choice('messages.'.$key, $large, ['count' => $large]), "{$locale} {$key}: {$small} vs {$large}");
                }
            }
        }
    }
}
