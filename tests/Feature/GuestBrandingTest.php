<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The full deployment x tier branding matrix for guest pages - see docs/BRANDING_MATRIX.md.
 *
 * Three inputs decide what a guest page credits us with (IS_HOSTED, IS_NEXUS and the schedule's
 * plan tier) and they are independent, so the interesting cases are combinations rather than
 * single flags. GrantedPlanCreditTest covers the nexus granted-plan case in depth; this covers
 * the grid, including the selfhost row that carried nothing at all until Role::creditChipReason()
 * existed.
 *
 * The page credit is the corner chip and nothing else. Off eventschedule.com it is on every page,
 * whatever the tier; on eventschedule.com its free tier carries it and a paid plan takes it off.
 * Operators used to put a dark footer strip on their free tier instead, linking their own site,
 * and the chip stood down for it; the strip was removed on 2026-10-20, and the tests below keep it
 * gone.
 *
 * Routes are registered at boot from the environment's IS_HOSTED, so overriding app.hosted here
 * changes what the views decide but not the URL shape. Feature tests run path-based either way
 * (app.is_testing), so every assertion below reads rendered HTML and none reads a path.
 */
class GuestBrandingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The dark strip that was removed, asserted absent. marketing_domain() is not stable under is_testing. */
    private const STRIP = 'Create your free schedule at';

    /** The corner chip, whose utm tag names which of its four jobs applies. */
    private const CHIP_SELFHOST = 'utm_source=selfhost';

    private const CHIP_SAAS = 'utm_source=saas';

    private const CHIP_FREE = 'utm_source=free-plan';

    private const CHIP_GRANTED = 'utm_source=granted-plan';

    /** The card in the column beside an event's details. */
    private const CARD = 'Create your own event schedule!';

    /** @param  'nexus'|'saas'|'selfhost'  $mode */
    private function deploy(string $mode): void
    {
        config([
            'app.hosted' => $mode !== 'selfhost',
            'app.is_nexus' => $mode === 'nexus',
        ]);
    }

    /** createRole() defaults to enterprise with a future expiry, i.e. a paying schedule. */
    private function freeRole(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Free Venue',
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'plan_source' => null,
        ]);
    }

    private function paidRole(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Paid Venue',
            'plan_source' => null,
        ]);
    }

    private function guestPage(Role $role, string $query = ''): string
    {
        return $this->get('/'.$role->subdomain.$query)->assertOk()->getContent();
    }

    // ---------------------------------------------------------------- nexus

    public function test_nexus_free_gets_the_chip_instead_of_the_strip(): void
    {
        // eventschedule.com runs no footer strip. Its free tier carries the same small corner chip
        // as every other install, pointing at the hardcoded domain (BRANDING_MATRIX rule 1) and
        // tagged apart so the traffic report can tell it from a granted plan's.
        $this->deploy('nexus');

        $content = $this->guestPage($this->freeRole());

        $this->assertStringContainsString(
            'href="https://eventschedule.com?'.self::CHIP_FREE.'&amp;utm_medium=footer"',
            $content
        );
        $this->assertStringNotContainsString(self::STRIP, $content);
    }

    public function test_nexus_free_chip_is_absent_from_embeds(): void
    {
        // Attribution inside a third party's iframe is the snippet's job, on this tier as on
        // every other.
        $this->deploy('nexus');

        $this->assertStringNotContainsString(
            'utm_medium=footer',
            $this->guestPage($this->freeRole(), '?embed=1')
        );
    }

    public function test_nexus_paid_gets_nothing(): void
    {
        $this->deploy('nexus');

        $content = $this->guestPage($this->paidRole());

        $this->assertStringNotContainsString(self::STRIP, $content);
        $this->assertStringNotContainsString('utm_medium=footer', $content);
    }

    /**
     * With no credit under it, the privacy pill is the last thing on the page, and it sat against
     * the bottom edge of the window: the chip's own padding had been the only room beneath it.
     */
    public function test_the_privacy_pill_has_room_under_it_where_no_credit_follows(): void
    {
        $this->deploy('nexus');
        $pill = fn (string $html) => preg_match('/data-privacy-pill class="([^"]*)"/', $html, $m) === 1 ? $m[1] : null;

        $this->assertStringContainsString('pb-8', (string) $pill($this->guestPage($this->paidRole())));

        // The free tier's chip follows it and brings its own room.
        $free = $pill($this->guestPage($this->freeRole()));
        $this->assertNotNull($free);
        $this->assertStringNotContainsString('pb-8', $free);
    }

    // ------------------------------------------------------ selfhosted SaaS

    public function test_saas_free_gets_the_chip(): void
    {
        // The chip is the license credit, owed by whoever redistributes the software, so an
        // operator's free tier carries it as their paid tiers do. It used to carry the operator's
        // own footer strip instead, which left a free tenant with no Event Schedule credit at all.
        $this->deploy('saas');

        $content = $this->guestPage($this->freeRole());

        $this->assertStringContainsString('href="https://eventschedule.com?'.self::CHIP_SAAS.'&amp;utm_medium=footer"', $content);
        $this->assertStringNotContainsString(self::STRIP, $content);
    }

    public function test_saas_paid_gets_the_chip(): void
    {
        // The tenant's subscription is between them and the operator. Our credit does not come
        // off when a tenant upgrades - only eventschedule.com sells white-label.
        $this->deploy('saas');

        $content = $this->guestPage($this->paidRole());

        $this->assertStringContainsString(self::CHIP_SAAS, $content);
        $this->assertStringNotContainsString(self::STRIP, $content);
    }

    public function test_the_chip_tooltip_keeps_our_name_through_a_translation_override(): void
    {
        // An operator renamed the tooltip to their own brand at /admin/translations while the
        // link still went to eventschedule.com. The attribution is not theirs to rebrand.
        $this->deploy('saas');
        $role = $this->paidRole();

        $this->assertStringContainsString('title="Powered by Event Schedule"', $this->guestPage($role));

        app('translator')->addLines(['messages.powered_by_event_schedule' => 'Powered by Another Brand'], 'en');

        $content = $this->guestPage($role);
        $this->assertStringContainsString('title="Powered by Event Schedule"', $content);
        $this->assertStringNotContainsString('Another Brand', $content);
        $this->assertSame('Powered by Event Schedule', Role::creditChipTitle());

        // A translation that keeps the name is used as it is.
        app('translator')->addLines(['messages.powered_by_event_schedule' => 'Unterstützt von Event Schedule'], 'en');
        $this->assertSame('Unterstützt von Event Schedule', Role::creditChipTitle());
    }

    // ------------------------------------------------------------- selfhost

    public function test_selfhost_gets_the_chip_and_nothing_else(): void
    {
        $this->deploy('selfhost');

        $content = $this->guestPage($this->paidRole());

        $this->assertStringContainsString(self::CHIP_SELFHOST, $content);
        $this->assertStringContainsString('https://eventschedule.com?'.self::CHIP_SELFHOST, $content);
        // Everything the free tier carries on a hosted platform is absent here.
        $this->assertStringNotContainsString(self::STRIP, $content);
    }

    public function test_selfhost_chip_ignores_the_plan_columns(): void
    {
        // Every schedule on a single-tenant install resolves to 'enterprise' whatever the columns
        // say, so the chip cannot be tier-gated there - that is what left it never rendering.
        $this->deploy('selfhost');

        $this->assertStringContainsString(self::CHIP_SELFHOST, $this->guestPage($this->freeRole()));
    }

    public function test_selfhost_keeps_the_credit_even_if_is_nexus_is_set(): void
    {
        // hosted and is_nexus are independent env vars. The SaaS setup guide tells operators to
        // leave IS_NEXUS off, but a misconfigured install must not silently drop the attribution:
        // without the hosted-first guard this falls through to the nexus's plan logic, where an
        // unhosted schedule resolves to 'enterprise', matches nothing, and answers null.
        // deploy() cannot express this combination, so it is set directly.
        config(['app.hosted' => false, 'app.is_nexus' => true]);

        $this->assertSame('selfhost', $this->paidRole()->creditChipReason());
        $this->assertSame('selfhost', $this->freeRole()->creditChipReason());

        // A stale plan_source from a database that used to live on a hosted install must not
        // rewrite this into the granted-plan case and mistag the traffic report.
        $stale = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Migrated Venue',
            'plan_source' => 'admin',
        ]);
        $this->assertSame('selfhost', $stale->creditChipReason());
    }

    public function test_selfhost_chip_is_absent_from_embeds(): void
    {
        // An embed renders inside a third party's iframe; attribution there is the snippet's job.
        $this->deploy('selfhost');

        $this->assertStringNotContainsString(
            self::CHIP_SELFHOST,
            $this->guestPage($this->paidRole(), '?embed=1')
        );
    }

    // ---------------------------------------------------------- the invariant

    public function test_no_guest_page_carries_the_footer_strip(): void
    {
        // Over every cell of the grid, so bringing the strip back at any render site fails here.
        //
        // The roles are built once and the deployment re-read around them: nothing in the
        // predicates is memoized, and none reads a stored column that config() would change.
        $free = $this->freeRole();
        $paid = $this->paidRole();

        foreach (['nexus', 'saas', 'selfhost'] as $mode) {
            $this->deploy($mode);

            foreach (['free' => $free, 'paid' => $paid] as $tier => $role) {
                $this->assertStringNotContainsString(
                    self::STRIP,
                    $this->guestPage($role),
                    "A {$mode} {$tier} guest page carried the footer strip."
                );
            }
        }
    }

    public function test_every_saas_guest_page_carries_the_chip(): void
    {
        // The rule this file used to hold the other way round: off the nexus no tier goes
        // without our credit, so a page with no chip is a regression whatever its plan.
        $this->deploy('saas');

        foreach (['free' => $this->freeRole(), 'paid' => $this->paidRole()] as $tier => $role) {
            $this->assertStringContainsString(
                self::CHIP_SAAS,
                $this->guestPage($role),
                "A saas {$tier} guest page must carry the chip."
            );
        }
    }

    public function test_every_free_guest_page_carries_the_chip(): void
    {
        // A free page with no credit is the change that fails silently: every row that only
        // asserts absence still passes.
        $free = $this->freeRole();

        foreach (['nexus', 'saas', 'selfhost'] as $mode) {
            $this->deploy($mode);

            $this->assertStringContainsString(
                'utm_medium=footer',
                $this->guestPage($free),
                "A {$mode} free guest page must carry the chip."
            );
        }
    }

    // ------------------------------------------------- the event-page card

    public function test_event_card_follows_this_schedule_not_the_bill(): void
    {
        // Regression: the card used to read `! $event->isPro()`, true when ANY schedule on the
        // event is paid, so a free curator's page dropped the card while still carrying the
        // free tier's page credit.
        $this->deploy('nexus');

        $curator = $this->createRole($this->createOwner(), 'curator', [
            'name' => 'Free Curator',
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);
        $venue = $this->paidRole();

        $event = $this->createEvent($curator, ['name' => 'Shared Bill']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $content = $this->get('/'.$curator->subdomain.'/'.$event->slug)->assertOk()->getContent();

        $this->assertStringContainsString(self::CARD, $content);
        $this->assertStringContainsString(self::CHIP_FREE, $content);
        $this->assertStringNotContainsString(self::STRIP, $content);
    }

    public function test_event_card_is_absent_for_a_paid_schedule(): void
    {
        $this->deploy('nexus');

        $role = $this->paidRole();
        $event = $this->createEvent($role, ['name' => 'Paid Bill']);

        $content = $this->get('/'.$role->subdomain.'/'.$event->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::CARD, $content);
    }

    public function test_selfhost_never_shows_the_event_card(): void
    {
        $this->deploy('selfhost');

        $role = $this->paidRole();
        $event = $this->createEvent($role, ['name' => 'Selfhost Bill']);

        $content = $this->get('/'.$role->subdomain.'/'.$event->slug)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::CARD, $content);
        $this->assertStringContainsString(self::CHIP_SELFHOST, $content);
    }

    // ---------------------------------------------------- the head metadata

    /** Everything before </head>: the metadata, never the body, which quotes whatever it likes. */
    private function pageHead(string $html): string
    {
        $end = stripos($html, '</head>');

        $this->assertNotFalse($end, 'The page has no </head> to cut at.');

        return substr($html, 0, $end);
    }

    public function test_no_guest_page_names_our_x_account_in_its_head(): void
    {
        // twitter:site is the X account of the site a card belongs to. On a guest page that site
        // is the schedule's, and @ScheduleEvent there was the one mention of us left in its head,
        // which white-label.blade.php promises is gone. The matrix row is flat, so every
        // deployment is checked, and one page per branch of app-guest's meta slot: each marker
        // proves the page reached the branch it is named after.
        $role = $this->paidRole();
        $event = $this->createEvent($role, ['name' => 'Head Check', 'creator_role_id' => $role->id, 'fan_photos_enabled' => true]);
        $gated = $this->createEvent($role, ['name' => 'Locked Night', 'creator_role_id' => $role->id, 'event_password' => 'letmein']);

        $pages = [
            'schedule' => ['/'.$role->subdomain, '~<meta property="og:type" content="website">~'],
            'embed' => ['/'.$role->subdomain.'?embed=true', '~<meta name="robots" content="noindex, nofollow">~'],
            'event' => [$this->guestEventUrl($role, $event), '~<meta property="og:title" content="Head Check">~'],
            'gallery' => [$this->guestEventUrl($role, $event).'/photos', '~<link rel="canonical" href="[^"]*/photos"~'],
            'password gate' => [$this->guestEventUrl($role, $gated), '~<meta property="og:title" content="'.preg_quote(e(__('messages.event_password_required')), '~').'">~'],
        ];

        foreach (['nexus', 'saas', 'selfhost'] as $mode) {
            $this->deploy($mode);

            foreach ($pages as $kind => [$url, $marker]) {
                $head = $this->pageHead($this->get($url)->assertOk()->getContent());
                $label = "{$mode} {$kind} page";

                $this->assertMatchesRegularExpression($marker, $head, $label.': not the branch it is named after');
                $this->assertStringContainsString('name="twitter:card"', $head, $label.': no card metadata at all');
                $this->assertStringNotContainsString('twitter:site', $head, $label);
                $this->assertStringNotContainsString('@ScheduleEvent', $head, $label);
            }
        }
    }

    public function test_the_guest_layout_source_never_prints_twitter_site(): void
    {
        // Every branch of the meta slot at once, including the ones no render above reaches,
        // such as a draft's page. The whole string, not the tag: a comment naming it is how a
        // copy-paste brings it back.
        $source = file_get_contents(resource_path('views/layouts/app-guest.blade.php'));

        $this->assertStringNotContainsString('twitter:site', $source);
        $this->assertStringNotContainsString('@ScheduleEvent', $source);
    }

    public function test_the_marketing_site_keeps_its_x_account(): void
    {
        // The control for the two above: the same cut and the same string match do find the tag
        // where it belongs, on our own pages - so their silence on a guest page means something.
        $head = $this->pageHead($this->get(route('marketing.white_label'))->assertOk()->getContent());

        $this->assertStringContainsString('<meta name="twitter:site" content="@ScheduleEvent">', $head);
    }

    // ------------------------------------------------------- the predicates

    public function test_show_branding_is_free_tier_when_hosted_and_never_on_selfhost(): void
    {
        $free = $this->freeRole();
        $paid = $this->paidRole();

        $this->deploy('nexus');
        $this->assertTrue($free->showBranding());
        $this->assertFalse($paid->showBranding());

        $this->deploy('saas');
        $this->assertTrue($free->showBranding());
        $this->assertFalse($paid->showBranding());

        // Not the inverse of isWhiteLabeled(): both schedules are white-labeled here, and
        // neither carries the free-tier surfaces, because those are a hosted platform's CTAs.
        $this->deploy('selfhost');
        $this->assertFalse($free->showBranding());
        $this->assertFalse($paid->showBranding());
    }

    public function test_the_footer_strip_predicate_is_gone(): void
    {
        // Nothing may reintroduce a second page credit beside the chip by name.
        $this->assertFalse(method_exists(Role::class, 'showFooterStrip'));
    }

    public function test_credit_chip_reason_names_the_case(): void
    {
        $free = $this->freeRole();
        $paid = $this->paidRole();
        $granted = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Granted Venue',
            'plan_source' => 'admin',
        ]);

        // eventschedule.com has no strip, so its free tier is named here too. A paid plan takes
        // the chip off unless an admin granted it by hand.
        $this->deploy('nexus');
        $this->assertSame('free_plan', $free->creditChipReason());
        $this->assertNull($paid->creditChipReason());
        $this->assertSame('granted_plan', $granted->creditChipReason());

        // Off the nexus the tier stops deciding anything, and plan_source still means nothing -
        // that column only counts on the one install that hands plans out.
        $this->deploy('saas');
        $this->assertSame('saas', $free->creditChipReason());
        $this->assertSame('saas', $paid->creditChipReason());
        $this->assertSame('saas', $granted->creditChipReason());

        // Nor on a selfhost.
        $this->deploy('selfhost');
        $this->assertSame('selfhost', $free->creditChipReason());
        $this->assertSame('selfhost', $paid->creditChipReason());
        $this->assertSame('selfhost', $granted->creditChipReason());
    }
}
