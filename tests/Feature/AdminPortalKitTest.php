<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What every page of the admin portal shares (October 2026): the two kits, loaded once by the
 * layout; the title row a page opens with; the platform admin's navigation; one list where a page
 * used to draw the same rows twice.
 *
 * Each test is something a person could see. What the pages look like belongs to the screenshots;
 * these hold what they must say. Assertions count matches with substr_count()/preg_match() and
 * never hand the whole page to a pattern assertion: a failure would print 600 KB of HTML.
 */
class AdminPortalKitTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // No page here may reach the network: the suite loads the developer's .env, where real
        // tokens live, and a page that asks an outside service (the Domains page asked
        // DigitalOcean) would do it for real on every run. A request nothing faked throws.
        \Illuminate\Support\Facades\Http::preventStrayRequests();
    }

    private function adminActing(): User
    {
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        return $admin;
    }

    public function test_every_page_of_the_portal_has_the_two_kits_exactly_once(): void
    {
        // The kits were included page by page, by the three forms and a schedule's tabs. The
        // layout carries them now, so a page that includes one again would ship it twice, and a
        // page that never did (every other page of the portal) has them.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $pages = [
            'my tickets' => route('tickets'),
            'the schedule form' => route('role.edit', ['subdomain' => $role->subdomain]),
            'a schedule tab' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']),
            'settings' => route('profile.edit'),
            'the event form' => route('event.create', ['subdomain' => $role->subdomain]),
        ];

        foreach ($pages as $name => $url) {
            $html = $this->actingAs($owner)->get($url)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '.form-kit-col {'), "the form kit, once, on $name");
            $this->assertSame(1, substr_count($html, '.page-top {'), "the page kit, once, on $name");
            $this->assertLessThan(strpos($html, '</head>'), strpos($html, '.page-top {'), "in the head, ahead of the page's own rules, on $name");
        }
    }

    public function test_a_page_that_hangs_from_a_schedule_names_the_way_back(): void
    {
        // "Back" on the right of the title, where the page's own action belongs, and nothing
        // saying where it led. The way back is a link over the title that names the schedule.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Moe Szyslak Tavern']);
        $team = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']);

        $html = $this->actingAs($owner)->get(route('role.create_member', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1 class="page-title"'), 'one title');
        $this->assertSame(1, preg_match('/<a href="'.preg_quote($team, '/').'" class="page-back">.*?<bdi>Moe Szyslak Tavern<\/bdi>/s', $html), 'the way back is the Team tab, by the schedule\'s name');

        // The form ends with Cancel and then the button that goes on, both in the kit's voice.
        $this->assertSame(1, preg_match('/<div class="page-form-actions">(.*?)<\/div>/s', $html, $actions));
        $this->assertLessThan(strpos($actions[1], __('messages.save')), strpos($actions[1], __('messages.cancel')), 'Cancel first, Save last');
        $this->assertSame(0, substr_count($html, 'data-fallback-url='), 'the grey capitals Cancel button is gone');
    }

    public function test_my_tickets_is_one_list_at_every_width(): void
    {
        // The page drew every ticket twice: a table from a tablet up and a stack of cards for a
        // phone, each with its own copy of the status pill.
        $owner = $this->createOwner();
        $buyer = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['name' => 'Flaming Moe Night']);
        $this->createSale($event, $role, ['user_id' => $buyer->id, 'status' => 'paid']);

        $html = $this->actingAs($buyer)->get(route('tickets'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<table class="page-table'), 'one list');
        $this->assertSame(1, substr_count($html, 'Flaming Moe Night'), 'the event is named once');
        $this->assertSame(1, substr_count($html, '<span class="event-status is-on">'.__('messages.paid').'</span>'), 'and so is what became of the order');
        $this->assertSame(1, substr_count($html, '<h1 class="page-title"'));
    }

    public function test_the_platform_admins_navigation_says_which_page_this_is(): void
    {
        // The groups were menus hanging from their tab: on any page inside one, only "System" was
        // lit, and nothing on screen named the page.
        config(['app.hosted' => true, 'auth.admin_requires_two_factor' => false]);
        $this->adminActing();

        $html = $this->get(route('admin.audit_log'))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<h1 class="sr-only">([^<]*)<\/h1>/', $html, $h1), 'the page has a name');
        $this->assertStringContainsString(__('messages.audit_log'), $h1[1]);

        // Five tabs; the group this page is in is the lit one.
        $this->assertSame(1, preg_match('/<nav class="ap-tabs" id="admin-nav"[^>]*>(.*?)<\/nav>/s', $html, $nav));
        $this->assertSame(5, substr_count($nav[1], 'class="ap-tab"'));
        $this->assertSame(1, substr_count($nav[1], 'aria-current='));
        $this->assertSame(1, preg_match('/data-admin-group="system"\s+aria-current="true"/', $nav[1]));

        // The second row lists the group's pages and marks this one.
        $this->assertSame(1, preg_match('/<nav class="ap-subtabs[^"]*"[^>]*>(.*?)<\/nav>/s', $html, $row));
        $this->assertSame(1, substr_count($row[1], 'aria-current="page"'));
        foreach (['audit_log', 'queue', 'logs', 'settings', 'translations', 'legal_pages'] as $key) {
            $this->assertSame(1, substr_count($row[1], __('messages.'.$key)), "$key is in the row");
        }

        // A phone gets every page in one dropdown, with this one chosen.
        $this->assertSame(1, preg_match('/<select id="admin-nav-select"[^>]*>(.*?)<\/select>/s', $html, $select));
        $this->assertSame(3, substr_count($select[1], '<optgroup'));
        $this->assertSame(1, preg_match('/<option value="'.preg_quote(route('admin.audit_log'), '/').'" selected>/', $select[1]));

        // The menus were the last Alpine on these pages.
        $this->assertSame(0, substr_count($html, 'openDropdown'));
    }

    public function test_the_navigation_has_no_words_left_in_english(): void
    {
        // "Boost", "Logs" and "Support" were typed into the template.
        config(['app.hosted' => true, 'auth.admin_requires_two_factor' => false]);
        $admin = $this->adminActing();
        $admin->language_code = 'de';
        $admin->save();

        $html = $this->get(route('admin.queue'))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<nav class="ap-subtabs[^"]*"[^>]*>(.*?)<\/nav>/s', $html, $row));
        $this->assertSame(1, substr_count($row[1], __('messages.logs', [], 'de')));
        $this->assertSame(0, preg_match('/>\s*Logs\s*</', $row[1]));
        $this->assertSame(1, substr_count($row[1], __('messages.support', [], 'de')));
    }

    public function test_what_the_last_request_said_is_said_once(): void
    {
        // layouts/app toasts `error`; a page that also printed it showed a red toast over a red
        // box. A page that prints a key tells the layout, and the toast for that key stands down.
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue');

        $quiet = $this->actingAs($owner)->get(route('newsletter.index'))->assertOk()->getContent();
        $toasts = substr_count($quiet, '.showToast()');

        $html = $this->actingAs($owner)->withSession(['error' => 'The card was declined'])
            ->get(route('newsletter.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'The card was declined'), 'said once');
        $this->assertSame(1, preg_match('/role="alert"[^>]*>.*?The card was declined/s', $html), 'as a notice on the page');
        $this->assertSame($toasts, substr_count($html, '.showToast()'), 'and not as a toast as well');
    }

    public function test_a_flash_the_page_does_not_print_is_still_toasted(): void
    {
        // The note is left on the request. Shared with the view factory it outlived the request
        // inside one test, and the next page lost its toast; and a page that prints `success`
        // only must not silence an `error`.
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue');

        // A page that printed the error itself...
        $this->actingAs($owner)->withSession(['error' => 'First'])->get(route('newsletter.index'))->assertOk();

        // ...then one that prints no flash at all: the layout says it.
        $html = $this->actingAs($owner)->withSession(['error' => 'Second problem'])
            ->get(route('tickets'))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/Toastify\(\{\s*text: "Second problem"/', $html), 'toasted');
        $this->assertSame(0, substr_count($html, 'role="alert"'), 'and not printed twice');

        // My carpools prints `success` only: an error there is the layout's to say.
        $html = $this->actingAs($owner)->withSession(['error' => 'Third problem'])
            ->get(route('my_carpools'))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/Toastify\(\{\s*text: "Third problem"/', $html));
    }

    public function test_an_attendee_import_says_what_it_did(): void
    {
        // The import ends on Sales with "Imported 12 attendees. Skipped 2 rows." flashed as
        // `status`, a key the layout does not toast and the page did not print.
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue');

        $html = $this->actingAs($owner)->withSession(['status' => 'Imported 12 attendees. Skipped 2 rows.'])
            ->get(route('sales'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'Imported 12 attendees. Skipped 2 rows.'));
    }

    public function test_a_schedules_audit_log_names_its_categories_in_the_readers_language(): void
    {
        // The category filter printed its keys with a capital letter: "Sale", "Schedule", in
        // every language.
        $owner = $this->createOwner();
        $owner->language_code = 'de';
        $owner->save();
        $role = $this->createRole($owner, 'venue');

        $html = $this->actingAs($owner)->get(route('role.audit_log', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<select name="category"[^>]*>(.*?)<\/select>/s', $html, $select));
        $this->assertSame(1, preg_match('/<option value="schedule"[^>]*>'.preg_quote(__('messages.schedule', [], 'de'), '/').'<\/option>/', $select[1]));
        $this->assertSame(0, substr_count($select[1], '>Schedule<'));
        $this->assertSame(1, substr_count($html, 'class="page-back"'), 'and the page hangs from its schedule');
    }

    /** A class attribute that carries every one of these tokens, in any order. */
    private function hasClasses(string $html, array $tokens, string $before = ''): bool
    {
        $lookaheads = implode('', array_map(fn ($token) => '(?=[^"]*(?<![\w-])'.preg_quote($token, '/').'(?![\w-]))', $tokens));

        return preg_match('/'.$before.'class="'.$lookaheads.'[^"]*"/', $html) === 1;
    }

    /**
     * The kit's rules as [media query or null, selector, body], comments out. Enough of a parser
     * for one stylesheet that nests nothing deeper than a rule inside one media block, and what
     * lets a test say "this rule holds at every width" or "this one starts at 90rem".
     */
    private function rules(string $view): array
    {
        $css = preg_replace(['/\{\{--.*?--\}\}/s', '/\/\*.*?\*\//s'], '', file_get_contents(resource_path("views/$view.blade.php")));
        $rules = [];
        $media = null;
        $buffer = '';
        $depth = 0;
        foreach (str_split($css) as $char) {
            if ($char === '{') {
                $selector = trim($buffer);
                $buffer = '';
                if (str_starts_with($selector, '@media')) {
                    $media = trim(substr($selector, 6));
                } else {
                    $current = $selector;
                }
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if (isset($current)) {
                    $rules[] = [$media, preg_replace('/\s+/', ' ', $current), preg_replace('/\s+/', ' ', trim($buffer))];
                    unset($current);
                } else {
                    $media = null;
                }
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }

        return $rules;
    }

    /** The bodies of every rule whose selector list names this selector, keyed by media query. */
    private function declared(array $rules, string $selector): array
    {
        $found = [];
        foreach ($rules as [$media, $selectors, $body]) {
            if (in_array($selector, array_map('trim', explode(',', $selectors)), true)) {
                $found[] = [$media, $body];
            }
        }

        return $found;
    }

    /**
     * On a wide window the header and tabs of a page ran to its edge while lists and cards
     * stopped at 64rem against the sidebar, so one page had two right-hand edges. Every page is
     * one frame now: the layout caps <main>, brings the bar above it in to the same edges, and
     * the 64rem column is gone from the kit and from every view.
     */
    public function test_every_page_of_the_portal_shares_one_frame(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        foreach ([route('tickets'), route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']), route('profile.edit')] as $url) {
            $html = $this->actingAs($owner)->get($url)->assertOk()->getContent();

            // On <main> itself: SetupGuide.vue measures #main-content to dock beside a form.
            $this->assertTrue($this->hasClasses($html, ['ap-frame'], '<main id="main-content"[^>]*'), $url);
            $this->assertTrue($this->hasClasses($html, ['sticky', 'header-gradient', 'ap-frame-bar']), 'the bar above the page');
            $this->assertTrue($this->hasClasses($html, ['mt-auto', 'ap-frame']), 'the line under the page');
            $this->assertFalse($this->hasClasses($html, ['is-wide'], '<main id="main-content"[^>]*'), 'only a canvas lifts the cap');
        }

        $kit = $this->rules('partials/admin-page-styles');
        [[$media, $frame]] = $this->declared($kit, '.ap-frame');
        $this->assertNull($media);
        $this->assertStringContainsString('max-width: 84rem;', $frame);
        $this->assertStringContainsString('margin-inline: auto;', $frame);
        $this->assertSame([], $this->declared($kit, '.page-col'), 'no column of its own for lists and cards');

        // The class stays only where it means "one form": every other use capped a page.
        $plain = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (str_ends_with($file->getPathname(), '.blade.php') && ! str_ends_with($file->getPathname(), 'admin-page-styles.blade.php')
                && preg_match('/\bpage-col\b(?! is-narrow)/', file_get_contents($file->getPathname()))) {
                $plain[] = str_replace(resource_path('views').'/', '', $file->getPathname());
            }
        }
        $this->assertSame([], $plain);
    }

    /** The two canvases want every pixel: a room plan in a 1280px frame is a smaller room plan. */
    public function test_a_canvas_keeps_the_whole_window(): void
    {
        foreach (['role/seating-designer', 'role/seating-box-office'] as $view) {
            $this->assertSame(1, preg_match('/<x-app-admin-layout\b[^>]*\swide\b[^>]*>/', file_get_contents(resource_path("views/$view.blade.php"))), $view);
        }

        // The layout alone, outside a request: it wants somebody signed in and the error bag the
        // session middleware would have shared.
        $this->actingAs($this->createOwner());
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);
        $html = \Illuminate\Support\Facades\Blade::render('<x-app-admin-layout wide><p>canvas</p></x-app-admin-layout>');
        $this->assertTrue($this->hasClasses($html, ['ap-frame', 'is-wide'], '<main id="main-content"[^>]*'));
        $this->assertTrue($this->hasClasses($html, ['header-gradient', 'ap-frame-bar', 'is-wide']));
    }

    /**
     * A page that is one form with no navigation above it is one column in the middle of the
     * frame, of one width, and its header is in the column with it. The three that existed were
     * 576, 640 and 808px with three left edges; the Eventbrite import ran the whole width with a
     * 512px field in it; Scan agenda had its title at the frame's edge over a body centred on
     * its own 672px; and the plan checkout, left at the frame's width, had Subscribe in the
     * middle of its card.
     */
    public function test_a_page_that_is_one_form_is_one_centred_column(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        $pages = [
            route('role.create_member', ['subdomain' => $role->subdomain]),
            route('event.show_import_eventbrite', ['subdomain' => $role->subdomain]),
            route('event.scan_agenda', ['subdomain' => $role->subdomain]),
            route('ticket.scan'),
        ];
        foreach ($pages as $url) {
            $html = $this->actingAs($owner)->get($url)->assertOk()->getContent();
            $this->assertTrue($this->hasClasses($html, ['page-shell', 'page-col', 'is-narrow']), $url);
            // The page's own title is inside the column, not at the frame's edge above it.
            $this->assertSame(1, preg_match('/class="[^"]*\bis-narrow\b[^"]*">.*?<h1 class="page-title/s', $html), $url);
        }
        // The checkout cannot be opened without asking Stripe for a setup intent (ApDoorPagesTest
        // renders its view directly), so its wrapper is read from the source.
        $this->assertTrue($this->hasClasses(file_get_contents(resource_path('views/subscription/show.blade.php')), ['page-shell', 'page-col', 'is-narrow']));

        $kit = $this->rules('partials/admin-page-styles');
        [[$media, $narrow]] = $this->declared($kit, '.page-col.is-narrow');
        $this->assertNull($media);
        $this->assertStringContainsString('max-width: 48rem;', $narrow);
        $this->assertStringContainsString('margin-inline: auto;', $narrow);

        // What a refused form got wrong is listed by the layout, above the page and at the
        // frame's width: over a centred column it is brought in to the column, or the notice
        // starts 16rem to the left of the form it is about.
        [[$media, $errors]] = $this->declared($kit, '.ap-form-errors:has(~ .page-col.is-narrow)');
        $this->assertNull($media);
        $this->assertStringContainsString('max-width: 48rem;', $errors);
        $this->assertStringContainsString('margin-inline: auto;', $errors);
        // And the layout's list is the element that rule finds.
        $this->actingAs($owner)->post(route('role.store_member', ['subdomain' => $role->subdomain]), [])->assertSessionHasErrors();
        $refused = $this->actingAs($owner)->get(route('role.create_member', ['subdomain' => $role->subdomain]))->getContent();
        $this->assertSame(1, preg_match('/<div(?=[^>]*\\brole="alert")(?=[^>]*\\bap-form-errors\\b)[^>]*>/', $refused));

        // Under the platform admin's navigation, which runs the frame, a narrower column had two
        // left edges on one screen: a form there fills the frame.
        foreach (['admin/schedules-edit', 'blog/create', 'blog/edit'] as $view) {
            $this->assertFalse($this->hasClasses(file_get_contents(resource_path("views/$view.blade.php")), ['is-narrow']), $view);
        }
    }

    /**
     * From a 1440px window a settings card puts its title on one side and its body on the other,
     * so the card runs to the frame's edges without a form stretched across it. A page does this
     * for all of its titled cards that are not lists, or for none: card by card, the content of
     * one page went left, right, full, right, full on its way down.
     */
    public function test_a_settings_page_sets_all_of_its_cards_side_by_side_or_none(): void
    {
        $card = fn (string $props) => \Illuminate\Support\Facades\Blade::render('<x-page-card '.$props.'><p>body</p></x-page-card>');
        $this->assertStringContainsString('page-card is-beside', $card('beside title="Plan pricing"'));
        $this->assertStringNotContainsString('is-beside', $card('title="Plan pricing"'));
        // The card never invents a title to stand beside its body.
        $this->assertStringNotContainsString('is-beside', $card('beside'));

        // Every view that sets one card side by side sets all of its titled cards that are not
        // lists: found by reading the views, not from a list of them kept here.
        $pages = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! str_ends_with($file->getPathname(), '.blade.php')) {
                continue;
            }
            preg_match_all('/<x-page-card\b[^>]*>/s', file_get_contents($file->getPathname()), $cards);
            $titled = array_filter($cards[0], fn ($tag) => preg_match('/\s:?title=/', $tag) && ! preg_match('/\sflush\b/', $tag));
            $beside = array_filter($titled, fn ($tag) => preg_match('/\sbeside\b/', $tag));
            if ($beside) {
                $pages[str_replace([resource_path('views').'/', '.blade.php'], '', $file->getPathname())] = [count($titled), count($beside)];
            }
        }
        $this->assertSame(
            ['admin/app-update', 'admin/legal', 'admin/newsletters/segment-edit', 'admin/newsletters/segments', 'admin/schedules-edit', 'admin/settings', 'newsletter/segment-edit', 'newsletter/segments'],
            collect($pages)->keys()->sort()->values()->all()
        );
        foreach ($pages as $view => [$titled, $beside]) {
            $this->assertSame($titled, $beside, "every titled card of $view that is not a list");
        }
    }

    /**
     * What keeps a full frame from looking stretched, and at which widths. The first version
     * capped a form at 48rem only from a 1536px window, but the frame is wider than the old
     * column from 1377px: on a 1440px laptop a schedule's admin edit page had inputs 1048px wide,
     * and at 1536px they snapped back to 768px, so a wider window gave a narrower form. The cap
     * also reached button rows and pairs inside cards that were not capped themselves (Merge
     * 480px short of the rows it acts on; two right edges for the numbers of one card).
     */
    public function test_a_form_keeps_its_measure_at_every_width(): void
    {
        $kit = $this->rules('partials/admin-page-styles');

        foreach (['.page-form-fields', '.page-form-fields + .page-form-actions', '.page-card > .page-kv'] as $selector) {
            $rules = $this->declared($kit, $selector);
            $capped = array_values(array_filter($rules, fn ($rule) => str_contains($rule[1], 'max-width: 48rem;')));
            $this->assertCount(1, $capped, $selector);
            $this->assertNull($capped[0][0], "$selector holds at every width");
        }
        // A side-by-side card holds its whole body to the measure while it is still stacked.
        $stacked = array_values(array_filter($this->declared($kit, '.page-card.is-beside > :not(.page-card-head)'), fn ($rule) => $rule[0] === null));
        $this->assertCount(1, $stacked);
        $this->assertStringContainsString('max-width: 48rem;', $stacked[0][1]);
        // Not every button row in a card, and not every pair: only the ones beside capped fields.
        $this->assertSame([], array_filter($this->declared($kit, '.page-card .page-form-actions'), fn ($rule) => str_contains($rule[1], 'max-width')));
        $this->assertSame([], array_filter($this->declared($kit, '.page-kv'), fn ($rule) => str_contains($rule[1], 'max-width')));

        // Side by side from the width at which the card first has room for a title beside 48rem
        // of form, and never with a title column of nothing.
        [[$media, $grid]] = $this->declared($kit, '.page-card.is-beside');
        $this->assertSame('(min-width: 90rem)', $media);
        $this->assertStringContainsString('grid-template-columns: minmax(14rem, 1fr) minmax(0, 48rem);', $grid);

        // A bar that reaches past the gutters keeps doing so (it covers what scrolls under it)
        // and draws its line at the frame's width, from where the frame stops growing.
        foreach ([['partials/form-kit-styles', '.event-save-bar::before'], ['partials/admin-page-styles', '.ap-frame-bleed::after']] as [$view, $selector]) {
            [[$media, $line]] = $this->declared($this->rules($view), $selector);
            $this->assertSame('(min-width: 102rem)', $media, $selector);
            $this->assertStringContainsString('inset-inline: 2rem;', $line);
        }
        foreach ($this->declared($this->rules('partials/form-kit-styles'), '.event-save-bar') as [$media, $body]) {
            $this->assertStringNotContainsString('margin-inline: 0', $body, 'the bar keeps its bleed');
        }
    }

    /**
     * Small things two reviewers saw on pages that were otherwise right, each of them one page
     * (or one component) out of step with the rest: the admin tabs 16px lower on two pages, a
     * red button 4px shorter than the Cancel beside it, a usage bar 1,230px long, a bar fixed to
     * the window where the page is centred on its room.
     */
    public function test_the_small_things_stay_in_step(): void
    {
        // The navigation is ahead of the page's stack on every admin page. Inside the stack, as
        // its second child after the hidden heading, it took the stack's gap, and the tabs
        // jumped 16px between Dashboard or Realtime and any other page.
        $this->adminActing();
        foreach (['/admin/dashboard', '/admin/realtime', '/admin/users'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $nav = strpos($html, '<div class="admin-nav">');
            $this->assertNotFalse($nav, $url);
            $this->assertSame(0, preg_match('/<div class="space-y-4"[^>]*>\s*(?:<!--.*?-->\s*)*<h1 class="sr-only">/s', $html), "$url: the navigation is not inside a stack");
        }

        $kit = $this->rules('partials/admin-page-styles');
        // An older capitals button in a row of actions takes the brand button's type and so its
        // height.
        [[$media, $pair]] = $this->declared($kit, '.page-shell .page-form-actions > button.uppercase');
        $this->assertNull($media);
        $this->assertStringContainsString('font-size: 1rem;', $pair);
        $this->assertStringContainsString('line-height: 1.5rem;', $pair);

        // A bar fixed to the foot of the page starts where the sidebar ends.
        $foot = $this->declared($kit, '.ap-foot-bar');
        $this->assertSame([null, '(min-width: 1024px)'], array_column($foot, 0));
        $this->assertStringContainsString('inset-inline-start: 18rem;', $foot[1][1]);
        $this->assertSame(1, substr_count(file_get_contents(resource_path('views/event/scan-agenda.blade.php')), 'class="ap-foot-bar '));

        // A usage bar that is a panel of its own keeps to the form measure; the compact one
        // inside another panel takes the width it is given.
        $meter = fn (string $variant) => \Illuminate\Support\Facades\Blade::render('<x-usage-meter label="Photos" :used="5" :limit="25" variant="'.$variant.'" />');
        $this->assertSame(1, preg_match('/<div class="max-w-3xl">\s*<h5/', $meter('panel')));
        $this->assertStringNotContainsString('max-w-3xl', $meter('inline'));

        // One size of Save on Platform settings: each card's Save is that card's one action.
        $settings = file_get_contents(resource_path('views/admin/settings.blade.php'));
        $this->assertSame(7, preg_match_all('/<x-brand-button type="submit">\{\{ __\(\'messages\.save\'\) \}\}<\/x-brand-button>/', $settings));
        $this->assertSame(0, preg_match_all('/<x-brand-button[^>]*size="sm"/', $settings));
    }
}
