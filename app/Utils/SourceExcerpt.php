<?php

namespace App\Utils;

/**
 * A few lines of this application's own source, read off the disk of the server that is
 * answering, for the one page that prints them: /open-source ("every claim here has a file
 * path", so the page shows the file).
 *
 * An excerpt is found by what its lines SAY, never by a line number: Role.php is edited most
 * weeks, and a number would point at something else by the next release. When the words are not
 * there any more the answer is null, the page prints the claim without its code, and
 * OpenSourcePageTest fails the build, which is the point: a claim whose proof has left the file
 * should not survive on the page either.
 *
 * Nothing here takes a visitor's input. The paths are written in the view, the output is the same
 * for every visitor (the page is cached at the edge), and every character is escaped.
 */
class SourceExcerpt
{
    public const REPOSITORY = 'https://github.com/eventschedule/eventschedule';

    /** An excerpt is a quotation, not a listing: this many lines at most. */
    public const LONGEST = 60;

    /** Where source lives. Only a .php file under one of these is quoted. */
    private const SOURCE = ['app/', 'config/', 'routes/', 'database/migrations/', 'resources/views/', 'tests/'];

    /** The three other files the repository shows on purpose. */
    private const OPEN = ['license', 'composer.json', '.env.example'];

    /** @var array<string, list<string>|null> */
    private static array $read = [];

    /**
     * The lines of a repository file from the first one holding $from through the first one, at
     * or after it, holding $through (or that many lines, when a number).
     *
     * @param  list<array{0: string, 1?: int, 2?: string, 3?: bool}>  $marks  [words on a line, how many lines from it, a name, every such line?]
     * @return array{path: string, first: int, last: int, url: string, lines: list<array{n: int, html: string, in: int, mark: ?string}>}|null
     */
    public static function take(string $path, string $from, string|int $through, array $marks = [], int $after = 0): ?array
    {
        $all = self::read(base_path($path));

        if ($all === null || (is_int($through) && $through < 1)) {
            return null;
        }

        $start = self::find($all, $from, 0);

        if ($start === null) {
            return null;
        }

        // $after: the closing lines that follow the last words looked for (a brace or two).
        $end = is_int($through) ? $start + $through - 1 : self::find($all, $through, $start);

        return $end === null ? null : self::build($path, $all, $start, $end + max(0, $after), $marks);
    }

    /**
     * A whole method, found by reflection, so it is the method this process is running.
     *
     * @param  list<array{0: string, 1?: int, 2?: string, 3?: bool}>  $marks
     */
    public static function method(string $class, string $method, array $marks = []): ?array
    {
        try {
            $reflection = new \ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            return null;
        }

        $file = $reflection->getFileName();
        $real = $file ? realpath($file) : false;
        $root = realpath(base_path());

        if ($real === false || $root === false || ! str_starts_with($real, $root.DIRECTORY_SEPARATOR)) {
            return null;
        }

        $all = self::read($real);
        $start = $reflection->getStartLine() - 1;

        // Reflection speaks for the class as it was loaded, the disk for the file as it is now.
        // If the two have parted (a file replaced under a running process), say nothing.
        if ($all === null || ! str_contains($all[$start] ?? '', 'function '.$method.'(')) {
            return null;
        }

        return self::build(str_replace(DIRECTORY_SEPARATOR, '/', substr($real, strlen($root) + 1)), $all, $start, $reflection->getEndLine() - 1, $marks);
    }

    /**
     * The lines of a view between the two Blade comments that name a region of it
     * ("{{-- name --}}" and "{{-- /name --}}"), for a page that shows its own markup.
     *
     * @param  list<array{0: string, 1?: int, 2?: string, 3?: bool}>  $marks
     */
    public static function view(string $name, string $region, array $marks = []): ?array
    {
        try {
            $file = app('view')->getFinder()->find($name);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        // The view finder's own answer, not a path anybody passed: it may stand outside the
        // application (a package's views, a test's), and a Blade file is all it can be.
        $all = str_ends_with($file, '.blade.php') ? self::read($file, false) : null;

        if ($all === null) {
            return null;
        }

        $start = self::find($all, '{{-- '.$region.' --}}', 0);
        $end = $start === null ? null : self::find($all, '{{-- /'.$region.' --}}', $start + 1);

        // The two comments stand around the excerpt; they are not part of it.
        if ($start === null || $end === null || $end - $start < 2) {
            return null;
        }

        return self::build('resources/views/'.str_replace('.', '/', $name).'.blade.php', $all, $start + 1, $end - 1, $marks);
    }

    /**
     * A plain text file as its paragraphs, with its size.
     *
     * @return array{path: string, url: string, lines: int, words: int, paragraphs: list<string>}|null
     */
    public static function paragraphs(string $path): ?array
    {
        $all = self::read(base_path($path));

        if ($all === null) {
            return null;
        }

        $text = trim(implode("\n", $all));
        $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', $text) ?: [])));

        return [
            'path' => $path,
            'url' => self::REPOSITORY.'/blob/main/'.$path,
            'lines' => count($all),
            // Counted as `wc -w` counts: whatever stands between two spaces.
            'words' => count(preg_split('/\s+/', $text) ?: []),
            'paragraphs' => $paragraphs,
        ];
    }

    /**
     * Only source is quoted: a .php file where source lives, or one of the three files named
     * above. Not the install's settings, not what it wrote (a log, an upload, a database file),
     * not somebody else's package. The paths are written in the view, so this refuses nothing
     * today; it is here for the edit that passes the wrong one.
     */
    private static function quotable(string $path): bool
    {
        // Lower case: on a disk that does not tell ".ENV" from ".env", neither may this.
        $path = strtolower(ltrim(str_replace('\\', '/', $path), '/'));

        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        if (in_array($path, self::OPEN, true)) {
            return true;
        }

        foreach (self::SOURCE as $folder) {
            if (str_starts_with($path, $folder) && str_ends_with($path, '.php')) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string>|null */
    private static function read(string $file, bool $inRepository = true): ?array
    {
        // A path with a nul in it is no path (and realpath() would throw on it).
        if (str_contains($file, "\0")) {
            return null;
        }

        // Kept apart by how the file was asked for: one read as a view is not thereby readable
        // as a path.
        $key = ($inRepository ? 'r:' : 'v:').$file;

        if (! array_key_exists($key, self::$read)) {
            // Where the path really leads, links followed: still inside the application and
            // still source, or nothing.
            $real = realpath($file);
            $root = realpath(base_path());
            $inside = $real !== false && (! $inRepository || ($root !== false && str_starts_with($real, $root.DIRECTORY_SEPARATOR) && self::quotable(substr($real, strlen($root) + 1))));
            $lines = $inside && is_file($real) && is_readable($real) ? @file($real, FILE_IGNORE_NEW_LINES) : false;
            self::$read[$key] = $lines === false ? null : $lines;
        }

        return self::$read[$key];
    }

    /** @param  list<string>  $lines */
    private static function find(array $lines, string $needle, int $from): ?int
    {
        if ($needle === '') {
            return null;
        }

        for ($i = max(0, $from), $n = count($lines); $i < $n; $i++) {
            if (str_contains($lines[$i], $needle)) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $all
     * @param  list<array{0: string, 1?: int, 2?: string, 3?: bool}>  $marks
     */
    private static function build(string $path, array $all, int $start, int $end, array $marks): ?array
    {
        if ($start < 0 || $end < $start || $end >= count($all) || $end - $start + 1 > self::LONGEST) {
            return null;
        }

        $php = str_ends_with($path, '.php') && ! str_ends_with($path, '.blade.php');

        // PHP is coloured by its own tokenizer, which has to start where code starts. A
        // quotation that began inside a comment block would be read as code, and an apostrophe
        // in it would colour everything after as a string. Such a quotation is refused: start
        // it on the code.
        if ($php && self::insideComment($all, $start)) {
            return null;
        }

        $raw = array_slice($all, $start, $end - $start + 1);

        // Out to the left edge: the excerpt keeps its own shape, not its depth in the file.
        $indent = null;
        foreach ($raw as $line) {
            if (trim($line) === '') {
                continue;
            }
            $depth = strlen($line) - strlen(ltrim($line, ' '));
            $indent = $indent === null ? $depth : min($indent, $depth);
        }
        $raw = array_map(fn ($line) => trim($line) === '' ? '' : substr($line, (int) $indent), $raw);

        $marked = array_fill(0, count($raw), null);
        foreach ($marks as $mark) {
            [$needle, $span, $name, $every] = [$mark[0], max(1, $mark[1] ?? 1), $mark[2] ?? 'proof', $mark[3] ?? false];
            $at = self::find($raw, $needle, 0);

            // The first line that holds the words, or every one of them when asked.
            while ($at !== null) {
                for ($i = $at; $i < min(count($raw), $at + $span); $i++) {
                    $marked[$i] = $name;
                }
                $at = $every ? self::find($raw, $needle, $at + $span) : null;
            }
        }

        $html = match (true) {
            str_ends_with($path, '.blade.php') => array_map([self::class, 'blade'], $raw),
            $php => self::php($raw),
            str_contains($path, '.env') => array_map([self::class, 'env'], $raw),
            default => array_map('e', $raw),
        };

        // A line's own indent is handed over as a number and taken off its text, so a line too
        // long for a phone folds under itself, a step in, and nothing has to scroll sideways.
        $lines = [];
        foreach ($raw as $i => $line) {
            $depth = strlen($line) - strlen(ltrim($line, ' '));
            $text = $html[$i] ?? e($line);
            if ($depth > 0) {
                $text = preg_replace('/^((?:<span class="os-[a-z]">)?) {'.$depth.'}/', '$1', $text) ?? $text;
            }
            $lines[] = ['n' => $start + $i + 1, 'html' => $text, 'in' => $depth, 'mark' => $marked[$i]];
        }

        return [
            'path' => $path,
            'first' => $start + 1,
            'last' => $end + 1,
            'url' => self::REPOSITORY.'/blob/main/'.$path,
            'lines' => $lines,
        ];
    }

    /**
     * Is this line of a PHP file inside a comment block that opened above it? Looked for upward,
     * a few hundred lines at most: a line that BEGINS a block and does not end it says yes, a
     * line that ENDS one says no. Only the two ends of a line are read, so the same two
     * characters inside a string (a glob, a mime type) decide nothing.
     *
     * @param  list<string>  $all
     */
    private static function insideComment(array $all, int $line): bool
    {
        for ($i = $line - 1, $stop = max(0, $line - 300); $i >= $stop; $i--) {
            $text = trim($all[$i]);
            $opens = str_starts_with($text, '/*');
            $closes = str_ends_with($text, '*/');

            if ($opens || $closes) {
                return $opens && ! $closes;
            }
        }

        return false;
    }

    /**
     * PHP's own tokenizer decides what is a string and what is a comment, so nothing here can
     * colour a quote mark inside a comment as the start of a string.
     *
     * @param  list<string>  $raw
     * @return list<string>
     */
    private static function php(array $raw): array
    {
        static $keywords = null;
        $keywords ??= array_flip(array_filter(array_map(fn ($name) => defined($name) ? constant($name) : null, [
            'T_ABSTRACT', 'T_ARRAY', 'T_AS', 'T_BREAK', 'T_CASE', 'T_CATCH', 'T_CLASS', 'T_CONST', 'T_CONTINUE',
            'T_DEFAULT', 'T_ECHO', 'T_ELSE', 'T_ELSEIF', 'T_EMPTY', 'T_EXTENDS', 'T_FINAL', 'T_FINALLY', 'T_FN',
            'T_FOR', 'T_FOREACH', 'T_FUNCTION', 'T_IF', 'T_IMPLEMENTS', 'T_INSTANCEOF', 'T_ISSET', 'T_MATCH',
            'T_NAMESPACE', 'T_NEW', 'T_PRIVATE', 'T_PROTECTED', 'T_PUBLIC', 'T_READONLY', 'T_RETURN', 'T_STATIC',
            'T_SWITCH', 'T_THROW', 'T_TRY', 'T_USE', 'T_WHILE',
        ])));

        try {
            $tokens = token_get_all("<?php\n".implode("\n", $raw));
        } catch (\Throwable $e) {
            return array_map('e', $raw);
        }

        array_shift($tokens);

        // One span to a line: a token that runs over several lines (a docblock) is closed at the
        // end of each and opened again on the next, so no line leaves a span open.
        $out = [''];
        $put = function (string $text, ?string $class) use (&$out) {
            foreach (explode("\n", $text) as $i => $part) {
                if ($i > 0) {
                    $out[] = '';
                }
                if ($part !== '') {
                    $out[count($out) - 1] .= $class ? '<span class="os-'.$class.'">'.e($part).'</span>' : e($part);
                }
            }
        };

        foreach ($tokens as $i => $token) {
            if (is_string($token)) {
                $put($token, null);

                continue;
            }

            [$id, $text] = $token;
            $class = null;

            if ($id === T_COMMENT || $id === T_DOC_COMMENT) {
                $class = 'c';
            } elseif ($id === T_CONSTANT_ENCAPSED_STRING || $id === T_ENCAPSED_AND_WHITESPACE) {
                $class = 's';
            } elseif ($id === T_VARIABLE) {
                $class = 'v';
            } elseif ($id === T_LNUMBER || $id === T_DNUMBER) {
                $class = 'n';
            } elseif (isset($keywords[$id])) {
                $class = 'k';
            } elseif ($id === T_STRING) {
                $next = $tokens[$i + 1] ?? null;
                if (in_array(strtolower($text), ['true', 'false', 'null'], true)) {
                    $class = 'n';
                } elseif ($next === '(') {
                    $class = 'f';
                } elseif (ctype_upper($text[0])) {
                    $class = 't';
                }
            }

            $put($text, $class);
        }

        return array_pad(array_slice($out, 0, count($raw)), count($raw), '');
    }

    private static function blade(string $line): string
    {
        $parts = preg_split('/(\{\{--.*?--\}\}|\{\{.*?\}\}|\{!!.*?!!\}|"[^"]*"|<\/?[\w.:-]+|\/?>)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$line];
        $html = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $class = match (true) {
                str_starts_with($part, '{{--') => 'c',
                str_starts_with($part, '{{'), str_starts_with($part, '{!!') => 'v',
                $part[0] === '"' => 's',
                $part[0] === '<', $part === '>', $part === '/>' => 'k',
                default => null,
            };
            $html .= $class ? '<span class="os-'.$class.'">'.e($part).'</span>' : e($part);
        }

        return $html;
    }

    private static function env(string $line): string
    {
        if (str_starts_with(ltrim($line), '#')) {
            return '<span class="os-c">'.e($line).'</span>';
        }

        if (preg_match('/^([A-Z0-9_]+)(=)(.*)$/', $line, $m)) {
            return '<span class="os-v">'.e($m[1]).'</span>'.e($m[2]).'<span class="os-s">'.e($m[3]).'</span>';
        }

        return e($line);
    }
}
