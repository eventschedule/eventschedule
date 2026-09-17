<?php

namespace App\Services;

use App\Jobs\SendFederationWelcome;
use App\Models\FederatedInstance;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Bus;

/**
 * Sends the welcome an operator gets once their install is approved on the nexus.
 *
 * Three callers reach it: an admin approving an install, an admin pressing "Send welcome email"
 * for one approved before the welcome existed, and a signed re-registration that brings an
 * approved install its first contact address. They can overlap - a double-clicked Approve, a
 * single approval racing a bulk one - so every path goes through the claim below and the
 * operator gets one email.
 */
class FederationWelcomeService
{
    /** A resend waits this long after the last send, so a double click cannot mail twice. */
    public const RESEND_COOLDOWN_MINUTES = 10;

    /**
     * The first welcome. A no-op once one has been queued.
     */
    public function send(FederatedInstance $instance): bool
    {
        if (! $this->canWelcome($instance)) {
            return false;
        }

        $now = now();

        // The claim IS the decision: a conditional update is atomic, where reading welcomed_at
        // and writing it afterwards lets two requests both see null.
        $claimed = FederatedInstance::whereKey($instance->id)
            ->whereNull('welcomed_at')
            ->update(['welcomed_at' => $now, 'welcomed_email' => $instance->contact_email]);

        return $claimed === 1 && $this->dispatch($instance, $now, null, null);
    }

    /**
     * Send it again, from the admin screen. Allowed once the cooldown has passed.
     */
    public function resend(FederatedInstance $instance): bool
    {
        if (! $this->canWelcome($instance) || ! $this->canResend($instance)) {
            return false;
        }

        $previousAt = $instance->welcomed_at;
        $previousEmail = $instance->welcomed_email;
        $now = now();

        $claimed = FederatedInstance::whereKey($instance->id)
            ->where(fn ($q) => $q->whereNull('welcomed_at')
                ->orWhere('welcomed_at', '<', $now->copy()->subMinutes(self::RESEND_COOLDOWN_MINUTES)))
            ->update(['welcomed_at' => $now, 'welcomed_email' => $instance->contact_email]);

        return $claimed === 1 && $this->dispatch($instance, $now, $previousAt, $previousEmail);
    }

    public function canWelcome(FederatedInstance $instance): bool
    {
        return $instance->isApproved() && filled($instance->contact_email);
    }

    public function canResend(FederatedInstance $instance): bool
    {
        return ! $instance->welcomed_at
            || $instance->welcomed_at->lt(now()->subMinutes(self::RESEND_COOLDOWN_MINUTES));
    }

    /**
     * Has the operator given a different address since the welcome went out? Worth showing: a
     * mistyped address otherwise never gets the steps. Never acted on automatically - with a
     * cooldown this short, an approved install could otherwise use it to relay mail.
     *
     * No claim either way without a recorded address: every claim stores one, so a welcome with
     * none is not evidence that anything changed.
     */
    public function addressChangedSinceWelcome(FederatedInstance $instance): bool
    {
        return $instance->welcomed_at
            && filled($instance->welcomed_email)
            && filled($instance->contact_email)
            && strcasecmp((string) $instance->welcomed_email, (string) $instance->contact_email) !== 0;
    }

    /**
     * Queue the mail, or hand the claim back if that fails - otherwise the admin screen would
     * report a welcome that never left. A send that fails later, on the worker, is handed back
     * by SendFederationWelcome::failed().
     */
    protected function dispatch(FederatedInstance $instance, CarbonInterface $claimedAt, ?CarbonInterface $previousAt, ?string $previousEmail): bool
    {
        // The format the query builder writes a Carbon value in, so failed() can match the row
        // it claimed and nothing newer.
        $job = new SendFederationWelcome(
            $instance,
            $claimedAt->format('Y-m-d H:i:s'),
            $previousAt?->format('Y-m-d H:i:s'),
            $previousEmail
        );

        try {
            // Bus::dispatch() rather than the dispatch() helper: the helper returns a
            // PendingDispatch that only dispatches from its destructor, which is a poor place
            // for the exception this block exists to catch.
            Bus::dispatch($job);
        } catch (\Throwable $e) {
            report($e);
            $job->releaseClaim();

            return false;
        }

        // Keep the caller's copy in step with the row, so a later save() on it cannot write the
        // old values back over the claim.
        $instance->welcomed_at = $claimedAt;
        $instance->welcomed_email = $instance->contact_email;
        $instance->syncOriginalAttributes(['welcomed_at', 'welcomed_email']);

        return true;
    }
}
