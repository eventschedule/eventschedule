<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's website, social links, payment links and sponsor links are printed into href on
 * the pages other people open, and none of them is validated as a link: the website is a string,
 * the link lists are JSON, and sponsor urls are client-built JSON nobody checks. A stored
 * javascript: value ran on a visitor's click - on a tenant subdomain, which on hosted shares the
 * session cookie's site with the app, or in the app itself for the pages below.
 *
 * Every such href goes through UrlUtils::safeHref(). A value with no safe link renders as text,
 * or its icon is left out, and the text on the page does not change.
 */
class OwnerSuppliedLinksTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** An href whose value is javascript:, in any case and behind any leading whitespace. */
    private const SCRIPT_HREF = '/href\s*=\s*["\']\s*javascript:/i';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.backend' => null]);
    }

    private function scheduleWithScriptLinks(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', array_merge([
            'name' => 'Blue Room',
            'website' => 'javascript:alert(1)',
            'social_links' => json_encode([
                ['name' => 'Scripted', 'url' => 'javascript:alert(2)'],
                ['name' => 'Facebook', 'url' => 'https://facebook.com/bluetest'],
            ]),
            'payment_links' => json_encode([
                ['name' => 'Scripted', 'url' => 'javascript:alert(3)'],
                ['name' => 'PayPal', 'url' => 'https://paypal.me/bluetest'],
            ]),
            'sponsor_logos' => json_encode([
                ['name' => 'Scripted Sponsor', 'url' => 'javascript:alert(4)', 'logo' => ''],
                ['name' => 'Good Sponsor', 'url' => 'https://sponsor.example.com', 'logo' => ''],
            ]),
        ], $attrs));
    }

    private function assertSafeLinks(string $html, Role $role, string $where): void
    {
        $this->assertDoesNotMatchRegularExpression(self::SCRIPT_HREF, $html, $where);

        // The web links are still links, and the scripted social link left no icon behind: its
        // short address would only answer as an unknown one.
        $this->assertStringContainsString('href="'.$role->getGuestUrl().'/facebook"', $html, $where);
        $this->assertStringContainsString('href="https://paypal.me/bluetest"', $html, $where);
        $this->assertStringNotContainsString('javascriptalert2', $html, $where);
    }

    public function test_the_banner_header_and_the_sponsors_link_no_script(): void
    {
        $role = $this->scheduleWithScriptLinks(['header_style' => 'banner']);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertSafeLinks($html, $role, 'banner header');
        $this->assertStringContainsString('href="https://sponsor.example.com"', $html);
        $this->assertStringContainsString('Scripted Sponsor', $html, 'the sponsor is still shown, unlinked');
    }

    public function test_the_compact_header_links_no_script(): void
    {
        $role = $this->scheduleWithScriptLinks(['header_style' => 'compact']);

        $this->assertSafeLinks($this->get('/'.$role->subdomain)->assertOk()->getContent(), $role, 'compact header');
    }

    /** A scheme-less website used to be a link relative to the page; now it is the site. */
    public function test_a_scheme_less_website_links_to_the_site(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['website' => 'www.blueroom-example.com']);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('href="https://www.blueroom-example.com"', $html);
    }

    /** The venue card on an event page: its website reads as text, its scripted icon is gone. */
    public function test_the_event_pages_venue_card_links_no_script(): void
    {
        $venue = $this->scheduleWithScriptLinks();
        $event = $this->createEvent($venue, ['name' => 'Harbour Show', 'creator_role_id' => $venue->id]);

        $html = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression(self::SCRIPT_HREF, $html);
        $this->assertStringContainsString('href="'.$venue->getGuestUrl().'/facebook"', $html);
        $this->assertStringNotContainsString('javascriptalert2', $html);
        $this->assertStringContainsString('javascript:alert(1)</span>', $html, 'the website still reads as it was typed');
    }

    /**
     * The short-link resolver answers a scripted link's slug the way it answers an unknown one,
     * rather than redirecting to it: by its suggested name, by the platform its "host" names, or
     * by a custom slug. A scheme-less link redirects to its https:// form.
     */
    public function test_a_short_link_never_redirects_to_script(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $role->social_links = json_encode([
            ['name' => 'Scripted', 'url' => 'javascript:alert(2)'],
            ['name' => 'Disguised', 'url' => 'javascript://facebook.com/%0Aalert(3)'],
            ['name' => 'Custom', 'url' => 'javascript:alert(4)', 'slug' => 'tickets'],
            ['name' => 'Instagram', 'url' => 'instagram.com/bluetest'],
        ]);
        $role->save();

        $this->get('/'.$role->subdomain.'/javascriptalert2')->assertNotFound();
        $this->get('/'.$role->subdomain.'/facebook')->assertNotFound();
        $this->get('/'.$role->subdomain.'/tickets')->assertNotFound();
        $this->get('/'.$role->subdomain.'/instagram')->assertRedirect('https://instagram.com/bluetest');
    }

    /** Buyers read an event's own terms link on their ticket, on the app's host. */
    public function test_the_ticket_page_links_no_scripted_terms(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['terms_url' => 'javascript:alert(5)']);
        $sale = $this->createSale($event, $role);
        $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);

        $html = $this->get($url)->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression(self::SCRIPT_HREF, $html);
        $this->assertStringContainsString('javascript:alert(5)</span>', $html, 'the terms still read as typed');

        DB::table('events')->where('id', $event->id)->update(['terms_url' => 'https://blueroom-example.com/terms']);
        $this->assertStringContainsString('href="https://blueroom-example.com/terms"', $this->get($url)->assertOk()->getContent());
    }

    /**
     * The admin portal shows websites other people typed too: a followed schedule's on the
     * Following list, and a schedule's own to every one of its members. That is the app's origin.
     */
    public function test_the_admin_portal_links_no_scripted_website(): void
    {
        $follower = $this->createOwner();
        $followed = $this->scheduleWithScriptLinks();
        $this->followRole($follower, $followed);

        $following = $this->actingAs($follower)->get(route('following'))->assertOk()->getContent();
        $this->assertStringContainsString('Blue Room', $following, 'the followed schedule is listed');
        $this->assertDoesNotMatchRegularExpression(self::SCRIPT_HREF, $following);

        $schedule = $this->actingAs($followed->user)->get(route('role.view_admin', ['subdomain' => $followed->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression(self::SCRIPT_HREF, $schedule);
        $this->assertStringContainsString('javascript:alert(1)', $schedule, 'the website still reads as typed');
    }

    /** The newsletter's sponsor block, which the builder also previews inside the app. */
    public function test_the_newsletter_sponsor_block_links_no_script(): void
    {
        $html = view('emails.newsletter_blocks._sponsors', [
            'block' => ['data' => ['resolvedSponsors' => [
                ['name' => 'Scripted Sponsor', 'display_name' => 'Scripted Sponsor', 'url' => 'javascript:alert(6)', 'logo_url' => 'https://cdn.example.com/a.png'],
                ['name' => 'Good Sponsor', 'display_name' => 'Good Sponsor', 'url' => 'https://sponsor.example.com', 'logo_url' => ''],
            ], 'sponsorTitle' => 'Sponsors']],
            'style' => ['fontFamily' => 'Arial', 'textColor' => '#111111', 'buttonRadius' => 'rounded'],
        ])->render();

        $this->assertDoesNotMatchRegularExpression(self::SCRIPT_HREF, $html);
        $this->assertStringContainsString('Scripted Sponsor', $html);
        $this->assertStringContainsString('href="https://sponsor.example.com"', $html);
    }
}
