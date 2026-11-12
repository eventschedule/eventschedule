<?php

namespace App\Services\Wallet;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Apple Wallet passes: a signed .pkpass file the buyer's iPhone or Mac opens into Wallet.
 *
 * OFF UNTIL CONFIGURED, completely: with no pass type id, team id and a signing certificate that
 * actually opens, isConfigured() is false, so no button is drawn, no email carries a link and the
 * route answers 404 (TicketController::appleWalletPass()). Nothing is ever sent to Apple: the pass
 * is a file, and it names no web service, so it is a snapshot like Google's (no live updates; the
 * pass's back links to the ticket page for anything that changed).
 *
 * The pass is built with nothing but PHP: a JSON description, a manifest of SHA-1 hashes, a
 * detached PKCS #7 signature over the manifest (DER) made with the Pass Type ID
 * certificate plus Apple's WWDR G4 intermediate (shipped in resources/wallet, public), all zipped.
 *
 * The barcode is byte-identical to TicketController::qrCode() and Google's, so the door scanner
 * reads a pass exactly as it reads the ticket page.
 */
class AppleWalletService
{
    /** Shipped with the repository: Apple's public WWDR G4 intermediate, valid to 2030-12-10. */
    public const WWDR_PATH = 'wallet/AppleWWDRCAG4.pem';

    /** @var array<string, array{cert: \OpenSSLCertificate, key: \OpenSSLAsymmetricKey, wwdr: string}|null> */
    private static array $credentials = [];

    public static function isConfigured(): bool
    {
        // Silent until the operator has started setting it up; from then on, what is missing is
        // said in the log (warn()), never on a page.
        if (self::passTypeId() === '' || self::teamId() === '') {
            return false;
        }

        if (! class_exists(\ZipArchive::class)) {
            self::warn('the zip extension is not available');

            return false;
        }

        if (! function_exists('openssl_pkcs7_sign')) {
            self::warn('the openssl extension is not available');

            return false;
        }

        return self::credentials() !== null;
    }

    /** Whether this sale may be offered as an Apple Wallet pass (the rules are WalletPasses::eligible(), shared with Google). */
    public static function canOffer(?Sale $sale, ?Event $event): bool
    {
        return self::isConfigured() && WalletPasses::eligible($sale, $event);
    }

    /**
     * Apple's own "Add to Apple Wallet" badge for the current language, when the operator has put
     * Apple's artwork in public/images/wallet/apple (it is licensed per developer account, so the
     * repository does not ship it); null draws a plain text button instead. Never a home-made badge:
     * Apple's guidelines allow only its own artwork.
     */
    public static function badge(string $extension = 'svg', ?string $locale = null): ?string
    {
        $locale = strtolower(substr((string) ($locale ?: app()->getLocale()), 0, 2));

        foreach (array_unique([$locale, 'en']) as $candidate) {
            $relative = 'images/wallet/apple/'.$candidate.'.'.$extension;
            if (is_file(public_path($relative))) {
                return $relative;
            }
        }

        return null;
    }

    /**
     * The .pkpass file for a sale, as bytes; null when it cannot be built (the route then sends the
     * buyer back to their ticket with a message, never a broken download).
     */
    public function pass(Sale $sale, Event $event, ?Role $role = null): ?string
    {
        $credentials = self::credentials();

        if (! $credentials) {
            return null;
        }

        $role ??= $event->getRoleWithEmailSettings() ?? $event->roles->first();
        $files = ['pass.json' => json_encode($this->passJson($sale, $event, $role), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)];
        $files += $this->images($role);

        // Wallet refuses a pass with no icon.png, silently, on the buyer's phone.
        if (! isset($files['icon.png'])) {
            self::warn('the pass icon could not be drawn');

            return null;
        }

        $manifest = [];
        foreach ($files as $name => $bytes) {
            $manifest[$name] = sha1($bytes);
        }
        $files['manifest.json'] = json_encode($manifest, JSON_UNESCAPED_SLASHES);

        $signature = $this->sign($files['manifest.json'], $credentials);

        if ($signature === null) {
            self::warn('the manifest could not be signed: '.self::opensslErrors());

            return null;
        }

        $files['signature'] = $signature;

        return $this->zip($files);
    }

    /** The pass's description (pass.json). Public for the tests. */
    public function passJson(Sale $sale, Event $event, ?Role $role): array
    {
        // The language the holder bought in (Sale::guestLanguage()), else the schedule's: its
        // labels, and the event's name in it where the event has one. Apple's pass is one file per
        // holder, so unlike Google's shared class it can carry exactly their language.
        $language = $sale->guestLanguage() ?? ($role?->language_code ?: config('app.locale'));
        $event = $sale->eventAsRead($event);
        $label = fn (string $key) => __('messages.'.$key, [], $language);
        $sale->loadMissing('saleTickets.ticket');

        // On a pass sale, the PASS row, as GoogleWalletService::objectPayload() picks it.
        $saleTicket = $sale->isPass()
            ? $sale->saleTickets->first(fn ($row) => $row->ticket?->is_pass)
            : $sale->saleTickets->first(fn ($row) => $row->ticket && ! $row->ticket->is_addon);

        $ticketUrl = canonical_url(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ], false));

        [$background, $foreground] = $this->colors($role);

        $pass = [
            'formatVersion' => 1,
            'passTypeIdentifier' => self::passTypeId(),
            'teamIdentifier' => self::teamId(),
            // The install's wallet prefix (GOOGLE_WALLET_ID_PREFIX, default "es"), shared by both
            // wallets so a staging install's passes never share a serial with production's.
            'serialNumber' => (config('services.google.wallet_id_prefix') ?: 'es').'-'.UrlUtils::encodeId($sale->id),
            'organizationName' => $this->clamp($role?->translatedName() ?: config('app.name'), 60),
            'description' => $this->clamp($event->name, 120),
            'logoText' => $this->clamp($role?->translatedName() ?: config('app.name'), 30),
            'backgroundColor' => $background,
            'foregroundColor' => $foreground,
            'labelColor' => $foreground,
            // Byte-identical to TicketController::qrCode(): the door scanner reads this.
            'barcodes' => [[
                'format' => 'PKBarcodeFormatQR',
                'message' => $ticketUrl,
                'messageEncoding' => 'iso-8859-1',
            ]],
            'eventTicket' => [
                'primaryFields' => [['key' => 'event', 'label' => $label('event'), 'value' => $this->clamp($event->name, 80)]],
                'secondaryFields' => [],
                'auxiliaryFields' => [],
                'backFields' => [],
            ],
        ];

        if (! $sale->isPass() && $event->starts_at && $event->hasDateOnlyStart()) {
            // An all-day event: the day alone, never a midnight converted between zones, and no
            // expiry, which would land on the day itself (Google's pass guards the same case).
            $day = Event::isOccurrenceDate($sale->event_date) ? $sale->event_date : (string) $event->starts_at;
            $pass['eventTicket']['secondaryFields'][] = [
                'key' => 'date',
                'label' => $label('date'),
                'value' => $day.'T00:00:00Z',
                'dateStyle' => 'PKDateStyleMedium',
                'timeStyle' => 'PKDateStyleNone',
                'ignoresTimeZone' => true,
            ];
        } elseif (! $sale->isPass() && $event->starts_at) {
            $start = $event->occurrenceStartUtc((string) $sale->event_date)->setTimezone($event->scheduleTimezone());

            // The event's own clock, whatever zone the phone is in (ignoresTimeZone), as every
            // date in the app is the schedule's.
            $pass['eventTicket']['secondaryFields'][] = [
                'key' => 'date',
                'label' => $label('date'),
                'value' => $start->toIso8601String(),
                'dateStyle' => 'PKDateStyleMedium',
                'timeStyle' => 'PKDateStyleShort',
                'ignoresTimeZone' => true,
            ];
            // Wallet raises the pass on the lock screen around this moment.
            $pass['relevantDate'] = $start->toIso8601String();
            // Greyed out once over, as Google's validTimeInterval archives it.
            $pass['expirationDate'] = $start->copy()->addMinutes(max($event->durationInMinutes(), 60) + 180)->toIso8601String();
        } elseif ($sale->isPass() && $saleTicket?->pass_expires_at) {
            $pass['expirationDate'] = $saleTicket->pass_expires_at->toIso8601String();
        }

        if ($venue = $event->venue) {
            if ($name = trim((string) $venue->translatedName())) {
                $pass['eventTicket']['secondaryFields'][] = ['key' => 'venue', 'label' => $label('venue'), 'value' => $this->clamp($name, 60)];
            }
            if ($address = trim((string) $venue->bestAddress())) {
                $pass['eventTicket']['backFields'][] = ['key' => 'address', 'label' => $label('address'), 'value' => $this->clamp($address, 200)];
            }
        }

        if ($saleTicket?->ticket?->type) {
            $pass['eventTicket']['auxiliaryFields'][] = ['key' => 'type', 'label' => $label('ticket'), 'value' => $this->clamp($saleTicket->ticket->type, 60)];
        }

        if ($sale->name) {
            $pass['eventTicket']['auxiliaryFields'][] = ['key' => 'holder', 'label' => $label('attendee'), 'value' => $this->clamp($sale->name, 60)];
        }

        $admits = $saleTicket?->ticket?->is_pass
            ? $saleTicket->ticket->admitsPerEvent()
            : ($sale->isRsvp() ? 1 : $sale->legTotalQuantity());

        if ($admits > 1) {
            $pass['eventTicket']['auxiliaryFields'][] = ['key' => 'admits', 'label' => $label('guests'), 'value' => (string) $admits];
        }

        if ($saleTicket && ($seats = $saleTicket->seatLabels())) {
            $pass['eventTicket']['backFields'][] = ['key' => 'seats', 'label' => $label('wallet_seats'), 'value' => $this->clamp(implode(', ', $seats), 200)];
        }

        $pass['eventTicket']['backFields'][] = ['key' => 'number', 'label' => $label('wallet_ticket_number'), 'value' => UrlUtils::encodeId($sale->id)];

        // The plain-text notes, as on Google's pass: a pass renders no markup.
        $notes = $event->parsedTicketNotesText($sale->event_date, $role);
        if ($notes && trim($notes) !== '') {
            $pass['eventTicket']['backFields'][] = ['key' => 'notes', 'label' => $label('important_information'), 'value' => $this->clamp(trim($notes), 1000)];
        }

        // A snapshot is never updated, so the back points at the live ticket, opened in the
        // holder's language. The barcode above keeps the bare address the door reads.
        $backUrl = Sale::withLanguage($ticketUrl, $sale->linkLanguage($role, $event));
        $pass['eventTicket']['backFields'][] = [
            'key' => 'ticket',
            'label' => $label('view_ticket'),
            'value' => $backUrl,
            // Only <a> is allowed here; quotes in a label stay quotes (e() would print &#039;).
            'attributedValue' => '<a href="'.htmlspecialchars($backUrl, ENT_QUOTES).'">'.htmlspecialchars($label('view_ticket'), ENT_NOQUOTES).'</a>',
        ];

        return $pass;
    }

    /*
     * ------------------------------------------------------------------ configuration
     */

    protected static function passTypeId(): string
    {
        return trim((string) config('services.apple.wallet_pass_type_id'));
    }

    protected static function teamId(): string
    {
        return trim((string) config('services.apple.wallet_team_id'));
    }

    /**
     * The signing certificate, its private key and the WWDR intermediate, opened once per process
     * (isConfigured() runs on every ticket page and email). The certificate is a path or base64 of
     * a .p12, or a PEM file holding the certificate and the key: a .p12 exported from Keychain may
     * need OpenSSL 3's legacy provider, which a PEM sidesteps (docs/APPLE_WALLET_SETUP.md). Never
     * throws: unreadable credentials are "not configured".
     */
    protected static function credentials(): ?array
    {
        $source = trim((string) config('services.apple.wallet_certificate'));
        $password = (string) config('services.apple.wallet_certificate_password');
        $wwdrSource = trim((string) config('services.apple.wallet_wwdr'));
        $memo = sha1($source."\0".$password."\0".$wwdrSource);

        if (array_key_exists($memo, self::$credentials)) {
            return self::$credentials[$memo];
        }

        try {
            return self::$credentials[$memo] = self::openCredentials($source, $password, $wwdrSource);
        } catch (\Throwable $e) {
            self::warn('the credentials could not be read: '.$e->getMessage());

            return self::$credentials[$memo] = null;
        }
    }

    /**
     * Tell the operator, once an hour, why Apple Wallet is off or a pass failed: otherwise a wrong
     * password, a legacy .p12 or an expired certificate is a button that silently never appears.
     * Never the secret itself.
     */
    private static function warn(string $why): void
    {
        try {
            if (Cache::add('apple_wallet_warned:'.sha1($why), true, now()->addHour())) {
                Log::warning('Apple Wallet: '.$why);
            }
        } catch (\Throwable) {
        }
    }

    private static function opensslErrors(): string
    {
        $errors = [];
        while (($error = openssl_error_string()) !== false && count($errors) < 5) {
            $errors[] = $error;
        }

        return implode('; ', $errors);
    }

    private static function openCredentials(string $source, string $password, string $wwdrSource): ?array
    {
        // Reached only once the pass type id and team id are set, so an empty certificate here is
        // a setup that is half done, and says so like every other mistake below.
        if ($source === '') {
            self::warn('APPLE_WALLET_CERTIFICATE is not set');

            return null;
        }

        if (! function_exists('openssl_pkcs12_read')) {
            self::warn('the openssl extension is not available');

            return null;
        }

        $bytes = self::readSource($source);
        $wwdr = $wwdrSource !== '' ? self::readSource($wwdrSource) : @file_get_contents(resource_path(self::WWDR_PATH));

        if (! $bytes) {
            self::warn('APPLE_WALLET_CERTIFICATE is neither a readable absolute path nor base64');

            return null;
        }

        if (! $wwdr || ! str_contains($wwdr, 'BEGIN CERTIFICATE')) {
            self::warn('the WWDR intermediate certificate could not be read');

            return null;
        }

        $cert = null;
        $key = null;

        if (str_contains($bytes, 'BEGIN CERTIFICATE')) {
            $cert = @openssl_x509_read($bytes) ?: null;
            $key = @openssl_pkey_get_private($bytes, $password) ?: null;
        } elseif (@openssl_pkcs12_read($bytes, $bundle, $password)) {
            $cert = @openssl_x509_read($bundle['cert'] ?? '') ?: null;
            $key = @openssl_pkey_get_private($bundle['pkey'] ?? '') ?: null;
        }

        if (! $cert || ! $key || ! @openssl_x509_check_private_key($cert, $key)) {
            self::warn('the certificate or its key did not open (a wrong password, a .p12 that needs OpenSSL\'s legacy provider, or a key that is not this certificate\'s): '.self::opensslErrors());

            return null;
        }

        // A Pass Type ID certificate expires; a pass signed with an expired one is refused by every
        // iPhone, so an expired certificate is "not configured".
        $validTo = (int) (@openssl_x509_parse($cert)['validTo_time_t'] ?? 0);
        if ($validTo > 0 && $validTo < time()) {
            self::warn('the Pass Type ID certificate has expired');

            return null;
        }

        return ['cert' => $cert, 'key' => $key, 'wwdr' => $wwdr];
    }

    /** An absolute path to a readable file, or the file's bytes base64-encoded (the app spec has no file mount). */
    private static function readSource(string $source): ?string
    {
        if (str_starts_with($source, '/') && is_file($source) && is_readable($source)) {
            return (string) file_get_contents($source);
        }

        $decoded = base64_decode($source, true);

        return $decoded !== false && $decoded !== '' ? $decoded : null;
    }

    /** Forget the opened credentials (tests change the config within one process). */
    public static function flush(): void
    {
        self::$credentials = [];
    }

    /*
     * ------------------------------------------------------------------ building the file
     */

    /**
     * The detached DER signature of the manifest, or null when OpenSSL refuses.
     *
     * openssl_pkcs7_sign(), not openssl_cms_sign(): handed the WWDR intermediate as an extra
     * certificate, openssl_cms_sign() (PHP 8.3 on OpenSSL 3.6) wrote a SignerInfo whose signature did
     * not verify, while without the intermediate it did; and Wallet refuses a signature that does not
     * carry the intermediate. openssl_pkcs7_sign() writes an S/MIME message, so the signature is the
     * base64 body of its smime.p7s part, decoded to DER (the way the established pkpass libraries do).
     */
    protected function sign(string $manifest, array $credentials): ?string
    {
        $dir = sys_get_temp_dir().'/es-pkpass-'.bin2hex(random_bytes(8));

        if (! @mkdir($dir, 0700)) {
            return null;
        }

        try {
            if (@file_put_contents($dir.'/manifest.json', $manifest) === false || @file_put_contents($dir.'/wwdr.pem', $credentials['wwdr']) === false) {
                return null;
            }

            $signed = @openssl_pkcs7_sign(
                $dir.'/manifest.json',
                $dir.'/smime',
                $credentials['cert'],
                $credentials['key'],
                [],
                PKCS7_BINARY | PKCS7_DETACHED,
                $dir.'/wwdr.pem',
            );

            if (! $signed) {
                return null;
            }

            $smime = (string) file_get_contents($dir.'/smime');

            if (! preg_match('/filename="?smime\.p7s"?\r?\n\r?\n([A-Za-z0-9+\/=\r\n]+)/', $smime, $match)) {
                return null;
            }

            $der = base64_decode(preg_replace('/\s+/', '', $match[1]), true);

            return $der !== false && $der !== '' ? $der : null;
        } finally {
            foreach (['manifest.json', 'wwdr.pem', 'smime'] as $file) {
                @unlink($dir.'/'.$file);
            }
            @rmdir($dir);
        }
    }

    protected function zip(array $files): ?string
    {
        $path = @tempnam(sys_get_temp_dir(), 'es-pkpass');
        if (! $path) {
            return null;
        }
        $zip = new \ZipArchive;

        try {
            if ($zip->open($path, \ZipArchive::OVERWRITE) !== true) {
                return null;
            }
            foreach ($files as $name => $bytes) {
                $zip->addFromString($name, $bytes);
            }
            $zip->close();

            return file_get_contents($path) ?: null;
        } finally {
            @unlink($path);
        }
    }

    /**
     * icon.png (required by Wallet, shown on the lock screen) and logo.png, at 1x, 2x and 3x, from
     * the schedule's profile picture when it can be read, else the app's own icon.
     *
     * @return array<string, string>
     */
    protected function images(?Role $role): array
    {
        $stored = $role ? ($role->getAttributes()['profile_image_url'] ?? null) : null;

        // Drawn once per picture and kept a day: on hosted the picture is a Spaces GET and six
        // resamples, which a tap should not wait for every time.
        $build = fn () => array_map('base64_encode', $this->drawImages($stored));

        try {
            $encoded = Cache::remember('apple_wallet_images:'.sha1((string) $stored), now()->addDay(), $build);
        } catch (\Throwable) {
            $encoded = $build();
        }

        return array_filter(array_map(fn ($bytes) => base64_decode((string) $bytes, true) ?: null, $encoded));
    }

    /** @return array<string, string> */
    private function drawImages(?string $stored): array
    {
        // The schedule's picture when it decodes (an SVG or HEIC does not), else the app's icon:
        // a pass must always carry an icon.
        $image = $this->decode($stored ? $this->profileImageBytes($stored) : null)
            ?? $this->decode(@file_get_contents(public_path('images/apple-touch-icon.png')) ?: null);

        if (! $image) {
            return [];
        }

        $files = [];
        foreach ([1 => '', 2 => '@2x', 3 => '@3x'] as $scale => $suffix) {
            $files['icon'.$suffix.'.png'] = $this->png($image, 29 * $scale, 29 * $scale);
            $files['logo'.$suffix.'.png'] = $this->png($image, 50 * $scale, 50 * $scale);
        }
        imagedestroy($image);

        return array_filter($files);
    }

    /** GD's image of these bytes, when they are one GD can read within the memory there is. */
    private function decode(?string $bytes)
    {
        if (! $bytes) {
            return null;
        }

        $info = @getimagesizefromstring($bytes);
        if ($info === false || empty($info[0]) || empty($info[1]) || ! ImageUtils::canDecodePixels((int) $info[0] * (int) $info[1])) {
            return null;
        }

        return @imagecreatefromstring($bytes) ?: null;
    }

    private function profileImageBytes(string $stored): ?string
    {
        try {
            if (str_starts_with($stored, 'demo_')) {
                return @file_get_contents(public_path('images/demo/'.$stored)) ?: null;
            }

            // Where the upload went (RoleController::update()): under public/ on the local disk.
            $disk = config('filesystems.default');
            $path = $disk === 'local' ? 'public/'.$stored : $stored;

            return Storage::disk($disk)->get($path) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** A square crop of $image at $width x $height, as PNG bytes. */
    private function png($image, int $width, int $height): ?string
    {
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $side = min($sourceWidth, $sourceHeight);

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $image, 0, 0, intdiv($sourceWidth - $side, 2), intdiv($sourceHeight - $side, 2), $width, $height, $side, $side);

        ob_start();
        imagepng($canvas);
        $bytes = ob_get_clean();
        imagedestroy($canvas);

        return $bytes ?: null;
    }

    /**
     * The schedule's colour as the pass background, with whichever of near-black or white reads on
     * it; a dark slate when the schedule has none.
     *
     * @return array{0: string, 1: string}
     */
    private function colors(?Role $role): array
    {
        $hex = (string) ($role?->manifestThemeColor() ?? '');

        if (! preg_match('/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $hex, $m)) {
            return ['rgb(17, 24, 39)', 'rgb(255, 255, 255)'];
        }

        [$r, $g, $b] = [hexdec($m[1]), hexdec($m[2]), hexdec($m[3])];
        $channel = fn ($c) => ($c /= 255) <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        $luminance = 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);

        // White text when it contrasts better than near-black does.
        $foreground = (1.05 / ($luminance + 0.05)) >= (($luminance + 0.05) / 0.0625) ? 'rgb(255, 255, 255)' : 'rgb(17, 24, 39)';

        return ["rgb($r, $g, $b)", $foreground];
    }

    private function clamp(?string $value, int $length): string
    {
        return mb_substr(trim((string) $value), 0, $length);
    }
}
