<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\UsageDaily;
use App\Services\UsageTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * WhatsApp event creation is sold as Enterprise everywhere it is described, and every message runs
 * an AI parse. The webhook checked neither the plan nor the daily parse allowance, so any schedule
 * with a verified phone could create events by message, as often as it liked - and each parse was
 * recorded against an allowance nothing on this path ever read.
 *
 * The request is signed the way Twilio signs it, because an unsigned one is dropped before any of
 * this runs. Every outbound call is faked, and no AI key is configured, so the only request that
 * can leave is the WhatsApp reply itself.
 */
class WhatsAppWebhookGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const TWILIO_TOKEN = 'test-twilio-auth-token';

    private const PHONE = '+14155550123';

    private const MESSAGE = 'Jazz night at the Blue Room this Friday at 8pm';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            // isEnterprise() and aiParseDailyLimit() both answer "no limit" when not hosted, and
            // CI runs with IS_HOSTED=false, so the gates only exist if this is set.
            'app.hosted' => true,
            'services.twilio.sid' => 'ACtest',
            'services.twilio.token' => self::TWILIO_TOKEN,
            'services.twilio.from' => '+14155550100',
            // A message that gets past both gates reaches GeminiUtils::parseEvent(), which gives
            // up without a key instead of calling out.
            'services.ai.text_provider' => 'gemini',
            'services.google.gemini_key' => null,
            'services.openai.api_key' => null,
        ]);

        Http::fake([
            'api.twilio.com/*' => Http::response(['sid' => 'SMtest'], 201),
            '*' => Http::response([], 500),
        ]);
    }

    public function test_a_schedule_below_enterprise_is_told_so_and_nothing_is_parsed(): void
    {
        $role = $this->senderSchedule(['plan_type' => 'pro']);
        $this->assertFalse($role->isEnterprise(), 'the fixture must be below Enterprise');

        $this->sendWhatsApp(self::MESSAGE)->assertOk();

        $this->assertReplied(__('messages.whatsapp_requires_enterprise'));
        // One request in total, the reply: nothing reached an AI provider.
        Http::assertSentCount(1);
        $this->assertSame(0, Event::count());
    }

    public function test_a_schedule_over_its_daily_parse_allowance_gets_the_limit_reply(): void
    {
        $role = $this->senderSchedule();
        $limit = $role->aiParseDailyLimit();
        $this->assertNotNull($limit, 'a hosted schedule has a daily parse allowance');

        // Seeded exactly the way canMakeAiParseRequest() counts: today's GEMINI_PARSE_EVENT row,
        // which is what GeminiUtils::parseEvent() increments after every successful parse.
        UsageDaily::create([
            'date' => now()->toDateString(),
            'operation' => UsageTrackingService::GEMINI_PARSE_EVENT,
            'role_id' => $role->id,
            'count' => $limit,
        ]);
        $this->assertFalse($role->canMakeAiParseRequest());

        $this->sendWhatsApp(self::MESSAGE)->assertOk();

        $this->assertReplied(__('messages.ai_text_daily_limit_reached', ['limit' => $limit]));
        Http::assertSentCount(1);
        $this->assertSame(0, Event::count());
    }

    public function test_an_enterprise_schedule_within_its_allowance_reaches_the_parser(): void
    {
        $role = $this->senderSchedule();
        $this->assertTrue($role->isEnterprise());
        $this->assertTrue($role->canMakeAiParseRequest());

        $this->sendWhatsApp(self::MESSAGE)->assertOk();

        // With no AI key the parse comes back empty, so the reply is the parse failure: proof the
        // message got past both gates instead of being stopped at one of them.
        $this->assertReplied(__('messages.whatsapp_parse_failed'));
        $this->assertNotReplied(__('messages.whatsapp_requires_enterprise'));
    }

    public function test_selfhost_is_not_gated(): void
    {
        config(['app.hosted' => false]);

        // Below Enterprise and far past any hosted allowance: neither matters on selfhost, where
        // isEnterprise() is always true and there is no daily parse allowance at all.
        $role = $this->senderSchedule(['plan_type' => 'pro']);
        UsageDaily::create([
            'date' => now()->toDateString(),
            'operation' => UsageTrackingService::GEMINI_PARSE_EVENT,
            'role_id' => $role->id,
            'count' => 100000,
        ]);
        $this->assertTrue($role->isEnterprise());
        $this->assertNull($role->aiParseDailyLimit());

        $this->sendWhatsApp(self::MESSAGE)->assertOk();

        $this->assertReplied(__('messages.whatsapp_parse_failed'));
        $this->assertNotReplied(__('messages.whatsapp_requires_enterprise'));
    }

    /**
     * An owner whose verified phone is on their account and whose default schedule is the new one.
     */
    private function senderSchedule(array $attrs = []): Role
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', $attrs);

        // Quietly: on hosted, User's updating hook clears phone_verified_at whenever the phone
        // changes, which would undo the verification set in the same save.
        $owner->forceFill([
            'phone' => self::PHONE,
            'phone_verified_at' => now(),
            'default_role_id' => $role->id,
        ])->saveQuietly();

        return $role;
    }

    /**
     * POST the webhook signed the way WhatsAppService::verifySignature() checks it: the full URL,
     * then every POST parameter sorted by key with key and value appended, HMAC-SHA1 with the
     * auth token, base64. The values are ones TrimStrings and ConvertEmptyStringsToNull leave
     * alone, so the controller verifies the same string that was signed.
     */
    private function sendWhatsApp(string $body): TestResponse
    {
        $url = route('whatsapp.webhook');
        $params = [
            'Body' => $body,
            'From' => 'whatsapp:'.self::PHONE,
            'NumMedia' => '0',
        ];

        ksort($params);
        $data = $url;
        foreach ($params as $key => $value) {
            $data .= $key.$value;
        }

        return $this->post($url, $params, [
            'X-Twilio-Signature' => base64_encode(hash_hmac('sha1', $data, self::TWILIO_TOKEN, true)),
        ]);
    }

    private function assertReplied(string $message): void
    {
        Http::assertSent(fn (ClientRequest $request) => str_contains($request->url(), 'api.twilio.com')
            && $request['To'] === 'whatsapp:'.self::PHONE
            && $request['Body'] === $message);
    }

    private function assertNotReplied(string $message): void
    {
        Http::assertNotSent(fn (ClientRequest $request) => str_contains($request->url(), 'api.twilio.com')
            && $request['Body'] === $message);
    }
}
