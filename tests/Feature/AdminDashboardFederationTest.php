<?php

namespace Tests\Feature;

use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Models\Setting;
use App\Services\AdminDashboard;
use App\Services\FederationStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The federation card of /admin/dashboard, which is three different cards.
 *
 * On eventschedule.com it is the hub's numbers: the installs that registered, what they list and
 * the clicks sent on to them (federation_clicks_daily, written since July and read by nothing
 * until this card). On any other install it is that install's own sharing. And where sharing is
 * switched off it is one line, with no event query run for a feature nobody is using.
 */
class AdminDashboardFederationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setTime(12, 0));
    }

    private function install(string $name, array $attrs = []): FederatedInstance
    {
        $instance = new FederatedInstance;
        $instance->forceFill($attrs + [
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://'.Str::slug($name).'.test',
            'name' => $name,
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ])->save();

        return $instance;
    }

    /** A listing that is live unless $attrs says otherwise: an image, and a date still ahead. */
    private function listing(FederatedInstance $instance, array $attrs = []): FederatedEvent
    {
        $listing = new FederatedEvent;
        $listing->forceFill($attrs + [
            'federated_instance_id' => $instance->id,
            'external_id' => Str::random(8),
            'url' => $instance->site_url.'/show',
            'name' => 'Summer Show',
            'next_occurrence_at' => now()->addWeek(),
            'image_path' => 'federated/'.Str::random(8).'.jpg',
        ])->save();

        return $listing;
    }

    private function clicks(FederatedInstance $instance, int $daysAgo, int $clicks): void
    {
        DB::table('federation_clicks_daily')->insert([
            'federated_instance_id' => $instance->id,
            'date' => now()->subDays($daysAgo)->toDateString(),
            'clicks' => $clicks,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Mutation: let federation() fall through to previewTotals() when sharing is off. */
    public function test_with_sharing_off_the_card_is_one_fact_and_no_event_query(): void
    {
        config(['app.is_nexus' => false]);
        $this->createEvent($this->createRole($this->createOwner()));

        DB::flushQueryLog();
        DB::enableQueryLog();
        $federation = (new AdminDashboard)->federation();
        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        $this->assertSame(['mode' => 'off'], $federation);
        $this->assertSame([], array_values(array_filter($queries, fn ($sql) => str_contains($sql, '`events`'))));
    }

    public function test_an_install_that_shares_sees_its_own_sharing(): void
    {
        config(['app.is_nexus' => false]);
        Setting::set('federation_enabled', '1');

        $federation = (new AdminDashboard)->federation();

        $this->assertSame('sender', $federation['mode']);
        $this->assertSame('not_connected', $federation['status']);
        $this->assertNull($federation['last_synced_at']);
        $this->assertFalse($federation['has_error']);
        $this->assertSame(['total', 'sent'], array_keys($federation['totals']));
        $this->assertIsInt($federation['undecided']);

        Setting::set('federation_status', 'approved');
        Setting::set('federation_last_synced_at', (string) now()->subHour());
        Setting::set('federation_last_error', 'The hub did not answer');

        $federation = (new AdminDashboard)->federation();

        $this->assertSame('approved', $federation['status']);
        $this->assertTrue($federation['last_synced_at']->equalTo(now()->subHour()));
        $this->assertTrue($federation['has_error']);
    }

    /**
     * Mutation: count live() where listable() is meant (a pending install publishes nothing), or
     * call a listing with no venue online, or count a schedule by its URL alone.
     */
    public function test_the_hub_counts_installs_and_what_is_really_listed(): void
    {
        config(['app.is_nexus' => true]);

        $alpha = $this->install('Alpha', ['last_seen_at' => now()->subDays(2), 'app_version' => 'v1.0.134']);
        $beta = $this->install('Beta', ['last_seen_at' => now()->subDays(40)]);
        $pending = $this->install('Gamma', ['status' => FederatedInstance::STATUS_PENDING, 'last_seen_at' => now()]);

        $this->listing($alpha, ['venue_name' => 'The Hall', 'schedule_url' => 'https://alpha.test/jazz']);
        $this->listing($alpha, ['is_online' => true, 'schedule_url' => 'https://alpha.test/jazz']);
        $this->listing($alpha, ['is_online' => true, 'venue_name' => 'The Hall', 'schedule_url' => 'https://alpha.test/folk']);
        // The same address on another install is another schedule.
        $this->listing($beta, ['venue_name' => 'The Shed', 'schedule_url' => 'https://alpha.test/jazz']);
        // No venue was sent and the sender did not say online: in person, as the listing page
        // reads it. Calling it online because it names no venue is the mistake this row is for.
        $this->listing($beta);

        // Not listed: blocked, no image yet, over, and anything from an install not approved.
        $this->listing($alpha, ['blocked_at' => now()]);
        $this->listing($alpha, ['image_path' => null]);
        $this->listing($alpha, ['next_occurrence_at' => now()->subDays(3)]);
        $this->listing($pending, ['venue_name' => 'Elsewhere']);

        $federation = (new AdminDashboard)->federation();

        $this->assertSame('hub', $federation['mode']);
        $this->assertEquals(['approved' => 2, 'pending' => 1], $federation['installs']['by_status']);
        $this->assertSame(1, $federation['installs']['active_30d']);
        $this->assertEquals(['v1.0.134' => 1, 'unknown' => 1], $federation['installs']['by_version']);
        $this->assertSame(
            ['live' => 5, 'schedules' => 3, 'online' => 1, 'hybrid' => 1, 'in_person' => 3],
            $federation['listings']
        );
    }

    /**
     * Thirty days including today against the thirty before, and the installs that received most.
     * Mutation: sum without the date bound, or rank installs by listings.
     */
    public function test_the_hub_counts_the_clicks_it_sent_on(): void
    {
        config(['app.is_nexus' => true]);

        // Beta lists more and is clicked less, so ranking by listings would put it first.
        $alpha = $this->install('Alpha');
        $beta = $this->install('Beta');
        $this->listing($alpha);
        $this->listing($alpha);
        $this->listing($alpha, ['blocked_at' => now()]);
        $this->listing($beta);
        $this->listing($beta);
        $this->listing($beta);

        $this->clicks($alpha, 0, 5);
        $this->clicks($alpha, 10, 7);
        $this->clicks($beta, 29, 2);     // the first day of the window
        $this->clicks($alpha, 30, 3);    // the last day of the one before
        $this->clicks($beta, 59, 1);     // and its first
        $this->clicks($alpha, 60, 100);  // before both

        $clicks = FederationStats::clicks();

        $this->assertSame(14, $clicks['total']);
        $this->assertSame(4, $clicks['previous']);
        $this->assertSame(250.0, $clicks['change']);
        $this->assertSame([
            ['id' => $alpha->id, 'name' => 'Alpha', 'host' => 'alpha.test', 'listings' => 2, 'clicks' => 12],
            ['id' => $beta->id, 'name' => 'Beta', 'host' => 'beta.test', 'listings' => 3, 'clicks' => 2],
        ], $clicks['top']);

        $this->assertSame($clicks, (new AdminDashboard)->federation()['clicks']);
    }

    public function test_a_hub_with_no_earlier_clicks_reports_no_change(): void
    {
        config(['app.is_nexus' => true]);
        $this->clicks($this->install('Alpha'), 1, 9);

        $clicks = FederationStats::clicks();

        $this->assertSame(9, $clicks['total']);
        $this->assertNull($clicks['change']);
    }
}
