<?php

namespace Tests\Feature\Console;

use App\Services\ReleaseChannelService;
use Codedge\Updater\UpdaterManager;
use Illuminate\Support\Facades\Artisan as ArtisanFacade;
use Mockery;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Tests\TestCase;

class UpdateAppCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testCommandFailsWhenUpdaterReturnsFalse(): void
    {
        config([
            'app.hosted' => false,
            'self-update.release_channel' => 'production',
        ]);

        ArtisanFacade::shouldReceive('call')->never();

        $source = Mockery::mock();
        $updater = Mockery::mock(UpdaterManager::class);
        $releaseChannels = Mockery::mock(ReleaseChannelService::class);

        $updater->shouldReceive('source')->times(3)->andReturn($source);
        $source->shouldReceive('getVersionInstalled')->once()->andReturn('20251023-01p');

        $releaseChannels->shouldReceive('getLatestRelease')->once()->andReturn([
            'version' => '20251024-01p',
            'tag' => 'v20251024-01p',
        ]);

        $source->shouldReceive('fetch')->once()->with('v20251024-01p')->andReturn('release');
        $source->shouldReceive('update')->once()->with('release')->andReturn(false);

        $this->app->instance(UpdaterManager::class, $updater);
        $this->app->instance(ReleaseChannelService::class, $releaseChannels);

        $this->artisan('app:update')
            ->expectsOutput('Updating app via the Production (stable) channel. This can take a few minutes...')
            ->expectsOutput('The update could not be installed. Please check the application logs for more details.')
            ->assertExitCode(SymfonyCommand::FAILURE);
    }
}
