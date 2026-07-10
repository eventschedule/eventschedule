<?php

namespace Tests\Feature\Characterization;

use App\Rules\NoFakeEmail;
use Tests\TestCase;

/**
 * Characterizes app/Rules/NoFakeEmail.php ahead of the O2 blocklist-to-data-
 * file move (REFACTOR_PLAN.md): known-blocked domains rejected, real domains
 * pass, the substring-matching semantics, and the exact blocklist size.
 */
class NoFakeEmailCharacterizationTest extends TestCase
{
    public function test_known_disposable_domains_are_rejected(): void
    {
        $rule = new NoFakeEmail;

        foreach ([
            'user@padvn.com',        // first entry
            'user@zzz.com',          // last entry
            'user@sharklasers.com',
            'user@yandex.com',       // deliberate entry - pinned
            'user@0815.ru',
        ] as $email) {
            $this->assertFalse($rule->passes('email', $email), "$email should be blocked");
        }
    }

    public function test_example_com_is_rejected_before_the_list(): void
    {
        $rule = new NoFakeEmail;

        $this->assertFalse($rule->passes('email', 'user@example.com'));
    }

    public function test_real_domains_pass(): void
    {
        $rule = new NoFakeEmail;

        foreach ([
            'user@gmail.com',
            'user@outlook.com',
            'user@company.co.il',
            // example.org/net are handled by Sale::excludeTestEmails, NOT here.
            'user@example.org',
        ] as $email) {
            $this->assertTrue($rule->passes('email', $email), "$email should pass");
        }
    }

    public function test_matching_is_substring_based_not_exact_domain(): void
    {
        $rule = new NoFakeEmail;

        // The check is strpos($value, "@$domain") - a blocked domain appearing
        // mid-hostname is still rejected. The O2 move must not "fix" this to
        // exact-domain matching.
        $this->assertFalse($rule->passes('email', 'user@0815.ru.evil.com'));

        // But a superstring domain does not match the "@domain" needle.
        $this->assertTrue($rule->passes('email', 'user@notzzz.com'));
    }

    public function test_blocklist_size_is_pinned(): void
    {
        // Derived from the source until O2 moves the list to a data file
        // (which must load exactly this many entries).
        $source = file_get_contents(app_path('Rules/NoFakeEmail.php'));
        $this->assertSame(1, preg_match('/\$fakeDomains = \[(.*?)\];/s', $source, $match), 'blocklist array not found in source');
        preg_match_all("/'([^']+)'/", $match[1], $domains);

        $this->assertCount(3632, $domains[1]);
        $this->assertSame('padvn.com', $domains[1][0]);
        $this->assertSame('zzz.com', end($domains[1]));
    }

    public function test_validation_message_is_pinned(): void
    {
        $this->assertSame(
            'The :attribute field must be a permanent email address.',
            (new NoFakeEmail)->message()
        );
    }
}
