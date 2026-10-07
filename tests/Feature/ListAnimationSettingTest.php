<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Role;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * roles.list_animation: the "Event animation" an owner picks on Style > Branding, which the guest
 * page's event lists play as cards scroll into view (resources/css/list-reveal.css).
 *
 * The guest-page assertions target `activeListAnimation: listRevealMotionOk ? "<design>"`, the Vue
 * data key in role/partials/calendar.blade.php that every list root binds data-list-anim from.
 * NULL (every existing schedule) must stay "none": no live page starts animating on deploy.
 */
class ListAnimationSettingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function save(Role $role, $owner, array $extra = [])
    {
        return $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            array_merge([
                'name' => $role->name,
                'timezone' => $role->timezone,
                'email' => $role->email,
                'new_subdomain' => $role->subdomain,
            ], $extra)
        );
    }

    private function assertGuestAnimation(string $url, string $design): void
    {
        $this->get($url)
            ->assertOk()
            ->assertSee('activeListAnimation: listRevealMotionOk ? "'.$design.'"', false);
    }

    public function test_every_design_saves_and_an_unknown_one_is_rejected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        foreach (Role::LIST_ANIMATIONS as $design) {
            $this->save($role, $owner, ['list_animation' => $design])->assertSessionHasNoErrors();
            $this->assertSame($design, $role->fresh()->list_animation);
        }

        $this->save($role, $owner, ['list_animation' => 'spin'])->assertSessionHasErrors('list_animation');
        $this->assertSame('curtain', $role->fresh()->list_animation, 'A rejected value must leave the stored one alone.');
    }

    public function test_null_and_unknown_values_resolve_to_none(): void
    {
        $role = new Role;
        $this->assertSame('none', $role->listAnimation());

        $role->list_animation = 'spin';
        $this->assertSame('none', $role->listAnimation());

        $role->list_animation = 'deal';
        $this->assertSame('deal', $role->listAnimation());
    }

    public function test_the_guest_page_plays_the_saved_design_and_none_by_default(): void
    {
        $owner = $this->createOwner();
        $plain = $this->createRole($owner);
        $animated = $this->createRole($owner, 'venue', ['list_animation' => 'shine']);
        $this->createEvent($plain);
        $this->createEvent($animated);

        $this->assertGuestAnimation('/'.$plain->subdomain, 'none');
        $this->assertGuestAnimation('/'.$animated->subdomain, 'shine');
    }

    public function test_the_preview_param_overrides_the_saved_design_and_junk_is_ignored(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['list_animation' => 'rise']);
        $this->createEvent($role);

        $this->assertGuestAnimation('/'.$role->subdomain.'?list_animation=curtain', 'curtain');
        $this->assertGuestAnimation('/'.$role->subdomain.'?list_animation=CURTAIN', 'curtain');
        $this->assertGuestAnimation('/'.$role->subdomain.'?list_animation=spin', 'rise');
        $this->assertGuestAnimation('/'.$role->subdomain.'?list_animation[]=deal', 'rise');
    }

    public function test_the_admin_views_never_animate(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['list_animation' => 'deal']);
        $this->createEvent($role);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertSee('activeListAnimation: listRevealMotionOk ? "none"', false);

        // The dashboard includes the same partial with no schedule at all.
        $this->actingAs($owner)->get(route('home'))->assertOk();
    }

    public function test_the_edit_page_hands_the_preview_the_owners_own_events(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['list_animation' => 'slide', 'accent_color' => '#FF5500']);
        $this->createEvent($role, ['name' => 'Opening Night']);
        $this->createEvent($role, ['name' => 'Pending Request', 'is_accepted' => false]);

        $response = $this->actingAs($owner)->get('/'.$role->subdomain.'/edit')->assertOk();

        $events = collect($response->viewData('listAnimationPreviewEvents'));
        $this->assertSame(['Opening Night'], $events->pluck('name')->all(), 'Only accepted events belong in the preview.');
        $this->assertNotSame('', $events->first()['day']);

        $html = $response->getContent();
        $this->assertStringContainsString('class="vue-list-animation-picker', $html);
        $this->assertMatchesRegularExpression('/id="list_animation_slide"[^>]*checked/s', $html);
        $this->assertStringContainsString('&quot;accentColor&quot;:&quot;#FF5500&quot;', $html);
    }

    public function test_the_preview_dates_events_in_the_schedules_timezone(): void
    {
        // 03:00 UTC on the 11th is 20:00 on the 10th in Los Angeles: the bare getStartDateTime()
        // returns UTC and would label the evening show with the next day.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'America/Los_Angeles']);
        $startsAt = now()->addMonth()->setDay(11)->setTime(3, 0);
        $this->createEvent($role, ['name' => 'Late Show', 'starts_at' => $startsAt->format('Y-m-d H:i:s'), 'creator_role_id' => $role->id]);

        $events = $this->actingAs($owner)->get('/'.$role->subdomain.'/edit')->assertOk()->viewData('listAnimationPreviewEvents');

        $this->assertSame('10', $events[0]['day']);
    }

    public function test_drafts_are_left_out_of_the_preview(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['name' => 'Published']);
        $this->createEvent($role, ['name' => 'Work In Progress', 'is_draft' => true]);

        $events = $this->actingAs($owner)->get('/'.$role->subdomain.'/edit')->assertOk()->viewData('listAnimationPreviewEvents');

        $this->assertSame(['Published'], array_column($events, 'name'));
    }

    public function test_the_event_pages_other_events_never_animate(): void
    {
        // The setting is about scrolling the schedule. The event page's "events" panel used to
        // be the same list partial, told to stay still; it is three plain rows drawn by the
        // server now (event/partials/more-events), with no list app to animate at all.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['list_animation' => 'deal']);
        $event = $this->createEvent($role, ['name' => 'Headliner']);
        $this->createEvent($role, ['name' => 'Next Week', 'starts_at' => now()->addDays(14)->setTime(12, 0)->format('Y-m-d H:i:s')]);

        $html = $this->get($event->getGuestUrl($role->subdomain).'?list_animation=curtain')->assertOk()->getContent();

        $rows = substr($html, strpos($html, 'id="gp-upcoming-events"'), 4500);
        $this->assertStringContainsString('Next Week', $rows, 'fixture: the other event is offered');
        $this->assertStringNotContainsString('data-list-anim', $html);
        $this->assertStringNotContainsString('activeListAnimation', $html);
        $this->assertStringNotContainsString('listRevealMotionOk', $html);
    }

    public function test_choosing_a_new_design_flashes_the_share_card(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->save($role, $owner, ['list_animation' => 'curtain'])
            ->assertSessionHas('list_animation_saved', 'curtain');

        // Saving again without a change, or switching animations off, is not a moment to share.
        $this->save($role, $owner, ['list_animation' => 'curtain'])->assertSessionMissing('list_animation_saved');
        $this->save($role, $owner, ['list_animation' => 'none'])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('list_animation_saved');
        $this->assertSame('none', $role->fresh()->list_animation, 'The save must have gone through for the missing flash to mean anything.');
    }

    public function test_an_unclaimed_schedule_gets_no_share_card(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['email_verified_at' => null]);

        $this->save($role, $owner, ['list_animation' => 'rise'])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('list_animation_saved');
        $this->assertSame('rise', $role->fresh()->list_animation, 'The save must have gone through for the missing flash to mean anything.');
    }

    public function test_the_share_card_renders_with_a_copyable_link(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['list_animation' => 'deal']);

        $html = $this->actingAs($owner)
            ->withSession(['list_animation_saved' => 'deal'])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.list_animation_share_title'), $html);
        $this->assertStringContainsString('id="list-animation-share-url"', $html);
        $this->assertStringContainsString('value="'.e($role->getGuestUrl(true)).'"', $html);

        // A forged or stale flash value renders nothing.
        $html = $this->actingAs($owner)
            ->withSession(['list_animation_saved' => '<b>x</b>'])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('id="list-animation-share-url"', $html);
    }

    public function test_backup_round_trip_keeps_the_design_and_drops_a_bad_one(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['list_animation' => 'focus']);
        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];
        $this->assertSame('focus', $data['schedules'][0]['role']['list_animation']);

        // A hand-edited backup: an over-long value would fail the INSERT and abort the restore.
        $tampered = $data;
        $tampered['schedules'][0]['role']['list_animation'] = str_repeat('x', 40);

        foreach ([[$data, 'focus'], [$tampered, null]] as [$payload, $expected]) {
            $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
            $svc->importSchedules($payload, [0], $owner->id, $importJob);

            $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
            $this->assertSame($expected, $restored->list_animation);
        }
    }
}
