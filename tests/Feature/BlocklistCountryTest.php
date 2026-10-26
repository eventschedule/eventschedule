<?php

namespace Tests\Feature;

use App\Models\AccountBlock;
use App\Models\BlocklistEntry;
use App\Models\User;
use App\Services\AccountBlockService;
use App\Services\Blocklist;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Refusing new accounts from a country, and any list entry ending by itself (/admin/blocked).
 *
 * What is pinned here is what would come back unnoticed:
 *
 * - an entry whose time is up that still refuses (each of the four kinds is asked through its own
 *   query, and one forgotten `active()` is a country refused for good);
 * - a country added again after its week answering "already listed" and refusing nobody;
 * - Unblock leaving the country a block added on the list, or taking it off while another
 *   blocked account from there stands behind it;
 * - a visitor whose country cannot be told being refused.
 *
 * The addresses are ones the bundled DB-IP database places: 5.255.255.70 in RU, 8.8.8.8 in US.
 */
class BlocklistCountryTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const RU_ADDRESS = '5.255.255.70';

    private const US_ADDRESS = '8.8.8.8';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Http::preventStrayRequests();

        config(['app.hosted' => false, 'app.is_nexus' => false]);
        $this->admin = $this->createOwner(true);
    }

    private function asAdmin(): self
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
    }

    private function spammer(array $attrs = []): User
    {
        $user = $this->createOwner();
        $user->forceFill($attrs + ['email' => 'seller'.Str::lower(Str::random(6)).'@cheap-pills.test'])->save();

        return $user->fresh();
    }

    /** A date as the pages print it: in the operator's own time zone, not the app's. */
    private function onAdminsDay($moment): string
    {
        return $moment->copy()->setTimezone($this->admin->timezone ?: config('app.timezone'))->translatedFormat('M j, Y');
    }

    private function signUp(string $email, string $address)
    {
        Auth::forgetGuards();

        return $this->withServerVariables(['REMOTE_ADDR' => $address])->post(route('sign_up'), [
            'name' => 'New Person',
            'email' => $email,
            'password' => 'correct-horse-battery',
        ]);
    }

    public function test_a_new_account_from_a_listed_country_is_refused_and_one_from_elsewhere_is_not(): void
    {
        Blocklist::add(Blocklist::COUNTRY, 'ru', null, null, null, now()->addWeek());

        $this->signUp('honest@eventschedule-test.org', self::RU_ADDRESS)
            ->assertSessionHasErrors(['email' => __('messages.signup_refused_address')]);
        $this->assertDatabaseMissing('users', ['email' => 'honest@eventschedule-test.org']);
        $this->assertSame(1, BlocklistEntry::where('type', Blocklist::COUNTRY)->value('refused_count'));

        $this->signUp('other@eventschedule-test.org', self::US_ADDRESS)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'other@eventschedule-test.org']);
    }

    public function test_on_hosted_the_country_is_the_one_cloudflare_names(): void
    {
        config(['app.hosted' => true]);
        Blocklist::add(Blocklist::COUNTRY, 'RU');

        $from = fn (?string $country) => Request::create('/sign_up', 'POST', [], [], [], array_filter([
            'REMOTE_ADDR' => '172.70.0.1',
            'HTTP_CF_CONNECTING_IP' => self::US_ADDRESS,
            'HTTP_CF_IPCOUNTRY' => $country,
        ]));

        $this->assertSame(Blocklist::COUNTRY, Blocklist::refusal('honest@eventschedule-test.org', $from('RU'))?->type);
        $this->assertNull(Blocklist::refusal('honest@eventschedule-test.org', $from('DE')));
        // Cloudflare's "unknown" and "Tor" answer, and no answer, fall to the database, which
        // places this address in the US.
        $this->assertNull(Blocklist::refusal('honest@eventschedule-test.org', $from('XX')));
        $this->assertNull(Blocklist::refusal('honest@eventschedule-test.org', $from(null)));
    }

    public function test_a_visitor_whose_country_cannot_be_told_is_not_refused(): void
    {
        Blocklist::add(Blocklist::COUNTRY, 'RU');

        $this->assertNull(Blocklist::refusal('honest@eventschedule-test.org', Request::create('/sign_up', 'POST', [], [], [], ['REMOTE_ADDR' => '10.0.0.5'])));
    }

    public function test_an_entry_whose_time_is_up_refuses_nobody_of_any_kind(): void
    {
        $this->travelTo(now()->subDays(2));
        Blocklist::add(Blocklist::EMAIL, 'pill.seller@gmail.com', null, null, null, now()->addDay());
        Blocklist::add(Blocklist::DOMAIN, 'cheap-pills.test', null, null, null, now()->addDay());
        Blocklist::add(Blocklist::IP, self::US_ADDRESS, null, null, null, now()->addDay());
        Blocklist::add(Blocklist::COUNTRY, 'RU', null, null, null, now()->addDay());
        $this->travelBack();

        $this->assertNull(Blocklist::forEmail('pill.seller@gmail.com'));
        $this->assertNull(Blocklist::forEmail('next@cheap-pills.test'));
        $this->assertNull(Blocklist::forAddress(self::US_ADDRESS));
        $this->assertNull(Blocklist::forCountry('RU'));

        // The same four, with their time still to come.
        BlocklistEntry::query()->update(['expires_at' => now()->addHour()]);

        $this->assertNotNull(Blocklist::forEmail('pill.seller@gmail.com'));
        $this->assertNotNull(Blocklist::forEmail('next@cheap-pills.test'));
        $this->assertNotNull(Blocklist::forAddress(self::US_ADDRESS));
        $this->assertNotNull(Blocklist::forCountry('RU'));
    }

    public function test_adding_a_country_again_after_it_ended_starts_it_over(): void
    {
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'RU', 'for' => '1d'])
            ->assertSessionHas('message');
        $entry = BlocklistEntry::where('type', Blocklist::COUNTRY)->firstOrFail();
        $this->assertSame('RU', $entry->match_key);
        $this->assertTrue($entry->expires_at->between(now()->addHours(23), now()->addHours(25)));

        // Still running: the same length again changes nothing.
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'RU', 'for' => '1d'])
            // Said with the end it keeps, so a shorter length asked for is not taken as done.
            ->assertSessionHas('warning', __('messages.blocklist_already_listed', ['value' => 'Russia']).' '.__('messages.blocklist_until', ['date' => $this->onAdminsDay($entry->expires_at)]).'.');

        $this->travel(2)->days();
        $this->assertNull(Blocklist::forCountry('RU'));

        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'RU', 'for' => '7d'])
            ->assertSessionHas('message', fn ($message) => str_starts_with($message, __('messages.blocklist_renewed', ['value' => 'Russia'])));
        $this->assertSame(1, BlocklistEntry::count());
        $this->assertNotNull(Blocklist::forCountry('RU'));
        $this->assertTrue(BlocklistEntry::first()->expires_at->greaterThan(now()->addDays(6)));

        // Still running, and asked for longer: it lasts longer.
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'RU', 'for' => 'forever'])
            ->assertSessionHas('message', __('messages.blocklist_extended', ['value' => 'Russia']).' '.__('messages.blocklist_for_forever').'.');
        $this->assertNull(BlocklistEntry::first()->expires_at);
    }

    public function test_a_hand_renewal_of_what_a_block_added_is_the_operators_from_then_on(): void
    {
        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);
        $blocks = app(AccountBlockService::class);
        $blocks->block($spammer, $this->admin, null, false, false, true, now()->addDay());

        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'RU', 'for' => '30d']);
        $blocks->unblock($spammer, $this->admin);

        $this->assertNotNull(Blocklist::forCountry('RU'), 'asked for again by hand, so letting the account back in leaves it');
    }

    public function test_a_country_is_seven_days_unless_the_form_says_otherwise_and_must_be_a_real_one(): void
    {
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'RU']);
        $this->assertTrue(BlocklistEntry::first()->expires_at->between(now()->addDays(7)->subMinute(), now()->addDays(7)->addMinute()));

        // Everything else stays until removed when nothing is said.
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::DOMAIN, 'value' => 'cheap-pills.test']);
        $this->assertNull(BlocklistEntry::where('type', Blocklist::DOMAIN)->value('expires_at'));

        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'ZZ'])
            ->assertSessionHasErrors(['value' => __('messages.blocklist_invalid_country')]);
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'RU', 'for' => '1y'])
            ->assertSessionHasErrors('for');
    }

    public function test_a_block_can_add_its_country_for_a_while_and_unblock_takes_it_off(): void
    {
        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);
        $hash = UrlUtils::encodeId($spammer->id);

        $this->asAdmin()->post(route('admin.blocked.block', ['hash' => $hash]), ['block_country' => '1', 'country_for' => '30d'])
            ->assertSessionHas('success');

        $entry = BlocklistEntry::where('type', Blocklist::COUNTRY)->firstOrFail();
        $this->assertSame('RU', $entry->value);
        $this->assertSame(AccountBlock::where('user_id', $spammer->id)->value('id'), $entry->account_block_id);
        $this->assertTrue($entry->expires_at->greaterThan(now()->addDays(29)));

        $this->asAdmin()->post(route('admin.blocked.unblock', ['hash' => $hash]))->assertSessionHas('success');
        $this->assertSame(0, BlocklistEntry::where('type', Blocklist::COUNTRY)->count());
    }

    public function test_a_block_without_the_switch_leaves_the_country_alone(): void
    {
        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);

        $this->asAdmin()->post(route('admin.blocked.block', ['hash' => UrlUtils::encodeId($spammer->id)]), ['block_country' => '0']);

        $this->assertSame(0, BlocklistEntry::where('type', Blocklist::COUNTRY)->count());
    }

    public function test_the_country_stays_while_another_blocked_account_from_there_stands_behind_it(): void
    {
        $blocks = app(AccountBlockService::class);
        $first = $this->spammer(['signup_ip' => self::RU_ADDRESS]);
        $second = $this->spammer(['signup_ip' => '5.255.255.77']);

        $blocks->block($first, $this->admin, null, false, false, true, now()->addWeek());
        $blocks->block($second, $this->admin, null, false, false, true, now()->addWeek());
        $blocks->unblock($first, $this->admin);

        $entry = BlocklistEntry::where('type', Blocklist::COUNTRY)->first();
        $this->assertNotNull($entry, 'the other account from there is still blocked');
        $this->assertSame(AccountBlock::where('user_id', $second->id)->value('id'), $entry->account_block_id);
    }

    public function test_a_block_never_stretches_an_entry_the_operator_typed_in(): void
    {
        $entry = Blocklist::add(Blocklist::COUNTRY, 'RU', null, null, null, now()->addDay());
        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);
        $blocks = app(AccountBlockService::class);

        $blocks->block($spammer, $this->admin, null, false, false, true, now()->addDays(30));

        $entry->refresh();
        $this->assertNull($entry->account_block_id, 'still the operator\'s');
        $this->assertTrue($entry->expires_at->lessThan(now()->addDays(2)), 'and still a day, not thirty');

        $blocks->unblock($spammer, $this->admin);
        $this->assertNotNull(Blocklist::forCountry('RU'), 'Unblock leaves what it did not add');
    }

    public function test_the_block_page_says_when_a_typed_in_entry_keeps_its_own_end(): void
    {
        Blocklist::add(Blocklist::COUNTRY, 'RU', null, null, null, now()->addDay());
        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);

        $this->asAdmin()->post(route('admin.blocked.block', ['hash' => UrlUtils::encodeId($spammer->id)]), ['block_country' => '1', 'country_for' => '30d'])
            ->assertSessionHas('success')
            ->assertSessionHas('warning', fn ($warning) => str_contains($warning, 'Russia'));
    }

    public function test_asking_by_hand_for_what_a_block_added_makes_it_the_operators(): void
    {
        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);
        $blocks = app(AccountBlockService::class);
        $blocks->block($spammer, $this->admin, null, false, false, true, now()->addWeek());

        // Shorter than what is there, so nothing about its end changes.
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'RU', 'for' => '1d'])
            ->assertSessionHas('warning');
        $blocks->unblock($spammer, $this->admin);

        $this->assertNotNull(Blocklist::forCountry('RU'));
    }

    public function test_a_block_that_revives_an_ended_entry_owns_it_and_unblock_ends_it(): void
    {
        $this->travelTo(now()->subDays(3));
        Blocklist::add(Blocklist::COUNTRY, 'RU', null, null, null, now()->addDay());
        $this->travelBack();

        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);
        $blocks = app(AccountBlockService::class);
        $blocks->block($spammer, $this->admin, null, false, false, true, now()->addWeek());

        $this->assertNotNull(Blocklist::forCountry('RU'));
        $blocks->unblock($spammer, $this->admin);
        $this->assertSame(0, BlocklistEntry::where('type', Blocklist::COUNTRY)->count());
    }

    public function test_an_account_blocked_before_the_country_was_listed_does_not_keep_it_on(): void
    {
        $blocks = app(AccountBlockService::class);
        $old = $this->spammer(['signup_ip' => '5.255.255.77']);

        $this->travelTo(now()->subMonths(3));
        $blocks->block($old, $this->admin);
        $this->travelBack();

        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);
        $blocks->block($spammer, $this->admin, null, false, false, true, now()->addWeek());
        $blocks->unblock($spammer, $this->admin);

        $this->assertSame(0, BlocklistEntry::where('type', Blocklist::COUNTRY)->count());
    }

    public function test_a_revived_entry_is_not_kept_on_by_an_account_blocked_before_it_was_revived(): void
    {
        $blocks = app(AccountBlockService::class);

        $this->travelTo(now()->subMonths(6));
        Blocklist::add(Blocklist::COUNTRY, 'RU', null, null, null, now()->addDay());
        $this->travelBack();

        $this->travelTo(now()->subMonths(3));
        $blocks->block($this->spammer(['signup_ip' => '5.255.255.77']), $this->admin);
        $this->travelBack();

        $spammer = $this->spammer(['signup_ip' => self::RU_ADDRESS]);
        $blocks->block($spammer, $this->admin, null, false, false, true, now()->addWeek());
        $blocks->unblock($spammer, $this->admin);

        $this->assertSame(0, BlocklistEntry::where('type', Blocklist::COUNTRY)->count());
    }

    public function test_the_countries_offered_are_places_a_visitor_can_be_in_named_once_each(): void
    {
        $countries = Blocklist::countries();

        $this->assertArrayHasKey('RU', $countries);
        $this->assertArrayHasKey('XK', $countries);
        foreach (['SU', 'UK', 'YU', 'DD', 'ZZ', 'EU', 'UN', 'AC'] as $code) {
            $this->assertArrayNotHasKey($code, $countries, $code.' is not a country a visitor is in');
        }
        $this->assertSame(count($countries), count(array_unique($countries)), 'no name is offered twice');

        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::COUNTRY, 'value' => 'SU'])
            ->assertSessionHasErrors(['value' => __('messages.blocklist_invalid_country')]);

        app()->setLocale('he');
        $this->assertSame('רוסיה', Blocklist::countryName('RU'));
    }

    public function test_the_pages_show_the_country_its_cost_and_its_end(): void
    {
        $spammer = $this->spammer(['name' => 'Pill Seller', 'signup_ip' => self::RU_ADDRESS]);
        $this->spammer(['signup_ip' => '5.255.255.77']);
        $this->spammer(['signup_ip' => self::US_ADDRESS]);

        $this->asAdmin()->get(route('admin.blocked.account', ['hash' => UrlUtils::encodeId($spammer->id)]))
            ->assertOk()
            ->assertSee('name="block_country"', false)
            ->assertSee('name="country_for"', false)
            ->assertSee(__('messages.block_also_country', ['country' => '<bdi>Russia</bdi>']), false)
            // The other account from Russia, not the one from the US and not this one.
            ->assertSee(e(__('messages.block_others_same_country', ['count' => 1, 'days' => Blocklist::RECENT_DAYS])), false);

        Blocklist::add(Blocklist::COUNTRY, 'RU', null, null, null, now()->addWeek());
        Blocklist::add(Blocklist::DOMAIN, 'cheap-pills.test', null, null, null, now()->subDay());

        $html = $this->asAdmin()->get(route('admin.blocked'))
            ->assertOk()
            ->assertSee('id="blocklist-country"', false)
            ->getContent();

        // The rows, not the add row above them, which names Russia and Country too.
        $at = strpos($html, '<table class="page-table');
        $this->assertNotFalse($at);
        $table = substr($html, $at);
        $this->assertStringContainsString('<span class="c-strong"><bdi>Russia</bdi></span>', $table);
        $this->assertStringContainsString(e(__('messages.blocklist_until', ['date' => $this->onAdminsDay(now()->addWeek())])), $table);
        $this->assertStringContainsString(__('messages.blocklist_ended'), $table);
    }

    public function test_an_account_whose_country_is_not_known_is_not_offered_the_switch(): void
    {
        $spammer = $this->spammer(['signup_ip' => null]);

        $this->asAdmin()->get(route('admin.blocked.account', ['hash' => UrlUtils::encodeId($spammer->id)]))
            ->assertOk()
            ->assertDontSee('name="block_country"', false)
            ->assertSee(__('messages.block_country_unknown_help'));
    }
}
