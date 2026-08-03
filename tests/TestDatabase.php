<?php

namespace Tests;

use Dotenv\Dotenv;
use FilesystemIterator;
use PDO;
use PDOException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Gives every Claude Code session - and any other concurrent test run - its own MySQL schema.
 *
 * Without this, two runs share eventschedule_test, and RefreshDatabase's migrate:fresh drops all
 * the tables out from under whichever run started first. That does not fail cleanly: the first
 * run's open transaction holds metadata locks, so the second run's DROP TABLE blocks for
 * lock_wait_timeout - a year by default - and the suite hangs. See the note in
 * tests/Feature/RouteLoadTest.php.
 *
 * Called from tests/bootstrap.php, which PHPUnit loads *after* it applies the <env> block in
 * phpunit.xml (TextUI/Application.php runs PhpHandler, then loads the bootstrap script). That
 * ordering is what lets this override DB_DATABASE despite its force="true" - and the force stays,
 * because the name here is derived from the forced value rather than taken from the shell.
 *
 * Laravel's Env repository is immutable, so values set here survive .env loading. Its adapters
 * read $_SERVER, then $_ENV, then getenv(), so each one has to be written to all three.
 */
class TestDatabase
{
    /** How long to wait for another run that already holds this schema. */
    private const LOCK_TIMEOUT_SECONDS = 1200;

    /** How long a schema may sit unused before a later run reclaims it. */
    private const DEFAULT_PRUNE_DAYS = 3;

    /**
     * Held open for the lifetime of the process so the lock is not released early. The OS drops
     * it on exit, including on a kill, so there is no such thing as a stale lock here.
     *
     * @var resource|null
     */
    private static $lockHandle = null;

    public static function bootstrap(string $basePath): void
    {
        $basePath = rtrim($basePath, DIRECTORY_SEPARATOR);

        // phpunit.xml has already forced this; the per-session name is built from it.
        $base = getenv('DB_DATABASE') ?: 'eventschedule_test';
        $token = self::resolveToken();

        $database = $token === null ? $base : $base.'_'.$token;

        self::acquireLock($basePath, $database);

        if ($token !== null) {
            $credentials = self::credentials($basePath);
            $connection = self::connect($credentials);

            self::createDatabase($connection, $database, $base, $credentials['username']);
            self::pruneStaleDatabases($connection, $basePath, $base, $database);

            self::publish('LANG_OVERRIDES_PATH', 'storage/framework/testing/lang_'.$token);

            // Roots every Storage::fake() disk at ..._test_<token>, which otherwise gets wiped
            // by a concurrent run. Deliberately not paired with LARAVEL_PARALLEL_TESTING:
            // ParallelTesting::inParallel() requires both, and leaving that one unset keeps
            // Laravel's own per-worker database switching dormant.
            self::publish('TEST_TOKEN', $token);
        }

        self::publish('DB_DATABASE', $database);
    }

    /**
     * A short, stable, filesystem- and identifier-safe id for this test run.
     *
     * CLAUDE_CODE_SESSION_ID is exported into every Bash call a session makes, and its first
     * segment also names the session's scratchpad directory, so 8 characters is enough to trace a
     * schema back to the session that owns it. An explicitly chosen TEST_DB_TOKEN gets more room,
     * because branch-shaped names collide badly at 8 (`my-feature-a` and `my-feature-b` both
     * reduce to `myfeatur`). With neither variable set - CI, or a plain terminal - this returns
     * null and everything behaves exactly as it did before.
     */
    private static function resolveToken(): ?string
    {
        foreach (['TEST_DB_TOKEN' => 16, 'CLAUDE_CODE_SESSION_ID' => 8] as $variable => $length) {
            $value = (string) getenv($variable);

            if ($value === '') {
                continue;
            }

            $token = substr(strtolower((string) preg_replace('/[^A-Za-z0-9]/', '', $value)), 0, $length);

            if ($token !== '') {
                return $token;
            }
        }

        return null;
    }

    /**
     * Serialise anything that would otherwise share this schema, such as a second run inside the
     * same session. Blocking here costs a wait; not blocking costs a year-long metadata lock.
     *
     * The handle is also what pruneStaleDatabases() probes to tell a live schema from an
     * abandoned one, so a run that cannot take its lock says so rather than failing quietly.
     */
    private static function acquireLock(string $basePath, string $database): void
    {
        $directory = dirname(self::lockPath($basePath, $database));

        if (! is_dir($directory) && ! @mkdir($directory, 0777, true) && ! is_dir($directory)) {
            self::warn("could not create {$directory}, so this run is not protected against another run using {$database}.");

            return;
        }

        $handle = @fopen(self::lockPath($basePath, $database), 'c');

        if ($handle === false) {
            self::warn("could not open a lock file for {$database}, so this run is not protected against another run using it.");

            return;
        }

        self::$lockHandle = $handle;

        if (flock($handle, LOCK_EX | LOCK_NB)) {
            return;
        }

        fwrite(STDERR, "Another test run is using {$database}; waiting for it to finish...".PHP_EOL);

        $deadline = time() + self::LOCK_TIMEOUT_SECONDS;

        while (time() < $deadline) {
            sleep(2);

            if (flock($handle, LOCK_EX | LOCK_NB)) {
                return;
            }
        }

        self::fail(
            'Gave up after '.self::LOCK_TIMEOUT_SECONDS." seconds waiting for the test run holding {$database}."
        );
    }

    private static function lockPath(string $basePath, string $database): string
    {
        return $basePath.'/storage/framework/testing/locks/'.$database.'.lock';
    }

    /**
     * Is another run using this schema right now?
     *
     * PHP's flock() maps to flock(2), whose locks belong to the open file description rather than
     * the process, so this probe neither succeeds against a live holder nor releases one when it
     * closes its own handle.
     */
    private static function isInUse(string $basePath, string $database): bool
    {
        $path = self::lockPath($basePath, $database);

        if (! is_file($path)) {
            return false;
        }

        $handle = @fopen($path, 'r');

        if ($handle === false) {
            return false;
        }

        $free = flock($handle, LOCK_EX | LOCK_NB);

        if ($free) {
            flock($handle, LOCK_UN);
        }

        fclose($handle);

        return ! $free;
    }

    /**
     * Read the connection details straight from .env: Laravel has not booted yet, so config() is
     * unavailable. Array-backed so this never leaks into the process environment.
     *
     * @return array{host: string, port: string, socket: string, url: string, username: string, password: string}
     */
    private static function credentials(string $basePath): array
    {
        $file = Dotenv::createArrayBacked($basePath)->safeLoad();

        $read = static function (string $key, string $default) use ($file): string {
            $value = getenv($key);

            return $value !== false ? $value : (string) ($file[$key] ?? $default);
        };

        return [
            'host' => $read('DB_HOST', '127.0.0.1'),
            'port' => $read('DB_PORT', '3306'),
            'socket' => $read('DB_SOCKET', ''),
            'url' => $read('DB_URL', ''),
            'username' => $read('DB_USERNAME', 'root'),
            'password' => $read('DB_PASSWORD', ''),
        ];
    }

    /**
     * @param  array{host: string, port: string, socket: string, url: string, username: string, password: string}  $credentials
     */
    private static function connect(array $credentials): PDO
    {
        if ($credentials['url'] !== '') {
            // config/database.php reads DB_URL and it wins over the individual settings, so
            // creating the schema from host/port here could target a different server entirely.
            self::fail(
                'DB_URL is set, and per-session test databases cannot safely resolve it.'.PHP_EOL
                .'Unset DB_URL, or run against the shared schema:'.PHP_EOL.PHP_EOL
                .'  env -u CLAUDE_CODE_SESSION_ID php artisan test'
            );
        }

        $dsn = $credentials['socket'] !== ''
            ? "mysql:unix_socket={$credentials['socket']}"
            : "mysql:host={$credentials['host']};port={$credentials['port']}";

        try {
            return new PDO(
                $dsn,
                $credentials['username'],
                $credentials['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );
        } catch (PDOException $e) {
            self::fail('Could not reach MySQL to prepare the per-session test database: '.$e->getMessage());
        }
    }

    private static function createDatabase(PDO $connection, string $database, string $base, string $username): void
    {
        try {
            $connection->exec(
                "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
        } catch (PDOException $e) {
            // Never fall back to the shared schema: that is exactly the clobbering this avoids.
            self::fail(
                "Could not create the per-session test database `{$database}`.".PHP_EOL
                .$e->getMessage().PHP_EOL.PHP_EOL
                ."Either give '{$username}' permission to create schemas under that prefix - as MySQL root, once:".PHP_EOL.PHP_EOL
                .'  GRANT ALL PRIVILEGES ON `'.self::schemaPattern($base)."`.* TO '{$username}'@'localhost';".PHP_EOL
                .'  FLUSH PRIVILEGES;'.PHP_EOL.PHP_EOL
                .'...or run against the shared schema, which needs no grant:'.PHP_EOL.PHP_EOL
                .'  env -u CLAUDE_CODE_SESSION_ID php artisan test'
            );
        }
    }

    /**
     * Reclaim schemas from sessions that have since ended.
     *
     * migrate:fresh recreates every table on every run, so MAX(CREATE_TIME) is precisely "the last
     * time tests ran here"; a schema with no tables at all is one whose run died before migrating.
     * The escaped underscores in the pattern are load-bearing: they keep this scoped to
     * <base>_<token> and make it impossible to match the dev database or the shared
     * eventschedule_test, whatever their age.
     *
     * CREATE_TIME only catches up once migrate:fresh runs, a beat after this does, so a schema
     * that another run has just claimed still looks stale. The lock probe is what closes that
     * window - without it this would drop a schema seconds before its owner migrated it.
     */
    private static function pruneStaleDatabases(PDO $connection, string $basePath, string $base, string $current): void
    {
        $configured = getenv('TEST_DB_PRUNE_DAYS');

        $days = $configured === false || $configured === ''
            ? self::DEFAULT_PRUNE_DAYS
            : (int) $configured;

        if ($days < 0) {
            return;
        }

        try {
            $statement = $connection->prepare(
                'SELECT s.SCHEMA_NAME FROM information_schema.SCHEMATA s
                   LEFT JOIN information_schema.TABLES t ON t.TABLE_SCHEMA = s.SCHEMA_NAME
                  WHERE s.SCHEMA_NAME LIKE ?
                  GROUP BY s.SCHEMA_NAME
                 HAVING MAX(t.CREATE_TIME) IS NULL
                     OR MAX(t.CREATE_TIME) < NOW() - INTERVAL '.$days.' DAY'
            );

            $statement->execute([self::schemaPattern($base)]);

            foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $stale) {
                $stale = (string) $stale;

                if ($stale === $current || $stale === $base || ! preg_match('/^[A-Za-z0-9_]+$/', $stale)) {
                    continue;
                }

                if (self::isInUse($basePath, $stale)) {
                    continue;
                }

                $connection->exec("DROP DATABASE IF EXISTS `{$stale}`");
                self::removeArtifacts($basePath, substr($stale, strlen($base) + 1));
            }
        } catch (Throwable) {
            // Housekeeping only. A permissions hiccup here must never fail the suite.
        }
    }

    /**
     * The per-session directories that belong to a schema being dropped. Lock files are left
     * alone on purpose: deleting one races a waiting process onto a fresh inode, which would let
     * two runs believe they both hold it.
     */
    private static function removeArtifacts(string $basePath, string $token): void
    {
        if (! preg_match('/^[a-z0-9]+$/', $token)) {
            return;
        }

        $testing = $basePath.'/storage/framework/testing';

        foreach (array_merge([$testing.'/lang_'.$token], glob($testing.'/disks/*_test_'.$token) ?: []) as $path) {
            self::deleteDirectory($path);
        }
    }

    private static function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $contents = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($contents as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($path);
    }

    /**
     * Matches every per-session schema built from this base and nothing else. Valid both as a
     * MySQL LIKE pattern and as the database part of a GRANT, which is why the underscores are
     * escaped: unescaped they are single-character wildcards in both.
     *
     * Public so TestDatabaseSchemaTest can pin it against real MySQL LIKE semantics. Getting this
     * wrong is the difference between reclaiming dead schemas and dropping the dev database, and
     * a missing '%' here already once produced a GRANT that looked right and did nothing.
     */
    public static function schemaPattern(string $base): string
    {
        return str_replace('_', '\_', $base.'_').'%';
    }

    /**
     * Is this a schema the suite is allowed to migrate:fresh? Used by Tests\TestCase's refuse-to-
     * run guard, which is the last thing standing between a mistake and the dev database.
     */
    public static function isDedicatedTestSchema(string $database): bool
    {
        return (bool) preg_match('/_test(_[a-z0-9]+)?$/D', $database);
    }

    /** Laravel reads $_SERVER first, then $_ENV, then getenv(), so set all three. */
    private static function publish(string $key, string $value): void
    {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    private static function warn(string $message): void
    {
        fwrite(STDERR, 'Warning: '.$message.PHP_EOL);
    }

    /**
     * Print and exit rather than throw: PHPUnit does surface a bootstrap exception, but buries
     * the actionable line under a stack trace.
     */
    private static function fail(string $message): never
    {
        fwrite(STDERR, PHP_EOL.$message.PHP_EOL.PHP_EOL);

        exit(1);
    }
}
