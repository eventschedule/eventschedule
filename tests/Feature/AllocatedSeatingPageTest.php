<?php

namespace Tests\Feature;

use Tests\TestCase;

class AllocatedSeatingPageTest extends TestCase
{
    public function test_the_page_renders_and_says_what_it_is(): void
    {
        $body = $this->get(route('marketing.allocated_seating'))->assertOk()->getContent();

        $this->assertStringContainsString('Draw the room once', $body);
        $this->assertStringContainsString('Enterprise', $body);
        // The seat map is server-rendered, so it is there with JavaScript off.
        $this->assertStringContainsString('houseHatch', $body);
        $this->assertGreaterThan(60, substr_count($body, '<circle'), 'the plan should draw its seats');
    }

    public function test_it_is_reachable_and_indexable(): void
    {
        // A page nothing links to is a page nobody reads.
        $features = $this->get(route('marketing.features'))->assertOk()->getContent();
        $this->assertStringContainsString(route('marketing.allocated_seating'), $features);

        // The sitemap is streamed, so the body has to be pulled rather than read off the response.
        $xml = $this->get('/sitemap-pages.xml')->assertOk()->streamedContent();
        $this->assertStringContainsString(url('/features/allocated-seating'), $xml);
    }
}
