<?php

namespace App\Http\Controllers;

use App\Jobs\SendQueuedEmail;
use App\Mail\FederationInstanceReviewed;
use App\Mail\FederationInstanceWelcome;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Services\AuditService;
use App\Services\FederationWelcomeService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Nexus-side moderation of the federation network. Approving an instance is the
 * highest-risk action in the feature - it lets a third party publish content and
 * outbound links on this domain - so the screen shows what is actually being
 * approved rather than just a name.
 */
class AdminFederationController extends Controller
{
    /** Sample listings shown inline on a row, so a review decision is informed. */
    public const SAMPLE_SIZE = 6;

    /** Origin schedules listed inline per instance. */
    public const MAX_SCHEDULES = 6;

    /** Matches the translation review queue's bulk cap. */
    public const MAX_BULK = 100;

    public function index(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $status = $request->input('status', FederatedInstance::STATUS_PENDING);

        $instances = FederatedInstance::query()
            // withCount rather than a GROUP BY: grouping on a select alias binds to a
            // same-named table column and errors with 1055.
            //
            // live_events_count deliberately omits listable()'s instance-approval
            // check - the parent IS the instance - so it is non-zero for a pending or
            // suspended instance that publishes nothing. The view gates on
            // isApproved() before offering the link that uses it.
            ->withCount(['events', 'events as live_events_count' => fn ($q) => $q->live()])
            ->when(in_array($status, ['pending', 'approved', 'suspended'], true), fn ($q) => $q->where('status', $status))
            // Where the dashboard alert points. Matched to AdminAlertService's own
            // federation_flagged query exactly, so the badge and the page it links to
            // cannot disagree. Not in the in_array() above, or the two would fight.
            ->when($status === 'flagged', fn ($q) => $q->whereNotNull('flagged_at')
                ->where('status', FederatedInstance::STATUS_APPROVED))
            // Flagged instances first (a site_url that stopped matching), then the ones
            // carrying the most content, so attention lands where it matters.
            ->orderByRaw('flagged_at IS NULL')
            ->orderByDesc('events_count')
            ->orderBy('created_at')
            ->paginate(30)
            ->withQueryString();

        // A sample of what each instance has actually sent, on every tab and not only
        // the pending one. An approved instance is the one you may need to reconsider,
        // and showing nothing there left the row with no evidence at all.
        //
        // Deliberately NOT folded into one grouped query the way the schedule list
        // below is: this returns rows per LISTING, so a single query with a global
        // limit would let one instance holding thousands of them consume the whole
        // budget and starve every other row on the page. Thirty small bounded queries
        // against the FK index is the cheaper shape here.
        $samples = [];
        foreach ($instances as $instance) {
            $samples[$instance->id] = FederatedEvent::where('federated_instance_id', $instance->id)
                ->orderByDesc('created_at')
                ->limit(self::SAMPLE_SIZE)
                ->get();
        }

        // Hosts of suspended installs, so a pending row on the same site can say so. A suspension
        // sticks to its row, but a site that clears its settings registers under a new identity -
        // and the old pruning used to delete suspended rows outright - so the host is the only
        // thread left between the two. One small query, compared in PHP per row.
        $suspendedHosts = $status === FederatedInstance::STATUS_APPROVED
            ? collect()
            : FederatedInstance::where('status', FederatedInstance::STATUS_SUSPENDED)
                ->pluck('site_url')
                ->map(fn ($url) => strtolower((string) parse_url((string) $url, PHP_URL_HOST)))
                ->filter()
                ->flip();

        return view('admin.federation', [
            'instances' => $instances,
            'samples' => $samples,
            'suspendedHosts' => $suspendedHosts,
            // The origin's own public pages. The instance's site_url lands on its login
            // screen (a selfhost install publishes no index of its schedules), so these
            // are the only links on the row a reviewer can actually learn anything from.
            'scheduleLinks' => FederatedEvent::schedulesForInstances($instances->pluck('id')->all()),
            'status' => $status,
            'pendingCount' => FederatedInstance::pending()->count(),
            // Drives whether the Flagged tab is offered at all. A tab that is empty on
            // every healthy install is noise, so it appears only when it has something.
            'flaggedCount' => FederatedInstance::whereNotNull('flagged_at')
                ->where('status', FederatedInstance::STATUS_APPROVED)
                ->count(),
        ]);
    }

    public function approve(Request $request, string $hash)
    {
        return $this->setStatus($hash, FederatedInstance::STATUS_APPROVED, AuditService::ADMIN_FEDERATION_APPROVE);
    }

    public function suspend(Request $request, string $hash)
    {
        return $this->setStatus($hash, FederatedInstance::STATUS_SUSPENDED, AuditService::ADMIN_FEDERATION_SUSPEND);
    }

    /**
     * Remove an instance and everything it sent.
     *
     * Not a way to keep an install out. An install that is still running re-registers on its
     * next hourly run (FederateEvents reconnects after a 403) and comes back as a fresh pending
     * row. Suspend is the decision that sticks: a suspended row is kept, and a re-registration
     * against it cannot move it out of suspension.
     */
    public function destroy(Request $request, string $hash)
    {
        abort_unless(config('app.is_nexus'), 404);

        $instance = FederatedInstance::findOrFail(UrlUtils::decodeId($hash));
        $siteUrl = $instance->site_url;

        // The FK cascade drops the listings at the database level, where PHP never sees
        // them - so their stored images have to go first or they are orphaned for good.
        FederatedEvent::purge(FederatedEvent::where('federated_instance_id', $instance->id));

        $instance->delete();

        AuditService::log(
            AuditService::ADMIN_FEDERATION_DELETE,
            auth()->id(),
            'FederatedInstance',
            null,
            ['site_url' => $siteUrl],
            null,
            'Removed federated instance',
        );

        return back()->with('message', __('messages.federation_instance_removed'));
    }

    /**
     * Adopt the address an approved install now reports, settling the flag it raised.
     *
     * The push path flags a mismatch and leaves the instance approved, so the queue hides
     * the Approve button and there is no status change to settle it with. Approving an
     * already-approved instance now settles a flag through settleFlag(), but settleFlag()
     * deliberately REFUSES this case: an address is on the table, and waving it through
     * without adopting or rejecting it is a dismiss the next push re-raises. So this stays
     * the only way to say yes to a reported address, and Suspend the only way to say no.
     *
     * Not offered in bulk(): site_url is the authority every backlink host check runs
     * against, so adopting one is a per-instance judgement, not a sweep.
     */
    public function acceptAddress(Request $request, string $hash)
    {
        abort_unless(config('app.is_nexus'), 404);

        $instance = FederatedInstance::findOrFail(UrlUtils::decodeId($hash));
        $reported = $instance->reported_site_url;

        // Nothing to adopt: a flag raised by register() (which moves site_url itself), a
        // second submit, or a row flagged before this column existed - those carry the
        // mismatch but not the address, and only the next push can supply it.
        if (! $reported) {
            return back()->with('error', __('messages.federation_address_none_reported'));
        }

        // It arrived signed, but this is the moment it becomes the authority for
        // ownsUrl() and every backlink check, so assert its shape here too - the same
        // rule registration is held to.
        $validator = Validator::make(
            ['site_url' => $reported],
            ['site_url' => ['required', 'url', 'max:255']]
        );

        if ($validator->fails() || ! parse_url($reported, PHP_URL_HOST)) {
            return back()->with('error', __('messages.federation_address_invalid'));
        }

        $previous = $instance->site_url;

        $instance->site_url = $reported;
        $instance->reported_site_url = null;
        $instance->flagged_at = null;
        $instance->save();

        AuditService::log(
            AuditService::ADMIN_FEDERATION_ACCEPT_ADDRESS,
            auth()->id(),
            'FederatedInstance',
            $instance->id,
            ['site_url' => $previous],
            ['site_url' => $reported],
            'Accepted federated instance address change',
        );

        return back()->with('message', __('messages.federation_address_accepted'));
    }

    /**
     * Send the welcome email to an approved install, or send it again.
     *
     * For installs approved before the welcome existed, and for the operator who lost it. The
     * service applies the cooldown, so a double click queues one email.
     */
    public function welcome(Request $request, string $hash)
    {
        abort_unless(config('app.is_nexus'), 404);

        $instance = FederatedInstance::findOrFail(UrlUtils::decodeId($hash));
        $welcome = app(FederationWelcomeService::class);

        if (! $welcome->canWelcome($instance)) {
            return back()->with('error', __('messages.federation_welcome_unavailable'));
        }

        if (! $welcome->canResend($instance)) {
            return back()->with('error', __('messages.federation_welcome_not_sent'));
        }

        // Past both checks, a false here is the queue refusing the job (reported), or another
        // request claiming the row in the same moment.
        if (! $welcome->resend($instance)) {
            return back()->with('error', __('messages.something_went_wrong'));
        }

        $this->logWelcome($instance);

        return back()->with('message', __('messages.federation_welcome_queued'));
    }

    /**
     * The welcome as this install would receive it right now, rendered in the browser. Worth a
     * look before sending it to an install that was approved a while ago.
     */
    public function welcomePreview(Request $request, string $hash)
    {
        abort_unless(config('app.is_nexus'), 404);

        $instance = FederatedInstance::findOrFail(UrlUtils::decodeId($hash));

        // In the operator's language, as the queued job would send it, not the admin's.
        return (new FederationInstanceWelcome($instance))->locale($instance->mailLocale());
    }

    /**
     * Bulk approve, suspend or welcome. Reviewing one at a time does not survive the first
     * week of a network open to every selfhosted install.
     */
    public function bulk(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $validated = $request->validate([
            'action' => ['required', 'in:approve,suspend,welcome'],
            'hashes' => ['required', 'array', 'min:1', 'max:'.self::MAX_BULK],
            'hashes.*' => ['string'],
        ]);

        if ($validated['action'] === 'welcome') {
            $welcome = app(FederationWelcomeService::class);
            $sent = 0;

            foreach ($validated['hashes'] as $hash) {
                $instance = FederatedInstance::find(UrlUtils::decodeId($hash));

                // send(), not resend(): a bulk pass is for installs that never got one, and must
                // not mail everyone selected a second time.
                if ($instance && $welcome->send($instance)) {
                    $this->logWelcome($instance);
                    $sent++;
                }
            }

            return back()->with('message', trans_choice('messages.federation_welcome_bulk_queued', $sent, ['count' => $sent]));
        }

        $status = $validated['action'] === 'approve'
            ? FederatedInstance::STATUS_APPROVED
            : FederatedInstance::STATUS_SUSPENDED;

        $auditAction = $validated['action'] === 'approve'
            ? AuditService::ADMIN_FEDERATION_APPROVE
            : AuditService::ADMIN_FEDERATION_SUSPEND;

        // Counted, not assumed. Approving a selection that is already approved changes
        // nothing at all on the flagged tab, where every row is approved by definition -
        // and a flat "Instances updated" there reported a success that never happened.
        $changed = 0;

        foreach ($validated['hashes'] as $hash) {
            $instance = FederatedInstance::find(UrlUtils::decodeId($hash));
            if ($instance && $this->applyStatus($instance, $status, $auditAction)) {
                $changed++;
            }
        }

        return back()->with('message', trans_choice('messages.federation_instances_bulk_updated', $changed, ['count' => $changed]));
    }

    /**
     * Hide a single listing. Sets blocked_at rather than deleting the row: a delete
     * would be undone by the next push, and dropped again by reconcile, so the block
     * would never stick.
     */
    public function blockEvent(Request $request, string $hash)
    {
        abort_unless(config('app.is_nexus'), 404);

        $event = FederatedEvent::findOrFail(UrlUtils::decodeId($hash));
        $event->isBlocked() ? $event->unblock() : $event->block();

        AuditService::log(
            AuditService::ADMIN_FEDERATION_BLOCK_EVENT,
            auth()->id(),
            'FederatedEvent',
            $event->id,
            null,
            ['blocked' => $event->isBlocked()],
            $event->isBlocked() ? 'Blocked federated listing' : 'Unblocked federated listing',
        );

        return back()->with('message', __('messages.saved'));
    }

    protected function setStatus(string $hash, string $status, string $auditAction)
    {
        abort_unless(config('app.is_nexus'), 404);

        $instance = FederatedInstance::findOrFail(UrlUtils::decodeId($hash));

        // Reported, not assumed. The review button under the amber panel posts here, and on
        // a second submit - or from a page rendered before another admin settled the same
        // row - there is nothing left to do. Flashing "Saved" there is the same lie bulk()
        // used to tell on the flagged tab. Both per-row buttons are hidden in the state that
        // produces this, so it only ever surfaces on a genuinely stale submit.
        if (! $this->applyStatus($instance, $status, $auditAction)) {
            return back()->with('error', __('messages.federation_nothing_changed'));
        }

        return back()->with('message', __('messages.saved'));
    }

    /**
     * Returns whether anything actually changed, so bulk() can report a real count
     * instead of claiming a success on a selection it left untouched.
     */
    protected function applyStatus(FederatedInstance $instance, string $status, string $auditAction): bool
    {
        if ($instance->status === $status) {
            // A redundant approve is not nothing: on a flagged row it is the admin saying
            // they looked and the install is genuine. That is the only way to settle a flag
            // on the flagged tab, where every row is approved already and the per-row
            // Approve button is therefore hidden. A redundant SUSPEND stays a no-op -
            // authenticateInstance() leaves a pending or suspended flag standing for a human.
            return $status === FederatedInstance::STATUS_APPROVED && $this->settleFlag($instance);
        }

        $previous = $instance->status;

        $instance->status = $status;
        if ($status === FederatedInstance::STATUS_APPROVED) {
            $instance->approved_by = auth()->id();
            $instance->approved_at = now();
        }
        // Reviewing the instance settles the mismatch that raised the flag.
        $instance->flagged_at = null;
        $instance->save();

        AuditService::log(
            $auditAction,
            auth()->id(),
            'FederatedInstance',
            $instance->id,
            ['status' => $previous],
            ['status' => $status],
            'Federation instance '.$status,
        );

        // Mail only on an admin decision, never on registration: contact_email arrives
        // unauthenticated, so mailing it earlier would make this a spam relay.
        if (! $instance->contact_email) {
            return true;
        }

        // And not when suspending a registration nobody ever approved. That is how junk is
        // cleared from the queue, and a junk row's address, name and site are whatever the
        // registrant typed - mailing them would send attacker-chosen text under this site's name
        // to whoever they pointed it at. An install that was listed and welcomed is still told.
        if ($status === FederatedInstance::STATUS_SUSPENDED
            && $previous === FederatedInstance::STATUS_PENDING
            && ! $instance->welcomed_at) {
            return true;
        }

        // The first approval gets the welcome, which walks the operator through listing their
        // schedules - approval alone publishes nothing. Everything after that (a suspension, or
        // approval again after one) gets the short decision note. welcomed_at survives both,
        // so an install is welcomed once however often it is reviewed.
        if ($status === FederatedInstance::STATUS_APPROVED && ! $instance->welcomed_at) {
            app(FederationWelcomeService::class)->send($instance);

            return true;
        }

        // Queued rather than sent inline, following the SendQueuedEmail convention used
        // elsewhere: bulk() approves up to MAX_BULK instances in one request, and a
        // blocking Mail::send() per instance would mean that many SMTP round-trips
        // inside a single admin request. No roleId - a federated instance is not one of
        // our schedules, so this goes out on the platform mailer. The operator's own
        // language, never config('app.locale'), which is the acting admin's.
        try {
            SendQueuedEmail::dispatch(
                new FederationInstanceReviewed($instance),
                $instance->contact_email,
                null,
                $instance->mailLocale()
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return true;
    }

    /**
     * Settle the address flag on an instance whose status is not changing.
     *
     * This is the "I looked, it is genuine" review. It is reached by approving an
     * instance that is already approved - which applyStatus() used to swallow whole,
     * leaving the flagged tab's Approve button a guaranteed no-op and AdminAlertService's
     * federation_flagged row with no way to drain, against that service's own rule.
     *
     * Deliberately refuses a flag that carries a reported address. That one is a LIVE
     * mismatch: clearing it without adopting the address (acceptAddress) or rejecting the
     * install (suspend) is a dismiss the next push re-raises within the hour - it
     * recomputes $isNewAddress against a null flagged_at - so the dashboard alert would
     * flap instead of settling.
     *
     * What is left is a flag with nothing to adopt, where site_url on record is all there
     * is to judge. Two ways to get there, and neither is only historical:
     *  - flagged before reported_site_url existed, which is the live nexus row;
     *  - a push flagged a full-URL mismatch (a path or scheme change on the same host),
     *    then the install re-registered on that same host. register() nulls the stale
     *    claim but only stamps flagged_at on a HOST change, so the flag outlives the
     *    address that raised it. See FederationHardeningTest::
     *    test_re_registering_drops_a_pending_address_claim.
     *
     * Not covered: a reported address that fails acceptAddress()'s url validation. Suspend
     * is the right answer to an install reporting junk, and widening this to cover it would
     * mean duplicating that validator in the view to keep the button and the guard aligned.
     */
    protected function settleFlag(FederatedInstance $instance): bool
    {
        if (! $instance->flagged_at || ! $instance->isApproved() || $instance->reported_site_url) {
            return false;
        }

        $instance->flagged_at = null;
        $instance->save();

        AuditService::log(
            AuditService::ADMIN_FEDERATION_CLEAR_FLAG,
            auth()->id(),
            'FederatedInstance',
            $instance->id,
            ['flagged' => true],
            ['flagged' => false],
            'Marked federated instance reviewed',
        );

        // No mail on purpose: nothing about the instance's standing changed, so there is
        // nothing to tell its operator.
        return true;
    }

    protected function logWelcome(FederatedInstance $instance): void
    {
        AuditService::log(
            AuditService::ADMIN_FEDERATION_WELCOME,
            auth()->id(),
            'FederatedInstance',
            $instance->id,
            null,
            ['contact_email' => $instance->contact_email],
            'Sent federation welcome email',
        );
    }
}
