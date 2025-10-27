<?php

namespace Tests\Unit\Services\Wallet;

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
}
