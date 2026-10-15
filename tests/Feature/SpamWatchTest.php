<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SpamClearance;
use App\Models\User;
use App\Services\AccountBlockService;
use App\Services\AdminAlertService;
use App\Services\DemoService;
use App\Services\SpamWatch;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Accounts that may be spam (/admin/blocked#suspects, the spam_suspects alert row).
 *
 * What is pinned here is what would come back unnoticed:
 *
 * - a place a spammer writes the word that the scan does not look at, or a word in capitals or
 *   with an accent;
 * - a ticket buyer or a follower listed for their own name, which they can publish nowhere;
 * - a row that never drains: a blocked account, an administrator, the demo or one marked not
 *   spam still counted, or a decision that waits for the cache;
 * - the badge and the list disagreeing, or a change of words that waits for the cache;
 * - what an account typed reaching the page as markup.
 */
class SpamWatchTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

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
        // EnsureUserIsAdmin gates every /admin route on a confirmed password this session.
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
    }

    /** An account, owning a live schedule with a plain name unless $schedule is null. */
    private function account(array $attrs = [], ?string $schedule = 'Open Stage'): User
    {
        $user = $this->createOwner();
        $user->forceFill($attrs)->save();

        if ($schedule !== null) {
            $this->createRole($user, 'venue', ['name' => $schedule]);
        }

        return $user->fresh();
    }

    /** @return list<int> */
    private function suspectIds(): array
    {
        Cache::flush();

        return SpamWatch::suspects()->orderBy('users.id')->pluck('users.id')->all();
    }

    private function hash(User $user): string
    {
        return UrlUtils::encodeId($user->id);
    }

    // ---------------------------------------------------------------- what is flagged

    public function test_the_word_is_found_wherever_the_account_wrote_it(): void
    {
        $byName = $this->account(['name' => 'PAYPAL Refunds Desk']);
        $byEmail = $this->account(['email' => 'support@bitcoinmail.test']);
        $bySchedule = $this->account([], 'Coinbase Help Number');
        $bySubdomain = $this->account([], null);
        $this->createRole($bySubdomain, 'talent', ['name' => 'Help Desk', 'subdomain' => 'binance-desk']);
        $byEvent = $this->account([], null);
        $this->createEvent($this->createRole($byEvent), ['name' => 'Call the Venmo desk']);
        $honest = $this->account(['name' => 'Jane Doe'], 'The Blue Note');
        $this->createEvent($honest->createdRoles()->first(), ['name' => 'Jazz Night']);

        $this->assertSame(
            [$byName->id, $byEmail->id, $bySchedule->id, $bySubdomain->id, $byEvent->id],
            $this->suspectIds()
        );

        $found = SpamWatch::matches(User::whereIn('id', [$bySchedule->id, $bySubdomain->id, $byEvent->id])->get(), 5);
        $this->assertSame(['kind' => SpamWatch::SCHEDULE, 'text' => 'Coinbase Help Number', 'word' => 'coinbase'], $found[$bySchedule->id]['items'][0]);
        $this->assertSame('binance-desk', $found[$bySubdomain->id]['items'][0]['text'], 'the subdomain, where the word is only there');
        // The event's schedule is named "Test Schedule": only the event matched.
        $this->assertSame([['kind' => SpamWatch::EVENT, 'text' => 'Call the Venmo desk', 'word' => 'venmo']], $found[$byEvent->id]['items']);
        $this->assertSame(1, $found[$byEvent->id]['total']);
    }

    public function test_a_buyer_or_follower_is_not_listed_for_their_own_name_until_they_own_a_schedule(): void
    {
        // A ticket buyer's or a follower's account: no schedule, nothing published.
        $buyer = $this->account(['name' => 'PayPal Buyer', 'email' => 'paypal-buyer@gmail.com'], null);

        $this->assertSame([], $this->suspectIds());
        $this->assertSame(0, SpamWatch::matches(collect([$buyer]))[$buyer->id]['total'], 'and the evidence shown is what counts');

        // A schedule taken down is not a live one either.
        $this->createRole($buyer, 'venue', ['name' => 'Gone Hall', 'is_deleted' => true]);
        $this->assertSame([], $this->suspectIds());

        $this->createRole($buyer, 'venue', ['name' => 'Corner Cafe']);
        $this->assertSame([$buyer->id], $this->suspectIds());
        $this->assertSame(
            [SpamWatch::ACCOUNT, SpamWatch::EMAIL],
            array_column(SpamWatch::matches(collect([$buyer]))[$buyer->id]['items'], 'kind')
        );
    }

    public function test_capitals_and_accents_do_not_hide_a_word(): void
    {
        $accented = $this->account([], 'Bítcoin Desk');
        $shouting = $this->account([], 'METAMASK RECOVERY');

        $this->assertSame([$accented->id, $shouting->id], $this->suspectIds());
    }

    public function test_a_word_with_a_space_is_found_in_a_subdomain_with_a_hyphen(): void
    {
        $account = $this->account([], null);
        $this->createRole($account, 'venue', ['name' => 'Help Desk', 'subdomain' => 'cash-app-help']);

        $this->assertSame([$account->id], $this->suspectIds());

        $item = SpamWatch::matches(collect([$account]))[$account->id]['items'][0];
        $this->assertSame('cash-app-help', $item['text']);
        $this->assertSame('<mark class="spam-word">cash-app</mark>-help', (string) SpamWatch::highlight($item['text'], $item['word']));
    }

    public function test_a_schedule_already_taken_down_does_not_count(): void
    {
        $owner = $this->account([], null);
        $this->createRole($owner, 'venue', ['name' => 'PayPal Desk', 'is_deleted' => true]);

        $this->assertSame([], $this->suspectIds());
    }

    public function test_the_default_words_do_not_sit_inside_ordinary_names(): void
    {
        $this->account([], 'Gazelle Lounge');
        $this->account([], 'Rozelle Village Hall');

        $this->assertSame([], $this->suspectIds());
    }

    // ---------------------------------------------------------------- who is never listed

    public function test_blocked_accounts_administrators_the_demo_and_accounts_marked_not_spam_are_not_listed(): void
    {
        $blocked = $this->account(['name' => 'Bitcoin Doubler']);
        $admin = $this->account(['name' => 'Bitcoin Admin']);
        $admin->is_admin = true;
        $admin->save();
        $demo = $this->account(['name' => 'Bitcoin Demo', 'email' => DemoService::DEMO_EMAIL]);
        $cleared = $this->account(['name' => 'Bitcoin Meetup Berlin']);
        $waiting = $this->account(['name' => 'Bitcoin Giveaway']);

        $this->assertSame([$blocked->id, $cleared->id, $waiting->id], $this->suspectIds());

        app(AccountBlockService::class)->block($blocked, $this->admin);
        SpamWatch::clear($cleared, $this->admin);

        // Asked live, not from the cache: a decision drains the list at once.
        $this->assertSame([$waiting->id], SpamWatch::suspects()->pluck('users.id')->all());

        // canBeFlagged() is the same rule for the account's page.
        $this->assertFalse(SpamWatch::canBeFlagged($blocked->fresh()));
        $this->assertFalse(SpamWatch::canBeFlagged($admin));
        $this->assertFalse(SpamWatch::canBeFlagged($demo));
        $this->assertTrue(SpamWatch::canBeFlagged($waiting));
    }

    public function test_an_account_the_list_never_holds_gets_no_card(): void
    {
        $admin = $this->account(['name' => 'Bitcoin Admin']);
        $admin->is_admin = true;
        $admin->save();
        $demo = $this->account(['name' => 'Bitcoin Demo', 'email' => DemoService::DEMO_EMAIL]);
        // An event survives the block, so this one still matches: only the gate keeps its card off.
        $blocked = $this->account(['name' => 'Bitcoin Doubler']);
        $this->createEvent($blocked->createdRoles()->first(), ['name' => 'Bitcoin giveaway']);
        app(AccountBlockService::class)->block($blocked, $this->admin);
        $this->assertSame(1, SpamWatch::matches(collect([$blocked->fresh()]))[$blocked->id]['total']);
        $honest = $this->account(['name' => 'Jane Doe']);

        foreach ([$admin, $demo, $blocked, $honest] as $user) {
            $this->asAdmin()->get(route('admin.blocked.account', ['hash' => $this->hash($user)]))->assertOk()
                ->assertDontSee(__('messages.spam_watch_account_lead'))
                ->assertDontSee(route('admin.blocked.not_spam', ['hash' => $this->hash($user)]));
        }
    }

    public function test_unblocking_marks_a_matching_account_not_spam_and_no_other(): void
    {
        $matching = $this->account(['name' => 'Bitcoin Meetup Berlin']);
        $plain = $this->account(['name' => 'Jane Doe']);

        foreach ([$matching, $plain] as $user) {
            app(AccountBlockService::class)->block($user, $this->admin);
            $this->asAdmin()->post(route('admin.blocked.unblock', ['hash' => $this->hash($user)]))->assertRedirect();
            $this->assertFalse($user->fresh()->isBlocked());
        }

        $this->assertTrue(SpamClearance::where('user_id', $matching->id)->exists());
        $this->assertFalse(SpamClearance::where('user_id', $plain->id)->exists());
        $this->assertSame([], $this->suspectIds(), 'not straight back on the list the block took it off');
    }

    // ---------------------------------------------------------------- the alert and the pages

    public function test_the_alert_row_counts_them_links_to_the_list_and_drains(): void
    {
        $suspect = $this->account(['name' => 'Cash App Refund Line']);

        $row = AdminAlertService::items()->firstWhere('type', 'spam_suspects');
        $this->assertNotNull($row);
        $this->assertSame(1, $row['count']);
        $this->assertSame(route('admin.blocked').'#suspects', $row['url']);
        $this->assertSame(trans_choice('messages.admin_alert_spam_suspects', 1, ['count' => 1]), $row['title']);
        $this->assertSame('amber', $row['color']);

        $this->asAdmin()->post(route('admin.blocked.not_spam', ['hash' => $this->hash($suspect)]), ['from' => 'list'])
            ->assertRedirect(route('admin.blocked').'#suspects');

        AdminAlertService::flush();
        $this->assertNull(AdminAlertService::items()->firstWhere('type', 'spam_suspects'));
    }

    public function test_the_list_shows_where_the_word_is_and_marks_it(): void
    {
        $suspect = $this->account(['name' => 'Spammy'], 'Official PayPal Support');

        $html = $this->asAdmin()->get(route('admin.blocked'))->assertOk()->getContent();

        $this->assertStringContainsString('id="suspects"', $html);
        $this->assertStringContainsString('Official <mark class="spam-word">PayPal</mark> Support', $html);
        $this->assertStringContainsString(route('admin.blocked.not_spam', ['hash' => $this->hash($suspect)]), $html);
        $this->assertStringContainsString(e(__('messages.spam_watch_words')), $html);
    }

    public function test_the_list_says_how_many_more_there_are(): void
    {
        $account = $this->account([], 'Plain Hall');
        $role = $account->createdRoles()->first();
        foreach (range(1, 5) as $night) {
            $this->createEvent($role, ['name' => "Bitcoin night $night"]);
        }

        // The newest three, marked, then the rest as a number.
        $this->asAdmin()->get(route('admin.blocked'))->assertOk()
            ->assertSee('<mark class="spam-word">Bitcoin</mark> night 5', false)
            ->assertDontSee('<mark class="spam-word">Bitcoin</mark> night 2', false)
            ->assertSee(__('messages.spam_watch_more', ['count' => 2]));
    }

    public function test_what_an_account_typed_reaches_the_page_as_text(): void
    {
        $this->account(['name' => '<img src=x onerror=alert(1)>bitcoin']);

        $html = $this->asAdmin()->get(route('admin.blocked'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;<mark class="spam-word">bitcoin</mark>', $html);
    }

    public function test_the_accounts_page_says_why_and_offers_not_spam_then_the_way_back(): void
    {
        $suspect = $this->account(['name' => 'Venmo Help']);
        $hash = $this->hash($suspect);
        $page = route('admin.blocked.account', ['hash' => $hash]);

        $this->asAdmin()->get($page)->assertOk()
            ->assertSee(__('messages.spam_watch_account_lead'))
            ->assertSee(route('admin.blocked.not_spam', ['hash' => $hash]));

        $this->asAdmin()->post(route('admin.blocked.not_spam', ['hash' => $hash]))
            ->assertRedirect($page)
            ->assertSessionHas('success', __('messages.spam_watch_not_spam_done', ['name' => 'Venmo Help']));

        // A second press (a double click) is answered, not a 500, and logged once.
        $this->asAdmin()->post(route('admin.blocked.not_spam', ['hash' => $hash]))->assertRedirect($page);

        $clearance = SpamClearance::where('user_id', $suspect->id)->sole();
        $this->assertSame($this->admin->id, $clearance->cleared_by);
        $this->assertSame(1, \App\Models\AuditLog::where('action', 'admin.spam_clear')->where('model_id', $suspect->id)->count());

        $this->asAdmin()->get($page)->assertOk()
            ->assertSee(__('messages.spam_watch_cleared_title'))
            ->assertSee(route('admin.blocked.flag_again', ['hash' => $hash]))
            ->assertDontSee(route('admin.blocked.not_spam', ['hash' => $hash]));

        $this->asAdmin()->post(route('admin.blocked.flag_again', ['hash' => $hash]))->assertRedirect($page);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.spam_unclear', 'model_id' => $suspect->id]);
        $this->assertSame([$suspect->id], $this->suspectIds());
    }

    public function test_not_spam_from_the_list_goes_back_to_where_it_was_and_nowhere_else(): void
    {
        $first = $this->account(['name' => 'PayPal One']);
        $second = $this->account(['name' => 'PayPal Two']);
        $where = route('admin.blocked', ['search' => 'pills', 'suspects' => 1]);

        $this->asAdmin()->from($where)
            ->post(route('admin.blocked.not_spam', ['hash' => $this->hash($first)]), ['from' => 'list'])
            ->assertRedirect($where.'#suspects');

        $this->asAdmin()->from('https://elsewhere.test/admin/blocked?suspects=1')
            ->post(route('admin.blocked.not_spam', ['hash' => $this->hash($second)]), ['from' => 'list'])
            ->assertRedirect(route('admin.blocked').'#suspects');
    }

    public function test_a_page_of_the_list_that_has_emptied_goes_back_to_the_last_one(): void
    {
        $this->account(['name' => 'PayPal One']);

        $this->asAdmin()->get(route('admin.blocked', ['search' => 'pills', 'suspects' => 2]))
            ->assertRedirect(route('admin.blocked', ['search' => 'pills', 'suspects' => 1]).'#suspects');

        $this->asAdmin()->get(route('admin.blocked', ['suspects' => 1]))->assertOk()
            ->assertDontSee(__('messages.spam_watch_none_title'));
    }

    // ---------------------------------------------------------------- the words

    public function test_the_words_are_the_operators_and_take_effect_at_once(): void
    {
        $pills = $this->account(['name' => 'Cheap Pills Online']);
        $paypal = $this->account(['name' => 'PayPal Desk']);

        $this->assertSame([$paypal->id], SpamWatch::suspects()->pluck('users.id')->all());

        $this->asAdmin()->post(route('admin.blocked.words'), ['words' => " Cheap  Pills,\nPAYPAL, cheap pills "])
            ->assertRedirect(route('admin.blocked').'#suspects')
            ->assertSessionHas('message', __('messages.spam_watch_words_saved'));

        $this->assertSame(['cheap pills', 'paypal'], SpamWatch::words());
        // Without flushing the cache: the key carries the words.
        $this->assertSame([$pills->id, $paypal->id], SpamWatch::suspects()->orderBy('users.id')->pluck('users.id')->all());
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.settings_update']);

        // Empty is off, not the defaults.
        $this->asAdmin()->post(route('admin.blocked.words'), ['words' => ''])
            ->assertSessionHas('message', __('messages.spam_watch_words_off'));
        $this->assertSame([], SpamWatch::words());
        $this->assertSame(0, SpamWatch::count());
        $this->asAdmin()->get(route('admin.blocked'))->assertOk()->assertSee(__('messages.spam_watch_off_text'));

        // The defaults are saved as nothing, so a later change to them reaches this install.
        $this->asAdmin()->post(route('admin.blocked.words'), ['words' => implode(', ', SpamWatch::DEFAULT_WORDS)]);
        $this->assertNull(Setting::get(SpamWatch::SETTING));
        $this->assertSame(SpamWatch::DEFAULT_WORDS, SpamWatch::words());
    }

    public function test_the_words_are_held_to_their_limits(): void
    {
        $refused = [
            'paypal, ai',
            'paypal, '.str_repeat('x', SpamWatch::MAX_LENGTH + 1),
            implode(', ', array_map(fn ($n) => "word$n", range(1, SpamWatch::MAX_WORDS + 1))),
        ];

        foreach ($refused as $words) {
            $this->asAdmin()->post(route('admin.blocked.words'), ['words' => $words])
                ->assertRedirect(route('admin.blocked').'#suspects')
                ->assertSessionHasErrors('words');
        }

        $this->assertSame(SpamWatch::DEFAULT_WORDS, SpamWatch::words());

        // The most the limits allow is accepted.
        $most = array_map(fn ($n) => str_pad("w$n", SpamWatch::MAX_LENGTH, 'x'), range(1, SpamWatch::MAX_WORDS));
        $this->asAdmin()->post(route('admin.blocked.words'), ['words' => implode(', ', $most)])->assertSessionHasNoErrors();
        $this->assertSame($most, SpamWatch::words());
    }

    public function test_a_word_is_matched_as_it_is_written_not_as_a_pattern(): void
    {
        $percent = $this->account(['name' => '100% legit']);
        $this->account(['name' => '100 legit']);

        $this->asAdmin()->post(route('admin.blocked.words'), ['words' => '00%']);

        $this->assertSame([$percent->id], $this->suspectIds());
    }

    public function test_none_of_it_is_open_to_someone_who_is_not_an_administrator(): void
    {
        $owner = $this->createOwner();
        $suspect = $this->account(['name' => 'PayPal Desk']);

        $this->actingAs($owner)->post(route('admin.blocked.not_spam', ['hash' => $this->hash($suspect)]))->assertRedirect();
        $this->actingAs($owner)->post(route('admin.blocked.words'), ['words' => ''])->assertRedirect();

        $this->assertSame(0, SpamClearance::count());
        $this->assertSame(SpamWatch::DEFAULT_WORDS, SpamWatch::words());
    }

    // ---------------------------------------------------------------- the mark

    public function test_a_long_name_is_cut_with_the_word_whole_in_it(): void
    {
        $text = str_repeat('a', 200).' bitcoin '.str_repeat('b', 200);
        $html = (string) SpamWatch::highlight($text, 'bitcoin', 80);

        $this->assertStringContainsString('<mark class="spam-word">bitcoin</mark>', $html);
        $this->assertStringStartsWith('…', $html);
        $this->assertStringEndsWith('…', $html);
        $this->assertLessThanOrEqual(82, mb_strlen(strip_tags($html)));

        // The longest word allowed, in a window not much wider than it.
        $word = str_repeat('w', SpamWatch::MAX_LENGTH);
        $html = (string) SpamWatch::highlight(str_repeat('a', 100).$word.str_repeat('b', 100), $word, 80);
        $this->assertStringContainsString('<mark class="spam-word">'.$word.'</mark>', $html);
    }
}
