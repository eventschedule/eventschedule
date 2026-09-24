<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The guest calendar's event cards carry real links.
 *
 * The Vue calendar is rendered in the browser, so a crawler that does not run it sees only the
 * <noscript> list (GuestNoscriptListTest); one that does - Googlebot renders - found a single
 * <a href> per event, in the desktop month grid. The list view, the mobile agenda and the mobile
 * cards were `div @click="navigateToEvent"`, which no crawler follows and no visitor can
 * middle-click or open in a new tab.
 *
 * A source scan, in the style of MarketingPriceTest, because these templates only ever run in the
 * browser: the markup a feature test gets back is the uncompiled template.
 */
class CalendarCrawlableLinksTest extends TestCase
{
    /** The anchor every card title is: the event URL, the card target, the shared click handler. */
    private const LINK_ATTRIBUTES = ':href="getEventUrl(event)" :target="eventLinkTarget()" @click="onEventLinkClick(event, $event)"';

    private function source(string $view): string
    {
        return File::get(resource_path('views/'.$view));
    }

    public function test_the_mobile_card_title_is_a_real_link(): void
    {
        $card = $this->source('role/partials/mobile-event-card.blade.php');

        $this->assertStringContainsString('<a '.self::LINK_ATTRIBUTES.' v-text="event.name"></a>', $card);
        $this->assertStringNotContainsString('<span v-text="event.name"></span>', $card, 'The title is no longer a bare span');
    }

    /**
     * The mobile agenda and the mobile list render every card through that partial, so its link is
     * theirs: both includes must still be there.
     */
    public function test_the_mobile_agenda_and_the_mobile_list_render_the_linked_card(): void
    {
        $calendar = $this->source('role/partials/calendar.blade.php');

        $this->assertSame(2, substr_count($calendar, "@include('role/partials/mobile-event-card')"));
    }

    public function test_both_desktop_list_layouts_link_their_titles(): void
    {
        $calendar = $this->source('role/partials/calendar.blade.php');

        // commaBreak() escapes the name before inserting its line breaks, so v-html is safe here.
        $this->assertSame(
            2,
            substr_count($calendar, '<a '.self::LINK_ATTRIBUTES.' v-html="commaBreak(event.name)"></a>'),
            'The flyer layout and the stacked layout each link the title'
        );
        $this->assertStringNotContainsString('<span v-html="commaBreak(event.name)"></span>', $calendar);
        $this->assertMatchesRegularExpression('/commaBreak\(text\) \{\s*if \(!text\) return \'\';\s*const div = document\.createElement\(\'div\'\);\s*div\.textContent = text;/', $calendar);
    }

    /**
     * The images link to the same place, and duplicate the title's link, so they are kept out of
     * the tab order and the accessibility tree rather than announced twice.
     */
    public function test_card_images_link_but_stay_out_of_the_tab_order(): void
    {
        foreach ([
            'role/partials/mobile-event-card.blade.php' => 1,
            'role/partials/calendar.blade.php' => 2,
        ] as $view => $expected) {
            preg_match_all('/<a '.preg_quote(self::LINK_ATTRIBUTES, '/').'([^>]*)>\s*<img\b/', $this->source($view), $m);

            $this->assertCount($expected, $m[0], "{$view}: every card image is wrapped in the event link");

            foreach ($m[1] as $attributes) {
                $this->assertStringContainsString('tabindex="-1"', $attributes, $view);
                $this->assertStringContainsString('aria-hidden="true"', $attributes, $view);
            }
        }
    }

    /**
     * A link must not lose what the card click did: a new tab in an embed and in the admin portal,
     * and the direct-registration jump. And it must not take over what a link is for: a modified
     * or middle click is the browser's.
     */
    public function test_the_link_handler_keeps_the_card_behaviour_and_the_browsers(): void
    {
        $calendar = $this->source('role/partials/calendar.blade.php');

        $this->assertSame(1, preg_match('/eventLinkTarget\(\) \{(.*?)\n        \},/s', $calendar, $target));
        $this->assertStringContainsString("(this.embed || this.route === 'admin') ? '_blank' : null", $target[1]);

        $this->assertSame(1, preg_match('/onEventLinkClick\(event, e\) \{(.*?)\n        \},/s', $calendar, $handler));
        foreach (['e.defaultPrevented', 'e.button !== 0', 'e.metaKey', 'e.ctrlKey', 'e.shiftKey', 'e.altKey'] as $guard) {
            $this->assertStringContainsString($guard, $handler[1], "A {$guard} click is left to the browser");
        }
        $this->assertStringContainsString('this.directRegistration && event.registration_url', $handler[1]);
        $this->assertStringContainsString('e.preventDefault();', $handler[1]);
        $this->assertStringContainsString("window.open(event.registration_url, '_blank', 'noopener');", $handler[1]);
    }

    /**
     * The whole card stays clickable, and it ignores clicks that land in a link - or a click on
     * the title would navigate twice.
     */
    public function test_the_whole_card_click_stays_and_ignores_clicks_inside_a_link(): void
    {
        $calendar = $this->source('role/partials/calendar.blade.php');

        $this->assertSame(3, substr_count($calendar, '@click="navigateToEvent(event, $event)"'), 'The agenda, the desktop list and the mobile list cards');
        $this->assertSame(1, preg_match('/navigateToEvent\(event, e\) \{(.*?)\n        \},/s', $calendar, $navigate));
        $this->assertStringContainsString("e.target?.closest('a')", $navigate[1]);
    }
}
