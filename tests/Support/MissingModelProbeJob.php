<?php

namespace Tests\Support;

use App\Models\Sale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * A queued job that carries a Sale and deliberately does NOT set deleteWhenMissingModels.
 *
 * It exists so the queue tests have a control: it is the only way left to produce a genuinely
 * poisoned failed_jobs row, because the jobs that used to produce one (SendQueuedEmail and
 * friends) now opt in to the flag and are dropped instead. Without it, a green assertion that
 * "SendQueuedEmail leaves no failed row" would be indistinguishable from a harness that never
 * managed to fail a job at all.
 *
 * Lives in tests/Support rather than tests/Feature so PHPUnit never collects it as a test class.
 */
class MissingModelProbeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    protected Sale $sale;

    public function __construct(Sale $sale)
    {
        // The suite runs inside an uncommitted RefreshDatabase transaction, so a job that waits
        // for a commit would never be pushed at all.
        $this->beforeCommit();

        $this->sale = $sale;
    }

    public function handle(): void
    {
        // Never reached: this job only exists to fail during payload deserialization.
    }
}
