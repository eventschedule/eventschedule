<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
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
 *
 * And the 404 a deleted or unpublished schedule gives is the one a missing schedule gives, byte
 * for byte once the per-request tokens are out: the schedule's own 404 named it, which told
 * anybody who guessed the address that a schedule was sitting behind it.
 */
class ScheduleNotFoundResponseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function galleryUrl(Role $role, $event): string
    {
        return $this->guestEventUrl($role, $event).'/photos';
    }

    /**
     * The response without what changes per request - CSP nonces and CSRF tokens - and with the
     * subdomain it was asked for taken out, since the page echoing the address tells the visitor
     * only what they typed.
     *
     * @return array{status: int, body: string}
     */
    private function comparable(TestResponse $response, string $subdomain): array
    {
        $body = preg_replace(
            ['~nonce="[^"]*"~', '~(<meta name="csrf-token" content=")[^"]*~', '~(name="_token" value=")[^"]*~'],
            ['nonce=""', '$1', '$1'],
            (string) $response->getContent()
        );

        return ['status' => $response->getStatusCode(), 'body' => str_replace($subdomain, '{subdomain}', $body)];
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
     * Owned, but no contact was ever verified, so the schedule is not public: to a visitor it is
     * not there at all, on the schedule, its events and their galleries alike. ?lang= included -
     * the language redirect used to run first, so ?lang=zz or a second language answered this
     * schedule with a 302 and a missing one with a 404. And inside an iframe, the empty 404 an
     * embedded missing schedule gets.
     */
    public function test_an_unpublished_schedule_answers_a_visitor_as_a_missing_one_does(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Coming Soon Hall', 'email_verified_at' => null]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'fan_photos_enabled' => true]);

        $this->assertFalse($role->isClaimed());
        $this->assertFalse($role->isClaimable(), 'fixture: somebody really runs it, so no claim page');
        $this->assertSame('en', $role->language_code, 'fixture: es is not its language, so ?lang=es redirects');

        $missing = 'no-such-schedule-here';
        $eventPath = '/'.$event->slug.'/'.UrlUtils::encodeId($event->id);

        foreach (['the schedule' => '', 'an event' => $eventPath, 'a gallery' => $eventPath.'/photos'] as $page => $path) {
            foreach (['', '?lang=zz', '?lang=es'] as $query) {
                $unknown = $this->get('/'.$missing.$path.$query)->assertNotFound();
                $hidden = $this->get('/'.$role->subdomain.$path.$query)
                    ->assertNotFound()
                    ->assertDontSee('Coming Soon Hall');

                $this->assertSame(
                    $this->comparable($unknown, $missing),
                    $this->comparable($hidden, $role->subdomain),
                    "{$page}{$query}: not the 404 a missing schedule gets"
                );
            }

            foreach ([$missing, $role->subdomain] as $subdomain) {
                $embedded = $this->get('/'.$subdomain.$path.'?embed=1')->assertNotFound();
                $this->assertSame('', $embedded->getContent(), "{$page} of {$subdomain}, embedded");
            }
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
            // Straight into the app: the language redirect no longer comes first.
            $this->actingAs($user)->get(route('role.view_guest', ['subdomain' => $role->subdomain]).'?lang=zz')
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
