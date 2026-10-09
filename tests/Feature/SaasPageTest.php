<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /saas ("Own every layer") is one view whose mocks, script and anchors are joined by attribute,
 * so the ways it breaks are quiet: a chapter the hero's label no longer reaches, a mock the typed
 * name skips, a setting printed in a mock that the app does not have, a light the script cannot
 * find. Each test here holds one of those joins. What the page may say about prices is
 * MarketingPriceTest's, and its keyword, strip and reveal gate have their own tests.
 */
class SaasPageTest extends TestCase
{
    private function page(): string
    {
        return $this->get('/saas')->assertOk()->getContent();
    }

    public function test_the_four_layers_are_chapters_and_the_hero_reaches_each(): void
    {
        $html = $this->page();

        foreach (['brand', 'tenants', 'billing', 'infra'] as $index => $id) {
            $number = $index + 1;

            // The label beside the hero's stack, the chapter it jumps to, and the number the
            // margin stack follows are three spellings of one layer.
            $this->assertStringContainsString('<a href="#'.$id.'" class="sk-tag" data-l="'.$number.'">', $html);
            $this->assertMatchesRegularExpression('/<section id="'.$id.'" class="[^"]*" data-l="'.$number.'" data-sk-ch="'.$number.'">/', $html);
            $this->assertSame(1, substr_count($html, 'id="'.$id.'"'), "#{$id} must exist once");
        }

        // Every section a link, the review of the page or an older bookmark can name.
        foreach (['top', 'machinery', 'catch', 'product', 'federation', 'revenue', 'launch', 'compare', 'paths', 'faq', 'start'] as $id) {
            $this->assertSame(1, substr_count($html, ' id="'.$id.'"'), "#{$id} must exist once");
        }
    }

    public function test_a_typed_name_has_a_place_in_every_chapter(): void
    {
        $html = $this->page();

        // Two boxes (the hero's and the finale's) that the script keeps as one.
        $this->assertSame(2, substr_count($html, ' data-sk-name>'));
        $this->assertStringContainsString("root.querySelectorAll('[data-sk-name]')", $html);
        $this->assertStringContainsString("root.querySelectorAll('[data-sk]')", $html);

        // The name is only ever a logo and a domain: the places the app really takes them.
        // (config/app.php's name is a literal, so no mock may claim APP_NAME renames anything.)
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'data-sk="name"'));
        $this->assertGreaterThanOrEqual(6, substr_count($html, 'data-sk="mark"'));
        $this->assertGreaterThanOrEqual(20, substr_count($html, 'data-sk="domain"'));
        $this->assertStringNotContainsString('APP_NAME', $html);

        // The value reaches the page as text, never as markup.
        $this->assertStringNotContainsString('innerHTML', $html);
    }

    public function test_the_mocks_only_print_settings_and_commands_that_exist(): void
    {
        $html = $this->page();
        $example = file_get_contents(base_path('.env.example'));

        foreach (['APP_URL', 'APP_LOGO_LIGHT', 'APP_LOGO_DARK', 'APP_MARKETING_URL', 'TRIAL_DAYS', 'STRIPE_PLATFORM_SECRET', 'ADS_ENABLED', 'PROMOTIONS_ENGINE_ENABLED', 'STAY22_ENABLED'] as $key) {
            $this->assertStringContainsString($key, $html, "{$key} left the page");
            $this->assertMatchesRegularExpression('/^#?\s*'.$key.'=/m', $example, "{$key} is not a setting in .env.example");
        }

        // Three strings an earlier version of this page printed that were never true.
        $this->assertStringNotContainsString('STRIPE_SECRET=', $html);
        $this->assertStringNotContainsString('PromotionController', $html);
        $this->assertStringNotContainsString('git clone eventschedule/eventschedule', $html);

        // The Docker install is its own repository, as the README and /selfhost say.
        $this->assertStringContainsString('eventschedule/<wbr>dockerfiles.git', $html);
        $this->assertStringContainsString('eventschedule/dockerfiles', file_get_contents(base_path('README.md')));
        $this->assertStringContainsString('docker compose up --build -d', $html);
    }

    public function test_there_are_five_hundred_lights_in_one_fixed_order(): void
    {
        $html = $this->page();

        $this->assertSame(1, preg_match('/id="sk-field" data-order=\'(\[[0-9,]+\])\'>(.*?)<\/div>/s', $html, $field));
        $order = json_decode($field[1], true);

        // A shuffle of every light, each named once: the script lights them in this order.
        $this->assertCount(500, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame(range(0, 499), $sorted);
        $this->assertSame(500, substr_count($field[2], '<i'));

        // The page arrives showing the sliders' own starting point, with no script needed.
        $this->assertSame(25, substr_count($field[2], 'class="is-on"'));
        $this->assertStringContainsString('id="es-r-customers" class="sk-range" min="1" max="500" step="1" value="25"', $html);

        // The page is cached at the edge, so the order cannot differ from one render to the next.
        $this->assertSame(1, preg_match('/id="sk-field" data-order=\'(\[[0-9,]+\])\'/', $this->page(), $again));
        $this->assertSame($field[1], $again[1]);
    }

    public function test_the_page_is_whole_without_script_and_nothing_blinks(): void
    {
        $html = $this->page();

        // The gate says script is here; the controls that need it are hidden by that word alone.
        $this->assertStringContainsString("document.documentElement.classList.add('sk-js');", $html);
        $this->assertStringContainsString('html:not(.sk-js) #hp :is(.sk-name, .sk-finale-brand, .sk-knobs) { display: none; }', $html);
        $this->assertStringContainsString('html:not(.sk-js) #hp .sk-switch { display: none; }', $html);

        // Every hidden resting state is behind the motion gate.
        preg_match_all('/^\s*([^{}\n]*:not\(\.is-revealed\)[^{}\n]*)\{/m', $html, $rules);
        $this->assertNotEmpty($rules[1]);
        foreach ($rules[1] as $selector) {
            $this->assertStringStartsWith('html.es-anim ', trim($selector), 'A pre-reveal state outside the motion gate: '.trim($selector));
        }

        // Blinking cursors were taken off this page by request.
        $this->assertDoesNotMatchRegularExpression('/@keyframes\s+[a-z-]*(caret|blink|cursor)/i', $html);
    }

    public function test_the_credit_is_drawn_at_the_size_the_guest_layout_gives_it(): void
    {
        $html = $this->page();
        $guest = file_get_contents(resource_path('views/layouts/app-guest.blade.php'));

        // "Shown at actual size" is a claim. The guest layout's chip is 12px type in a pill padded
        // 12px by 6px with a 16px mark; the page's copy of it spells the same sizes in rem.
        $this->assertStringContainsString('gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-xs font-medium', $guest);
        $this->assertStringContainsString('flex h-4 w-4 items-center justify-center rounded-[5px]', $guest);
        $this->assertStringContainsString('#hp .sk-chip { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem;', $html);
        $this->assertMatchesRegularExpression('/#hp \.sk-chip \{[^}]*font-size: 0\.75rem;/', $html);
        $this->assertMatchesRegularExpression('/#hp \.sk-chip i \{[^}]*width: 1rem; height: 1rem; border-radius: 5px;/', $html);
        $this->assertStringContainsString('Shown at actual size.', $html);
    }
}
