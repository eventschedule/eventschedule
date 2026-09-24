<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule address that serves nothing answers 404, not a redirect into the app.
 *
 * A missing, deleted or unpublished schedule used to 302 to app_url(), which lands on the login
 * page: a crawler files that as a soft 404 against the app host and keeps the dead URL, and three
 * of the sitemap's event URLs did exactly that in production. Only the schedule's own people - who
 * can do something about an unpublished schedule - are still sent into the app. A claimable
 * placeholder is its own case (UnclaimedSchedulePageTest) and is unchanged.
 */
class ScheduleNotFoundResponseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function galleryUrl(Role $role, $event): string
    {
        return $this->guestEventUrl($role, $event).'/photos';
    }

    public function test_an_unknown_schedule_is_a_404(): void
    {
        $this->get('/no-such-schedule-here')->assertNotFound();
        $this->get('/no-such-schedule-here/some-event')->assertNotFound();
        $this->get('/no-such-schedule-here/some-event/'.UrlUtils::encodeId(1).'/photos')->assertNotFound();
    }

    public function test_a_deleted_schedule_is_a_404_on_every_page(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'fan_photos_enabled' => true]);
        $this->get($this->galleryUrl($role, $event))->assertOk();

        Role::whereKey($role->id)->update(['is_deleted' => true]);

        $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))->assertNotFound();
        $this->get($this->guestEventUrl($role, $event))->assertNotFound();
        $this->get($this->galleryUrl($role, $event))->assertNotFound();
    }

    /**
     * Owned, but no contact was ever verified, so the schedule is not public: its own 404 for a
     * visitor, on the schedule, its events and their galleries alike.
     */
    public function test_an_unpublished_schedule_is_its_own_404_for_a_visitor(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Coming Soon Hall', 'email_verified_at' => null]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'fan_photos_enabled' => true]);

        $this->assertFalse($role->isClaimed());
        $this->assertFalse($role->isClaimable(), 'fixture: somebody really runs it, so no claim page');

        foreach ([
            route('role.view_guest', ['subdomain' => $role->subdomain]),
            $this->guestEventUrl($role, $event),
            $this->galleryUrl($role, $event),
        ] as $url) {
            $this->get($url)
                ->assertNotFound()
                ->assertSee('Coming Soon Hall')
                ->assertSee(__('messages.guest_not_found_heading'));
        }

        // Not counted as a dead link on the owner's analytics: the address is fine, the schedule is
        // just not public yet.
        $this->assertSame(0, DB::table('analytics_missing_daily')->count());
    }

    public function test_an_unpublished_schedule_still_sends_its_own_people_into_the_app(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'fan_photos_enabled' => true]);
        $admin = $this->createOwner(admin: true);

        foreach (['owner' => $owner, 'admin' => $admin] as $who => $user) {
            $this->actingAs($user)->get(route('role.view_guest', ['subdomain' => $role->subdomain]))
                ->assertRedirect(app_url());
            $this->actingAs($user)->get($this->galleryUrl($role, $event))
                ->assertRedirect(app_url());
        }

        // A signed-in stranger is a visitor.
        $this->actingAs($this->createOwner())->get(route('role.view_guest', ['subdomain' => $role->subdomain]))
            ->assertNotFound();
    }

    /** A placeholder answers only at its root, and every deeper path - the gallery too - keeps its redirect. */
    public function test_a_placeholder_gallery_keeps_its_redirect(): void
    {
        $curator = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id, 'fan_photos_enabled' => true]);

        $placeholder = new Role;
        $placeholder->subdomain = 'placeholder-act-'.strtolower(\Illuminate\Support\Str::random(6));
        $placeholder->type = 'talent';
        $placeholder->name = 'The Wandering Few';
        $placeholder->timezone = 'America/New_York';
        $placeholder->save();
        $event->roles()->attach($placeholder->id, ['is_accepted' => true]);

        $this->assertTrue($placeholder->fresh()->isClaimable());

        $this->get($this->galleryUrl($placeholder, $event))->assertRedirect(app_url());
    }

    public function test_an_impossible_carpool_date_is_a_404_not_a_500(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['carpool_enabled' => true]);
        $series = $this->createRecurringEvent($role, ['creator_role_id' => $role->id]);
        $oneOff = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $url = fn ($event, $date) => route('carpool.index_date', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
            'date' => $date,
        ]);

        // The route accepts any \d{4}-\d{2}-\d{2}, and Carbon::parse() threw on this one.
        $this->get($url($series, '2026-13-45'))->assertNotFound();
        $this->get($url($series, '2026-02-30'))->assertNotFound();
        // A one-off event's ride board has no dated address, real date or not.
        $this->get($url($oneOff, '2026-13-45'))->assertNotFound();

        // The real ones still answer.
        $this->get($url($series, $series->nextOccurrenceFrom()))->assertOk();
        $this->get(route('carpool.index', ['subdomain' => $role->subdomain, 'event_hash' => UrlUtils::encodeId($oneOff->id)]))
            ->assertOk();
    }
}
