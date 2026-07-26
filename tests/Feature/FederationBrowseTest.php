<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Models\FederationClicksDaily;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The public surface: how federated listings appear on /browse, and what must not
 * happen to them there.
 */
class FederationBrowseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A local event that /browse's own discovery query will surface. */
    private function makeLocalDiscoverableEvent(array $attrs = []): Event
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        return $this->createEvent($role, array_merge([
            'name' => 'Local Showcase',
            // Discovery only surfaces events whose card shows a real image.
            'flyer_image_url' => 'flyer.jpg',
            'creator_role_id' => $role->id,
        ], $attrs));
    }

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
        $listing = $this->makeListing($this->makeInstance());

        $html = $this->get('/browse')->assertOk()->getContent();

        // Scoped to the listing's own anchor. Asserting `nofollow` is absent from the
        // whole page would break the day any unrelated link on /browse legitimately
        // carries it, and would pass even if this anchor vanished entirely.
        $anchor = $this->federatedAnchor($html, $listing);

        $this->assertStringContainsString('rel="noopener"', $anchor);
        $this->assertStringNotContainsString('nofollow', $anchor);
    }

    /** The single <a> carrying this listing's click marker, so assertions can scope to it. */
    private function federatedAnchor(string $html, FederatedEvent $listing): string
    {
        $marker = 'data-federated-click="'.UrlUtils::encodeId($listing->id).'"';
        $start = strpos($html, $marker);

        $this->assertNotFalse($start, 'No federated anchor was rendered for the listing.');

        $open = strrpos(substr($html, 0, $start), '<a ');
        $this->assertNotFalse($open, 'The click marker was not inside an anchor.');

        $end = strpos($html, '</a>', $open);
        $this->assertNotFalse($end, 'The federated anchor was never closed.');

        return substr($html, $open, $end - $open);
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

        // A local event, so the ItemList is actually emitted. Without one the block is
        // absent and this would assert nothing - which is how the original version of
        // this test passed unconditionally.
        $this->makeLocalDiscoverableEvent();

        $html = $this->get('/browse')->assertOk()->getContent();

        $json = $this->itemListJson($html);

        $this->assertStringContainsString('ItemList', $json);
        $this->assertStringNotContainsString('operator.test', $json);
    }

    /**
     * The exact contents of the ItemList ld+json element.
     *
     * /browse carries more than one ld+json block (the layout emits its own), so this
     * picks the ItemList specifically. It also reads to the real closing tag instead of
     * windowing a fixed number of bytes, and fails loudly when the block is missing
     * rather than skipping the assertion - which is how the original version of this
     * test passed unconditionally.
     */
    private function itemListJson(string $html): string
    {
        preg_match_all(
            '#<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>#s',
            $html,
            $matches
        );

        foreach ($matches[1] as $block) {
            if (str_contains($block, 'ItemList')) {
                return $block;
            }
        }

        $this->fail('The /browse ItemList structured data was not rendered.');
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
