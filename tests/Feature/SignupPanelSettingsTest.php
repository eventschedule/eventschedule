<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;
use App\Services\BackupService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The owner's two switches for the guest-page email sign-up surfaces (Settings > Advanced).
 *
 * - roles.show_subscribe_panel: the "Stay up to date" panel on the schedule page and at the foot of
 *   each event page. ON by default, and it only HIDES the panel.
 * - roles.show_event_interest: the "Tell me when tickets go on sale" card and the links that jump
 *   to it. OFF by default. The switch that counts is the event CREATOR's, wherever the event is
 *   listed (Event::offersInterestCapture()), and while it is off new sign-ups are refused.
 *
 * Assertions key on the id= and href= attributes, never on panel copy or the join URL: the follow
 * modal renders on every guest page whether or not it is opened, and it carries the same copy and
 * the same join URL, so a copy assertion stays green with the panel gone.
 */
class SignupPanelSettingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_columns_default_to_panel_on_and_card_off(): void
    {
        // Read from the schema rather than from a saved Role: the migration's default is what every
        // EXISTING schedule got, and nothing in a model round trip would notice it inverted.
        $defaults = collect(Schema::getColumns('roles'))->pluck('default', 'name');

        $this->assertSame('1', (string) $defaults['show_subscribe_panel']);
        $this->assertSame('0', (string) $defaults['show_event_interest']);
    }

    public function test_the_create_page_paints_both_defaults(): void
    {
        // The Advanced tab renders on the create page, and a toggle posts its hidden 0 whenever its
        // checkbox is unticked - so what this page paints is what store() saves.
        $html = $this->actingAs($this->createOwner())->get('/new/venue')->assertOk()->getContent();

        $this->assertTrue($this->toggleIsChecked($html, 'show_subscribe_panel'),
            'the sign-up panel toggle must render ON for a new schedule, or store() persists the hidden 0');
        $this->assertFalse($this->toggleIsChecked($html, 'show_event_interest'),
            'the "Notify me" card toggle must render OFF for a new schedule');
    }

    public function test_by_default_the_panel_shows_and_the_card_does_not(): void
    {
        [$role, $event] = $this->scheduleWithEvent();

        $this->get($role->getGuestUrl())
            ->assertOk()
            ->assertSee('id="gp-subscribe"', false);

        $this->get($event->getGuestUrl($role->subdomain))
            ->assertOk()
            ->assertSee('id="gp-subscribe"', false)
            ->assertDontSee('id="gp-event-interest"', false)
            ->assertDontSee('href="#gp-event-interest"', false);
    }

    public function test_turning_the_card_on_shows_it_and_its_links(): void
    {
        [$role, $event] = $this->scheduleWithEvent(['show_event_interest' => true]);

        $this->get($event->getGuestUrl($role->subdomain))
            ->assertOk()
            ->assertSee('id="gp-event-interest"', false)
            ->assertSee('href="#gp-event-interest"', false);
    }

    public function test_turning_the_panel_off_hides_it_on_both_pages(): void
    {
        [$role, $event] = $this->scheduleWithEvent(['show_subscribe_panel' => false, 'show_event_interest' => true]);

        $this->get($role->getGuestUrl())
            ->assertOk()
            ->assertDontSee('id="gp-subscribe"', false);

        // The card is its own switch, so it stays.
        $this->get($event->getGuestUrl($role->subdomain))
            ->assertOk()
            ->assertDontSee('id="gp-subscribe"', false)
            ->assertSee('id="gp-event-interest"', false);
    }

    public function test_a_schedule_with_no_stored_value_shows_the_panel(): void
    {
        // Only an explicit off hides it. A model without the attribute - never read from the table,
        // or read before the column existed, between a deploy and its migration - gets the column's
        // default, which is on. Rendered directly: no real request can produce such a model.
        [$role] = $this->scheduleWithEvent();
        unset($role->show_subscribe_panel);
        $this->assertNull($role->show_subscribe_panel);

        $this->assertStringContainsString('id="gp-subscribe"',
            view('partials.subscribe-panel', ['role' => $role])->render());
    }

    public function test_the_follow_link_still_opens_a_hidden_panel(): void
    {
        // ?subscribe=1 is what the Followers tab's QR code and "Your follow link" open. An owner
        // who printed that on a poster asked for the form on purpose.
        [$role] = $this->scheduleWithEvent(['show_subscribe_panel' => false]);

        $this->get($role->getGuestUrl().'?subscribe=1')
            ->assertOk()
            ->assertSee('id="gp-subscribe"', false);
    }

    public function test_a_pending_result_still_renders_on_a_hidden_panel(): void
    {
        // role.audience.join stays open with the panel off (the follow modal posts to it too), and
        // this panel is the only thing that shows the outcome of a plain form post.
        [$role] = $this->scheduleWithEvent(['show_subscribe_panel' => false]);

        $this->withSession(['subscribe_done' => $role->subdomain])
            ->get($role->getGuestUrl())
            ->assertOk()
            ->assertSee('id="gp-subscribe"', false)
            ->assertSee(__('messages.subscribe_done_heading'));

        // withSession() writes to a store every later request in this test shares.
        $this->flushSession();

        $this->withSession(['subscribe_error' => 'Something went wrong', 'subscribe_error_for' => $role->subdomain])
            ->get($role->getGuestUrl())
            ->assertOk()
            ->assertSee('id="gp-subscribe"', false)
            ->assertSee('Something went wrong');

        $this->flushSession();

        // A result that belongs to another schedule does not light this one up.
        $this->withSession(['subscribe_done' => 'someone-else'])
            ->get($role->getGuestUrl())
            ->assertOk()
            ->assertDontSee('id="gp-subscribe"', false);
    }

    public function test_the_card_takes_no_signups_while_it_is_off(): void
    {
        [$role, $event] = $this->scheduleWithEvent();

        $this->postJson($this->interestUrl($role), $this->interestPayload($event))
            ->assertOk()
            ->assertJson(['success' => false, 'message' => __('messages.event_interest_unavailable')]);

        $this->assertSame(0, EventInterest::count(), 'a direct POST must not build a list the owner never offered');

        $role->forceFill(['show_event_interest' => true])->save();

        $this->postJson($this->interestUrl($role), $this->interestPayload($event))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(1, EventInterest::count());
    }

    public function test_a_refused_signup_is_shown_where_the_card_was(): void
    {
        // The plain form post a tab opened before the switch was turned off still sends. The page it
        // lands back on no longer offers the card, so the answer has to render without it.
        [$role, $event] = $this->scheduleWithEvent();
        $eventUrl = $event->getGuestUrl($role->subdomain);

        $html = $this->from($eventUrl)
            ->followingRedirects()
            ->post($this->interestUrl($role), $this->interestPayload($event))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="gp-event-interest"', $html);
        $this->assertStringContainsString(e(__('messages.event_interest_unavailable')), $html);
        $this->assertStringNotContainsString('id="event_interest_email"', $html,
            'the card is off, so nothing may invite the visitor to try again');
        $this->assertSame(0, EventInterest::count());
    }

    public function test_the_creators_switch_decides_on_a_curators_page(): void
    {
        // The list and its mail belong to the event's CREATOR (SendEventInterestMail sends as
        // $event->creatorRole), so a curator that switched the card on must not start a list the
        // venue then emails - and a venue that switched it on must not lose the card on a page
        // that is not its own.
        $venue = $this->createRole($this->createOwner(), 'venue');
        $curator = $this->createCurator($this->createOwner(), ['show_event_interest' => true]);
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $curatorPage = $event->getGuestUrl($curator->subdomain);

        $this->get($curatorPage)
            ->assertOk()
            ->assertDontSee('id="gp-event-interest"', false)
            ->assertDontSee('href="#gp-event-interest"', false);

        $this->postJson($this->interestUrl($curator), $this->interestPayload($event))
            ->assertOk()
            ->assertJson(['success' => false]);
        $this->assertSame(0, EventInterest::count());

        $venue->forceFill(['show_event_interest' => true])->save();
        $curator->forceFill(['show_event_interest' => false])->save();

        $this->get($curatorPage)
            ->assertOk()
            ->assertSee('id="gp-event-interest"', false)
            ->assertSee('href="#gp-event-interest"', false);

        $this->postJson($this->interestUrl($curator), $this->interestPayload($event))
            ->assertOk()
            ->assertJson(['success' => true]);
        $this->assertSame(1, EventInterest::count());
    }

    public function test_the_owner_can_switch_both(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $url = route('role.update', ['subdomain' => $role->subdomain]);

        $this->actingAs($owner)->put($url, $this->rolePayload($role, [
            'show_subscribe_panel' => '0',
            'show_event_interest' => '1',
        ]))->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertFalse($role->show_subscribe_panel);
        $this->assertTrue($role->show_event_interest);

        $this->actingAs($owner)->put($url, $this->rolePayload($role, [
            'show_subscribe_panel' => '1',
            'show_event_interest' => '0',
        ]))->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertTrue($role->show_subscribe_panel);
        $this->assertFalse($role->show_event_interest);
    }

    public function test_an_empty_or_junk_value_is_refused_rather_than_saved(): void
    {
        // Both columns are NOT NULL. An empty value used to pass validation as null and fail the
        // UPDATE with a 500; the create form had no rule at all.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)
            ->put(route('role.update', ['subdomain' => $role->subdomain]), $this->rolePayload($role, [
                'show_subscribe_panel' => '',
            ]))
            ->assertSessionHasErrors('show_subscribe_panel');

        $this->assertTrue($role->fresh()->show_subscribe_panel);

        $this->actingAs($owner)
            ->post(route('role.store'), [
                'name' => 'Junk Toggle Schedule',
                'email' => 'junk@gmail.com',
                'timezone' => 'America/New_York',
                'language_code' => 'en',
                'show_event_interest' => 'abc',
            ])
            ->assertSessionHasErrors('show_event_interest');

        $this->assertFalse(Role::where('name', 'Junk Toggle Schedule')->exists());
    }

    public function test_a_backup_round_trip_keeps_both_switches(): void
    {
        // ROLE_EXPORT_FIELDS is an explicit allowlist, and the two defaults point in opposite
        // directions - so a missing entry would flip whichever switch the owner had changed.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'show_subscribe_panel' => false,
            'show_event_interest' => true,
        ]);

        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $this->assertArrayHasKey('show_subscribe_panel', $data['schedules'][0]['role']);
        $this->assertArrayHasKey('show_event_interest', $data['schedules'][0]['role']);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertFalse((bool) $restored->show_subscribe_panel, 'a hidden sign-up panel must stay hidden after a restore');
        $this->assertTrue((bool) $restored->show_event_interest, 'a card the owner switched on must stay on after a restore');
    }

    public function test_the_migration_keeps_the_card_on_where_it_has_signups(): void
    {
        // Adding the column switched the card off everywhere. A schedule whose events already had
        // sign-ups keeps it: that is the CREATOR of such an event, not a schedule that merely lists it.
        $owner = $this->createOwner();
        $withSignups = $this->createRole($owner, 'venue');
        $withoutSignups = $this->createRole($owner, 'venue');
        $curator = $this->createCurator($owner);

        $asked = $this->createEvent($withSignups, ['creator_role_id' => $withSignups->id]);
        $asked->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->createEvent($withoutSignups, ['creator_role_id' => $withoutSignups->id]);
        $this->addInterest($asked);

        foreach ([$withSignups, $withoutSignups, $curator] as $role) {
            $this->assertFalse($role->fresh()->show_event_interest);
        }

        $migration = require database_path('migrations/2026_09_17_000003_keep_event_interest_on_where_it_has_signups.php');
        $migration->up();

        $this->assertTrue($withSignups->fresh()->show_event_interest);
        $this->assertFalse($withoutSignups->fresh()->show_event_interest);
        $this->assertFalse($curator->fresh()->show_event_interest, 'listing an event does not make it your list');
    }

    public function test_the_event_editor_says_when_new_signups_are_off(): void
    {
        // The waiting count keeps counting people who asked before the card was switched off, so the
        // editor has to say that nobody new can join.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $this->addInterest($event);
        $editUrl = route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]);

        $this->actingAs($owner)->get($editUrl)
            ->assertOk()
            ->assertSee(__('messages.event_interest_signups_off'))
            ->assertSee(route('role.edit', ['subdomain' => $role->subdomain]).'#section-settings', false);

        $role->forceFill(['show_event_interest' => true])->save();

        $this->actingAs($owner)->get($editUrl)
            ->assertOk()
            ->assertDontSee(__('messages.event_interest_signups_off'));
    }

    public function test_every_language_translates_the_new_strings(): void
    {
        // Read the language FILES: __() falls back to English, so a rendered check passes with a
        // key missing. Only the sentences are compared against English - a short label can
        // legitimately match in another language.
        $english = require resource_path('lang/en/messages.php');
        $sentences = ['show_subscribe_panel_help', 'show_event_interest_help', 'event_interest_unavailable', 'event_interest_signups_off'];

        foreach (config('app.supported_languages') as $lang => $label) {
            $messages = require resource_path('lang/'.$lang.'/messages.php');

            foreach (array_merge(['show_subscribe_panel', 'show_event_interest'], $sentences) as $key) {
                $this->assertArrayHasKey($key, $messages, "{$lang} is missing {$key}");
            }

            if ($lang === 'en') {
                continue;
            }

            foreach ($sentences as $key) {
                $this->assertNotSame($english[$key], $messages[$key], "{$lang}.{$key} is still the English string");
            }
        }
    }

    /** @return array{0: Role, 1: Event} */
    private function scheduleWithEvent(array $attrs = []): array
    {
        $role = $this->createRole($this->createOwner(), 'venue', $attrs);

        // creator_role_id is what the card's eligibility reads (and what scheduleTimezone() needs).
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        return [$role, $event];
    }

    private function addInterest(Event $event): EventInterest
    {
        return EventInterest::create([
            'event_id' => $event->id,
            'event_date' => $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
            'email' => 'waiting@fans.test',
            'source' => 'event_page',
            'confirmed_at' => now(),
            'token' => EventInterest::newToken(),
        ]);
    }

    private function interestUrl(Role $role): string
    {
        return route('event.interest.join', ['subdomain' => $role->subdomain]);
    }

    private function interestPayload(Event $event): array
    {
        return [
            'email' => 'fan@fans.test',
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
        ];
    }

    private function toggleIsChecked(string $html, string $name): bool
    {
        $this->assertSame(1, preg_match('~<input type="checkbox"[^>]*\bname="'.preg_quote($name, '~').'"[^>]*>~s', $html, $m),
            "the {$name} toggle did not render");

        return (bool) preg_match('~\bchecked\b~', $m[0]);
    }

    /** Copied from Stay22SettingsTest: the minimum RoleUpdateRequest accepts. */
    private function rolePayload(Role $role, array $overrides = []): array
    {
        return array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => 'en',
            // Required by RoleUpdateRequest; omitting it fails validation before the fields
            // under test are ever reached.
            'new_subdomain' => $role->subdomain,
        ], $overrides);
    }
}
