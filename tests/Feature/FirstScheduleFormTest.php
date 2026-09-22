<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /new/{type} renders a first-run form instead of the full settings page.
 *
 * The risk in that change is not the markup, it is RoleController::store(): it does
 * $role->fill($request->all()), so a field the new form stops posting is not "left at the column
 * default", it is whatever fill() and the code after it leave behind. Three of those are silent
 * and none of them were covered.
 *
 * So the load-bearing test here is the A/B: save a schedule through the new form, save one through
 * the field set the old form posted, and compare them column by column.
 */
class FirstScheduleFormTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The columns the hidden inputs exist to carry. */
    private const CARRIED = [
        'type',
        'require_account',
        'accept_requests',
        'background',
        // background_colors is deliberately absent: create() picks a RANDOM gradient per call, so
        // the two arms differ by design. That it is a usable gradient at all - rather than the
        // literal ", " store() writes when the colour fields are missing - is asserted on its own
        // below, which is the part that can actually break.
        'language_code',
        'use_24_hour_time',
        'event_layout',
        'announce_new_events',
        'show_subscribe_panel',
    ];

    private function distinctiveUser(): \App\Models\User
    {
        $user = $this->createOwner();

        // Deliberately NOT the column defaults ('en', 0). A user sitting on the defaults makes
        // "the field was carried" and "the field was dropped" produce the same row, so the A/B
        // below cannot see a missing input at all - which is exactly what happened first time.
        $user->forceFill(['language_code' => 'he', 'use_24_hour_time' => true])->save();

        return $user->fresh();
    }

    private function submitCreateForm(string $type, array $overrides = []): Role
    {
        $user = $this->distinctiveUser();
        $this->actingAs($user);

        // Exactly what role/create.blade.php posts.
        $role = new Role;
        $page = $this->get(route('new', ['type' => $type]))->assertOk();

        $payload = $this->fieldsFrom($page->getContent()) + $overrides;

        $this->post(route('role.store'), $payload)->assertRedirect();

        return Role::where('user_id', $user->id)->firstOrFail();
    }

    /** Scrape the form the way a browser would submit it, rather than hand-writing the payload. */
    private function fieldsFrom(string $html): array
    {
        preg_match_all('/<input[^>]*name="([^"]+)"[^>]*value="([^"]*)"[^>]*>/', $html, $m, PREG_SET_ORDER);

        $fields = [];
        foreach ($m as $match) {
            if ($match[1] === '_token') {
                continue;
            }
            $fields[$match[1]] = html_entity_decode($match[2], ENT_QUOTES);
        }

        return $fields;
    }

    public function test_the_create_page_renders_the_first_run_form(): void
    {
        $this->actingAs($this->createOwner());

        $html = $this->get(route('new', ['type' => 'venue']))->assertOk()->getContent();

        // The settings page is 9,680 lines and about two dozen sub-tabs. If /new/ is rendering it
        // again, these are the tell.
        $this->assertStringNotContainsString('class="details-tab', $html);
        $this->assertStringNotContainsString('class="integration-tab', $html);
        $this->assertStringContainsString('id="edit-form"', $html);
        $this->assertStringContainsString('id="name"', $html);
        $this->assertStringContainsString('id="address1"', $html, 'a venue still has to give an address');
    }

    public function test_a_talent_form_does_not_ask_for_an_address(): void
    {
        $this->actingAs($this->createOwner());

        $html = $this->get(route('new', ['type' => 'talent']))->assertOk()->getContent();

        $this->assertStringNotContainsString('id="address1"', $html);
    }

    /**
     * The A/B. Anything the hidden inputs stopped carrying shows up as a differing column.
     *
     * Mutate this by deleting one of the hidden inputs from role/create.blade.php: the matching
     * column diverges and this fails. A version of this test that only asserted "a Role exists"
     * passed with every one of them removed.
     */
    public function test_a_schedule_from_the_first_run_form_matches_one_from_the_full_form(): void
    {
        foreach (['venue', 'talent', 'curator'] as $type) {
            $viaCreate = $this->submitCreateForm($type, ['name' => 'Compare '.$type, 'address1' => '1 Test St']);

            // The same save, with the field set the full settings page posts. Only the values
            // RoleController::create() prefills are listed; everything else is the column default
            // in both cases.
            $user = $this->distinctiveUser();
            $this->actingAs($user);
            $this->post(route('role.store'), [
                'name' => 'Full '.$type,
                'type' => $type,
                'email' => $user->email,
                'timezone' => $user->timezone ?: config('app.timezone'),
                'language_code' => $user->language_code ?: 'en',
                'use_24_hour_time' => $user->use_24_hour_time ? 1 : 0,
                'address1' => '1 Test St',
                'require_account' => $type === 'curator' ? 1 : 0,
                'accept_requests' => 1,
                'background' => 'image',
                'background_colors' => '#111111, #222222',
                'event_layout' => 'list',
                'announce_new_events' => 1,
                'show_subscribe_panel' => 1,
            ])->assertRedirect();
            $viaFull = Role::where('user_id', $user->id)->firstOrFail();

            foreach (self::CARRIED as $column) {
                $this->assertEquals(
                    $viaFull->{$column},
                    $viaCreate->{$column},
                    $type.': '.$column.' differs between the first-run form and the full form'
                );
            }
        }
    }

    /**
     * store() writes the literal string ", " when all three colour fields are absent.
     *
     * `if (! $request->background_colors) { $role->background_colors = $custom1.', '.$custom2; }`
     * - hasConfiguredBackground() then reads ", " as configured and the guest page emits
     * `linear-gradient(150deg, , )`, which is not a declaration, so the schedule renders with no
     * background at all.
     */
    public function test_a_new_schedule_has_a_usable_background(): void
    {
        $role = $this->submitCreateForm('venue', ['name' => 'Background Test', 'address1' => '1 Test St']);

        $this->assertNotSame(', ', $role->background_colors);
        $this->assertNotEmpty(trim(str_replace(',', '', (string) $role->background_colors)));
    }

    /** Guests may submit to a new talent or venue without being made to create an account. */
    public function test_require_account_follows_the_type(): void
    {
        $this->assertFalse((bool) $this->submitCreateForm('venue', ['name' => 'Req Venue', 'address1' => '1 Test St'])->require_account);
        $this->assertFalse((bool) $this->submitCreateForm('talent', ['name' => 'Req Talent'])->require_account);
        $this->assertTrue((bool) $this->submitCreateForm('curator', ['name' => 'Req Curator'])->require_account);
    }

    /** The column default is true, and the old form's toggle posted 0 over it. */
    public function test_a_new_schedule_accepts_requests(): void
    {
        $this->assertTrue((bool) $this->submitCreateForm('venue', ['name' => 'Accepts', 'address1' => '1 Test St'])->accept_requests);
    }

    /**
     * A first schedule goes on to the event form rather than a dashboard.
     *
     * 91% of everyone who ever creates a schedule does it within an hour of signing up, so the
     * next step has to be in front of them while they are still there.
     */
    public function test_a_first_schedule_leads_to_the_event_form(): void
    {
        $user = $this->createOwner();
        $this->actingAs($user);

        $payload = $this->fieldsFrom($this->get(route('new', ['type' => 'venue']))->getContent());
        $payload['name'] = 'First One';
        $payload['address1'] = '1 Test St';

        $response = $this->post(route('role.store'), $payload);

        // Read the slug rather than predicting it: Role::generateSubdomain() takes the shortest
        // FREE left-to-right variation, so "First One" becomes "first" on an empty install and
        // "first-one" on a busy one.
        $role = Role::where('user_id', $user->id)->firstOrFail();

        $response->assertRedirect(route('event.create', ['subdomain' => $role->subdomain]));
    }

    /**
     * The redirect must not stamp the funnel stage it lands on.
     *
     * EventController::create() stamps users.event_form_viewed_at on every visit, and
     * GrowthExportService defines reached_event as that OR has-events. Left alone, sending every
     * new organizer to the event form would make reached_event exactly equal saved_schedule - a
     * stage permanently at 100%, and a silent change to what the rate below it is a fraction of.
     */
    public function test_the_post_save_redirect_does_not_stamp_the_event_form_stage(): void
    {
        $user = $this->distinctiveUser();
        $this->actingAs($user);

        $payload = $this->fieldsFrom($this->get(route('new', ['type' => 'venue']))->getContent());
        $payload['name'] = 'Stamp Test';
        $payload['address1'] = '1 Test St';

        $role = null;
        $this->followingRedirects()->post(route('role.store'), $payload)->assertOk();
        $role = Role::where('user_id', $user->id)->firstOrFail();

        $this->assertNull($user->fresh()->event_form_viewed_at,
            'the redirect stamped the stage, so reached_event now equals saved_schedule by construction');

        // Going there deliberately still counts, or the stage would measure nothing at all.
        $this->get(route('event.create', ['subdomain' => $role->subdomain]))->assertOk();

        $this->assertNotNull($user->fresh()->event_form_viewed_at);
    }

    /** A second schedule does not: that person has already been through onboarding. */
    public function test_a_later_schedule_still_lands_on_the_schedule_page(): void
    {
        $user = $this->createOwner();
        $this->createRole($user, 'talent');
        $this->actingAs($user);

        $payload = $this->fieldsFrom($this->get(route('new', ['type' => 'venue']))->getContent());
        $payload['name'] = 'Second One';
        $payload['address1'] = '1 Test St';

        $response = $this->post(route('role.store'), $payload);

        $role = Role::where('user_id', $user->id)->where('type', 'venue')->firstOrFail();

        $response->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']));
    }
}
