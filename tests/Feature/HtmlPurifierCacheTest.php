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

    public function test_the_definition_cache_is_kept_under_storage_in_a_folder_for_its_version(): void
    {
        // A fresh storage folder, so the glob below can only find what this render wrote.
        $this->app->useStoragePath($this->tmpDir());
        $dir = storage_path('framework/cache/htmlpurifier/'.HTMLPurifier::VERSION);

        // The version is in the path because the Serializer unlinks another version's files
        // on every miss, and two requests unlinking the same one is a warning, and a 500.
        $this->assertSame($dir, HtmlPurifierUtils::config()->get('Cache.SerializerPath'));

        $this->assertSame("<p><strong>x</strong></p>\n", MarkdownUtils::convertToHtml('**x**'));
        $this->assertNotEmpty(glob($dir.'/HTML/*.ser'));
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

    /**
     * Every way to reach HTMLPurifier_Config::createDefault() without the helper. The
     * constructor passes what it is given to HTMLPurifier_Config::create(), which builds the
     * default for null, an array or a string. purify() and purifyArray() do the same with a
     * config that is truthy, and use the instance's own for null or an empty array.
     *
     * Comments are taken out first, and PHP's class names ignore case. What this cannot see:
     * a literal config passed through a variable, a purify() whose named arguments put the
     * config first, and a purifier resolved from the container.
     */
    private const DEFAULT_CONFIG_PATTERNS = [
        'a config built directly' => '/HTMLPurifier_Config::(?:create\w*|inherit)\s*\(|new\s+\\\\?HTMLPurifier_Config\b/i',
        'new HTMLPurifier without a config object' => '/new\s+\\\\?HTMLPurifier\b(?!\s*\(\s*(?:config\s*:\s*)?(?:\$|\\\\?(?:App\\\\Utils\\\\)?HtmlPurifierUtils::config\s*\())/i',
        'the shared instance' => '/HTMLPurifier::(?:getInstance|instance)\s*\(/i',
        // The first argument may hold calls of its own: (?1) is a balanced pair of parentheses.
        'purify() given a literal config' => '/->purify(?:Array)?\s*\((?:[^(),]++|(\((?:[^()]++|(?1))*\)))*,\s*(?:config\s*:\s*)?(?:\[(?!\s*\])|\'|"|array\s*\((?!\s*\)))/i',
    ];

    private function defaultConfigUses(string $code): array
    {
        $code = $this->withoutComments($code);
        $uses = [];

        foreach (self::DEFAULT_CONFIG_PATTERNS as $use => $pattern) {
            $found = preg_match($pattern, $code);

            // preg_match() answers false on a PCRE error, which would read as nothing found.
            $this->assertNotFalse($found, "\"$use\" could not be matched: ".preg_last_error_msg());

            if ($found === 1) {
                $uses[] = $use;
            }
        }

        return $uses;
    }

    private function withoutComments(string $code): string
    {
        $kept = '';

        foreach (token_get_all(str_starts_with($code, '<?php') ? $code : '<?php '.$code) as $token) {
            if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            $kept .= is_array($token) ? $token[1] : $token;
        }

        return $kept;
    }

    public function test_the_scan_knows_every_way_to_get_the_default_config(): void
    {
        $caught = [
            'new HTMLPurifier();',
            'new HTMLPurifier;',
            'new \HTMLPurifier([\'HTML.Allowed\' => \'b\']);',
            'HTMLPurifier::instance();',
            'HTMLPurifier::getInstance();',
            'HTMLPurifier_Config::createDefault();',
            'HTMLPurifier_Config::inherit($config);',
            'new HTMLPurifier_Config($schema);',
            '$purifier->purify($html, [\'Core.Encoding\' => \'UTF-8\']);',
            '$purifier->purify(trim($html, "x"), \'HTML.Allowed=b\');',
            '$purifier->purify(trim(strip_tags($html)), [\'HTML.Allowed\' => \'b\']);',
            '$purifier->purify($html, config: [\'HTML.Allowed\' => \'b\']);',
            '$purifier->purifyArray($items, [\'HTML.Allowed\' => \'b\']);',
            'new HTMLPurifier(null);',
            'new htmlpurifier();',
        ];
        $allowed = [
            'new HTMLPurifier($config);',
            'new HTMLPurifier( $this->config );',
            'new HTMLPurifier(HtmlPurifierUtils::config());',
            'new \HTMLPurifier(\App\Utils\HtmlPurifierUtils::config($dir));',
            'new HTMLPurifier(config: $config);',
            '$purifier->purify($html);',
            '$purifier->purify(trim($html, "x"));',
            '$purifier->purify($html, $config);',
            '$purifier->purify(trim(strip_tags($html)), $config);',
            '$purifier->purify($html, null);',
            '$config = HtmlPurifierUtils::config();',
            '// one new HTMLPurifier per call, built from HTMLPurifier_Config::createDefault()',
            '/** The shared HTMLPurifier::instance() would cache in vendor/. */',
        ];

        foreach ($caught as $code) {
            $this->assertNotSame([], $this->defaultConfigUses($code), $code);
        }

        foreach ($allowed as $code) {
            $this->assertSame([], $this->defaultConfigUses($code), $code);
        }
    }

    public function test_every_purifier_is_configured_through_the_helper(): void
    {
        $offenders = [];

        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php' || $file->getRealPath() === realpath(app_path('Utils/HtmlPurifierUtils.php'))) {
                continue;
            }

            foreach ($this->defaultConfigUses($file->getContents()) as $use) {
                $offenders[] = $file->getRelativePathname().': '.$use;
            }
        }

        $this->assertSame([], $offenders, 'Build the config with HtmlPurifierUtils::config(), or its cache is written into vendor/.');
    }
}
