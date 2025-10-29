<?php

namespace Tests\Unit\Services\Wallet;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\SaleTicketEntry;
use App\Models\Ticket;
use App\Services\Wallet\GoogleWalletService;
use Carbon\Carbon;
use Tests\TestCase;

class GoogleWalletServiceTest extends TestCase
{
    public function testEntryPayloadShowsSingleAttendeeCount(): void
    {
        $service = new GoogleWalletServiceForTests();

        $event = new Event([
            'id' => 42,
            'name' => 'Sample Event',
            'starts_at' => Carbon::parse('2025-05-01 18:00:00'),
        ]);
        $event->setRelation('creatorRole', new Role(['timezone' => 'America/New_York']));
        $event->setRelation('roles', collect());

        $sale = new Sale([
            'id' => 1001,
            'secret' => 'sale-secret',
            'status' => 'paid',
            'event_date' => '2025-05-01',
            'name' => 'Taylor Attendee',
        ]);
        $sale->setRelation('event', $event);

        $ticket = new Ticket(['type' => 'General Admission']);

        $saleTicket = new SaleTicket(['quantity' => 2]);
        $saleTicket->setRelation('sale', $sale);
        $saleTicket->setRelation('ticket', $ticket);

        $entry = new SaleTicketEntry([
            'seat_number' => 2,
            'secret' => 'entry-secret',
        ]);
        $entry->setRelation('saleTicket', $saleTicket);

        $saleTicket->setRelation('entries', collect([$entry]));
        $sale->setRelation('saleTickets', collect([$saleTicket]));

        $payload = $service->exposeBuildPayload($sale, $entry);

        $textModules = collect($payload['eventTicketObjects'][0]['textModulesData']);
        $attendeeModule = $textModules->firstWhere('header', __('messages.number_of_attendees'));

        $this->assertNotNull($attendeeModule, 'Payload should include attendee count text module.');
        $this->assertSame('1', $attendeeModule['body']);
    }
}

class GoogleWalletServiceForTests extends GoogleWalletService
{
    public function __construct()
    {
        parent::__construct();

        $this->enabled = true;
        $this->issuerId = 'issuer-id';
        $this->issuerName = 'Test Issuer';
        $this->classSuffix = 'event';
        $this->serviceAccountJson = json_encode([
            'client_email' => 'wallet@example.com',
            'private_key' => '-----BEGIN PRIVATE KEY-----\\nMIIBVw==\\n-----END PRIVATE KEY-----',
        ]);
    }

    public function exposeBuildPayload(Sale $sale, SaleTicketEntry $entry): array
    {
        return $this->buildPayload($sale, $entry);
    }

    protected function resolveServiceAccountCredentials(): array
    {
        return [
            'client_email' => 'wallet@example.com',
            'private_key' => 'dummy',
        ];
    }
}
