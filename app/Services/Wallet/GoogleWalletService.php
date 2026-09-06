<?php

namespace App\Services\Wallet;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Builds "Add to Google Wallet" save links for a ticket.
 *
 * Opt-in and off by default: with GOOGLE_WALLET_ISSUER_ID / GOOGLE_WALLET_SERVICE_ACCOUNT unset,
 * isConfigured() is false, no button is rendered anywhere and nothing is ever sent to Google.
 * Same shape as OneSignalService.
 *
 * THE BARCODE IS THE WHOLE FEATURE. The pass carries a QR whose value is byte-identical to what
 * TicketController::qrCode() encodes - canonical_url(route('ticket.view', ...)) - because the door
 * scanner (resources/views/ticket/scan.blade.php) matches that URL with
 * /\/ticket\/view\/([^/]+)\/([^/]+)\/?$/ and POSTs the two captured segments. Change the value and
 * every wallet pass silently stops scanning while the on-page QR keeps working.
 *
 * WHY THE CLASS IS CREATED OVER REST AND THE OBJECT IS NOT. Google truncates a save link past 1800
 * characters. Class plus object inline measures about 2100 at realistic field lengths, because
 * every human string is wrapped in a LocalizedString and the RS256 signature alone is 342. So the
 * class - one per occurrence, carrying the branding, the venue and the date - is inserted once over
 * REST and cached, and only the object rides inside the JWT. That lands around 1280.
 *
 * The IDs are deterministic rather than stored, which is what keeps the snapshot decision
 * reversible: a later "expire the pass when the sale is cancelled" upgrade is a PATCH by ID and
 * needs no column. Note Google can never DELETE a class or an object, only expire one, which is
 * also why GOOGLE_WALLET_ID_PREFIX exists - a staging install sharing an issuer account with
 * production and reusing its prefix would collide permanently.
 *
 * Deliberately no Google SDK, though google/apiclient is vendored: driving both the token exchange
 * and the class insert through Laravel's Http facade is what makes the whole thing Http::fake()-able.
 * google/auth drives Guzzle directly and cannot be faked that way.
 */
class GoogleWalletService
{
    const SAVE_URL = 'https://pay.google.com/gp/v/save/';

    const API_BASE = 'https://walletobjects.googleapis.com/walletobjects/v1';

    const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    const SCOPE = 'https://www.googleapis.com/auth/wallet_object.issuer';

    /**
     * Google truncates the save link past this, so a longer JWT is a pass that silently fails to
     * save. buildJwt() sheds optional fields rather than hand one back.
     */
    const MAX_JWT_LENGTH = 1800;

    /**
     * The languages we shipped a badge for, under public/images/wallet/google. Kept as a list so
     * the blade never stats the filesystem on render; anything else falls back to English, which
     * is Google's own guidance.
     */
    const BADGE_LOCALES = ['ar', 'de', 'en', 'es', 'et', 'fr', 'he', 'it', 'nl', 'pt', 'ro', 'ru'];

    public static function isConfigured(): bool
    {
        return ! empty(config('services.google.wallet_issuer_id'))
            && self::serviceAccount() !== null;
    }

    /**
     * Whether this particular sale may be offered as a wallet pass.
     *
     * One predicate for the button on the ticket page, the badge in the email, the badge on the
     * order page and the route itself, so the four cannot drift.
     *
     * is_cancelled is not redundant with the status check: a cancelled EVENT leaves its sales
     * `paid`, which is the trap ticket/order.blade.php already documents ("the buyer saw a live
     * ticket with a working code for an event that is not happening"). It matters more here than
     * on a web page, because a wallet pass then sits on the phone indefinitely.
     */
    public static function canOffer(?Sale $sale, ?Event $event): bool
    {
        if (! $sale || ! $event || ! self::isConfigured()) {
            return false;
        }

        return $sale->status === 'paid'
            && ! $sale->is_deleted
            && ! $event->is_cancelled
            && ! $sale->isInstallmentDelinquent();
    }

    /**
     * The badge file basename for a locale, falling back to English.
     */
    public static function badgeLocale(?string $locale = null): string
    {
        $locale = strtolower(substr((string) ($locale ?: app()->getLocale()), 0, 2));

        return in_array($locale, self::BADGE_LOCALES, true) ? $locale : 'en';
    }

    /**
     * The full "Add to Google Wallet" link, or null when one cannot be built.
     *
     * Never throws: a buyer tapping a badge must not meet a stack trace, and an install whose
     * credentials have gone stale should quietly stop offering passes rather than 500.
     */
    public function saveUrl(Sale $sale, Event $event, ?Role $role = null): ?string
    {
        if (! self::canOffer($sale, $event)) {
            return null;
        }

        try {
            $role = $role ?: ($event->getRoleWithEmailSettings() ?? $event->roles->first());

            $classId = $this->ensureClass($sale, $event, $role);

            if (! $classId) {
                return null;
            }

            $jwt = $this->buildJwt($sale, $event, $classId, $role);

            return $jwt ? self::SAVE_URL.$jwt : null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /*
     * ------------------------------------------------------------------ credentials
     */

    /**
     * The decoded service-account key, or null when it is absent or unusable.
     *
     * Accepts either an absolute path to the JSON file (the selfhost shape) or the base64-encoded
     * JSON itself, because hosted production config is the DigitalOcean app spec and it has no
     * writable file mount to point a path at.
     */
    public static function serviceAccount(): ?array
    {
        $raw = trim((string) config('services.google.wallet_service_account'));

        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, '/') || str_starts_with($raw, './')) {
            if (! is_readable($raw)) {
                return null;
            }

            $raw = (string) file_get_contents($raw);
        } elseif (! str_starts_with($raw, '{')) {
            // base64_decode(strict) so a truncated or line-wrapped paste fails here rather than
            // producing bytes that json_decode reports as some unrelated syntax error.
            $decoded = base64_decode($raw, true);
            $raw = $decoded === false ? '' : $decoded;
        }

        $data = json_decode($raw, true);

        if (! is_array($data) || empty($data['client_email']) || empty($data['private_key'])) {
            return null;
        }

        return $data;
    }

    /**
     * An OAuth access token for the walletobjects API.
     *
     * Cached just under Google's own hour so a busy door never pays for the exchange twice, and
     * keyed on the account so rotating the credential does not serve the old token.
     */
    protected function accessToken(): ?string
    {
        $account = self::serviceAccount();

        if (! $account) {
            return null;
        }

        $key = 'gwallet.token.'.substr(hash('sha256', $account['client_email'].'|'.$account['private_key']), 0, 32);

        return Cache::remember($key, now()->addMinutes(55), function () use ($account) {
            $now = time();

            $assertion = $this->signJwt([
                'iss' => $account['client_email'],
                'scope' => self::SCOPE,
                'aud' => self::TOKEN_URL,
                'iat' => $now,
                'exp' => $now + 3600,
            ], $account['private_key']);

            $response = Http::asForm()->timeout(15)->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if (! $response->successful() || ! $response->json('access_token')) {
                Log::error('Google Wallet token exchange failed', [
                    'status' => $response->status(),
                    'body' => Str::limit((string) $response->body(), 500),
                ]);

                // Returned rather than thrown so Cache::remember stores nothing and the next tap
                // retries; a null here is what makes saveUrl() bail cleanly.
                return null;
            }

            return (string) $response->json('access_token');
        });
    }

    /*
     * ------------------------------------------------------------------ the class
     */

    /**
     * Make sure the pass class exists, and return its id.
     *
     * Success is cached for a day and failure for five minutes: a misconfigured install should
     * stop offering passes without hammering Google on every tap, but should recover on its own
     * once the operator fixes the credentials.
     */
    protected function ensureClass(Sale $sale, Event $event, ?Role $role): ?string
    {
        $classId = $this->classId($sale, $event);
        $cacheKey = 'gwallet.class.'.md5($classId);

        if (Cache::get($cacheKey) === true) {
            return $classId;
        }

        if (Cache::get($cacheKey) === false) {
            return null;
        }

        $token = $this->accessToken();

        if (! $token) {
            Cache::put($cacheKey, false, now()->addMinutes(5));

            return null;
        }

        $existing = Http::withToken($token)->timeout(15)
            ->get(self::API_BASE.'/eventTicketClass/'.$classId);

        if ($existing->successful()) {
            Cache::put($cacheKey, true, now()->addDay());

            return $classId;
        }

        if ($existing->status() !== 404) {
            Log::error('Google Wallet class lookup failed', [
                'class_id' => $classId,
                'status' => $existing->status(),
                'body' => Str::limit((string) $existing->body(), 500),
            ]);
            Cache::put($cacheKey, false, now()->addMinutes(5));

            return null;
        }

        $created = Http::withToken($token)->timeout(15)
            ->post(self::API_BASE.'/eventTicketClass', $this->classPayload($sale, $event, $role, $classId));

        // 409 means another request won the race, which is a success for our purposes.
        if (! $created->successful() && $created->status() !== 409) {
            Log::error('Google Wallet class insert failed', [
                'class_id' => $classId,
                'status' => $created->status(),
                'body' => Str::limit((string) $created->body(), 500),
            ]);
            Cache::put($cacheKey, false, now()->addMinutes(5));

            return null;
        }

        Cache::put($cacheKey, true, now()->addDay());

        return $classId;
    }

    protected function classPayload(Sale $sale, Event $event, ?Role $role, string $classId): array
    {
        $language = $this->language($role);
        $venue = $event->venue;

        $payload = [
            'id' => $classId,
            'issuerName' => $this->clamp($role?->translatedName() ?: config('app.name'), 40),
            'eventName' => $this->localized($event->name, $language),
            // UNDER_REVIEW, not DRAFT: a draft class can only be used by accounts registered as
            // test accounts on the issuer, so every real buyer would silently fail to save.
            'reviewStatus' => 'UNDER_REVIEW',
        ];

        if ($venue) {
            $venuePayload = [];

            if ($venue->translatedName()) {
                $venuePayload['name'] = $this->localized($this->clamp($venue->translatedName(), 120), $language);
            }

            if ($venue->bestAddress()) {
                $venuePayload['address'] = $this->localized($this->clamp($venue->bestAddress(), 200), $language);
            }

            // Google rejects a venue carrying only one of the two.
            if (count($venuePayload) === 2) {
                $payload['venue'] = $venuePayload;
            }
        }

        // A season pass covers a whole series, so pinning its class to one night would date-stamp
        // it with whichever occurrence the holder happened to buy on. Its validity lives on the
        // object's validTimeInterval instead.
        if (! $sale->isPass() && ($dateTime = $this->classDateTime($sale, $event))) {
            $payload['dateTime'] = $dateTime;
        }

        if ($homepage = $event->getGuestUrl($sale->subdomain, $sale->event_date)) {
            $payload['homepageUri'] = ['uri' => $homepage, 'description' => $this->clamp($event->name, 60)];
        }

        if ($color = $this->hexColor($role)) {
            $payload['hexBackgroundColor'] = $color;
        }

        // Google fetches these from us, so they are only worth sending when it can actually reach
        // the install. On a LAN or http selfhost they would render as broken art on the pass.
        if ($this->imagesAreFetchable()) {
            $logo = $role?->profile_image_url ?: asset('images/logo.png');

            if ($logo) {
                $payload['logo'] = $this->image($logo);
            }

            if ($hero = $event->getImageUrl()) {
                $payload['heroImage'] = $this->image($hero);
            }
        }

        return $payload;
    }

    /**
     * The occurrence's start and end for the class, in the SCHEDULE's timezone.
     *
     * Never the viewer's: an occurrence happens where it happens. Two shapes have no reliable
     * instant and get a date-only value rather than a bogus midnight-UTC one - a days_of_week
     * event with no starts_at, and an all-day event whose starts_at is date-only. Same pair
     * Ticket::passCancelDeadlineUtc() guards against.
     */
    protected function classDateTime(Sale $sale, Event $event): ?array
    {
        if (! $event->starts_at) {
            return null;
        }

        $timezone = $event->scheduleTimezone();

        if (strlen((string) $event->starts_at) === 10) {
            $date = Event::isOccurrenceDate($sale->event_date)
                ? $sale->event_date
                : substr((string) $event->starts_at, 0, 10);

            return ['start' => $date];
        }

        $start = $event->occurrenceStartUtc($sale->event_date)->setTimezone($timezone);
        $minutes = $event->durationInMinutes();

        $dateTime = ['start' => $start->toIso8601String()];

        if ($minutes > 0) {
            $dateTime['end'] = $start->copy()->addMinutes($minutes)->toIso8601String();
        }

        return $dateTime;
    }

    /*
     * ------------------------------------------------------------------ the object + JWT
     */

    /**
     * Sign the save JWT, shedding optional fields until it fits inside Google's limit.
     *
     * The order matters: seat labels and the door note are the two fields with no ceiling worth
     * trusting (an allocated party can hold twenty seats), so they go first, and the identity
     * fields the attendee is actually shown at the door go last.
     */
    protected function buildJwt(Sale $sale, Event $event, string $classId, ?Role $role): ?string
    {
        $account = self::serviceAccount();

        if (! $account) {
            return null;
        }

        // Built once and then trimmed, not rebuilt per attempt: objectPayload() re-parses the
        // event's ticket notes and re-resolves the schedule, and nothing it produces changes
        // between attempts.
        $full = $this->objectPayload($sale, $event, $classId, $role);

        $shed = [[], ['textModulesData'], ['textModulesData', 'seatInfo'], ['textModulesData', 'seatInfo', 'ticketType']];

        foreach ($shed as $drop) {
            $object = $full;

            foreach ($drop as $field) {
                unset($object[$field]);
            }

            $jwt = $this->signJwt([
                'iss' => $account['client_email'],
                'aud' => 'google',
                'typ' => 'savetowallet',
                'iat' => time(),
                'origins' => $this->origins(),
                'payload' => ['eventTicketObjects' => [$object]],
            ], $account['private_key']);

            if (strlen($jwt) <= self::MAX_JWT_LENGTH) {
                return $jwt;
            }
        }

        // Nothing left to shed. Better no button than a link the browser truncates into a save
        // that fails with no explanation.
        Log::warning('Google Wallet JWT exceeds the safe length even after shedding optional fields', [
            'sale_id' => $sale->id,
            'event_id' => $event->id,
        ]);

        return null;
    }

    protected function objectPayload(Sale $sale, Event $event, string $classId, ?Role $role): array
    {
        $language = $this->language($role);

        $object = [
            'id' => $this->objectId($sale),
            'classId' => $classId,
            'state' => 'ACTIVE',
            'barcode' => [
                'type' => 'QR_CODE',
                // Byte-identical to TicketController::qrCode(). See the class docblock.
                'value' => canonical_url(route('ticket.view', [
                    'event_id' => UrlUtils::encodeId($event->id),
                    'secret' => $sale->secret,
                ], false)),
            ],
            'ticketNumber' => UrlUtils::encodeId($sale->id),
        ];

        if ($sale->name) {
            $object['ticketHolderName'] = $this->clamp($sale->name, 80);
        }

        $sale->loadMissing('saleTickets.ticket');
        $saleTicket = $sale->saleTickets->first(fn ($row) => $row->ticket && ! $row->ticket->is_addon);

        if ($saleTicket?->ticket?->type) {
            $object['ticketType'] = $this->localized($this->clamp($saleTicket->ticket->type, 60), $language);
        }

        if ($saleTicket && ($seats = $saleTicket->seatLabels())) {
            $object['seatInfo'] = ['seat' => $this->localized($this->clamp(implode(', ', $seats), 60), $language)];
        }

        if ($interval = $this->validTimeInterval($sale, $event, $saleTicket)) {
            $object['validTimeInterval'] = $interval;
        }

        // The reason a wallet pass beats a link: with coordinates Google surfaces the pass on the
        // holder's lock screen when they arrive at the venue.
        $venue = $event->venue;

        if ($venue && $venue->geo_lat && $venue->geo_lon) {
            $object['locations'] = [[
                'latitude' => (float) $venue->geo_lat,
                'longitude' => (float) $venue->geo_lon,
            ]];
        }

        if ($rows = $this->textModules($sale, $event, $saleTicket)) {
            $object['textModulesData'] = $rows;
        }

        return $object;
    }

    /**
     * Extra rows on the pass, so the attendee reads the door information without opening anything.
     */
    protected function textModules(Sale $sale, Event $event, $saleTicket): array
    {
        $rows = [];

        $admits = $saleTicket?->ticket?->is_pass
            ? $saleTicket->ticket->admitsPerEvent()
            : ($sale->isRsvp() ? 1 : $sale->legTotalQuantity());

        if ($admits > 1) {
            $rows[] = [
                'id' => 'admits',
                'header' => __('messages.guests'),
                'body' => (string) $admits,
            ];
        }

        // The plain-text twin, not parsedTicketNotesHtml(): a wallet pass renders no markup.
        $notes = $event->parsedTicketNotesText($sale->event_date);

        if ($notes && trim($notes) !== '') {
            $rows[] = [
                'id' => 'notes',
                'header' => __('messages.important_information'),
                'body' => $this->clamp(trim($notes), 200),
            ];
        }

        return $rows;
    }

    /**
     * When the pass stops being current. Google archives it afterwards, which is what stops a
     * snapshot pass looking live forever once the event is over.
     */
    protected function validTimeInterval(Sale $sale, Event $event, $saleTicket): ?array
    {
        if ($sale->isPass()) {
            $expires = $saleTicket?->pass_expires_at;

            return $expires ? ['end' => ['date' => $expires->toIso8601String()]] : null;
        }

        if (! $event->starts_at || strlen((string) $event->starts_at) === 10) {
            return null;
        }

        $start = $event->occurrenceStartUtc($sale->event_date);
        $minutes = max($event->durationInMinutes(), 60);

        return [
            // Doors, roughly: the pass becomes current the morning of the event rather than at the
            // exact start, so an attendee arriving early still finds it at the top of their wallet.
            'start' => ['date' => $start->copy()->subHours(12)->toIso8601String()],
            'end' => ['date' => $start->copy()->addMinutes($minutes + 180)->toIso8601String()],
        ];
    }

    /*
     * ------------------------------------------------------------------ ids and helpers
     */

    public function classId(Sale $sale, Event $event): string
    {
        // Date-less for a season pass; see classPayload().
        $suffix = $sale->isPass()
            ? 'p'.$event->id
            : 'e'.$event->id.'-'.str_replace('-', '', (string) $sale->event_date);

        return $this->qualify($suffix);
    }

    public function objectId(Sale $sale): string
    {
        return $this->qualify('s'.$sale->id);
    }

    /**
     * Google accepts only alphanumerics, '.', '_' and '-' in the part after the issuer id.
     */
    protected function qualify(string $suffix): string
    {
        $prefix = preg_replace('/[^A-Za-z0-9_.-]/', '', (string) config('services.google.wallet_id_prefix')) ?: 'es';
        $suffix = preg_replace('/[^A-Za-z0-9_.-]/', '', $suffix);

        return config('services.google.wallet_issuer_id').'.'.$prefix.'-'.$suffix;
    }

    /**
     * RS256, base64url, no library. firebase/php-jwt is vendored but only transitively, via
     * google/auth, and this is ten lines.
     */
    protected function signJwt(array $claims, string $privateKey): string
    {
        $segments = [
            $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_UNESCAPED_SLASHES)),
            $this->base64Url(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
        ];

        $input = implode('.', $segments);
        $signature = '';

        if (! openssl_sign($input, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Google Wallet: unable to sign with the configured service-account key.');
        }

        return $input.'.'.$this->base64Url($signature);
    }

    protected function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * The origins claim. Built from the live request rather than APP_URL, because a buyer can be
     * on a schedule's custom domain when they tap the badge.
     */
    protected function origins(): array
    {
        $origins = [];

        $appUrl = (string) config('app.url');

        if (preg_match('#^https?://[^/]+#', $appUrl, $matches)) {
            $origins[] = $matches[0];
        }

        if (! app()->runningInConsole()) {
            $origins[] = request()->getSchemeAndHttpHost();
        }

        return array_values(array_unique(array_filter($origins)));
    }

    protected function localized(?string $value, string $language): array
    {
        return ['defaultValue' => ['language' => $language, 'value' => (string) $value]];
    }

    protected function image(string $uri): array
    {
        return ['sourceUri' => ['uri' => $uri]];
    }

    protected function language(?Role $role): string
    {
        return $role?->language_code ?: (string) config('app.locale');
    }

    protected function hexColor(?Role $role): ?string
    {
        $color = $role?->manifestThemeColor();

        return $color && preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? strtolower($color) : '#4e81fa';
    }

    protected function clamp(?string $value, int $length): string
    {
        return Str::limit(trim((string) $value), $length, '');
    }

    /**
     * Whether Google could fetch an image URL from this install. A hostname we cannot rule out is
     * treated as reachable; the point is to skip the obviously-unreachable local cases, not to
     * resolve DNS on every pass.
     */
    protected function imagesAreFetchable(): bool
    {
        $url = (string) config('app.url');

        if (! Str::startsWith(strtolower($url), 'https://')) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($host === '' || $host === 'localhost' || Str::endsWith($host, ['.local', '.test', '.localhost', '.internal'])) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) && UrlUtils::isBlockedIp($host)) {
            return false;
        }

        return true;
    }
}
