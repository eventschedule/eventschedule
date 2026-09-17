<?php

namespace Tests\Feature;

use App\Models\DismissedNextStep;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\FederationService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "List on the network": the prompt that asks a schedule owner to list their schedules once the
 * install has joined the network. Every schedule starts undecided and nothing is shared until
 * somebody says yes, which is why approved installs used to send nothing at all.
 */
class FederationListingPromptTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Federation is an instance-side feature; the suite runs as the nexus. The switch goes
        // on before any schedule exists: Role::saving refuses the column while it is off.
        config(['app.is_nexus' => false]);
        Setting::set('federation_enabled', '1');
        Setting::set('federation_status', 'approved');
        Http::fake();
    }

    private function service(): FederationService
    {
        return app(FederationService::class);
    }

    /** An undecided schedule with one event that would be shared once it is listed. */
    private function shareableSchedule(User $owner, string $name = 'Harbour Hall', array $roleAttrs = [])
    {
        $role = $this->createRole($owner, 'venue', array_merge(['name' => $name], $roleAttrs));

        $this->createEvent($role, [
            'name' => $name.' Opening Night',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ]);

        return $role;
    }

    private function hash(Role $role): string
    {
        return UrlUtils::encodeId($role->id);
    }

    private function dashboard()
    {
        return $this->get(route('home'))->assertOk();
    }

    private function schedulePage(Role $role, string $tab = 'schedule')
    {
        return $this->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => $tab]))->assertOk();
    }

    private function listUrl(): string
    {
        return route('home.federation_list');
    }

    /**
     * The prompt's own field for a schedule. A bare value="<hash>" is not enough: the Next steps
     * panel on the same dashboard carries schedule hashes in its dismiss forms.
     */
    private function offered(Role $role): string
    {
        return 'name="schedules[]" value="'.$this->hash($role).'"';
    }

    // ------------------------------------------------------------------ where it shows

    public function test_the_dashboard_offers_an_undecided_schedule_with_something_to_share(): void
    {
        $owner = $this->createOwner();
        $role = $this->shareableSchedule($owner);

        $content = $this->actingAs($owner)->dashboard()
            ->assertSee($this->listUrl(), false)
            ->assertSee(trans_choice('messages.federation_listing_prompt_title', 1, ['name' => $role->name, 'count' => 1]))
            ->getContent();

        // The next-steps panel renders submit buttons of its own, so look for THIS one: the
        // brand button that submits the listing form, and the schedule it would list.
        $this->assertMatchesRegularExpression('/<button[^>]*type="submit"[^>]*form="federation-list-form"|<button[^>]*form="federation-list-form"[^>]*type="submit"/', $content);
        $this->assertStringContainsString($this->offered($role), $content);
    }

    public function test_several_schedules_are_all_named_with_a_checkbox_each(): void
    {
        $owner = $this->createOwner();
        $a = $this->shareableSchedule($owner, 'Harbour Hall');
        $b = $this->shareableSchedule($owner, 'Riverside Stage');

        $content = $this->actingAs($owner)->dashboard()
            ->assertSee(trans_choice('messages.federation_listing_prompt_title', 2, ['name' => $a->name, 'count' => 2]))
            ->assertSee('Harbour Hall')
            ->assertSee('Riverside Stage')
            ->getContent();

        foreach ([$a, $b] as $role) {
            $this->assertMatchesRegularExpression('/type="checkbox" name="schedules\[\]" value="'.preg_quote($this->hash($role), '/').'" checked/', $content);
        }
    }

    public function test_the_dashboard_is_quiet_when_there_is_nothing_to_ask(): void
    {
        $owner = $this->createOwner();

        // No qualifying event: a draft is never shared.
        $empty = $this->createRole($owner, 'venue', ['name' => 'Quiet Room']);
        $this->createEvent($empty, ['name' => 'Private Rehearsal', 'is_draft' => true, 'flyer_image_url' => 'f.jpg', 'creator_role_id' => $empty->id]);

        // Already decided, either way.
        $this->shareableSchedule($owner, 'Listed Hall', ['federation_enabled' => true]);
        $this->shareableSchedule($owner, 'Hidden Hall', ['federation_enabled' => false]);

        // Unlisted: listing it would publish nothing.
        $this->shareableSchedule($owner, 'Unlisted Hall', ['is_unlisted' => true]);

        $this->actingAs($owner)->dashboard()->assertDontSee($this->listUrl(), false);
    }

    public function test_the_prompt_waits_for_the_install_to_be_on_the_network(): void
    {
        $owner = $this->createOwner();
        $this->shareableSchedule($owner);
        $this->actingAs($owner);

        Setting::set('federation_status', 'suspended');
        $this->dashboard()->assertDontSee($this->listUrl(), false);

        Setting::set('federation_status', 'pending');
        $this->dashboard()->assertSee($this->listUrl(), false);

        Setting::set('federation_enabled', null);
        $this->dashboard()->assertDontSee($this->listUrl(), false);

        Setting::set('federation_enabled', '1');
        config(['app.is_nexus' => true]);
        $this->dashboard()->assertDontSee($this->listUrl(), false);
    }

    /** Dismissal is per schedule: one created later is still asked. */
    public function test_a_dismissed_schedule_is_not_offered_again_but_a_new_one_is(): void
    {
        $owner = $this->createOwner();
        $old = $this->shareableSchedule($owner, 'Harbour Hall');

        $this->actingAs($owner)
            ->post(route('home.federation_list_dismiss'), ['schedules' => [$this->hash($old)]])
            ->assertRedirect();

        $this->assertDatabaseHas('dismissed_next_steps', [
            'user_id' => $owner->id,
            'role_id' => $old->id,
            'step_type' => DismissedNextStep::FEDERATION_LISTING,
        ]);
        $this->assertNull($old->fresh()->federation_enabled, 'dismissing must not record a "Not listed"');
        $this->dashboard()->assertDontSee($this->listUrl(), false);

        $new = $this->shareableSchedule($owner, 'Riverside Stage');

        $this->dashboard()
            ->assertSee($this->listUrl(), false)
            ->assertSee($this->offered($new), false)
            ->assertDontSee($this->offered($old), false);
    }

    /**
     * The dashboard offers what the viewer OWNS. A schedule they only help run is theirs to list
     * from its own page, one at a time, and a viewer cannot list anything.
     */
    public function test_a_schedule_the_viewer_does_not_own_is_only_offered_on_its_own_page(): void
    {
        $customer = $this->createOwner();
        $role = $this->shareableSchedule($customer, 'Customer Club');

        $teamAdmin = $this->createOwner();
        $role->users()->attach($teamAdmin->id, ['level' => 'admin']);

        $this->actingAs($teamAdmin);
        $this->dashboard()->assertDontSee($this->listUrl(), false);
        $this->schedulePage($role)->assertSee($this->listUrl(), false);

        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $this->actingAs($viewer);
        $this->schedulePage($role)->assertDontSee($this->listUrl(), false);
    }

    public function test_the_schedule_page_asks_about_that_schedule_only(): void
    {
        $owner = $this->createOwner();
        $undecided = $this->shareableSchedule($owner, 'Harbour Hall');
        $decided = $this->shareableSchedule($owner, 'Listed Hall', ['federation_enabled' => true]);
        $this->actingAs($owner);

        $this->schedulePage($undecided)
            ->assertSee($this->listUrl(), false)
            ->assertSee($this->offered($undecided), false);

        $this->schedulePage($decided)->assertDontSee($this->listUrl(), false);

        // Every tab still renders: the view data is defined whichever tab is open.
        $this->schedulePage($undecided, 'team')->assertDontSee($this->listUrl(), false);
    }

    public function test_a_listed_schedule_says_so_and_links_to_the_setting(): void
    {
        $owner = $this->createOwner();
        $listed = $this->shareableSchedule($owner, 'Listed Hall', ['federation_enabled' => true]);
        $undecided = $this->shareableSchedule($owner, 'Harbour Hall');
        $this->actingAs($owner);

        $this->schedulePage($listed)
            ->assertSee(__('messages.federation_listed_on'))
            ->assertSee('settings_tab=advanced', false)
            ->assertSee('focus=federation_enabled', false);

        $this->schedulePage($undecided)->assertDontSee('settings_tab=advanced', false);

        Setting::set('federation_enabled', null);
        $this->schedulePage($listed)->assertDontSee('settings_tab=advanced', false);
    }

    // ------------------------------------------------------------------ what the button does

    public function test_listing_lists_exactly_the_schedules_posted(): void
    {
        $owner = $this->createOwner();
        $chosen = $this->shareableSchedule($owner, 'Harbour Hall');
        $unticked = $this->shareableSchedule($owner, 'Riverside Stage');

        $this->actingAs($owner)
            ->post($this->listUrl(), ['schedules' => [$this->hash($chosen)]])
            ->assertRedirect()
            ->assertSessionHas('message', trans_choice('messages.federation_listed_approved', 1, ['name' => 'Harbour Hall', 'count' => 1]));

        $this->assertTrue($chosen->fresh()->federation_enabled);
        $this->assertNull($unticked->fresh()->federation_enabled);
        $this->assertDatabaseHas('audit_logs', ['action' => 'schedule.update', 'model_id' => $chosen->id]);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'schedule.update', 'model_id' => $unticked->id]);
    }

    /** The whole point: an event that was not being shared is shared once its schedule is listed. */
    public function test_listing_is_what_unblocks_sharing(): void
    {
        $owner = $this->createOwner();
        $role = $this->shareableSchedule($owner);

        $this->assertCount(0, $this->service()->previewEvents());

        $this->actingAs($owner)->post($this->listUrl(), ['schedules' => [$this->hash($role)]]);

        $this->assertSame(['Harbour Hall Opening Night'], $this->service()->previewEvents()->pluck('name')->all());
    }

    /** A pending install's listings wait for approval, and the message says so. */
    public function test_a_pending_install_is_told_the_events_wait_for_approval(): void
    {
        Setting::set('federation_status', 'pending');
        $owner = $this->createOwner();
        $a = $this->shareableSchedule($owner, 'Harbour Hall');
        $b = $this->shareableSchedule($owner, 'Riverside Stage');

        $this->actingAs($owner)
            ->post($this->listUrl(), ['schedules' => [$this->hash($a), $this->hash($b)]])
            ->assertSessionHas('message', trans_choice('messages.federation_listed_pending', 2, ['name' => 'Harbour Hall', 'count' => 2]));
    }

    public function test_an_unverified_schedule_is_listed_with_a_warning(): void
    {
        $owner = $this->createOwner();
        $role = $this->shareableSchedule($owner, 'Harbour Hall', ['email_verified_at' => null]);

        $this->actingAs($owner)
            ->post($this->listUrl(), ['schedules' => [$this->hash($role)]])
            ->assertSessionHas('warning', trans_choice('messages.federation_listed_held_back', 1, ['name' => 'Harbour Hall', 'count' => 1]))
            ->assertSessionMissing('message');

        $this->assertTrue($role->fresh()->federation_enabled);
    }

    /**
     * Never "all of them" by accident, and never a schedule the user cannot edit. decodeId()
     * returns null for a malformed hash. A schedule that was already answered is the user's to
     * act on, so it is skipped quietly - an explicit "Not listed" is never overridden.
     */
    public function test_a_form_with_anything_it_may_not_list_lists_nothing(): void
    {
        $owner = $this->createOwner();
        $mine = $this->shareableSchedule($owner, 'Harbour Hall');
        $hidden = $this->shareableSchedule($owner, 'Hidden Hall', ['federation_enabled' => false]);
        $theirs = $this->shareableSchedule($this->createOwner(), 'Their Hall');
        $this->actingAs($owner);

        foreach ([[$this->hash($mine), $this->hash($theirs)], ['not-a-hash']] as $posted) {
            $this->post($this->listUrl(), ['schedules' => $posted])
                ->assertSessionHas('error', __('messages.not_authorized'));
        }

        $this->post($this->listUrl(), ['schedules' => [$this->hash($hidden)]])
            ->assertSessionMissing('error')
            ->assertSessionMissing('message');

        // Every box unticked: said so, rather than a validation bounce with nothing on screen.
        $this->post($this->listUrl(), ['schedules' => []])
            ->assertSessionHas('warning', __('messages.federation_listing_none_ticked'));

        $this->assertNull($mine->fresh()->federation_enabled);
        $this->assertFalse($hidden->fresh()->federation_enabled);
        $this->assertNull($theirs->fresh()->federation_enabled);
    }

    /** A double click, or a second tab: the repeat is neither an error nor a second toast. */
    public function test_a_repeat_submission_is_quiet(): void
    {
        $owner = $this->createOwner();
        $role = $this->shareableSchedule($owner);
        $this->actingAs($owner);

        $this->post($this->listUrl(), ['schedules' => [$this->hash($role)]])->assertSessionHas('message');

        $this->post($this->listUrl(), ['schedules' => [$this->hash($role)]])
            ->assertRedirect()
            ->assertSessionMissing('error')
            ->assertSessionMissing('message');

        $this->assertTrue($role->fresh()->federation_enabled);
    }

    /**
     * federatableQuery()'s role clause is an any-match. Without the eligibility check, an
     * unverified talent on a verified venue's event would be credited with that event and offered
     * a listing that publishes nothing.
     */
    public function test_a_schedule_that_would_publish_nothing_itself_is_not_offered(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', ['name' => 'Harbour Hall']);
        $event = $this->createEvent($venue, ['name' => 'Harbour Hall Opening Night', 'flyer_image_url' => 'f.jpg', 'creator_role_id' => $venue->id]);

        $talentOwner = $this->createOwner();
        $talent = $this->createRole($talentOwner, 'talent', ['name' => 'The Unverified Trio', 'email_verified_at' => null]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $this->assertSame([], $this->service()->shareableCounts([$talent->id]));
        $this->assertSame([$venue->id => 1], $this->service()->shareableCounts([$venue->id]));

        $this->actingAs($talentOwner)->dashboard()->assertDontSee($this->listUrl(), false);
    }

    /** buildPayload() refuses an event with no image, so it is not counted as something to share. */
    public function test_an_event_without_any_image_is_not_counted(): void
    {
        $owner = $this->createOwner();
        $bare = $this->createRole($owner, 'venue', ['name' => 'Bare Room']);
        $this->createEvent($bare, ['name' => 'No Picture Night', 'creator_role_id' => $bare->id]);

        $this->assertSame([], $this->service()->shareableCounts([$bare->id]));
        $this->actingAs($owner)->dashboard()->assertDontSee($this->listUrl(), false);

        // The venue's own profile image is enough, as it is for a listing.
        Role::whereKey($bare->id)->toBase()->update(['profile_image_url' => 'venue.jpg']);
        $this->assertSame([$bare->id => 1], $this->service()->shareableCounts([$bare->id]));
    }

    /** Every offered name is shown with a ticked box, so the dashboard offers a batch at a time. */
    public function test_the_dashboard_offers_a_batch_at_a_time(): void
    {
        $owner = $this->createOwner();
        foreach (range(1, FederationService::LISTING_PROMPT_LIMIT + 2) as $i) {
            $this->shareableSchedule($owner, 'Stage '.$i);
        }

        $this->assertCount(FederationService::LISTING_PROMPT_LIMIT, $this->service()->listingPromptSchedules($owner));
    }

    /** The listing prompt's dismissals have their own route; the Next steps route cannot write them. */
    public function test_the_next_steps_route_cannot_write_a_listing_dismissal(): void
    {
        $owner = $this->createOwner();
        $role = $this->shareableSchedule($owner);

        $this->actingAs($owner)
            ->post(route('home.next_steps_dismiss'), ['schedule' => $this->hash($role), 'type' => DismissedNextStep::FEDERATION_LISTING])
            ->assertSessionHasErrors('type');

        $this->assertDatabaseCount('dismissed_next_steps', 0);
    }

    /** Listing an unlisted schedule would publish nothing, so it is never listed. */
    public function test_an_unlisted_schedule_is_not_listed(): void
    {
        $owner = $this->createOwner();
        $unlisted = $this->shareableSchedule($owner, 'Quiet Hall', ['is_unlisted' => true]);

        $this->actingAs($owner)
            ->post($this->listUrl(), ['schedules' => [$this->hash($unlisted)]])
            ->assertSessionMissing('message');

        $this->assertNull($unlisted->fresh()->federation_enabled);
    }

    public function test_nothing_is_listed_while_the_install_is_off_the_network(): void
    {
        $owner = $this->createOwner();
        $role = $this->shareableSchedule($owner);
        Setting::set('federation_enabled', null);

        $this->actingAs($owner)
            ->post($this->listUrl(), ['schedules' => [$this->hash($role)]])
            ->assertRedirect()
            ->assertSessionMissing('message');

        $this->assertNull($role->fresh()->federation_enabled);
    }

    /**
     * roles.updated_at is the schedule page's sitemap lastmod, and listing it on the network
     * does not change that page.
     */
    public function test_listing_does_not_touch_the_schedules_updated_at(): void
    {
        $owner = $this->createOwner();
        $role = $this->shareableSchedule($owner);
        Role::whereKey($role->id)->toBase()->update(['updated_at' => '2026-01-01 00:00:00']);

        $this->actingAs($owner)->post($this->listUrl(), ['schedules' => [$this->hash($role)]]);

        $this->assertSame('2026-01-01 00:00:00', $role->fresh()->updated_at->format('Y-m-d H:i:s'));
    }

    public function test_dismissing_a_schedule_the_user_cannot_edit_is_refused(): void
    {
        $owner = $this->createOwner();
        $theirs = $this->shareableSchedule($this->createOwner(), 'Their Hall');

        $this->actingAs($owner)
            ->post(route('home.federation_list_dismiss'), ['schedules' => [$this->hash($theirs)]])
            ->assertSessionHas('error', __('messages.not_authorized'));

        $this->assertDatabaseCount('dismissed_next_steps', 0);
    }
}
