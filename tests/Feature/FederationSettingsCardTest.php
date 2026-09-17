<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The network card on /admin/settings: choosing what to share in the same save that switches
 * sharing on, the connection panel, and the preview that says why an event has not gone yet.
 *
 * Its own class rather than part of FederationSettingsTest, whose setUp registers a catch-all
 * Http::fake() - stubs merge in order, so a test there could never answer register differently.
 */
class FederationSettingsCardTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const REGISTER = 'https://eventschedule.com/api/federation/register';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.is_nexus' => false]);
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    private function scheduleWithEvent(User $owner, string $name, array $roleAttrs = [], array $eventAttrs = []): Role
    {
        $role = $this->createRole($owner, 'venue', array_merge(['name' => $name], $roleAttrs));

        $this->createEvent($role, array_merge([
            'name' => $name.' Opening Night',
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ], $eventAttrs));

        return $role;
    }

    private function save(array $fields)
    {
        return $this->post(route('admin.settings.update'), array_merge([
            'federation_settings_submitted' => '1',
        ], $fields));
    }

    private function checkboxFor(Role $role): string
    {
        return '/name="list_schedules\[\]" value="'.preg_quote(UrlUtils::encodeId($role->id), '/').'"/';
    }

    private function tickedCheckboxFor(Role $role): string
    {
        return '/name="list_schedules\[\]" value="'.preg_quote(UrlUtils::encodeId($role->id), '/').'" checked/';
    }

    // ------------------------------------------------------------------ the checklist

    /** Several schedules at once, so only the operator's own: somebody else's is theirs to decide. */
    public function test_the_checklist_offers_only_the_admins_own_undecided_schedules(): void
    {
        Http::fake();
        $admin = $this->adminActing();
        Setting::set('federation_enabled', '1');

        $mine = $this->scheduleWithEvent($admin, 'Harbour Hall');
        $mineDecided = $this->scheduleWithEvent($admin, 'Listed Hall', ['federation_enabled' => true]);
        // Listing it would publish nothing.
        $mineUnlisted = $this->scheduleWithEvent($admin, 'Quiet Hall', ['is_unlisted' => true]);
        $customers = $this->scheduleWithEvent($this->createOwner(), 'Customer Club');
        $coManaged = $this->scheduleWithEvent($this->createOwner(), 'Shared Space');
        $coManaged->users()->attach($admin->id, ['level' => 'admin']);

        $content = $this->get(route('admin.settings'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression($this->checkboxFor($mine), $content);
        $this->assertStringContainsString(e(trans_choice('messages.federation_would_share_count', 1, ['count' => 1])), $content);
        foreach ([$mineDecided, $mineUnlisted, $customers, $coManaged] as $notOffered) {
            $this->assertDoesNotMatchRegularExpression($this->checkboxFor($notOffered), $content);
        }

        // The other owners' schedules are counted instead, and told where they will be asked.
        $this->assertStringContainsString(e(trans_choice('messages.federation_undecided_others_count', 2, ['count' => 2])), $content);
    }

    /**
     * Ticked while sharing is off, so the save that switches it on carries them. Unticked once it
     * is on, so an unrelated save - a new contact email - never lists anything.
     */
    public function test_the_checklist_is_ticked_only_while_sharing_is_off(): void
    {
        Http::fake();
        $admin = $this->adminActing();
        $role = $this->scheduleWithEvent($admin, 'Harbour Hall');

        $this->assertMatchesRegularExpression($this->tickedCheckboxFor($role), $this->get(route('admin.settings'))->getContent());

        Setting::set('federation_enabled', '1');
        $content = $this->get(route('admin.settings'))->getContent();
        $this->assertMatchesRegularExpression($this->checkboxFor($role), $content);
        $this->assertDoesNotMatchRegularExpression($this->tickedCheckboxFor($role), $content);
    }

    public function test_switching_sharing_on_lists_the_ticked_schedules_in_the_same_save(): void
    {
        Http::fake([self::REGISTER => Http::response(['status' => 'pending', 'registered' => true], 201)]);
        $admin = $this->adminActing();
        $ticked = $this->scheduleWithEvent($admin, 'Harbour Hall');
        $unticked = $this->scheduleWithEvent($admin, 'Riverside Stage');

        $this->save([
            'federation_enabled' => '1',
            'federation_contact_email' => 'ops@operator.test',
            'list_schedules' => [UrlUtils::encodeId($ticked->id)],
        ])
            ->assertRedirect(route('admin.settings').'#federation')
            ->assertSessionHas('message', __('messages.federation_saved_enabled', ['email' => 'ops@operator.test'])
                .' '.trans_choice('messages.federation_saved_listed', 1, ['count' => 1, 'name' => 'Harbour Hall']));

        $this->assertTrue($ticked->fresh()->federation_enabled);
        $this->assertNull($unticked->fresh()->federation_enabled);
        Http::assertSent(fn ($request) => $request->url() === self::REGISTER);
    }

    public function test_the_card_cannot_list_a_schedule_it_did_not_offer(): void
    {
        Http::fake();
        $admin = $this->adminActing();
        Setting::set('federation_enabled', '1');

        // Editable by the admin, but owned by someone else: the dashboard prompt or its own page
        // is where that is decided, not a batch on the operator's card.
        $coManaged = $this->scheduleWithEvent($this->createOwner(), 'Shared Space');
        $coManaged->users()->attach($admin->id, ['level' => 'admin']);

        $this->save([
            'federation_enabled' => '1',
            'list_schedules' => [UrlUtils::encodeId($coManaged->id), 'not-a-hash'],
        ])->assertRedirect();

        $this->assertNull($coManaged->fresh()->federation_enabled);
    }

    public function test_ticks_do_nothing_while_sharing_stays_off(): void
    {
        Http::fake();
        $admin = $this->adminActing();
        $role = $this->scheduleWithEvent($admin, 'Harbour Hall');

        $this->save(['list_schedules' => [UrlUtils::encodeId($role->id)]])->assertRedirect();

        $this->assertNull($role->fresh()->federation_enabled);
    }

    // ------------------------------------------------------------------ saving

    public function test_switching_on_without_an_address_asks_for_one(): void
    {
        Http::fake();
        $this->adminActing();

        $this->save(['federation_enabled' => '1'])
            ->assertSessionHas('message', __('messages.federation_saved_enabled_no_email'));
    }

    public function test_switching_off_says_the_listings_are_coming_down(): void
    {
        Http::fake();
        $this->adminActing();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_instance_id', (string) \Illuminate\Support\Str::uuid());

        $this->save([])
            ->assertRedirect(route('admin.settings').'#federation')
            ->assertSessionHas('message', __('messages.federation_saved_disabled'));
    }

    /** A leftover withdrawal would take everything down and send it all again on the next run. */
    public function test_switching_on_clears_a_withdrawal_that_is_still_queued(): void
    {
        Http::fake();
        $this->adminActing();
        Setting::set('federation_withdraw_pending', '1');

        $this->save(['federation_enabled' => '1']);

        $this->assertNull(Setting::get('federation_withdraw_pending'));
    }

    /**
     * The address used to travel only when sharing was switched on, so an operator who added one
     * later could never be told they were approved.
     */
    public function test_a_new_contact_email_is_sent_to_the_network(): void
    {
        Http::fake([self::REGISTER => Http::response(['status' => 'approved', 'registered' => true])]);
        $this->adminActing();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_contact_email', 'old@operator.test');
        Setting::set('federation_status', 'approved');

        $this->save(['federation_enabled' => '1', 'federation_contact_email' => 'new@operator.test'])
            ->assertSessionHas('message', __('messages.settings_saved'));

        Http::assertSent(fn ($request) => $request->url() === self::REGISTER
            && json_decode($request->body(), true)['contact_email'] === 'new@operator.test');
    }

    public function test_an_unchanged_or_cleared_email_is_not_resent(): void
    {
        Http::fake();
        $this->adminActing();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_contact_email', 'ops@operator.test');
        // What the network already holds.
        Setting::set('federation_registered_email', 'ops@operator.test');

        $this->save(['federation_enabled' => '1', 'federation_contact_email' => 'OPS@operator.test ']);
        $this->save(['federation_enabled' => '1', 'federation_contact_email' => '']);

        Http::assertNothingSent();
    }

    /** Re-registering from a moved address sends an approved install back for review. */
    public function test_losing_approval_on_a_resend_is_a_warning(): void
    {
        Http::fake([self::REGISTER => Http::response(['status' => 'pending', 'registered' => true])]);
        $this->adminActing();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_contact_email', 'old@operator.test');
        Setting::set('federation_status', 'approved');

        $this->save(['federation_enabled' => '1', 'federation_contact_email' => 'new@operator.test'])
            ->assertSessionHas('warning', __('messages.federation_rereview_warning'))
            ->assertSessionMissing('message');
    }

    /**
     * The admin password confirm keeps a query string through intended(), not a fragment - so
     * walk the real flow, from an unconfirmed session to the card.
     */
    public function test_the_card_link_survives_the_password_confirm(): void
    {
        $admin = $this->createOwner(true);
        $this->actingAs($admin);
        $cardUrl = route('admin.settings', ['card' => 'federation']);

        $this->get($cardUrl)->assertRedirect(route('admin.password.confirm.show'));

        $this->post(route('admin.password.confirm'), ['password' => 'password'])
            ->assertRedirect($cardUrl);

        $this->get($cardUrl)->assertRedirect(route('admin.settings').'#federation');
    }

    /** Switching an approved install back on is not a new review, and nothing is emailed. */
    public function test_switching_an_approved_install_back_on_says_so(): void
    {
        Http::fake([self::REGISTER => Http::response(['status' => 'approved', 'registered' => true])]);
        $this->adminActing();
        Setting::set('federation_instance_id', (string) \Illuminate\Support\Str::uuid());
        Setting::set('federation_status', 'approved');

        $this->save(['federation_enabled' => '1', 'federation_contact_email' => 'ops@operator.test'])
            ->assertSessionHas('message', __('messages.federation_saved_enabled_approved'));
    }

    /**
     * The network card does not carry the header/footer code, and must not write it: a copy of
     * the page opened before someone else saved new code would otherwise put the old code back.
     */
    public function test_a_network_card_save_leaves_the_header_code_alone(): void
    {
        Http::fake();
        $this->adminActing();
        Setting::set('custom_header_code', '<!-- saved in another tab -->');

        $this->save(['federation_enabled' => '1'])->assertRedirect();

        $this->assertSame('<!-- saved in another tab -->', Setting::get('custom_header_code'));
    }

    /** An address saved while the network was unreachable is sent on the next hourly run. */
    public function test_a_contact_email_that_failed_to_arrive_is_sent_on_the_next_sync(): void
    {
        $attempts = 0;
        Http::fake([
            self::REGISTER => function () use (&$attempts) {
                $attempts++;

                return $attempts === 1
                    ? Http::response(['error' => 'unavailable'], 503)
                    : Http::response(['status' => 'approved', 'registered' => true]);
            },
            'https://eventschedule.com/api/federation/*' => Http::response(['removed' => 0, 'missing' => [], 'status' => 'approved']),
        ]);
        $this->adminActing();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_contact_email', 'old@operator.test');
        Setting::set('federation_registered_email', 'old@operator.test');

        $this->save(['federation_enabled' => '1', 'federation_contact_email' => 'new@operator.test']);
        $this->assertSame('old@operator.test', Setting::get('federation_registered_email'));

        $this->artisan('federation:push')->assertSuccessful();

        $this->assertSame(2, $attempts);
        $this->assertSame('new@operator.test', Setting::get('federation_registered_email'));
        $this->assertSame(self::REGISTER, Http::recorded()[1][0]->url(), 'the retry comes before the sync');

        // Once it has arrived, the hourly run leaves registration alone.
        $this->artisan('federation:push')->assertSuccessful();
        $this->assertSame(2, $attempts);
    }

    /**
     * A successful withdrawal empties the network of this install's events, so none of them is
     * "sent" any more - without touching every event's updated_at.
     */
    public function test_a_successful_withdraw_clears_the_sent_markers(): void
    {
        Http::fake(['https://eventschedule.com/api/federation/*' => Http::response(['removed' => 1, 'missing' => [], 'status' => 'approved'])]);
        $admin = $this->adminActing();
        Setting::set('federation_enabled', '1');
        Setting::set('federation_instance_id', (string) \Illuminate\Support\Str::uuid());

        $role = $this->createRole($admin, 'venue', ['name' => 'Harbour Hall', 'federation_enabled' => true]);
        $event = $this->createEvent($role, ['name' => 'Already Out', 'flyer_image_url' => 'f.jpg', 'creator_role_id' => $role->id]);
        Event::whereKey($event->id)->toBase()->update([
            'federated_at' => now(),
            'federated_hash' => 'abc',
            'updated_at' => '2026-01-01 00:00:00',
        ]);

        $this->save([])->assertSessionHas('message', __('messages.federation_saved_disabled'));

        $row = Event::whereKey($event->id)->toBase()->first(['federated_at', 'federated_hash', 'updated_at']);
        $this->assertNull($row->federated_at);
        $this->assertNull($row->federated_hash);
        $this->assertSame('2026-01-01 00:00:00', (string) $row->updated_at);
    }

    /** A validation error keeps the admin's own ticks, not the defaults. */
    public function test_the_checklist_keeps_ticks_after_a_validation_error(): void
    {
        Http::fake();
        $admin = $this->adminActing();
        $kept = $this->scheduleWithEvent($admin, 'Harbour Hall');
        $unticked = $this->scheduleWithEvent($admin, 'Riverside Stage');

        $this->from(route('admin.settings'))->save([
            'federation_enabled' => '1',
            'federation_contact_email' => 'not-an-address',
            'list_schedules' => [UrlUtils::encodeId($kept->id)],
        ])->assertSessionHasErrors('federation_contact_email');

        $content = $this->get(route('admin.settings'))->getContent();
        $this->assertMatchesRegularExpression($this->tickedCheckboxFor($kept), $content);
        $this->assertDoesNotMatchRegularExpression($this->tickedCheckboxFor($unticked), $content);
        $this->assertNull(Setting::get('federation_enabled'));
    }

    public function test_the_other_cards_saves_are_shown(): void
    {
        $this->adminActing();

        $this->followingRedirects()
            ->post(route('admin.settings.update'), ['custom_header_code' => '<!-- x -->'])
            ->assertOk()
            ->assertSee(__('messages.settings_saved'));
    }

    // ------------------------------------------------------------------ what the card shows

    public function test_the_contact_email_starts_as_the_admins_own_until_the_first_connection(): void
    {
        $admin = $this->adminActing();
        $field = '/id="federation_contact_email"[^>]*value="'.preg_quote($admin->email, '/').'"|value="'.preg_quote($admin->email, '/').'"[^>]*id="federation_contact_email"/';

        $this->assertMatchesRegularExpression($field, $this->get(route('admin.settings'))->getContent());

        Setting::set('federation_instance_id', (string) \Illuminate\Support\Str::uuid());
        $this->assertDoesNotMatchRegularExpression($field, $this->get(route('admin.settings'))->getContent());
    }

    public function test_the_connection_panel_waits_for_the_first_connection(): void
    {
        $this->adminActing();

        $this->get(route('admin.settings'))->assertDontSee(__('messages.federation_never_synced'));

        Setting::set('federation_enabled', '1');
        Setting::set('federation_instance_id', (string) \Illuminate\Support\Str::uuid());
        Setting::set('federation_status', 'pending');

        $this->get(route('admin.settings'))
            ->assertSee(__('messages.federation_never_synced'))
            ->assertSee(__('messages.federation_state_pending'));
    }

    /** "Listed" is not "sent": an event with no picture is refused, and the preview says so. */
    public function test_the_preview_says_where_each_event_stands(): void
    {
        $admin = $this->adminActing();
        Setting::set('federation_enabled', '1');

        $role = $this->createRole($admin, 'venue', ['name' => 'Harbour Hall', 'federation_enabled' => true]);
        $this->createEvent($role, ['name' => 'Pictureless Gig', 'creator_role_id' => $role->id]);
        $sent = $this->createEvent($role, ['name' => 'Already Out', 'flyer_image_url' => 'f.jpg', 'creator_role_id' => $role->id]);
        Event::whereKey($sent->id)->update(['federated_at' => now()]);
        $this->createEvent($role, ['name' => 'Next In Line', 'flyer_image_url' => 'g.jpg', 'creator_role_id' => $role->id]);

        $content = $this->get(route('admin.settings'))->assertOk()->getContent();

        foreach ([
            'Pictureless Gig' => 'federation_pill_needs_image',
            'Already Out' => 'federation_pill_sent',
            'Next In Line' => 'federation_pill_next_sync',
        ] as $name => $pill) {
            // Tempered: name and pill inside the SAME list item, never the next one's pill.
            $inItem = '(?:(?!<\/li>).)*';
            $this->assertMatchesRegularExpression(
                '/<li[^>]*>'.$inItem.preg_quote($name, '/').$inItem.preg_quote(__('messages.'.$pill), '/').$inItem.'<\/li>/s',
                $content,
                "{$name} should be marked {$pill}"
            );
        }
    }

    public function test_the_empty_preview_says_what_to_do(): void
    {
        $admin = $this->adminActing();
        Setting::set('federation_enabled', '1');

        $this->get(route('admin.settings'))->assertSee(__('messages.federation_preview_empty_rules'), false);

        $this->scheduleWithEvent($admin, 'Harbour Hall');

        $this->get(route('admin.settings'))->assertSee(__('messages.federation_preview_empty_tick'));
    }

    /**
     * The link comes from the network in every response, and the page renders it, so only one
     * pointing at the network this install is configured for is ever kept.
     */
    public function test_only_a_listings_link_on_the_configured_network_is_kept_and_shown(): void
    {
        $admin = $this->adminActing();
        Setting::set('federation_enabled', '1');
        $role = $this->createRole($admin, 'venue', ['name' => 'Harbour Hall', 'federation_enabled' => true]);
        $event = $this->createEvent($role, ['name' => 'Already Out', 'flyer_image_url' => 'f.jpg', 'creator_role_id' => $role->id]);

        $good = 'https://eventschedule.com/browse?instance=abc#network';
        $attempts = 0;
        Http::fake([
            self::REGISTER => Http::response(['status' => 'approved', 'registered' => true, 'listings_url' => 'https://evil.test/browse']),
            'https://eventschedule.com/api/federation/*' => function () use (&$attempts, $good) {
                $attempts++;

                return Http::response(['accepted' => 1, 'skipped' => 0, 'removed' => 0, 'missing' => [], 'status' => 'approved', 'listings_url' => $good]);
            },
        ]);

        app(\App\Services\FederationService::class)->register();
        $this->assertNull(Setting::get('federation_listings_url'));

        $this->artisan('federation:push')->assertSuccessful();
        $this->assertSame($good, Setting::get('federation_listings_url'));
        $this->assertNotNull($event->fresh()->federated_at);

        $this->get(route('admin.settings'))
            ->assertSee(__('messages.federation_see_listings'))
            ->assertSee($good, false);
    }
}
