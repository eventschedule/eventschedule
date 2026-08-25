<?php

namespace Tests\Unit;

use App\Models\Role;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Covers subdomain romanization for non-Latin names (Role::cleanSubdomain / transliterateToAscii).
 * A personal name like "רותם רם" used to fall back to a random hash; it now transliterates to a
 * readable slug. The AI keys are nulled so GeminiUtils::translate() returns null with no network
 * (sendRequest bails when neither key is set), exercising the translate -> transliterate -> random
 * fall-through exactly as it behaves when Gemini is unavailable.
 */
class RoleSubdomainTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.gemini_key' => null,
            'services.openai.api_key' => null,
        ]);
    }

    public function test_transliterate_to_ascii_romanizes_non_latin(): void
    {
        if (! class_exists(\Transliterator::class)) {
            $this->markTestSkipped('ext-intl not installed');
        }

        $this->assertSame('rwtm-rm', Str::slug(Role::transliterateToAscii('רותם רם')));
        $this->assertSame('brykh-prtyt', Str::slug(Role::transliterateToAscii('בריכה פרטית')));
        $this->assertSame('dong-jing', Str::slug(Role::transliterateToAscii('東京')));
        $this->assertSame('', Role::transliterateToAscii(''));
    }

    public function test_clean_subdomain_romanizes_proper_name_instead_of_randomizing(): void
    {
        if (! class_exists(\Transliterator::class)) {
            $this->markTestSkipped('ext-intl not installed');
        }

        $subdomain = Role::cleanSubdomain('רותם רם');

        $this->assertSame('rwtm-rm', $subdomain);
        $this->assertDoesNotMatchRegularExpression('/^[a-z0-9]{8}$/', $subdomain);
    }

    public function test_clean_subdomain_prefers_fallback_english_without_api(): void
    {
        // The caller-supplied English name resolves the slug with no API and no transliteration.
        $this->assertSame('private-pool', Role::cleanSubdomain('בריכה פרטית', 'Private Pool'));
    }

    public function test_clean_subdomain_keeps_latin_names_unchanged(): void
    {
        $this->assertSame('blue-note', Role::cleanSubdomain('Blue Note'));
    }

    public function test_clean_subdomain_falls_back_to_random_when_unresolvable(): void
    {
        // A single non-Latin letter has no meaningful romanization (<= 2 chars), so the random
        // fallback still applies.
        $this->assertMatchesRegularExpression('/^[a-z0-9]{8}$/', Role::cleanSubdomain('א'));
    }

    /**
     * The reserved list applies on SELFHOST, which is the deployment it mostly exists for.
     *
     * Several entries are there because selfhost serves tenants out of the same path space as the
     * app's own routes, and 'schedule-transfer' was added for a route registered ahead of the
     * /{subdomain} catch-all - yet the check was gated on config('app.hosted'), so it never ran
     * where it was needed. On hosted a reserved name is a DNS subdomain and could not shadow an
     * app-domain route anyway, so the gate was backwards.
     *
     * Asserted with app.hosted BOTH ways: this is a rule about the list, not about the mode.
     */
    public function test_reserved_subdomains_are_refused_in_both_deployment_modes(): void
    {
        foreach ([true, false] as $hosted) {
            config(['app.hosted' => $hosted]);

            foreach (['schedule-transfer', 'docs', 'checkout'] as $reserved) {
                $this->assertMatchesRegularExpression(
                    '/^[a-z0-9]{8}$/',
                    Role::cleanSubdomain($reserved),
                    "'{$reserved}' must not be handed out with app.hosted=".var_export($hosted, true),
                );
            }

            // An ordinary name is untouched either way, so the list has not simply swallowed
            // everything.
            $this->assertSame('blue-note', Role::cleanSubdomain('Blue Note'));
        }
    }
}
