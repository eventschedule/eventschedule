<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The owner's switch for the sponsors section (Engagement > Sponsors, roles.show_sponsors).
 *
 * Until it existed the section showed whenever a sponsor was stored, so taking it off the page for
 * a while meant deleting the sponsors and uploading them again. Off hides the section on the
 * schedule page and on every event that shows the SCHEDULE's sponsors. It deletes nothing, it
 * leaves an event's own list alone (that list has an off of its own), and it leaves a newsletter's
 * sponsors block alone (the owner placed that by hand).
 *
 * The switch is not a plan feature: a guest page prints stored sponsors on any plan, so a schedule
 * whose plan lapsed must still be able to hide them.
 *
 * Assertions key on id="gp-sponsors", the section's own id, never on a sponsor's name: the edit
 * page and the event form print the names too.
 */
class SponsorsVisibilityTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function sponsors(int $count = 2, string $prefix = 'Partner'): string
    {
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            // demo_ logos resolve to /images/demo/ without touching storage.
            $rows[] = ['name' => $prefix.' '.$i, 'logo' => 'demo_sponsor_'.$i.'.jpg', 'url' => 'https://partner'.$i.'.test', 'tier' => ''];
        }

        return json_encode($rows);
    }

    /** @return array{0: Role, 1: Event, 2: User} */
    private function sponsoredSchedule(array $attrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator', $attrs + ['sponsor_logos' => $this->sponsors()]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        return [$role, $event, $owner];
    }

    private function save(User $user, Role $role, array $fields = [])
    {
        return $this->actingAs($user)->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => 'en',
            'new_subdomain' => $role->subdomain,
        ], $fields));
    }

    public function test_the_column_defaults_to_shown(): void
    {
        // From the schema, not a saved Role: the default is what every existing schedule got.
        $defaults = collect(Schema::getColumns('roles'))->pluck('default', 'name');

        $this->assertSame('1', (string) $defaults['show_sponsors']);
    }

    public function test_by_default_the_section_is_on_both_pages(): void
    {
        [$role, $event] = $this->sponsoredSchedule();

        $this->get($role->getGuestUrl())->assertOk()->assertSee('id="gp-sponsors"', false);
        $this->get($event->getGuestUrl($role->subdomain))->assertOk()->assertSee('id="gp-sponsors"', false);
    }

    public function test_off_hides_the_section_on_both_pages_and_keeps_the_sponsors(): void
    {
        [$role, $event] = $this->sponsoredSchedule(['show_sponsors' => false]);

        $this->get($role->getGuestUrl())->assertOk()->assertDontSee('id="gp-sponsors"', false);
        $this->get($event->getGuestUrl($role->subdomain))->assertOk()->assertDontSee('id="gp-sponsors"', false);

        $this->assertCount(2, $role->fresh()->getSponsorLogos(), 'hiding the section deletes nothing');
        $this->assertSame([], $role->fresh()->shownSponsorLogos());
    }

    public function test_a_schedule_that_never_loaded_the_column_still_shows_its_sponsors(): void
    {
        [$role] = $this->sponsoredSchedule();

        $narrow = Role::query()->select(['id', 'sponsor_logos', 'subdomain'])->findOrFail($role->id);

        $this->assertCount(2, $narrow->shownSponsorLogos(), 'only an explicit off hides them');
    }

    public function test_an_events_own_sponsors_still_show_while_the_schedules_are_hidden(): void
    {
        [$role] = $this->sponsoredSchedule(['show_sponsors' => false]);
        $own = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'name' => 'Own Sponsors Night',
            'sponsor_mode' => 'custom',
            'sponsor_logos' => $this->sponsors(1, 'Event Backer'),
        ]);

        $this->get($own->getGuestUrl($role->subdomain))
            ->assertOk()
            ->assertSee('id="gp-sponsors"', false)
            ->assertSee('Event Backer 1');
    }

    public function test_a_newsletters_sponsors_are_not_switched_off_with_the_page(): void
    {
        // NewsletterService reads these two for its 'schedule' and 'first_event' sources. A block
        // an owner placed in a newsletter is not a page section.
        [$role, $event] = $this->sponsoredSchedule(['show_sponsors' => false]);

        $this->assertCount(2, $role->getSponsorLogos());
        $this->assertCount(2, $event->getEffectiveSponsorLogos($role));
        $this->assertSame([], $event->guestSponsorLogos($role));
    }

    public function test_the_owner_can_switch_it_off_and_on_and_the_sponsors_are_kept(): void
    {
        [$role, , $owner] = $this->sponsoredSchedule();

        // As the form posts it on a plan that can edit sponsors: the switch and the list together.
        $this->save($owner, $role, ['show_sponsors' => '0', 'existing_sponsors' => $role->sponsor_logos])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertFalse($role->show_sponsors);
        $this->assertCount(2, $role->getSponsorLogos());

        $this->save($owner, $role, ['show_sponsors' => '1', 'existing_sponsors' => $role->sponsor_logos])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertTrue($role->show_sponsors);
        $this->assertCount(2, $role->getSponsorLogos());
    }

    public function test_a_schedule_whose_plan_lapsed_can_still_hide_its_sponsors(): void
    {
        // Its guest page prints the stored sponsors on any plan, and its Sponsors row holds no
        // editor any more - so the switch is the one thing there, above the plan branch.
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner, 'curator', ['sponsor_logos' => $this->sponsors()]);
        $this->assertFalse($role->fresh()->isPro(), 'sanity check: the schedule is on the free plan');

        $this->get($role->getGuestUrl())->assertOk()->assertSee('id="gp-sponsors"', false);

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertSame(1, preg_match('~<input type="checkbox"[^>]*\bname="show_sponsors"[^>]*>~s', $html, $m), 'the switch is drawn for a free schedule that has sponsors');
        $this->assertStringContainsString('checked', $m[0]);
        $this->assertStringNotContainsString('name="existing_sponsors"', $html, 'the editor itself stays behind the plan');

        $this->save($owner, $role, ['show_sponsors' => '0'])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertFalse($role->show_sponsors);
        $this->assertCount(2, $role->getSponsorLogos(), 'a free save never touches the stored sponsors');

        auth()->logout();
        $this->get($role->getGuestUrl())->assertOk()->assertDontSee('id="gp-sponsors"', false);
    }

    public function test_the_switch_is_drawn_only_once_there_is_a_sponsor_to_hide(): void
    {
        $owner = $this->createOwner();
        $empty = $this->createRole($owner, 'venue');
        [$with] = $this->sponsoredSchedule(['user_id' => $owner->id]);
        $with->users()->syncWithoutDetaching([$owner->id => ['level' => 'owner']]);

        // The checkbox itself: the page's script names the field too, with or without it.
        $switch = '~<input type="checkbox"[^>]*\bname="show_sponsors"~s';

        $this->assertDoesNotMatchRegularExpression($switch, $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $empty->subdomain]))->assertOk()->getContent());

        $this->assertMatchesRegularExpression($switch, $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $with->subdomain]))->assertOk()->getContent());
    }

    public function test_an_empty_or_junk_value_is_refused_rather_than_saved(): void
    {
        // The column is NOT NULL: an empty value must fail validation, not the UPDATE.
        [$role, , $owner] = $this->sponsoredSchedule();

        $this->save($owner, $role, ['show_sponsors' => ''])->assertSessionHasErrors('show_sponsors');
        $this->save($owner, $role, ['show_sponsors' => 'abc'])->assertSessionHasErrors('show_sponsors');

        $this->assertTrue($role->fresh()->show_sponsors);
    }

    public function test_the_event_form_says_when_the_schedules_sponsors_are_hidden(): void
    {
        [$role, $event, $owner] = $this->sponsoredSchedule(['show_sponsors' => false]);

        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->assertSee('Partner 1, Partner 2 · '.__('messages.sponsors_hidden'));
    }

    public function test_a_backup_round_trip_keeps_the_switch(): void
    {
        // ROLE_EXPORT_FIELDS is an explicit allowlist: without the entry a restored schedule would
        // put back on show the sponsors its owner had hidden.
        [$role, , $owner] = $this->sponsoredSchedule(['show_sponsors' => false]);

        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $this->assertArrayHasKey('show_sponsors', $data['schedules'][0]['role']);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertFalse((bool) $restored->show_sponsors, 'hidden sponsors must stay hidden after a restore');
    }

    public function test_every_language_translates_the_new_strings(): void
    {
        // Read the language FILES: __() falls back to English, so a rendered check passes with a
        // key missing.
        $english = require resource_path('lang/en/messages.php');
        $keys = ['show_sponsors', 'show_sponsors_help', 'sponsors_hidden_notice', 'sponsors_hidden'];

        foreach (config('app.supported_languages') as $lang => $label) {
            $messages = require resource_path('lang/'.$lang.'/messages.php');

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $messages, "{$lang} is missing {$key}");

                if ($lang !== 'en') {
                    $this->assertNotSame($english[$key], $messages[$key], "{$lang}.{$key} is still the English string");
                }
            }
        }
    }

    /**
     * "Hidden" is about sponsors that exist. The switch is drawn only while there is one, so left
     * off after the last sponsor went it stayed off where nobody could see it: the next sponsors
     * were saved hidden, the row said "2", and the pages showed nothing.
     */
    public function test_emptying_the_list_puts_the_switch_back_on(): void
    {
        [$role, , $owner] = $this->sponsoredSchedule();

        // Hidden for the off season.
        $this->save($owner, $role, ['show_sponsors' => '0', 'existing_sponsors' => $role->sponsor_logos])->assertSessionHasNoErrors();
        $this->assertFalse($role->fresh()->show_sponsors);

        // Last season's sponsors removed. The form still drew the switch, and posts it off.
        $this->save($owner, $role, ['show_sponsors' => '0', 'existing_sponsors' => '[]'])->assertSessionHasNoErrors();
        $this->assertNull($role->fresh()->sponsor_logos);
        $this->assertTrue($role->fresh()->show_sponsors, 'with nothing to hide, the switch is on again');

        // New sponsors, from a form that drew no switch and so sends none.
        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('/<input type="checkbox"[^>]*name="show_sponsors"/', $html);

        $this->save($owner, $role, ['existing_sponsors' => $this->sponsors(2, 'New')])->assertSessionHasNoErrors();
        $this->assertCount(2, $role->fresh()->shownSponsorLogos(), 'and the new sponsors are on the page');
        $this->get($role->fresh()->getGuestUrl())->assertOk()->assertSee('id="gp-sponsors"', false);
    }
}
