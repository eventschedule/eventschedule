<?php

namespace Tests\Feature;

use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The ticket price box on the AP event form is <input type="number">, so whatever the
 * Vue model holds has to be a valid HTML floating-point number. Seeding it through
 * Intl.NumberFormat produced locale strings ("25,00", "1,500.00") that the browser's
 * value sanitization silently replaces with the empty string - and Vue re-asserts the
 * model on every re-render, so the field blanked again after any edit elsewhere in the
 * ticket settings. Saving then stored the ticket as free.
 *
 * PHPUnit cannot run the sanitizer, so this pins the seeding expression itself.
 */
class EventEditTicketPriceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function editFormHtml(string $locale): string
    {
        $owner = $this->createOwner();
        $owner->language_code = $locale;
        $owner->save();

        $role = $this->createRole($owner, 'talent', ['language_code' => $locale]);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $this->createTicket($event, ['type' => 'General', 'price' => 1500.5, 'quantity' => 100]);

        return $this->actingAs($owner)
            ->get(route('event.edit', [
                'subdomain' => $role->subdomain,
                'hash' => UrlUtils::encodeId($event->id),
            ]))
            ->assertOk()
            ->getContent();
    }

    /**
     * German is one of the nine shipped locales whose decimal separator is a comma, so
     * every price blanked there; English only blanked from 1000 up, via the grouping
     * separator. Both must now seed the raw value.
     */
    public function test_ticket_price_is_seeded_raw_not_locale_formatted(): void
    {
        foreach (['de', 'en'] as $locale) {
            $html = $this->editFormHtml($locale);

            $this->assertStringContainsString(
                'parseFloat(ticket.price)',
                $html,
                "the {$locale} event form should seed the ticket price as a raw number"
            );
            $this->assertStringNotContainsString(
                '.format(ticket.price)',
                $html,
                "the {$locale} event form must not run the ticket price through Intl.NumberFormat"
            );
        }
    }
}
