<?php

namespace Tests\Feature;

use App\Models\AnalyticsSocialClicksDaily;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Short links for a schedule's social links.
 *
 * A link answers to the slug derived from its domain (facebook.com -> /facebook), to a custom one
 * the owner types, and - for a domain we do not recognise - to its brand name (promee.co.il ->
 * /promee). All three are free; the custom one only overrides. The pair is the whole point:
 * /facebook is printed on flyers, so adding /fb must not retire it.
 *
 * Resolution happens inside RoleController::viewGuest, and the tier decides WHERE in that ladder:
 * an OWNED slug (typed or platform-derived) resolves ahead of event and sub-schedule lookup, a
 * SUGGESTED one after both. These pin that precedence, because a slug nobody asked for must never
 * cost a schedule a page of its own.
 */
class SocialShortLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FACEBOOK = 'https://facebook.com/emeklive';

    private const PROMEE = 'https://promee.co.il/?r=33221';

    /** The links tab's two states, as the Blade row renders them. */
    private const LIVE_ADDRESS_CLASS = '<p class="link-slug-url text-xs truncate text-gray-400 dark:text-gray-500"';

    private const UNCLAIMED_ADDRESS_CLASS = '<p class="link-slug-url text-xs truncate text-gray-300 dark:text-gray-600 italic"';

    /** A click is only counted for something that looks like a real browser (PageView::isBot). */
    private const REAL_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';

    private function visit(string $path)
    {
        return $this->withHeaders(['User-Agent' => self::REAL_UA])->get($path);
    }

    /** @param array<int, array<string, string>> $links */
    private function scheduleWithLinks(array $links, array $attrs = [])
    {
        $role = $this->createRole($this->createOwner(), 'venue', $attrs);
        $role->social_links = json_encode($links);
        $role->save();

        return $role->fresh();
    }

    public function test_a_platform_slug_still_forwards(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Facebook', 'url' => self::FACEBOOK]]);

        $this->get("/{$role->subdomain}/facebook")->assertRedirect(self::FACEBOOK);
    }

    public function test_a_custom_slug_forwards_a_link_that_had_no_short_url(): void
    {
        $role = $this->scheduleWithLinks([
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'promee'],
        ]);

        $this->get("/{$role->subdomain}/promee")->assertRedirect(self::PROMEE);
    }

    /** The flyer case: a custom slug ADDS an address, it never retires the platform one. */
    public function test_a_custom_slug_does_not_retire_the_platform_slug(): void
    {
        $role = $this->scheduleWithLinks([
            ['name' => 'Facebook', 'url' => self::FACEBOOK, 'slug' => 'fb'],
        ]);

        $this->get("/{$role->subdomain}/fb")->assertRedirect(self::FACEBOOK);
        $this->get("/{$role->subdomain}/facebook")->assertRedirect(self::FACEBOOK);
    }

    public function test_a_slug_resolves_regardless_of_case(): void
    {
        $role = $this->scheduleWithLinks([
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'promee'],
        ]);

        $this->get("/{$role->subdomain}/Promee")->assertRedirect(self::PROMEE);
    }

    public function test_a_slug_the_schedule_does_not_own_falls_through_to_a_404(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Facebook', 'url' => self::FACEBOOK]]);

        // Was a redirect to the schedule home; an address that names nothing now says so. What
        // this test still proves is the important half: the 404 sits BELOW both social tiers.
        $this->get("/{$role->subdomain}/nothing-here")->assertNotFound();
    }

    /**
     * Regression: the check used to run against the GLOBAL platform list, so a slug like
     * "discord" was intercepted whether or not the schedule had a Discord link, and an event
     * named after a platform was bounced to the schedule home instead of rendering.
     */
    public function test_an_event_named_after_an_unused_platform_still_renders(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Facebook', 'url' => self::FACEBOOK]]);
        $this->createEvent($role, [
            'name' => 'Discord',
            'slug' => 'discord',
            'creator_role_id' => $role->id,
        ]);

        $this->get("/{$role->subdomain}/discord")->assertOk();
    }

    /** The escape hatch: an id in the URL means the slug names an event, not a short link. */
    public function test_an_event_id_wins_over_a_matching_short_link(): void
    {
        $role = $this->scheduleWithLinks([
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'promee'],
        ]);
        $event = $this->createEvent($role, [
            'name' => 'Promee',
            'slug' => 'promee',
            'creator_role_id' => $role->id,
        ]);

        $this->get("/{$role->subdomain}/promee/".UrlUtils::encodeId($event->id))->assertOk();
    }

    public function test_a_custom_slug_click_is_counted_under_that_slug(): void
    {
        $role = $this->scheduleWithLinks([
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'promee'],
        ]);

        $this->visit("/{$role->subdomain}/promee");

        $this->assertDatabaseHas('analytics_social_clicks_daily', [
            'role_id' => $role->id,
            'platform' => 'promee',
            'clicks' => 1,
        ]);
    }

    /**
     * Both of a Facebook link's addresses count into the SAME bucket. Keying on the slug would
     * split a platform's existing history the day an owner added an alias.
     */
    public function test_an_alias_counts_into_the_platform_bucket(): void
    {
        $role = $this->scheduleWithLinks([
            ['name' => 'Facebook', 'url' => self::FACEBOOK, 'slug' => 'fb'],
        ]);

        $this->visit("/{$role->subdomain}/fb");
        $this->visit("/{$role->subdomain}/facebook");

        $this->assertDatabaseHas('analytics_social_clicks_daily', [
            'role_id' => $role->id,
            'platform' => 'facebook',
            'clicks' => 2,
        ]);
        $this->assertDatabaseMissing('analytics_social_clicks_daily', [
            'role_id' => $role->id,
            'platform' => 'fb',
        ]);
    }

    public function test_an_owner_visiting_their_own_short_link_is_not_counted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->social_links = json_encode([
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'promee'],
        ]);
        $role->save();

        $this->actingAs($owner)->withHeaders(['User-Agent' => self::REAL_UA])
            ->get("/{$role->subdomain}/promee")->assertRedirect(self::PROMEE);

        $this->assertSame(0, AnalyticsSocialClicksDaily::where('role_id', $role->id)->count());
    }

    /** Guards the four guest partials that build the icon href. */
    public function test_the_guest_header_routes_a_custom_slug_through_the_short_link(): void
    {
        $role = $this->scheduleWithLinks([
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'promee'],
        ]);

        $this->get("/{$role->subdomain}")
            ->assertOk()
            ->assertSee($role->getGuestUrl().'/promee', false);
    }

    /**
     * The reported bug. An unrecognised domain answers to its brand name with NO slug stored and
     * nothing for the owner to do, the way facebook.com already answers to /facebook.
     */
    public function test_an_unrecognised_domain_answers_to_its_brand_name_with_no_slug_stored(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Promee', 'url' => self::PROMEE]]);

        $this->get("/{$role->subdomain}/promee")->assertRedirect(self::PROMEE);
    }

    public function test_the_guest_header_routes_a_free_slug_through_the_short_link(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Promee', 'url' => self::PROMEE]]);

        $this->get("/{$role->subdomain}")
            ->assertOk()
            ->assertSee($role->getGuestUrl().'/promee', false)
            ->assertDontSee('href="'.self::PROMEE.'"', false);
    }

    public function test_a_free_slug_click_is_counted_under_that_slug(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Promee', 'url' => self::PROMEE]]);

        $this->visit("/{$role->subdomain}/promee");

        $this->assertDatabaseHas('analytics_social_clicks_daily', [
            'role_id' => $role->id,
            'platform' => 'promee',
            'clicks' => 1,
        ]);
    }

    /**
     * The whole reason the free slug resolves LAST. A sub-schedule is a page of the schedule's
     * own; a slug nobody asked for must not take it.
     */
    public function test_a_sub_schedule_beats_a_free_slug(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Promee', 'url' => self::PROMEE]]);
        $this->createGroup($role, ['name' => 'Promee', 'slug' => 'promee']);

        $this->get("/{$role->subdomain}/promee")->assertOk();
    }

    public function test_an_event_beats_a_free_slug(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Promee', 'url' => self::PROMEE]]);
        $this->createEvent($role, [
            'name' => 'Promee',
            'slug' => 'promee',
            'creator_role_id' => $role->id,
        ]);

        $this->get("/{$role->subdomain}/promee")->assertOk();
    }

    /** An owner-typed slug still wins outright, and the free one it displaces is not reassigned. */
    public function test_a_typed_slug_on_another_link_takes_the_name_outright(): void
    {
        $role = $this->scheduleWithLinks([
            ['name' => 'Facebook', 'url' => self::FACEBOOK, 'slug' => 'promee'],
            ['name' => 'Promee', 'url' => self::PROMEE],
        ]);

        $this->get("/{$role->subdomain}/promee")->assertRedirect(self::FACEBOOK);
        // No "-2" consolation prize: a suffixed address is not one an owner would print.
        $this->get("/{$role->subdomain}/promee-2")->assertNotFound();
    }

    /**
     * /follow is a real route (routes/web.php), so it never reaches viewGuest at all. Offering it
     * as a live address would hand the owner a short link that silently does something else.
     */
    public function test_a_free_slug_is_never_a_reserved_route(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Follow', 'url' => 'https://follow.example.com/x']]);

        $this->assertSame([0 => ''], $role->shortLinkSlugs());
    }

    /** A sub-schedule owning the name keeps the link off it, rather than shifting it to promee-2. */
    public function test_a_free_slug_is_not_offered_when_a_sub_schedule_owns_the_name(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Promee', 'url' => self::PROMEE]]);
        $this->createGroup($role, ['name' => 'Promee', 'slug' => 'promee']);

        $this->assertSame([0 => ''], $role->fresh()->shortLinkSlugs());
    }

    /**
     * The links tab must print the address the router actually honours. It printed the greyed
     * SUGGESTION for a link with no stored slug, which is how emeklive.co.il/promee came to be
     * tried, copied and reported as broken.
     */
    public function test_the_links_tab_shows_a_free_slug_as_a_live_address(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->social_links = json_encode([['name' => 'Promee', 'url' => self::PROMEE]]);
        $role->save();

        $html = $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        // The row's live address, and the copy button that only shows for one.
        $this->assertStringContainsString('data-url="'.$role->getGuestUrl().'/promee"', $html);
        // Nothing stored, so the editor opens empty and the next unrelated save cannot claim it.
        $this->assertStringContainsString('data-link-custom=""', $html);
        $this->assertStringContainsString('data-link-auto="promee"', $html);
        // The "not stored yet" styling is what made a suggestion look live; it must be gone.
        // Matched on the rendered element, not the bare class string - createTextLinkLi() carries
        // the same classes as a JS literal, so a looser assertion would pass on the script alone.
        $this->assertStringContainsString(self::LIVE_ADDRESS_CLASS, $html);
        $this->assertStringNotContainsString(self::UNCLAIMED_ADDRESS_CLASS, $html);
    }

    /** The other half: a link whose name is taken still renders as an unclaimed suggestion. */
    public function test_the_links_tab_still_greys_a_suggestion_it_cannot_hand_out(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->social_links = json_encode([['name' => 'Promee', 'url' => self::PROMEE]]);
        $role->save();
        $this->createGroup($role, ['name' => 'Promee', 'slug' => 'promee']);

        $html = $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(self::UNCLAIMED_ADDRESS_CLASS, $html);
        $this->assertStringNotContainsString(self::LIVE_ADDRESS_CLASS, $html);
        $this->assertStringContainsString('data-link-suggestion="promee-2"', $html);
        $this->assertStringContainsString('data-link-auto=""', $html);
    }
}
