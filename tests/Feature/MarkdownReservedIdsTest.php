<?php

namespace Tests\Feature;

use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use Tests\TestCase;

/**
 * What a description may not do to the page it is printed on.
 *
 * A description is sanitized HTML, and two things used to reach past the sanitizer:
 *
 *   - UrlUtils::convertUrlsToLinks() rewrote the HTML afterwards. An address inside an attribute
 *     (an image's alt text) was wrapped in a link there, the link's quotes ended the attribute, and
 *     the rest of the tag was read as markup nobody had sanitized. It links text only now;
 *   - a description could carry the id of an element a page mounts. Coming first in the page, it
 *     was the element the app started on. Every such id is in MarkdownUtils::RESERVED_IDS, and the
 *     apps on the pages that print a description start only on an element carrying data-vue-root,
 *     which the sanitizer strips, so a description stored before the list grew cannot stand in.
 *
 * The second test reads the views: a mount added without its id on the list fails the build.
 */
class MarkdownReservedIdsTest extends TestCase
{
    public function test_linking_addresses_leaves_the_sanitized_markup_whole(): void
    {
        $sanitized = MarkdownUtils::convertToHtml('![see https://example.com/page for more](https://example.com/a.png)');
        $linked = UrlUtils::convertUrlsToLinks($sanitized);

        $this->assertSame($sanitized, $linked, 'an address inside an attribute is not text to link');
    }

    public function test_an_address_in_text_is_still_linked_and_a_link_is_left_alone(): void
    {
        $text = UrlUtils::convertUrlsToLinks(MarkdownUtils::convertToHtml('Tickets: https://example.com/t'));
        $this->assertStringContainsString('<a href="https://example.com/t"', $text);

        $already = MarkdownUtils::convertToHtml('[tickets](https://example.com/t)');
        $this->assertSame($already, UrlUtils::convertUrlsToLinks($already));
    }

    public function test_a_description_cannot_carry_the_id_of_an_element_a_page_mounts(): void
    {
        foreach (['follow-consent-modal-app', 'calendar-app', 'rsvp-form', 'es-cart-app', 'carpool-app', 'event-submit-app', 'booking-app'] as $id) {
            $this->assertStringNotContainsString('id="'.$id.'"', MarkdownUtils::convertToHtml('<div id="'.$id.'">x</div>'), $id);
        }
    }

    public function test_a_bracket_or_a_quote_in_an_attribute_does_not_open_the_tag(): void
    {
        foreach (['![a > b https://example.com/x](https://example.com/a.png)', '![say "hi" https://example.com/x y](https://example.com/a.png)', '[t](https://example.com/a "go > https://example.com/b now")'] as $markdown) {
            $sanitized = MarkdownUtils::convertToHtml($markdown);
            $this->assertSame($sanitized, UrlUtils::convertUrlsToLinks($sanitized), $markdown);
        }
    }

    public function test_every_element_a_page_mounts_by_id_is_an_id_a_description_cannot_carry(): void
    {
        $mounted = [];
        foreach (\Illuminate\Support\Facades\File::allFiles(resource_path('views')) as $file) {
            if (preg_match_all('/\.mount\(\s*[\'"]#([A-Za-z0-9_-]+)/', $file->getContents(), $m)) {
                $mounted = array_merge($mounted, $m[1]);
            }
        }
        $mounted = array_values(array_unique($mounted));

        $this->assertGreaterThan(25, count($mounted), 'sanity check: the views were read');
        $this->assertSame([], array_values(array_diff($mounted, MarkdownUtils::RESERVED_IDS)));
    }

    public function test_a_page_that_prints_a_description_starts_its_app_only_on_its_own_element(): void
    {
        $this->assertStringNotContainsString('data-vue-root', MarkdownUtils::convertToHtml('<div id="anything" data-vue-root>x</div>'), 'a description cannot carry the mark');

        foreach ([
            'partials/follow-consent-modal' => 'follow-consent-modal-app', 'role/partials/calendar' => 'calendar-app',
            'event/tickets' => 'ticket-selector', 'event/rsvp' => 'rsvp-form', 'partials/guest-cart' => 'es-cart-app',
            'carpool/index' => 'carpool-app', 'appointments/book-type' => 'booking-app',
        ] as $view => $id) {
            $source = file_get_contents(resource_path('views/'.$view.'.blade.php'));
            $this->assertMatchesRegularExpression('/id="'.$id.'"[^>]*\sdata-vue-root[\s>]/', $source, $view.': the element');
            $this->assertStringContainsString("mount('#".$id."[data-vue-root]')", $source, $view.': the mount');
        }
    }
}
