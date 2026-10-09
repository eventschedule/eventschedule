<?php

namespace Tests\Unit;

use App\Utils\DocsContents;
use App\Utils\DocsUtils;
use Tests\TestCase;

/**
 * DocsContents reads a guide's "On this page" list out of the guide's own view, so that the /docs
 * home can print it beside the guide's name. It reads Blade source with a pattern, which is only
 * safe while every guide writes its list the way the pattern expects: these tests are what says so.
 */
class DocsContentsTest extends TestCase
{
    public function test_groups_links_and_headings_without_a_link_are_read_in_order(): void
    {
        $entries = DocsContents::parse(<<<'BLADE'
            <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
            <x-doc-nav-group label="Payment" href="#payment" expanded>
                <x-doc-nav-link href="#stripe">Stripe</x-doc-nav-link>
                <x-doc-nav-link href="#paypal" search="pay pal">PayPal</x-doc-nav-link>
            </x-doc-nav-group>
            <x-doc-nav-group label="Security">
                <x-doc-nav-link href="#password">Password</x-doc-nav-link>
            </x-doc-nav-group>
            <x-doc-nav-link href="#billing">Billing &amp; Refunds</x-doc-nav-link>
            <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
            BLADE);

        $this->assertSame(['Overview', 'Payment', 'Security', 'Billing & Refunds'], array_column($entries, 'label'));
        $this->assertSame(['overview', 'payment', 'password', 'billing'], array_column($entries, 'anchor'));
        $this->assertSame(['Stripe', 'PayPal'], array_column($entries[1]['children'], 'label'));
        $this->assertSame([], $entries[0]['children']);
    }

    public function test_a_link_that_is_commented_out_or_has_no_target_is_not_listed(): void
    {
        $entries = DocsContents::parse(<<<'BLADE'
            <x-doc-nav-link href="#one">One</x-doc-nav-link>
            {{-- <x-doc-nav-link href="#gone">Gone</x-doc-nav-link> --}}
            <x-doc-nav-link>No target</x-doc-nav-link>
            <x-doc-nav-group label="Group" href="#group">
                <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
                <x-doc-nav-link href="#two">Two</x-doc-nav-link>
            </x-doc-nav-group>
            BLADE);

        $this->assertSame(['one', 'group'], array_column($entries, 'anchor'));
        $this->assertSame(['two'], array_column($entries[1]['children'], 'anchor'));
    }

    public function test_the_lists_are_written_the_way_the_reader_expects(): void
    {
        // The reader is a pattern over Blade source. It does not understand a group inside a
        // group, or a ">" inside an attribute: no guide writes either, and this is what says so.
        foreach (DocsUtils::pages() as $key => $page) {
            $source = file_get_contents(resource_path('views/marketing/docs/'.$key.'.blade.php'));

            if (! preg_match('~<x-slot:toc>(.*?)</x-slot:toc>~s', $source, $slot)) {
                continue;
            }

            $depth = 0;
            preg_match_all('~<(/?)x-doc-nav-group\b~', $slot[1], $groups);
            foreach ($groups[1] as $closing) {
                $depth += $closing === '/' ? -1 : 1;
                $this->assertLessThanOrEqual(1, $depth, "{$key} nests a group inside a group");
            }

            $this->assertSame(0, preg_match('~<x-doc-nav-(?:link|group)\b[^>]*="[^"]*>~', $slot[1]), "{$key} has a > inside an attribute of its contents list");
        }
    }

    public function test_a_section_s_picture_is_the_first_one_it_prints(): void
    {
        $shots = DocsContents::shots('tickets');

        $this->assertSame('tickets--checkin', $shots['check-in'] ?? null);
        $this->assertSame([], DocsContents::shots('subscriptions'), 'A guide with no pictures gives none');
        $this->assertSame([], DocsContents::shots('no-such-page'));

        foreach (DocsUtils::pagesInGroup('user-guide') as $page) {
            $anchors = array_column(DocsContents::for($page['key']), 'anchor');

            foreach (DocsContents::shots($page['key']) as $anchor => $shot) {
                $this->assertContains($anchor, $anchors, "{$page['key']}: a picture is filed under #{$anchor}, which is not a top-level section");
                foreach (['.png', '.webp', '-dark.png', '-dark.webp'] as $suffix) {
                    $this->assertFileExists(public_path('images/docs/'.$shot.$suffix));
                }
            }
        }
    }

    public function test_a_page_with_no_list_and_an_unknown_page_have_no_entries(): void
    {
        $this->assertSame([], DocsContents::for('selfhost/index'));
        $this->assertSame([], DocsContents::for('no-such-page'));
        $this->assertSame([], DocsContents::parse(''));
    }

    public function test_every_guide_with_a_list_is_read_whole_and_every_entry_lands(): void
    {
        foreach (DocsUtils::pages() as $key => $page) {
            $source = file_get_contents(resource_path('views/marketing/docs/'.$key.'.blade.php'));

            if (! str_contains($source, '<x-slot:toc>')) {
                $this->assertSame([], DocsContents::for($key), "{$key} prints no list");

                continue;
            }

            $entries = DocsContents::for($key);
            $this->assertNotEmpty($entries, "{$key} has a contents list that was read as empty");

            // Nothing the page lists is lost: every link and every linked group is an entry or a child.
            $read = 0;
            foreach ($entries as $entry) {
                $read += 1 + count($entry['children']);
            }
            preg_match('~<x-slot:toc>(.*?)</x-slot:toc>~s', $source, $slot);
            $written = preg_match_all('~<x-doc-nav-(?:link|group)\b~', $slot[1]);
            $furniture = preg_match_all('~href="#(?:see-also|next-steps)"~', $slot[1]);
            $this->assertSame($written - $furniture, $read, "{$key}: the list was not read whole");

            foreach ($entries as $entry) {
                foreach (array_merge([$entry], $entry['children']) as $part) {
                    $this->assertNotSame('', $part['label'], "{$key} has an entry with no name");
                    $this->assertStringNotContainsString('&amp;', $part['label'], "{$key}: a label was left encoded");
                    $this->assertStringNotContainsString('<', $part['label']);
                }
            }
        }
    }
}
