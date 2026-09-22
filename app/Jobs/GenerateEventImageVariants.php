<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;

/**
 * Build the resized WebP derivatives of an event flyer. The work itself, and its failure
 * handling, is in GenerateImageVariants.
 *
 * Dispatched from Event's created/updated hooks rather than from each controller, because the
 * flyer is written from nine different places (web upload, API, guest submit, guest import, AI
 * flyer, Eventbrite, WhatsApp, curator import, clone) and every one of them ends in an Eloquent
 * save(). The one path that does not is BackupService's restore, which uses saveQuietly() on
 * purpose; `php artisan images:backfill-variants` covers those rows.
 */
class GenerateEventImageVariants extends GenerateImageVariants
{
    /**
     * Property names are part of the serialized payload of jobs already sitting in the queue,
     * so they stay as they were before the shared base class existed.
     *
     * @param  int  $eventId  The event whose flyer to resize.
     * @param  string  $flyer  The raw stored filename at dispatch time.
     */
    public function __construct(public int $eventId, public string $flyer) {}

    protected function findModel(): ?Model
    {
        return Event::find($this->eventId);
    }

    protected function storedName(): string
    {
        return $this->flyer;
    }

    protected function subject(): string
    {
        return 'event '.$this->eventId;
    }
}
