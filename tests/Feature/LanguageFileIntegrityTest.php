<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Structural checks on resources/lang, of the kind PHP will not make for you.
 *
 * A duplicate array key is silently legal: the later entry wins and the earlier one becomes dead
 * text. That is exactly how a key added by hand - one that already existed a few lines above -
 * shipped in all twelve locales, with the German file carrying two different translations of the
 * same string and the wrong one winning. check_translations.php compares key SETS across
 * languages, so it cannot see a key duplicated within one file.
 */
class LanguageFileIntegrityTest extends TestCase
{
    /**
     * Keys that are ALREADY declared twice somewhere, recorded so this test fails only on new
     * ones. 51 of them, pre-dating this test - shrink the list, never grow it. Each is dead text
     * in whichever file carries it: PHP keeps the last entry, so editing the first changes
     * nothing, and a translator fixing the wrong one has no way to tell.
     */
    private const KNOWN_DUPLICATES = [
        'accent_color', 'add_image', 'add_link', 'all_schedules',
        'attendee', 'background_color', 'clear', 'content',
        'copy_text', 'create_schedule', 'created_schedule', 'daily',
        'date', 'deleted_curator', 'deleted_schedule', 'embed_schedule',
        'font_family', 'general', 'header_image', 'layout',
        'loading', 'matched_venue', 'monthly', 'name_required',
        'new_schedule', 'no_events_found', 'phone', 'public',
        'register', 'remove', 'reset_password', 'schedule',
        'search', 'send_test_email', 'sending', 'solid',
        'source', 'stripe', 'stripe_connected', 'submit',
        'subscribe', 'talent', 'test_email_sent', 'testing',
        'unlisted', 'unsubscribed', 'updated_curator', 'updated_schedule',
        'verify', 'view_event', 'weekly',
    ];

    /** @return array<int, string> */
    private function languages(): array
    {
        return array_keys(config('app.supported_languages'));
    }

    public function test_no_language_file_declares_the_same_key_twice(): void
    {
        $offenders = [];

        foreach ($this->languages() as $lang) {
            $path = resource_path("lang/{$lang}/messages.php");

            if (! file_exists($path)) {
                continue;
            }

            $seen = [];

            foreach (explode("\n", file_get_contents($path)) as $index => $line) {
                // Top-level entries only: one indent level, a quoted key, then =>. Nested array
                // values are indented further and are not part of the same key space.
                if (! preg_match("~^ {4}'([a-z0-9_]+)'\s*=>~i", $line, $m)) {
                    continue;
                }

                $key = $m[1];

                if (isset($seen[$key])) {
                    if (! in_array($key, self::KNOWN_DUPLICATES, true)) {
                        $offenders[] = "{$lang}/messages.php:".($index + 1).": '{$key}' (first declared on line {$seen[$key]})";
                    }

                    continue;
                }

                $seen[$key] = $index + 1;
            }
        }

        $this->assertSame([], $offenders,
            'Duplicate translation key. PHP keeps the LAST one, so the earlier entry is dead text '
            ."and editing it changes nothing:\n".implode("\n", $offenders));
    }
}
