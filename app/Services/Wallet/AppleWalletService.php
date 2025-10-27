<?php

namespace App\Services\Wallet;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class AppleWalletService
{
    protected bool $enabled;
    protected ?string $certificatePath;
    protected ?string $certificatePassword;
    protected ?string $wwdrCertificatePath;
    protected ?string $passTypeIdentifier;
    protected ?string $teamIdentifier;
    protected string $organizationName;
    protected string $backgroundColor;
    protected string $foregroundColor;
    protected string $labelColor;
    protected static bool $legacyProviderInitialized = false;
    protected static ?string $legacyProviderConfigPath = null;
    protected static ?string $legacyProviderOriginalConfig = null;
    protected static ?string $legacyProviderOriginalModules = null;

    public function __construct()
    {
        $config = config('wallet.apple');

        $this->enabled = (bool) ($config['enabled'] ?? false);
        $this->certificatePath = $config['certificate_path'] ?? null;
        $this->certificatePassword = $config['certificate_password'] ?? null;
        $this->wwdrCertificatePath = $config['wwdr_certificate_path'] ?? null;
        $this->passTypeIdentifier = $config['pass_type_identifier'] ?? null;
        $this->teamIdentifier = $config['team_identifier'] ?? null;
        $this->organizationName = $config['organization_name'] ?? config('app.name');
        $this->backgroundColor = $config['background_color'] ?? 'rgb(78,129,250)';
        $this->foregroundColor = $config['foreground_color'] ?? 'rgb(255,255,255)';
        $this->labelColor = $config['label_color'] ?? 'rgb(255,255,255)';
    }

    public function isConfigured(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (! $this->certificatePath || ! $this->passTypeIdentifier || ! $this->teamIdentifier || ! $this->wwdrCertificatePath) {
            return false;
        }

        if (! file_exists($this->certificatePath) || ! file_exists($this->wwdrCertificatePath)) {
            return false;
        }

        return true;
    }

    public function isAvailableForSale(Sale $sale): bool
    {
        return $this->isConfigured() && $sale->status === 'paid';
    }

    public function generateTicketPass(Sale $sale): string
    {
        if (! $this->isAvailableForSale($sale)) {
            throw new RuntimeException('Apple Wallet is not configured for this sale.');
        }

        $sale->loadMissing('event.creatorRole', 'event.venue', 'saleTickets.ticket');

        if (! $sale->event) {
            throw new RuntimeException('Sale event is not available.');
        }

        $payload = $this->buildPassPayload($sale);

        $baseFiles = [
            'pass.json' => $this->encodeJson($payload),
            'icon.png' => $this->createPassImage($sale->event, 58, 58),
            'icon@2x.png' => $this->createPassImage($sale->event, 116, 116),
            'logo.png' => $this->createPassImage($sale->event, 160, 50),
            'logo@2x.png' => $this->createPassImage($sale->event, 320, 100),
        ];

        $manifest = $this->createManifest($baseFiles);
        $signature = $this->signManifest($manifest);

        return $this->createPackage($baseFiles, $manifest, $signature);
    }

    protected function buildPassPayload(Sale $sale): array
    {
        $event = $sale->event;
        $startsAt = $this->resolveEventStart($event, $sale->event_date);
        $relevantDate = $startsAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
        $ticketSummary = $sale->saleTickets->map(function ($saleTicket) {
            $name = $saleTicket->ticket->type ?: __('messages.ticket');

            return trim($name) . ' x ' . $saleTicket->quantity;
        })->implode(', ');

        $fields = [
            'formatVersion' => 1,
            'passTypeIdentifier' => $this->passTypeIdentifier,
            'serialNumber' => $this->buildSerialNumber($sale),
            'teamIdentifier' => $this->teamIdentifier,
            'organizationName' => $this->organizationName,
            'description' => $event->name,
            'logoText' => Str::limit($event->name, 24),
            'relevantDate' => $relevantDate,
            'backgroundColor' => $this->backgroundColor,
            'foregroundColor' => $this->foregroundColor,
            'labelColor' => $this->labelColor,
            'barcode' => [
                'format' => 'PKBarcodeFormatQR',
                'message' => $sale->secret,
                'messageEncoding' => 'utf-8',
            ],
            'eventTicket' => [
                'primaryFields' => [
                    [
                        'key' => 'event-name',
                        'label' => __('messages.event'),
                        'value' => $event->name,
                    ],
                ],
                'secondaryFields' => array_values(array_filter([
                    [
                        'key' => 'event-date',
                        'label' => __('messages.date'),
                        'value' => $this->formatDisplayDate($event, $sale->event_date),
                    ],
                    $sale->name ? [
                        'key' => 'attendee',
                        'label' => __('messages.attendee'),
                        'value' => $sale->name,
                    ] : null,
                    $event->venue ? [
                        'key' => 'venue',
                        'label' => __('messages.venue'),
                        'value' => $event->venue->shortAddress() ?: $event->venue->name,
                    ] : null,
                ])),
                'auxiliaryFields' => array_values(array_filter([
                    [
                        'key' => 'tickets',
                        'label' => __('messages.tickets'),
                        'value' => $ticketSummary ?: $sale->quantity(),
                    ],
                    [
                        'key' => 'order',
                        'label' => __('messages.order_number'),
                        'value' => (string) $sale->id,
                    ],
                ])),
                'backFields' => [
                    [
                        'key' => 'ticket-url',
                        'label' => __('messages.ticket'),
                        'value' => route('ticket.view', [
                            'event_id' => $event->hashedId(),
                            'secret' => $sale->secret,
                        ]),
                    ],
                ],
            ],
        ];

        $locations = $this->resolveLocations($event);

        if (! empty($locations)) {
            $fields['locations'] = $locations;
        }

        return $fields;
    }

    protected function buildSerialNumber(Sale $sale): string
    {
        return strtoupper(substr(hash('sha256', $sale->id . '|' . $sale->secret), 0, 32));
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

    protected function resolveTimezone(Event $event): string
    {
        return $event->venue?->timezone
            ?? $event->creatorRole?->timezone
            ?? config('app.timezone', 'UTC');
    }

    protected function formatDisplayDate(Event $event, ?string $eventDate): string
    {
        $localized = $event->localStartsAt(true, $eventDate);

        if ($localized) {
            return $localized;
        }

        $startsAt = $event->getStartDateTime($eventDate, true);

        if ($startsAt) {
            return $startsAt->format('F j, Y g:i A');
        }

        return __('messages.unscheduled');
    }

    /**
     * @param  array<string, string>  $files
     * @return array<string, string>
     */
    protected function createManifest(array $files): array
    {
        $manifest = [];

        foreach ($files as $filename => $contents) {
            $manifest[$filename] = sha1($contents);
        }

        return $manifest;
    }

    /**
     * @param  array<string, string>  $manifest
     */
    protected function signManifest(array $manifest): string
    {
        $certificateContents = file_get_contents($this->certificatePath ?? '');

        if ($certificateContents === false) {
            throw new RuntimeException('Unable to read Apple Wallet certificate.');
        }

        $certificates = $this->loadCertificates($certificateContents);
        $manifestPath = $this->createTempFile($this->encodeJson($manifest));

        try {
            $cmsSignature = $this->tryCmsManifestSignature($manifestPath, $certificates);

            if ($cmsSignature !== null) {
                return $cmsSignature;
            }

            return $this->signManifestWithPkcs7($manifestPath, $certificates);
        } finally {
            @unlink($manifestPath);
        }
    }

    /**
     * @param  array{cert: mixed, pkey: mixed}  $certificates
     */
    protected function tryCmsManifestSignature(string $manifestPath, array $certificates): ?string
    {
        if (! function_exists('openssl_cms_sign')) {
            return null;
        }

        $signaturePath = tempnam(sys_get_temp_dir(), 'pkpass-signature-');

        if ($signaturePath === false) {
            throw new RuntimeException('Unable to create signature file.');
        }

        $flags = 0;

        if (defined('OPENSSL_CMS_BINARY')) {
            $flags |= constant('OPENSSL_CMS_BINARY');
        }

        if (defined('OPENSSL_CMS_DETACHED')) {
            $flags |= constant('OPENSSL_CMS_DETACHED');
        }

        $encoding = defined('OPENSSL_ENCODING_DER') ? constant('OPENSSL_ENCODING_DER') : 0;

        $result = @openssl_cms_sign(
            $manifestPath,
            $signaturePath,
            $certificates['cert'] ?? null,
            $certificates['pkey'] ?? null,
            [],
            $flags,
            $encoding,
            $this->wwdrCertificatePath ?? ''
        );

        $signatureContents = null;

        if ($result) {
            $signatureContents = file_get_contents($signaturePath);
        }

        @unlink($signaturePath);

        if (! $result || $signatureContents === false) {
            return null;
        }

        if ($signatureContents === '') {
            return null;
        }

        return $signatureContents;
    }

    /**
     * @param  array{cert: mixed, pkey: mixed}  $certificates
     */
    protected function signManifestWithPkcs7(string $manifestPath, array $certificates): string
    {
        $signaturePath = tempnam(sys_get_temp_dir(), 'pkpass-signature-');

        if ($signaturePath === false) {
            throw new RuntimeException('Unable to create signature file.');
        }

        $signingResult = openssl_pkcs7_sign(
            $manifestPath,
            $signaturePath,
            $certificates['cert'] ?? null,
            $certificates['pkey'] ?? null,
            [],
            PKCS7_BINARY | PKCS7_DETACHED,
            $this->wwdrCertificatePath
        );

        if (! $signingResult) {
            @unlink($signaturePath);
            throw new RuntimeException('Unable to sign Apple Wallet manifest.');
        }

        $signatureContents = file_get_contents($signaturePath);

        @unlink($signaturePath);

        if ($signatureContents === false) {
            throw new RuntimeException('Unable to read Apple Wallet signature.');
        }

        return $this->convertSignatureToBinary($signatureContents);
    }

    protected function convertSignatureToBinary(string $signature): string
    {
        if (str_contains($signature, '-----BEGIN PKCS7-----')) {
            $signature = preg_replace('/-----BEGIN PKCS7-----|-----END PKCS7-----|\s+/', '', $signature) ?? '';
            $signature = base64_decode($signature, true) ?: '';
        }

        if (preg_match('/Content-Transfer-Encoding:\s*base64/i', $signature)) {
            $parts = preg_split("/\r?\n\r?\n/", $signature, 2);
            $body = $parts[1] ?? '';
            $body = preg_replace('/^--.*$/m', '', $body) ?? '';
            $body = preg_replace('/\s+/', '', $body) ?? '';
            $decoded = $body !== '' ? base64_decode($body, true) : '';

            if ($decoded !== '') {
                return $decoded;
            }
        }

        $maybeBase64 = preg_replace('/\s+/', '', $signature) ?? '';

        if ($maybeBase64 !== '') {
            $decoded = base64_decode($maybeBase64, true) ?: '';

            if ($decoded !== '') {
                return $decoded;
            }
        }

        if ($signature !== '' && preg_match('/[^\x09\x0A\x0D\x20-\x7E]/', $signature)) {
            return $signature;
        }

        if ($signature === '') {
            throw new RuntimeException('Invalid Apple Wallet signature content.');
        }

        return $signature;
    }

    /**
     * @return array{cert: mixed, pkey: mixed}
     */
    protected function loadCertificates(string $certificateContents): array
    {
        $password = $this->certificatePassword ?? '';
        $errors = [];

        $pkcs12 = $this->tryParsePkcs12Certificate($certificateContents, $password, $errors);

        if ($pkcs12 !== null) {
            return $pkcs12;
        }

        $decoded = $this->decodeCertificateIfNeeded($certificateContents);

        if ($decoded !== null) {
            $pkcs12 = $this->tryParsePkcs12Certificate($decoded, $password, $errors);

            if ($pkcs12 !== null) {
                return $pkcs12;
            }
        }

        $cliCertificates = $this->tryExtractCertificatesWithOpenSslCli($certificateContents, $password, $errors);

        if ($cliCertificates !== null) {
            return $cliCertificates;
        }

        if ($decoded !== null) {
            $cliCertificates = $this->tryExtractCertificatesWithOpenSslCli($decoded, $password, $errors);

            if ($cliCertificates !== null) {
                return $cliCertificates;
            }
        }

        $pemCertificates = $this->parsePemCertificateBundle($certificateContents, $password, $errors);

        if ($pemCertificates !== null) {
            return $pemCertificates;
        }

        if ($decoded !== null) {
            $pemCertificates = $this->parsePemCertificateBundle($decoded, $password, $errors);

            if ($pemCertificates !== null) {
                return $pemCertificates;
            }
        }

        $errorDetails = $this->formatOpenSslErrors($errors);

        throw new RuntimeException(trim('Unable to parse Apple Wallet certificate. ' . $errorDetails));
    }

    /**
     * Attempt to parse a PEM bundle that includes the certificate and private key.
     *
     * @param  array<int, string>  $errors
     * @return array{cert: string, pkey: resource|string}|null
     */
    protected function parsePemCertificateBundle(string $contents, string $password, array &$errors = []): ?array
    {
        $this->ensureOpenSslLegacyProvider();

        $certificatePattern = '/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s';
        $privateKeyPattern = '/-----BEGIN (?:RSA |EC |ENCRYPTED )?PRIVATE KEY-----.*?-----END (?:RSA |EC |ENCRYPTED )?PRIVATE KEY-----/s';

        if (! preg_match_all($certificatePattern, $contents, $certificateMatches) || empty($certificateMatches[0])) {
            return null;
        }

        if (! preg_match($privateKeyPattern, $contents, $privateKeyMatch)) {
            return null;
        }

        $privateKey = @openssl_pkey_get_private($privateKeyMatch[0], $password === '' ? null : $password);

        if ($privateKey === false) {
            $errors = array_merge($errors, $this->drainOpenSslErrors());
            return null;
        }

        $certificateChain = implode(PHP_EOL, array_map('trim', $certificateMatches[0]));

        return [
            'cert' => $certificateChain,
            'pkey' => $privateKey,
        ];
    }

    /**
     * @param  array<int, string>  $errors
     * @return array{cert: string|resource, pkey: string|resource}|null
     */
    protected function tryParsePkcs12Certificate(string $contents, string $password, array &$errors): ?array
    {
        $this->ensureOpenSslLegacyProvider();

        $certificates = [];

        if (@openssl_pkcs12_read($contents, $certificates, $password)) {
            if (! empty($certificates['cert']) && ! empty($certificates['pkey'])) {
                return $certificates;
            }
        }

        $errors = array_merge($errors, $this->drainOpenSslErrors());

        return null;
    }

    protected function decodeCertificateIfNeeded(string $contents): ?string
    {
        $trimmed = trim($contents);

        if ($trimmed === '') {
            return null;
        }

        if (str_contains($trimmed, '\0')) {
            return null;
        }

        if (str_starts_with($trimmed, 'data:')) {
            $separator = strpos($trimmed, ',');

            if ($separator === false) {
                return null;
            }

            $metadata = substr($trimmed, 0, $separator);

            if (! str_contains(strtolower($metadata), ';base64')) {
                return null;
            }

            $trimmed = substr($trimmed, $separator + 1);
        }

        if (str_contains($trimmed, '-----BEGIN PKCS12-----')) {
            $trimmed = preg_replace('/-----BEGIN PKCS12-----|-----END PKCS12-----/i', '', $trimmed) ?? $trimmed;
        }

        $normalized = preg_replace('/\s+/', '', $trimmed);

        if ($normalized === null || $normalized === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9+\/=]+$/', $normalized) !== 1) {
            return null;
        }

        $decoded = base64_decode($normalized, true);

        if ($decoded === false || $decoded === '') {
            return null;
        }

        return $decoded;
    }

    /**
     * @param  array<int, string>  $errors
     */
    protected function formatOpenSslErrors(array $errors): string
    {
        $messages = array_values(array_unique(array_filter($errors)));

        if (empty($messages)) {
            return '';
        }

        return 'OpenSSL errors: ' . implode('; ', $messages);
    }

    /**
     * @return array<int, string>
     */
    protected function drainOpenSslErrors(): array
    {
        $messages = [];

        while (($error = openssl_error_string()) !== false) {
            $messages[] = $error;
        }

        return $messages;
    }

    /**
     * Attempt to extract the certificate using the OpenSSL CLI as a fallback when
     * the PHP extension cannot load legacy algorithms required by some PKCS#12 bundles.
     *
     * @param  array<int, string>  $errors
     * @return array{cert: string, pkey: resource|string}|null
     */
    protected function tryExtractCertificatesWithOpenSslCli(string $contents, string $password, array &$errors): ?array
    {
        if (! $this->canUseOpenSslCli()) {
            return null;
        }

        $inputPath = $this->createTempFile($contents);
        $outputPath = tempnam(sys_get_temp_dir(), 'pkpass-cli-');

        if ($outputPath === false) {
            @unlink($inputPath);
            return null;
        }

        $passwordPath = tempnam(sys_get_temp_dir(), 'pkpass-password-');

        if ($passwordPath === false) {
            @unlink($inputPath);
            @unlink($outputPath);
            return null;
        }

        if (file_put_contents($passwordPath, $password . PHP_EOL) === false) {
            @unlink($inputPath);
            @unlink($outputPath);
            @unlink($passwordPath);
            return null;
        }

        $command = [
            'openssl',
            'pkcs12',
            '-in',
            $inputPath,
            '-out',
            $outputPath,
            '-nodes',
        ];

        if (defined('OPENSSL_VERSION_NUMBER') && OPENSSL_VERSION_NUMBER >= 0x30000000) {
            $command[] = '-legacy';
        }

        $command[] = '-passout';
        $command[] = 'pass:';
        $command[] = '-passin';
        $command[] = 'file:' . $passwordPath;

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptorSpec, $pipes);

        if (! is_resource($process)) {
            @unlink($inputPath);
            @unlink($outputPath);
            @unlink($passwordPath);
            return null;
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]) ?: '';
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        $pemContents = file_get_contents($outputPath);

        @unlink($inputPath);
        @unlink($outputPath);
        @unlink($passwordPath);

        if ($exitCode !== 0 || $pemContents === false || trim($pemContents) === '') {
            $message = trim($stderr ?: $stdout);

            if ($message !== '') {
                $errors[] = $message;
            }

            return null;
        }

        return $this->parsePemCertificateBundle($pemContents, '', $errors);
    }

    protected function canUseOpenSslCli(): bool
    {
        if (! function_exists('proc_open')) {
            return false;
        }

        $disabled = ini_get('disable_functions');

        if ($disabled) {
            $disabledFunctions = array_map('trim', explode(',', $disabled));

            if (in_array('proc_open', $disabledFunctions, true)) {
                return false;
            }
        }

        return true;
    }

    protected function ensureOpenSslLegacyProvider(): void
    {
        if (self::$legacyProviderInitialized) {
            return;
        }

        if (! defined('OPENSSL_VERSION_NUMBER') || OPENSSL_VERSION_NUMBER < 0x30000000) {
            self::$legacyProviderInitialized = true;
            return;
        }

        $existingConfig = getenv('OPENSSL_CONF') ?: null;
        self::$legacyProviderOriginalConfig = $existingConfig ?: null;
        self::$legacyProviderOriginalModules = getenv('OPENSSL_MODULES') ?: null;
        $config = '';

        if ($existingConfig) {
            $escapedConfigPath = str_replace(["\\", "\""], ["\\\\", "\\\""], $existingConfig);
            $config .= '.include = "' . $escapedConfigPath . '"' . PHP_EOL;
        }

        $config .= 'openssl_conf = pkpass_conf' . PHP_EOL;

        $config .= PHP_EOL . '[pkpass_conf]' . PHP_EOL . 'providers = provider_sect' . PHP_EOL . PHP_EOL
            . '[provider_sect]' . PHP_EOL . 'default = default_sect' . PHP_EOL . 'legacy = legacy_sect' . PHP_EOL . PHP_EOL
            . '[default_sect]' . PHP_EOL . 'activate = 1' . PHP_EOL . PHP_EOL
            . '[legacy_sect]' . PHP_EOL . 'activate = 1' . PHP_EOL;

        $path = tempnam(sys_get_temp_dir(), 'openssl-conf-');

        if ($path === false) {
            return;
        }

        $bytes = @file_put_contents($path, $config);

        if ($bytes === false) {
            @unlink($path);
            return;
        }

        self::setEnvironmentVariable('OPENSSL_CONF', $path);

        if (self::$legacyProviderOriginalModules === null) {
            foreach ($this->resolveOpenSslModuleDirectories() as $modulePath) {
                if (is_dir($modulePath)) {
                    self::setEnvironmentVariable('OPENSSL_MODULES', $modulePath);
                    break;
                }
            }
        }
        self::$legacyProviderConfigPath = $path;
        self::$legacyProviderInitialized = true;

        register_shutdown_function(static function (): void {
            if (self::$legacyProviderConfigPath) {
                @unlink(self::$legacyProviderConfigPath);
                self::$legacyProviderConfigPath = null;
            }

            if (self::$legacyProviderOriginalConfig !== null) {
                self::setEnvironmentVariable('OPENSSL_CONF', self::$legacyProviderOriginalConfig);
            } else {
                self::setEnvironmentVariable('OPENSSL_CONF', null);
            }

            if (self::$legacyProviderOriginalModules !== null) {
                self::setEnvironmentVariable('OPENSSL_MODULES', self::$legacyProviderOriginalModules);
            } else {
                self::setEnvironmentVariable('OPENSSL_MODULES', null);
            }

            self::$legacyProviderOriginalConfig = null;
            self::$legacyProviderOriginalModules = null;
            self::$legacyProviderInitialized = false;
        });
    }

    /**
     * @return array<int, string>
     */
    protected function resolveOpenSslModuleDirectories(): array
    {
        $paths = [
            '/usr/lib/ssl/ossl-modules',
            '/usr/lib64/ossl-modules',
            '/usr/lib/x86_64-linux-gnu/ossl-modules',
            '/opt/homebrew/opt/openssl@3/lib/ossl-modules',
        ];

        $patterns = [
            '/usr/lib*/ossl-modules',
            '/usr/local/lib*/ossl-modules',
            '/opt/*/lib/ossl-modules',
        ];

        foreach ($patterns as $pattern) {
            $matches = glob($pattern, GLOB_ONLYDIR);

            if (! $matches) {
                continue;
            }

            foreach ($matches as $match) {
                if (is_string($match) && $match !== '') {
                    $paths[] = $match;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    protected static function setEnvironmentVariable(string $name, ?string $value): void
    {
        if ($value === null || $value === '') {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);

            return;
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    /**
     * @param  array<string, string>  $files
     * @param  array<string, string>  $manifest
     */
    protected function createPackage(array $files, array $manifest, string $signature): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'pkpass-') ?: throw new RuntimeException('Unable to create temporary pass file.');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            throw new RuntimeException('Unable to initialise pass archive.');
        }

        foreach ($files as $filename => $contents) {
            $zip->addFromString($filename, $contents);
        }

        $zip->addFromString('manifest.json', $this->encodeJson($manifest));
        $zip->addFromString('signature', $signature);

        $zip->close();

        $binary = file_get_contents($zipPath);
        @unlink($zipPath);

        if ($binary === false) {
            throw new RuntimeException('Unable to read Apple Wallet package.');
        }

        return $binary;
    }

    /**
     * Generate a solid colour PNG asset that matches Apple's required dimensions.
     */
    protected function createPassImage(Event $event, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);

        if (! $image) {
            throw new RuntimeException('Unable to create wallet image asset.');
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $background = $this->allocateColor($image, $event->creatorRole?->accent_color ?? '#4E81FA');
        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        $textColor = imagecolorallocatealpha($image, 255, 255, 255, 40);
        $initials = $this->resolveInitials($event);

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($initials);
        $textHeight = imagefontheight($font);
        $x = (int) max(0, ($width - $textWidth) / 2);
        $y = (int) max(0, ($height - $textHeight) / 2);

        imagestring($image, $font, $x, $y, $initials, $textColor);

        ob_start();
        imagepng($image);
        $contents = ob_get_clean();
        imagedestroy($image);

        if ($contents === false) {
            throw new RuntimeException('Unable to generate wallet icon.');
        }

        return $contents;
    }

    protected function resolveInitials(Event $event): string
    {
        $words = preg_split('/\s+/', trim($event->name));

        if (! $words || $words === ['']) {
            return 'E';
        }

        $initials = collect($words)
            ->take(2)
            ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials ?: 'E';
    }

    protected function allocateColor($image, ?string $hexColor)
    {
        $hexColor = $hexColor ?: '#4E81FA';
        $hexColor = ltrim($hexColor, '#');

        if (strlen($hexColor) === 3) {
            $hexColor = implode('', array_map(fn ($char) => $char . $char, str_split($hexColor)));
        }

        $rgb = [
            hexdec(substr($hexColor, 0, 2)),
            hexdec(substr($hexColor, 2, 2)),
            hexdec(substr($hexColor, 4, 2)),
        ];

        return imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
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

    protected function encodeJson(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new RuntimeException('Unable to encode Apple Wallet payload.');
        }

        return $json;
    }

    protected function createTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'pkpass-');

        if ($path === false) {
            throw new RuntimeException('Unable to create temporary file.');
        }

        $bytes = file_put_contents($path, $contents);

        if ($bytes === false) {
            @unlink($path);
            throw new RuntimeException('Unable to write temporary file.');
        }

        return $path;
    }
}
