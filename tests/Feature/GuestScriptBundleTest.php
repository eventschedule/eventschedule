<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Vite as ViteFacade;
use Illuminate\Support\HtmlString;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Which script bundle a page is served (layouts/app-guest, layouts/app).
 *
 * resources/js/app.js carries the Markdown editors (EasyMDE, a third of a megabyte), and it was
 * parsed on every schedule and event page, where nobody edits anything. A guest page that draws
 * no editor is served resources/js/guest.js, the same without them; one that does (the submit
 * and booking forms, the import page) keeps app.js, decided from what the page drew so a new one
 * needs no list. A page given guest.js that then calls window.initHtmlEditors() would break
 * silently, which is what this holds.
 */
class GuestScriptBundleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @var list<string> */
    public static array $entries = [];

    protected function setUp(): void
    {
        parent::setUp();

        self::$entries = [];
        ViteFacade::clearResolvedInstance();
        $this->swap(Vite::class, new class extends Vite
        {
            public function __invoke($entrypoints, $buildDirectory = null)
            {
                foreach ((array) $entrypoints as $entry) {
                    GuestScriptBundleTest::$entries[] = $entry;
                }

                return new HtmlString('');
            }

            public function __call($method, $parameters)
            {
                return '';
            }

            public function __toString()
            {
                return '';
            }
        });
    }

    public function test_a_schedule_page_is_served_the_bundle_without_the_editors(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $this->get('/'.$role->subdomain)->assertOk();

        $this->assertContains('resources/js/guest.js', self::$entries);
        $this->assertNotContains('resources/js/app.js', self::$entries);
    }

    public function test_a_guest_page_that_draws_an_editor_keeps_them(): void
    {
        $curator = $this->createCurator($this->createOwner(), ['accept_requests' => true]);
        $html = $this->actingAs($this->createOwner())->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]))->assertOk()->getContent();

        $this->assertStringContainsString('html-editor', $html, 'the form draws its description editor');
        $this->assertContains('resources/js/app.js', self::$entries);
        $this->assertNotContains('resources/js/guest.js', self::$entries);
    }

    public function test_the_admin_portal_keeps_the_full_bundle(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue');

        $this->actingAs($owner)->get('/dashboard')->assertOk();

        $this->assertContains('resources/js/app.js', self::$entries);
        $this->assertNotContains('resources/js/guest.js', self::$entries);
    }
}
