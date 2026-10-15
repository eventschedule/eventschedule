<?php

namespace Tests\Feature;

use App\Jobs\LaunchPartnerBoost;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\BoostPartnerService;
use App\Services\MetaAdsServiceFake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The boost partner's API: the same steps and rules as its pages, readable and writable only
 * with the partner account's own key.
 */
class ApiBoostTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $partnerUser;

    private User $business;

    private BoostPartner $partner;

    private Role $role;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        MetaAdsServiceFake::resetRecorder();
        config(['app.hosted' => true, 'services.meta.access_token' => '']);

        $this->partnerUser = $this->createOwner();
        $this->role = $this->createRole($this->partnerUser, 'venue');
        $this->business = User::factory()->create();
        $this->role->users()->attach($this->business->id, ['level' => 'admin']);
        $this->event = $this->createEvent($this->role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->partner = new BoostPartner;
        $this->partner->forceFill([
            'user_id' => $this->partnerUser->id,
            'enabled_at' => now(),
            'connection_method' => BoostPartner::METHOD_PARTNER_SHARE,
            'page_id' => '111', 'page_name' => 'Partner Page',
            'ad_account_id' => '222', 'ad_account_currency' => 'USD', 'ad_account_timezone' => 'America/New_York',
            'confirmed_at' => now(),
        ])->save();
    }

    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    private function request(): BoostCampaign
    {
        return app(BoostPartnerService::class)->submit($this->partner, $this->event, $this->role, $this->business, [
            'budget' => 50, 'headline' => 'Friday Jazz', 'primary_text' => 'Live trio',
        ], [
            'scheduled_start' => now(), 'scheduled_end' => now()->addDays(7), 'targeting' => ['geo_locations' => ['countries' => ['US']]],
            'headline' => 'x', 'primary_text' => 'x', 'description' => 'x', 'call_to_action' => 'LEARN_MORE',
        ]);
    }

    public function test_the_partner_lists_and_reads_its_boosts(): void
    {
        $c = $this->request();
        $key = $this->apiKey($this->partnerUser);

        $this->getJson('/api/boosts', ['X-API-Key' => $key])
            ->assertOk()
            ->assertJsonPath('data.0.id', $c->hashedId())
            ->assertJsonPath('data.0.status', 'pending_approval')
            ->assertJsonPath('data.0.version', 1)
            ->assertJsonPath('data.0.ad.headline', 'Friday Jazz')
            ->assertJsonPath('data.0.budget.amount', '50.00')
            ->assertJsonPath('data.0.audience.summary', 'United States')
            ->assertJsonPath('meta.total', 1);

        $this->getJson('/api/boosts/'.$c->hashedId(), ['X-API-Key' => $key])
            ->assertOk()
            ->assertJsonPath('data.schedule', $this->role->subdomain);
    }

    public function test_a_business_key_reads_nothing_here(): void
    {
        $c = $this->request();
        $key = $this->apiKey($this->business);

        $this->getJson('/api/boosts', ['X-API-Key' => $key])->assertStatus(403);
        $this->getJson('/api/boosts/'.$c->hashedId(), ['X-API-Key' => $key])->assertStatus(403);
        $this->postJson('/api/boosts/'.$c->hashedId().'/approve', ['version' => 1], ['X-API-Key' => $key])->assertStatus(403);
    }

    public function test_approve_and_confirm_payment_launch_and_repeat_safely(): void
    {
        $c = $this->request();
        $key = $this->apiKey($this->partnerUser);

        $this->postJson('/api/boosts/'.$c->hashedId().'/approve', ['version' => 1, 'note' => 'ok'], ['X-API-Key' => $key])
            ->assertOk()->assertJsonPath('data.status', 'awaiting_payment');

        $this->postJson('/api/boosts/'.$c->hashedId().'/confirm-payment', ['reference' => 'pi_3Qx', 'amount' => '60.00', 'currency' => 'USD'], ['X-API-Key' => $key])
            ->assertOk()->assertJsonPath('data.status', 'in_review')
            ->assertJsonPath('data.payment.reference', 'pi_3Qx')
            ->assertJsonPath('data.payment.confirmed', true);

        // Repeats answer the boost as it stands.
        $this->postJson('/api/boosts/'.$c->hashedId().'/approve', ['version' => 1], ['X-API-Key' => $key])->assertOk();
        $this->postJson('/api/boosts/'.$c->hashedId().'/confirm-payment', ['reference' => 'pi_3Qx', 'amount' => '60.00'], ['X-API-Key' => $key])->assertOk();
        Queue::assertPushed(LaunchPartnerBoost::class, 1);

        // A different payment on a paid budget is refused.
        $this->postJson('/api/boosts/'.$c->hashedId().'/confirm-payment', ['reference' => 'pi_other', 'amount' => '10.00'], ['X-API-Key' => $key])->assertStatus(409);
    }

    public function test_an_approval_naming_an_old_version_is_a_409(): void
    {
        $c = $this->request();
        app(BoostPartnerService::class)->edit($c, $this->business, ['headline' => 'Saturday Jazz']);
        $key = $this->apiKey($this->partnerUser);

        $this->postJson('/api/boosts/'.$c->hashedId().'/approve', ['version' => 1], ['X-API-Key' => $key])
            ->assertStatus(409)->assertJsonPath('code', 'stale_version');
    }

    public function test_decline_needs_a_note_and_withdrawing_a_payment_works_before_launch(): void
    {
        $c = $this->request();
        $key = $this->apiKey($this->partnerUser);

        $this->postJson('/api/boosts/'.$c->hashedId().'/decline', [], ['X-API-Key' => $key])->assertStatus(422);

        $this->postJson('/api/boosts/'.$c->hashedId().'/confirm-payment', ['reference' => 'pi_1', 'amount' => '5'], ['X-API-Key' => $key])->assertOk();
        $this->deleteJson('/api/boosts/'.$c->hashedId().'/confirm-payment', [], ['X-API-Key' => $key])
            ->assertOk()->assertJsonPath('data.payment.confirmed', false);

        $this->postJson('/api/boosts/'.$c->hashedId().'/decline', ['note' => 'Use a venue photo'], ['X-API-Key' => $key])
            ->assertOk()->assertJsonPath('data.status', 'declined')->assertJsonPath('data.approval.note', 'Use a venue photo');
    }

    public function test_stop_ends_the_boost_and_updated_since_finds_it(): void
    {
        $c = $this->request();
        $key = $this->apiKey($this->partnerUser);
        $since = now()->subSecond()->utc()->toIso8601String();

        $this->postJson('/api/boosts/'.$c->hashedId().'/stop', ['reason' => 'Event cancelled'], ['X-API-Key' => $key])
            ->assertOk()->assertJsonPath('data.status', 'stopped')->assertJsonPath('data.stop_reason', 'Event cancelled');

        $this->getJson('/api/boosts?updated_since='.urlencode($since).'&status=stopped', ['X-API-Key' => $key])
            ->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_another_partners_boost_is_not_reachable(): void
    {
        $other = $this->createOwner();
        $otherPartner = new BoostPartner;
        $otherPartner->forceFill(['user_id' => $other->id, 'enabled_at' => now()])->save();
        $c = $this->request();

        $this->getJson('/api/boosts/'.$c->hashedId(), ['X-API-Key' => $this->apiKey($other)])->assertStatus(403);
    }

    public function test_statements_are_listed_and_read(): void
    {
        $key = $this->apiKey($this->partnerUser);
        $this->partner->statements()->create(['period' => '2026-10', 'currency' => 'USD', 'total_spend' => 46, 'total_fee' => 9.2, 'status' => 'paid']);

        $this->getJson('/api/boost-statements', ['X-API-Key' => $key])->assertOk()->assertJsonPath('data.0.period', '2026-10');
        $this->getJson('/api/boost-statements/2026-10', ['X-API-Key' => $key])->assertOk()->assertJsonPath('data.totals.fee', '9.20');
        $this->getJson('/api/boost-statements/2026-09', ['X-API-Key' => $key])->assertStatus(404);
    }
}
