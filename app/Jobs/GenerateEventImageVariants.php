<?php

namespace App\Jobs;

use App\Models\Event;
use App\Utils\ImageUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Build the resized WebP derivative of an event flyer.
 *
 * Dispatched from Event's created/updated hooks rather than from each controller, because the
 * flyer is written from nine different places (web upload, API, guest submit, guest import, AI
 * flyer, Eventbrite, WhatsApp, curator import, clone) and every one of them ends in an Eloquent
 * save(). The one path that does not is BackupService's restore, which uses saveQuietly() on
 * purpose; `php artisan images:backfill-variants` covers those rows.
 *
 * Queue latency is up to a minute on the hosted deploy (the queue is drained by the scheduler's
 * process-queue entry, not a resident worker). That is fine: every consumer falls back to the
 * original until the derivative is recorded.
 */
class GenerateEventImageVariants implements ShouldQueue
{
    use Queueable;

    /**
     * Two attempts, not the default of one-plus-retries-forever: the work is bounded by the
     * pixel guard in the helper, so a failure here is a disk or network problem, not something
     * that gets better on the tenth try.
     */
    public int $tries = 2;

    public int $timeout = 120;

    /**
     * @param  int  $eventId  The event whose flyer to resize.
     * @param  string  $flyer  The raw stored filename at dispatch time. Carried so a flyer that
     *                         was replaced while the job waited in the queue is not resized, and
     *                         so a derivative of the OLD file can never be recorded against the
     *                         NEW one.
     */
    public function __construct(public int $eventId, public string $flyer) {}

    public function handle(): void
    {
        $event = Event::find($this->eventId);

        if (! $event) {
            return;
        }

        $raw = $event->getAttributes()['flyer_image_url'] ?? null;

        if ($raw !== $this->flyer) {
            return;
        }

        if ($event->imageVariantFilename(ImageUtils::VARIANT_WIDTH)) {
            return;
        }

        $result = ImageUtils::generateStoredVariant($raw, ImageUtils::VARIANT_WIDTH);
        $key = 'w'.ImageUtils::VARIANT_WIDTH;

        if ($result['ok']) {
            $event->recordImageVariants([$key => $result['filename']]);

            return;
        }

        Log::info('GenerateEventImageVariants skipped event '.$this->eventId.': '.$result['reason']);

        $event->recordImageVariants([$key => null, 'skipped' => $result['reason']]);
    }
}
