<?php

namespace Tests\Feature;

use App\Utils\DocsContents;
use App\Utils\DocsUtils;
use Tests\TestCase;

/**
 * /docs, the home of the guide: "The whole product, written down" (2026-10).
 *
 * The page is whole without its script: every step of the reading list and every guide's contents
 * are in the HTML. The script only chooses which are shown (the goals that are ticked, the guide
 * that is pointed at). So what can go wrong goes wrong quietly: a step whose section was renamed,
 * a guide the line-up does not carry, a picture that was deleted, a goal with nothing behind it,
 * a hook the script looks for and the markup no longer has. Each test below holds one of those.
 */
class DocsHomePageTest extends TestCase
{
    private function html(): string
    {
        return $this->get('/docs')->assertOk()->getContent();
    }

    private function source(): string
    {
        return file_get_contents(resource_path('views/marketing/docs/index.blade.php'));
    }

    /** Every id a guide's view (and the partials it includes) declares. */
    private function idsOf(string $key): array
    {
        $source = file_get_contents(resource_path('views/marketing/docs/'.$key.'.blade.php'));

        preg_match_all("~@include\('([^']+)'~", $source, $includes);
        foreach ($includes[1] as $view) {
            $path = resource_path('views/'.str_replace('.', '/', $view).'.blade.php');
            $source .= is_file($path) ? file_get_contents($path) : '';
        }

        $source = preg_replace('~<x-doc-screenshot\b[^>]*>~', '', $source);
        preg_match_all('~\bid="([^"]+)"~', $source, $ids);

        return $ids[1];
    }

    public function test_every_guide_is_in_the_line_up_once_under_its_act(): void
    {
        $html = $this->html();

        preg_match_all('~<a class="dx-name" href="([^"]+)" data-dx-name~', $html, $names);

        $expected = [];
        foreach (DocsUtils::clusters() as $cluster) {
            foreach ($cluster['pages'] as $page) {
                $expected[] = route($page['route']);
            }
        }

        $this->assertSame($expected, $names[1], 'The line-up must carry every User Guide page once, in the manifest order');
        $this->assertSame(count(DocsUtils::pagesInGroup('user-guide')), count($expected), 'A User Guide page has no cluster');

        // The stage's grid is told how many rows the first column has.
        $rows = count(array_filter(DocsUtils::clusters(), fn ($cluster) => count($cluster['pages']) > 0)) + count($expected);
        $this->assertStringContainsString('style="--dx-rows: '.$rows.';"', $html);

        // One guide is on the stage before any script runs, and only one.
        $this->assertSame(1, preg_match_all('~<div class="dx-item is-on"~', $html));
    }

    public function test_every_link_into_a_guide_lands_on_a_section_of_it(): void
    {
        $html = $this->html();
        $urls = array_flip(DocsUtils::urlMap());
        $ids = [];
        $checked = 0;

        preg_match_all('~href="([^"#]+/docs/[^"#]*)#([^"]+)"~', $html, $links, PREG_SET_ORDER);

        foreach ($links as [, $url, $anchor]) {
            $key = $urls[$url] ?? null;
            $this->assertNotNull($key, "{$url} is not a page of the guide");

            $ids[$key] ??= $this->idsOf($key);
            $this->assertContains($anchor, $ids[$key], "{$key} has no section #{$anchor}");
            $checked++;
        }

        $this->assertGreaterThan(150, $checked, 'The stage, the reading list and the glossary should deep-link well over a hundred sections');
    }

    public function test_the_stage_lists_each_guide_s_own_contents(): void
    {
        $html = $this->html();

        foreach (DocsUtils::pagesInGroup('user-guide') as $page) {
            $parts = DocsContents::for($page['key']);
            $this->assertNotEmpty($parts, "{$page['key']} has no contents list to show");

            $panel = 'id="dx-guide-'.$page['key'].'"';
            $this->assertStringContainsString($panel, $html);
            $this->assertStringContainsString('<span class="dx-mono">'.count($parts).' sections</span>', $html);
            $this->assertMatchesRegularExpression('~href="'.preg_quote(route($page['route']).'#'.$parts[0]['anchor'], '~').'"[^>]*>\s*<span>'.preg_quote(e($parts[0]['label']), '~').'</span>~', $html);
        }
    }

    public function test_every_guide_s_picture_exists_in_both_lights(): void
    {
        foreach (DocsUtils::pagesInGroup('user-guide') as $page) {
            $this->assertNotEmpty($page['shot'] ?? null, "{$page['key']} names no picture for the stage");
            $this->assertNotEmpty($page['shot_alt'] ?? null, "{$page['key']}'s picture has no description");

            foreach (['.png', '.webp', '-dark.png', '-dark.webp'] as $suffix) {
                $this->assertFileExists(public_path('images/docs/'.$page['shot'].$suffix));
            }
        }
    }

    public function test_every_goal_adds_steps_and_every_step_belongs_to_a_goal(): void
    {
        $html = $this->html();

        preg_match_all('~data-dx-goal="([a-z]+)" aria-pressed="false"~', $html, $goals);
        preg_match_all('~<li class="dx-step ([a-z-]*)" data-dx-step="([a-z]+)" data-dx-guide="([^"]+)"~', $html, $steps, PREG_SET_ORDER);

        $this->assertGreaterThanOrEqual(8, count($goals[1]));
        $this->assertSame(array_unique($goals[1]), $goals[1], 'A goal is offered twice');

        $byGoal = [];
        foreach ($steps as [, $class, $goal, $guide]) {
            $byGoal[$goal][] = $guide;
            // Only everybody's steps are shown before a goal is ticked.
            $this->assertSame($goal === 'base', $class === 'is-on', "A {$goal} step starts in the wrong state");
            $this->assertNotNull(DocsUtils::page($guide), "A step names {$guide}, which is not a guide");
        }

        $this->assertGreaterThanOrEqual(3, count($byGoal['base'] ?? []), 'The list everybody starts from');
        foreach ($goals[1] as $goal) {
            $this->assertNotEmpty($byGoal[$goal] ?? [], "Ticking \"{$goal}\" would add nothing to the list");
            // The address carries the ticks as #path-a-b, so a key cannot hold a hyphen.
            $this->assertMatchesRegularExpression('/^[a-z]+$/', $goal);
        }
        $this->assertSame([], array_diff(array_keys($byGoal), array_merge(['base'], $goals[1])), 'A step waits for a goal nobody can tick');
    }

    public function test_every_page_of_the_three_shelves_is_one_press_away(): void
    {
        $html = $this->html();
        preg_match('~<section id="platforms".*?</section>~s', $html, $night);
        $this->assertNotEmpty($night);

        foreach (['selfhost', 'saas', 'developer'] as $group) {
            $pages = DocsUtils::pagesInGroup($group);

            foreach ($pages as $page) {
                $this->assertStringContainsString('href="'.route($page['route']).'"', $night[0], "{$page['key']} is not on its shelf");
            }
            // The count over a shelf is the count of the rows under it.
            $this->assertStringContainsString('<span class="dx-mono">'.count($pages).' pages</span>', $night[0]);
        }

        // Each shelf opens on a line its guide really prints.
        foreach ([
            'selfhost/installation' => '* * * * * php /path/to/eventschedule/artisan schedule:run',
            'saas/setup' => '<span class="code-variable">IS_HOSTED</span>=<span class="code-value">true</span>',
            'developer/api' => '-H <span class="code-string">"X-API-Key: your_api_key_here"</span>',
        ] as $key => $line) {
            $this->assertStringContainsString($line, file_get_contents(resource_path('views/marketing/docs/'.$key.'.blade.php')), "{$key} no longer prints the line the home page quotes");
            $this->assertStringContainsString($line, $night[0]);
        }
    }

    public function test_the_glossary_on_the_page_is_the_glossary_in_the_head(): void
    {
        $html = $this->html();

        // A word shows its first sentence and opens on the rest; together they are the definition.
        preg_match_all('~<details class="dx-word" id="term-([a-z0-9-]+)" name="dx-word">.*?<dfn class="dx-word-name">([^<]+)</dfn>\s*<span class="dx-word-first">(.*?)</span>.*?<div class="dx-word-more">(.*?)</div>\s*</details>~s', $html, $visible, PREG_SET_ORDER);
        preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $html, $blocks);

        $set = null;
        foreach ($blocks[1] as $block) {
            $data = json_decode($block, true);
            if (($data['@type'] ?? null) === 'DefinedTermSet') {
                $set = $data;
            }
        }

        $this->assertNotNull($set, 'The page lost its DefinedTermSet');
        $this->assertSame(count($set['hasDefinedTerm']), count($visible), 'A word is in one place and not the other');

        $text = fn (string $markup) => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($markup), ENT_QUOTES | ENT_HTML5)));
        $ids = [];

        foreach ($set['hasDefinedTerm'] as $i => $term) {
            [, $id, $name, $first, $more] = $visible[$i];
            $rest = preg_match('~<p>(.*?)</p>~s', $more, $paragraph) ? $text($paragraph[1]) : '';

            $this->assertSame($term['name'], $text($name));
            $this->assertSame($term['description'], trim($text($first).' '.$rest), "{$term['name']}: the page and the head disagree");
            $this->assertNotSame('', $text($first));
            $ids[] = 'term-'.$id;
        }

        // A definition that sends the reader to another word links to a word that is there.
        preg_match_all('~href="#(term-[a-z0-9-]+)" data-dx-word-link~', $html, $links);
        $this->assertNotEmpty($links[1], '"(see Subscriber)" should be a link');
        $this->assertSame([], array_values(array_diff($links[1], $ids)));
    }

    public function test_every_screen_the_stage_can_show_exists(): void
    {
        $html = $this->html();

        // A dot on a picture, and a contents line that carries a picture mark, each name a
        // screenshot the script fetches by name: nothing else would notice one going missing.
        preg_match_all('~data-dx-pi[cp]="([^"]+)"~', $html, $asked);
        preg_match('~data-dx-shots="([^"]+)" data-dx-frames="([^"]*)"~', $html, $bill);

        $this->assertGreaterThan(20, count(array_unique($asked[1])), 'Many sections print a screenshot of their own; the stage should offer them');
        $this->assertSame(url('images/docs').'/', $bill[1]);

        $framed = [];
        foreach (array_filter(explode(' ', $bill[2])) as $pair) {
            [$shot, $frame] = explode(':', $pair);
            $this->assertContains($frame, ['page', 'dialog'], "{$shot} is framed in a way the stylesheet does not know");
            $framed[] = $shot;
        }

        foreach (array_unique(array_merge($asked[1], $framed)) as $shot) {
            foreach (['.png', '.webp', '-dark.png', '-dark.webp'] as $suffix) {
                $this->assertFileExists(public_path('images/docs/'.$shot.$suffix));
            }
        }

        // A screen with nothing on it yet is passed over.
        $this->assertSame([], array_values(array_intersect($asked[1], config('docs.empty_shots'))));

        // A picture has dots only when there is more than one screen to choose, and one is chosen.
        preg_match_all('~<span class="dx-pips".*?</span>~s', $html, $strips);
        $this->assertNotEmpty($strips[0]);
        foreach ($strips[0] as $strip) {
            $this->assertGreaterThan(1, substr_count($strip, 'data-dx-pip='));
            $this->assertSame(1, substr_count($strip, 'aria-pressed="true"'));
        }
    }

    public function test_a_long_list_says_how_many_lines_a_small_frame_leaves_out(): void
    {
        $html = $this->html();

        foreach (DocsUtils::pagesInGroup('user-guide') as $page) {
            $count = count(DocsContents::for($page['key']));
            preg_match('~id="dx-guide-'.preg_quote($page['key'], '~').'".*?<ul class="dx-parts([^"]*)">(.*?)</ul>~s', $html, $list);

            $this->assertNotEmpty($list, "{$page['key']} has no contents on the stage");
            $this->assertSame($count, substr_count($list[2], '<li>'), "{$page['key']}: every section is printed");

            // A list is cut only when at least two lines would go, and then says how many.
            foreach ([7, 8, 11] as $kept) {
                $cut = $count >= $kept + 2;
                $this->assertSame($cut, str_contains($list[1], 'cut-'.$kept), "{$page['key']} at {$kept} lines");
                $this->assertSame($cut, str_contains($list[2], 'dx-rest-'.$kept.'"><a href="'.route($page['route']).'"><span>and '.($count - $kept).' more in the guide'));
            }
        }
    }

    public function test_the_script_s_hooks_are_in_the_markup_and_the_markup_s_in_the_script(): void
    {
        $html = $this->html();
        $source = $this->source();

        // Every data-dx-* attribute the script asks for is printed, and every one printed is asked for.
        preg_match_all('~\[data-(dx-[a-z-]+)~', $source, $asked);
        preg_match_all('~getAttribute\(\'data-(dx-[a-z-]+)\'\)~', $source, $read);
        preg_match_all('~(?<=\s)data-(dx-[a-z-]+)(?=[=\s>])~', $html, $printed);

        $used = array_unique(array_merge($asked[1], $read[1]));
        $this->assertNotEmpty($used);
        $this->assertSame([], array_values(array_diff($used, $printed[1])), 'The script looks for a hook the page does not print');
        $this->assertSame([], array_values(array_diff(array_unique($printed[1]), $used)), 'The page prints a hook nothing uses');
    }

    public function test_the_page_is_whole_without_script_and_keeps_the_house_rules(): void
    {
        $html = $this->html();
        $source = $this->source();

        // Nothing waits behind a hidden attribute for a script to reveal it.
        preg_match('~<main\b.*?</main>~s', $html, $main);
        $this->assertSame(0, preg_match('~<(?:li|div|section|ol|ul|p)\b[^>]*\shidden[\s>]~', preg_replace('~<div[^>]*data-role="results"[^>]*>~', '', $main[0])), 'Something in the page starts hidden');

        // The reveals are fired by marketing-home.js; the page must never lose it.
        $this->assertStringContainsString('data-reveal', $source);
        $this->assertStringContainsString("@vite('resources/js/marketing-home.js')", $source);

        $this->assertStringNotContainsString('—', $source, 'No em dashes');
        $this->assertDoesNotMatchRegularExpression('/self-host(?!ing)/i', $source, 'Write "selfhost"');
        $this->assertStringNotContainsString('<!--', $source, 'Notes are Blade comments, so they are not sent');
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+="/', $source, 'No inline event handlers (the CSP refuses them)');
        $this->assertSame(substr_count($source, '<script'), substr_count($source, '<script {!! nonce_attr() !!}>') + substr_count($source, '<script type="application/ld+json" {!! nonce_attr() !!}>'), 'Every script carries the nonce');
    }
}
