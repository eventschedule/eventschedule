<?php

namespace Tests\Unit\Support;

use App\Support\WalletConfigManager;
use Tests\TestCase;

class WalletConfigManagerTest extends TestCase
{
    public function testApplyAppleTrimsStringValues(): void
    {
        $original = config('wallet.apple');

        try {
            config(['wallet.apple' => [
                'enabled' => false,
            ]]);

            WalletConfigManager::applyApple([
                'enabled' => '1',
                'pass_type_identifier' => ' pass.com.eventschedule.demo ',
                'team_identifier' => " TEAM42 \n",
                'organization_name' => " Demo Org \t",
                'background_color' => "  rgb(1,2,3)  ",
                'foreground_color' => " rgb(4,5,6) ",
                'label_color' => " rgb(7,8,9) ",
                'certificate_password' => " secret ",
                'certificate_path' => " wallet/apple/cert.p12 ",
                'wwdr_certificate_path' => " wallet/apple/wwdr.pem ",
            ]);

            $config = config('wallet.apple');

            $this->assertTrue($config['enabled']);
            $this->assertSame('pass.com.eventschedule.demo', $config['pass_type_identifier']);
            $this->assertSame('TEAM42', $config['team_identifier']);
            $this->assertSame('Demo Org', $config['organization_name']);
            $this->assertSame('rgb(1,2,3)', $config['background_color']);
            $this->assertSame('rgb(4,5,6)', $config['foreground_color']);
            $this->assertSame('rgb(7,8,9)', $config['label_color']);
            $this->assertSame('secret', $config['certificate_password']);
            $this->assertSame(storage_path('app/wallet/apple/cert.p12'), $config['certificate_path']);
            $this->assertSame(storage_path('app/wallet/apple/wwdr.pem'), $config['wwdr_certificate_path']);
        } finally {
            config(['wallet.apple' => $original]);
        }
    }

    public function testApplyGoogleTrimsStringValues(): void
    {
        $original = config('wallet.google');

        try {
            config(['wallet.google' => []]);

            WalletConfigManager::applyGoogle([
                'enabled' => 'true',
                'issuer_id' => " ISSUER123 \n",
                'issuer_name' => " Demo Issuer  ",
                'class_suffix' => " event  ",
                'service_account_json' => " {\"demo\":true}  ",
                'service_account_json_path' => " wallet/google/service-account.json ",
            ]);

            $config = config('wallet.google');

            $this->assertTrue($config['enabled']);
            $this->assertSame('ISSUER123', $config['issuer_id']);
            $this->assertSame('Demo Issuer', $config['issuer_name']);
            $this->assertSame('event', $config['class_suffix']);
            $this->assertSame('{"demo":true}', $config['service_account_json']);
            $this->assertSame(storage_path('app/wallet/google/service-account.json'), $config['service_account_json_path']);
        } finally {
            config(['wallet.google' => $original]);
        }
    }
}
