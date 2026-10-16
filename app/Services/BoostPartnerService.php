<?php

namespace App\Services;

use App\Exceptions\BoostPartnerException;
use App\Exceptions\MetaApiException;
use App\Jobs\LaunchPartnerBoost;
use App\Models\BoostAd;
use App\Models\BoostAdVersion;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The lifecycle of a boost that runs through a boost partner (see BoostPartner).
 *
 * Two independent gates, both needed to launch, in either order:
 *  - approval, per VERSION of the ad (approved_version must equal version), and
 *  - payment, per BUDGET (paid_budget must equal user_budget).
 *
 * Every step locks the campaign row and checks the version under that lock, so an approval and
 * a payment that arrive together launch exactly once, and an edit that lands while a launch is
 * running is caught by LaunchPartnerBoost's re-check. Nothing here refunds or charges anything:
 * the partner's customer pays the partner, Meta bills the partner's ad account, and the
 * platform's fee is billed monthly on actual spend (BoostStatementService).
 */
class BoostPartnerService
{
    /** Stop codes, carried by the API as stop_reason when no note was written. */
    public const STOP_BY_PARTNER = 'partner';

    public const STOP_BY_BUSINESS = 'business';

    public const STOP_WITHDRAWN = 'withdrawn';

    public const STOP_ACCESS_WITHDRAWN = 'access_withdrawn';

    public const STOP_EVENT_REMOVED = 'event_removed';

    public const DECLINE_BY_PARTNER = 'partner';

    public const DECLINE_OWNERSHIP_CHANGED = 'ownership_changed';

    public const DECLINE_END_PASSED = 'end_passed';

    // -------------------------------------------------------------------------------------
    // Submit and edit (the Business)
    // -------------------------------------------------------------------------------------

    /**
     * Create a request. $data: headline, primary_text, description, call_to_action, budget,
     * scheduled_start, scheduled_end, objective, targeting (array), placements (array),
     * image (UploadedFile|null). A request the partner sends itself counts its first version
     * as approved.
     */
    public function submit(BoostPartner $partner, Event $event, Role $role, User $user, array $data, array $defaults): BoostCampaign
    {
        $isPartnerOwn = (int) $user->id === (int) $partner->user_id;

        return DB::transaction(function () use ($partner, $event, $role, $user, $data, $defaults, $isPartnerOwn) {
            // One open request per event: a second Boost opens the existing one. The event's row
            // is the lock, so two at once queue instead of deadlocking on the index gap.
            Event::whereKey($event->id)->lockForUpdate()->first();
            $open = BoostCampaign::partnerOpen()->where('event_id', $event->id)->first();
            if ($open) {
                return $open;
            }

            $budget = round((float) $data['budget'], 2);
            $start = ! empty($data['scheduled_start']) ? \Carbon\Carbon::parse($data['scheduled_start']) : $defaults['scheduled_start'];
            if ($start->lt(now())) {
                $start = now();
            }
            $end = ! empty($data['scheduled_end']) ? \Carbon\Carbon::parse($data['scheduled_end']) : $defaults['scheduled_end'];

            $campaign = new BoostCampaign([
                'event_id' => $event->id,
                'role_id' => $role->id,
                'user_id' => $user->id,
                'channel' => 'meta',
                'name' => 'Boost: '.$event->translatedName(),
                'objective' => $data['objective'] ?? 'OUTCOME_AWARENESS',
                'status' => 'pending_approval',
                'lifetime_budget' => $budget,
                'budget_type' => 'lifetime',
                'currency_code' => $partner->ad_account_currency ?: config('services.meta.default_currency', 'USD'),
                'scheduled_start' => $start,
                'scheduled_end' => $end,
                'targeting' => $data['targeting'] ?? $defaults['targeting'],
                'placements' => $data['placements'] ?? null,
                'user_budget' => $budget,
                'billing_status' => 'partner',
            ]);
            $campaign->markup_rate = 0;
            $campaign->forceFill([
                'boost_partner_id' => $partner->id,
                'meta_ad_account_id' => $partner->ad_account_id,
                'meta_page_id' => $partner->page_id,
                'meta_page_name' => $partner->page_name,
                'account_timezone' => $partner->ad_account_timezone,
                'schedule_subdomain' => $role->subdomain,
                'schedule_name' => $role->getDisplayName(false),
                'event_name' => $event->translatedName(),
                'version' => 1,
                'submitted_at' => now(),
            ]);
            $campaign->save();

            $destination = $this->destinationUrl($event, $campaign);
            $imagePath = $this->storeImage($campaign, 1, $data['image'] ?? null, $event);

            // Meta refuses an ad with no picture, and that refusal would come only at launch,
            // after the partner approved the request and the Business paid for it.
            if (! $imagePath) {
                throw BoostPartnerException::invalid(__('messages.boost_partner_image_required'));
            }

            $version = BoostAdVersion::create([
                'boost_campaign_id' => $campaign->id,
                'version' => 1,
                'headline' => $data['headline'] ?? $defaults['headline'],
                'primary_text' => $data['primary_text'] ?? $defaults['primary_text'],
                'description' => array_key_exists('description', $data) ? ($data['description'] ?: null) : $defaults['description'],
                'call_to_action' => $data['call_to_action'] ?? $defaults['call_to_action'],
                'image_path' => $imagePath,
                'destination_url' => $destination,
                'budget' => $budget,
                'end_date' => $end,
                'targeting' => $campaign->targeting,
                'created_by' => $user->id,
            ]);

            BoostAd::create([
                'boost_campaign_id' => $campaign->id,
                'variant' => 'A',
                'status' => 'pending',
            ] + $this->adFieldsFrom($version));

            if ($isPartnerOwn) {
                $campaign->forceFill([
                    'approved_version' => 1,
                    'approved_at' => now(),
                    'approved_by' => $user->id,
                    'status' => 'awaiting_payment',
                ])->save();
            }

            AuditService::log(AuditService::BOOST_PARTNER_SUBMIT, $user->id, 'BoostCampaign', $campaign->id, null, ['version' => 1, 'budget' => $budget]);

            DB::afterCommit(function () use ($campaign, $isPartnerOwn) {
                if (! $isPartnerOwn) {
                    BoostPartnerNotifier::requestReceived($campaign->fresh());
                }
            });

            return $campaign;
        });
    }

    /**
     * The Business changes its request. A change to what the ad says or shows raises the
     * version and needs approval again (a launched ad is paused at Meta first); a changed budget
     * also needs paying again; an end date or audience alone is applied at Meta directly.
     *
     * @return array{campaign: BoostCampaign, needs_approval: bool, needs_payment: bool}
     */
    public function edit(BoostCampaign $campaign, User $user, array $data): array
    {
        $campaign = BoostCampaign::findOrFail($campaign->id);
        if (! $campaign->isPartner() || in_array($campaign->status, ['completed', 'stopped'], true)) {
            throw BoostPartnerException::state();
        }

        // A request is the partner's only while the event's schedule is the partner's: one
        // declined because the schedule changed hands must not be resent to the old partner.
        if (! $campaign->event || BoostPartner::forEvent($campaign->event)?->id !== $campaign->boost_partner_id) {
            throw BoostPartnerException::state();
        }

        $current = $campaign->currentVersion();
        $isLive = in_array($campaign->status, BoostCampaign::PARTNER_LIVE_STATUSES, true) && $campaign->meta_campaign_id;

        $newBudget = isset($data['budget']) ? round((float) $data['budget'], 2) : (float) $campaign->user_budget;
        // Whenever the ad has been at Meta, not only while it runs: one paused by an earlier edit
        // resumes with this budget as its lifetime budget, which Meta refuses below what was
        // spent, and the boost would then be refused after it was approved and paid for.
        if ($campaign->meta_campaign_id && $newBudget < round((float) $campaign->actual_spend, 2)) {
            throw BoostPartnerException::invalid(__('messages.boost_partner_budget_below_spend'));
        }

        $proposed = [
            'headline' => $data['headline'] ?? $current?->headline,
            'primary_text' => $data['primary_text'] ?? $current?->primary_text,
            'description' => array_key_exists('description', $data) ? ($data['description'] ?: null) : $current?->description,
            'call_to_action' => $data['call_to_action'] ?? $current?->call_to_action,
            'image_path' => $current?->image_path,
            'destination_url' => $current?->destination_url,
        ];
        $newImage = $data['image'] ?? null;
        $useEventImage = ! empty($data['use_event_image']);

        $creativeChanged = $newImage instanceof UploadedFile || $useEventImage;
        foreach (['headline', 'primary_text', 'description', 'call_to_action'] as $field) {
            if ((string) $proposed[$field] !== (string) $current?->{$field}) {
                $creativeChanged = true;
            }
        }
        // A request that was declined or refused by Meta goes back for approval on any resend.
        $resend = in_array($campaign->status, ['declined', 'rejected'], true);
        $budgetChanged = $newBudget !== round((float) $campaign->user_budget, 2);
        $newEnd = ! empty($data['scheduled_end']) ? \Carbon\Carbon::parse($data['scheduled_end']) : $campaign->scheduled_end;
        $endChanged = $newEnd && (! $campaign->scheduled_end || ! $newEnd->equalTo($campaign->scheduled_end));
        if ($newEnd && $campaign->scheduled_start && $newEnd->lte($campaign->scheduled_start)) {
            throw BoostPartnerException::invalid(__('validation.after', ['attribute' => __('messages.end_date'), 'date' => __('messages.start_date')]));
        }
        $newTargeting = $data['targeting'] ?? $campaign->targeting;
        $audienceChanged = json_encode($newTargeting) !== json_encode($campaign->targeting);

        $needsApproval = $creativeChanged || $resend;
        $needsPayment = $budgetChanged;

        // The picture is stored, or found missing, before Meta is asked to pause anything: refused
        // after the pause, a running ad would stay paused while it reads active. And an ad with no
        // picture is refused by Meta only after approval and payment, as on submit. (The version
        // only names the file.)
        if ($newImage instanceof UploadedFile || $useEventImage) {
            $proposed['image_path'] = $this->storeImage($campaign, $needsApproval ? (int) $campaign->version + 1 : (int) $campaign->version, $newImage, $useEventImage ? $campaign->event : null);
        }
        if (! $proposed['image_path']) {
            throw BoostPartnerException::invalid(__('messages.boost_partner_image_required'));
        }

        // Meta first: a running ad is paused (or its ad set changed) before anything here is
        // saved, so a refusal from Meta leaves this request exactly as it was.
        if ($isLive && ($needsApproval || $needsPayment)) {
            MetaAdsService::forCampaign($campaign)->setStatus($campaign->meta_campaign_id, 'PAUSED');
        } elseif ($isLive && ($endChanged || $audienceChanged) && $campaign->meta_adset_id) {
            MetaAdsService::forCampaign($campaign)->updateAdSet($campaign->meta_adset_id, null, $endChanged ? $newEnd : null, $audienceChanged ? $newTargeting : null);
        }

        $launch = false;

        $result = DB::transaction(function () use ($campaign, $user, $proposed, $needsApproval, $needsPayment, $newBudget, $newEnd, $newTargeting, $current, $isLive, &$launch) {
            $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);

            // A Stop that landed while Meta was being asked to pause wins: an edit never reopens a
            // boost that has ended.
            if (in_array($campaign->status, ['completed', 'stopped'], true)) {
                throw BoostPartnerException::state();
            }

            $nextVersion = $needsApproval ? $campaign->version + 1 : $campaign->version;

            $versionData = $proposed + [
                'budget' => $newBudget,
                'end_date' => $newEnd,
                'targeting' => $newTargeting,
            ];

            if ($needsApproval) {
                BoostAdVersion::create($versionData + [
                    'boost_campaign_id' => $campaign->id,
                    'version' => $nextVersion,
                    'created_by' => $user->id,
                ]);
            } elseif ($current) {
                $current->update($versionData);
            }

            $campaign->fill([
                'user_budget' => $newBudget,
                'lifetime_budget' => $newBudget,
                'scheduled_end' => $newEnd,
                'targeting' => $newTargeting,
            ]);
            $campaign->forceFill(['version' => $nextVersion]);

            if ($needsApproval) {
                $campaign->forceFill([
                    'status' => 'pending_approval',
                    'declined_at' => null,
                    'decline_reason' => null,
                    'meta_rejection_reason' => null,
                ]);
            } elseif ($needsPayment && in_array($campaign->status, ['ready', 'in_review', 'active'], true)) {
                $campaign->forceFill(['status' => $campaign->isApprovedCurrent() ? 'awaiting_payment' : 'pending_approval']);
            }

            if ($isLive && ($needsApproval || $needsPayment)) {
                $campaign->forceFill(['meta_status' => 'PAUSED']);
            }

            // A request waiting on approval or payment is looked at again after any edit: one
            // whose budget went back to the amount already paid is approved and paid once more,
            // and must move on rather than wait for a step nobody can take.
            if (in_array($campaign->status, ['pending_approval', 'awaiting_payment', 'ready'], true)) {
                $launch = $this->advance($campaign);
            }

            $campaign->save();

            AuditService::log(AuditService::BOOST_PARTNER_EDIT, $user->id, 'BoostCampaign', $campaign->id, null, [
                'version' => $nextVersion, 'needs_approval' => $needsApproval, 'needs_payment' => $needsPayment,
            ]);

            if ($needsApproval) {
                DB::afterCommit(fn () => BoostPartnerNotifier::requestResent($campaign->fresh()));
            }

            return [
                'campaign' => $campaign,
                'needs_approval' => $needsApproval,
                'needs_payment' => $needsPayment && ! $campaign->isPaidCurrent(),
            ];
        });

        if ($launch) {
            LaunchPartnerBoost::dispatch($result['campaign'])->afterCommit();
        }

        return $result;
    }

    // -------------------------------------------------------------------------------------
    // The partner's decisions
    // -------------------------------------------------------------------------------------

    /**
     * Approve a named version. An identical repeat answers the boost as it stands. $payment,
     * when given, also records "Payment already received" in the same step.
     */
    public function approve(BoostCampaign $campaign, int $version, ?string $note, User $by, ?array $payment = null): BoostCampaign
    {
        $launch = false;

        $campaign = DB::transaction(function () use ($campaign, $version, $note, $by, $payment, &$launch) {
            $campaign = BoostCampaign::lockForUpdate()->findOrFail($campaign->id);

            if ($version !== (int) $campaign->version) {
                throw BoostPartnerException::stale();
            }

            if ($campaign->isApprovedCurrent() && $campaign->status !== 'pending_approval') {
                return $campaign;
            }

            if ($campaign->status !== 'pending_approval') {
                throw BoostPartnerException::state();
            }

            $campaign->forceFill([
                'approved_version' => $version,
                'approved_at' => now(),
                'approved_by' => $by->id,
                'approval_note' => $note,
                'declined_at' => null,
                'decline_reason' => null,
            ]);

            if ($payment) {
                $this->assertPaidForCurrentBudget($campaign, $payment);
                $this->recordPayment($campaign, $payment, $by);
            }

            $launch = $this->advance($campaign);
            $campaign->save();

            AuditService::log(AuditService::BOOST_PARTNER_APPROVE, $by->id, 'BoostCampaign', $campaign->id, null, ['version' => $version, 'with_payment' => (bool) $payment]);

            DB::afterCommit(fn () => BoostPartnerNotifier::approved($campaign->fresh()));

            return $campaign;
        });

        if ($launch) {
            LaunchPartnerBoost::dispatch($campaign)->afterCommit();
        }

        return $campaign;
    }

    public function decline(BoostCampaign $campaign, string $note, User $by): BoostCampaign
    {
        return DB::transaction(function () use ($campaign, $note, $by) {
            $campaign = BoostCampaign::lockForUpdate()->findOrFail($campaign->id);

            if ($campaign->status === 'declined' && $campaign->approval_note === $note) {
                return $campaign;
            }

            if (! in_array($campaign->status, ['pending_approval', 'awaiting_payment', 'ready'], true)) {
                throw BoostPartnerException::state();
            }

            $campaign->forceFill([
                'status' => 'declined',
                'declined_at' => now(),
                'decline_reason' => self::DECLINE_BY_PARTNER,
                'approval_note' => $note,
                'approved_version' => null,
                'approved_at' => null,
                'approved_by' => null,
            ])->save();

            AuditService::log(AuditService::BOOST_PARTNER_DECLINE, $by->id, 'BoostCampaign', $campaign->id);

            DB::afterCommit(fn () => BoostPartnerNotifier::declined($campaign->fresh()));

            return $campaign;
        });
    }

    /**
     * Record that the Business paid the partner for this budget. The amount is the partner's
     * own price and is not compared with the budget; the budget it was paid for, when given,
     * is (assertPaidForCurrentBudget()).
     */
    public function confirmPayment(BoostCampaign $campaign, array $payment, User $by): BoostCampaign
    {
        $launch = false;

        $campaign = DB::transaction(function () use ($campaign, $payment, $by, &$launch) {
            $campaign = BoostCampaign::lockForUpdate()->findOrFail($campaign->id);

            $this->assertPaidForCurrentBudget($campaign, $payment);

            if ($campaign->isPaidCurrent()) {
                if ((string) $campaign->payment_reference === (string) $payment['reference']
                    && round((float) $campaign->payment_amount, 2) === round((float) $payment['amount'], 2)) {
                    return $campaign;
                }

                throw BoostPartnerException::state();
            }

            if (! in_array($campaign->status, ['pending_approval', 'awaiting_payment'], true)) {
                throw BoostPartnerException::state();
            }

            $this->recordPayment($campaign, $payment, $by);
            $launch = $this->advance($campaign);
            $campaign->save();

            AuditService::log(AuditService::BOOST_PARTNER_PAYMENT, $by->id, 'BoostCampaign', $campaign->id, null, ['amount' => $payment['amount'], 'currency' => $payment['currency'] ?? null]);

            return $campaign;
        });

        if ($launch) {
            LaunchPartnerBoost::dispatch($campaign)->afterCommit();
        }

        return $campaign;
    }

    /** Withdraw a payment confirmation (a refund, a dispute). Only before launch. */
    public function withdrawPayment(BoostCampaign $campaign, User $by): BoostCampaign
    {
        return DB::transaction(function () use ($campaign, $by) {
            $campaign = BoostCampaign::lockForUpdate()->findOrFail($campaign->id);

            if (! $campaign->payment_confirmed_at) {
                return $campaign;
            }

            // Before launch only: once the ad has run, the payment is what it ran on.
            if ($campaign->launched_at || ! in_array($campaign->status, ['pending_approval', 'awaiting_payment', 'ready', 'declined'], true)) {
                throw BoostPartnerException::state();
            }

            $campaign->forceFill([
                'payment_confirmed_at' => null,
                'payment_reference' => null,
                'payment_amount' => null,
                'payment_currency' => null,
                'paid_budget' => null,
                'payment_confirmed_by' => null,
            ]);
            if ($campaign->status === 'ready') {
                $campaign->status = 'awaiting_payment';
            }
            $campaign->save();

            AuditService::log(AuditService::BOOST_PARTNER_PAYMENT_WITHDRAWN, $by->id, 'BoostCampaign', $campaign->id);

            return $campaign;
        });
    }

    /**
     * End a boost for good. A launched ad is paused at Meta (never deleted: its spend must stay
     * readable for the statement). If Meta cannot be reached the boost is still stopped here and
     * pause_pending_at asks the sync to keep trying; the caller is told so.
     *
     * @return array{campaign: BoostCampaign, paused_at_meta: bool}
     */
    public function stop(BoostCampaign $campaign, ?User $by, string $code, ?string $reason = null): array
    {
        $result = DB::transaction(function () use ($campaign, $by, $code, $reason) {
            $campaign = BoostCampaign::lockForUpdate()->findOrFail($campaign->id);

            if ($campaign->status === 'stopped') {
                return ['campaign' => $campaign, 'changed' => false];
            }

            if (! in_array($campaign->status, BoostCampaign::PARTNER_OPEN_STATUSES, true)) {
                throw BoostPartnerException::state();
            }

            $campaign->forceFill([
                'status' => 'stopped',
                'stopped_at' => now(),
                'stop_code' => $code,
                'stop_reason' => $reason,
                'stopped_by' => $by?->id,
                'pause_pending_at' => $campaign->meta_campaign_id ? now() : null,
            ])->save();

            AuditService::log(AuditService::BOOST_PARTNER_STOP, $by?->id, 'BoostCampaign', $campaign->id, null, ['code' => $code]);

            return ['campaign' => $campaign, 'changed' => true];
        });

        $campaign = $result['campaign'];
        $paused = ! $campaign->meta_campaign_id || ! $campaign->pause_pending_at;

        if ($result['changed'] && $campaign->meta_campaign_id) {
            $paused = $this->pauseAtMeta($campaign);
        }

        if ($result['changed']) {
            BoostPartnerNotifier::stopped($campaign->fresh(), $by);
        }

        return ['campaign' => $campaign->fresh(), 'paused_at_meta' => $paused];
    }

    /**
     * Pause a stopped boost at Meta and clear pause_pending_at once it is done. The sync calls
     * this again for every row still pending.
     */
    public function pauseAtMeta(BoostCampaign $campaign): bool
    {
        try {
            MetaAdsService::forCampaign($campaign)->setStatus($campaign->meta_campaign_id, 'PAUSED');
            $campaign->forceFill(['pause_pending_at' => null, 'meta_status' => 'PAUSED'])->save();

            return true;
        } catch (\Exception $e) {
            Log::warning('Boost partner: could not pause a stopped campaign at Meta', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // -------------------------------------------------------------------------------------
    // Launch
    // -------------------------------------------------------------------------------------

    /**
     * Move a request on when its gates are met. Returns true when a launch should be
     * dispatched; the caller dispatches it after commit. Must run under the campaign's lock.
     */
    protected function advance(BoostCampaign $campaign): bool
    {
        if (! $campaign->isApprovedCurrent()) {
            $campaign->status = 'pending_approval';

            return false;
        }

        if (! $campaign->isPaidCurrent()) {
            $campaign->status = 'awaiting_payment';

            return false;
        }

        $partner = $campaign->partner;
        if (! $partner || ! $partner->isUsable()) {
            $campaign->status = 'ready';

            return false;
        }

        $campaign->status = 'in_review';

        return true;
    }

    /**
     * Launch every request that waited only for the partner's account: run when an admin
     * confirms a partner share, and after a login.
     */
    public function launchReady(BoostPartner $partner): int
    {
        $launched = 0;
        $this->refreshSnapshots($partner);

        foreach ($partner->campaigns()->where('status', 'ready')->pluck('id') as $id) {
            $dispatch = DB::transaction(function () use ($id, $partner) {
                $campaign = BoostCampaign::lockForUpdate()->find($id);
                if (! $campaign || $campaign->status !== 'ready') {
                    return null;
                }

                // A request made before the partner connected has no account snapshot yet.
                if (! $campaign->meta_ad_account_id) {
                    $campaign->forceFill([
                        'meta_ad_account_id' => $partner->ad_account_id,
                        'meta_page_id' => $partner->page_id,
                        'meta_page_name' => $partner->page_name,
                        'account_timezone' => $partner->ad_account_timezone,
                        'currency_code' => $partner->ad_account_currency ?: $campaign->currency_code,
                    ]);
                }

                $launch = $this->advance($campaign);
                $campaign->save();

                return $launch ? $campaign : null;
            });

            if ($dispatch) {
                LaunchPartnerBoost::dispatch($dispatch);
                $launched++;
            }
        }

        return $launched;
    }

    /**
     * Point every request that has not reached Meta at the partner's current Page and ad
     * account, so what a request shows matches where it would go. The launch takes them from the
     * partner again in any case (LaunchPartnerBoost), this is for display and the API.
     */
    public function refreshSnapshots(BoostPartner $partner): void
    {
        $partner->campaigns()
            ->whereNull('meta_campaign_id')
            ->whereIn('status', ['pending_approval', 'awaiting_payment', 'ready', 'declined'])
            ->update(array_filter([
                'meta_ad_account_id' => $partner->ad_account_id,
                'meta_page_id' => $partner->page_id,
                'meta_page_name' => $partner->page_name,
                'account_timezone' => $partner->ad_account_timezone,
                // A request made before the partner first connected had no real currency (the
                // platform's default stood in). The settings refuse a CHANGE of currency while
                // requests are open, so this only ever gives those requests their first real one.
                'currency_code' => $partner->ad_account_currency,
            ], fn ($v) => $v !== null));
    }

    /**
     * When a schedule moves into or out of a partner's account, its open requests have nobody
     * left to approve them. Running campaigns keep the partner they launched under.
     */
    public function declineOpenRequestsForRole(Role $role): void
    {
        $ids = BoostCampaign::viaPartner()
            ->where('role_id', $role->id)
            ->whereIn('status', ['pending_approval', 'awaiting_payment', 'ready'])
            ->pluck('id');

        foreach ($ids as $id) {
            DB::transaction(function () use ($id) {
                $campaign = BoostCampaign::lockForUpdate()->find($id);
                if (! $campaign || ! in_array($campaign->status, ['pending_approval', 'awaiting_payment', 'ready'], true)) {
                    return;
                }
                $campaign->forceFill([
                    'status' => 'declined',
                    'declined_at' => now(),
                    'decline_reason' => self::DECLINE_OWNERSHIP_CHANGED,
                    'approval_note' => null,
                ])->save();
            });
        }
    }

    // -------------------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------------------

    /**
     * A payment says which budget it was for ($payment['budget'], the figure the partner read
     * and charged for), and is refused when the Business has changed the budget since: a budget
     * edit does not raise the version, so without this a payment taken for 100 was recorded
     * against the 1000 asked for a minute later and launched at 1000 on the partner's account.
     * Optional, so an integration written before it existed keeps working.
     */
    protected function assertPaidForCurrentBudget(BoostCampaign $campaign, array $payment): void
    {
        $budget = $payment['budget'] ?? null;

        if ($budget !== null && $budget !== '' && round((float) $budget, 2) !== round((float) $campaign->user_budget, 2)) {
            throw BoostPartnerException::staleBudget();
        }
    }

    protected function recordPayment(BoostCampaign $campaign, array $payment, User $by): void
    {
        $reference = trim((string) ($payment['reference'] ?? ''));
        $amount = $payment['amount'] ?? null;
        if ($reference === '' || ! is_numeric($amount) || (float) $amount < 0) {
            throw BoostPartnerException::invalid(__('messages.boost_partner_payment_invalid'));
        }

        $campaign->forceFill([
            'payment_confirmed_at' => now(),
            'payment_reference' => Str::limit($reference, 255, ''),
            'payment_amount' => round((float) $amount, 2),
            'payment_currency' => strtoupper((string) ($payment['currency'] ?? $campaign->currency_code)),
            'paid_budget' => $campaign->user_budget,
            'payment_confirmed_by' => $by->id,
        ]);
    }

    /** The BoostAd row's copy of a version: what Meta is sent. */
    public function adFieldsFrom(BoostAdVersion $version): array
    {
        return [
            'headline' => $version->headline,
            'primary_text' => $version->primary_text,
            'description' => $version->description,
            'call_to_action' => $version->call_to_action,
            'image_url' => $version->imageUrl(),
            'destination_url' => $version->destination_url,
        ];
    }

    protected function destinationUrl(Event $event, BoostCampaign $campaign): string
    {
        $url = $event->getGuestUrl(false, null, true);

        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query([
            'utm_source' => 'boost',
            'utm_medium' => 'paid_social',
            'utm_campaign' => $campaign->hashedId(),
        ]);
    }

    /**
     * Keep our own copy of the picture: replacing an event's flyer deletes the old file, and an
     * approved picture must not change or disappear underneath the approval.
     */
    protected function storeImage(BoostCampaign $campaign, int $version, ?UploadedFile $upload, ?Event $event): ?string
    {
        $dir = 'boost/'.$campaign->hashedId();

        try {
            if ($upload) {
                $name = 'v'.$version.'-'.Str::random(8).'.'.($upload->guessExtension() ?: 'jpg');

                return Storage::putFileAs($dir, $upload, $name) ?: null;
            }

            $event = $event ?? $campaign->event;
            if (! $event) {
                return null;
            }

            $raw = $event->getRawOriginal('flyer_image_url');
            if ($raw && ! str_starts_with($raw, 'demo_') && Storage::exists($raw)) {
                $target = $dir.'/v'.$version.'-'.Str::random(8).'.'.(pathinfo($raw, PATHINFO_EXTENSION) ?: 'jpg');
                Storage::copy($raw, $target);

                return $target;
            }

            // A demo image or a schedule's picture standing in for a flyer: keep its address.
            return $event->getImageUrl() ?: null;
        } catch (\Exception $e) {
            report($e);

            return $event?->getImageUrl() ?: null;
        }
    }

    /**
     * Stop a partner boost from a deletion or cancellation cascade (the event, its schedule or
     * an account going). Anything that is not open is left as it is, and a failure never stops
     * the cascade.
     */
    public static function stopInCascade(BoostCampaign $campaign): void
    {
        if (! in_array($campaign->status, BoostCampaign::PARTNER_OPEN_STATUSES, true)) {
            return;
        }

        try {
            app(self::class)->stop($campaign, null, self::STOP_EVENT_REMOVED);
        } catch (\Exception $e) {
            report($e);
        }
    }

    /** Whether a Meta error means our access to the partner's assets is gone. */
    public static function isAccessError(\Throwable $e): bool
    {
        return $e instanceof MetaApiException && $e->isPermission();
    }
}
