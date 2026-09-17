<?php

namespace App\Jobs;

use App\Mail\FederationInstanceWelcome;
use App\Models\FederatedInstance;
use Throwable;

/**
 * The federation welcome, sent like any other platform mail, plus one thing SendQueuedEmail
 * cannot do: hand the claim back when the send finally fails.
 *
 * FederationWelcomeService claims federated_instances.welcomed_at before queueing, so a double
 * approve cannot mail twice. Without this, a send that failed every retry on the worker would
 * leave the row claimed - the admin screen reporting a welcome that never arrived, and the bulk
 * send and the re-register path both skipping the install for good.
 */
class SendFederationWelcome extends SendQueuedEmail
{
    protected int $instanceId;

    protected string $claimedAt;

    protected ?string $previousAt;

    protected ?string $previousEmail;

    /**
     * @param  string  $claimedAt  the welcomed_at value the claim wrote, 'Y-m-d H:i:s'
     * @param  string|null  $previousAt  what welcomed_at held before (a resend), or null
     * @param  string|null  $previousEmail  what welcomed_email held before, or null
     */
    public function __construct(FederatedInstance $instance, string $claimedAt, ?string $previousAt = null, ?string $previousEmail = null)
    {
        // No roleId: a federated instance is not one of our schedules, so this goes out on the
        // platform mailer. The operator's language, never config('app.locale'), which is the
        // acting admin's.
        parent::__construct(
            new FederationInstanceWelcome($instance),
            (string) $instance->contact_email,
            null,
            $instance->mailLocale()
        );

        $this->instanceId = $instance->id;
        $this->claimedAt = $claimedAt;
        $this->previousAt = $previousAt;
        $this->previousEmail = $previousEmail;
    }

    /**
     * Every retry failed. Put the row back the way it was - but only if it still holds THIS
     * claim, so a newer send that has claimed it since is left alone.
     */
    public function failed(?Throwable $exception = null): void
    {
        $this->releaseClaim();
    }

    public function releaseClaim(): void
    {
        FederatedInstance::whereKey($this->instanceId)
            ->where('welcomed_at', $this->claimedAt)
            ->update([
                'welcomed_at' => $this->previousAt,
                'welcomed_email' => $this->previousEmail,
            ]);
    }
}
