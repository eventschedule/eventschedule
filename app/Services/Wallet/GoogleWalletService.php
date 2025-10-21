<?php

namespace App\Services\Wallet;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleWalletService
{
    protected bool $enabled;
    protected ?string $issuerId;
    protected string $issuerName;
    protected string $classSuffix;
    protected ?string $serviceAccountJson;
    protected ?string $serviceAccountJsonPath;

    public function __construct()
    {
        $config = config('wallet.google');

        $this->enabled = (bool) ($config['enabled'] ?? false);
        $this->issuerId = $config['issuer_id'] ?? null;
        $this->issuerName = $config['issuer_name'] ?? config('app.name');
        $this->classSuffix = $config['class_suffix'] ?? 'event';
        $this->serviceAccountJson = $config['service_account_json'] ?? null;
        $this->serviceAccountJsonPath = $config['service_account_json_path'] ?? null;
    }

    public function isConfigured(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (! $this->issuerId) {
            return false;
        }

        if ($this->serviceAccountJsonPath && file_exists($this->serviceAccountJsonPath)) {
            return true;
        }

        return (bool) $this->serviceAccountJson;
    }

    public function isAvailableForSale(Sale $sale): bool
    {
        return $this->isConfigured() && $sale->status === 'paid';
    }

    public function createSaveLink(Sale $sale): string
    {
        if (! $this->isAvailableForSale($sale)) {
            throw new RuntimeException('Google Wallet is not configured for this sale.');
        }

        $sale->loadMissing('event.creatorRole', 'event.venue', 'saleTickets.ticket');

        if (! $sale->event) {
            throw new RuntimeException('Sale event is not available.');
        }

        $credentials = $this->resolveServiceAccountCredentials();
        $clientEmail = $credentials['client_email'] ?? null;

        if (! $clientEmail) {
            throw new RuntimeException('Google Wallet credentials are missing the client email.');
        }

        $payload = $this->buildPayload($sale);

        $claims = [
            'iss' => $clientEmail,
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => time(),
            'exp' => time() + 3600,
            'payload' => $payload,
        ];

        $client = new GoogleClient();
        $client->setAuthConfig($credentials);
        $client->setScopes(['https://www.googleapis.com/auth/wallet_object.issuer']);

        $jwt = $client->signJwt($claims);

        return 'https://pay.google.com/gp/v/save/' . $jwt;
    }

    protected function resolveServiceAccountCredentials(): array
    {
        if ($this->serviceAccountJsonPath && file_exists($this->serviceAccountJsonPath)) {
            $contents = file_get_contents($this->serviceAccountJsonPath);

            if ($contents !== false) {
                $decoded = json_decode($contents, true);

                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        if ($this->serviceAccountJson) {
            $json = $this->serviceAccountJson;
            $trimmed = trim($json);

            if (! str_starts_with($trimmed, '{')) {
                $decoded = base64_decode($json, true);

                if ($decoded !== false) {
                    $json = $decoded;
                }
            }

            $decodedJson = json_decode($json, true);

            if (is_array($decodedJson)) {
                return $decodedJson;
            }
        }

        throw new RuntimeException('Google Wallet service account credentials are invalid.');
    }

    protected function buildPayload(Sale $sale): array
    {
        $event = $sale->event;
        $classId = $this->resolveClassId($event);
        $objectId = $this->resolveObjectId($sale);
        $ticketSummary = $sale->saleTickets->map(function ($saleTicket) {
            $name = $saleTicket->ticket->type ?: __('messages.ticket');

            return trim($name) . ' x ' . $saleTicket->quantity;
        })->implode(', ');

        $start = $this->resolveEventStart($event, $sale->event_date);
        $end = $this->resolveEventEnd($event, $start);
        $timezone = $this->resolveTimezone($event);
        $locations = $this->resolveLocations($event);
        $ticketNotes = $event->ticket_notes_html ? strip_tags($event->ticket_notes_html) : null;

        $classPayload = [
            'id' => $classId,
            'issuerName' => $this->issuerName,
            'reviewStatus' => 'UNDER_REVIEW',
            'eventName' => [
                'defaultValue' => [
                    'language' => $event->getLanguageCode() ?? 'en-US',
                    'value' => $event->name,
                ],
            ],
        ];

        $venue = $this->resolveVenueDetails($event);

        if ($venue) {
            $classPayload['venue'] = $venue;
        }

        if ($logoUrl = $this->resolveImageUrl($event)) {
            $classPayload['logo'] = [
                'sourceUri' => ['uri' => $logoUrl],
            ];
        }

        $objectPayload = [
            'id' => $objectId,
            'classId' => $classId,
            'state' => 'ACTIVE',
            'barcode' => [
                'type' => 'QR_CODE',
                'value' => $sale->secret,
            ],
            'ticketHolderName' => $this->resolveTicketHolder($sale),
            'ticketNumber' => (string) $sale->id,
            'eventStartDateTime' => [
                'dateTime' => $this->formatDateTime($start),
                'timeZone' => $timezone,
            ],
            'eventEndDateTime' => [
                'dateTime' => $this->formatDateTime($end),
                'timeZone' => $timezone,
            ],
            'linksModuleData' => [
                'uris' => [
                    [
                        'uri' => route('ticket.view', [
                            'event_id' => $event->hashedId(),
                            'secret' => $sale->secret,
                        ]),
                        'description' => __('messages.view_tickets'),
                    ],
                ],
            ],
            'textModulesData' => array_values(array_filter([
                [
                    'header' => __('messages.tickets'),
                    'body' => $ticketSummary ?: (string) $sale->quantity(),
                ],
                $ticketNotes ? [
                    'header' => __('messages.notes'),
                    'body' => $ticketNotes,
                ] : null,
            ])),
        ];

        if ($locations) {
            $objectPayload['locations'] = $locations;
        }

        if ($heroImage = $this->resolveImageUrl($event)) {
            $objectPayload['imageModulesData'] = [
                [
                    'mainImage' => [
                        'sourceUri' => ['uri' => $heroImage],
                    ],
                ],
            ];
        }

        return [
            'eventTicketClasses' => [$classPayload],
            'eventTicketObjects' => [$objectPayload],
        ];
    }

    protected function resolveClassId(Event $event): string
    {
        $suffix = $this->classSuffix ?: 'event';
        $eventSlug = Str::slug($event->name, '-');

        if ($eventSlug === '') {
            $eventSlug = 'event-' . $event->id;
        }

        $eventSlug = substr($eventSlug, 0, 64);
        $suffix = substr($suffix, 0, 32);

        return $this->issuerId . '.' . $suffix . '-' . $eventSlug;
    }

    protected function resolveObjectId(Sale $sale): string
    {
        $suffix = 'sale-' . $sale->id . '-' . substr($sale->secret, 0, 6);

        return $this->issuerId . '.' . $suffix;
    }

    protected function resolveEventStart(Event $event, ?string $eventDate): Carbon
    {
        $startsAt = $event->getStartDateTime($eventDate);

        if (! $startsAt) {
            throw new \RuntimeException('Cannot build wallet pass for event without a start time.');
        }

        $timezone = $this->resolveTimezone($event);

        return $startsAt->clone()->setTimezone($timezone);
    }

    protected function resolveEventEnd(Event $event, Carbon $start): Carbon
    {
        $end = $start->clone();
        $duration = $event->duration > 0 ? $event->duration : 2;

        return $end->addHours($duration);
    }

    protected function resolveTimezone(Event $event): string
    {
        return $event->venue?->timezone
            ?? $event->creatorRole?->timezone
            ?? config('app.timezone', 'UTC');
    }

    protected function formatDateTime(Carbon $dateTime): string
    {
        return $dateTime->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
    }

    protected function resolveTicketHolder(Sale $sale): string
    {
        if ($sale->name) {
            return $sale->name;
        }

        if ($sale->email) {
            return $sale->email;
        }

        return __('messages.attendee');
    }

    protected function resolveImageUrl(Event $event): ?string
    {
        return $event->getImageUrl();
    }

    protected function resolveVenueDetails(Event $event): ?array
    {
        $role = $event->venue instanceof Role ? $event->venue : null;

        if (! $role) {
            return null;
        }

        $venueName = $role->name;
        $venueAddress = $role->fullAddress();

        return [
            'name' => [
                'defaultValue' => [
                    'language' => $event->getLanguageCode() ?? 'en-US',
                    'value' => $venueName,
                ],
            ],
            'address' => [
                'defaultValue' => [
                    'language' => $event->getLanguageCode() ?? 'en-US',
                    'value' => $venueAddress,
                ],
            ],
        ];
    }

    protected function resolveLocations(Event $event): array
    {
        $locations = [];

        $role = $event->venue instanceof Role ? $event->venue : $event->creatorRole;

        if ($role && $role->geo_lat && $role->geo_lon) {
            $locations[] = [
                'latitude' => (float) $role->geo_lat,
                'longitude' => (float) $role->geo_lon,
            ];
        }

        return $locations;
    }
}
