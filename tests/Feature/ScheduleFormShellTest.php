<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The schedule form's shell: what the page is built from, as the event form's is.
 *
 * Each tab says what it holds under its name and marks itself when it has unsaved changes; a tab
 * inside a tab is a row that opens in place, with its setting on the row; there is one Save, in a
 * bar that also says what saving will remove. The behaviour is driven in a browser by
 * tests/Browser/ScheduleFormJourneyTest; this file pins the markup that behaviour is wired to.
 */
class ScheduleFormShellTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function formHtml(User $user, Role $role): string
    {
        return $this->actingAs($user)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
    }

    /** @return array{0: User, 1: Role, 2: string} */
    private function talentForm(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        return [$owner, $role, $this->formHtml($owner, $role)];
    }

    public function test_there_is_one_save_and_it_is_in_the_bar(): void
    {
        [, , $html] = $this->talentForm();

        $form = substr($html, strpos($html, 'id="edit-form"'));
        $form = substr($form, 0, strpos($form, '</form>'));

        $this->assertSame(1, substr_count($form, 'type="submit"'), 'one Save, where the sidebar and the phone bar each had their own');
        $this->assertStringContainsString('id="form-save-bar"', $form, 'and the bar is inside the form it saves');
        $this->assertStringContainsString('event-bar-save', $form);
        $this->assertSame(1, substr_count($html, 'class="js-cancel-btn'), 'Cancel is the bar\'s, which asks before it throws anything away');
        $this->assertStringContainsString('id="form-cancel-real"', $form);
    }

    public function test_no_tab_holds_a_strip_of_tabs(): void
    {
        [, , $html] = $this->talentForm();

        $this->assertStringNotContainsString('ap-tab-container', $html);
        // The keys the strips remembered their tab under are cleared, never written.
        $this->assertStringNotContainsString("localStorage.setItem('detailsActiveTab'", $html);
        $this->assertStringNotContainsString("localStorage.setItem('settingsActiveTab'", $html);
    }

    /** A row names the pane it opens, and the pane starts closed. */
    public function test_every_inner_tab_is_a_row_with_a_pane_that_starts_closed(): void
    {
        [, , $html] = $this->talentForm();

        $rows = [
            'details' => ['localization' => 'details-tab-localization', 'contact' => 'details-tab-contact'],
            'style' => ['background' => 'style-content-background', 'advanced' => 'style-content-advanced'],
            'customize' => [
                'subschedules' => 'customize-tab-subschedules', 'custom-fields' => 'customize-tab-custom-fields',
                'categories' => 'customize-tab-categories', 'custom-labels' => 'customize-tab-custom-labels',
            ],
            'settings' => ['notifications' => 'settings-tab-notifications', 'advanced' => 'settings-tab-advanced'],
            'engagement' => [
                'requests' => 'engagement-tab-requests', 'fan_content' => 'engagement-tab-fan_content',
                'feedback' => 'engagement-tab-feedback', 'carpool' => 'engagement-tab-carpool', 'sponsors' => 'engagement-tab-sponsors',
            ],
            'integration' => [
                'email' => 'integration-tab-email', 'google' => 'integration-tab-google', 'microsoft' => 'integration-tab-microsoft',
                'caldav' => 'integration-tab-caldav', 'feeds' => 'integration-tab-feeds', 'advanced' => 'integration-tab-advanced',
            ],
        ];

        foreach ($rows as $group => $tabs) {
            foreach ($tabs as $tab => $pane) {
                $this->assertMatchesRegularExpression(
                    '/<button type="button"[^>]*data-row-group="'.$group.'" data-tab="'.preg_quote($tab, '/').'"\s+aria-expanded="false" aria-controls="'.preg_quote($pane, '/').'"/',
                    $html, "the {$group} row for {$tab}"
                );
                $this->assertMatchesRegularExpression(
                    '/<div id="'.preg_quote($pane, '/').'" class="event-subrow-body[^"]*"[^>]* hidden>/',
                    $html, "the pane {$pane} starts closed"
                );
                $this->assertStringContainsString('data-summary="'.$group.':'.$tab.'"', $html, "the {$group} row for {$tab} has a line to say its setting on");
            }
        }

        // What every schedule fills in first is on the page without opening anything.
        foreach (['details-tab-general', 'style-content-branding', 'settings-tab-general'] as $always) {
            $this->assertMatchesRegularExpression('/<div id="'.$always.'"[^>]*>/', $html);
            $this->assertDoesNotMatchRegularExpression('/<div id="'.$always.'"[^>]* hidden>/', $html, "{$always} is always open");
        }
    }

    /** The classes the Help link and the browser tests find the rows by. */
    public function test_the_rows_keep_the_names_the_tabs_were_found_by(): void
    {
        [, , $html] = $this->talentForm();

        foreach (['details-tab' => 'contact', 'customize-tab' => 'categories', 'settings-tab' => 'notifications', 'engagement-tab' => 'requests', 'integration-tab' => 'google'] as $class => $tab) {
            $this->assertMatchesRegularExpression('/<button type="button" class="event-subrow '.$class.'"[^>]*data-tab="'.$tab.'"/', $html);
        }
        $this->assertStringContainsString('id="style-tab-background"', $html);
        $this->assertStringContainsString('id="style-tab-advanced"', $html);
    }

    public function test_every_tab_says_what_it_holds_and_can_mark_itself_unsaved(): void
    {
        [, , $html] = $this->talentForm();

        preg_match_all('/class="section-nav-link" data-section="(section-[a-z-]+)"/', $html, $links);
        $this->assertGreaterThanOrEqual(8, count($links[1]));

        foreach ($links[1] as $section) {
            $this->assertSame(2, substr_count($html, 'class="section-nav-summary" data-summary="'.$section.'"'), "{$section}: under its name in the sidebar and in its phone header");
            $this->assertSame(2, substr_count($html, 'data-dirty-dot="'.$section.'"'), "{$section}: a dot in both places");
            $this->assertStringContainsString("id=\"{$section}\"", $html);
        }
    }

    public function test_the_address_is_under_the_title_and_cancel_is_not_beside_it(): void
    {
        [, $role, $html] = $this->talentForm();

        $this->assertMatchesRegularExpression('/<p class="event-eyebrow">'.preg_quote(__('messages.edit_schedule'), '/').'<\/p>\s*<h2[^>]*>\s*'.preg_quote($role->name, '/').'\s*<\/h2>/', $html, 'the title names the schedule');
        $this->assertStringContainsString('class="event-url-strip"', $html);
        $this->assertStringContainsString('<span class="event-url-path">/'.$role->subdomain.'</span>', $html);
        $this->assertStringNotContainsString('<!-- Header with Cancel Button -->', $html);
    }

    /** The lists say they were on the page, which is what lets a save empty one (and only then). */
    public function test_the_lists_post_their_markers(): void
    {
        [$owner, $role, $html] = $this->talentForm();

        $this->assertStringContainsString('name="groups_submitted" value="1"', $html);
        $this->assertStringContainsString('name="gift_card_amounts_submitted" value="1"', $html);

        config(['app.hosted' => false]);
        $this->assertStringContainsString('name="import_lists_submitted" value="1"', $this->formHtml($owner, $role), 'auto import is a selfhost tab');
    }

    public function test_a_sub_schedule_is_one_row_of_its_list(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $group = $this->createGroup($role, ['name' => 'Main stage', 'slug' => 'main-stage']);

        $html = $this->formHtml($owner, $role);

        $this->assertMatchesRegularExpression('/<div id="group-items" class="event-list">\s*<div class="event-list-row" data-list-row>/', $html, 'a row is a direct child of the list');
        $this->assertStringContainsString('name="groups['.$group->id.'][name]"', $html);
        $this->assertStringContainsString('id="group_slug_'.$group->id.'" name="groups['.$group->id.'][slug]"', $html, 'its address is still posted from the row');
        $this->assertStringContainsString('data-action="remove-list-row"', $html);
    }

    /** A refused save comes back with what was chosen, not with what is stored. */
    public function test_a_refused_save_keeps_the_choices_made_on_the_style_tab(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['background' => 'gradient', 'event_layout' => 'calendar']);
        $taken = $this->createRole($owner, 'talent');
        $editUrl = route('role.edit', ['subdomain' => $role->subdomain]);

        $this->actingAs($owner)->from($editUrl)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => 'Typed before the refusal',
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $taken->subdomain,
            'background' => 'solid',
            'event_layout' => 'list',
            'header_style' => 'compact',
        ])->assertRedirect($editUrl);

        $html = $this->actingAs($owner)->get($editUrl)->assertOk()->getContent();

        $this->assertStringContainsString('value="Typed before the refusal"', $html);
        foreach (['background_type_solid', 'event_layout_list', 'header_style_compact'] as $id) {
            $this->assertMatchesRegularExpression('/id="'.$id.'"[^>]*\schecked/s', $html, "{$id} is still the one chosen");
        }
        foreach (['background_type_gradient', 'event_layout_calendar', 'header_style_banner'] as $id) {
            $this->assertDoesNotMatchRegularExpression('/id="'.$id.'"[^>]*\schecked/s', $html);
        }
    }

    /**
     * The lists whose rows are added and removed by buttons are watched by id, so that a removal
     * marks its tab and is announced by the bar. Three of the ids named wrappers or nothing at
     * all, and those lists were silently unwatched: every id has to be on some schedule's form.
     */
    public function test_every_list_the_page_watches_exists(): void
    {
        $owner = $this->createOwner();
        $pages = $this->formHtml($owner, $this->createRole($owner, 'talent'))
            .$this->formHtml($owner, $this->createRole($owner, 'venue'))
            .$this->formHtml($owner, $this->createCurator($owner));
        config(['app.hosted' => false]);
        $pages .= $this->formHtml($owner, $this->createRole($owner, 'talent'));

        preg_match_all("/\\['(section-[a-z-]+)', '#([a-z_-]+)'\\],/", $pages, $watched, PREG_SET_ORDER);
        $ids = array_unique(array_map(fn ($match) => $match[2], $watched));
        $this->assertGreaterThanOrEqual(10, count($ids), 'sanity check: the registry was found');

        foreach ($ids as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $pages, "#{$id} is watched and is on no schedule's form");
        }
    }

    public function test_what_the_review_of_the_form_found_stays_fixed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->createGroup($role, ['name' => 'Main stage', 'slug' => 'main-stage']);
        $html = $this->formHtml($owner, $role);

        // The setup guide's ring rides above the save bar, where Save is.
        $this->assertStringContainsString('--sg-bar: 4.75rem', $html);
        // A sub-schedule's colour swatch has a name, and opens towards the row it ends.
        $this->assertMatchesRegularExpression('/groups\['.$role->groups()->first()->id.'\]\[color\]&quot;.*?&quot;label&quot;:&quot;'.preg_quote(__('messages.color'), '/').'&quot;,&quot;align&quot;:&quot;end&quot;/', $html);
        // The Add Link dialog is above the bar, not under it.
        $this->assertMatchesRegularExpression('/id="add_link_modal" class="hidden relative z-50"/', $html);
        // A control of another form inside this one's markup is not a change to the schedule.
        $this->assertStringContainsString("kit.track(form, { ignore: '[form]:not([form=\"edit-form\"])' });", $html);
        // A new row's key starts past the ones a refused save drew the page with.
        $this->assertStringContainsString('input[name^="groups[new_"]', $html);
    }

    /** Email was the tab that opened by itself, so a link to the tab alone meant "email settings". */
    public function test_links_to_the_email_settings_name_their_row(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $mail = new \App\Mail\EmailSettingsFailedMail($role, $owner, 'SMTP said no', now());
        $this->assertStringContainsString('#integration-tab-email', json_encode($mail->content()->with));
        $this->assertStringContainsString('id="integration-tab-email"', $this->formHtml($owner, $role), 'and the row is there to open');
    }

    /**
     * The CalDAV connection is the owner's (its controller refuses everyone else, and a save puts
     * the direction back). Another member used to get live radios whose choice was dropped
     * without a word, and a connect form that could only fail.
     */
    public function test_a_member_who_is_not_the_owner_sees_the_caldav_connection_and_cannot_change_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $editor = $this->createOwner();
        $role->users()->attach($editor->id, ['level' => 'admin']);

        // Not connected: the owner gets the form, the member gets told whose it is.
        $this->assertStringContainsString('id="caldav-connection-form"', $this->formHtml($owner, $role));
        $memberHtml = $this->formHtml($editor->fresh(), $role);
        $this->assertStringNotContainsString('id="caldav-connection-form"', $memberHtml);
        $this->assertStringContainsString('data-caldav-owner-note', $memberHtml);

        $role->caldav_settings = ['server_url' => 'https://caldav.example.org', 'username' => 'lisa', 'password' => 'secret', 'calendar_url' => 'https://caldav.example.org/cal'];
        $role->caldav_sync_direction = 'to';
        $role->save();

        $radio = '/<input type="radio"\s+name="caldav_sync_direction"\s+(disabled\s+)?value="/';
        $ownerHtml = $this->formHtml($owner, $role->fresh());
        preg_match_all($radio, $ownerHtml, $ownerRadios);
        $this->assertCount(4, $ownerRadios[0]);
        $this->assertSame(['', '', '', ''], $ownerRadios[1], 'the owner chooses the direction');
        $this->assertStringContainsString('id="caldav-disconnect-btn"', $ownerHtml);
        $this->assertStringNotContainsString('data-caldav-owner-note', $ownerHtml);

        $memberHtml = $this->formHtml($editor->fresh(), $role->fresh());
        preg_match_all($radio, $memberHtml, $memberRadios);
        $this->assertCount(4, $memberRadios[0], 'the member still sees how it is set');
        $this->assertNotContains('', $memberRadios[1], 'and cannot change it');
        $this->assertStringNotContainsString('id="caldav-disconnect-btn"', $memberHtml);
        $this->assertStringContainsString('data-caldav-owner-note', $memberHtml);
    }

    /**
     * Help follows the form: every tab and every row has an anchor in HelpUtils (keyed by the
     * tab's id, the row's pane id, or the row's own id, which is what layouts/navigation looks up
     * when one is pressed), and every anchor lands on a heading that exists in the user guide.
     */
    public function test_every_tab_and_row_has_a_help_anchor_that_exists_in_the_user_guide(): void
    {
        $owner = $this->createOwner();
        $pages = $this->formHtml($owner, $this->createRole($owner, 'talent'))
            .$this->formHtml($owner, $this->createRole($owner, 'venue'))
            .$this->formHtml($owner, $this->createCurator($owner));
        config(['app.hosted' => false]);
        $pages .= $this->formHtml($owner, $this->createRole($owner, 'talent'));

        $helpUtils = file_get_contents(app_path('Utils/HelpUtils.php'));
        $start = strpos($helpUtils, "'{subdomain}/edit' => [");
        $block = substr($helpUtils, $start, strpos($helpUtils, "'{subdomain}/edit-event/*'") - $start);
        preg_match_all("/'([a-z_-]+)' => '(\/docs\/[a-z\/-]+)(?:#([a-z0-9-]+))?'/", $block, $entries, PREG_SET_ORDER);
        $anchors = [];
        foreach ($entries as $entry) {
            if ($entry[1] !== 'doc') {
                $anchors[$entry[1]] = [$entry[2], $entry[3] ?? null];
            }
        }
        $this->assertGreaterThan(30, count($anchors), 'sanity check: the map was read');

        // Every tab.
        preg_match_all('/<div id="(section-[a-z-]+)" class="section-content/', $pages, $tabs);
        foreach (array_unique($tabs[1]) as $tab) {
            $this->assertArrayHasKey($tab, $anchors, "the {$tab} tab has no Help anchor");
        }

        // Every row: by the pane it opens, or by its own id.
        preg_match_all('/<button type="button"([^>]*)data-row-group="[a-z]+" data-tab="[a-z_-]+"\s+aria-expanded="false" aria-controls="([a-z_-]+)"/', $pages, $rows, PREG_SET_ORDER);
        $this->assertGreaterThan(20, count($rows));
        foreach ($rows as $row) {
            $id = preg_match('/\bid="([a-z_-]+)"/', $row[1], $own) ? $own[1] : null;
            $this->assertTrue(isset($anchors[$row[2]]) || ($id && isset($anchors[$id])), "the row that opens #{$row[2]} has no Help anchor");
        }

        // Every anchor: an element of the form, and a heading of the guide. The form's source and
        // not a rendered page, because some tabs and rows only exist for some schedules (a venue
        // that can be merged, an install with the accommodation map); with its Blade comments
        // taken out, so markup that was commented away does not count as being there.
        $source = preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents(resource_path('views/role/edit.blade.php')));
        foreach ($anchors as $key => [$doc, $fragment]) {
            $this->assertStringContainsString('id="'.$key.'"', $source, "Help names #{$key}, which is not on the schedule form");
            $view = resource_path('views/marketing'.$doc.'.blade.php');
            $this->assertFileExists($view, "Help sends #{$key} to {$doc}, which is not a page of the guide");
            if ($fragment) {
                $this->assertStringContainsString('id="'.$fragment.'"', file_get_contents($view), "Help sends #{$key} to {$doc}#{$fragment}, which is not a heading there");
            }
        }
    }

    /** The final pass over each panel: what it changed, pinned where a browser is not needed to see it. */
    public function test_lists_are_one_line_each_and_long_panes_are_in_named_groups(): void
    {
        [, , $html] = $this->talentForm();

        // A category is a row of its list, as a sub-schedule is: it was a card of three stacked fields.
        $this->assertMatchesRegularExpression('/<div id="event-categories-container" class="event-list sched-list"[^>]*>\s*<div class="event-list-row event-category-item" data-list-row data-category-id="\d+"/', $html);
        $this->assertSame(12, preg_match_all('/class="event-list-row event-category-item" data-list-row data-category-id="\d+"/', $html), 'the twelve defaults');

        // Settings > Advanced: three headings, in order, and the event URL pattern under the first.
        $advanced = substr($html, strpos($html, 'id="settings-tab-advanced"'));
        $advanced = substr($advanced, 0, strpos($advanced, '<!-- End Tab Content: Advanced -->'));
        $order = array_map(fn ($needle) => strpos($advanced, $needle), [
            '>'.__('messages.settings_group_new_events').'</p>', 'name="default_event_visibility"', 'name="slug_pattern"', 'name="default_category_id"',
            '>'.__('messages.settings_group_public_page').'</p>', 'name="hide_past_events"', 'name="first_day_of_week"',
            '>'.__('messages.ai_import').'</p>', 'id="import_form_fields_section"',
        ]);
        $this->assertNotContains(false, $order, 'each heading and each of its settings is in the row');
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order, 'in the order the headings promise');
        $general = substr($html, strpos($html, 'id="settings-tab-general"'), strpos($html, 'id="settings-tab-notifications"') - strpos($html, 'id="settings-tab-general"'));
        $this->assertStringNotContainsString('name="slug_pattern"', $general, 'the first screen of Settings is the address');

        // Settings > Notifications: by who the email goes to.
        $notifications = substr($html, strpos($html, 'id="settings-tab-notifications"'), strpos($html, 'id="settings-tab-advanced"') - strpos($html, 'id="settings-tab-notifications"'));
        $order = array_map(fn ($needle) => strpos($notifications, $needle), [
            '>'.__('messages.notify_group_you').'</p>', 'name="notification_new_request"', 'name="notification_new_sale"',
            '>'.__('messages.notify_group_followers').'</p>', 'name="announce_new_events"',
            '>'.__('messages.notify_group_shared').'</p>', 'id="notification-email-section"',
        ]);
        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order);

        // Style: the animation picker is a row, and the rows say what they hold.
        $this->assertMatchesRegularExpression('/data-row-group="style" data-tab="animation"\s+aria-expanded="false" aria-controls="style-content-animation"/', $html);
        $this->assertMatchesRegularExpression('/<div id="style-content-animation" class="event-subrow-body" hidden>/', $html);
        $this->assertStringContainsString('<span class="event-row-title">'.__('messages.style_row_header_layout').'</span>', $html);
        $this->assertStringContainsString('<span class="event-row-title">'.__('messages.integration_row_feeds').'</span>', $html);
        $this->assertStringContainsString('<span class="event-row-title">'.__('messages.language_and_time').'</span>', $html);

        // Gift cards: what a gift card needs is under the switch, shown once it is on.
        $this->assertMatchesRegularExpression('/<div id="gift-card-details"\s+hidden\s*>/', $html);

        // Sponsors: an empty line, a link, and the form behind it.
        $this->assertMatchesRegularExpression('/<p class="event-empty" id="sponsors-empty"\s*>/', $html);
        $this->assertMatchesRegularExpression('/<div id="sponsor-form-shell" hidden>\s*<div id="add-sponsor-form"/', $html);
        $this->assertMatchesRegularExpression('/id="sponsor-background-controls"\s+hidden/', $html);

        // A custom field's rarely used parts fold away, in the card a new field is built from too.
        $this->assertStringContainsString('<div class="sched-field-more" hidden>', $html);
        $this->assertStringContainsString('data-action="toggle-field-more"', $html);

        // Connecting a calendar is a button, and the two colours of a gradient are named.
        $this->assertMatchesRegularExpression('/<a href="[^"]*#section-google-calendar" class="ap-secondary-btn[^"]*"[^>]*>\s*'.preg_quote(__('messages.connect_google_calendar'), '/').'/', $html);
        $this->assertMatchesRegularExpression('/<label[^>]*for="custom_color1"[^>]*>\s*'.preg_quote(__('messages.color'), '/').' 1/', $html);
        $this->assertMatchesRegularExpression('/<label[^>]*for="custom_color2"[^>]*>\s*'.preg_quote(__('messages.color'), '/').' 2/', $html);

        // A phone's header already says the tab's name: the heading's copy of it can be hidden there.
        $this->assertGreaterThanOrEqual(8, substr_count($html, '<span class="section-heading-name inline-flex items-center gap-2">'));
    }

    public function test_a_curator_finds_sources_second_and_a_venue_is_told_its_address_is_public(): void
    {
        $owner = $this->createOwner();
        $curatorHtml = $this->formHtml($owner, $this->createCurator($owner));
        preg_match_all('/class="section-nav-link" data-section="(section-[a-z-]+)"/', $curatorHtml, $links);
        $this->assertSame(['section-details', 'section-sources'], array_slice($links[1], 0, 2));
        preg_match_all('/class="mobile-section-header" data-section="(section-[a-z-]+)"/', $curatorHtml, $headers);
        $this->assertSame(['section-details', 'section-sources'], array_slice($headers[1], 0, 2), 'and in the same order on a phone');

        $venueHtml = $this->formHtml($owner, $this->createRole($owner, 'venue', ['address1' => '131 W 3rd St']));
        $this->assertStringContainsString(__('messages.address_is_public'), $venueHtml);
    }

    public function test_gift_card_settings_are_shown_while_gift_cards_are_on(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['gift_cards_enabled' => true]);

        $html = $this->formHtml($owner, $role);

        $this->assertMatchesRegularExpression('/<div id="gift-card-details"\s*>/', $html);
        $this->assertStringContainsString('name="gift_card_amounts_submitted" value="1"', $html);
    }

    /** The merge tab had a header on a phone and no link in the sidebar. */
    public function test_the_merge_tab_can_be_reached_on_a_desktop(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Blue Note', 'address1' => '131 W 3rd St']);
        $this->createRole($owner, 'venue', ['name' => 'The Blue Note', 'address1' => '131 West 3rd Street']);
        Role::where('id', $venue->id)->update(['email_verified_at' => null, 'user_id' => null]);

        $html = $this->formHtml($owner, $venue->fresh());

        if (! str_contains($html, 'id="section-merge"')) {
            $this->markTestSkipped('This fixture does not produce a merge candidate.');
        }
        $this->assertStringContainsString('class="section-nav-link" data-section="section-merge"', $html);
    }

    public function test_the_address_editor_is_not_offered_where_it_is_not_rendered(): void
    {
        [$owner, $role] = $this->talentForm();
        // By query: saving the model would un-verify the address it was just given.
        User::where('id', $owner->id)->update(['email' => \App\Services\DemoService::DEMO_EMAIL]);
        $this->assertTrue(User::find($owner->id)->hasVerifiedEmail());

        $html = $this->formHtml($owner->fresh(), $role);

        $this->assertStringNotContainsString('id="subdomain-edit"', $html, 'sanity check: demo mode does not render the editor');
        $this->assertStringNotContainsString('data-action="toggle-subdomain-edit"', $html, 'so it does not render the button that opens it either');
    }

    /**
     * No "New" badges: not on a tab, not beside a field. Two were here (the Gallery tab until a
     * date, and "List animation" until it was first set), and they are not to come back.
     */
    public function test_nothing_on_the_form_is_badged_new(): void
    {
        [, , $html] = $this->talentForm();

        // Counted, not handed to a pattern assertion: one that fails with the whole page as its
        // subject takes minutes to print.
        $this->assertSame(0, preg_match('/<span class="[^"]*rounded-full[^"]*">\s*'.preg_quote(__('messages.new'), '/').'\s*<\/span>/', $html), 'a "New" pill is on the form');
        $view = file_get_contents(resource_path('views/role/edit.blade.php'));
        $this->assertStringNotContainsString("__('messages.new')", $view);
        $this->assertFalse(defined(\App\Utils\GalleryUtils::class.'::NEW_UNTIL'), 'and nothing times one');
    }

    /**
     * Videos and Links: each list's "+ Add" sits under its own list. Under a tall, centred empty
     * state it stood nearer the next list's name than anything of its own, and read as that list's.
     */
    public function test_each_list_of_links_keeps_its_add_link_beside_it(): void
    {
        [, , $html] = $this->talentForm();

        foreach (['youtube_videos' => ['no_youtube_videos', 'youtube_links', 'add_video'], 'social_links' => ['no_social_links', 'social_links', 'add_link']] as $tab => [$empty, $type, $add]) {
            $this->assertSame(1, preg_match(
                '/<div id="links-tab-'.$tab.'" class="links-tab-content">.*?<div class="link-empty-state"\s*>\s*<p class="event-empty">'.preg_quote(__('messages.'.$empty), '/').'<\/p>\s*<\/div>\s*'
                .'<button type="button"\s+class="btn-show-add-link [^"]*"\s+data-link-type="'.$type.'">\s*\+ '.preg_quote(__('messages.'.$add), '/').'\s*<\/button>\s*<\/div>/s',
                $html
            ), "{$tab}: one quiet line when empty, then its own Add link, and nothing else before the list ends");
        }
        $this->assertStringNotContainsString('link-empty-state text-center py-8', substr($html, 0, strpos($html, 'id="youtube_links_data"')), 'no tall empty state is left in a live list');
        // The next list stands apart: a rule and a wider gap, from the page's own styles.
        $this->assertSame(1, preg_match('/\.links-tab-content \+ \.event-group-label \{[^}]*border-top: 1px solid/', $html), 'the next list begins after a rule');
        $this->assertSame(1, preg_match('/<\/div>\s*<p class="event-group-label">'.preg_quote(__('messages.social_links'), '/').'<\/p>/', $html), 'and its name follows the first list directly, which is what the rule is hung on');
    }
}
