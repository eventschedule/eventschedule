<?php

namespace Tests\Feature;

use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Models\FederationClicksDaily;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The public surface: how federated listings appear on /browse, and what must not
 * happen to them there.
 */
class FederationBrowseTest extends TestCase
{
    use RefreshDatabase;

    private function makeInstance(array $attributes = []): FederatedInstance
    {
        return FederatedInstance::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_APPROVED,
        ], $attributes));
    }

    /** A listing that will actually render: approved, imaged, upcoming. */
    private function makeListing(FederatedInstance $instance, array $attributes = []): FederatedEvent
    {
        $event = FederatedEvent::create(array_merge([
            'federated_instance_id' => $instance->id,
            'external_id' => Str::random(8),
            'url' => 'https://operator.test/venue/summer-show',
            'name' => 'Federated Summer Show',
            'language' => 'en',
            'country_code' => 'DE',
            'city' => 'Berlin',
            'venue_name' => 'The Hall',
            'timezone' => 'Europe/Berlin',
            'next_occurrence_at' => now()->addWeek(),
        ], $attributes));

        // image_path is not fillable by a push; only the local fetch sets it.
        $event->image_path = $attributes['image_path'] ?? 'federated/flyer.jpg';
        $event->save();

        return $event;
    }

    public function test_an_approved_listing_appears_and_links_to_the_origin(): void
    {
        $this->makeListing($this->makeInstance());

        $this->get('/browse')
            ->assertOk()
            ->assertSee('Federated Summer Show')
            ->assertSee('https://operator.test/venue/summer-show')
            // The source is named so a visitor knows the click leaves this site.
            ->assertSee('operator.test');
    }

    /**
     * The backlink is the entire value proposition. rel="nofollow" would void it,
     * and this is exactly the kind of thing a later cleanup adds by reflex.
     */
    public function test_the_outbound_link_is_dofollow(): void
    {
        $this->makeListing($this->makeInstance());

        $this->get('/browse')->assertOk()->assertDontSee('nofollow', false);
    }

    public function test_listings_from_unapproved_instances_do_not_appear(): void
    {
        $this->makeListing($this->makeInstance(['status' => FederatedInstance::STATUS_PENDING]));

        $this->get('/browse')->assertOk()->assertDontSee('Federated Summer Show');
    }

    public function test_a_blocked_listing_does_not_appear(): void
    {
        $listing = $this->makeListing($this->makeInstance());
        $listing->block();

        $this->get('/browse')->assertOk()->assertDontSee('Federated Summer Show');
    }

    public function test_a_listing_without_a_local_image_does_not_appear(): void
    {
        $instance = $this->makeInstance();
        $event = $this->makeListing($instance);
        $event->image_path = null;
        $event->save();

        // Matches the bar browse applies to its own events, and keeps rendering off
        // third-party hosts.
        $this->get('/browse')->assertOk()->assertDontSee('Federated Summer Show');
    }

    public function test_the_country_filter_narrows_the_section(): void
    {
        $instance = $this->makeInstance();
        $this->makeListing($instance, ['name' => 'Berlin Gig', 'country_code' => 'DE']);
        $this->makeListing($instance, ['name' => 'Lisbon Gig', 'country_code' => 'PT', 'city' => 'Lisbon']);

        $this->get('/browse?country=DE')
            ->assertOk()
            ->assertSee('Berlin Gig')
            ->assertDontSee('Lisbon Gig');
    }

    public function test_the_language_filter_narrows_the_section(): void
    {
        $instance = $this->makeInstance();
        $this->makeListing($instance, ['name' => 'English Gig', 'language' => 'en']);
        $this->makeListing($instance, ['name' => 'Deutsches Konzert', 'language' => 'de']);

        $this->get('/browse?lang=de')
            ->assertOk()
            ->assertSee('Deutsches Konzert')
            ->assertDontSee('English Gig');
    }

    /**
     * Consistent with keeping federated rows out of the sitemap: this site should not
     * claim off-site URLs as its own list.
     */
    public function test_federated_rows_are_excluded_from_the_item_list_structured_data(): void
    {
        $this->makeListing($this->makeInstance());

        $html = $this->get('/browse')->assertOk()->getContent();

        $start = strpos($html, 'application/ld+json');
        if ($start !== false) {
            $jsonBlock = substr($html, $start, 4000);
            $this->assertStringNotContainsString('operator.test', $jsonBlock);
        }

        $this->assertTrue(true);
    }

    public function test_a_click_is_counted_against_the_instance(): void
    {
        $instance = $this->makeInstance();
        $listing = $this->makeListing($instance);

        $this->post('/browse/federated/'.UrlUtils::encodeId($listing->id).'/click')
            ->assertNoContent();

        $this->assertSame(1, FederationClicksDaily::totalForInstance($instance->id));
    }

    public function test_the_network_section_is_absent_when_there_is_nothing_to_show(): void
    {
        $this->get('/browse')
            ->assertOk()
            ->assertDontSee(__('messages.federation_browse_heading'));
    }

    /**
     * The page is deliberately not a Vue mount, so a listing name containing a
     * mustache expression must render as literal text rather than be compiled.
     */
    public function test_a_listing_name_is_never_evaluated_as_a_template(): void
    {
        $this->makeListing($this->makeInstance(), ['name' => '{{ 7*7 }} Injection Test']);

        $this->get('/browse')
            ->assertOk()
            ->assertDontSee('49 Injection Test')
            ->assertSee('{{ 7*7 }} Injection Test', false);
    }
}
