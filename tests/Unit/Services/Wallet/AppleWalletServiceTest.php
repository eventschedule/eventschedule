<?php

namespace Tests\Unit\Services\Wallet;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\Wallet\AppleWalletService;
use Carbon\Carbon;
use Tests\TestCase;

class AppleWalletServiceTest extends TestCase
{
    public function testItParsesBase64EncodedPkcs12Certificates(): void
    {
        $password = 'secret-pass';
        $bundle = $this->generatePkcs12CertificateBundle($password);
        $pkcs12 = $bundle['pkcs12'];
        $base64 = base64_encode($pkcs12);

        $service = $this->makeService($password);

        $certificates = $service->exposeLoadCertificates($base64);

        $this->assertNotEmpty($certificates['cert'] ?? null);
        $this->assertNotEmpty($certificates['pkey'] ?? null);
        $this->assertIsArray($certificates['extracerts'] ?? null);
    }

    public function testItConsidersTrimmedConfigurationValuesWhenCheckingAvailability(): void
    {
        $original = config('wallet.apple');
        $certificatePath = tempnam(sys_get_temp_dir(), 'wallet-cert-');
        $wwdrPath = tempnam(sys_get_temp_dir(), 'wallet-wwdr-');

        $this->assertNotFalse($certificatePath, 'Unable to create temporary certificate path.');
        $this->assertNotFalse($wwdrPath, 'Unable to create temporary WWDR path.');

        try {
            config(['wallet.apple' => [
                'enabled' => true,
                'certificate_path' => $certificatePath . "  \n",
                'certificate_password' => "  ",
                'wwdr_certificate_path' => " \t{$wwdrPath} ",
                'pass_type_identifier' => " pass.com.eventschedule.sample ",
                'team_identifier' => " TEAM12345A \n",
                'organization_name' => " Example Org \n",
                'background_color' => "  rgb(78,129,250)  ",
                'foreground_color' => " rgb(255,255,255) ",
                'label_color' => " rgb(0,0,0) ",
            ]]);

            $service = new AppleWalletService();

            $this->assertTrue($service->isConfigured(), 'Trimmed configuration should satisfy Apple Wallet requirements.');
        } finally {
            config(['wallet.apple' => $original]);
            if (is_string($certificatePath)) {
                @unlink($certificatePath);
            }
            if (is_string($wwdrPath)) {
                @unlink($wwdrPath);
            }
        }
    }

    public function testItRejectsInvalidTeamIdentifier(): void
    {
        $original = config('wallet.apple');
        $certificatePath = tempnam(sys_get_temp_dir(), 'wallet-cert-');
        $wwdrPath = tempnam(sys_get_temp_dir(), 'wallet-wwdr-');

        $this->assertNotFalse($certificatePath, 'Unable to create temporary certificate path.');
        $this->assertNotFalse($wwdrPath, 'Unable to create temporary WWDR path.');

        try {
            if (is_string($certificatePath)) {
                touch($certificatePath);
            }

            if (is_string($wwdrPath)) {
                touch($wwdrPath);
            }

            config(['wallet.apple' => [
                'enabled' => true,
                'certificate_path' => $certificatePath,
                'certificate_password' => null,
                'wwdr_certificate_path' => $wwdrPath,
                'pass_type_identifier' => 'pass.com.eventschedule.sample',
                'team_identifier' => 'InvalidTeam',
                'organization_name' => 'Example Org',
            ]]);

            $service = new AppleWalletService();

            $this->assertFalse($service->isConfigured(), 'Invalid team identifier should prevent Apple Wallet from configuring.');
        } finally {
            config(['wallet.apple' => $original]);

            if (is_string($certificatePath)) {
                @unlink($certificatePath);
            }

            if (is_string($wwdrPath)) {
                @unlink($wwdrPath);
            }
        }
    }

    public function testItGeneratesWalletAssetsWithExpectedDimensions(): void
    {
        $event = new Event(['name' => 'Sample Event']);
        $service = $this->makeService(null);

        $icon = $service->exposeCreatePassImage($event, 58, 58);
        $iconImage = imagecreatefromstring($icon);
        $this->assertNotFalse($iconImage, 'Icon PNG is invalid.');
        $this->assertSame(58, imagesx($iconImage));
        $this->assertSame(58, imagesy($iconImage));
        imagedestroy($iconImage);

        $logo = $service->exposeCreatePassImage($event, 160, 50);
        $logoImage = imagecreatefromstring($logo);
        $this->assertNotFalse($logoImage, 'Logo PNG is invalid.');
        $this->assertSame(160, imagesx($logoImage));
        $this->assertSame(50, imagesy($logoImage));
        imagedestroy($logoImage);
    }

    public function testItConvertsPemEncodedSignatureToBinary(): void
    {
        $binary = random_bytes(64);
        $base64 = chunk_split(base64_encode($binary));
        $pem = "-----BEGIN PKCS7-----\n{$base64}-----END PKCS7-----";

        $service = $this->makeService(null);

        $this->assertSame($binary, $service->exposeConvertSignatureToBinary($pem));
    }

    public function testItConvertsMimeEncodedSignatureToBinary(): void
    {
        $binary = random_bytes(64);
        $base64 = chunk_split(base64_encode($binary));
        $mime = implode("\n", [
            'MIME-Version: 1.0',
            'Content-Type: application/x-pkcs7-signature; name="smime.p7s"',
            'Content-Transfer-Encoding: base64',
            '',
            trim($base64),
            '--boundary--',
        ]);

        $service = $this->makeService(null);

        $this->assertSame($binary, $service->exposeConvertSignatureToBinary($mime));
    }

    public function testItReturnsBinarySignatureUnchanged(): void
    {
        $binary = random_bytes(64);
        $service = $this->makeService(null);

        $this->assertSame($binary, $service->exposeConvertSignatureToBinary($binary));
    }

    public function testItGeneratesDerEncodedSignatureWhenCmsIsAvailable(): void
    {
        if (! function_exists('openssl_cms_sign')) {
            $this->markTestSkipped('openssl_cms_sign not available in this environment.');
        }

        $password = 'secret-pass';
        $bundle = $this->generatePkcs12CertificateBundle($password);

        $certificatePath = tempnam(sys_get_temp_dir(), 'wallet-test-cert-');
        $wwdrPath = tempnam(sys_get_temp_dir(), 'wallet-test-wwdr-');

        $this->assertNotFalse($certificatePath, 'Unable to create temporary certificate path.');
        $this->assertNotFalse($wwdrPath, 'Unable to create temporary WWDR path.');

        file_put_contents($certificatePath, $bundle['pkcs12']);
        file_put_contents($wwdrPath, $bundle['certificate']);

        $service = $this->makeService($password);
        $service->setCertificatePath($certificatePath);
        $service->setWwdrCertificatePath($wwdrPath);

        $signature = $service->exposeSignManifest(['pass.json' => sha1('payload')]);

        @unlink($certificatePath);
        @unlink($wwdrPath);

        $this->assertNotEmpty($signature, 'Signature should not be empty.');
        $this->assertSame(0x30, ord($signature[0]), 'CMS signature should start with a DER sequence.');
    }

    public function testItFallsBackToPkcs7WhenCmsDerEncodingUnavailable(): void
    {
        $password = 'secret-pass';
        $bundle = $this->generatePkcs12CertificateBundle($password);

        $certificatePath = tempnam(sys_get_temp_dir(), 'wallet-test-cert-');
        $wwdrPath = tempnam(sys_get_temp_dir(), 'wallet-test-wwdr-');

        $this->assertNotFalse($certificatePath, 'Unable to create temporary certificate path.');
        $this->assertNotFalse($wwdrPath, 'Unable to create temporary WWDR path.');

        file_put_contents($certificatePath, $bundle['pkcs12']);
        file_put_contents($wwdrPath, $bundle['certificate']);

        $service = $this->makeService($password);
        $service->setCertificatePath($certificatePath);
        $service->setWwdrCertificatePath($wwdrPath);
        $service->setCmsDerAvailable(false);

        $signature = $service->exposeSignManifest(['pass.json' => sha1('payload')]);

        @unlink($certificatePath);
        @unlink($wwdrPath);

        $this->assertTrue($service->pkcs7Used, 'PKCS#7 signing should be used when CMS DER encoding is unavailable.');
        $this->assertNull($service->capturedCmsSignature, 'CMS signature should not be produced when DER encoding is unavailable.');
        $this->assertNotEmpty($signature, 'Signature should not be empty.');
        $this->assertSame(0x30, ord($signature[0]), 'PKCS#7 signature should start with a DER sequence.');
    }

    public function testItFallsBackToSaleTimestampWhenEventStartMissing(): void
    {
        $event = new Event(['name' => 'Unscheduled Meetup']);
        $event->setRelation('creatorRole', new Role(['timezone' => 'America/New_York']));

        $sale = new Sale(['event_date' => null]);
        $sale->created_at = Carbon::create(2024, 2, 1, 15, 0, 0, 'UTC');

        $service = $this->makeService(null);

        $resolved = $service->exposeResolveEventStart($event, $sale);

        $this->assertSame('America/New_York', $resolved->getTimezone()->getName(), 'Resolved start should respect event timezone.');
        $this->assertSame(
            $sale->created_at->timestamp,
            $resolved->copy()->setTimezone('UTC')->timestamp,
            'Fallback start should match the sale creation moment.'
        );
    }

    public function testItBuildsSignerCertificateChainFileWithIntermediate(): void
    {
        $password = 'secret-pass';
        $bundle = $this->generatePkcs12CertificateBundle($password);
        $wwdrPem = $this->generateStandaloneCertificatePem();

        $service = $this->makeService($password);

        $certificatePath = tempnam(sys_get_temp_dir(), 'wallet-test-cert-');
        $wwdrPath = tempnam(sys_get_temp_dir(), 'wallet-test-wwdr-');

        $this->assertNotFalse($certificatePath, 'Unable to create temporary certificate path.');
        $this->assertNotFalse($wwdrPath, 'Unable to create temporary WWDR path.');

        file_put_contents($certificatePath, $bundle['pkcs12']);
        file_put_contents($wwdrPath, $wwdrPem);

        $service->setCertificatePath($certificatePath);
        $service->setWwdrCertificatePath($wwdrPath);

        $certificates = $service->exposeLoadCertificates($bundle['pkcs12']);
        $chainPath = $service->exposeCreateSignerCertificateChainFile($certificates);

        @unlink($certificatePath);
        @unlink($wwdrPath);

        $this->assertNotNull($chainPath, 'Signer chain file should be generated.');

        $contents = $chainPath ? file_get_contents($chainPath) : false;

        if ($chainPath) {
            @unlink($chainPath);
        }

        $this->assertNotFalse($contents, 'Unable to read signer chain file.');
        $this->assertSame(
            2,
            substr_count((string) $contents, '-----BEGIN CERTIFICATE-----'),
            'Signer chain should include both the pass certificate and WWDR intermediate.'
        );
    }

    public function testItChoosesCertificateMatchingPrivateKeyFromPemBundles(): void
    {
        $password = 'secret-pass';
        $bundle = $this->generatePkcs12CertificateBundle($password);
        $wwdrPem = $this->generateStandaloneCertificatePem();

        $pemBundle = implode(PHP_EOL . PHP_EOL, [
            trim($wwdrPem),
            trim($bundle['certificate']),
            trim($bundle['private_key']),
        ]);

        $service = $this->makeService($password);
        $parsed = $service->exposeParsePemCertificateBundle($pemBundle, $password);

        $this->assertNotNull($parsed, 'PEM bundle should be parsed successfully.');
        $this->assertSame(
            trim($bundle['certificate']),
            trim($parsed['cert']),
            'Primary certificate should match the private key when parsing PEM bundles.'
        );
        $this->assertNotEmpty($parsed['extracerts'], 'Additional certificates should be retained.');
        $this->assertSame(
            trim($wwdrPem),
            trim($parsed['extracerts'][0]),
            'Remaining certificates should include the non-matching PEM blocks.'
        );
    }

    public function testItFallsBackToPkcs7WhenCmsSignatureIsNotDerEncoded(): void
    {
        if (! function_exists('openssl_cms_sign')) {
            $this->markTestSkipped('openssl_cms_sign not available in this environment.');
        }

        if (! defined('OPENSSL_ENCODING_DER')) {
            $this->markTestSkipped('OPENSSL_ENCODING_DER not available in this environment.');
        }

        $password = 'secret-pass';
        $bundle = $this->generatePkcs12CertificateBundle($password);

        $certificatePath = tempnam(sys_get_temp_dir(), 'wallet-test-cert-');
        $wwdrPath = tempnam(sys_get_temp_dir(), 'wallet-test-wwdr-');

        $this->assertNotFalse($certificatePath, 'Unable to create temporary certificate path.');
        $this->assertNotFalse($wwdrPath, 'Unable to create temporary WWDR path.');

        file_put_contents($certificatePath, $bundle['pkcs12']);
        file_put_contents($wwdrPath, $bundle['certificate']);

        $service = $this->makeService($password);
        $service->setCertificatePath($certificatePath);
        $service->setWwdrCertificatePath($wwdrPath);
        $service->setCmsSignatureAppearsDer(false);

        $signature = $service->exposeSignManifest(['pass.json' => sha1('payload')]);

        @unlink($certificatePath);
        @unlink($wwdrPath);

        $this->assertTrue($service->pkcs7Used, 'PKCS#7 signing should be used when CMS signature is not DER encoded.');
        $this->assertNotNull($service->capturedCmsSignature, 'CMS signature contents should be captured for inspection.');
        $this->assertNotEmpty($signature, 'Signature should not be empty.');
        $this->assertSame(0x30, ord($signature[0]), 'PKCS#7 signature should start with a DER sequence.');
    }

    protected function makeService(?string $password): AppleWalletServiceForTests
    {
        return new AppleWalletServiceForTests($password);
    }

    protected function generatePkcs12Certificate(string $password): string
    {
        return $this->generatePkcs12CertificateBundle($password)['pkcs12'];
    }

    /**
     * @return array{pkcs12: string, certificate: string, private_key: string}
     */
    protected function generatePkcs12CertificateBundle(string $password): array
    {
        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        $csr = openssl_csr_new(['commonName' => 'Event Schedule Test'], $privateKey);
        $certificate = openssl_csr_sign($csr, null, $privateKey, 365);

        $exported = '';
        $result = openssl_pkcs12_export($certificate, $exported, $privateKey, $password, [
            'friendly_name' => 'EventScheduleTest',
        ]);

        $this->assertTrue($result, 'Failed to export PKCS12 certificate for test.');

        $certificatePem = '';
        $pemExported = openssl_x509_export($certificate, $certificatePem);

        $this->assertTrue($pemExported, 'Failed to export certificate to PEM.');

        $privateKeyPem = '';
        $keyExported = openssl_pkey_export($privateKey, $privateKeyPem, $password);

        $this->assertTrue($keyExported, 'Failed to export private key to PEM.');

        return [
            'pkcs12' => $exported,
            'certificate' => $certificatePem,
            'private_key' => $privateKeyPem,
        ];
    }

    protected function generateStandaloneCertificatePem(): string
    {
        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        $this->assertNotFalse($privateKey, 'Failed to generate WWDR private key for test.');

        $csr = openssl_csr_new(['commonName' => 'EventSchedule WWDR Test'], $privateKey);

        $this->assertNotFalse($csr, 'Failed to create WWDR CSR for test.');

        $certificate = openssl_csr_sign($csr, null, $privateKey, 365);

        $this->assertNotFalse($certificate, 'Failed to sign WWDR certificate for test.');

        $pem = '';
        $exported = openssl_x509_export($certificate, $pem);

        $this->assertTrue($exported, 'Failed to export WWDR certificate to PEM.');

        return $pem;
    }
}

class AppleWalletServiceForTests extends AppleWalletService
{
    public bool $pkcs7Used = false;

    public ?string $capturedCmsSignature = null;

    private bool $cmsDerEncodingAvailable = true;

    private bool $cmsSignatureAppearsDerValue = true;

    public function __construct(?string $password)
    {
        $this->certificatePassword = $password;
        $this->enabled = true;
    }

    /**
     * @return array{cert: string|resource, pkey: string|resource}
     */
    public function exposeLoadCertificates(string $contents): array
    {
        return $this->loadCertificates($contents);
    }

    public function exposeCreatePassImage(Event $event, int $width, int $height): string
    {
        return $this->createPassImage($event, $width, $height);
    }

    public function exposeConvertSignatureToBinary(string $signature): string
    {
        return $this->convertSignatureToBinary($signature);
    }

    public function exposeCreateSignerCertificateChainFile(array $certificates): ?string
    {
        return $this->createSignerCertificateChainFile($certificates);
    }

    public function exposeParsePemCertificateBundle(string $contents, string $password): ?array
    {
        $errors = [];

        return $this->parsePemCertificateBundle($contents, $password, $errors);
    }

    public function setCertificatePath(?string $path): void
    {
        $this->certificatePath = $path;
    }

    public function setWwdrCertificatePath(?string $path): void
    {
        $this->wwdrCertificatePath = $path;
    }

    public function setCmsDerAvailable(bool $available): void
    {
        $this->cmsDerEncodingAvailable = $available;
    }

    public function setCmsSignatureAppearsDer(bool $appearsDer): void
    {
        $this->cmsSignatureAppearsDerValue = $appearsDer;
    }

    public function exposeSignManifest(array $manifest): string
    {
        $this->pkcs7Used = false;
        $this->capturedCmsSignature = null;

        return $this->signManifest($manifest);
    }

    public function exposeResolveEventStart(Event $event, Sale $sale): Carbon
    {
        return $this->resolveEventStart($event, $sale);
    }

    protected function cmsSupportsDerEncoding(): bool
    {
        if (! $this->cmsDerEncodingAvailable) {
            return false;
        }

        return parent::cmsSupportsDerEncoding();
    }

    protected function cmsSignatureAppearsDer(string $signature): bool
    {
        $this->capturedCmsSignature = $signature;

        if (! $this->cmsSignatureAppearsDerValue) {
            return false;
        }

        return parent::cmsSignatureAppearsDer($signature);
    }

    /**
     * @param  array{cert: mixed, pkey: mixed}  $certificates
     */
    protected function signManifestWithPkcs7(string $manifestPath, array $certificates, ?string $chainPath = null): string
    {
        $this->pkcs7Used = true;

        return parent::signManifestWithPkcs7($manifestPath, $certificates, $chainPath);
    }
}
