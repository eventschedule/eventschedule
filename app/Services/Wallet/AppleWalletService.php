<?php

namespace App\Services\Wallet;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;
use Throwable;

class AppleWalletService
{
    protected bool $enabled = false;
    protected ?string $certificatePath;
    protected ?string $certificatePassword;
    protected ?string $wwdrCertificatePath;
    protected ?string $passTypeIdentifier;
    protected ?string $teamIdentifier;
    protected string $organizationName;
    protected string $backgroundColor;
    protected string $foregroundColor;
    protected string $labelColor;
    protected bool $debugEnabled = false;
    protected ?string $logChannel;
    protected ?string $debugDumpPath = null;
    protected static bool $legacyProviderInitialized = false;
    protected static ?string $legacyProviderConfigPath = null;
    protected static ?string $legacyProviderOriginalConfig = null;
    protected static ?string $legacyProviderOriginalModules = null;

    public function __construct()
    {
        $config = config('wallet.apple');

        $this->enabled = (bool) ($config['enabled'] ?? false);
        $this->certificatePath = $this->sanitizeConfigValue($config['certificate_path'] ?? null);
        $this->certificatePassword = $this->sanitizeConfigValue($config['certificate_password'] ?? null);
        $this->wwdrCertificatePath = $this->sanitizeConfigValue($config['wwdr_certificate_path'] ?? null);
        $this->passTypeIdentifier = $this->sanitizeConfigValue($config['pass_type_identifier'] ?? null);
        $teamIdentifier = $this->sanitizeConfigValue($config['team_identifier'] ?? null);
        $this->teamIdentifier = $teamIdentifier !== null ? strtoupper($teamIdentifier) : null;
        $this->organizationName = $this->sanitizeConfigValue($config['organization_name'] ?? config('app.name')) ?? config('app.name');
        $this->backgroundColor = $this->sanitizeConfigValue($config['background_color'] ?? 'rgb(78,129,250)') ?? 'rgb(78,129,250)';
        $this->foregroundColor = $this->sanitizeConfigValue($config['foreground_color'] ?? 'rgb(255,255,255)') ?? 'rgb(255,255,255)';
        $this->labelColor = $this->sanitizeConfigValue($config['label_color'] ?? 'rgb(255,255,255)') ?? 'rgb(255,255,255)';
        $this->debugEnabled = (bool) ($config['debug'] ?? config('app.debug'));
        $this->logChannel = $this->sanitizeConfigValue($config['log_channel'] ?? null);
        $debugDumpPath = $config['debug_dump_path'] ?? storage_path('app/wallet/debug-dumps');
        $this->debugDumpPath = $this->sanitizeConfigValue($debugDumpPath);

        if ($this->debugDumpPath !== null) {
            $this->debugDumpPath = rtrim($this->debugDumpPath, DIRECTORY_SEPARATOR);
        }

        $this->logDebug('Apple Wallet service configured.', [
            'enabled' => $this->enabled,
            'certificate_path' => $this->certificatePath,
            'certificate_exists' => $this->certificatePath ? file_exists($this->certificatePath) : null,
            'wwdr_certificate_path' => $this->wwdrCertificatePath,
            'wwdr_certificate_exists' => $this->wwdrCertificatePath ? file_exists($this->wwdrCertificatePath) : null,
            'pass_type_identifier' => $this->passTypeIdentifier,
            'team_identifier' => $this->teamIdentifier,
            'debug_enabled' => $this->debugEnabled,
            'debug_dump_path' => $this->debugDumpPath,
        ]);
    }

    public function isConfigured(): bool
    {
        if (! $this->enabled) {
            $this->logDebug('Apple Wallet is disabled in configuration.');

            return false;
        }

        $missing = [];

        if (! $this->certificatePath) {
            $missing[] = 'certificate_path';
        }

        if (! $this->passTypeIdentifier) {
            $missing[] = 'pass_type_identifier';
        }

        if (! $this->teamIdentifier) {
            $missing[] = 'team_identifier';
        }

        if ($this->teamIdentifier && ! $this->isValidTeamIdentifier($this->teamIdentifier)) {
            $this->logDebug('Apple Wallet team identifier is invalid.', [
                'team_identifier' => $this->teamIdentifier,
            ]);

            return false;
        }

        if (! $this->wwdrCertificatePath) {
            $missing[] = 'wwdr_certificate_path';
        }

        if ($missing !== []) {
            $this->logDebug('Apple Wallet configuration is missing required values.', ['missing' => $missing]);

            return false;
        }

        $missingFiles = [];

        if ($this->certificatePath && ! file_exists($this->certificatePath)) {
            $missingFiles['certificate_path'] = $this->certificatePath;
        }

        if ($this->wwdrCertificatePath && ! file_exists($this->wwdrCertificatePath)) {
            $missingFiles['wwdr_certificate_path'] = $this->wwdrCertificatePath;
        }

        if ($missingFiles !== []) {
            $this->logDebug('Apple Wallet certificate files are missing.', ['files' => $missingFiles]);

            return false;
        }

        return true;
    }

    /**
     * @param  mixed  $value
     */
    protected function sanitizeConfigValue($value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function isValidTeamIdentifier(string $teamIdentifier): bool
    {
        return preg_match('/^[A-Z0-9]{10}$/', $teamIdentifier) === 1;
    }

    protected function logDebug(string $message, array $context = []): void
    {
        if (! $this->debugEnabled) {
            return;
        }

        $context = $this->prepareLogContext($context);

        try {
            if ($this->logChannel) {
                Log::channel($this->logChannel)->debug($message, $context);

                return;
            }
        } catch (Throwable $exception) {
            try {
                Log::debug('Failed to write Apple Wallet debug message to configured channel.', [
                    'channel' => $this->logChannel,
                    'error' => $exception->getMessage(),
                ]);
            } catch (Throwable $inner) {
                // Swallow logging failures to avoid masking the original issue.
            }
        }

        try {
            Log::debug($message, $context);
        } catch (Throwable $exception) {
            // Swallow logging failures to avoid masking the original issue.
        }
    }

    protected function prepareLogContext(array $context): array
    {
        $redactedKeys = [
            'certificate_password',
            'password',
        ];

        foreach ($context as $key => $value) {
            if (in_array($key, $redactedKeys, true)) {
                $context[$key] = '[redacted]';
                continue;
            }

            if (is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $context[$key] = $value->toArray();
                } elseif (method_exists($value, '__toString')) {
                    $context[$key] = (string) $value;
                } else {
                    $context[$key] = get_class($value);
                }
            } elseif (is_resource($value)) {
                $context[$key] = sprintf('resource(%s)', get_resource_type($value));
            }
        }

        return $context;
    }

    public function isAvailableForSale(Sale $sale): bool
    {
        if (! $this->isConfigured()) {
            $this->logDebug('Apple Wallet is not available for this sale because configuration is incomplete.', [
                'sale_id' => $sale->id,
                'sale_status' => $sale->status,
            ]);

            return false;
        }

        if ($sale->status !== 'paid') {
            $this->logDebug('Apple Wallet is not available for this sale because it is not paid.', [
                'sale_id' => $sale->id,
                'sale_status' => $sale->status,
            ]);

            return false;
        }

        return true;
    }

    public function generateTicketPass(Sale $sale): string
    {
        $this->logDebug('Starting Apple Wallet pass generation.', [
            'sale_id' => $sale->id,
            'event_id' => $sale->event_id,
            'sale_status' => $sale->status,
        ]);

        if (! $this->isAvailableForSale($sale)) {
            throw new RuntimeException('Apple Wallet is not configured for this sale.');
        }

        $sale->loadMissing('event.creatorRole', 'event.venue', 'saleTickets.ticket');

        $eventTimezone = $sale->event ? $this->resolveTimezone($sale->event) : null;

        $this->logDebug('Apple Wallet sale context loaded.', [
            'sale_id' => $sale->id,
            'event_id' => $sale->event_id,
            'event_timezone' => $eventTimezone,
            'event_date' => $sale->event_date,
            'sale_created_at' => $sale->created_at instanceof Carbon ? $sale->created_at->toIso8601String() : null,
            'ticket_count' => $sale->saleTickets->sum('quantity'),
            'ticket_breakdown' => $this->summarizeTickets($sale),
        ]);

        if (! $sale->event) {
            $this->logDebug('Sale does not have an associated event when generating Apple Wallet pass.', [
                'sale_id' => $sale->id,
            ]);

            throw new RuntimeException('Sale event is not available.');
        }

        $payload = $this->buildPassPayload($sale);

        $this->logDebug('Apple Wallet pass payload generated.', [
            'sale_id' => $sale->id,
            'serial_number' => $payload['serialNumber'] ?? null,
            'relevant_date' => $payload['relevantDate'] ?? null,
            'location_count' => isset($payload['locations']) ? count($payload['locations']) : 0,
            'payload_summary' => $this->summarizePayloadForLog($payload),
        ]);

        $baseFiles = [
            'pass.json' => $this->encodeJson($payload),
            'icon.png' => $this->createPassImage($sale->event, 58, 58),
            'icon@2x.png' => $this->createPassImage($sale->event, 116, 116),
            'logo.png' => $this->createPassImage($sale->event, 160, 50),
            'logo@2x.png' => $this->createPassImage($sale->event, 320, 100),
        ];

        $this->logDebug('Apple Wallet pass base assets generated.', [
            'sale_id' => $sale->id,
            'asset_details' => $this->summarizeFiles($baseFiles),
        ]);

        $manifest = $this->createManifest($baseFiles);

        $this->logDebug('Apple Wallet manifest prepared.', [
            'sale_id' => $sale->id,
            'manifest' => $this->summarizeManifest($manifest),
        ]);

        $signature = $this->signManifest($manifest);

        $this->logDebug('Apple Wallet manifest signature generated.', [
            'sale_id' => $sale->id,
            'signature_bytes' => strlen($signature),
            'signature_sha1' => sha1($signature),
        ]);

        $this->logDebug('Apple Wallet manifest signed successfully.', [
            'sale_id' => $sale->id,
            'manifest_files' => array_keys($manifest),
        ]);

        $package = $this->createPackage($baseFiles, $manifest, $signature);

        $this->dumpDebugArtifacts($sale, $payload, $baseFiles, $manifest, $signature, $package);

        $this->logDebug('Apple Wallet pass package created.', [
            'sale_id' => $sale->id,
            'package_size' => strlen($package),
            'package_sha1' => sha1($package),
        ]);

        return $package;
    }

    protected function buildPassPayload(Sale $sale): array
    {
        $event = $sale->event;
        $startsAt = $this->resolveEventStart($event, $sale);
        $relevantDate = $startsAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
        $ticketSummary = $sale->saleTickets->map(function ($saleTicket) {
            $name = $saleTicket->ticket->type ?: __('messages.ticket');

            return trim($name) . ' x ' . $saleTicket->quantity;
        })->implode(', ');

        $this->logDebug('Apple Wallet pass timing resolved.', [
            'sale_id' => $sale->id,
            'event_id' => $event->id,
            'starts_at_local' => $startsAt->toIso8601String(),
            'starts_at_utc' => $startsAt->copy()->setTimezone('UTC')->toIso8601String(),
            'relevant_date' => $relevantDate,
            'event_timezone' => $this->resolveTimezone($event),
        ]);

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

    protected function summarizeTickets(Sale $sale): array
    {
        if (! $sale->relationLoaded('saleTickets')) {
            return [];
        }

        return $sale->saleTickets->map(function ($saleTicket) {
            $ticket = $saleTicket->ticket;

            return [
                'sale_ticket_id' => $saleTicket->id,
                'ticket_id' => $ticket?->id,
                'ticket_name' => $ticket?->type ?: $ticket?->name,
                'quantity' => $saleTicket->quantity,
            ];
        })->values()->all();
    }

    protected function summarizePayloadForLog(array $payload): array
    {
        return [
            'pass_type_identifier' => $payload['passTypeIdentifier'] ?? null,
            'team_identifier' => $payload['teamIdentifier'] ?? null,
            'serial_number' => $payload['serialNumber'] ?? null,
            'relevant_date' => $payload['relevantDate'] ?? null,
            'location_count' => isset($payload['locations']) ? count($payload['locations']) : 0,
            'primary_fields' => count($payload['eventTicket']['primaryFields'] ?? []),
            'secondary_fields' => count($payload['eventTicket']['secondaryFields'] ?? []),
            'auxiliary_fields' => count($payload['eventTicket']['auxiliaryFields'] ?? []),
            'back_fields' => count($payload['eventTicket']['backFields'] ?? []),
            'barcode_message_length' => isset($payload['barcode']['message']) ? strlen((string) $payload['barcode']['message']) : 0,
        ];
    }

    /**
     * @param  array<string, string>  $files
     * @return array<int, array<string, int|string>>
     */
    protected function summarizeFiles(array $files): array
    {
        $summary = [];

        foreach ($files as $filename => $contents) {
            $summary[] = [
                'filename' => $filename,
                'bytes' => strlen($contents),
                'sha1' => sha1($contents),
            ];
        }

        return $summary;
    }

    /**
     * @param  array<string, string>  $manifest
     * @return array<int, array<string, string>>
     */
    protected function summarizeManifest(array $manifest): array
    {
        $summary = [];

        foreach ($manifest as $filename => $hash) {
            $summary[] = [
                'filename' => $filename,
                'sha1' => $hash,
            ];
        }

        return $summary;
    }

    protected function buildSerialNumber(Sale $sale): string
    {
        return strtoupper(substr(hash('sha256', $sale->id . '|' . $sale->secret), 0, 32));
    }

    protected function resolveEventStart(Event $event, Sale $sale): Carbon
    {
        $startsAt = $event->getStartDateTime($sale->event_date);

        if ($startsAt) {
            $timezone = $this->resolveTimezone($event);

            return $startsAt->clone()->setTimezone($timezone);
        }

        $fallback = $this->resolveFallbackStart($event, $sale);
        $timezone = $this->resolveTimezone($event);

        return $fallback->setTimezone($timezone);
    }

    protected function resolveFallbackStart(Event $event, Sale $sale): Carbon
    {
        if ($sale->created_at instanceof Carbon) {
            return $sale->created_at->clone();
        }

        if ($event->created_at instanceof Carbon) {
            return $event->created_at->clone();
        }

        return Carbon::now();
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
        $this->logDebug('Signing Apple Wallet manifest.', [
            'manifest_files' => array_keys($manifest),
            'certificate_path' => $this->certificatePath,
        ]);

        $certificateContents = file_get_contents($this->certificatePath ?? '');

        if ($certificateContents === false) {
            throw new RuntimeException('Unable to read Apple Wallet certificate.');
        }

        $this->logDebug('Apple Wallet certificate loaded for signing.', [
            'certificate_bytes' => strlen($certificateContents),
        ]);

        $certificates = $this->loadCertificates($certificateContents);
        $manifestPath = $this->createTempFile($this->encodeJson($manifest));
        $chainPath = $this->createSignerCertificateChainFile($certificates);

        $this->logDebug('Generated manifest signing workspace.', [
            'manifest_path' => $manifestPath,
            'chain_path' => $chainPath,
        ]);

        try {
            $cmsSignature = $this->tryCmsManifestSignature($manifestPath, $certificates, $chainPath);

            if ($cmsSignature !== null) {
                $this->logDebug('Manifest signed using openssl_cms_sign.', [
                    'chain_path' => $chainPath,
                    'signature_length' => strlen($cmsSignature),
                    'signature_sha1' => sha1($cmsSignature),
                ]);

                return $cmsSignature;
            }

            $this->logDebug('Falling back to PKCS#7 manifest signing.');

            $fallbackSignature = $this->signManifestWithPkcs7($manifestPath, $certificates, $chainPath);

            $this->logDebug('PKCS#7 fallback signature produced.', [
                'signature_length' => strlen($fallbackSignature),
                'signature_sha1' => sha1($fallbackSignature),
            ]);

            return $fallbackSignature;
        } finally {
            @unlink($manifestPath);
            if ($chainPath !== null) {
                @unlink($chainPath);
            }
        }
    }

    /**
     * @param  array{cert: mixed, pkey: mixed, extracerts?: array<int, mixed>}  $certificates
     */
    protected function tryCmsManifestSignature(string $manifestPath, array $certificates, ?string $chainPath = null): ?string
    {
        if (! function_exists('openssl_cms_sign')) {
            $this->logDebug('openssl_cms_sign is not available; skipping CMS signature path.');

            return null;
        }

        if (! $this->cmsSupportsDerEncoding()) {
            $this->logDebug('openssl_cms_sign does not support DER encoding; skipping CMS signature path.');

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
            $chainPath ?: ($this->wwdrCertificatePath ?? '')
        );

        $signatureContents = null;

        if ($result) {
            $signatureContents = file_get_contents($signaturePath);
        }

        @unlink($signaturePath);

        if (! $result || $signatureContents === false) {
            $this->logDebug('openssl_cms_sign failed to generate a signature. Falling back to PKCS#7.', [
                'result' => $result,
            ]);

            return null;
        }

        if ($signatureContents === '') {
            $this->logDebug('openssl_cms_sign returned an empty signature.');

            return null;
        }

        if (! $this->cmsSignatureAppearsDer($signatureContents)) {
            $this->logDebug('openssl_cms_sign produced a signature that does not appear to be DER encoded.');

            return null;
        }

        return $signatureContents;
    }

    protected function cmsSupportsDerEncoding(): bool
    {
        return defined('OPENSSL_ENCODING_DER');
    }

    protected function cmsSignatureAppearsDer(string $signature): bool
    {
        if (str_contains($signature, '-----BEGIN')) {
            return false;
        }

        if (preg_match('/Content-Transfer-Encoding:\s*base64/i', $signature)) {
            return false;
        }

        if (preg_match('/^MIME-Version:\s*1\.0/im', $signature)) {
            return false;
        }

        return true;
    }

    /**
     * @param  array{cert: mixed, pkey: mixed, extracerts?: array<int, mixed>}  $certificates
     */
    protected function signManifestWithPkcs7(string $manifestPath, array $certificates, ?string $chainPath = null): string
    {
        $signaturePath = tempnam(sys_get_temp_dir(), 'pkpass-signature-');

        if ($signaturePath === false) {
            throw new RuntimeException('Unable to create signature file.');
        }

        $this->logDebug('Attempting PKCS#7 manifest signature.', [
            'manifest_path' => $manifestPath,
            'chain_path' => $chainPath,
        ]);

        $signingResult = openssl_pkcs7_sign(
            $manifestPath,
            $signaturePath,
            $certificates['cert'] ?? null,
            $certificates['pkey'] ?? null,
            [],
            PKCS7_BINARY | PKCS7_DETACHED,
            $chainPath ?: $this->wwdrCertificatePath
        );

        if (! $signingResult) {
            @unlink($signaturePath);
            $this->logDebug('openssl_pkcs7_sign failed to generate a signature.');
            throw new RuntimeException('Unable to sign Apple Wallet manifest.');
        }

        $signatureContents = file_get_contents($signaturePath);

        @unlink($signaturePath);

        if ($signatureContents === false) {
            throw new RuntimeException('Unable to read Apple Wallet signature.');
        }

        $this->logDebug('PKCS#7 manifest signature created successfully.', [
            'signature_length' => strlen($signatureContents),
        ]);

        $binarySignature = $this->convertSignatureToBinary($signatureContents);

        $this->logDebug('PKCS#7 manifest signature normalized to binary.', [
            'signature_length' => strlen($binarySignature),
            'signature_sha1' => sha1($binarySignature),
        ]);

        return $binarySignature;
    }

    /**
     * @param  array{cert: mixed, pkey: mixed, extracerts: array<int, mixed>}  $certificates
     */
    protected function createSignerCertificateChainFile(array $certificates): ?string
    {
        $chain = $this->buildSignerCertificateChain($certificates);

        if ($chain === null || trim($chain) === '') {
            $this->logDebug('No signer certificate chain was generated.');

            return null;
        }

        $path = tempnam(sys_get_temp_dir(), 'pkpass-chain-');

        if ($path === false) {
            $this->logDebug('Unable to allocate temporary file for signer certificate chain.');

            return null;
        }

        if (@file_put_contents($path, $chain) === false) {
            @unlink($path);
            $this->logDebug('Unable to write signer certificate chain to temporary file.');
            return null;
        }

        $this->logDebug('Signer certificate chain file created.', [
            'chain_path' => $path,
            'chain_length' => strlen($chain),
        ]);

        return $path;
    }

    /**
     * @param  array{cert: mixed, pkey: mixed, extracerts: array<int, mixed>}  $certificates
     */
    protected function buildSignerCertificateChain(array $certificates): ?string
    {
        $chain = [];
        $fingerprints = [];

        $primaryPem = $this->exportCertificateToPem($certificates['cert'] ?? null);

        if ($primaryPem === null) {
            return null;
        }

        $primaryFingerprint = preg_replace('/\s+/', '', $primaryPem) ?? '';

        if ($primaryFingerprint !== '') {
            $fingerprints[sha1($primaryFingerprint)] = true;
        }

        foreach ($certificates['extracerts'] as $extra) {
            $pem = $this->exportCertificateToPem($extra);

            if ($pem !== null) {
                $this->appendCertificateToChain($chain, $fingerprints, $pem);
            }
        }

        if ($this->wwdrCertificatePath && file_exists($this->wwdrCertificatePath)) {
            $wwdrContents = @file_get_contents($this->wwdrCertificatePath);

            if ($wwdrContents !== false) {
                $wwdrPem = $this->exportCertificateToPem($wwdrContents);

                if ($wwdrPem !== null) {
                    $this->appendCertificateToChain($chain, $fingerprints, $wwdrPem);
                }
            }
        }

        if (empty($chain)) {
            $this->logDebug('Signer certificate chain is empty after processing certificates.');

            return null;
        }

        $finalChain = implode(PHP_EOL . PHP_EOL, array_map('trim', $chain));

        $this->logDebug('Signer certificate chain built.', [
            'certificate_count' => count($chain),
        ]);

        return $finalChain;
    }

    protected function appendCertificateToChain(array &$chain, array &$fingerprints, string $certificate): void
    {
        $normalized = preg_replace('/\s+/', '', $certificate) ?? '';

        if ($normalized === '') {
            return;
        }

        $fingerprint = sha1($normalized);

        if (isset($fingerprints[$fingerprint])) {
            return;
        }

        $chain[] = trim($certificate);
        $fingerprints[$fingerprint] = true;
    }

    protected function exportCertificateToPem(mixed $certificate): ?string
    {
        if ($certificate === null) {
            return null;
        }

        $this->ensureOpenSslLegacyProvider();

        if (is_string($certificate)) {
            $trimmed = trim($certificate);

            if ($trimmed === '') {
                return null;
            }

            if (str_contains($trimmed, '-----BEGIN CERTIFICATE-----')) {
                return $trimmed;
            }

            $parsed = @openssl_x509_read($certificate);

            if ($parsed === false) {
                return null;
            }

            $exported = '';

            if (@openssl_x509_export($parsed, $exported)) {
                return trim($exported);
            }

            return null;
        }

        if (is_resource($certificate) || is_object($certificate)) {
            $exported = '';

            if (@openssl_x509_export($certificate, $exported)) {
                return trim($exported);
            }
        }

        return null;
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
     * @return array{cert: mixed, pkey: mixed, extracerts: array<int, mixed>}
     */
    protected function loadCertificates(string $certificateContents): array
    {
        $password = $this->certificatePassword ?? '';
        $errors = [];

        $this->logDebug('Attempting to load Apple Wallet certificate bundle.', [
            'password_provided' => $password !== '',
            'content_length' => strlen($certificateContents),
        ]);

        $pkcs12 = $this->tryParsePkcs12Certificate($certificateContents, $password, $errors);

        if ($pkcs12 !== null) {
            $this->logDebug('Loaded Apple Wallet certificate using openssl_pkcs12_read.');

            return $this->normalizeCertificateResult($pkcs12);
        }

        $decoded = $this->decodeCertificateIfNeeded($certificateContents);

        if ($decoded !== null) {
            $this->logDebug('Detected base64-encoded certificate. Retrying PKCS#12 parse after decoding.');

            $pkcs12 = $this->tryParsePkcs12Certificate($decoded, $password, $errors);

            if ($pkcs12 !== null) {
                $this->logDebug('Loaded Apple Wallet certificate using openssl_pkcs12_read after decoding.');

                return $this->normalizeCertificateResult($pkcs12);
            }
        }

        $cliCertificates = $this->tryExtractCertificatesWithOpenSslCli($certificateContents, $password, $errors);

        if ($cliCertificates !== null) {
            $this->logDebug('Loaded Apple Wallet certificate using OpenSSL CLI fallback.');

            return $this->normalizeCertificateResult($cliCertificates);
        }

        if ($decoded !== null) {
            $cliCertificates = $this->tryExtractCertificatesWithOpenSslCli($decoded, $password, $errors);

            if ($cliCertificates !== null) {
                $this->logDebug('Loaded Apple Wallet certificate using OpenSSL CLI fallback after decoding.');

                return $this->normalizeCertificateResult($cliCertificates);
            }
        }

        $pemCertificates = $this->parsePemCertificateBundle($certificateContents, $password, $errors);

        if ($pemCertificates !== null) {
            $this->logDebug('Loaded Apple Wallet certificate from PEM bundle.');

            return $this->normalizeCertificateResult($pemCertificates);
        }

        if ($decoded !== null) {
            $pemCertificates = $this->parsePemCertificateBundle($decoded, $password, $errors);

            if ($pemCertificates !== null) {
                $this->logDebug('Loaded Apple Wallet certificate from PEM bundle after decoding.');

                return $this->normalizeCertificateResult($pemCertificates);
            }
        }

        $errorDetails = $this->formatOpenSslErrors($errors);

        $this->logDebug('Unable to parse Apple Wallet certificate after trying all strategies.', [
            'errors' => $errorDetails,
        ]);

        throw new RuntimeException(trim('Unable to parse Apple Wallet certificate. ' . $errorDetails));
    }

    /**
     * @param  array{cert: mixed, pkey: mixed, extracerts?: array<int, mixed>}  $certificates
     * @return array{cert: mixed, pkey: mixed, extracerts: array<int, mixed>}
     */
    protected function normalizeCertificateResult(array $certificates): array
    {
        $extras = [];

        if (! empty($certificates['extracerts']) && is_array($certificates['extracerts'])) {
            $extras = array_values($certificates['extracerts']);
        }

        $certificates['extracerts'] = $extras;

        return $certificates;
    }

    /**
     * Attempt to parse a PEM bundle that includes the certificate and private key.
     *
     * @param  array<int, string>  $errors
     * @return array{cert: string, pkey: resource|string, extracerts: array<int, string>}|null
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

        $certificateBlocks = array_map('trim', $certificateMatches[0]);
        $primaryCertificate = null;

        foreach ($certificateBlocks as $index => $candidate) {
            if ($this->certificateMatchesPrivateKey($candidate, $privateKey)) {
                $primaryCertificate = $candidate;
                unset($certificateBlocks[$index]);
                break;
            }
        }

        if ($primaryCertificate === null) {
            $primaryCertificate = array_shift($certificateBlocks);

            if ($primaryCertificate === null) {
                return null;
            }
        }

        return [
            'cert' => $primaryCertificate,
            'pkey' => $privateKey,
            'extracerts' => array_values($certificateBlocks),
        ];
    }

    protected function certificateMatchesPrivateKey(string $certificate, mixed $privateKey): bool
    {
        if (! is_string($certificate) || trim($certificate) === '') {
            return false;
        }

        $parsed = @openssl_x509_read($certificate);

        if ($parsed === false) {
            return false;
        }

        try {
            return @openssl_x509_check_private_key($parsed, $privateKey) === true;
        } finally {
            if (is_resource($parsed)) {
                @openssl_x509_free($parsed);
            }
        }
    }

    /**
     * @param  array<int, string>  $errors
     * @return array{cert: string|resource, pkey: string|resource, extracerts: array<int, mixed>}|null
     */
    protected function tryParsePkcs12Certificate(string $contents, string $password, array &$errors): ?array
    {
        $this->ensureOpenSslLegacyProvider();

        $certificates = [];

        if (@openssl_pkcs12_read($contents, $certificates, $password)) {
            if (! empty($certificates['cert']) && ! empty($certificates['pkey'])) {
                $extras = [];

                if (! empty($certificates['extracerts']) && is_array($certificates['extracerts'])) {
                    $extras = array_values($certificates['extracerts']);
                }

                return [
                    'cert' => $certificates['cert'],
                    'pkey' => $certificates['pkey'],
                    'extracerts' => $extras,
                ];
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
     * @return array{cert: string, pkey: resource|string, extracerts: array<int, string>}|null
     */
    protected function tryExtractCertificatesWithOpenSslCli(string $contents, string $password, array &$errors): ?array
    {
        if (! $this->canUseOpenSslCli()) {
            $this->logDebug('Skipping OpenSSL CLI extraction because the CLI is not available.');

            return null;
        }

        $this->logDebug('Attempting to extract Apple Wallet certificate with OpenSSL CLI.');

        $inputPath = $this->createTempFile($contents);
        $outputPath = tempnam(sys_get_temp_dir(), 'pkpass-cli-');

        if ($outputPath === false) {
            @unlink($inputPath);
            $this->logDebug('Failed to allocate output path for OpenSSL CLI extraction.');

            return null;
        }

        $passwordPath = tempnam(sys_get_temp_dir(), 'pkpass-password-');

        if ($passwordPath === false) {
            @unlink($inputPath);
            @unlink($outputPath);
            $this->logDebug('Failed to allocate password path for OpenSSL CLI extraction.');

            return null;
        }

        if (file_put_contents($passwordPath, $password . PHP_EOL) === false) {
            @unlink($inputPath);
            @unlink($outputPath);
            @unlink($passwordPath);
            $this->logDebug('Failed to write OpenSSL CLI password file.');

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
            $this->logDebug('Unable to start OpenSSL CLI process.');

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

            $this->logDebug('OpenSSL CLI extraction failed.', [
                'exit_code' => $exitCode,
                'stderr' => $stderr !== '' ? $stderr : null,
                'stdout' => $stdout !== '' ? $stdout : null,
            ]);

            return null;
        }

        $this->logDebug('OpenSSL CLI extraction succeeded.', [
            'exit_code' => $exitCode,
            'output_length' => strlen($pemContents),
        ]);

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

    protected function dumpDebugArtifacts(Sale $sale, array $payload, array $files, array $manifest, string $signature, string $package): void
    {
        if (! $this->debugEnabled || ! $this->debugDumpPath) {
            return;
        }

        try {
            $timestamp = Carbon::now('UTC');
            $directory = implode(DIRECTORY_SEPARATOR, [
                $this->debugDumpPath,
                sprintf(
                    'sale-%s-%s-%s',
                    $sale->id,
                    $this->sanitizeFilesystemSegment($payload['serialNumber'] ?? 'unknown'),
                    $timestamp->format('Ymd_His')
                ),
            ]);

            if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
                throw new RuntimeException('Unable to create Apple Wallet debug dump directory.');
            }

            $assetsDirectory = $directory . DIRECTORY_SEPARATOR . 'assets';

            if (! is_dir($assetsDirectory) && ! @mkdir($assetsDirectory, 0775, true) && ! is_dir($assetsDirectory)) {
                throw new RuntimeException('Unable to create Apple Wallet debug assets directory.');
            }

            foreach ($files as $filename => $contents) {
                $this->writeDebugFile($assetsDirectory . DIRECTORY_SEPARATOR . $filename, $contents);
            }

            $this->writeDebugFile($directory . DIRECTORY_SEPARATOR . 'payload.json', $this->encodeJsonPretty($payload));
            $this->writeDebugFile($directory . DIRECTORY_SEPARATOR . 'manifest.json', $this->encodeJsonPretty($manifest));
            $this->writeDebugFile($directory . DIRECTORY_SEPARATOR . 'signature.bin', $signature);
            $this->writeDebugFile($directory . DIRECTORY_SEPARATOR . 'package.pkpass', $package);

            $metadata = [
                'sale_id' => $sale->id,
                'event_id' => $sale->event_id,
                'serial_number' => $payload['serialNumber'] ?? null,
                'generated_at' => $timestamp->toIso8601String(),
                'asset_details' => $this->summarizeFiles($files),
                'manifest' => $this->summarizeManifest($manifest),
                'payload_summary' => $this->summarizePayloadForLog($payload),
                'signature_sha1' => sha1($signature),
                'signature_bytes' => strlen($signature),
                'package_sha1' => sha1($package),
                'package_bytes' => strlen($package),
                'dump_path' => $directory,
            ];

            $this->writeDebugFile($directory . DIRECTORY_SEPARATOR . 'metadata.json', $this->encodeJsonPretty($metadata));

            $this->logDebug('Apple Wallet debug artifacts written to disk.', [
                'sale_id' => $sale->id,
                'dump_path' => $directory,
                'serial_number' => $payload['serialNumber'] ?? null,
                'package_bytes' => strlen($package),
            ]);
        } catch (Throwable $exception) {
            $this->logDebug('Failed to write Apple Wallet debug artifacts.', [
                'sale_id' => $sale->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function sanitizeFilesystemSegment(?string $value): string
    {
        if ($value === null || $value === '') {
            return 'unknown';
        }

        $sanitized = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?: 'unknown';
        $trimmed = trim($sanitized, '-');

        if ($trimmed === '') {
            return 'unknown';
        }

        return substr($trimmed, 0, 64);
    }

    protected function encodeJsonPretty(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if ($json === false) {
            throw new RuntimeException('Unable to encode Apple Wallet payload.');
        }

        return $json;
    }

    protected function writeDebugFile(string $path, string $contents): void
    {
        $bytes = @file_put_contents($path, $contents);

        if ($bytes === false) {
            throw new RuntimeException(sprintf('Unable to write Apple Wallet debug file [%s].', $path));
        }
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
