<?php

namespace App\Jobs;

use App\Services\OneSignalService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Queued OneSignal push send. Mirrors SendQueuedEmail: the recipient's locale
 * is applied before the title/body translation keys are resolved, so the push
 * text matches the language the recipient would receive email in.
 */
class SendQueuedPush implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    protected string $aliasField;

    protected array $aliasValues;

    protected array $payload;

    protected ?string $locale;

    protected ?int $roleId;

    /**
     * @param  array  $payload  ['title_key','title_params'?,'body_key','body_params'?,'url'?,'options'?]
     */
    public function __construct(string $aliasField, array $aliasValues, array $payload, ?string $locale = null, ?int $roleId = null)
    {
        $this->aliasField = $aliasField;
        $this->aliasValues = $aliasValues;
        $this->payload = $payload;
        $this->locale = $locale;
        $this->roleId = $roleId;
    }

    public function handle(): void
    {
        $originalLocale = app()->getLocale();

        try {
            if ($this->locale) {
                app()->setLocale($this->locale);
            }

            $title = __($this->payload['title_key'], $this->payload['title_params'] ?? []);
            $body = __($this->payload['body_key'], $this->payload['body_params'] ?? []);

            OneSignalService::send(
                $this->aliasField,
                $this->aliasValues,
                $title,
                $body,
                $this->payload['url'] ?? null,
                $this->payload['options'] ?? [],
                $this->roleId,
            );
        } finally {
            app()->setLocale($originalLocale);
        }
    }
}
