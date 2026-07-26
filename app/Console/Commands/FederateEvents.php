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

        if ($this->option('register')) {
            $result = $federation->register();

            if (! $result['ok']) {
                $this->error('Registration failed.');

                return self::FAILURE;
            }

            $this->info('Registered. Status: '.($result['body']['status'] ?? 'unknown'));
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

        return self::SUCCESS;
    }
}
