<?php

namespace Tests\Feature;

use App\Models\BlocklistEntry;
use App\Models\Setting;
use App\Rules\NotBlocklisted;
use App\Services\AudienceMailGuard;
use App\Services\Blocklist;
use App\Services\SpamWatch;
use App\Utils\RealtimeTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the review of the nexus's blocked accounts (2026-11) found, held.
 *
 * - Every sign-up address on hosted was a Cloudflare edge: the visitor is App Platform's
 *   do-connecting-ip, an edge address is never taken for a visitor, and the blocklist neither
 *   stores nor applies one, because it refuses everyone that edge serves.
 * - The support and billing scams used no watched word: a phone number, a charge written out,
 *   lookalike characters and a throwaway address flag them, for what is recent only, and never
 *   ordinary names.
 */
class SpamSignalsAndEdgeAddressTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The texts the blocked accounts used, shortened. */
    private const SCAMS = [
        'Geek Squad Auto-Renewal Notice: a charge of $234.99 USD. Contact Customer Support at +1 (863) 316-4088',
        'Thanks for your $5ᒣ9 Bitcoin purchase Support +181ᗱ9ᗱ45805',
        'Amount Charged: $364.99 USD  Support: 845 573 3428',
        'PаyPal refund',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        config(['app.hosted' => false, 'app.is_nexus' => false]);
    }

    private function request(array $headers, string $peer = '172.70.1.1'): Request
    {
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $peer]);

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }

    public function test_on_hosted_the_visitor_is_app_platforms_header_and_never_an_edge(): void
    {
        config(['app.hosted' => true]);

        $this->assertSame('203.0.113.9', RealtimeTracker::clientIp($this->request([
            'do-connecting-ip' => '203.0.113.9',
            'CF-Connecting-IP' => '172.71.215.30',
        ])));
        $this->assertSame('2001:db8::7', RealtimeTracker::clientIp($this->request(['do-connecting-ip' => '2001:db8::7'])));

        // An edge in either header is passed over; a real address in the older one still counts.
        $this->assertSame('198.51.100.4', RealtimeTracker::clientIp($this->request([
            'CF-Connecting-IP' => '198.51.100.4',
        ])));
        $this->assertSame('203.0.113.1', RealtimeTracker::clientIp($this->request(['CF-Connecting-IP' => '104.22.66.66'], '203.0.113.1')));
        $this->assertSame('203.0.113.1', RealtimeTracker::clientIp($this->request(['do-connecting-ip' => 'not an address'], '203.0.113.1')));

        // WARP and iCloud Private Relay leave from Cloudflare's own ranges: App Platform's header
        // is still that visitor, and only the older header is checked for an edge.
        $this->assertSame('104.28.5.9', RealtimeTracker::clientIp($this->request(['do-connecting-ip' => '104.28.5.9'])));
    }

    public function test_off_hosted_no_header_is_believed(): void
    {
        $this->assertSame('203.0.113.1', RealtimeTracker::clientIp($this->request([
            'do-connecting-ip' => '198.51.100.1',
            'CF-Connecting-IP' => '198.51.100.2',
        ], '203.0.113.1')));
    }

    public function test_the_blocklist_refuses_to_store_an_edge_address_or_a_range_over_one(): void
    {
        foreach (['172.71.215.30', '104.22.0.0/16', '2606:4700::1', '104.0.0.0/8'] as $value) {
            try {
                Blocklist::add(Blocklist::IP, $value);
                $this->fail($value.' was stored');
            } catch (\InvalidArgumentException $e) {
                $this->assertContains($e->getMessage(), ['messages.blocklist_edge_address', 'messages.blocklist_range_too_wide'], $value);
            }
        }

        $this->assertSame('203.0.113.9', Blocklist::add(Blocklist::IP, '203.0.113.9')->match_key);
        $this->assertNull(Blocklist::visitorRange('162.158.193.229'), 'an account whose sign-up address is an edge has no address to block');
        $this->assertSame('203.0.113.9', Blocklist::visitorRange('203.0.113.9'));
    }

    public function test_an_edge_entry_stored_before_the_fix_refuses_nobody(): void
    {
        // Written the way the old code wrote them, past add()'s new check.
        BlocklistEntry::forceCreate(['type' => Blocklist::IP, 'value' => '104.22.193.133', 'match_key' => '104.22.193.133']);
        BlocklistEntry::forceCreate(['type' => Blocklist::IP, 'value' => '198.51.100.0/24', 'match_key' => '198.51.100.0/24']);

        $this->assertNull(Blocklist::forAddress('104.22.193.133'));
        $this->assertNotNull(Blocklist::forAddress('198.51.100.20'), 'a real entry still refuses');
    }

    public function test_the_signs_flag_every_scam_and_no_ordinary_name(): void
    {
        foreach (self::SCAMS as $text) {
            $this->assertNotNull(SpamWatch::signalIn($text), $text);
        }

        foreach ([
            'Jazz Night 2026-11-26 19:00', 'Summer Fest 2026', 'Tickets $9.99', 'Концерт в Москве',
            'ΣAE Formal', 'Doors 8:00, show 9:30', 'Reunion 25.12.2026', 'Workshop 10-12 Oct 2026',
            'Open mic 7.30pm £5.00', 'ザ・ライブ 2026', 'Room 101-B',
            // What the review found the first version flagged.
            '第２回 Jazz Night', '２０２６年 Live Tokyo', 'ᐃᓄᒃᑎᑐᑦ Language Night', '𝐉𝐚𝐳𝐳 Night',
            'Вечiр поезiї', 'Дiджей сет', 'Room 101 102 2026', 'Bus 100 200 3000',
            'Levée de fonds 100 000 2026', 'Cours 2026-2027 groupe 101 202 3030',
            'Workshop 49.00 USD', 'Early bird 15.00 EUR', 'Show at 7.30 EUR', 'Entrada 25,00 EUR',
            'حفلة ٢٠٢٦', 'งานดนตรี 2026 รอบ 101 102 2026',
        ] as $text) {
            $this->assertNull(SpamWatch::signalIn($text), $text);
        }

        $this->assertTrue(SpamWatch::throwawayEmail('eaexnb04703@hadjoy.com'));
        $this->assertTrue(SpamWatch::throwawayEmail('diazdimrs.2.9.19.8.7@gmail.com'));
        $this->assertTrue(SpamWatch::throwawayEmail('Name.En.S7.8.23+x@googlemail.com'));
        $this->assertFalse(SpamWatch::throwawayEmail('john.smith.1990@gmail.com'));
        $this->assertFalse(SpamWatch::throwawayEmail('someone@example.com'));
    }

    public function test_a_recent_scam_is_listed_with_its_sign_and_an_old_name_is_not(): void
    {
        $scammer = $this->createOwner();
        $this->createRole($scammer, 'talent', ['name' => self::SCAMS[0]]);

        $old = $this->createOwner();
        $oldRole = $this->createRole($old, 'venue', ['name' => 'Box office 845 573 3428']);
        DB::table('roles')->where('id', $oldRole->id)->update(['created_at' => now()->subDays(SpamWatch::SIGNAL_DAYS + 5), 'updated_at' => now()->subDays(SpamWatch::SIGNAL_DAYS + 5)]);

        // Clean for months, then renamed into a scam: the window is when a name last changed, not
        // when the schedule was made, or renaming an old schedule would get past every sign.
        $switcher = $this->createOwner();
        $switched = $this->createRole($switcher, 'venue', ['name' => 'Corner Stage']);
        DB::table('roles')->where('id', $switched->id)->update(['created_at' => now()->subYear(), 'updated_at' => now()->subYear()]);
        $switched->refresh()->forceFill(['name' => self::SCAMS[0]])->save();

        $eventOwner = $this->createOwner();
        $role = $this->createRole($eventOwner, 'venue', ['name' => 'The Hall']);
        $this->createEvent($role, ['name' => self::SCAMS[2], 'user_id' => $eventOwner->id]);

        Cache::flush();
        $ids = SpamWatch::suspects()->pluck('id')->all();

        $this->assertContains($scammer->id, $ids);
        $this->assertContains($eventOwner->id, $ids);
        $this->assertNotContains($old->id, $ids, 'a number in a name left alone for months is not a reason to look now');
        $this->assertContains($switcher->id, $ids, 'a schedule renamed into a scam is, however old it is');

        $items = SpamWatch::matches(collect([$eventOwner]))[$eventOwner->id]['items'];
        $this->assertSame([SpamWatch::EVENT], array_column($items, 'kind'));
        $this->assertSame([SpamWatch::SIGNAL_PHONE], array_column($items, 'signal'));
        // Listed for an operator to look at, and nothing more: a sign never pauses mail.
        $this->assertFalse(SpamWatch::flagsNow($eventOwner));
        $this->assertFalse(AudienceMailGuard::wouldBePaused($eventOwner->fresh()));
    }

    public function test_a_throwaway_owner_is_listed_and_a_buyer_with_one_is_not(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['email' => 'wxjord92710@hadjoy.com'])->save();
        $this->createRole($owner, 'venue', ['name' => 'Corner Stage']);

        $buyer = $this->createOwner();
        $buyer->forceFill(['email' => 'fibzdw18116@hadjoy.com'])->save();

        Cache::flush();
        $ids = SpamWatch::suspects()->pluck('id')->all();

        $this->assertContains($owner->id, $ids);
        $this->assertNotContains($buyer->id, $ids);
        $this->assertSame([SpamWatch::SIGNAL_THROWAWAY], array_column(SpamWatch::matches(collect([$owner->fresh()]))[$owner->id]['items'], 'signal'));
    }

    public function test_a_word_still_pauses_mail_where_a_sign_alone_does_not(): void
    {
        $byWord = $this->createOwner();
        $this->createRole($byWord, 'talent', ['name' => 'Geek Squad Desk']);
        $bySign = $this->createOwner();
        $this->createRole($bySign, 'talent', ['name' => 'Call 845 573 3428 today']);

        $this->assertTrue(SpamWatch::flagsNow($byWord));
        $this->assertFalse(SpamWatch::flagsNow($bySign));
        Cache::flush();
        $this->assertContains($bySign->id, SpamWatch::suspects()->pluck('id')->all(), 'it is still listed');
    }

    public function test_an_empty_list_still_watches_nothing(): void
    {
        Setting::set(SpamWatch::SETTING, '');
        $user = $this->createOwner();
        $this->createRole($user, 'talent', ['name' => self::SCAMS[0]]);

        Cache::flush();
        $this->assertSame([], SpamWatch::suspects()->pluck('id')->all());
        $this->assertFalse(SpamWatch::flagsNow($user));
    }

    public function test_the_list_page_names_the_sign(): void
    {
        $admin = $this->createOwner(true);
        $user = $this->createOwner();
        $this->createRole($user, 'talent', ['name' => self::SCAMS[1]]);
        Cache::flush();

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin)
            ->get(route('admin.blocked'))
            ->assertOk()
            ->assertSee(__('messages.spam_watch_signal_lookalike'));
    }

    public function test_the_migration_adds_the_new_words_to_a_saved_list_and_drops_edge_blocks(): void
    {
        $migration = require database_path('migrations/2026_11_26_000000_widen_spam_watch_and_drop_edge_blocks.php');

        Setting::set(SpamWatch::SETTING, 'paypal, hero conf');
        BlocklistEntry::forceCreate(['type' => Blocklist::IP, 'value' => '172.71.124.21', 'match_key' => '172.71.124.21']);
        BlocklistEntry::forceCreate(['type' => Blocklist::IP, 'value' => '203.0.113.9', 'match_key' => '203.0.113.9']);
        BlocklistEntry::forceCreate(['type' => Blocklist::DOMAIN, 'value' => 'hadjoy.com', 'match_key' => 'hadjoy.com']);

        $migration->up();

        $words = SpamWatch::words();
        $this->assertSame(['paypal', 'hero conf'], array_slice($words, 0, 2), 'the operator\'s own words stay first');
        $this->assertContains('geek squad', $words);
        $this->assertContains('customer support', $words);
        $this->assertSame(['203.0.113.9', 'hadjoy.com'], BlocklistEntry::orderBy('id')->pluck('match_key')->all());

        // Saved empty stays off; never saved keeps reading the defaults.
        Setting::set(SpamWatch::SETTING, '');
        $migration->up();
        $this->assertSame([], SpamWatch::words());

        Setting::set(SpamWatch::SETTING, null);
        $migration->up();
        $this->assertSame(SpamWatch::DEFAULT_WORDS, SpamWatch::words());
    }

    public function test_the_new_words_do_not_sit_inside_ordinary_names(): void
    {
        foreach (['Billings Jazz Club', 'Northern Lights', 'Documentary Night', 'Renewal Church', 'Squadron Ball'] as $name) {
            $this->assertNull(SpamWatch::wordInText($name), $name);
        }
    }

    public function test_on_hosted_a_new_account_cannot_be_made_with_a_throwaway_address(): void
    {
        $passes = fn (string $email) => Validator::make(['email' => $email], ['email' => [new NotBlocklisted]])->passes();

        config(['app.hosted' => true]);
        $this->assertFalse($passes('someone@anonmart.com'), 'on the throwaway list, not on NoFakeEmail\'s');
        $this->assertFalse($passes('Someone@ANONMART.com'));
        $this->assertTrue($passes('someone@gmail.com'));
        $this->assertTrue($passes('name.7.46.6@gmail.com'), 'the Gmail digit rule is a reason to look, never to refuse');
        $this->assertTrue($passes('abc@privaterelay.appleid.com'), 'a privacy relay forwards to a real inbox');
        $this->assertTrue($passes('abc@duck.com'));

        $message = Validator::make(['email' => 'someone@anonmart.com'], ['email' => [new NotBlocklisted]])->errors()->first('email');
        $this->assertSame(__('messages.email_not_permanent'), $message);

        config(['app.hosted' => false]);
        $this->assertTrue($passes('someone@anonmart.com'), 'a selfhost install decides for itself');
    }
}
