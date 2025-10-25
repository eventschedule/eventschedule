<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\AppController;
use App\Services\ReleaseChannelService;
use Codedge\Updater\UpdaterManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mockery;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class AppControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testCopyReleaseContentsReportsAllUnwritableDirectories(): void
    {
        $root = storage_path('framework/testing/copy-release-' . (string) Str::uuid());
        $source = $root . '/source';
        $destination = $root . '/destination';

        File::ensureDirectoryExists($source);
        File::ensureDirectoryExists($destination);

        File::ensureDirectoryExists($source . '/unwritable-one');
        File::ensureDirectoryExists($source . '/unwritable-two');
        File::ensureDirectoryExists($source . '/writable');

        File::put($source . '/unwritable-one/first.txt', 'first');
        File::put($source . '/unwritable-two/second.txt', 'second');
        File::put($source . '/writable/third.txt', 'third');

        File::ensureDirectoryExists($destination . '/unwritable-one');
        File::ensureDirectoryExists($destination . '/unwritable-two');

        chmod($destination . '/unwritable-one', 0555);
        chmod($destination . '/unwritable-two', 0555);

        $controller = new AppController();

        try {
            $exception = null;

            try {
                $this->invokeCopyReleaseContents($controller, $source, $destination);
            } catch (RuntimeException $caught) {
                $exception = $caught;
            }

            $this->assertInstanceOf(RuntimeException::class, $exception);
            $this->assertStringContainsString('unwritable-one, unwritable-two', $exception->getMessage());
            $this->assertStringContainsString('Please adjust the directory permissions and try again.', $exception->getMessage());

            $this->assertFalse(File::exists($destination . '/unwritable-one/first.txt'));
            $this->assertFalse(File::exists($destination . '/unwritable-two/second.txt'));
            $this->assertFalse(File::exists($destination . '/writable/third.txt'));
        } finally {
            if (is_dir($destination . '/unwritable-one')) {
                chmod($destination . '/unwritable-one', 0755);
            }

            if (is_dir($destination . '/unwritable-two')) {
                chmod($destination . '/unwritable-two', 0755);
            }

            if (is_dir($root)) {
                File::deleteDirectory($root);
            }
        }
    }

    public function testOnlineUpdateReportsFailureWhenUpdaterReturnsFalse(): void
    {
        config([
            'app.hosted' => false,
            'self-update.release_channel' => 'production',
        ]);

        Artisan::shouldReceive('call')->never();

        $source = Mockery::mock();
        $updater = Mockery::mock(UpdaterManager::class);
        $releaseChannels = Mockery::mock(ReleaseChannelService::class);

        $updater->shouldReceive('source')->times(3)->andReturn($source);
        $source->shouldReceive('getVersionInstalled')->once()->andReturn('1.0.0');

        $releaseChannels->shouldReceive('getLatestRelease')->once()->andReturn([
            'version' => '2.0.0',
            'tag' => 'v2.0.0',
        ]);

        $source->shouldReceive('fetch')->once()->with('v2.0.0')->andReturn('release');
        $source->shouldReceive('update')->once()->with('release')->andReturn(false);

        $this->app->instance(UpdaterManager::class, $updater);
        $this->app->instance(ReleaseChannelService::class, $releaseChannels);

        $response = $this->from('/update')->post('/update');

        $response->assertRedirect('/update');
        $response->assertSessionHas('error', 'The update could not be installed. Please check the application logs for more details.');
    }

    private function invokeCopyReleaseContents(AppController $controller, string $source, string $destination): void
    {
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('copyReleaseContents');
        $method->setAccessible(true);
        $method->invoke($controller, $source, $destination);
    }
}
