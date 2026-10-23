<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\AccountBlockService;
use App\Utils\HomeQuotes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The homepage's quotes (App\Utils\HomeQuotes): typed in by the operator at /admin/settings,
 * printed under the schedule's own name and link, and never under a schedule that is gone.
 */
class HomeQuotesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.is_nexus' => true]);
        Cache::flush();
    }

    public function test_nothing_is_printed_until_there_is_a_quote(): void
    {
        $this->get('/')->assertOk()->assertDontSee('id="quotes"', false)->assertDontSee('Organizers on Event Schedule');
    }

    public function test_a_quote_is_printed_escaped_under_its_schedule(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Corner Room']);
        HomeQuotes::save([['role_id' => $role->id, 'quote' => 'We kept every <b>dollar</b> of the door.', 'title' => 'Music bar, Leeds']]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('id="quotes"', false);
        $response->assertSee('hp-quotes is-1', false);
        $response->assertSee('We kept every &lt;b&gt;dollar&lt;/b&gt; of the door.', false);
        $response->assertDontSee('<b>dollar</b>', false);
        $response->assertSee('The Corner Room');
        $response->assertSee('Music bar, Leeds');
        $response->assertSee('href="'.e($role->getGuestUrl()).'"', false);
    }

    /**
     * Said by a person when the operator names one: their name first, then what they do, with
     * the schedule's picture and page beside them. A long quote is set as one.
     */
    public function test_a_named_person_leads_the_attribution(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall']);
        HomeQuotes::save([['role_id' => $role->id, 'quote' => str_repeat('Easy to set up and the support was quick. ', 8), 'title' => '', 'name' => 'Robin Example']]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<b><bdi>Robin Example</bdi></b>\s*<i><bdi>Harbour Hall</bdi></i>#', $html);
        $this->assertStringContainsString('hp-quote is-long', $html);
    }

    public function test_a_quote_whose_schedule_is_gone_is_left_off(): void
    {
        $owner = $this->createOwner();
        $kept = $this->createRole($owner, 'venue', ['name' => 'Still Here Hall']);
        $deleted = $this->createRole($this->createOwner(), 'venue', ['name' => 'Gone Lounge', 'is_deleted' => true]);
        $blockedOwner = $this->createOwner();
        $blocked = $this->createRole($blockedOwner, 'venue', ['name' => 'Blocked Barn']);
        app(AccountBlockService::class)->block($blockedOwner, $this->createOwner(true));
        // A block takes the schedule down; put it back up so the blocked-owner rule is what is
        // tested, not the deleted one.
        $blocked->forceFill(['is_deleted' => false])->save();

        HomeQuotes::save([
            ['role_id' => $deleted->id, 'quote' => 'Deleted quote', 'title' => ''],
            ['role_id' => $blocked->id, 'quote' => 'Blocked quote', 'title' => ''],
            ['role_id' => $kept->id, 'quote' => 'Kept quote', 'title' => ''],
        ]);
        Cache::flush();

        $response = $this->get('/')->assertOk();

        $response->assertSee('Kept quote');
        $response->assertSee('hp-quotes is-1', false);
        $response->assertDontSee('Deleted quote');
        $response->assertDontSee('Blocked quote');
    }

    /** A schedule nobody runs (a placeholder made for an act or venue someone listed) has no page to vouch for it. */
    public function test_a_quote_on_a_schedule_nobody_runs_is_left_off(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Placeholder Act']);
        \Illuminate\Support\Facades\DB::table('role_user')->where('role_id', $role->id)->update(['level' => 'follower']);
        HomeQuotes::save([['role_id' => $role->id, 'quote' => 'Nobody said this', 'title' => '']]);

        $this->get('/')->assertOk()->assertDontSee('Nobody said this');
    }

    /**
     * Owned but not claimed: no verified contact (on hosted, any change of email clears it), so
     * viewGuest() answers 404, and the homepage must not link to that page. Role::getGuestUrl()
     * answers '' for such a schedule, and forPage() leaves a quote with no address off.
     * Mutation: drop the `! $url` check in HomeQuotes::forPage().
     */
    public function test_a_quote_on_a_schedule_whose_page_is_not_public_is_left_off(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Unverified Hall']);
        HomeQuotes::save([['role_id' => $role->id, 'quote' => 'Said before the address changed', 'title' => '']]);
        $this->get('/')->assertOk()->assertSee('Said before the address changed');

        \Illuminate\Support\Facades\DB::table('roles')->where('id', $role->id)->update(['email_verified_at' => null, 'phone_verified_at' => null]);
        Cache::flush();

        $this->get('/'.$role->subdomain)->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee('Said before the address changed');
    }

    /** Erasing an account forgets the quotes on the schedules it owned, and the names with them. */
    public function test_erasing_an_account_forgets_its_quotes(): void
    {
        $leaving = $this->createOwner();
        $theirs = $this->createRole($leaving, 'venue');
        $staying = $this->createRole($this->createOwner(), 'venue');
        HomeQuotes::save([
            ['role_id' => $theirs->id, 'quote' => 'Theirs', 'title' => '', 'name' => 'Sam Example'],
            ['role_id' => $staying->id, 'quote' => 'Still here', 'title' => '', 'name' => ''],
        ]);

        app(\App\Services\AccountDeletionService::class)->prepare($leaving);

        Cache::flush();
        $this->assertSame([['role_id' => $staying->id, 'quote' => 'Still here', 'title' => '', 'name' => '']], HomeQuotes::stored());
    }

    public function test_a_malformed_setting_does_not_break_the_page(): void
    {
        Setting::set(HomeQuotes::SETTING, '{not json');
        $this->get('/')->assertOk()->assertDontSee('id="quotes"', false);

        Setting::set(HomeQuotes::SETTING, json_encode([['role_id' => 'x'], 'text', ['role_id' => 999999, 'quote' => 'Nobody']]));
        $this->get('/')->assertOk()->assertDontSee('id="quotes"', false);
    }

    public function test_quotes_are_the_nexus_homepage_only(): void
    {
        $role = $this->createRole($this->createOwner());
        HomeQuotes::save([['role_id' => $role->id, 'quote' => 'Only on the nexus', 'title' => '']]);
        config(['app.is_nexus' => false]);

        $this->get('/')->assertDontSee('Only on the nexus');
    }

    public function test_an_admin_saves_quotes_by_subdomain_and_the_form_shows_them_back(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Dana Plays']);
        $this->adminActing();

        $this->post(route('admin.settings.update_home_quotes'), ['quotes' => [
            ['subdomain' => strtoupper($role->subdomain), 'quote' => 'Set up in an evening.', 'title' => 'Singer, Lisbon', 'name' => 'Dana Example'],
            ['subdomain' => '', 'quote' => '', 'title' => ''],
        ]])->assertRedirect(route('admin.settings').'#home-quotes')->assertSessionHasNoErrors();

        Cache::flush();
        $this->assertSame([['role_id' => $role->id, 'quote' => 'Set up in an evening.', 'title' => 'Singer, Lisbon', 'name' => 'Dana Example']], HomeQuotes::stored());

        // Kept by id: the form reads back the schedule's subdomain as it is now.
        $role->forceFill(['subdomain' => 'dana-renamed'])->save();
        $this->get(route('admin.settings'))->assertOk()->assertSee('value="dana-renamed"', false)->assertSee('Set up in an evening.')->assertSee('value="Dana Example"', false);
    }

    /**
     * A quote whose schedule is down keeps that schedule through a save of the form, so it comes
     * back if the schedule does, and the form does not refuse the placeholder subdomain a
     * deletion leaves behind.
     */
    public function test_a_quote_whose_schedule_is_down_survives_a_save(): void
    {
        $down = $this->createRole($this->createOwner(), 'venue', ['name' => 'Resting Room']);
        $other = $this->createRole($this->createOwner(), 'venue');
        HomeQuotes::save([['role_id' => $down->id, 'quote' => 'Back soon', 'title' => '']]);
        $down->forceFill(['is_deleted' => true])->save();
        $this->adminActing();

        $this->get(route('admin.settings'))->assertOk()->assertSee('value="'.$down->id.'"', false);

        $this->post(route('admin.settings.update_home_quotes'), ['quotes' => [
            ['role_id' => $down->id, 'subdomain' => '', 'quote' => 'Back soon', 'title' => ''],
            // An id that is not a stored quote's is ignored: the row needs a subdomain.
            ['role_id' => $other->id, 'subdomain' => '', 'quote' => 'Smuggled', 'title' => ''],
        ]])->assertSessionHasErrors(['quotes.1.subdomain']);

        $this->post(route('admin.settings.update_home_quotes'), ['quotes' => [
            ['role_id' => $down->id, 'subdomain' => '', 'quote' => "Back soon\r\nfor good", 'title' => ''],
        ]])->assertSessionHasNoErrors();

        Cache::flush();
        $this->assertSame([['role_id' => $down->id, 'quote' => "Back soon\nfor good", 'title' => '', 'name' => '']], HomeQuotes::stored());
    }

    /** On a live schedule an empty subdomain is a question, not a keep: the form asks for one. */
    public function test_emptying_a_live_quotes_subdomain_asks_for_one(): void
    {
        $live = $this->createRole($this->createOwner(), 'venue');
        HomeQuotes::save([['role_id' => $live->id, 'quote' => 'Live one', 'title' => '', 'name' => '']]);
        $this->adminActing();

        $this->post(route('admin.settings.update_home_quotes'), ['quotes' => [
            ['role_id' => $live->id, 'subdomain' => '', 'quote' => 'Live one', 'title' => ''],
        ]])->assertSessionHasErrors(['quotes.0.subdomain']);
    }

    public function test_the_form_refuses_what_it_cannot_store(): void
    {
        $role = $this->createRole($this->createOwner());
        $this->adminActing();

        $this->post(route('admin.settings.update_home_quotes'), ['quotes' => [
            ['subdomain' => 'no-such-schedule', 'quote' => 'Hello', 'title' => ''],
            ['subdomain' => $role->subdomain, 'quote' => '', 'title' => 'Venue'],
            ['subdomain' => $role->subdomain, 'quote' => "Fast \u{2014} and free", 'title' => ''],
        ]])->assertSessionHasErrors(['quotes.0.subdomain', 'quotes.1.quote', 'quotes.2.quote']);

        $this->post(route('admin.settings.update_home_quotes'), ['quotes' => [
            ['subdomain' => $role->subdomain, 'quote' => str_repeat('a', HomeQuotes::QUOTE_MAX + 1), 'title' => ''],
        ]])->assertSessionHasErrors(['quotes.0.quote']);

        // A field posted as a list is no text at all, not a 500.
        $this->post(route('admin.settings.update_home_quotes'), ['quotes' => [
            ['subdomain' => [$role->subdomain], 'quote' => ['x'], 'title' => ''],
        ]])->assertRedirect();

        Cache::flush();
        $this->assertSame([], HomeQuotes::stored());
    }

    public function test_only_an_admin_on_the_nexus_can_save(): void
    {
        $role = $this->createRole($this->createOwner());
        $payload = ['quotes' => [['subdomain' => $role->subdomain, 'quote' => 'Sneaky', 'title' => '']]];

        $this->actingAs($this->createOwner())->post(route('admin.settings.update_home_quotes'), $payload);
        Cache::flush();
        $this->assertSame([], HomeQuotes::stored());

        config(['app.is_nexus' => false]);
        $this->adminActing();
        $this->post(route('admin.settings.update_home_quotes'), $payload)->assertNotFound();
        $this->get(route('admin.settings'))->assertOk()->assertDontSee('id="home-quotes"', false);

        Cache::flush();
        $this->assertSame([], HomeQuotes::stored());
    }

    private function adminActing(): void
    {
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->createOwner(true));
    }
}
