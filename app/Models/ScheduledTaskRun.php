<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The last observed outcome of one scheduled task, keyed by its ->name().
 *
 * Upserted rather than appended; see the migration for why. The display list on /admin/queue comes
 * from Schedule::events(), so rows here are joined onto that rather than driving it - a row whose
 * task no longer exists simply stops rendering.
 */
class ScheduledTaskRun extends Model
{
    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    /**
     * An overlap skip. Deliberately a status a row can hold but that never counts as a failure:
     * withoutOverlapping() blocking a task is normal under load, and the run that DID hold the
     * mutex is the one whose result matters.
     */
    public const STATUS_SKIPPED = 'skipped';

    /** Exception text is unbounded and multi-line; keep the stored excerpt readable in a table cell. */
    public const ERROR_LENGTH = 1000;

    protected $fillable = [
        'name',
        'last_started_at',
        'last_finished_at',
        'last_skipped_at',
        'last_status',
        'last_runtime_seconds',
        'last_error',
        'consecutive_failures',
        'last_via',
        'last_host',
    ];

    protected $casts = [
        'last_started_at' => 'datetime',
        'last_finished_at' => 'datetime',
        'last_skipped_at' => 'datetime',
        'last_runtime_seconds' => 'decimal:2',
        'consecutive_failures' => 'integer',
    ];

    /**
     * The most recent evidence the scheduler considered this task at all.
     *
     * Skips count. process-queue is blocked by its own mutex on any minute where the previous
     * queue:work is still draining, and treating that as "did not run" would mark the busiest
     * install's most important task permanently overdue.
     *
     * That makes this the wrong thing to ask "has this task completed lately", because a skip keeps
     * it fresh forever - SchedulerHealth::state() anchors that question on last_finished_at
     * instead, and only falls through to this for the overdue comparison.
     */
    public function lastSeenAt(): ?\Illuminate\Support\Carbon
    {
        return collect([$this->last_started_at, $this->last_skipped_at])
            ->filter()
            ->max();
    }

    public function isRunning(): bool
    {
        return $this->last_started_at !== null
            && ($this->last_finished_at === null || $this->last_started_at->gt($this->last_finished_at));
    }
}
