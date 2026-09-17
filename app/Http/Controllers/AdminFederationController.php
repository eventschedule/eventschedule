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

        foreach ($validated['hashes'] as $hash) {
            $instance = FederatedInstance::find(UrlUtils::decodeId($hash));
            if ($instance) {
                $this->applyStatus($instance, $status, $auditAction);
            }
        }

        return back()->with('message', __('messages.federation_instances_updated'));
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
        $this->applyStatus($instance, $status, $auditAction);

        return back()->with('message', __('messages.saved'));
    }

    protected function applyStatus(FederatedInstance $instance, string $status, string $auditAction): void
    {
        if ($instance->status === $status) {
            return;
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
            return;
        }

        // And not when suspending a registration nobody ever approved. That is how junk is
        // cleared from the queue, and a junk row's address, name and site are whatever the
        // registrant typed - mailing them would send attacker-chosen text under this site's name
        // to whoever they pointed it at. An install that was listed and welcomed is still told.
        if ($status === FederatedInstance::STATUS_SUSPENDED
            && $previous === FederatedInstance::STATUS_PENDING
            && ! $instance->welcomed_at) {
            return;
        }

        // The first approval gets the welcome, which walks the operator through listing their
        // schedules - approval alone publishes nothing. Everything after that (a suspension, or
        // approval again after one) gets the short decision note. welcomed_at survives both,
        // so an install is welcomed once however often it is reviewed.
        if ($status === FederatedInstance::STATUS_APPROVED && ! $instance->welcomed_at) {
            app(FederationWelcomeService::class)->send($instance);

            return;
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
