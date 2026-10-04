<?php

namespace App\Utils;

use App\Models\Role;
use Illuminate\Support\Str;

/**
 * One sitting on a schedule's import page, so the events it adds can be counted and undone
 * together (events.import_batch).
 *
 * The batch id lives in the session and is handed to EventRepo::saveEvent() by the controller. It
 * is never read from the request: the import page saves one event per fetch, and an id the
 * browser supplied would let one account tag its events into another's batch.
 */
class ImportRun
{
    /** A run left open this long is over: the next visit to the page starts a new one. */
    public const OPEN_MINUTES = 60;

    /** How long a finished run can still be undone. */
    public const UNDO_HOURS = 24;

    private static function key(Role $role): string
    {
        return 'import_run.'.$role->id;
    }

    /** The run that was last finished: what "Undo this import" removes. */
    private static function lastKey(Role $role): string
    {
        return 'import_last.'.$role->id;
    }

    /**
     * Called when an import page is opened. Returns the batch of the run in progress, starting
     * one when there is none: a reload half way through an import stays in the same batch.
     */
    public static function begin(Role $role): string
    {
        $run = session(self::key($role));

        if (is_array($run) && ! empty($run['batch']) && empty($run['finished_at'])
            && ($run['started_at'] ?? 0) > now()->subMinutes(self::OPEN_MINUTES)->getTimestamp()) {
            return $run['batch'];
        }

        $batch = strtolower(Str::random(12));
        session([self::key($role) => ['batch' => $batch, 'started_at' => now()->getTimestamp()]]);

        return $batch;
    }

    /** The batch to stamp on an event being saved now, or null outside an import page visit. */
    public static function batch(Role $role): ?string
    {
        $run = session(self::key($role));

        return is_array($run) && empty($run['finished_at']) ? ($run['batch'] ?? null) : null;
    }

    /**
     * Close the run in progress and keep it as the last one. Returns its batch, or null when
     * there was no run to close. The next visit to an import page starts a fresh batch, so
     * undoing one import never reaches back into the one before it.
     */
    public static function finish(Role $role): ?string
    {
        $batch = self::batch($role);

        if ($batch === null) {
            return null;
        }

        session([self::lastKey($role) => ['batch' => $batch, 'finished_at' => now()->getTimestamp()]]);
        session()->forget(self::key($role));

        return $batch;
    }

    /** The batch of the last finished run, while it can still be undone. */
    public static function last(Role $role): ?string
    {
        $run = session(self::lastKey($role));

        return is_array($run) && ! empty($run['batch'])
            && ($run['finished_at'] ?? 0) > now()->subHours(self::UNDO_HOURS)->getTimestamp()
            ? $run['batch']
            : null;
    }

    public static function forgetLast(Role $role): void
    {
        session()->forget(self::lastKey($role));
    }
}
