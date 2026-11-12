<?php

namespace App\Jobs;

use App\Models\Role;
use App\Services\TranslationGlossary;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

/**
 * After a schedule's translation glossary changed: hands the translations that use a changed term
 * back to app:translate (TranslationGlossary::requeue()). Queued, because a venue's events can be
 * many, and the schedule form should not wait on them.
 */
class RetranslateGlossaryTerms implements ShouldQueue
{
    use Queueable;

    public $deleteWhenMissingModels = true;

    // A second save waits behind the first (releaseAfter below), which counts as a try.
    public $tries = 5;

    public $timeout = 120;

    /**
     * @param  array<int, array{term: string, before: ?array, after: ?array}>  $changes  from TranslationGlossary::save()
     */
    public function __construct(protected Role $role, protected array $changes) {}

    public function middleware(): array
    {
        // Two saves in a row wait for each other rather than walk the same rows at once.
        return [(new WithoutOverlapping("glossary-retranslate-{$this->role->id}"))->releaseAfter(30)->expireAfter(180)];
    }

    public function handle(): void
    {
        try {
            TranslationGlossary::requeue($this->role, $this->changes);
        } catch (\Throwable $e) {
            report($e);
            Log::error('RetranslateGlossaryTerms failed', ['role_id' => $this->role->id, 'error' => $e->getMessage()]);
        }
    }
}
