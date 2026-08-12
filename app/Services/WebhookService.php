<?php

namespace App\Services;

use App\Jobs\SendWebhook;
use App\Models\Event;
use App\Models\Sale;
use App\Models\Webhook;
use Illuminate\Support\Facades\DB;

class WebhookService
{
    public static function dispatch(string $eventType, $model, ?array $prebuiltPayload = null, ?array $extraData = null): void
    {
        // Resolve the event owner's user_id
        if ($model instanceof Sale) {
            $model->loadMissing('event.roles');
            $userId = $model->event?->user_id;
        } elseif ($model instanceof Event) {
            $userId = $model->user_id;
        } else {
            return;
        }

        if (! $userId) {
            return;
        }

        // Pro gate: check if the event belongs to a Pro schedule
        if ($model instanceof Event) {
            $model->loadMissing('roles');
            if (! $model->isPro()) {
                return;
            }
        } elseif ($model instanceof Sale) {
            if (! $model->event?->isPro()) {
                return;
            }
        }

        // Load matching webhooks
        $webhooks = Webhook::where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($webhook) => $webhook->subscribesTo($eventType));

        if ($webhooks->isEmpty()) {
            return;
        }

        // Build payload
        if ($prebuiltPayload) {
            $payload = $prebuiltPayload;
        } else {
            if ($model instanceof Sale) {
                $model->loadMissing(['saleTickets.ticket', 'event']);
                $data = $model->toApiData(true);
            } else {
                $data = $model->toApiData();
            }

            if ($extraData) {
                // toApiData() returns a stdClass, and array_merge() rejects an object outright on
                // PHP 8 - so every dispatch carrying $extraData threw a TypeError. It went
                // unnoticed because dispatch() returns early when nobody is subscribed, which is
                // the case in every test, so the line was never reached under test.
                //
                // The blast radius was worst on the installment path: the throw landed after the
                // payment had already committed, was swallowed by the cron's catch, and rewrote a
                // just-settled row as awaiting_reconciliation - money taken, ticket eventually put
                // on hold. ticket.booked and ticket.booking_cancelled pass $extraData the same way.
                $data = (object) array_merge((array) $data, $extraData);
            }

            $payload = [
                'event' => $eventType,
                'timestamp' => now()->toIso8601String(),
                'data' => $data,
            ];
        }

        $dispatchFn = function () use ($webhooks, $payload, $eventType) {
            foreach ($webhooks as $webhook) {
                SendWebhook::dispatchAfterResponse($webhook, $payload, $eventType);
            }
        };

        // If inside a transaction, defer dispatch until after commit
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($dispatchFn);
        } else {
            $dispatchFn();
        }
    }
}
