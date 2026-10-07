<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\BoostCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The platform operator's management pages (/admin/schedules and its edit page, /admin/boost,
 * /admin/blog and its two forms) on the admin page kit, and the defects fixed on the way.
 *
 * Each of these pages opened its own way: eight icon tiles and a hand-rolled table, nine figure
 * boxes, a list that disappeared without an AI key. They now open with the navigation, one line on
 * what the page is for, strips of plain figures, one list. What is pinned here is what would come
 * back unnoticed: a second kind of figure box, a confirmation nobody is shown, a directive that
 * swallows the bracket after it, a status printed as its raw English key.
 */
class ApAdminManagePagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // No page here may reach the network: the suite loads the developer's .env, where real
        // tokens live, and a page that asks an outside service (the Domains page asked
        // DigitalOcean) would do it for real on every run. A request nothing faked throws.
        \Illuminate\Support\Facades\Http::preventStrayRequests();

        config(['app.hosted' => true]);
        $this->admin = $this->createOwner(true);
    }

    private function admin(): self
    {
        if (! Route::has('admin.schedules')) {
            $this->markTestSkipped('The admin routes are not registered in this environment.');
        }

        // EnsureUserIsAdmin gates every /admin route on a confirmed password this session.
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
    }

    private function page(string $url): string
    {
        return $this->admin()->get($url)->assertOk()->getContent();
    }

    /** The page itself: from its head on, past the layout's own markup and the navigation. */
    private function fromHead(string $html): string
    {
        return substr($html, strpos($html, 'class="page-head"'));
    }

    private function withoutAiKey(): void
    {
        config(['services.google.gemini_key' => null, 'services.openai.api_key' => null]);
    }

    public function test_the_schedules_list_is_strips_filters_and_one_list(): void
    {
        $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Note Cellar']);

        $html = $this->page('/admin/schedules');

        // One head, after the navigation: the navigation names the page, the head says what it is for.
        $this->assertSame(1, substr_count($html, 'class="page-head"'));
        $page = $this->fromHead($html);

        // Two strips of plain figures where there were eight icon tiles.
        $this->assertSame(2, substr_count($page, 'page-stats is-auto'));
        $this->assertSame(8, substr_count($page, 'class="page-stat-label"'));
        $this->assertStringNotContainsString('dashboard-icon', substr($page, 0, strpos($page, 'class="page-filters"')));

        // One row of filters, one list, and in it marks and text links where there were pills.
        $this->assertSame(1, substr_count($page, 'class="page-filters"'));
        $this->assertSame(1, substr_count($page, '<table class="page-table'));
        $this->assertStringContainsString('class="event-link is-danger"', $page);
        $this->assertStringContainsString('class="event-status is-on"', $page);
        $this->assertStringNotContainsString('rounded-full', substr($page, strpos($page, '<table'), strpos($page, '</table>') - strpos($page, '<table')));
    }

    public function test_a_plain_selfhost_gets_one_strip_of_two_figures(): void
    {
        config(['app.hosted' => false, 'app.is_nexus' => false]);

        $html = $this->page('/admin/schedules');

        $this->assertSame(1, substr_count($html, 'page-stats is-auto'));
        $this->assertSame(2, substr_count($html, 'class="page-stat-label"'));
        $this->assertStringContainsString(__('messages.admin_schedules_lead_selfhost'), $html);
    }

    /**
     * updateSchedule() redirects to the list with its confirmation under `success`, which the
     * layout's toast does not read and the list did not print: a saved plan said nothing.
     */
    public function test_saving_a_plan_is_confirmed_on_the_list(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbor Lights']);

        $this->admin()->followingRedirects()
            ->put(route('admin.schedules.update', ['role' => $role->encodeId()]), [
                'plan_type' => 'pro',
                'plan_term' => 'year',
                'plan_expires' => now()->addMonths(3)->format('Y-m-d'),
            ])
            ->assertOk()
            ->assertSee('Plan updated successfully for Harbor Lights.');
    }

    public function test_an_empty_list_says_why_and_offers_the_way_out(): void
    {
        $this->assertStringContainsString(__('messages.admin_schedules_empty_text'), $this->page('/admin/schedules'));

        $html = $this->page('/admin/schedules?search=nothing-by-this-name');

        $this->assertStringContainsString(__('messages.no_match_filters'), $html);
        $this->assertStringContainsString(__('messages.clear_filters'), $html);
        $this->assertStringNotContainsString(__('messages.admin_schedules_empty_text'), $html);
        $this->assertStringNotContainsString('<table class="page-table', $html);
    }

    /**
     * The list printed the plan, the term and the subscription state as their raw English keys
     * ("Month", "Past due", "None"), and a trial with one day to run read "1 days left".
     */
    public function test_the_list_speaks_the_readers_language_and_counts_days_properly(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'talent', [
            'name' => 'Night Owl Comedy',
            'plan_type' => 'pro',
            'plan_term' => 'month',
            'plan_expires' => null,
            'trial_ends_at' => now()->addDay()->addHours(3),
        ]);

        $this->admin->forceFill(['language_code' => 'de'])->save();
        $html = $this->page('/admin/schedules');

        $this->assertStringContainsString(trans_choice('messages.days_left_choice', 1, [], 'de'), $html);
        $this->assertStringContainsString(__('messages.monthly', [], 'de'), $html);
        $this->assertStringContainsString('>'.__('messages.trial', [], 'de').'</span>', $html);
        $this->assertStringContainsString(__('messages.talent', [], 'de'), $html);
        $this->assertStringNotContainsString('>Month<', $html);

        $this->assertSame('1 day left', trans_choice('messages.days_left_choice', 1, [], 'en'));
        $this->assertSame('5 days left', trans_choice('messages.days_left_choice', 5, [], 'en'));
        $this->assertSame('Ends today', trans_choice('messages.days_left_choice', 0, [], 'en'));
    }

    public function test_the_edit_page_is_cards_with_the_way_back_and_the_destroying_action_apart(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Riverside Yoga', 'email_verified_at' => null]);
        $role->forceFill(['email_verified_at' => null])->saveQuietly();

        $html = $this->fromHead($this->page(route('admin.schedules.edit', ['role' => $role->encodeId()])));

        // The way back is the list's own name, not a "Back to Schedules" button.
        $this->assertSame(1, substr_count($html, 'class="page-back"'));
        $this->assertStringNotContainsString(__('messages.back_to_schedules'), $html);

        // Two forms (details, plan), each ending Cancel then Save, and one red button on the page.
        $this->assertSame(2, substr_count($html, 'class="page-form-actions"'));
        $this->assertSame(2, substr_count($html, __('messages.save_changes')));
        $this->assertSame(1, substr_count($html, 'from-red-500'));
        $this->assertLessThan(strpos($html, 'from-red-500'), strrpos($html, __('messages.save_changes')));

        // The subscription is pairs down a card, not a blue box, and its state is translated.
        $this->assertStringContainsString('<dl class="page-kv">', $html);
        $this->assertStringNotContainsString('bg-blue-50', $html);
    }

    public function test_the_confirmations_on_the_edit_page_are_translated(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['phone' => '+15551230001']);
        $role->forceFill(['email_verified_at' => null, 'phone_verified_at' => null])->saveQuietly();

        $this->admin->forceFill(['language_code' => 'de'])->save();
        $html = $this->page(route('admin.schedules.edit', ['role' => $role->encodeId()]));

        $this->assertStringContainsString(e(__('messages.confirm_mark_email_verified', [], 'de')), $html);
        $this->assertStringContainsString(e(__('messages.confirm_mark_phone_verified', [], 'de')), $html);
        $this->assertStringNotContainsString('Mark this schedule', $html);
    }

    /** A refused plan came back as the stored plan, with the message beside a value nobody typed. */
    public function test_a_refused_plan_keeps_what_was_typed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['plan_type' => 'free', 'plan_expires' => null]);
        $edit = route('admin.schedules.edit', ['role' => $role->encodeId()]);

        $this->admin()->from($edit)
            ->put(route('admin.schedules.update', ['role' => $role->encodeId()]), [
                'plan_type' => 'enterprise',
                'plan_term' => 'month',
                'plan_expires' => 'not a date',
            ])
            ->assertRedirect($edit)
            ->assertSessionHasErrors('plan_expires');

        $html = $this->page($edit);

        $this->assertStringContainsString('<option value="enterprise" selected>', $html);
        $this->assertStringContainsString('<option value="month" selected>', $html);
        $this->assertStringContainsString('value="not a date"', $html);
    }

    public function test_the_boost_page_is_two_strips_two_forms_and_lists(): void
    {
        config(['services.meta.default_currency' => 'EUR']);

        $html = $this->page('/admin/boost');

        $this->assertSame(1, substr_count($html, 'class="page-head"'));
        $this->assertSame(2, substr_count($html, 'page-stats is-auto'));
        $this->assertSame(9, substr_count($html, 'class="page-stat-label"'));

        // The two forms, each with its own picker list, in the install's boost currency: the
        // amount was labelled with a literal dollar sign.
        $this->assertSame(2, substr_count($html, 'data-subdomain-dropdown class='));
        $this->assertStringContainsString(__('messages.amount').' (€)', $html);
        $this->assertStringNotContainsString('($)', $html);

        // The box stops at 1,000, which is what the guide promises ("up to 1,000"). The server's
        // own ceiling is higher; the form is what holds the documented one.
        $this->assertStringContainsString('name="amount" id="credit-amount" required min="1" max="1000"', $html);
    }

    /**
     * Both forms post to this page, and their answer sat inside the first card whichever was sent.
     * It is the layout's to give, above every page of the portal, and it is given once: the page
     * went on to list the same messages under its own head, so each was on the screen twice.
     */
    public function test_a_refused_boost_form_is_answered_once_at_the_top_of_the_page(): void
    {
        $this->admin()->from('/admin/boost')
            ->post(route('admin.boost.set_limit'), ['subdomain' => 'nobody-by-this-name', 'amount' => 50])
            ->assertRedirect('/admin/boost')
            ->assertSessionHasErrors();

        // Read before the page is asked for: showing the page is what uses the flashed errors up.
        $message = e(session('errors')->first());
        $this->assertNotSame('', $message);
        $html = $this->page('/admin/boost');

        $this->assertSame(1, substr_count($html, 'role="alert"'));
        $this->assertSame(1, substr_count($html, $message), 'said once');
        // Above the navigation and the page, so it is on screen when the page opens.
        $this->assertLessThan(strpos($html, 'class="page-head"'), strpos($html, 'role="alert"'));
        $this->assertLessThan(strpos($html, $message), strpos($html, 'role="alert"'));
    }

    /**
     * "@endif (3 hours ago)": Blade read the bracket as the directive's own argument and dropped
     * it, so the alert named the campaign and never said how long it had been stuck.
     */
    public function test_a_stuck_campaign_says_how_long_it_has_been_stuck(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $campaign = BoostCampaign::create([
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'name' => 'Open mic push',
            'status' => 'pending_payment',
            'user_budget' => 15,
        ]);
        $campaign->forceFill(['created_at' => now()->subHours(2)])->saveQuietly();

        $html = $this->page('/admin/boost');

        $this->assertStringContainsString('id="boost-alerts"', $html);
        $this->assertStringContainsString('('.$campaign->fresh()->created_at->diffForHumans().')', $html);

        // The list: its state as a mark in the reader's words, eight columns.
        $this->assertStringContainsString('<span class="event-status is-warn">'.__('messages.boost_status_pending_payment').'</span>', $html);
        $table = substr($html, strpos($html, 'boost-campaigns">'));
        $this->assertSame(8, substr_count(substr($table, 0, strpos($table, '</thead>')), '<th scope="col"'));
    }

    /**
     * The list and "Create post" were both behind the AI key, so an install without one could not
     * see, edit, hide or delete the posts it had. Only the AI draft needs the key.
     */
    public function test_the_blog_list_does_not_wait_on_an_ai_key(): void
    {
        if (! Route::has('blog.admin.index')) {
            $this->markTestSkipped('The admin blog routes are registered on the nexus only.');
        }

        $this->withoutAiKey();
        $post = BlogPost::create(['title' => 'Selling out a small venue', 'slug' => 'selling-out', 'content' => '<p>Body.</p>']);

        $html = $this->page('/admin/blog');

        $this->assertStringContainsString('Selling out a small venue', $html);
        $this->assertStringContainsString(route('blog.create'), $html);
        $this->assertStringContainsString(route('blog.edit', $post->encodeId()), $html);
        $this->assertStringNotContainsString(__('messages.setup_required_gemini'), $html);

        $this->assertSame(1, substr_count($html, '<table class="page-table'));
        $this->assertStringContainsString('<span class="event-status">'.__('messages.draft').'</span>', $html);

        // Nothing is written after the document any more (a modal root and a script were).
        $this->assertStringEndsWith('</html>', trim($html));
        $this->assertStringNotContainsString('previewPost', $html);
    }

    public function test_an_empty_blog_says_so_once(): void
    {
        if (! Route::has('blog.admin.index')) {
            $this->markTestSkipped('The admin blog routes are registered on the nexus only.');
        }

        $html = $this->page('/admin/blog');

        $this->assertSame(1, substr_count($html, 'class="page-empty"'));
        $this->assertStringNotContainsString('<table class="page-table', $html);
    }

    public function test_a_post_can_be_written_by_hand_without_an_ai_key(): void
    {
        if (! Route::has('blog.create')) {
            $this->markTestSkipped('The admin blog routes are registered on the nexus only.');
        }

        $this->withoutAiKey();
        $html = $this->page('/admin/blog/create');

        // The form is there, the AI card is not, and the setup card says the key is optional here.
        $this->assertStringContainsString('action="'.route('blog.store').'"', $html);
        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringNotContainsString('id="generate_btn"', $html);
        $this->assertStringContainsString(__('messages.blog_ai_needs_key'), $html);
        $this->assertStringNotContainsString(__('messages.setup_required_gemini'), $html);

        // The way back is the list's name above the first card.
        $this->assertSame(1, substr_count($html, 'class="page-back"'));
        $this->assertStringNotContainsString(__('messages.back_to_posts'), $html);

        config(['services.google.gemini_key' => 'test-key']);
        $html = $this->page('/admin/blog/create');

        $this->assertStringContainsString('id="generate_btn"', $html);
        $this->assertStringNotContainsString(__('messages.blog_ai_needs_key'), $html);
    }

    /** The publish switch posts 0 or 1 where a checkbox posted 1 or nothing. Both must save. */
    public function test_the_publish_switch_saves_a_draft_and_a_published_post(): void
    {
        if (! Route::has('blog.store')) {
            $this->markTestSkipped('The admin blog routes are registered on the nexus only.');
        }

        $this->admin()->post(route('blog.store'), ['title' => 'A draft', 'content' => '<p>Body.</p>', 'is_published' => '0'])
            ->assertRedirect(route('blog.admin.index'));
        $this->admin()->post(route('blog.store'), ['title' => 'Out now', 'content' => '<p>Body.</p>', 'is_published' => '1', 'published_at' => '2026-01-05 09:30'])
            ->assertRedirect(route('blog.admin.index'));

        $draft = BlogPost::where('title', 'A draft')->firstOrFail();
        $live = BlogPost::where('title', 'Out now')->firstOrFail();

        $this->assertFalse($draft->is_published);
        $this->assertNull($draft->published_at);
        $this->assertTrue($live->is_published);
        $this->assertSame('2026-01-05 09:30', $live->published_at->format('Y-m-d H:i'));

        $html = $this->page(route('blog.edit', $live->encodeId()));

        $this->assertStringContainsString('value="2026-01-05 09:30"', $html);
        $this->assertStringNotContainsString('type="datetime-local"', $html);
        $this->assertSame(1, substr_count($html, 'class="page-back"'));
    }

    public function test_the_ai_setup_card_is_a_plain_card_with_three_steps(): void
    {
        $this->withoutAiKey();

        $html = Blade::render('<x-gemini-setup-guide />');

        $this->assertStringContainsString('ap-card', $html);
        $this->assertStringContainsString(__('messages.setup_required_gemini'), $html);
        $this->assertSame(1, substr_count($html, '<ol '));
        $this->assertSame(3, substr_count($html, '<li>'));
        $this->assertStringContainsString('GEMINI_API_KEY=your_api_key_here', $html);
        $this->assertStringNotContainsString('gradient', $html);
        $this->assertStringNotContainsString('amber', $html);
        $this->assertStringNotContainsString('orange', $html);

        // On a page that works without a key, and inside a card that is already there.
        $html = Blade::render('<x-gemini-setup-guide optional flat text="Only the draft needs it." />');

        $this->assertStringContainsString('Only the draft needs it.', $html);
        $this->assertStringContainsString(__('messages.optional'), $html);
        $this->assertStringNotContainsString('ap-card', $html);

        config(['services.openai.api_key' => 'test-key']);
        $this->assertSame('', trim(Blade::render('<x-gemini-setup-guide />')));
    }

    /**
     * The picker's list is styled once, on its attribute, by the partial the three pages share.
     * The one on /admin/boost had no background and the page showed through it.
     */
    public function test_the_schedule_picker_carries_its_own_look(): void
    {
        $partial = File::get(resource_path('views/admin/partials/_subdomain-autocomplete.blade.php'));

        $this->assertStringContainsString('[data-subdomain-dropdown] {', $partial);
        $this->assertStringContainsString('background: rgb(var(--ap-surface));', $partial);
        // Built from text nodes: a schedule's name never passes through innerHTML.
        $this->assertStringNotContainsString('row.innerHTML', $partial);
    }
}
