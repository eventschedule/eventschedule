<?php

namespace Tests\Unit\Support;

use App\Support\MailConfigManager;
use Mockery;
use Tests\TestCase;

class MailConfigManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetAppliedHash();
    }

    protected function tearDown(): void
    {
        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');

        if (method_exists(app(), 'forgetResolvedInstance')) {
            app()->forgetResolvedInstance('mailer');
        }

        $this->resetAppliedHash();

        Mockery::close();

        parent::tearDown();
    }

    public function testApplyForceRebuildsMailerEvenWhenSettingsUnchanged(): void
    {
        config(['mail.default' => 'log']);

        $manager = Mockery::mock();
        $manager->shouldReceive('purge')->twice()->with('smtp')->andReturnNull();

        app()->instance('mail.manager', $manager);

        MailConfigManager::apply(['mailer' => 'smtp']);
        MailConfigManager::apply(['mailer' => 'smtp'], force: true);

        $this->assertSame('smtp', config('mail.default'));
    }

    private function resetAppliedHash(): void
    {
        $reflection = new \ReflectionClass(MailConfigManager::class);
        $property = $reflection->getProperty('appliedHash');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
}
