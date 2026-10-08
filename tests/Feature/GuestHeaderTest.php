<?php

namespace Tests\Feature;

use App\Utils\GuestHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The header of a schedule's public page (role/partials/headers/banner).
 *
 * It was two hand-copied bodies, one for a phone and one for a laptop, in which Follow was one of
 * six look-alike buttons and nothing said how much was on. It is drawn once now: one main button
 * beside the logo, a line of facts, the list's own tools in grey, and a slim bar that follows the
 * visitor down the page. These hold the rules a redesign would otherwise lose without anyone
 * seeing it happen: which button leads, what the facts may and may not say, and that the ids the
 * list's script and owners' custom CSS depend on are still there.
 */
class GuestHeaderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function page($role, string $query = ''): string
    {
        return $this->get('/'.$role->subdomain.$query)->assertOk()->getContent();
    }

    /** The header's card alone, so nothing else on the page can satisfy an assertion about it. */
    private function header(string $html): string
    {
        $this->assertSame(1, preg_match('~<div id="gp-header".*?</header>~s', $html, $m), 'the page has one header card');

        return $m[0];
    }

    private function part(string $header, string $class): string
    {
        return preg_match('~<(div|ul) class="'.preg_quote($class, '~').'">(.*?)</\1>~s', $header, $m) ? $m[2] : '';
    }

    public function test_the_facts_say_where_and_how_much_is_on(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['address1' => '12 Main St', 'city' => 'Springfield']);
        foreach (range(1, 3) as $i) {
            $this->createEvent($venue, ['name' => 'Show '.$i]);
        }

        $facts = $this->part($this->header($this->page($venue)), 'gk-head-facts');

        $this->assertStringContainsString('<b>3</b> upcoming events', $facts);
        // A venue's place is its address, and it still opens a map.
        $this->assertMatchesRegularExpression('~<a href="https://www\.google\.com/maps/search/[^"]+"[^>]*>12 Main St~', $facts);

        // An act says where it is based, as plain text: there is no address to open.
        $act = $this->createRole($this->createOwner(), 'talent', ['city' => 'Shelbyville']);
        $actFacts = $this->part($this->header($this->page($act)), 'gk-head-facts');
        $this->assertStringContainsString('<span>Shelbyville</span>', $actFacts);
        $this->assertStringNotContainsString('google.com/maps', $actFacts);
        $this->assertStringNotContainsString('upcoming event', $actFacts, 'nothing coming up is not a fact worth a line');
    }

    public function test_the_count_stops_at_the_lists_cap_and_takes_the_owners_word(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $this->assertNull(GuestHeader::upcomingFact($role, 0));
        $this->assertSame('1 upcoming event', GuestHeader::upcomingFact($role, 1));
        $this->assertSame('49 upcoming events', GuestHeader::upcomingFact($role, 49));
        // The page loads fifty; "50" would be a claim about a list that may be longer.
        $this->assertSame('50+ upcoming events', GuestHeader::upcomingFact($role, GuestHeader::UPCOMING_CAP));

        $role->custom_labels = ['events' => ['value' => 'Classes']];
        $this->assertSame('12 Classes', GuestHeader::upcomingFact($role, 12));
    }

    public function test_no_follower_figure_is_on_the_page(): void
    {
        // A place and an event, so the facts line is drawn: a schedule with neither has no line
        // for a follower count to be on, and this would pass whatever the header did.
        $role = $this->createRole($this->createOwner(), 'venue', ['address1' => '12 Main St', 'city' => 'Springfield']);
        $this->createEvent($role);
        foreach (range(1, 30) as $i) {
            $this->createOwner()->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }
        $this->assertSame(30, $role->followers()->count(), 'fixture: thirty people follow this schedule');

        $html = $this->page($role);

        $this->assertStringContainsString('<b>1</b> upcoming event', $this->part($this->header($html), 'gk-head-facts'), 'fixture: the facts line is on the page');
        $this->assertDoesNotMatchRegularExpression('~\b30\s*(\+\s*)?followers?\b~i', $html);
        $this->assertDoesNotMatchRegularExpression('~\d\s*followers?\b~i', $this->header($html));
    }

    public function test_follow_is_the_one_main_button_and_stands_beside_the_logo(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['accept_requests' => true]);

        $header = $this->header($this->page($role));

        $this->assertSame(1, substr_count($header, 'gk-btn-primary'), 'one button in the schedule\'s colour');
        $this->assertMatchesRegularExpression('~<div class="gk-head-actions">.*?<button type="button"\s+data-follow-trigger[^>]*class="gk-btn gk-btn-primary\s+gk-head-follow"~s', $header);
        // What else a visitor may do is the second row, under the description.
        $rest = $this->part($header, 'gk-head-more-actions');
        $this->assertStringContainsString('>Submit Event</a>', $rest);
        $this->assertStringNotContainsString('data-follow-trigger', $rest);
    }

    public function test_book_a_time_leads_where_there_are_appointments_and_follow_steps_back(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        $this->createAppointmentType($role);
        $this->assertTrue($role->fresh()->hasBookableAppointments(), 'fixture: this schedule takes appointments');

        $header = $this->header($this->page($role));

        $this->assertSame(1, substr_count($header, 'gk-btn-primary'));
        $this->assertMatchesRegularExpression('~<div class="gk-head-actions">.*?<a href="[^"]+" class="gk-btn gk-btn-primary ">[^<]*Book~s', $header);
        $this->assertMatchesRegularExpression('~data-follow-trigger[^>]*class="gk-btn gk-btn-secondary\s+gk-head-follow"~s', $this->part($header, 'gk-head-more-actions'));
    }

    public function test_a_schedule_with_nothing_else_to_offer_has_no_second_row(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['accept_requests' => false]);

        $this->assertStringNotContainsString('gk-head-more-actions', $this->header($this->page($role)));
    }

    public function test_a_follower_is_told_so_and_a_member_gets_manage(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $follower = $this->createOwner();
        $follower->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);

        $asFollower = $this->header($this->actingAs($follower)->get('/'.$role->subdomain)->assertOk()->getContent());
        // Among the facts, where it takes no room from the buttons on a phone.
        $this->assertStringContainsString('<li class="gk-head-fact gk-head-following">', $this->part($asFollower, 'gk-head-facts'));
        $this->assertStringNotContainsString('data-follow-trigger', $asFollower);
        $this->assertStringNotContainsString('gk-head-manage', $asFollower);

        $asOwner = $this->header($this->actingAs($owner)->get('/'.$role->subdomain)->assertOk()->getContent());
        // Manage is drawn twice and the stylesheet shows one: beside the main button on a laptop,
        // in the second row on a phone, where three buttons do not fit beside a logo.
        $this->assertSame(1, preg_match_all('~<a href="[^"]+" class="gk-btn gk-btn-quiet gk-head-manage gk-head-manage-desk">~', $this->part($asOwner, 'gk-head-actions')));
        $this->assertSame(1, preg_match_all('~<a href="[^"]+" class="gk-btn gk-btn-secondary gk-head-manage-phone">~', $asOwner));
        $this->assertStringNotContainsString('gk-head-following', $asOwner);
        $this->assertStringNotContainsString('data-follow-trigger', $asOwner);
    }

    public function test_the_lists_tools_keep_their_ids_and_say_which_view_is_on(): void
    {
        $list = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'list']);
        $calendar = $this->createRole($this->createOwner(), 'venue', ['event_layout' => 'calendar']);

        foreach ([[$list, '', 'list'], [$calendar, '', 'calendar'], [$list, '?layout=calendar', 'calendar']] as [$role, $query, $on]) {
            $header = $this->header($this->page($role, $query));
            $off = $on === 'list' ? 'calendar' : 'list';

            $this->assertMatchesRegularExpression('~id="toggle-'.$on.'-btn" class="gk-head-seg-btn" aria-pressed="true"~', $header, $on.' is pressed');
            $this->assertMatchesRegularExpression('~id="toggle-'.$off.'-btn" class="gk-head-seg-btn" aria-pressed="false"~', $header);
            // role/partials/calendar finds each of these by id, and wires a click to each.
            foreach (['hero-filters-btn', 'hero-filters-btn-mobile', 'hero-filters-badge', 'hero-filters-badge-mobile'] as $id) {
                $this->assertSame(1, substr_count($header, 'id="'.$id.'"'), $id);
            }
            // Grey: the filter and the switch carry no colour of the schedule's.
            $this->assertStringNotContainsString('style="border-color', $header);
        }
    }

    public function test_the_compact_header_has_the_same_buttons_and_tools(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['header_style' => 'compact', 'accept_requests' => true]);

        $html = $this->page($role);

        $this->assertSame(1, preg_match('~<div id="gp-header" class="relative z-20.*?</header>~s', $html, $m));
        $this->assertSame(1, substr_count($m[0], 'gk-btn-primary'));
        $this->assertStringContainsString('>Submit Event</a>', $m[0]);
        $this->assertSame(1, substr_count($m[0], 'id="hero-filters-btn"'));
        $this->assertSame(1, substr_count($m[0], 'id="hero-filters-btn-mobile"'));
        $this->assertSame(1, substr_count($m[0], 'id="toggle-list-btn"'));
        // The slim bar belongs to the banner: compact IS a bar across the top.
        $this->assertStringNotContainsString('id="gp-header-bar"', $html);
    }

    public function test_the_slim_bar_follows_the_banner_and_stays_out_of_the_picture_render(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $html = $this->page($role);
        $this->assertSame(1, preg_match('~<div id="gp-header-bar" class="gk-headbar[^"]*" inert>.*?</script>~s', $html, $bar), 'inert until it is shown');
        // A second Follow, wired by markup alone (the follow script listens on the document).
        $this->assertMatchesRegularExpression('~<button type="button"\s+data-follow-trigger[^>]*class="gk-btn gk-btn-primary gk-btn-sm gk-head-follow"~s', $bar[0]);
        // After the header: its script looks the header up when it runs.
        $this->assertGreaterThan(strpos($html, 'id="schedule-header"'), strpos($html, 'id="gp-header-bar"'));
        // Never inside the header's card, whose backdrop-filter would un-fix it.
        $this->assertStringNotContainsString('gp-header-bar', $this->header($html));

        $this->assertStringNotContainsString('id="gp-header-bar"', $this->page($role, '?graphic=1'));
    }

    /**
     * The wash of the accent across the top of the card is a Header Image choice ("Accent color
     * gradient"), stored as the header_image value 'gradient'. That column otherwise names a
     * built-in picture, so every place that turns it into a file's address has to know the
     * value is not one (Role::HEADER_IMAGE_KEYWORDS).
     */
    public function test_the_wash_of_the_accent_is_the_owners_choice_and_is_never_read_as_a_picture(): void
    {
        $owner = $this->createOwner();
        $plain = $this->createRole($owner, 'venue');
        $this->assertSame('none', $plain->header_image, 'fixture: a new schedule has no header image');
        $this->assertStringNotContainsString('gk-head-washed', $this->header($this->page($plain)), '"None" is a plain card');

        // With an upload still on file: choosing the gradient puts the picture away, as choosing
        // None does.
        $washed = $this->createRole($owner, 'venue', ['header_image' => 'gradient', 'header_image_url' => 'header_abc.png', 'accept_requests' => true]);
        $html = $this->page($washed);
        $header = $this->header($html);

        $this->assertMatchesRegularExpression('~<div id="gp-header"\s+class="gk-head [^"]*gk-head-washed ~', $header);
        $this->assertStringNotContainsString('gk-head-pictured', $header);
        $this->assertStringNotContainsString('class="gk-head-picture"', $header);
        $this->assertNull($washed->headerImageUrl());
        // The stylesheet draws it only where the card says so.
        $this->assertMatchesRegularExpression('~:where\(\.gk-head-washed\) \.gk-head-stage \{ background: linear-gradient~', $html);
        $this->assertDoesNotMatchRegularExpression('~\n\s*\.gk-head-stage \{[^}]*background~', $html, 'a card that did not choose it has no wash');

        // No page asks for a picture called "gradient": not the schedule's own, and not the two
        // request forms, which draw the schedule's header picture above the form. Each form is
        // opened with a built-in header first, to know the picture's address is there to be seen.
        $this->assertStringNotContainsString('images/headers/gradient', $html);
        // What each form needs to be drawn at all: the booking form its own switch and no
        // account wall, the submit form the account wall.
        $form = ['accept_requests' => true, 'require_approval' => true];
        foreach ([
            ['venue', 'event.booking_request', ['require_account' => false, 'event_request_form' => 'booking']],
            ['curator', 'event.guest_submit', ['require_account' => true]],
        ] as [$type, $route, $extra]) {
            $pictured = $this->createRole($owner, $type, $form + $extra + ['header_image' => 'Arena']);
            $this->assertStringContainsString('images/headers/Arena', $this->get(route($route, ['subdomain' => $pictured->subdomain]))->assertOk()->getContent(), $route.' draws a built-in header');

            $gradient = $this->createRole($owner, $type, $form + $extra + ['header_image' => 'gradient']);
            $this->assertStringNotContainsString('images/headers/gradient', $this->get(route($route, ['subdomain' => $gradient->subdomain]))->assertOk()->getContent(), $route);
        }

        // And the schedule form offers it, chosen, with no thumbnail of a picture beside it.
        $edit = $this->actingAs($owner)->get('/'.$washed->subdomain.'/edit')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<option value="gradient" SELECTED>\s*'.preg_quote(__('messages.header_image_gradient'), '~').'</option>~', $edit);
        $this->assertStringNotContainsString('images/headers/gradient', $edit);
        $this->assertStringContainsString('["none","gradient","logos"].indexOf(value) === -1', $edit, 'the form\'s script knows the value names no picture');
    }

    public function test_the_name_is_text_whatever_it_holds_and_steps_down_when_long(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => '<b>Bold</b> & {{ 7*7 }}']);

        $header = $this->header($this->page($role));

        $this->assertMatchesRegularExpression('~<h1 class="gk-head-name "[^>]*>\s*&lt;b&gt;Bold&lt;/b&gt; &amp; \{\{ 7\*7 \}\}\s*</h1>~', $header);
        $this->assertStringNotContainsString('<b>Bold</b>', $header);
        $this->assertMatchesRegularExpression('~<header id="schedule-header" class="gk-head-body[^"]*" v-pre>~', $header, 'and nothing in it is compiled as a template');

        $this->assertSame('', GuestHeader::nameStep('Moe\'s Tavern'));
        $this->assertSame('gk-head-name-m', GuestHeader::nameStep('The Simpsons - Springfield Events'));
        $this->assertSame('gk-head-name-s', GuestHeader::nameStep('Capital City Arena and Convention Centre East'));
    }
}
