<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Utils\MarkdownUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TermsTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_page_displays_default_content_when_not_configured(): void
    {
        $response = $this->get('/terms');

        $response->assertOk();
        $response->assertSee(__('messages.terms_and_conditions'));
        $response->assertSee('Eligibility');
    }

    public function test_terms_page_displays_custom_terms_when_configured(): void
    {
        $markdown = "# Custom Terms\n\nThis is a custom agreement.";

        Setting::setGroup('general', [
            'terms_markdown' => $markdown,
            'terms_html' => MarkdownUtils::convertToHtml($markdown),
            'terms_updated_at' => now()->toIso8601String(),
        ]);

        $response = $this->get('/terms');

        $response->assertOk();
        $response->assertSee('Custom Terms', false);
        $response->assertSee('This is a custom agreement.', false);
        $response->assertDontSee('Welcome to Event Schedule', false);
    }

    public function test_terms_page_converts_markdown_when_html_is_missing(): void
    {
        $markdown = "# Legacy Terms\n\nThese terms were saved without HTML.";

        Setting::setGroup('general', [
            'terms_markdown' => $markdown,
        ]);

        $response = $this->get('/terms');

        $response->assertOk();
        $response->assertSee('Legacy Terms', false);
        $response->assertSee('These terms were saved without HTML.', false);
        $response->assertDontSee('Welcome to Event Schedule', false);
    }
}
