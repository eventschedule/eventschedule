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
 * A link answers to the slug derived from its domain (facebook.com -> /facebook) and, once the
 * owner sets one, to a custom slug as well. The pair is the whole point: /facebook is printed on
 * flyers, so adding /fb must not retire it. An unrecognised domain (promee.co.il) had no slug at
 * all before this, which is what the feature exists to fix.
 *
 * Resolution happens inside RoleController::viewGuest, ahead of event and sub-schedule lookup,
 * so these also pin the precedence that placement creates.
 */
class SocialShortLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FACEBOOK = 'https://facebook.com/emeklive';

    private const PROMEE = 'https://promee.co.il/?r=33221';

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

    public function test_a_slug_the_schedule_does_not_own_falls_through_to_the_home_page(): void
    {
        $role = $this->scheduleWithLinks([['name' => 'Facebook', 'url' => self::FACEBOOK]]);

        $this->get("/{$role->subdomain}/nothing-here")->assertRedirect($role->getGuestUrl());
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
}
