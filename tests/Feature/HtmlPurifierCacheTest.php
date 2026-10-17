<?php

namespace Tests\Feature;

use App\Utils\HtmlPurifierUtils;
use App\Utils\MarkdownUtils;
use HTMLPurifier;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Issue #131: a selfhost install gives the web user storage, bootstrap and public, and
 * HTMLPurifier's default cache is a folder inside vendor/. Its Serializer raises a warning
 * for a cache folder it cannot write, Laravel throws that as an ErrorException, and saving
 * an event was a 500 until the operator handed vendor/ to the web server.
 *
 * HTMLPurifier chmods a folder the process OWNS back to writable by itself, so the warning
 * only comes from a folder that belongs to somebody else. That is why the reporter's case
 * is reproduced with `/`, which belongs to root: a 0555 folder this test makes is not it.
 */
class HtmlPurifierCacheTest extends TestCase
{
    private ?string $tmp = null;

    protected function tearDown(): void
    {
        if ($this->tmp !== null && is_dir($this->tmp)) {
            foreach (File::directories($this->tmp) as $dir) {
                @chmod($dir, 0755);
            }
            File::deleteDirectory($this->tmp);
        }

        parent::tearDown();
    }

    private function tmpDir(): string
    {
        $this->tmp = sys_get_temp_dir().'/htmlpurifier-test-'.bin2hex(random_bytes(6));

        return $this->tmp;
    }

    public function test_the_definition_cache_is_kept_under_storage(): void
    {
        $this->assertSame(
            storage_path('framework/cache/htmlpurifier'),
            HtmlPurifierUtils::config()->get('Cache.SerializerPath'),
        );

        $this->assertSame("<p><strong>x</strong></p>\n", MarkdownUtils::convertToHtml('**x**'));
        $this->assertNotEmpty(glob(storage_path('framework/cache/htmlpurifier/HTML/*.ser')));
    }

    public function test_a_missing_cache_folder_is_made_and_filled(): void
    {
        $dir = $this->tmpDir();

        $html = (new HTMLPurifier(HtmlPurifierUtils::config($dir)))->purify('<b>x</b>');

        $this->assertSame('<b>x</b>', $html);
        $this->assertNotEmpty(glob($dir.'/HTML/*.ser'), 'the definition is cached in the folder it was given');
    }

    public function test_a_cache_folder_owned_by_somebody_else_turns_the_cache_off_instead_of_failing(): void
    {
        if (is_writable('/')) {
            $this->markTestSkipped('Running as a user that can write to /.');
        }

        $config = HtmlPurifierUtils::config('/');

        // Without the fallback this throws "Directory / not writable.", the reporter's error.
        $this->assertSame('<b>x</b>', (new HTMLPurifier($config))->purify('<b>x</b><script>y</script>'));
        $this->assertNull($config->get('Cache.DefinitionImpl'));
    }

    public function test_a_type_folder_the_web_user_cannot_write_turns_the_cache_off(): void
    {
        // A cron job or an artisan command run as root leaves HTML/ owned by root, and the
        // next release's config would be refused there. This holds the helper's decision
        // only: HTMLPurifier would chmod this folder back, because the test owns it.
        $dir = $this->tmpDir();
        mkdir($dir.'/HTML', 0755, true);
        chmod($dir.'/HTML', 0555);

        if (is_writable($dir.'/HTML')) {
            $this->markTestSkipped('Running as a user that can write to a 0555 folder.');
        }

        $this->assertNull(HtmlPurifierUtils::config($dir)->get('Cache.DefinitionImpl'));
    }

    public function test_every_purifier_is_configured_through_the_helper(): void
    {
        $offenders = [];

        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php' || $file->getRealPath() === realpath(app_path('Utils/HtmlPurifierUtils.php'))) {
                continue;
            }

            if (str_contains($file->getContents(), 'HTMLPurifier_Config::create')) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'Build the config with HtmlPurifierUtils::config(), or its cache is written into vendor/.');
    }
}
