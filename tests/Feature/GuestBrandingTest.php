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
 * Routes are registered at boot from the environment's IS_HOSTED, so overriding app.hosted here
 * changes what the views decide but not the URL shape. Feature tests run path-based either way
 * (app.is_testing), so every assertion below reads rendered HTML and none reads a path.
 */
class GuestBrandingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The dark strip, on every deployment. marketing_domain() is not stable under is_testing. */
    private const STRIP = 'Create your free schedule at';

    /** Unique to the strip's nexus variant. */
    private const SPONSOR = 'Invoice Ninja';

    /** The corner chip, whose utm tag names which of its three jobs applies. */
    private const CHIP_SELFHOST = 'utm_source=selfhost';

    private const CHIP_SAAS = 'utm_source=saas';

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

    public function test_nexus_free_gets_the_strip_with_the_sponsor_and_no_chip(): void
    {
        $this->deploy('nexus');

        $content = $this->guestPage($this->freeRole());

        $this->assertStringContainsString(self::STRIP, $content);
        $this->assertStringContainsString(self::SPONSOR, $content);
        $this->assertStringNotContainsString('utm_medium=footer', $content);
    }

    public function test_nexus_paid_gets_nothing(): void
    {
        $this->deploy('nexus');

        $content = $this->guestPage($this->paidRole());

        $this->assertStringNotContainsString(self::STRIP, $content);
        $this->assertStringNotContainsString(self::SPONSOR, $content);
        $this->assertStringNotContainsString('utm_medium=footer', $content);
    }

    // ----------------------------------------------------- self-hosted SaaS

    public function test_saas_free_gets_both_the_strip_and_the_chip(): void
    {
        // Deliberate, not a double-branding bug: the strip promotes the operator through
        // marketing_url(), the chip is our license attribution and stays pointed at us.
        $this->deploy('saas');

        $content = $this->guestPage($this->freeRole());

        $this->assertStringContainsString(self::STRIP, $content);
        $this->assertStringContainsString(self::CHIP_SAAS, $content);
        // The sponsor credit is the nexus's own, not an operator's to carry.
        $this->assertStringNotContainsString(self::SPONSOR, $content);
    }

    public function test_saas_paid_gets_nothing(): void
    {
        $this->deploy('saas');

        $content = $this->guestPage($this->paidRole());

        $this->assertStringNotContainsString(self::STRIP, $content);
        $this->assertStringNotContainsString('utm_medium=footer', $content);
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
        $this->assertStringNotContainsString(self::SPONSOR, $content);
    }

    public function test_selfhost_chip_ignores_the_plan_columns(): void
    {
        // Every schedule on a single-tenant install resolves to 'enterprise' whatever the columns
        // say, so the chip cannot be tier-gated there - that is what left it never rendering.
        $this->deploy('selfhost');

        $this->assertStringContainsString(self::CHIP_SELFHOST, $this->guestPage($this->freeRole()));
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

    // ------------------------------------------------- the event-page card

    public function test_event_card_follows_this_schedule_not_the_bill(): void
    {
        // Regression: the card used to read `! $event->isPro()`, true when ANY schedule on the
        // event is paid, so a free curator's page dropped the card while still carrying the strip.
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
        $this->assertStringContainsString(self::STRIP, $content);
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
        // neither carries the strip, because the strip is a hosted-platform growth CTA.
        $this->deploy('selfhost');
        $this->assertFalse($free->showBranding());
        $this->assertFalse($paid->showBranding());
    }

    public function test_credit_chip_reason_names_the_case(): void
    {
        $free = $this->freeRole();
        $paid = $this->paidRole();
        $granted = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Granted Venue',
            'plan_source' => 'admin',
        ]);

        $this->deploy('nexus');
        $this->assertNull($free->creditChipReason());
        $this->assertNull($paid->creditChipReason());
        $this->assertSame('granted_plan', $granted->creditChipReason());

        $this->deploy('saas');
        $this->assertSame('saas_free', $free->creditChipReason());
        $this->assertNull($paid->creditChipReason());
        // plan_source is a nexus concern; an operator's paid tenant is simply white-labeled.
        $this->assertNull($granted->creditChipReason());

        $this->deploy('selfhost');
        $this->assertSame('selfhost', $free->creditChipReason());
        $this->assertSame('selfhost', $paid->creditChipReason());
        $this->assertSame('selfhost', $granted->creditChipReason());
    }
}
