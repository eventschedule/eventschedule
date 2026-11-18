<?php

namespace App\Jobs;

use App\Models\FederatedEvent;
use Illuminate\Database\Eloquent\Model;

/**
 * The light copies of a listing's stored picture, for /browse (GenerateImageVariants). Queued by
 * federation:maintain a bounded number per run, never decoded inside it: maintain also runs inside
 * the HTTP cron request, where one large picture would spend the request's time and memory.
 */
class GenerateFederatedImageVariants extends GenerateImageVariants
{
    public function __construct(public int $listingId, public string $imagePath) {}

    protected function findModel(): ?Model
    {
        return FederatedEvent::find($this->listingId);
    }

    protected function storedName(): string
    {
        return $this->imagePath;
    }

    protected function subject(): string
    {
        return 'federated listing '.$this->listingId;
    }
}
