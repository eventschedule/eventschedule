<?php

namespace App\Jobs;

use App\Services\TranslationQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/**
 * Translates one event a minute or so after it was saved with something untranslated (a new
 * event, or an original that changed), instead of waiting for the next fifteen-minute run.
 *
 * It is that run, narrowed: `app:translate --event_id`, under the SAME lock the scheduler and
 * /translate_data take (app_translate_lock), so the two never translate side by side and nothing is
 * bought twice; the command's own attempt ceiling and cooldown apply, and so does every gate the
 * cron asks (TranslationQueue::events(), which a bare --event_id skips on purpose): a feed's draft,
 * a booking, a cancelled or past event, a schedule that does not translate. When the lock is held
 * the job waits a minute and asks again, and after that the cron picks the event up as before.
 *
 * Dispatched by Event's saved hook (Event::queueTranslationSoon()) only where a provider key is
 * set and the queue is a real one: on the sync driver this would run the model calls inside the
 * request that saved the event.
 */
class TranslateEventSoon implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 120;

    /** One per event at a time: a run of saves queues one translation. */
    public int $uniqueFor = 600;

    public function __construct(public int $eventId) {}

    public function uniqueId(): string
    {
        return (string) $this->eventId;
    }

    public function handle(): void
    {
        if (! config('services.google.gemini_key') && ! config('services.openai.api_key')) {
            return;
        }

        if (! TranslationQueue::events()->whereKey($this->eventId)->exists()) {
            return;
        }

        $lock = Cache::lock('app_translate_lock', 600);
        if (! $lock->get()) {
            $this->release(60);

            return;
        }

        try {
            Artisan::call('app:translate', ['--event_id' => $this->eventId, '--max-seconds' => 60]);
        } finally {
            $lock->release();
        }
    }
}
