<?php

namespace App\Http\Controllers;

use App\Jobs\SendQueuedEmail;
use App\Mail\FederationInstanceReviewed;
use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Services\AuditService;
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

        return view('admin.federation', [
            'instances' => $instances,
            'samples' => $samples,
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
     * Bulk approve or suspend. Reviewing one at a time does not survive the first
     * week of a network open to every selfhosted install.
     */
    public function bulk(Request $request)
    {
        abort_unless(config('app.is_nexus'), 404);

        $validated = $request->validate([
            'action' => ['required', 'in:approve,suspend'],
            'hashes' => ['required', 'array', 'min:1', 'max:'.self::MAX_BULK],
            'hashes.*' => ['string'],
        ]);

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
        //
        // Queued rather than sent inline, following the SendQueuedEmail convention used
        // elsewhere: bulk() approves up to MAX_BULK instances in one request, and a
        // blocking Mail::send() per instance would mean that many SMTP round-trips
        // inside a single admin request. No roleId - a federated instance is not one of
        // our schedules, so this goes out on the platform mailer.
        if ($instance->contact_email) {
            try {
                SendQueuedEmail::dispatch(
                    new FederationInstanceReviewed($instance),
                    $instance->contact_email
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
