<?php

namespace Tests\Unit\Services\Wallet;

use App\Models\Event;
use App\Services\Wallet\AppleWalletService;
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

    protected function makeService(?string $password): AppleWalletServiceForTests
    {
        return new AppleWalletServiceForTests($password);
    }

    protected function generatePkcs12Certificate(string $password): string
    {
        return $this->generatePkcs12CertificateBundle($password)['pkcs12'];
    }

    /**
     * @return array{pkcs12: string, certificate: string}
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

        return [
            'pkcs12' => $exported,
            'certificate' => $certificatePem,
        ];
    }
}

class AppleWalletServiceForTests extends AppleWalletService
{
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

    public function setCertificatePath(?string $path): void
    {
        $this->certificatePath = $path;
    }

    public function setWwdrCertificatePath(?string $path): void
    {
        $this->wwdrCertificatePath = $path;
    }

    public function exposeSignManifest(array $manifest): string
    {
        return $this->signManifest($manifest);
    }
}
