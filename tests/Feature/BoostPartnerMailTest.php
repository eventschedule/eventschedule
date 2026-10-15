<?php

namespace Tests\Feature;

use App\Mail\BoostPartnerMail;
use App\Mail\BoostStatementMail;
use App\Models\BoostPartner;
use App\Models\User;
use App\Services\BoostPartnerService;
use App\Services\MetaAdsServiceFake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Every partner boost mail renders, to the partner and to the Business, with a heading that names
 * what it is about and a button that opens a page where that thing is.
 */
class BoostPartnerMailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_every_kind_renders_with_the_right_heading_and_button(): void
    {
        Queue::fake();
        MetaAdsServiceFake::resetRecorder();
        config(['app.hosted' => true, 'services.meta.access_token' => '']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Harbor Cafe']);
        $business = User::factory()->create();
        $role->users()->attach($business->id, ['level' => 'admin']);
        $event = $this->createEvent($role, ['name' => 'Friday Jazz', 'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $partner = new BoostPartner;
        $partner->forceFill([
            'user_id' => $owner->id, 'enabled_at' => now(), 'connection_method' => 'partner_share',
            'page_id' => '111', 'page_name' => 'Partner Page', 'ad_account_id' => '222',
            'ad_account_currency' => 'USD', 'ad_account_timezone' => 'America/New_York', 'confirmed_at' => now(),
        ])->save();

        $campaign = app(BoostPartnerService::class)->submit($partner, $event, $role, $business, ['budget' => 50, 'headline' => 'Friday Jazz'], [
            'scheduled_start' => now(), 'scheduled_end' => now()->addDays(7), 'targeting' => [],
            'headline' => 'x', 'primary_text' => 'x', 'description' => 'x', 'call_to_action' => 'LEARN_MORE',
        ]);

        foreach (BoostPartnerMail::KINDS as $kind) {
            foreach ([true, false] as $toPartner) {
                $withCampaign = ! in_array($kind, ['connected', 'access_problem'], true);
                $html = (new BoostPartnerMail($kind, $withCampaign ? $campaign->fresh() : null, $partner, $toPartner))->render();

                $this->assertStringNotContainsString(__('messages.deleted_event'), $html, "$kind names a deleted event");
                $this->assertStringNotContainsString('messages.', $html, "$kind has a missing key");
                $this->assertStringContainsString($withCampaign ? 'Friday Jazz' : 'Partner Page', $html, "$kind heading");
            }
        }

        // A partner's own Requests list only holds what waits on it: anything else opens the boost.
        $this->assertStringContainsString(route('boost.partner.requests'), (new BoostPartnerMail('connected', null, $partner, true))->render());
        $this->assertStringContainsString(route('boost.show', ['hash' => $campaign->hashedId()]), (new BoostPartnerMail('stopped', $campaign, null, true))->render());
        $this->assertStringContainsString(route('boost.partner.requests').'#request-'.$campaign->hashedId(), (new BoostPartnerMail('request_received', $campaign, null, true))->render());

        // An approval of a boost that is already paid does not ask for payment.
        app(BoostPartnerService::class)->confirmPayment($campaign, ['reference' => 'pi_1', 'amount' => '60', 'currency' => 'USD'], $owner);
        app(BoostPartnerService::class)->approve($campaign, 1, null, $owner);
        $mail = new BoostPartnerMail('approved', $campaign->fresh(), null, false);
        $this->assertSame('messages.boost_partner_mail_approved_paid_body', $mail->bodyKey());

        // The statement mail.
        $statement = $partner->statements()->create(['period' => '2026-10', 'currency' => 'USD', 'total_spend' => 46, 'total_fee' => 9.2, 'status' => 'paid']);
        $this->assertStringContainsString('9.20', (new BoostStatementMail($statement))->render());
    }
}
