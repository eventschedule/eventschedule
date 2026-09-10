<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The event editor locks an event's currency once it has taken money, as the API already did.
 *
 * events.ticket_currency_code is the only record of what a sale was charged in - `sales` has no
 * currency column - so changing it afterwards relabels every past sale and scales a later refund
 * by the wrong currency. "Taken money" is Event::hasSettledMoney(): a paid, amount_mismatch or
 * refunded sale. Unpaid, cancelled and expired sales never moved anything, so they lock nothing.
 */
class EventCurrencyLockTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public static function settledStatuses(): array
    {
        return [
            'paid' => ['paid'],
            'refunded' => ['refunded'],
            'amount_mismatch' => ['amount_mismatch'],
        ];
    }

    public static function unsettledStatuses(): array
    {
        return [
            'unpaid' => ['unpaid'],
            'cancelled' => ['cancelled'],
            'expired' => ['expired'],
        ];
    }

    #[DataProvider('settledStatuses')]
    public function test_an_event_that_has_taken_money_refuses_a_new_currency(string $status): void
    {
        [$owner, $role, $event] = $this->usdEventWithSale($status);

        $this->putUpdateEvent($owner, $role, $event, ['ticket_currency_code' => 'EUR'])
            ->assertRedirect()
            ->assertSessionHasErrors(['ticket_currency_code' => __('messages.currency_locked_after_sales')]);

        $fresh = $event->fresh();
        $this->assertSame('USD', $fresh->ticket_currency_code);
        // Refused before saveEvent(), so nothing else in that post was saved either.
        $this->assertSame('Test Event', $fresh->name);
    }

    #[DataProvider('unsettledStatuses')]
    public function test_a_sale_that_moved_no_money_leaves_the_currency_open(string $status): void
    {
        [$owner, $role, $event] = $this->usdEventWithSale($status);

        $this->putUpdateEvent($owner, $role, $event, ['ticket_currency_code' => 'EUR'])
            ->assertSessionDoesntHaveErrors();

        $this->assertSame('EUR', $event->fresh()->ticket_currency_code);
    }

    public function test_reposting_or_omitting_the_currency_still_saves_once_it_is_locked(): void
    {
        [$owner, $role, $event] = $this->usdEventWithSale('paid');

        // The stored code sent back unchanged is not a change.
        $this->putUpdateEvent($owner, $role, $event, ['ticket_currency_code' => 'USD', 'name' => 'Same Currency'])
            ->assertSessionDoesntHaveErrors();
        $this->assertSame('Same Currency', $event->fresh()->name);

        // What the form sends once both selects are disabled: no currency at all. The save goes
        // through and keeps the stored code.
        $this->putUpdateEvent($owner, $role, $event, ['name' => 'No Currency'])
            ->assertSessionDoesntHaveErrors();

        $fresh = $event->fresh();
        $this->assertSame('No Currency', $fresh->name);
        $this->assertSame('USD', $fresh->ticket_currency_code);
    }

    public function test_the_editor_disables_both_currency_selects_once_the_event_has_taken_money(): void
    {
        [$owner, $role, $event] = $this->usdEventWithSale('paid');

        $response = $this->actingAs($owner)->get($this->editUrl($role, $event))->assertOk();

        $selects = $this->currencySelects($response->getContent());
        $this->assertCount(2, $selects, 'the editor carries two Currency selects: the external price and the tickets one');
        foreach ($selects as $select) {
            $this->assertMatchesRegularExpression('/\sdisabled(?=[\s>])/', $select);
        }

        $response->assertSee(__('messages.currency_locked_after_sales'));
    }

    public function test_the_editor_leaves_the_currency_open_until_money_is_taken(): void
    {
        [$owner, $role, $event] = $this->usdEventWithSale('unpaid');

        $response = $this->actingAs($owner)->get($this->editUrl($role, $event))->assertOk();

        $selects = $this->currencySelects($response->getContent());
        $this->assertCount(2, $selects);
        foreach ($selects as $select) {
            $this->assertDoesNotMatchRegularExpression('/\sdisabled(?=[\s>])/', $select);
        }

        $response->assertDontSee(__('messages.currency_locked_after_sales'));
    }

    /** @return array{0: User, 1: Role, 2: Event} */
    private function usdEventWithSale(string $status): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'cash',
            'ticket_currency_code' => 'USD',
        ]);
        $ticket = $this->createTicket($event, ['price' => 10]);
        $this->createSale($event, $role, ['status' => $status, 'payment_amount' => 10], $ticket);

        return [$owner, $role, $event];
    }

    private function editUrl(Role $role, Event $event): string
    {
        return route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]);
    }

    /** @return list<string> every opening <select> tag named ticket_currency_code */
    private function currencySelects(string $html): array
    {
        preg_match_all('/<select\b[^>]*\bname="ticket_currency_code"[^>]*>/', $html, $matches);

        return $matches[0];
    }
}
