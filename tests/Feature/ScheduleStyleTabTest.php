<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Style tab of the schedule form (role/edit, #section-style).
 *
 * Its pickers and its preview are Vue islands (resources/js/style-studio.js) laid over fields that
 * stay in the page and carry the values. What a browser is not needed to see is held here: that
 * the fields are still the fields, that the islands have nothing server-rendered inside them, the
 * order of the rows, and the handful of faults the tab had before it was rebuilt. What the islands
 * DO is in tests/Browser/ScheduleFormJourneyTest.php.
 */
class ScheduleStyleTabTest extends TestCase
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

    /** The tab alone: from its own opening tag to the next tab's. */
    private function tab(string $html): string
    {
        $start = strpos($html, '<div id="section-style"');
        $this->assertNotFalse($start, 'the Style tab is on the page');
        $end = strpos($html, 'id="section-gallery"', $start) ?: strpos($html, 'id="section-links"', $start);
        $this->assertNotFalse($end);

        return substr($html, $start, $end - $start);
    }

    /** @return array{0: User, 1: Role, 2: string} */
    private function talentTab(array $attrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', $attrs);

        return [$owner, $role, $this->tab($this->formHtml($owner, $role))];
    }

    private function viewSource(): string
    {
        // With its Blade comments taken out, so a sentence about what used to be there does not
        // count as it being there.
        return preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents(resource_path('views/role/edit.blade.php')));
    }

    public function test_the_rows_follow_the_page_from_top_to_bottom(): void
    {
        [, , $tab] = $this->talentTab();

        $at = [];
        foreach (['advanced', 'background', 'animation', 'css'] as $row) {
            $at[] = strpos($tab, 'id="style-tab-'.$row.'"');
        }
        $this->assertNotContains(false, $at, 'Header, Background, Events and Custom CSS are each a row');
        $sorted = $at;
        sort($sorted);
        $this->assertSame($sorted, $at, 'in the order of the page: its header, the ground behind it, its events, then CSS');

        foreach ([__('messages.style_row_header'), __('messages.background'), __('messages.events'), __('messages.custom_css')] as $title) {
            $this->assertStringContainsString('<span class="event-row-title">'.$title.'</span>', $tab);
        }
    }

    public function test_the_default_layout_sits_with_the_event_animation(): void
    {
        [, , $tab] = $this->talentTab();

        $pane = strpos($tab, '<div id="style-content-animation"');
        $layout = strpos($tab, 'id="event_layout_list"');
        $animation = strpos($tab, 'class="vue-list-animation-picker');
        $next = strpos($tab, 'id="style-tab-css"');

        $this->assertTrue($pane < $layout && $layout < $animation && $animation < $next, 'Default Layout is in the Events row, above the animation it goes with');
    }

    public function test_the_custom_css_row_carries_the_lock_where_the_plan_lacks_it(): void
    {
        $owner = $this->createOwner();
        $free = $this->tab($this->formHtml($owner, $this->createRole($owner, 'talent', ['plan_type' => 'free'])));
        $paid = $this->tab($this->formHtml($owner, $this->createRole($owner, 'talent')));

        $row = fn (string $tab) => substr($tab, strpos($tab, 'id="style-tab-css"'), 1400);

        $this->assertStringContainsString('event-row-lock', $row($free), 'a plan without custom CSS sees the lock on the row, before opening it');
        $this->assertStringNotContainsString('id="custom_css"', $free);
        $this->assertStringNotContainsString('event-row-lock', $row($paid));
        $this->assertStringContainsString('<textarea id="custom_css" name="custom_css"', $paid);
    }

    /** A picker is a view of a field: the field is still in the form, under the name it is posted by. */
    public function test_the_four_fields_stay_and_the_dropdown_machinery_is_gone(): void
    {
        [, , $tab] = $this->talentTab();

        foreach (['font_family', 'background_colors', 'header_image', 'background_image'] as $field) {
            $this->assertMatchesRegularExpression('/<select id="'.$field.'" name="'.$field.'"[^>]*class="st-native[^"]*"/s', $tab, "{$field} is still the form's own field");
            preg_match('/<select id="'.$field.'"[^>]*>/s', $tab, $tag);
            $this->assertStringNotContainsString('data-searchable', $tag[0], "{$field} is not made into a searchable dropdown as well");
        }
        // The eight arrow buttons that stepped through a dropdown one at a time.
        $this->assertStringNotContainsString('data-nav-action', $tab);
        $this->assertStringNotContainsString('color-nav-button', $tab);
        // The tab's own class is what puts the fields out of sight while the islands run.
        $this->assertMatchesRegularExpression('/<div id="section-style" class="section-content[^"]* st-js"/', $tab);
    }

    /**
     * An island is mounted on an element: whatever the server had put inside would be compiled as
     * a Vue template, and a schedule's name or an event's is somebody's text.
     */
    public function test_every_island_mounts_on_an_empty_element(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'The {{ 7 * 7 }} Club']);
        $this->createEvent($role, ['name' => 'Opening {{ constructor }} Night']);
        $tab = $this->tab($this->formHtml($owner, $role));

        $placeholders = preg_match_all('/class="vue-style-(?:preview|font|gradient|wall|color)"/', $tab);
        $empty = preg_match_all('/<div class="vue-style-(?:preview|font|gradient|wall|color)" data-props="[^"]*"><\/div>/', $tab);

        $this->assertGreaterThanOrEqual(8, $placeholders, 'the preview, the font, the gradient, two walls and the colour fields');
        $this->assertSame($placeholders, $empty, 'each is an empty element with its props in an attribute');
        // The preview is handed the schedule's own events as data, with the day each is on.
        $this->assertMatchesRegularExpression('/class="vue-style-preview" data-props="[^"]*Opening \{\{ constructor \}\} Night[^"]*&quot;date&quot;:&quot;\d{4}-\d{2}-\d{2}&quot;/', $tab);
    }

    /** These two came back as the stored pictures, where every other choice came back as chosen. */
    public function test_a_refused_save_keeps_the_pictures_that_were_chosen(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['background' => 'image', 'background_image' => 'Autumn', 'header_image' => 'Arena']);
        $taken = $this->createRole($owner, 'talent');
        $editUrl = route('role.edit', ['subdomain' => $role->subdomain]);

        $this->actingAs($owner)->from($editUrl)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $taken->subdomain,
            'background' => 'image',
            'background_image' => 'Calm',
            'header_image' => 'Karate',
        ])->assertRedirect($editUrl);

        $tab = $this->tab($this->actingAs($owner)->get($editUrl)->assertOk()->getContent());

        $this->assertMatchesRegularExpression('/<option value="Karate"\s+SELECTED>/', $tab, 'the header picture chosen before the refusal');
        $this->assertDoesNotMatchRegularExpression('/<option value="Arena"\s+SELECTED>/', $tab);
        $this->assertMatchesRegularExpression('/<option value="Calm"\s+SELECTED>/', $tab, 'the background picture chosen before the refusal');
        $this->assertDoesNotMatchRegularExpression('/<option value="Autumn"\s+SELECTED>/', $tab);
    }

    public function test_an_ordinary_load_shows_the_stored_pictures(): void
    {
        [, , $tab] = $this->talentTab(['background' => 'image', 'background_image' => 'Autumn', 'header_image' => 'Arena']);

        $this->assertMatchesRegularExpression('/<option value="Arena"\s+SELECTED>/', $tab);
        $this->assertMatchesRegularExpression('/<option value="Autumn"\s+SELECTED>/', $tab);
        $this->assertDoesNotMatchRegularExpression('/<option value="none"\s+SELECTED>/', $tab);
    }

    /**
     * The preview was built by joining strings, the schedule's name and the picture addresses put
     * in as they were, and written with jQuery's html().
     */
    public function test_the_preview_is_not_built_from_strings(): void
    {
        $source = $this->viewSource();

        $this->assertStringNotContainsString("\$('#preview')", $source);
        $this->assertStringNotContainsString('$preview.html(', $source);
        $this->assertStringNotContainsString("'{{ \$role->profile_image_url }}'", $source);
        $this->assertStringNotContainsString("'{{ \$role->header_image_url }}'", $source);
        // What is left under the old name tells the islands to read the fields again.
        $this->assertMatchesRegularExpression("/function updatePreview\(\) \{\s*document\.dispatchEvent\(new CustomEvent\('style:sync'\)\);\s*\}/", $source);
    }

    public function test_every_label_in_the_tab_names_a_field_that_is_there(): void
    {
        [, , $tab] = $this->talentTab(['background' => 'image']);

        preg_match_all('/<label[^>]*\sfor="([^"]+)"/', $tab, $labels);
        $this->assertGreaterThan(8, count($labels[1]));
        foreach (array_unique($labels[1]) as $for) {
            $this->assertStringContainsString('id="'.$for.'"', $tab, "a label names #{$for}, which is not on the tab");
        }
    }

    /** It used to take deleting the stored logo before another could be chosen. */
    public function test_change_is_offered_while_a_logo_is_stored(): void
    {
        [, , $tab] = $this->talentTab(['profile_image_url' => 'demo_profile_donuts.jpg']);

        $this->assertMatchesRegularExpression('/<div id="profile_image_choose">\s*<button type="button" class="event-link" id="profile_image_change" data-trigger-file-input="profile_image"[^>]*>'.preg_quote(__('messages.change'), '/').'<\/button>/', $tab);
        $this->assertStringContainsString('id="profile_image_stored"', $tab, 'the stored picture is in the tile');
        // The stored picture's Remove is the delete button's own parent, which is what is taken
        // off the page once it is deleted.
        $this->assertMatchesRegularExpression('/<div id="profile_image_existing">\s*<button type="button" class="event-link is-danger"\s+data-delete-image-url="[^"]+"\s+data-delete-image-token="[^"]+"\s+data-delete-image-stored="profile_image_stored">/', $tab);
    }

    public function test_a_schedule_with_no_logo_is_offered_the_chooser(): void
    {
        [, , $tab] = $this->talentTab();

        $this->assertMatchesRegularExpression('/id="profile_image_change"[^>]*>'.preg_quote(__('messages.choose_file'), '/').'</', $tab);
        $this->assertStringNotContainsString('id="profile_image_existing"', $tab);
        $this->assertStringNotContainsString('id="profile_image_stored"', $tab);
    }

    /** A refused background upload had no line on the page to say so. */
    public function test_a_refused_upload_says_so_on_the_tab(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['background' => 'image']);
        $errors = (new ViewErrorBag)->put('default', new MessageBag([
            'background_image_url' => ['The background picture is too large.'],
            'header_image_url' => ['The header picture is too large.'],
        ]));

        $tab = $this->tab($this->actingAs($owner)->withSession(['errors' => $errors])
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent());

        $this->assertStringContainsString('The background picture is too large.', $tab);
        $this->assertStringContainsString('The header picture is too large.', $tab);
    }

    /** The setup guide docks beside the first .max-w-xl column it finds on the page. */
    public function test_nothing_on_the_tab_is_a_column_the_setup_guide_would_dock_beside(): void
    {
        [, , $tab] = $this->talentTab();

        $this->assertStringNotContainsString('max-w-xl', $tab);
    }

    /** An entry the page asks for and the build does not make is a 500 on the whole form. */
    public function test_every_script_the_page_asks_for_is_built(): void
    {
        preg_match('/@vite\(\[(.*?)\]\)/s', file_get_contents(resource_path('views/role/edit.blade.php')), $asked);
        preg_match_all("/'([^']+)'/", $asked[1], $entries);
        $config = file_get_contents(base_path('vite.config.js'));

        $this->assertContains('resources/js/style-studio.js', $entries[1]);
        foreach ($entries[1] as $entry) {
            $this->assertMatchesRegularExpression('/^\s*\''.preg_quote($entry, '/').'\',/m', $config, "{$entry} is not an input of the build");
            $this->assertFileExists(base_path($entry));
        }
    }

    public function test_the_one_new_word_is_in_every_language(): void
    {
        $english = __('messages.style_row_header', [], 'en');
        $this->assertSame('Header', $english);

        foreach (array_keys(config('app.supported_languages')) as $language) {
            $messages = include resource_path("lang/{$language}/messages.php");
            $this->assertArrayHasKey('style_row_header', $messages, "{$language} has no word for the Header row");
            $this->assertNotSame('', trim($messages['style_row_header']));
            if ($language !== 'en') {
                $this->assertNotSame($english, $messages['style_row_header'], "{$language} was given the English word");
            }
            $this->assertArrayNotHasKey('style_row_header_layout', $messages, 'the row it replaces is gone');
        }
    }

    /**
     * Read from the components' source: a picker's own button is never a submit button, nothing
     * of anyone's is written as HTML, no box of a picker is a field of the form, and its typing
     * and its Enter stay with it (the save bar takes any input event inside the form as a change,
     * and Enter in a text box submits the form).
     */
    public function test_the_islands_keep_their_typing_and_their_buttons_to_themselves(): void
    {
        $components = glob(resource_path('js/components/Style*.vue'));
        $this->assertCount(5, $components);

        foreach ($components as $path) {
            $name = basename($path);
            $template = substr(file_get_contents($path), 0, strpos(file_get_contents($path), '<script setup>'));

            // A tag ends at the first > that is outside a quoted attribute (v-if="list.length > 4").
            preg_match_all('/<button\b(?:[^>"]|"[^"]*")*>/s', $template, $buttons);
            $this->assertNotEmpty($buttons[0], "{$name} has buttons to read");
            foreach ($buttons[0] as $button) {
                $this->assertStringContainsString('type="button"', $button, "{$name}: a button without a type submits the form");
            }
            $this->assertStringNotContainsString('v-html', $template, "{$name}: nothing is written as HTML");
            $this->assertDoesNotMatchRegularExpression('/\sname="/', $template, "{$name}: a picker's box is not a field of the form");

            preg_match_all('/<input\b(?:[^>"]|"[^"]*")*>/s', $template, $inputs);
            foreach ($inputs[0] as $input) {
                $this->assertStringContainsString('@input.stop', $input, "{$name}: typing in a picker's own box is not a change to the form");
                $this->assertStringContainsString('@change.stop', $input, "{$name}");
                $this->assertStringContainsString('@keydown.enter.prevent', $input, "{$name}: Enter in a picker's box must not save the form");
            }
        }
    }

    /** A field is set the way a person would set it, and never while the islands are starting. */
    public function test_a_picker_sets_a_field_with_both_events(): void
    {
        $state = file_get_contents(resource_path('js/style/state.js'));
        $set = substr($state, strpos($state, 'export function setField('), 700);

        $this->assertStringContainsString("new Event('input', { bubbles: true })", $set);
        $this->assertStringContainsString("new Event('change', { bubbles: true })", $set);

        foreach (glob(resource_path('js/components/Style*.vue')) as $path) {
            $script = substr(file_get_contents($path), strpos(file_get_contents($path), '<script setup>'));
            if (preg_match('/onMounted\(\(\) => \{(.*?)\n\}\);/s', $script, $mounted)) {
                $this->assertStringNotContainsString('setField(', $mounted[1], basename($path).' sets a field as it starts, which the save bar reads as an unsaved change');
                $this->assertStringNotContainsString('pick(', $mounted[1], basename($path));
            }
        }
    }

    /** A drag fires no input event: the bar said "unsaved changes" without naming Style. */
    public function test_dragging_the_logo_wall_marks_the_style_tab(): void
    {
        $source = $this->viewSource();
        $start = strpos($source, 'Sortable.create(logoWallList');
        $this->assertNotFalse($start);
        $handler = substr($source, $start, 1200);

        $this->assertStringContainsString("window.FormKit.markDirty('section-style');", $handler);
        $this->assertStringContainsString('updatePreview();', $handler);
    }

    /**
     * A picture the AI generator made rides in a hidden field of its own. Remove used to leave
     * that field behind, so the picture that had just been removed was saved all the same.
     */
    public function test_removing_a_picture_forgets_the_one_the_ai_made(): void
    {
        $source = $this->viewSource();

        foreach (['function clearRoleFileInput(', 'function clearHeaderFileInput('] as $function) {
            $body = substr($source, strpos($source, $function), 1100);
            $this->assertStringContainsString('forgetAiPicture(', $body, "{$function} leaves the AI's picture in the form");
        }
        $this->assertStringContainsString("profile_image: 'ai_profile_image', header_image_url: 'ai_header_image', background_image_url: 'ai_background_image'", $source);
    }

    /** A deleted picture stayed in the preview, and a stored logo stayed in its tile. */
    public function test_deleting_a_stored_picture_tells_the_tab(): void
    {
        $source = $this->viewSource();
        $delete = substr($source, strpos($source, 'function deleteRoleImage('), 1800);

        $this->assertStringContainsString('storedId ? document.getElementById(storedId) : null', $delete);
        $this->assertStringContainsString('updatePreview()', $delete);
        $this->assertStringContainsString("new CustomEvent('style:image-removed')", $delete);
        // The preview is read again BEFORE the walls are told: they ask it whether a picture is left.
        $this->assertLessThan(strpos($delete, "new CustomEvent('style:image-removed')"), strpos($delete, 'updatePreview()'));
    }

    /**
     * The animation's own stage drew an event's name in the schedule's font and its date on the
     * accent. The public page does neither, and the tab's preview, beside it, drew the same events
     * differently.
     */
    public function test_the_animation_stage_draws_its_cards_as_the_page_does(): void
    {
        $picker = file_get_contents(resource_path('js/components/ListAnimationPicker.vue'));
        $template = substr($picker, 0, strpos($picker, '<script setup>'));

        $this->assertStringNotContainsString('fontFamily', $template);
        $this->assertDoesNotMatchRegularExpression('/data-reveal-date[^>]*:style=/s', $template);
    }

    /** With no islands (a build that failed, a blocked script) the fields come back into view. */
    public function test_the_fields_come_back_if_the_islands_never_start(): void
    {
        $source = $this->viewSource();

        $this->assertMatchesRegularExpression("/if \(styleTab && ! window\.StyleStudio\) \{\s*styleTab\.classList\.remove\('st-js'\);/", $source);
        $styles = file_get_contents(resource_path('views/role/partials/style-studio-styles.blade.php'));
        $this->assertStringContainsString('#section-style.st-js .st-native {', $styles, 'the fields are out of sight only while the tab says the islands run');
    }
}
