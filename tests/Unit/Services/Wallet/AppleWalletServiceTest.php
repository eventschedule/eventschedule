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
        $pkcs12 = $this->generatePkcs12Certificate($password);
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

    protected function makeService(?string $password): AppleWalletServiceForTests
    {
        return new AppleWalletServiceForTests($password);
    }

    protected function generatePkcs12Certificate(string $password): string
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

        return $exported;
    }
}

class AppleWalletServiceForTests extends AppleWalletService
{
    public function __construct(?string $password)
    {
        $this->certificatePassword = $password;
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
}
