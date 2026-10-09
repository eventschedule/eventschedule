<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Utils\SourceExcerpt;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * SourceExcerpt quotes this application's own files onto a public page. Two things must hold:
 * what it prints is the file, character for character, and it cannot be made to print a file
 * that is not the repository's to show.
 */
class SourceExcerptTest extends TestCase
{
    private function text(array $excerpt): string
    {
        return implode("\n", array_map(
            fn ($line) => str_repeat(' ', $line['in']).html_entity_decode(strip_tags($line['html']), ENT_QUOTES | ENT_HTML5),
            $excerpt['lines']
        ));
    }

    public function test_a_method_is_quoted_character_for_character(): void
    {
        $excerpt = SourceExcerpt::method(Role::class, 'isPro');
        $method = new \ReflectionMethod(Role::class, 'isPro');
        $lines = array_slice(file($method->getFileName(), FILE_IGNORE_NEW_LINES), $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);

        $this->assertSame('app/Models/Role.php', $excerpt['path']);
        $this->assertSame($method->getStartLine(), $excerpt['first']);
        $this->assertSame($method->getEndLine(), $excerpt['last']);
        // Out to the left edge: the method keeps its shape, not its depth in the class.
        $this->assertSame(implode("\n", array_map(fn ($line) => $line === '' ? '' : substr($line, 4), $lines)), $this->text($excerpt));
        $this->assertSame('https://github.com/eventschedule/eventschedule/blob/main/app/Models/Role.php', $excerpt['url']);
    }

    public function test_lines_are_found_by_their_words_and_marked_by_theirs(): void
    {
        $excerpt = SourceExcerpt::take('config/app.php', "'supported_languages' => [", '],', [["'he' =>", 1, 'hebrew']]);

        $this->assertStringContainsString("'supported_languages' => [", $this->text($excerpt));
        $this->assertStringEndsWith('],', $this->text($excerpt));
        $this->assertCount(count(config('app.supported_languages')) + 2, $excerpt['lines']);

        $marked = array_values(array_filter($excerpt['lines'], fn ($line) => $line['mark'] !== null));
        $this->assertCount(1, $marked);
        $this->assertSame('hebrew', $marked[0]['mark']);
        $this->assertStringContainsString("'he' => 'hebrew'", html_entity_decode(strip_tags($marked[0]['html']), ENT_QUOTES));
    }

    public function test_what_is_not_there_is_null_not_a_guess(): void
    {
        $this->assertNull(SourceExcerpt::take('no/such/file.php', 'x', 3));
        $this->assertNull(SourceExcerpt::take('config/app.php', 'words that are in no file 9f3c', 3));
        $this->assertNull(SourceExcerpt::take('config/app.php', "'supported_languages' => [", 'an ending that is in no file 9f3c'));
        $this->assertNull(SourceExcerpt::take('config/app.php', "'supported_languages' => [", SourceExcerpt::LONGEST + 1), 'a quotation, not a listing');
        $this->assertNull(SourceExcerpt::take('config/app.php', "'supported_languages' => [", 0));
        $this->assertNull(SourceExcerpt::take('LICENSE', 'THIS FREE SOFTWARE', 30), 'past the end of the file');
        $this->assertNull(SourceExcerpt::method(Role::class, 'noSuchMethod'));
        $this->assertNull(SourceExcerpt::view('marketing.no-such-view', 'source:headline'));
        $this->assertNull(SourceExcerpt::view('marketing.open-source', 'source:no-such-region'));
        $this->assertNull(SourceExcerpt::paragraphs('no-such-file'));
    }

    public function test_it_quotes_source_and_nothing_else(): void
    {
        // The paths are written in the view today. This is for the edit that passes the wrong
        // one. Each of these files EXISTS and holds the words asked for, so a null here is the
        // refusal and not a file that was not found.
        foreach ([
            'vendor/autoload.php' => '<?php',
            'package.json' => '"',
            'public/index.php' => '<?php',
            'phpunit.xml' => '<',
            'artisan' => '<?php',
        ] as $path => $words) {
            $this->assertFileExists(base_path($path));
            $this->assertStringContainsString($words, file_get_contents(base_path($path)));
            $this->assertNull(SourceExcerpt::take($path, $words, 1), "{$path} is not source and must not be quotable");
            $this->assertNull(SourceExcerpt::paragraphs($path), "{$path} is not source and must not be quotable");
        }

        // These need not exist on every machine; where they do, they hold what is asked for.
        foreach (['.env' => 'APP_', '.ENV' => 'APP_', 'app/../.env' => 'APP_', 'storage/logs/laravel.log' => ' ', 'database/database.sqlite' => 'a', "config/app.php\0.txt" => 'a', '/etc/hosts' => 'localhost'] as $path => $words) {
            $this->assertNull(SourceExcerpt::take($path, $words, 1), 'not quotable: '.json_encode($path));
        }

        $this->assertNull(SourceExcerpt::method(Str::class, 'slug'), 'a package is somebody else\'s to show');
        $this->assertNotNull(SourceExcerpt::take('.env.example', 'APP_NAME=', 1), 'the sample file is in the repository');
        $this->assertNotNull(SourceExcerpt::paragraphs('LICENSE'));
    }

    public function test_a_quotation_does_not_start_inside_a_comment(): void
    {
        // PHP is coloured by its own tokenizer, which must start on code: a quotation that began
        // inside a comment block would be coloured as code, so it is refused.
        $this->assertNull(SourceExcerpt::take('config/self-update.php', '| Version installed', 3));
        $this->assertNotNull(SourceExcerpt::take('config/self-update.php', "'version_installed' =>", 1));
        // The same two characters inside a string are not a comment.
        $this->assertNotNull(SourceExcerpt::take('app/Utils/UrlUtils.php', 'public static function isBlockedIp(', 3));
    }

    public function test_everything_it_prints_is_escaped_and_every_span_is_closed_on_its_line(): void
    {
        $excerpts = [
            SourceExcerpt::view('marketing.open-source', 'source:headline'),
            // A method with markup inside its strings.
            SourceExcerpt::take('app/Utils/UrlUtils.php', 'public static function convertUrlsToLinks', 30),
            SourceExcerpt::take('.env.example', '# OneSignal push notifications', 'ONESIGNAL_REST_API_KEY='),
            SourceExcerpt::method(Role::class, 'isPro'),
        ];

        foreach ($excerpts as $excerpt) {
            $this->assertNotNull($excerpt);

            foreach ($excerpt['lines'] as $line) {
                $this->assertDoesNotMatchRegularExpression('/<(?!\/?span\b)/', $line['html'], 'only the spans this class writes may be markup');
                $this->assertDoesNotMatchRegularExpression('/<span(?! class="os-[a-z]">)/', $line['html']);
                $this->assertSame(substr_count($line['html'], '<span'), substr_count($line['html'], '</span>'), 'a span left open would colour the rest of the page');
            }
        }
    }

    public function test_a_plain_file_comes_as_its_paragraphs_with_its_size(): void
    {
        $licence = SourceExcerpt::paragraphs('LICENSE');
        $file = file_get_contents(base_path('LICENSE'));

        $this->assertSame(count(file(base_path('LICENSE'))), $licence['lines']);
        $this->assertSame(count(preg_split('/\s+/', trim($file))), $licence['words']);
        $this->assertSame(trim($file), implode("\n\n", $licence['paragraphs']));
    }
}
