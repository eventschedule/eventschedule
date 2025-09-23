<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class AppControllerTest extends TestCase
{
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

    private function invokeCopyReleaseContents(AppController $controller, string $source, string $destination): void
    {
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('copyReleaseContents');
        $method->setAccessible(true);
        $method->invoke($controller, $source, $destination);
    }
}
