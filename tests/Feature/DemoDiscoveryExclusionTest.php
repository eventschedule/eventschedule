<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Demo schedules exist to be looked at from /examples, not to be found.
 *
 * The bug: /search?q=jazz answered with the demo venue "Village Idiot Blues & Jazz" (18 Fake
 * Street, Montreal) and the fabricated event "Wine & Jazz Evening" at a cafe in Paris. Every
 * discovery surface tested for demo data by SUBDOMAIN - `simpsons` or a `demo-` prefix - and the
 * twelve showcase schedules linked from /examples are ordinary schedules, owned by ordinary
 * accounts, on ordinary subdomains. Nothing caught them. What they share is roles.email, the
 * schedule's own contact address, set to DemoService::DEMO_EMAIL.
 *
 * So Role::scopeDemoContent() adds that arm, and the four discovery surfaces negate it.
 *
 * The test that matters most here is the one for a schedule with NO contact email. The scope is
 * negated with whereNot(), and MySQL's three-valued logic turns `NOT (NULL OR false)` into NULL,
 * which filters the row out - so a careless `roles.email = ?` would drop every schedule that
 * verified a phone instead of an email from its own search results. That failure is invisible
 * from the demo side: every assertion about demo data still passes.
 */
class DemoDiscoveryExclusionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A schedule wearing the demo CONTACT address - the arm the showcase demos are caught by. */
    private function demoContactRole(string $name)
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'name' => $name,
            'email' => DemoService::DEMO_EMAIL,
        ]);
    }

    /** Schedule names returned by /search, for the given query. */
    private function searchSchedules(string $q): array
    {
        return $this->get('/search?q='.urlencode($q))->assertOk()
            ->viewData('schedules')->map(fn ($r) => $r->name)->all();
    }

    /** Event names returned by /search, for the given query. */
    private function searchEvents(string $q): array
    {
        return $this->get('/search?q='.urlencode($q))->assertOk()
            ->viewData('events')->map(fn ($e) => $e->name)->all();
    }

    private function browseEvents(): array
    {
        return $this->get('/browse')->assertOk()
            ->viewData('events')->map(fn ($e) => $e->name)->all();
    }

    // ------------------------------------------------------- the reported bug

    public function test_a_schedule_using_the_demo_contact_email_is_not_in_search(): void
    {
        $this->demoContactRole('Village Idiot Blues');
        $this->createRole($this->createOwner(), 'venue', ['name' => 'Village Hall Blues']);

        $this->assertSame(['Village Hall Blues'], $this->searchSchedules('Village'));
    }

    public function test_a_demo_schedules_event_is_not_in_search(): void
    {
        $demo = $this->demoContactRole('Sufficient Grounds Cafe');
        $this->createEvent($demo, ['name' => 'Wine & Jazz Evening']);

        $real = $this->createRole($this->createOwner(), 'venue', ['name' => 'Corner Bar']);
        $this->createEvent($real, ['name' => 'Wine & Jazz Social']);

        $this->assertSame(['Wine & Jazz Social'], $this->searchEvents('Wine & Jazz'));
    }

    public function test_a_demo_schedules_event_is_not_on_browse(): void
    {
        // /browse additionally requires a renderable card image, hence the flyers.
        $demo = $this->demoContactRole('Sufficient Grounds Cafe');
        $this->createEvent($demo, ['name' => 'Fabricated Gig', 'flyer_image_url' => 'demo.png']);

        $real = $this->createRole($this->createOwner(), 'venue', ['name' => 'Corner Bar']);
        $this->createEvent($real, ['name' => 'Real Gig', 'flyer_image_url' => 'real.png']);

        $this->assertSame(['Real Gig'], $this->browseEvents());
    }

    /**
     * An event is dropped if ANY of its schedules is demo, which is what removes the reported
     * "Wine & Jazz Evening" - both the venue and the curator holding it are demo.
     */
    public function test_an_event_is_dropped_when_a_demo_curator_holds_it(): void
    {
        $real = $this->createRole($this->createOwner(), 'venue', ['name' => 'Corner Bar']);
        $event = $this->createEvent($real, ['name' => 'Shared Jazz Gig']);

        $this->assertSame(['Shared Jazz Gig'], $this->searchEvents('Shared Jazz'));

        $event->roles()->attach($this->demoContactRole('Jazz Blues')->id, ['is_accepted' => true]);

        $this->assertSame([], $this->searchEvents('Shared Jazz'));
    }

    // ------------------------------------------------- what must NOT be caught

    public function test_a_real_schedule_and_its_event_still_appear(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Gulu Gulu Cafe']);
        $this->createEvent($role, ['name' => 'Gulu Gulu Open Mic']);

        $this->assertSame(['Gulu Gulu Cafe'], $this->searchSchedules('Gulu'));
        $this->assertSame(['Gulu Gulu Open Mic'], $this->searchEvents('Gulu'));
    }

    /**
     * The null-safety guard. A schedule can qualify for discovery on a verified PHONE
     * (publicScheduleFilter admits either channel), leaving roles.email NULL. Under whereNot(),
     * a plain `roles.email = ?` makes the whole predicate NULL for that row and MySQL drops it -
     * so this schedule would vanish from its own search results, and nothing on the demo side of
     * the suite would notice. Swap the <=> in Role::scopeDemoContent() for = and only this fails.
     */
    public function test_a_schedule_with_no_contact_email_still_appears(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Phone Only Venue',
            'email' => null,
            'email_verified_at' => null,
            'phone' => '+15551234567',
            'phone_verified_at' => now(),
        ]);
        $this->createEvent($role, ['name' => 'Phone Only Session']);

        $this->assertNull($role->fresh()->email, 'The fixture needs a NULL email to be meaningful');

        $this->assertSame(['Phone Only Venue'], $this->searchSchedules('Phone Only'));
        $this->assertSame(['Phone Only Session'], $this->searchEvents('Phone Only'));
    }

    // ------------------------------------------- the arms that already existed

    public function test_the_simpsons_subdomain_stays_excluded(): void
    {
        $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Springfield Jazz',
            'subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN,
        ]);

        $this->assertSame([], $this->searchSchedules('Springfield'));
    }

    public function test_the_demo_subdomain_prefix_stays_excluded(): void
    {
        $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Moes Tavern Jazz',
            'subdomain' => 'demo-moestavern',
        ]);

        $this->assertSame([], $this->searchSchedules('Moes'));
    }

    public function test_a_schedule_owned_by_the_demo_user_stays_excluded(): void
    {
        $demoUser = User::factory()->create([
            'email' => DemoService::DEMO_EMAIL,
            'email_verified_at' => now(),
        ]);

        $this->createRole($demoUser, 'venue', ['name' => 'Owned By Demo']);

        $this->assertSame([], $this->searchSchedules('Owned By Demo'));
    }
}
