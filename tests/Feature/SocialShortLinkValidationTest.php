<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Save-time validation of owner-typed short-link slugs.
 *
 * A rejected slug has to be caught here, because viewGuest redirects rather than 404s on a slug
 * it does not recognise: an unreachable short link would otherwise just look like a dead link
 * with nothing anywhere to explain why.
 *
 * Errors land on 'social_links' as a whole - it is one hidden JSON input, so there is no
 * per-entry field to attach them to.
 */
class SocialShortLinkValidationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const PROMEE = 'https://promee.co.il/?r=33221';

    private function save($owner, $role, array $links)
    {
        return $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
            'social_links' => json_encode($links),
        ]);
    }

    /** @return array{0: \App\Models\User, 1: \App\Models\Role} */
    private function schedule(): array
    {
        $owner = $this->createOwner();

        return [$owner, $this->createRole($owner, 'venue')];
    }

    public function test_a_custom_slug_is_stored_normalized(): void
    {
        [$owner, $role] = $this->schedule();

        $this->save($owner, $role, [
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => '  Promee  '],
        ])->assertSessionHasNoErrors();

        $this->assertSame('promee', json_decode($role->fresh()->social_links, true)[0]['slug']);
    }

    /** Str::slug() alone returns "" here, which would make the field unusable on a Hebrew install. */
    public function test_a_hebrew_slug_is_romanized_rather_than_dropped(): void
    {
        [$owner, $role] = $this->schedule();

        $this->save($owner, $role, [
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'פרומי'],
        ])->assertSessionHasNoErrors();

        $slug = json_decode($role->fresh()->social_links, true)[0]['slug'];

        $this->assertNotSame('', $slug);
        $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $slug);
    }

    public function test_a_slug_naming_another_platform_is_rejected(): void
    {
        [$owner, $role] = $this->schedule();

        $this->save($owner, $role, [
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'facebook'],
        ])->assertSessionHasErrors('social_links');
    }

    /** A link's OWN platform is a no-op, not a collision: /facebook already points there. */
    public function test_a_slug_naming_the_links_own_platform_is_allowed(): void
    {
        [$owner, $role] = $this->schedule();

        $this->save($owner, $role, [
            ['name' => 'Facebook', 'url' => 'https://facebook.com/emeklive', 'slug' => 'facebook'],
        ])->assertSessionHasNoErrors();
    }

    public function test_a_slug_colliding_with_a_sub_schedule_is_rejected(): void
    {
        [$owner, $role] = $this->schedule();
        $role->groups()->create(['name' => 'Shows', 'slug' => Group::cleanSlug($role->id, 'Shows')]);

        $this->save($owner, $role->fresh(), [
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'shows'],
        ])->assertSessionHasErrors('social_links');
    }

    /**
     * A route registered ahead of the guest catch-all wins outright, so these slugs would never
     * reach viewGuest at all.
     */
    public function test_a_slug_owned_by_an_app_route_is_rejected(): void
    {
        foreach (['edit', 'follow', 'book'] as $reserved) {
            [$owner, $role] = $this->schedule();

            $this->save($owner, $role, [
                ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => $reserved],
            ])->assertSessionHasErrors('social_links', "'{$reserved}' must be rejected");
        }
    }

    public function test_two_links_cannot_share_a_slug(): void
    {
        [$owner, $role] = $this->schedule();

        $this->save($owner, $role, [
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'shop'],
            ['name' => 'N99', 'url' => 'https://n99.co.il/articles/222', 'slug' => 'shop'],
        ])->assertSessionHasErrors('social_links');
    }

    public function test_a_slug_that_romanizes_to_nothing_is_reported_not_silently_dropped(): void
    {
        [$owner, $role] = $this->schedule();

        $this->save($owner, $role, [
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => '!!!'],
        ])->assertSessionHasErrors('social_links');
    }

    public function test_a_slug_longer_than_the_analytics_column_cannot_be_stored(): void
    {
        [$owner, $role] = $this->schedule();

        $this->save($owner, $role, [
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => str_repeat('a', 40)],
        ])->assertSessionHasNoErrors();

        $slug = json_decode($role->fresh()->social_links, true)[0]['slug'];

        $this->assertSame(UrlUtils::LINK_SLUG_MAX, strlen($slug));
    }

    /** A link without a slug must round-trip untouched, so nothing is claimed on its behalf. */
    public function test_a_link_with_no_slug_gains_none(): void
    {
        [$owner, $role] = $this->schedule();

        $this->save($owner, $role, [
            ['name' => 'Promee', 'url' => self::PROMEE],
        ])->assertSessionHasNoErrors();

        $stored = json_decode($role->fresh()->social_links, true)[0];

        $this->assertArrayNotHasKey('slug', $stored);
        $this->assertArrayNotHasKey('slug_input', $stored);
    }

    /**
     * reservedPathSlugs() grows whenever a route is added. An owner must never find that an
     * unrelated save of their schedule now fails because of a slug they set months earlier.
     */
    public function test_an_existing_slug_that_is_now_reserved_survives_an_unrelated_save(): void
    {
        [$owner, $role] = $this->schedule();
        $role->social_links = json_encode([
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'follow'],
        ]);
        $role->save();

        $this->save($owner, $role->fresh(), [
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'follow'],
        ])->assertSessionHasNoErrors();

        $this->assertSame('follow', json_decode($role->fresh()->social_links, true)[0]['slug']);
    }

    /** Guards the old() fix: a bounce must not discard the owner's unsaved link edits. */
    public function test_a_rejected_save_keeps_the_submitted_links_for_redisplay(): void
    {
        [$owner, $role] = $this->schedule();
        $payload = json_encode([
            ['name' => 'Promee', 'url' => self::PROMEE, 'slug' => 'facebook'],
        ]);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
            'social_links' => $payload,
        ])->assertSessionHasErrors('social_links')
            ->assertSessionHasInput('social_links');
    }
}
