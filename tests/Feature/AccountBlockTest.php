<?php

namespace Tests\Feature;

use App\Console\Commands\PrunePersonalData;
use App\Models\AccountBlock;
use App\Models\BlocklistEntry;
use App\Models\Newsletter;
use App\Models\User;
use App\Services\AccountBlockService;
use App\Services\AuditService;
use App\Services\Blocklist;
use App\Services\ScheduleDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Blocking an account at /admin/blocked, and the list of what new accounts are refused for.
 *
 * What is pinned here is what would come back unnoticed:
 *
 * - a way in that nobody thought of (EnsureAccountNotBlocked asks after the request too, so a
 *   sign-in made by ANY code does not leave signed in);
 * - Unblock bringing back a schedule the owner had deleted before the block, or leaving on the
 *   list something the block added;
 * - an entry that touches an account that already exists, or that a "+tag" walks around;
 * - the address read from the socket, which behind Cloudflare is an edge that thousands share;
 * - a scheduled newsletter that still goes out from a schedule that was taken down.
 */
class AccountBlockTest extends TestCase
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

    private function blocks(): AccountBlockService
    {
        return app(AccountBlockService::class);
    }

    private function asAdmin(): self
    {
        // EnsureUserIsAdmin gates every /admin route on a confirmed password this session.
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
    }

    private function hash(User $user): string
    {
        return \App\Utils\UrlUtils::encodeId($user->id);
    }

    private function spammer(array $attrs = []): User
    {
        $user = $this->createOwner();
        $user->forceFill($attrs + ['email' => 'seller'.Str::lower(Str::random(6)).'@cheap-pills.test'])->save();

        return $user->fresh();
    }

    // ---------------------------------------------------------------- what a block does

    public function test_a_block_shuts_the_account_out_and_takes_down_what_it_owns(): void
    {
        $spammer = $this->spammer();
        $live = $this->createRole($spammer, 'venue', ['name' => 'Cheap Pills Arena']);
        $subdomain = $live->subdomain;
        $gone = $this->createRole($spammer, 'talent', ['name' => 'Deleted Before']);
        app(ScheduleDeletionService::class)->markDeleted($gone, $spammer->id);
        // Somebody else's schedule the spammer is only a member of is not theirs to lose.
        $other = $this->createRole($this->createOwner(), 'venue', ['name' => 'Honest Hall']);
        $other->users()->attach($spammer->id, ['level' => 'admin']);

        $result = $this->blocks()->block($spammer, $this->admin, 'Pharma links');

        $this->assertTrue($spammer->fresh()->isBlocked());
        $this->assertSame([$live->id], $result['taken']->pluck('id')->all());

        $live->refresh();
        $this->assertTrue((bool) $live->is_deleted);
        $this->assertSame($subdomain, $live->subdomain_before_delete, 'the name is released, as Delete does it');
        $this->assertFalse((bool) $other->fresh()->is_deleted);

        $block = AccountBlock::where('user_id', $spammer->id)->firstOrFail();
        $this->assertSame([$live->id], $block->role_ids, 'only what THIS block took down');
        $this->assertSame($this->admin->id, $block->blocked_by);
        $this->assertSame('Pharma links', $block->note);

        // Its address is on the list, and nothing else was asked for.
        $this->assertSame([[Blocklist::EMAIL, $spammer->email]], BlocklistEntry::get()->map(fn ($e) => [$e->type, $e->value])->all());

        $this->assertDatabaseHas('audit_logs', ['action' => AuditService::ADMIN_ACCOUNT_BLOCK, 'model_id' => $spammer->id, 'user_id' => $this->admin->id]);
    }

    public function test_a_newsletter_waiting_to_go_out_does_not_go_out(): void
    {
        $spammer = $this->spammer();
        $role = $this->createRole($spammer);
        $newsletter = Newsletter::create([
            'role_id' => $role->id, 'user_id' => $spammer->id, 'subject' => 'Buy now',
            'status' => 'scheduled', 'scheduled_at' => now()->addHour(), 'template' => 'modern',
        ]);

        $this->blocks()->block($spammer, $this->admin);

        $newsletter->refresh();
        $this->assertSame('draft', $newsletter->status);
        $this->assertNull($newsletter->scheduled_at);
    }

    public function test_an_admin_and_oneself_cannot_be_blocked(): void
    {
        $otherAdmin = $this->createOwner(true);

        foreach ([[$this->admin, 'messages.block_refused_self'], [$otherAdmin, 'messages.block_refused_admin']] as [$target, $reason]) {
            $this->assertSame($reason, $this->blocks()->refusal($target, $this->admin));

            try {
                $this->blocks()->block($target, $this->admin);
                $this->fail('the block went through');
            } catch (\DomainException $e) {
                $this->assertSame($reason, $e->getMessage());
            }

            $this->assertFalse($target->fresh()->isBlocked());
        }
    }

    // ---------------------------------------------------------------- every way in

    public function test_the_right_password_does_not_sign_a_blocked_account_in(): void
    {
        $spammer = $this->spammer();
        $this->blocks()->block($spammer, $this->admin);

        $this->post(route('login'), ['email' => $spammer->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => __('messages.account_blocked')]);

        $this->assertGuest();
        // Not a sign-in: the record must not say the account got in.
        $this->assertDatabaseMissing('audit_logs', ['action' => AuditService::AUTH_LOGIN, 'user_id' => $spammer->id]);
    }

    public function test_a_session_that_was_open_ends_on_its_next_page(): void
    {
        $spammer = $this->spammer();
        $role = $this->createRole($spammer);

        $this->actingAs($spammer)->get(route('home'))->assertOk();

        $this->blocks()->block($spammer, $this->admin);

        $this->actingAs($spammer->fresh())->get(route('home'))
            ->assertRedirect(app_url(route('login', [], false)))
            ->assertSessionHasErrors(['email' => __('messages.account_blocked')]);
        $this->assertGuest();

        // A page that answers a script says so in a way a script can read.
        $this->actingAs($spammer->fresh())->getJson(route('home'))->assertStatus(403);
        $this->assertGuest();
    }

    /**
     * Some twelve places sign a person in, and the next one will be written by someone who has
     * never heard of this. Whatever does it, the account does not leave signed in.
     */
    public function test_a_sign_in_made_by_any_code_does_not_leave_signed_in(): void
    {
        $spammer = $this->spammer();
        $this->blocks()->block($spammer, $this->admin);

        // The middleware itself, around a controller that signs the account in: a route added
        // here would sit behind the app's own /{subdomain}/... routes and never be reached.
        $request = \Illuminate\Http\Request::create('/some-new-door', 'GET');
        $request->setLaravelSession(app('session.store'));

        $response = app(\App\Http\Middleware\EnsureAccountNotBlocked::class)->handle($request, function () use ($spammer) {
            Auth::login(User::find($spammer->id), true);

            return response('welcome back');
        });

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(app_url(route('login', [], false)), $response->headers->get('Location'));
        $this->assertStringNotContainsString('welcome back', (string) $response->getContent());
        $this->assertGuest();

        // And the same door, for an account that is not blocked, is left alone.
        $honest = $this->createOwner();
        $response = app(\App\Http\Middleware\EnsureAccountNotBlocked::class)->handle($request, function () use ($honest) {
            Auth::login($honest);

            return response('welcome back');
        });

        $this->assertSame('welcome back', $response->getContent());
        $this->assertAuthenticatedAs($honest);
    }

    public function test_the_api_refuses_a_blocked_accounts_key(): void
    {
        $spammer = $this->spammer();
        $raw = 'testapikey_'.Str::random(24);
        $spammer->api_key = substr(hash('sha256', $raw), 0, 8);
        $spammer->api_key_hash = Hash::make($raw);
        $spammer->save();

        $this->getJson('/api/schedules', ['X-API-Key' => $raw])->assertOk();

        $this->blocks()->block($spammer, $this->admin);
        Auth::forgetGuards();

        $this->getJson('/api/schedules', ['X-API-Key' => $raw])
            ->assertStatus(403)
            ->assertJson(['error' => 'Account blocked']);

        // And no new key for the password.
        $this->postJson('/api/login', ['email' => $spammer->email, 'password' => 'password'])->assertStatus(403);
    }

    // ---------------------------------------------------------------- the way back

    public function test_unblock_undoes_what_the_block_did_and_nothing_else(): void
    {
        $spammer = $this->spammer(['signup_ip' => '203.0.113.9']);
        $live = $this->createRole($spammer, 'venue', ['name' => 'Wrongly Accused']);
        $subdomain = $live->subdomain;
        $gone = $this->createRole($spammer, 'talent');
        app(ScheduleDeletionService::class)->markDeleted($gone, $spammer->id);
        // Typed in by hand before the block: not the block's to remove.
        Blocklist::add(Blocklist::DOMAIN, 'elsewhere.test', 'by hand', $this->admin->id);

        $this->blocks()->block($spammer, $this->admin, null, true, true);
        $this->assertSame(4, BlocklistEntry::count(), 'the address, the network address, the domain, and the one typed in');

        $result = $this->blocks()->unblock($spammer->fresh(), $this->admin);

        $this->assertFalse($spammer->fresh()->isBlocked());
        $this->assertSame([$live->id], $result['restored']->pluck('id')->all());

        $live->refresh();
        $this->assertFalse((bool) $live->is_deleted);
        $this->assertSame($subdomain, $live->subdomain, 'under its own name again');
        $this->assertTrue((bool) $gone->fresh()->is_deleted, 'deleted before the block, so not brought back by its undoing');

        $this->assertSame(['elsewhere.test'], BlocklistEntry::pluck('value')->all());
        $this->assertDatabaseMissing('account_blocks', ['user_id' => $spammer->id]);

        $this->post(route('login'), ['email' => $spammer->email, 'password' => 'password'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($spammer->fresh());
    }

    /**
     * Two accounts of one spammer share an address and a domain. Letting one back in must not
     * take off the list what the other's block stands on.
     */
    public function test_an_entry_another_blocked_account_stands_behind_stays(): void
    {
        $first = $this->spammer(['email' => 'one@cheap-pills.test', 'signup_ip' => '2001:db8:aa:bb:1::5']);
        $second = $this->spammer(['email' => 'two@cheap-pills.test', 'signup_ip' => '2001:db8:aa:bb:9::7']);

        $this->blocks()->block($first, $this->admin, null, true, true);
        $this->blocks()->block($second, $this->admin, null, true, true);

        // One /64 and one domain between them, not two of each.
        $this->assertSame(1, BlocklistEntry::where('type', Blocklist::IP)->count());
        $this->assertSame('2001:db8:aa:bb::/64', BlocklistEntry::where('type', Blocklist::IP)->value('value'));

        $this->blocks()->unblock($first->fresh(), $this->admin);

        $secondBlock = AccountBlock::where('user_id', $second->id)->firstOrFail();
        $kept = BlocklistEntry::orderBy('type')->get();
        $this->assertSame(
            [[Blocklist::DOMAIN, 'cheap-pills.test'], [Blocklist::EMAIL, 'two@cheap-pills.test'], [Blocklist::IP, '2001:db8:aa:bb::/64']],
            $kept->map(fn ($e) => [$e->type, $e->value])->all()
        );
        $this->assertSame([$secondBlock->id], $kept->pluck('account_block_id')->unique()->values()->all(), 'and they are the second block\'s now');
    }

    // ---------------------------------------------------------------- the list

    private function signUp(string $email, array $server = [])
    {
        Auth::forgetGuards();

        return $this->withServerVariables($server)->post(route('sign_up'), [
            'name' => 'New Person',
            'email' => $email,
            'password' => 'correct-horse-battery',
        ]);
    }

    public function test_a_new_account_is_refused_for_its_address_its_domain_or_where_it_comes_from(): void
    {
        Blocklist::add(Blocklist::DOMAIN, 'cheap-pills.test');
        Blocklist::add(Blocklist::EMAIL, 'pill.seller@gmail.com');
        Blocklist::add(Blocklist::IP, '203.0.113.0/24');

        $refused = [
            // The domain, and a subdomain of it.
            ['next@cheap-pills.test', [], 'messages.signup_refused_email'],
            ['next@mail.cheap-pills.test', [], 'messages.signup_refused_email'],
            // One Gmail mailbox, three ways of writing it.
            ['pillseller+two@gmail.com', [], 'messages.signup_refused_email'],
            ['p.i.l.l.seller@googlemail.com', [], 'messages.signup_refused_email'],
            // An address nothing is wrong with, from a network that is on the list.
            ['honest@eventschedule-test.org', ['REMOTE_ADDR' => '203.0.113.77'], 'messages.signup_refused_address'],
        ];

        foreach ($refused as [$email, $server, $sentence]) {
            $this->signUp($email, $server)->assertSessionHasErrors(['email' => __($sentence)]);
            $this->assertDatabaseMissing('users', ['email' => $email]);
        }

        $this->assertSame(2, BlocklistEntry::where('type', Blocklist::DOMAIN)->value('refused_count'));
        $this->assertSame(2, BlocklistEntry::where('type', Blocklist::EMAIL)->value('refused_count'));
        $this->assertSame(1, BlocklistEntry::where('type', Blocklist::IP)->value('refused_count'));
        $this->assertNotNull(BlocklistEntry::where('type', Blocklist::IP)->value('last_refused_at'));

        // Next door to all three, and let in. Where it came from is kept for /admin/blocked.
        $this->signUp('pillseller@eventschedule-test.org', ['REMOTE_ADDR' => '203.0.114.77'])->assertSessionHasNoErrors();
        $this->assertSame('203.0.114.77', User::where('email', 'pillseller@eventschedule-test.org')->value('signup_ip'));
    }

    /**
     * Behind Cloudflare the socket's peer is an edge address that thousands of visitors share.
     * Recording that, or refusing it, would be recording and refusing all of them.
     */
    public function test_on_hosted_the_visitor_is_the_one_cloudflare_names(): void
    {
        config(['app.hosted' => true]);
        Blocklist::add(Blocklist::IP, '198.51.100.20');

        $request = \Illuminate\Http\Request::create('/sign_up', 'POST', [], [], [], ['REMOTE_ADDR' => '172.70.0.1', 'HTTP_CF_CONNECTING_IP' => '198.51.100.20']);
        $this->assertSame('198.51.100.20', Blocklist::address($request));
        $this->assertNotNull(Blocklist::refusal('honest@eventschedule-test.org', $request));

        // The edge itself, with somebody else behind it.
        $neighbour = \Illuminate\Http\Request::create('/sign_up', 'POST', [], [], [], ['REMOTE_ADDR' => '172.70.0.1', 'HTTP_CF_CONNECTING_IP' => '198.51.100.21']);
        $this->assertNull(Blocklist::refusal('honest@eventschedule-test.org', $neighbour));

        // Off Cloudflare the header is whatever the caller typed, and is not believed.
        config(['app.hosted' => false]);
        $this->assertSame('172.70.0.1', Blocklist::address($request));
    }

    public function test_an_entry_does_not_touch_an_account_that_already_exists(): void
    {
        $member = $this->spammer(['email' => 'longtime@cheap-pills.test']);
        Blocklist::add(Blocklist::DOMAIN, 'cheap-pills.test');

        $this->post(route('login'), ['email' => $member->email, 'password' => 'password'])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($member);

        // Saving the settings with the address it has always had is not a new address.
        $rule = fn (string $email) => \Illuminate\Support\Facades\Validator::make(
            ['email' => $email],
            ['email' => [new \App\Rules\NotBlocklisted(address: false)]]
        )->fails();
        $request = \App\Http\Requests\ProfileUpdateRequest::create('/settings', 'PATCH', ['email' => $member->email]);
        $request->setUserResolver(fn () => $member);
        $this->assertSame([], array_values(array_filter($request->rules()['email'], fn ($r) => $r instanceof \App\Rules\NotBlocklisted)));

        // Moving to another address at that domain is.
        $request = \App\Http\Requests\ProfileUpdateRequest::create('/settings', 'PATCH', ['email' => 'other@cheap-pills.test']);
        $request->setUserResolver(fn () => $member);
        $this->assertCount(1, array_filter($request->rules()['email'], fn ($r) => $r instanceof \App\Rules\NotBlocklisted));
        $this->assertTrue($rule('other@cheap-pills.test'));
        $this->assertFalse($rule('other@eventschedule-test.org'));
    }

    public function test_what_an_operator_types_is_stored_as_what_it_means(): void
    {
        $this->assertSame('cheap-pills.test', Blocklist::normalize(Blocklist::DOMAIN, ' https://Cheap-Pills.test/buy?x=1 ')['value']);
        $this->assertSame('cheap-pills.test', Blocklist::normalize(Blocklist::DOMAIN, '@cheap-pills.test')['value']);
        $this->assertSame('cheap-pills.test', Blocklist::normalize(Blocklist::DOMAIN, 'someone@cheap-pills.test')['value']);
        // A range is stored from its first address, so two spellings of it are one entry.
        $this->assertSame('203.0.113.0/24', Blocklist::normalize(Blocklist::IP, '203.0.113.77/24')['value']);
        $this->assertSame('203.0.113.77', Blocklist::normalize(Blocklist::IP, '203.0.113.77/32')['value']);
        $this->assertSame('2001:db8:aa:bb::/64', Blocklist::visitorRange('2001:db8:aa:bb:1::5'));
        $this->assertSame('203.0.113.77', Blocklist::visitorRange('203.0.113.77'));
        $this->assertNull(Blocklist::visitorRange(null));

        $refused = [
            [Blocklist::DOMAIN, 'localhost', 'messages.blocklist_invalid_domain'],
            [Blocklist::DOMAIN, 'not a domain', 'messages.blocklist_invalid_domain'],
            [Blocklist::EMAIL, 'cheap-pills.test', 'messages.blocklist_invalid_email'],
            [Blocklist::IP, '203.0.113', 'messages.blocklist_invalid_ip'],
            [Blocklist::IP, '203.0.113.0/40', 'messages.blocklist_invalid_ip'],
            // One line must not be able to refuse a whole provider, or everyone.
            [Blocklist::IP, '203.0.0.0/8', 'messages.blocklist_range_too_wide'],
            [Blocklist::IP, '0.0.0.0/0', 'messages.blocklist_range_too_wide'],
            [Blocklist::IP, '2001:db8::/16', 'messages.blocklist_range_too_wide'],
        ];

        foreach ($refused as [$type, $value, $reason]) {
            try {
                Blocklist::normalize($type, $value);
                $this->fail("$value was accepted");
            } catch (\InvalidArgumentException $e) {
                $this->assertSame($reason, $e->getMessage(), $value);
            }
        }
    }

    public function test_the_address_an_account_signed_up_from_is_forgotten_after_ninety_days(): void
    {
        $old = $this->spammer(['signup_ip' => '203.0.113.1']);
        $recent = $this->spammer(['signup_ip' => '203.0.113.2']);
        $blocked = $this->spammer(['signup_ip' => '203.0.113.3']);
        $this->blocks()->block($blocked, $this->admin);

        $past = now()->subDays(PrunePersonalData::SIGNUP_IP_DAYS + 1);
        DB::table('users')->whereIn('id', [$old->id, $blocked->id])->update(['created_at' => $past]);

        $this->artisan('app:prune-personal-data')->assertSuccessful();

        $this->assertNull($old->fresh()->signup_ip);
        $this->assertSame('203.0.113.2', $recent->fresh()->signup_ip);
        $this->assertSame('203.0.113.3', $blocked->fresh()->signup_ip, 'kept while the block it explains stands');
    }

    // ---------------------------------------------------------------- the pages

    public function test_the_page_lists_who_is_blocked_and_what_is_refused(): void
    {
        $spammer = $this->spammer(['name' => 'Pill Seller']);
        $this->createRole($spammer, 'venue', ['name' => 'Cheap Pills Arena']);
        $this->blocks()->block($spammer, $this->admin, 'Pharma links');
        Blocklist::add(Blocklist::IP, '203.0.113.0/24', 'Rented range', $this->admin->id);

        $html = $this->asAdmin()->get(route('admin.blocked'))->assertOk()->getContent();

        // In the navigation, in the group it lives in, and lit.
        $this->assertSame(1, preg_match('/<nav class="ap-subtabs[^"]*"[^>]*>(.*?)<\/nav>/s', $html, $row));
        $this->assertSame(1, preg_match('/href="'.preg_quote(route('admin.blocked'), '/').'" class="ap-subtab"\s+aria-current="page"/', $row[1]));
        $this->assertSame(1, preg_match('/<select id="admin-nav-select"[^>]*>(.*?)<\/select>/s', $html, $select));
        $this->assertSame(1, preg_match('/<option value="'.preg_quote(route('admin.blocked'), '/').'" selected>/', $select[1]));

        $page = substr($html, strpos($html, 'class="page-head"'));
        $this->assertStringContainsString('Pill Seller', $page);
        $this->assertStringContainsString('Pharma links', $page);
        $this->assertStringContainsString(route('admin.blocked.account', ['hash' => $this->hash($spammer)]), $page);
        $this->assertStringContainsString('203.0.113.0/24', $page);
        $this->assertStringContainsString('Rented range', $page);
        // The entry the block added says where it came from.
        $this->assertStringContainsString(__('messages.blocklist_from_block', ['name' => 'Pill Seller']), $page);
        // One list is one table, never a second copy of the rows for a phone.
        $this->assertSame(2, substr_count($page, '<table class="page-table'));

        // Found by the schedule it owned, under the name the schedule had.
        $found = $this->asAdmin()->get(route('admin.blocked', ['search' => 'Cheap Pills Arena']))->assertOk()->getContent();
        $this->assertSame(3, substr_count($found, '<table class="page-table'));
        $this->assertStringContainsString('class="event-status is-bad"', $found);
    }

    public function test_the_accounts_page_shows_what_a_block_would_cost_before_it_is_made(): void
    {
        $spammer = $this->spammer(['name' => 'Pill Seller', 'email' => 'one@cheap-pills.test', 'signup_ip' => '203.0.113.9']);
        $this->createRole($spammer, 'venue', ['name' => 'Cheap Pills Arena']);
        $twin = $this->spammer(['name' => 'Pill Seller Again', 'email' => 'two@cheap-pills.test', 'signup_ip' => '203.0.113.9']);

        $html = $this->asAdmin()->get(route('admin.blocked.account', ['hash' => $this->hash($spammer)]))->assertOk()->getContent();
        $page = substr($html, strpos($html, 'class="page-head"'));

        $this->assertStringContainsString('Cheap Pills Arena', $page);
        $this->assertStringContainsString('203.0.113.9', $page);
        // The other account from that address, by name and one press away.
        $this->assertStringContainsString('Pill Seller Again', $page);
        $this->assertStringContainsString(route('admin.blocked.account', ['hash' => $this->hash($twin)]), $page);
        // Each switch beside the number it would also have refused. Off until asked for.
        $this->assertStringContainsString(__('messages.block_others_same_address', ['count' => 1]), $page);
        $this->assertStringContainsString(__('messages.block_others_same_domain', ['count' => 1]), $page);
        $this->assertSame(0, preg_match('/name="block_(address|domain)" value="1"\s+checked/', $page));
        $this->assertStringContainsString('action="'.route('admin.blocked.block', ['hash' => $this->hash($spammer)]).'"', $page);

        // An administrator's page offers no block.
        $own = $this->asAdmin()->get(route('admin.blocked.account', ['hash' => $this->hash($this->admin)]))->assertOk()->getContent();
        $this->assertStringNotContainsString('/block"', $own);
        $this->assertStringContainsString(__('messages.block_refused_self'), $own);
    }

    public function test_blocking_and_unblocking_from_the_page(): void
    {
        $spammer = $this->spammer(['email' => 'one@cheap-pills.test', 'signup_ip' => '203.0.113.9']);
        $role = $this->createRole($spammer);
        $hash = $this->hash($spammer);

        $this->asAdmin()->post(route('admin.blocked.block', ['hash' => $hash]), ['note' => 'Pharma links', 'block_address' => '1', 'block_domain' => '0'])
            ->assertRedirect(route('admin.blocked.account', ['hash' => $hash]))
            ->assertSessionHas('success');

        $this->assertTrue($spammer->fresh()->isBlocked());
        $this->assertTrue((bool) $role->fresh()->is_deleted);
        $this->assertSame([Blocklist::EMAIL, Blocklist::IP], BlocklistEntry::orderBy('type')->pluck('type')->all());

        $html = $this->asAdmin()->get(route('admin.blocked.account', ['hash' => $hash]))->assertOk()->getContent();
        $this->assertStringContainsString('Pharma links', $html);
        $this->assertStringContainsString(__('messages.block_state_taken_down'), $html);
        $this->assertStringContainsString('action="'.route('admin.blocked.unblock', ['hash' => $hash]).'"', $html);

        $this->asAdmin()->post(route('admin.blocked.unblock', ['hash' => $hash]))
            ->assertRedirect(route('admin.blocked.account', ['hash' => $hash]));

        $this->assertFalse($spammer->fresh()->isBlocked());
        $this->assertFalse((bool) $role->fresh()->is_deleted);
        $this->assertSame(0, BlocklistEntry::count());
    }

    public function test_adding_to_the_list_and_taking_off_it(): void
    {
        $this->spammer(['email' => 'already@cheap-pills.test']);

        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::DOMAIN, 'value' => 'Cheap-Pills.test', 'note' => 'Pharma'])
            ->assertRedirect(route('admin.blocked').'#list')
            // The account that is already there is counted, and said not to be touched.
            ->assertSessionHas('message', __('messages.blocklist_added', ['value' => 'cheap-pills.test']).' '.__('messages.blocklist_existing_accounts', ['count' => 1]));

        $entry = BlocklistEntry::firstOrFail();
        $this->assertSame([Blocklist::DOMAIN, 'cheap-pills.test', 'Pharma', $this->admin->id], [$entry->type, $entry->value, $entry->note, $entry->created_by]);

        // Twice is once.
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::DOMAIN, 'value' => 'cheap-pills.test'])
            ->assertSessionHas('warning');
        $this->assertSame(1, BlocklistEntry::count());

        // Something that is not what it says it is comes back with what was typed.
        $this->asAdmin()->post(route('admin.blocked.entry.store'), ['type' => Blocklist::IP, 'value' => '10.0.0.0/8'])
            ->assertSessionHasErrors(['value' => __('messages.blocklist_range_too_wide')])
            ->assertSessionHasInput('value', '10.0.0.0/8');

        $this->asAdmin()->post(route('admin.blocked.entry.remove', ['hash' => \App\Utils\UrlUtils::encodeId($entry->id)]))
            ->assertRedirect(route('admin.blocked').'#list');
        $this->assertSame(0, BlocklistEntry::count());
        $this->assertDatabaseHas('audit_logs', ['action' => AuditService::ADMIN_BLOCKLIST_REMOVE, 'model_id' => $entry->id]);
    }

    public function test_none_of_it_is_open_to_someone_who_is_not_an_administrator(): void
    {
        $owner = $this->createOwner();
        $target = $this->spammer();
        $hash = $this->hash($target);

        $this->actingAs($owner)->get(route('admin.blocked'))->assertRedirect();
        $this->actingAs($owner)->get(route('admin.blocked.account', ['hash' => $hash]))->assertRedirect();
        $this->actingAs($owner)->post(route('admin.blocked.block', ['hash' => $hash]))->assertRedirect();
        $this->actingAs($owner)->post(route('admin.blocked.entry.store'), ['type' => Blocklist::DOMAIN, 'value' => 'cheap-pills.test'])->assertRedirect();

        $this->assertFalse($target->fresh()->isBlocked());
        $this->assertSame(0, BlocklistEntry::count());
    }

    /** The Help button on the page opens the page's own section of the guide, which exists. */
    public function test_the_help_button_opens_the_pages_own_section_of_the_guide(): void
    {
        $html = $this->asAdmin()->get(route('admin.blocked'))->assertOk()->getContent();
        $this->assertStringContainsString('/docs/selfhost/admin#manage-blocked', $html);

        $guide = file_get_contents(resource_path('views/marketing/docs/selfhost/admin.blade.php'));
        $this->assertStringContainsString('<section id="manage-blocked"', $guide);
        $this->assertStringContainsString('href="#manage-blocked"', $guide, 'and the guide\'s own side navigation lists it');
    }

    public function test_the_ways_into_the_account_page_from_schedules_and_users(): void
    {
        $spammer = $this->spammer(['name' => 'Pill Seller']);
        $role = $this->createRole($spammer, 'venue', ['name' => 'Cheap Pills Arena']);
        $link = route('admin.blocked.account', ['hash' => $this->hash($spammer)]);

        $this->assertStringContainsString($link, $this->asAdmin()->get(route('admin.schedules.edit', ['role' => $role->encodeId()]))->assertOk()->getContent());
        $this->assertStringContainsString($link, $this->asAdmin()->get(route('admin.users'))->assertOk()->getContent());
    }
}
