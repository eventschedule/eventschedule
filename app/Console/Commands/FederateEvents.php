<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\FederationService;
use Illuminate\Console\Command;

class FederateEvents extends Command
{
    protected $signature = 'federation:push {--register : Register or re-register this install with the nexus first}';

    protected $description = 'Share this install\'s public events with the eventschedule.com network';

    public function handle(FederationService $federation): int
    {
        if (config('app.is_nexus')) {
            $this->info('This install is the nexus; nothing to federate.');

            return self::SUCCESS;
        }

        // Ahead of the disabled check on purpose: this is the one thing that still has
        // to happen while federation is off. A withdrawal that could not reach the
        // nexus when the operator opted out has no other retry - the run below returns
        // immediately - so their events would stay published for good.
        if (Setting::get('federation_withdraw_pending')) {
            $withdraw = $federation->withdraw();

            $this->info($withdraw['ok']
                ? "Withdrew from the network: {$withdraw['removed']} listing(s) removed."
                : 'Withdrawal failed; it stays queued for the next run.');
        }

        if (! $federation->isEnabled()) {
            $this->info('Federation is disabled.');

            return self::SUCCESS;
        }

        // The network answers 403 for an install it does not know - most likely one it deleted
        // - and nothing else would ever re-introduce it, so every later run failed the same
        // way. Register again first. A suspended install is never deleted and gets a 200, so
        // this cannot lift a suspension; a genuine signature mismatch just fails again.
        //
        // Also when the network holds an older contact address than this install: the address
        // only travels in register(), so one saved while the network was unreachable would
        // otherwise never arrive.
        $reconnect = Setting::get('federation_last_error') === 'rejected'
            || $federation->registrationIsStale();

        if ($this->option('register') || $reconnect) {
            $result = $federation->register();

            if (! $result['ok']) {
                $this->error($reconnect ? 'Reconnect failed; it will be retried next run.' : 'Registration failed.');

                return self::FAILURE;
            }

            $this->info(($reconnect ? 'Reconnected' : 'Registered').'. Status: '.($result['body']['status'] ?? 'unknown'));
        }

        // Push before reconcile: reconciling first would report every newly-eligible
        // event as missing and send it round the recovery path on its first cycle.
        $push = $federation->push();
        $this->info("Pushed {$push['pushed']} event(s).");

        if ($push['failed']) {
            $this->error('Push failed; the remainder stays queued for the next run.');

            return self::FAILURE;
        }

        $reconcile = $federation->reconcile();

        if (! $reconcile['ok']) {
            $this->error('Reconcile failed.');

            return self::FAILURE;
        }

        $this->info("Reconciled: {$reconcile['removed']} removed, {$reconcile['requeued']} re-queued.");

        // The first run that comes back approved with events on the network: the dashboard's
        // one-time "You're on the Event Schedule network" card.
        // A nicety: never the reason a sync fails or skips the held push below.
        try {
            $federation->noteLive();
        } catch (\Throwable $e) {
            report($e);
        }

        // The push waited because the network had an older address for this install. The
        // reconcile just told it the current one, and a move within the same site is taken on
        // the spot: in that case send now rather than an hour from now.
        if ($push['held']) {
            if ($federation->addressPending()) {
                $this->info('Held back: the network is confirming this install\'s new address ('.$federation->ownUrl().').');
            } else {
                $push = $federation->push();
                $this->info("Address confirmed. Pushed {$push['pushed']} event(s).");

                if ($push['failed']) {
                    $this->error('Push failed; the remainder stays queued for the next run.');

                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }
}
