<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Webhook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * /features, "The board" (2026-10).
 *
 * Under the hero one console holds the product as forty keys in five banks. A key is a link to
 * its feature page that the page's script turns into a switch: pressed, a part of the scene on
 * the stage appears, the readout says what the feature does and the plan it needs (in the words
 * of the key's own entry in the chapter below), and the tally names the plan the lit keys add
 * up to. The server draws the board as the Free plan.
 *
 * Nothing here fails loudly. A key with no entry reads nothing; a part of the stage named for a
 * key that was renamed never appears; a key lit on a plan it is not on is a wrong claim about
 * what the product costs. Each test below holds one of those joins.
 */
class FeaturesPageTest extends TestCase
{
    use RefreshDatabase;

    private const BANKS = ['sell', 'schedule', 'promote', 'engage', 'own-it'];

    private const PLANS = ['free' => 'Free', 'pro' => 'Pro', 'ent' => 'Enterprise'];

    /** Each key's row in docs/FEATURES.md, by the name the row has there. Its table is the key's plan. */
    private const KEY_FEATURES = [
        'tickets' => 'Sell paid tickets',
        'registration' => 'Free event registration / RSVP',
        'promo' => 'Promo/discount codes',
        'passes' => 'Passes & subscriptions',
        'gift' => 'Gift cards',
        'installments' => 'Installment payments',
        'waitlist' => 'Ticket waitlist',
        'seating' => 'Allocated (reserved) seating',
        'recurring' => 'Recurring events',
        'sync' => 'Google Calendar sync',
        'import' => 'AI event parsing',
        'subs' => 'Sub-schedules',
        'online' => 'Online events',
        'appointments' => 'Appointment booking (1 free appointment type)',
        'requests' => 'Event requests',
        'availability' => 'Availability management',
        'newsletters' => 'Newsletter management',
        'graphics' => 'Generate event graphics',
        'signup' => 'Email subscribers (audience capture)',
        'links' => 'Short links',
        'embedcal' => 'Embed calendar on website',
        'lineup' => 'Claim a page created for you',
        'boost' => 'Event boosting with ads',
        'embedtix' => 'Embed ticket widget',
        'checkin' => 'QR code scanning at the door',
        'fan' => 'Fan videos, photos & comments on events',
        'analytics' => 'Built-in analytics',
        'polls' => 'Event polls',
        'feedback' => 'Post-event feedback',
        'carpool' => 'Carpool matching',
        'gallery' => 'Photo gallery (events + schedules)',
        'sponsors' => 'Sponsor/partner logos',
        'whitelabel' => 'Remove Event Schedule branding',
        'css' => 'Custom CSS styling',
        'labels' => 'Custom labels',
        'fields' => 'Custom fields',
        'api' => 'REST API access',
        'domain' => 'Custom domains',
        'private' => 'Internal & unlisted events',
        'team' => 'Multiple team members',
    ];

    private function html(): string
    {
        return $this->get('/features')->assertOk()->getContent();
    }

    private function xpath(?string $html = null): \DOMXPath
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.($html ?? $this->html()));

        return new \DOMXPath($dom);
    }

    private function source(): string
    {
        return File::get(resource_path('views/marketing/features.blade.php'));
    }

    /** The keys of the board as id => [bank, plan, href, lit, needs]. */
    private function keys(\DOMXPath $xpath): array
    {
        $keys = [];

        foreach ($xpath->query('//*[@data-fb-key]') as $key) {
            $bank = $xpath->query('ancestor::*[@data-fb-bank]', $key)->item(0);
            $keys[$key->getAttribute('data-fb-key')] = [
                'bank' => $bank?->getAttribute('data-fb-bank'),
                'plan' => $key->getAttribute('data-plan'),
                'href' => $key->getAttribute('href'),
                'lit' => str_contains(' '.$key->getAttribute('class').' ', ' is-on '),
                'needs' => $key->getAttribute('data-needs') ?: null,
            ];
        }

        return $keys;
    }

    private function has(\DOMElement $node, string $class): bool
    {
        return str_contains(' '.$node->getAttribute('class').' ', ' '.$class.' ');
    }

    public function test_the_board_is_forty_keys_in_five_banks_and_the_page_counts_them_right(): void
    {
        $html = $this->html();
        $xpath = $this->xpath($html);
        $keys = $this->keys($xpath);

        $this->assertCount(40, $keys, 'the board is forty keys, each with an id of its own');
        $this->assertSame(array_fill_keys(self::BANKS, 8), array_count_values(array_column($keys, 'bank')), 'five banks of eight, in the order of the chapters');
        $this->assertSame([], array_diff(array_column($keys, 'plan'), array_keys(self::PLANS)));

        $free = count(array_filter($keys, fn ($key) => $key['plan'] === 'free'));
        $rows = $xpath->query('//*[@id="also-list"]/div')->length;

        // The words on the page that are numbers.
        $this->assertSame(17, $free, 'the page says "the seventeen lit ones"');
        $this->assertSame(30, $rows, 'the page says "Thirty more things" and "Show all thirty"');
        foreach (['Forty features.', 'The seventeen lit ones', 'Play all forty', 'Forty keys, five banks', 'Thirty more things', 'Show all thirty'] as $words) {
            $this->assertStringContainsString($words, $html);
        }
        $this->assertStringContainsString("name: 'All forty on'", $html);
        $this->assertSame((string) $free, trim($xpath->query('//*[@data-fb-n]')->item(0)->textContent));
        $this->assertStringContainsString('of 40 on', $html);
    }

    public function test_every_key_has_one_entry_in_its_chapter_and_the_two_agree(): void
    {
        $xpath = $this->xpath();
        $keys = $this->keys($xpath);
        $entries = $xpath->query('//*[@data-fb-card]');

        $this->assertSame(count($keys), $entries->length, 'a key without an entry has nothing to read out, and an entry without a key is never lit');

        foreach ($entries as $entry) {
            $id = $entry->getAttribute('data-fb-card');
            $this->assertArrayHasKey($id, $keys, "the entry \"$id\" has no key on the board");
            $key = $keys[$id];

            $chapter = $xpath->query('ancestor::section[@data-fb-ch]', $entry)->item(0);
            $this->assertSame($key['bank'], $chapter?->getAttribute('data-fb-ch'), "\"$id\" is in another chapter than its bank");
            $this->assertSame($key['bank'], $chapter?->getAttribute('id'));

            $link = $xpath->query('.//h3/a', $entry)->item(0);
            $this->assertSame($key['href'], $link?->getAttribute('href'), "the key and the entry of \"$id\" lead to different pages");
            $this->assertNotSame('', trim($xpath->query('./p', $entry)->item(0)?->textContent ?? ''), "\"$id\" has no sentence for the readout");
            $this->assertSame(self::PLANS[$key['plan']], trim($xpath->query('.//*[contains(@class, "fb-tier")]', $entry)->item(0)?->textContent ?? ''), "\"$id\" names another plan in its chapter than on its key");
            $this->assertSame($key['lit'], $this->has($entry, 'is-on'), "the lamp of \"$id\" disagrees with its key");
        }
    }

    public function test_the_page_arrives_lit_for_the_free_plan_and_says_so(): void
    {
        $xpath = $this->xpath();
        $keys = $this->keys($xpath);
        $stage = $xpath->query('//*[@data-fb-stage]')->item(0);
        $this->assertNotNull($stage);

        $has = [];
        foreach (preg_split('/\s+/', $stage->getAttribute('class')) as $class) {
            if (str_starts_with($class, 'has-')) {
                $has[] = substr($class, 4);
            }
        }
        $free = array_keys(array_filter($keys, fn ($key) => $key['plan'] === 'free'));
        sort($has);
        sort($free);
        $this->assertSame($free, $has, 'the stage must carry has-<key> for the free keys and no others');

        foreach ($keys as $id => $key) {
            $this->assertSame($key['plan'] === 'free', $key['lit'], "\"$id\" is lit on arrival exactly when it is free");
        }
        foreach ($xpath->query('.//*[@data-fb-slot]', $stage) as $part) {
            $id = $part->getAttribute('data-fb-slot');
            $this->assertSame($keys[$id]['lit'] ?? null, $this->has($part, 'is-on'), "a part of the stage for \"$id\" disagrees with its key");
        }
        foreach ($xpath->query('.//*[@data-fb-unless]', $stage) as $part) {
            $this->assertFalse($this->has($part, 'is-off'), 'nothing a paid key replaces may start hidden');
        }

        // The tally, the bank counts and the checkout all describe that same board.
        $tally = $xpath->query('//*[@data-fb-tally]')->item(0);
        $this->assertSame('free', $tally->getAttribute('data-plan'));
        foreach ($xpath->query('.//*[@data-fb-needs]', $tally) as $says) {
            $this->assertSame($says->getAttribute('data-fb-needs') !== 'free', $says->hasAttribute('hidden'));
        }
        foreach ($xpath->query('//*[@data-fb-bank]') as $bank) {
            $lit = count(array_filter($keys, fn ($key) => $key['bank'] === $bank->getAttribute('data-fb-bank') && $key['lit']));
            $this->assertSame((string) $lit, trim($xpath->query('.//*[@data-fb-bank-n]', $bank)->item(0)->textContent));
            $this->assertSame((string) $lit, trim($xpath->query('//section[@data-fb-ch="'.$bank->getAttribute('data-fb-bank').'"]//*[@data-fb-ch-n]')->item(0)->textContent));
        }
        $this->assertSame('Free', trim($xpath->query('//*[@data-fb-total]')->item(0)->textContent));
        $this->assertSame('Register', trim($xpath->query('//*[@data-fb-pay]')->item(0)->textContent));
        $this->assertTrue($xpath->query('//*[@data-fb-reset]')->item(0)->hasAttribute('hidden'), 'there is nothing to go back from on arrival');
    }

    public function test_every_part_of_the_stage_belongs_to_a_key_and_every_key_changes_the_stage(): void
    {
        $xpath = $this->xpath();
        $keys = $this->keys($xpath);
        $rank = array_flip(array_keys(self::PLANS));
        $touched = [];

        foreach (['data-fb-slot', 'data-fb-unless'] as $attribute) {
            foreach ($xpath->query('//*[@data-fb-stage]//*[@'.$attribute.']') as $part) {
                $id = $part->getAttribute($attribute);
                $this->assertArrayHasKey($id, $keys, "the stage has a part for \"$id\", which is no key");
                $touched[$id] = true;
            }
        }
        // A key may also work through the stage's own has-<key> class (Custom CSS restyles a page).
        foreach (array_keys($keys) as $id) {
            if (str_contains($this->source(), '.fb-stage.has-'.$id.' ')) {
                $touched[$id] = true;
            }
        }
        $this->assertSame([], array_values(array_diff(array_keys($keys), array_keys($touched))), 'a key that changes nothing on the stage is a dead press');

        foreach ($keys as $id => $key) {
            if ($key['needs'] === null) {
                continue;
            }
            $this->assertArrayHasKey($key['needs'], $keys, "\"$id\" needs \"{$key['needs']}\", which is no key");
            // Otherwise pressing one key would raise the tally above the plan written on it.
            $this->assertLessThanOrEqual($rank[$key['plan']], $rank[$keys[$key['needs']]['plan']], "\"$id\" brings on a key of a higher plan than its own");
        }

        // Five scenes, five banks, five chapters: the same ids in the same order.
        foreach (['//*[@data-fb-scene]' => 'data-fb-scene', '//*[@data-fb-bank]' => 'data-fb-bank', '//*[@data-fb-tab]' => 'data-fb-tab', '//section[@data-fb-ch]' => 'data-fb-ch'] as $query => $attribute) {
            $ids = [];
            foreach ($xpath->query($query) as $node) {
                $ids[] = $node->getAttribute($attribute);
            }
            $this->assertSame(self::BANKS, $ids, "$attribute is not the five banks in order");
        }
        foreach ($xpath->query('//*[@data-fb-bank]') as $bank) {
            $this->assertSame('#'.$bank->getAttribute('data-fb-bank'), $xpath->query('.//h3/a', $bank)->item(0)?->getAttribute('href'), 'a bank heading is the way to its chapter');
        }
    }

    public function test_a_key_s_plan_is_its_table_in_the_feature_reference(): void
    {
        $features = [];
        $plan = null;
        foreach (file(base_path('docs/FEATURES.md'), FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^## (Free|Pro|Enterprise) Features\b/', $line, $heading)) {
                $plan = array_search($heading[1], self::PLANS, true);

                continue;
            }
            if (str_starts_with($line, '## ')) {
                $plan = null;
            }
            if ($plan !== null && preg_match('/^\| ([^|~][^|]*?) \|/', $line, $row) && ! in_array($row[1], ['Feature', '---------', '--------'], true) && ! str_starts_with($row[1], '-')) {
                $features[$row[1]] = $plan;
            }
        }
        $this->assertGreaterThan(80, count($features), 'docs/FEATURES.md was not read');

        $keys = $this->keys($this->xpath());
        $this->assertSame([], array_values(array_diff(array_keys($keys), array_keys(self::KEY_FEATURES))), 'a key has no row named for it in KEY_FEATURES');
        $this->assertSame([], array_values(array_diff(array_keys(self::KEY_FEATURES), array_keys($keys))), 'KEY_FEATURES names a key the board does not have');

        foreach (self::KEY_FEATURES as $id => $feature) {
            $this->assertArrayHasKey($feature, $features, "docs/FEATURES.md has no \"$feature\" row for the \"$id\" key");
            $this->assertSame($features[$feature], $keys[$id]['plan'], "the \"$id\" key says another plan than docs/FEATURES.md gives \"$feature\"");
        }

        // The blog's fact sheet names the plan of the same pages: the two may not disagree.
        foreach (config('blog_facts') as $fact) {
            foreach ($keys as $id => $key) {
                if (isset($fact['url']) && in_array($fact['tier'], ['free', 'pro', 'enterprise'], true) && str_ends_with($key['href'], $fact['url']) && $id !== 'checkin' && $id !== 'waitlist') {
                    $this->assertSame($fact['tier'] === 'enterprise' ? 'ent' : $fact['tier'], $key['plan'], "config/blog_facts.php and the \"$id\" key disagree");
                }
            }
        }
    }

    public function test_every_key_leads_to_a_page_and_to_the_section_it_names(): void
    {
        $pages = [];

        foreach ($this->keys($this->xpath()) as $id => $key) {
            $path = parse_url($key['href'], PHP_URL_PATH);
            $fragment = parse_url($key['href'], PHP_URL_FRAGMENT);
            $this->assertStringStartsWith('/features/', $path, "the \"$id\" key does not lead to a feature page");

            $pages[$path] ??= $this->get($path)->assertOk()->getContent();
            if ($fragment) {
                $this->assertStringContainsString('id="'.$fragment.'"', $pages[$path], "$path has no #$fragment for the \"$id\" key to land on");
            }
        }
    }

    public function test_the_prices_on_the_board_are_the_installation_s_own(): void
    {
        $html = $this->html();
        $pro = plan_price(\App\Utils\PlatformPricing::proMonthly());
        $enterprise = plan_price(\App\Utils\PlatformPricing::enterpriseMonthly());

        $this->assertStringContainsString('data-price-pro="'.e($pro).' a month"', $html);
        $this->assertStringContainsString('data-price-ent="'.e($enterprise).' a month"', $html);
        $this->assertStringContainsString('This board runs on Pro: '.e($pro).' a month.', $html);
        $this->assertStringContainsString('This board runs on Enterprise: '.e($enterprise).' a month.', $html);
        $this->assertStringContainsString('Everything lit is on the Free plan: '.e(plan_price(0)).', permanently.', $html);

        // The find box's last resort lands on the plan table, by its own id.
        $this->assertSame(1, preg_match('/data-pricing="([^"]+)#compare"/', $html, $pricing));
        $this->assertStringContainsString('id="compare"', $this->get(parse_url($pricing[1], PHP_URL_PATH))->assertOk()->getContent());
    }

    public function test_the_stage_speaks_in_the_product_s_own_words(): void
    {
        $xpath = $this->xpath();
        $labels = Role::getCustomizableLabels();

        // What "Custom labels" renames on the stage must be something an owner can rename.
        foreach ($xpath->query('//*[@data-fb-unless="labels"]') as $label) {
            $this->assertContains(trim($label->textContent), $labels, 'the stage renames a word that is not a customizable label');
        }
        $this->assertGreaterThanOrEqual(2, $xpath->query('//*[@data-fb-unless="labels"]')->length);

        $hook = trim(preg_replace('/\s+/', ' ', $xpath->query('//*[@data-fb-slot="api"]')->item(0)->textContent));
        $this->assertSame(1, preg_match('/^POST (\S+) 200$/', $hook, $event));
        $this->assertContains($event[1], Webhook::EVENT_TYPES);

        // The month on the stage is a real one: its first day and the night's date agree with the calendar.
        $this->assertSame('Thursday', date('l', strtotime('2026-10-01')));
        $this->assertSame('Friday', date('l', strtotime('2026-10-23')));
        $this->assertStringContainsString('Fri, Oct 23', $this->html());
    }

    public function test_the_script_finds_every_hook_it_asks_for(): void
    {
        $html = $this->html();
        $source = $this->source();

        preg_match_all('/\[data-(fb|also)-[a-z-]+(?:="[^"\']*")?\]/', $source, $selectors);
        $asked = array_unique(array_map(fn ($selector) => preg_replace('/^\[(data-[a-z-]+).*$/', '$1', $selector), $selectors[0]));

        $this->assertGreaterThan(25, count($asked));
        foreach ($asked as $attribute) {
            $this->assertStringContainsString(' '.$attribute, $html, "the script or the styles ask for [$attribute], which the page does not carry");
        }
        // The readout reads an entry by these, and nothing else names them.
        foreach (["querySelector('h3 a')", "querySelector('small')", "querySelector('p')", ".fb-ch-lede')"] as $read) {
            $this->assertStringContainsString($read, $source);
        }
    }

    public function test_the_page_is_whole_without_its_script(): void
    {
        $html = $this->html();
        $xpath = $this->xpath($html);

        // A key is a link first: forty pages reachable from the board with no script at all.
        foreach ($xpath->query('//*[@data-fb-key]') as $key) {
            $this->assertSame('a', $key->nodeName);
            $this->assertFalse($key->hasAttribute('role'), 'the script, not the server, makes a key a button');
        }
        // What only a script can work is not offered until one runs.
        $this->assertTrue($xpath->query('//*[@data-fb-play]')->item(0)->hasAttribute('hidden'));
        $this->assertTrue($xpath->query('//*[@id="also-filter"]')->item(0)->hasAttribute('hidden'));
        foreach (['#hp:not(.fb-js) .fb-find { display: none; }', '#hp:not(.fb-js) .fb-read-live { display: none; }', '#hp.fb-js .fb-read-rest { display: none; }'] as $rule) {
            $this->assertStringContainsString($rule, $html);
        }
        $this->assertSame(1, $xpath->query('//h1')->length);
    }

    public function test_nothing_is_hidden_before_the_script_and_motion_are_there_to_show_it(): void
    {
        $source = $this->source();

        // A pre-state without both gates leaves a visitor with no script, or with motion off,
        // looking at an unlit board or an empty stage for good.
        preg_match_all('/^\s*([^{}\n]*:not\(\.is-seen\)[^{}\n]*)\{/m', $source, $rules);
        $this->assertCount(3, $rules[1]);
        foreach ($rules[1] as $selector) {
            $this->assertStringStartsWith('html.es-anim #hp.fb-js ', trim($selector));
        }

        $this->assertStringNotContainsString('infinite', $source, 'nothing on this page moves forever');
        $this->assertStringNotContainsString('setInterval', $source);
        $this->assertStringNotContainsString('innerHTML', $source);
        $this->assertStringNotContainsString('<!--', $source, 'an HTML comment is sent to the visitor: notes are Blade comments');
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+="/', $source, 'the content security policy drops inline handlers');
        $this->assertStringNotContainsString("\u{2014}", $source);
        $this->assertStringNotContainsString('self-host', strtolower($source));
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $source);
    }
}
