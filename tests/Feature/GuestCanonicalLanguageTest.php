<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Every URL the sitemap submits has to be the canonical one.
 *
 * A translation-enabled schedule used to append ?lang=<primary> to the canonical of its clean URL,
 * while sitemap-events-1.xml submits only clean URLs (zero of its 4,314 entries carried ?lang=).
 * So every submitted URL told Google "do not index me, index my ?lang= twin" - the shape of the
 * property's ~99k "Alternate page with proper canonical tag" rows, and a doubling of the crawlable
 * URL space for no benefit.
 *
 * The primary language now lives on the clean URL and only the alternate language carries ?lang=.
 */
class GuestCanonicalLanguageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Schedule whose own language is English and which also publishes a Spanish translation. */
    private function translatedRole()
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Translated Venue',
            'language_code' => 'en',
            'translation_language_code' => 'es',
        ]);
    }

    private function canonicalOf(string $url): ?string
    {
        $content = $this->get($url)->assertOk()->getContent();

        return preg_match('/<link rel="canonical" href="([^"]*)"/', $content, $m) ? $m[1] : null;
    }

    public function test_clean_schedule_url_is_canonical_for_a_translated_schedule(): void
    {
        $role = $this->translatedRole();

        // The invariant: what the sitemap submits is what is canonical.
        $this->assertSame($role->getCanonicalUrl(), $this->canonicalOf('/'.$role->subdomain));
    }

    public function test_clean_event_url_is_canonical_for_a_translated_schedule(): void
    {
        $role = $this->translatedRole();
        $event = $this->createEvent($role, ['name' => 'Translated Event']);

        $canonical = $this->canonicalOf($this->guestEventUrl($role, $event));

        $this->assertNotNull($canonical);
        $this->assertStringNotContainsString('lang=', $canonical);
    }

    public function test_primary_language_query_consolidates_onto_the_clean_url(): void
    {
        $role = $this->translatedRole();

        // ?lang=<primary> never renders: viewGuest() strips the param and redirects, which
        // consolidates harder than a canonical would. Pinned here because it is the other half of
        // the guarantee - the clean URL is the only indexable home for the primary language.
        $this->get('/'.$role->subdomain.'?lang=en')
            ->assertRedirect(url('/'.$role->subdomain));
    }

    public function test_alternate_language_url_is_self_canonical(): void
    {
        $role = $this->translatedRole();

        // The alternate language is a genuinely different page, so it keeps its own canonical.
        $this->assertSame(
            $role->getCanonicalUrl().'?lang=es',
            $this->canonicalOf('/'.$role->subdomain.'?lang=es')
        );
    }

    public function test_hreflang_points_the_primary_and_x_default_at_the_clean_url(): void
    {
        $role = $this->translatedRole();
        $base = $role->getCanonicalUrl();

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('hreflang="es" href="'.$base.'?lang=es"', $content);
        $this->assertStringContainsString('hreflang="en" href="'.$base.'"', $content);
        $this->assertStringContainsString('hreflang="x-default" href="'.$base.'"', $content);
    }

    public function test_untranslated_schedule_is_unaffected(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['language_code' => 'en']);

        $this->assertSame($role->getCanonicalUrl(), $this->canonicalOf('/'.$role->subdomain));
        $this->assertStringNotContainsString('hreflang', $this->get('/'.$role->subdomain)->getContent());
    }

    public function test_array_lang_query_param_does_not_break_the_page(): void
    {
        $role = $this->translatedRole();

        // ?lang[]=x hands is_valid_language_code() an array against its ?string signature. The
        // suffix is computed on every guest render now, so this has to stay guarded.
        $this->assertSame(
            $role->getCanonicalUrl(),
            $this->canonicalOf('/'.$role->subdomain.'?lang[]=es')
        );
    }
}
