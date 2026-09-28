<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\OwnerDigest;
use App\Models\User;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The weekly owner digest (app:send-owner-digests).
 *
 * Like the activation nudges, it reaches people who have not asked for anything, so most of this
 * file is about who it must NOT reach: dormant schedules (542 of them), weeks with nothing to
 * report, owners who opted out, and the second run in a week.
 */
class OwnerDigestTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Queue::fake();
    }

    private function owner(array $attrs = []): User
    {
        $user = $this->createOwner();
        $user->forceFill(array_merge(['is_subscribed' => true], $attrs))->save();

        return $user->fresh();
    }

    private function run_(bool $apply = true): void
    {
        $this->artisan('app:send-owner-digests', $apply ? ['--apply' => true] : [])->assertExitCode(0);
    }

    /** The job's mailable, read the way the other mail tests read SendQueuedEmail. */
    private function digests(): array
    {
        $found = [];
        Queue::assertPushed(SendQueuedEmail::class, function ($job) use (&$found) {
            $mailable = (new \ReflectionProperty($job, 'mailable'))->getValue($job);
            if ($mailable instanceof OwnerDigest) {
                $found[] = [
                    'mailable' => $mailable,
                    'roleId' => (new \ReflectionProperty($job, 'roleId'))->getValue($job),
                ];
            }

            return true;
        });

        return $found;
    }

    public function test_an_active_owner_gets_one_digest_covering_their_schedules(): void
    {
        $owner = $this->owner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'The Venue']);
        $talent = $this->createRole($owner, 'talent', ['name' => 'The Band']);
        $this->createEvent($venue, ['name' => 'Friday Show', 'starts_at' => now()->addDays(3)->setTime(19, 0)->format('Y-m-d H:i:s'), 'creator_role_id' => $venue->id]);
        $this->createEvent($talent, ['starts_at' => now()->subDays(20)->format('Y-m-d H:i:s')]);
        DB::table('analytics_daily')->insert(['role_id' => $talent->id, 'date' => now()->subDay()->toDateString(), 'desktop_views' => 30, 'mobile_views' => 12, 'tablet_views' => 0, 'unknown_views' => 0]);

        $this->run_();

        $digests = $this->digests();
        $this->assertCount(1, $digests, 'one email per owner, however many schedules');
        $this->assertNull($digests[0]['roleId'], 'platform mailer, never a schedule\'s own SMTP');

        $sections = collect($digests[0]['mailable']->sections)->keyBy('name');
        $this->assertSame(['The Band', 'The Venue'], $sections->keys()->sort()->values()->all());
        $this->assertSame(42, $sections['The Band']['views']);
        $this->assertSame('Friday Show', $sections['The Venue']['upcoming'][0]['name']);
    }

    /** A running weekly series lists this week's dates, anchored however long ago. */
    public function test_a_running_series_lists_its_dates_this_week(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $this->createRecurringEvent($role, ['name' => 'Open Mic', 'starts_at' => now()->subDays(60)->setTime(20, 0)->format('Y-m-d H:i:s'), 'creator_role_id' => $role->id]);

        $this->run_();

        $upcoming = $this->digests()[0]['mailable']->sections[0]['upcoming'];
        $this->assertNotEmpty($upcoming);
        $this->assertSame('Open Mic', $upcoming[0]['name']);
        $this->assertLessThanOrEqual(5, count($upcoming));
    }

    /** 542 dormant schedules: an unbounded audience would be a mailshot to all of them. */
    public function test_a_dormant_schedule_gets_nothing(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->subDays(200)->format('Y-m-d H:i:s')]);
        DB::table('analytics_daily')->insert(['role_id' => $role->id, 'date' => now()->subDay()->toDateString(), 'desktop_views' => 5, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0]);

        $this->run_();

        Queue::assertNothingPushed();
    }

    /** Active, but a week with nothing to say is not worth an email. */
    public function test_a_week_with_nothing_to_report_sends_nothing_and_claims_nothing(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->subDays(20)->format('Y-m-d H:i:s')]);

        $this->run_();

        Queue::assertNothingPushed();
        $this->assertSame(0, DB::table('owner_digests')->count(), 'nothing sent, so the week stays open');
    }

    public function test_a_second_run_in_the_same_week_sends_nothing(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(2)->format('Y-m-d H:i:s')]);

        $this->run_();
        $this->run_();

        Queue::assertPushed(SendQueuedEmail::class, 1);
    }

    public function test_the_owner_can_turn_it_off_per_schedule(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(2)->format('Y-m-d H:i:s')]);
        $role->users()->updateExistingPivot($owner->id, ['notification_settings' => json_encode(['weekly_digest' => false])]);

        $this->run_();

        Queue::assertNothingPushed();
    }

    /** The setting defaults on, and the form round-trips it. */
    public function test_the_setting_defaults_on_and_saves(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);

        $this->assertTrue($role->getEditorsWantingNotification('weekly_digest')->contains('id', $owner->id));

        $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee('name="notification_weekly_digest"', false);
    }

    public function test_an_unsubscribed_owner_is_never_emailed(): void
    {
        $role = $this->createRole($this->owner(['is_subscribed' => false]));
        $this->createEvent($role, ['starts_at' => now()->addDays(2)->format('Y-m-d H:i:s')]);

        $this->run_();

        Queue::assertNothingPushed();
    }

    public function test_demo_schedules_are_never_included(): void
    {
        $role = $this->createRole($this->owner(), 'venue', ['subdomain' => 'demo-'.strtolower(\Illuminate\Support\Str::random(6))]);
        $this->createEvent($role, ['starts_at' => now()->addDays(2)->format('Y-m-d H:i:s')]);
        $demo = $this->createRole($this->owner(), 'venue', ['subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN]);
        $this->createEvent($demo, ['starts_at' => now()->addDays(2)->format('Y-m-d H:i:s')]);

        $this->run_();

        Queue::assertNothingPushed();
    }

    public function test_the_dry_run_neither_sends_nor_claims(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(2)->format('Y-m-d H:i:s')]);

        $this->run_(apply: false);

        Queue::assertNothingPushed();
        $this->assertSame(0, DB::table('owner_digests')->count());
    }

    public function test_it_does_nothing_on_a_selfhosted_install(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(2)->format('Y-m-d H:i:s')]);
        config(['app.hosted' => false]);

        $this->run_();

        Queue::assertNothingPushed();
    }

    /** Sign-ups and sales from the last seven days, a confirmed subscriber counted once. */
    public function test_it_counts_last_weeks_sign_ups_and_sales(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(2)->format('Y-m-d H:i:s'), 'creator_role_id' => $role->id]);
        $ticket = $this->createTicket($event, ['price' => 10]);
        $this->createSale($event, $role, ['payment_amount' => 10], $ticket);
        $this->createSale($event, $role, ['payment_method' => 'rsvp', 'payment_amount' => 0, 'email' => 'guest@example.com'], $ticket);
        $old = $this->createSale($event, $role, ['payment_amount' => 10, 'email' => 'old@example.com'], $ticket);
        DB::table('sales')->where('id', $old->id)->update(['created_at' => now()->subDays(30)]);

        $follower = $this->createOwner();
        $this->followRole($follower, $role);

        $this->run_();

        $section = $this->digests()[0]['mailable']->sections[0];
        $this->assertSame(1, $section['tickets']);
        $this->assertSame(1, $section['rsvps']);
        $this->assertSame(1, $section['followers']);
    }

    public function test_the_command_is_not_scheduled_on_either_rail(): void
    {
        foreach (['routes/console.php', 'app/Http/Controllers/AppController.php'] as $file) {
            $this->assertStringNotContainsString(
                "Artisan::call('app:send-owner-digests'", file_get_contents(base_path($file)),
                "{$file} schedules the digest; it is meant to be hand-run until a production pass has been read"
            );
        }
    }

    public function test_the_mail_renders_in_every_language(): void
    {
        $owner = $this->owner();
        $sections = [[
            'name' => 'The Venue', 'url' => 'https://example.test/x', 'views' => 3, 'followers' => 1,
            'subscribers' => 0, 'tickets' => 2, 'rsvps' => 0, 'upcoming' => [['name' => 'Show', 'date' => 'Fri 2 Oct']],
        ]];
        $english = require base_path('resources/lang/en/messages.php');

        foreach (array_keys(config('app.supported_languages')) as $locale) {
            app()->setLocale($locale);
            $html = (new OwnerDigest($owner, $sections))->render();
            $this->assertStringNotContainsString('messages.', $html, "{$locale} is missing a key");

            if ($locale !== 'en') {
                $messages = require base_path("resources/lang/{$locale}/messages.php");
                foreach (['owner_digest_heading', 'owner_digest_intro', 'owner_digest_why', 'notify_weekly_digest_help'] as $key) {
                    $this->assertNotSame($english[$key], $messages[$key], "{$locale}.{$key} is still English");
                }
            }
        }
    }
}
