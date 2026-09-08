<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The public page for a schedule the app created while somebody entered an event, and the two
 * answers it offers the person it describes.
 */
class UnclaimedSchedulePageTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function placeholder(array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = $attrs['subdomain'] ?? 'act'.strtolower(Str::random(10));
        $role->type = $attrs['type'] ?? 'talent';
        $role->name = $attrs['name'] ?? 'The Wandering Few';
        $role->timezone = 'America/New_York';
        $role->plan_type = 'free';

        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }

        $role->save();

        return $role->fresh();
    }

    private function listedOn(Role $placeholder, string $curatorName = 'Ba-Be Bar'): array
    {
        $organizer = $this->createOwner();
        $curator = $this->createRole($organizer, 'venue', ['name' => $curatorName]);
        $event = $this->createEvent($curator, ['name' => 'Double Bill', 'creator_role_id' => $curator->id]);
        $event->roles()->attach($placeholder->id, ['is_accepted' => true]);

        return [$organizer, $curator, $event->fresh()];
    }

    private function url(Role $role): string
    {
        return route('role.view_guest', ['subdomain' => $role->subdomain]);
    }

    public function test_the_page_renders_and_says_where_it_came_from(): void
    {
        $placeholder = $this->placeholder(['name' => 'The Wandering Few']);
        [, $curator, $event] = $this->listedOn($placeholder);

        $response = $this->get($this->url($placeholder));

        $response->assertOk()
            ->assertSee('The Wandering Few')
            ->assertSee($curator->name)
            ->assertSee($event->name)
            ->assertSee(__('messages.claim_strip_not_me'));
    }

    public function test_a_page_whose_dates_have_all_passed_still_names_its_creator(): void
    {
        // The strip's first line is the whole point of the page, and the creator is derived from
        // the events on it. When they have all been and gone the upcoming query is empty and a
        // second lookup answers instead - a branch nothing else reaches, and a common one: an act
        // listed once, months ago.
        $placeholder = $this->placeholder(['name' => 'Second On']);
        $organizer = $this->createOwner();
        $curator = $this->createRole($organizer, 'venue', ['name' => 'Ba-Be Bar']);
        $past = $this->createEvent($curator, [
            'name' => 'Last Winter',
            'creator_role_id' => $curator->id,
            'starts_at' => now()->subMonths(4)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);
        $past->roles()->attach($placeholder->id, ['is_accepted' => true]);

        $this->get($this->url($placeholder))
            ->assertOk()
            ->assertSee(__('messages.claim_strip_title', ['schedule' => 'Ba-Be Bar']))
            ->assertDontSee(__('messages.claim_strip_title_generic'))
            ->assertSee(__('messages.claim_strip_no_events'));
    }

    public function test_the_page_is_never_indexable(): void
    {
        // Even after an admin verifies the address by hand, which is one click away from the
        // ?owner=unclaimed list and used to flip the whole population to "index, follow".
        //
        // Two independent mechanisms hold this up and EITHER ONE is sufficient: the view passes
        // :no-index explicitly, and app-guest's own rule now asks about user_id as well as the
        // contact columns. Reverting one and finding this test still green does NOT mean it pins
        // nothing - verified by removing each alone (still passes) and both together (fails).
        // Keep both: the explicit prop states the intent, the layout rule catches a view that
        // forgets it.
        $placeholder = $this->placeholder(['email' => 'band@gmail.com', 'email_verified_at' => now()]);
        $this->listedOn($placeholder);

        $this->get($this->url($placeholder))
            ->assertOk()
            ->assertSee('noindex, nofollow', false)
            ->assertDontSee('index, follow"', false);
    }

    public function test_the_page_carries_nothing_that_belongs_to_an_owner(): void
    {
        $placeholder = $this->placeholder(['email' => 'band@gmail.com', 'phone' => '+15551234567']);
        $this->listedOn($placeholder);

        $html = $this->get($this->url($placeholder))->assertOk()->getContent();

        $this->assertStringNotContainsString('band@gmail.com', $html, 'a third party typed that address about another third party');
        $this->assertStringNotContainsString('+15551234567', $html);
        $this->assertStringNotContainsString('adsbygoogle', $html);
        $this->assertStringNotContainsString('subscribe-panel', $html);
        $this->assertStringNotContainsString(route('role.follow', ['subdomain' => $placeholder->subdomain]), $html);
    }

    public function test_only_the_root_of_an_unclaimed_schedule_answers(): void
    {
        $placeholder = $this->placeholder();
        [, , $event] = $this->listedOn($placeholder);

        // The events belong to the schedules that published them; their canonical pages are there.
        $this->get($this->url($placeholder).'/'.$event->slug)->assertRedirect();
        $this->get($this->url($placeholder).'?embed=1')->assertRedirect();
        $this->get($this->url($placeholder).'?graphic=1')->assertRedirect();
    }

    public function test_a_schedule_somebody_runs_but_never_verified_still_redirects(): void
    {
        // The population isClaimed() and hasRealOwner() disagree on. Printing "is this you?" on it
        // would be offering a stranger a page its owner is sitting in.
        $owner = $this->createOwner();
        $unverified = $this->createRole($owner, 'venue', ['email_verified_at' => null]);

        $this->assertFalse($unverified->isClaimed());
        $this->assertTrue($unverified->hasRealOwner());
        $this->get($this->url($unverified))->assertRedirect();
    }

    public function test_an_unclaimed_act_appears_on_the_event_that_listed_it(): void
    {
        // It used to be dropped from the page entirely, name and all, unless somebody had matched
        // a YouTube video to it - while an unclaimed VENUE has always rendered as plain text. This
        // link is also the only way anyone reaches a claim page without an invitation in hand.
        $placeholder = $this->placeholder(['name' => 'Second On']);
        [, $curator, $event] = $this->listedOn($placeholder);

        $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]).'/'.$event->slug.'?id='.\App\Utils\UrlUtils::encodeId($event->id))
            ->assertOk()
            ->assertSee('Second On')
            ->assertSee($placeholder->getClaimUrl(), false)
            // The target is noindex,nofollow and these rows are unbounded. app-guest records five
            // demo schedules taking ~165k of 637k Googlebot requests in 89 days; do not hand the
            // crawler an unbounded new surface from every event page on the platform.
            ->assertSee('rel="nofollow"', false);
    }

    public function test_a_bill_of_bare_names_does_not_become_a_stack_of_empty_cards(): void
    {
        // Showing the whole lineup is the point, but a card is a poor container for a name and
        // nothing else: eight acts typed in by a promoter would be eight white boxes holding one
        // line each, which is a worse page than the one this replaced. Anything with something to
        // show keeps its card; the rest become one compact list, and every name still links to its
        // own page.
        $organizer = $this->createOwner();
        $curator = $this->createRole($organizer, 'venue', ['name' => 'Ba-Be Bar']);
        $event = $this->createEvent($curator, ['name' => 'Festival Night', 'creator_role_id' => $curator->id]);

        $withBio = $this->placeholder(['name' => 'Headliner', 'description' => 'A band with a bio.']);
        $event->roles()->attach($withBio->id, ['is_accepted' => true]);

        $bare = collect(range(1, 5))->map(function ($i) use ($event) {
            $act = $this->placeholder(['name' => "Support Act $i"]);
            $event->roles()->attach($act->id, ['is_accepted' => true]);

            return $act;
        });

        $html = $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]).'/'.$event->slug.'?id='.\App\Utils\UrlUtils::encodeId($event->id))
            ->assertOk()
            ->getContent();

        $body = substr($html, strpos($html, '<body'));
        $cards = preg_match_all('/<div class="bg-white\/95 dark:bg-gray-900\/95 backdrop-blur-sm sm:rounded-2xl overflow-hidden">/', $body);
        $this->assertSame(1, $cards, 'only the act with something to show gets a card of its own');

        foreach ($bare->push($withBio) as $act) {
            $this->assertStringContainsString($act->name, $body);
            $this->assertStringContainsString($act->getClaimUrl(), $body, 'every name on the bill reaches its own page');
        }
    }

    public function test_an_unclaimed_acts_picture_is_rendered_once_not_twice(): void
    {
        // $hasTalentImage gates the big square hero fallback, whose job is to supply an image when
        // no talent card carries one. Now that an unclaimed act gets a card, that flag has to
        // follow the widened list: computing it from the old narrower set renders the same file
        // both in the card and as the fallback above it.
        $placeholder = $this->placeholder(['name' => 'Second On', 'profile_image_url' => 'https://example.com/act.jpg']);
        [, $curator, $event] = $this->listedOn($placeholder);

        $html = $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]).'/'.$event->slug.'?id='.\App\Utils\UrlUtils::encodeId($event->id))
            ->assertOk()
            ->getContent();

        // Count <img> tags only: og:image, twitter:image and the JSON-LD all legitimately name the
        // same file, and the JSON-LD block sits in the body.
        preg_match_all('/<img[^>]*act\\.jpg/', $html, $tags);
        $this->assertCount(1, $tags[0], 'the act\'s picture belongs in its card, not also above it');
    }

    public function test_a_deleted_placeholder_still_redirects(): void
    {
        $this->get($this->url($this->placeholder(['is_deleted' => true])))->assertRedirect();
    }

    public function test_the_claim_button_is_absent_when_there_is_nothing_to_verify(): void
    {
        $withContact = $this->placeholder(['email' => 'band@gmail.com']);
        $this->listedOn($withContact);
        $this->get($this->url($withContact))->assertOk()->assertSee(__('messages.claim_strip_cta'));

        $without = $this->placeholder();
        $this->listedOn($without);
        $this->get($this->url($without))->assertOk()
            ->assertDontSee(__('messages.claim_strip_cta'))
            ->assertSee(__('messages.claim_strip_no_contact'));
    }

    public function test_holding_the_address_on_the_row_claims_the_schedule(): void
    {
        $placeholder = $this->placeholder(['email' => 'band@gmail.com']);
        // Not createOwner() then ->email = ...: changing users.email un-verifies the account, and
        // holding the address is the entire proof this door accepts.
        $claimant = \App\Models\User::factory()->create(['email' => 'band@gmail.com', 'email_verified_at' => now()]);

        $this->actingAs($claimant)
            ->get(route('role.claim.start', ['subdomain' => $placeholder->subdomain]))
            ->assertRedirect();

        $placeholder->refresh();
        $this->assertSame($claimant->id, $placeholder->user_id);
        $this->assertTrue($placeholder->isClaimed());
        $this->assertDatabaseHas('audit_logs', ['action' => AuditService::SCHEDULE_CLAIM, 'model_id' => $placeholder->id]);
    }

    public function test_another_account_is_told_which_address_answers_without_being_shown_it(): void
    {
        $placeholder = $this->placeholder(['email' => 'band@gmail.com']);
        $stranger = $this->createOwner();

        $html = $this->actingAs($stranger)
            ->get(route('role.claim.start', ['subdomain' => $placeholder->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('band@gmail.com', $html);
        $this->assertStringContainsString('b***@g***.com', $html);
        $this->assertNull($placeholder->fresh()->user_id);
    }

    public function test_holding_the_address_takes_the_page_down(): void
    {
        $placeholder = $this->placeholder(['email' => 'band@gmail.com']);
        $holder = \App\Models\User::factory()->create(['email' => 'band@gmail.com', 'email_verified_at' => now()]);

        $this->actingAs($holder)
            ->post(route('role.claim.not_me.submit', ['subdomain' => $placeholder->subdomain]))
            ->assertRedirect();

        $this->assertTrue((bool) $placeholder->fresh()->is_deleted);
        $this->assertDatabaseHas('audit_logs', ['action' => AuditService::SCHEDULE_TAKEDOWN, 'model_id' => $placeholder->id]);
    }

    public function test_anyone_else_reporting_a_page_records_it_without_removing_it(): void
    {
        // Otherwise this is a takedown button anyone can aim at a competitor.
        $placeholder = $this->placeholder(['email' => 'band@gmail.com']);
        $stranger = $this->createOwner();

        $this->actingAs($stranger)
            ->post(route('role.claim.not_me.submit', ['subdomain' => $placeholder->subdomain]))
            ->assertRedirect();

        $this->assertFalse((bool) $placeholder->fresh()->is_deleted);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditService::SCHEDULE_TAKEDOWN_REQUESTED,
            'user_id' => $stranger->id,
            'model_id' => $placeholder->id,
        ]);
    }
}
