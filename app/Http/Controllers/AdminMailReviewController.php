<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use App\Models\User;
use App\Services\AudienceMailGuard;
use App\Services\AuditService;
use App\Services\NewsletterService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

/**
 * /admin/mail-review: what AudienceMailGuard held back for an operator to look at.
 *
 * Two lists. Newsletters held for review (status 'review'): who sent it, how new the account is
 * and whether it has paid, what it says (a preview, and the watched word that held it, if one
 * did), and how many of the people it is for came from an import. Approve sends it through
 * NewsletterService::send() like any other and trusts the owner from then on; Send back returns
 * it to a draft with a note the owner reads on the newsletter. And accounts whose email sending is
 * paused, the ones that asked to have it back first: Enable lets it back on (and trusts the
 * owner), Keep paused answers the request and leaves it.
 *
 * Hosted only, as the guard is. Every action is a POST behind the admin middleware.
 */
class AdminMailReviewController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        abort_unless(config('app.hosted'), 404);
    }

    public function index(NewsletterService $service)
    {
        $this->authorizeAdmin();

        // Any schedule's row in review, whatever its type column says (older rows have none).
        $held = AudienceMailGuard::heldQuery()
            ->with(['role.user', 'role.subscriptions', 'user'])
            ->orderBy('held_at')
            ->paginate(20, ['*'], 'held')
            ->withQueryString();

        $rows = $held->getCollection()->map(function (Newsletter $newsletter) use ($service) {
            $role = $newsletter->role;
            $owner = $role?->user;

            return [
                'newsletter' => $newsletter,
                'role' => $role,
                'owner' => $owner,
                // Recorded when it was held (AudienceMailGuard::hold()); worked out only for one
                // held before that was recorded.
                'mix' => is_array($newsletter->review_mix)
                    ? $newsletter->review_mix + ['opted_in' => 0, 'imported' => 0, 'sends_to' => 0]
                    : ($role ? $service->audienceMix($role, $newsletter->segment_ids ?? []) : ['opted_in' => 0, 'imported' => 0, 'sends_to' => 0]),
                'word' => AudienceMailGuard::watchedWordIn($newsletter),
                'plan' => $role ? $role->actualPlanTier() : 'free',
                'trial' => $role ? $role->isOnTrial() : false,
                'paid' => AudienceMailGuard::ownerHasPaid($owner),
            ];
        });

        // Asked for their email back first, then the rest of what is paused.
        $requests = AudienceMailGuard::openRequests()->orderBy('mail_access_requested_at')->limit(50)->get();
        $paused = User::whereNotNull('mail_disabled_at')
            ->whereNotIn('id', $requests->pluck('id'))
            ->orderByDesc('mail_disabled_at')
            ->paginate(20, ['*'], 'paused')
            ->withQueryString();

        // What would have paused an account that has paid: shown, and nothing stopped.
        $flagged = AudienceMailGuard::flagged()->orderByDesc('mail_flagged_at')->limit(50)->get();

        return view('admin.mail-review', [
            'held' => $held,
            'rows' => $rows,
            'requests' => $requests,
            'paused' => $paused,
            'flagged' => $flagged,
        ]);
    }

    /** The newsletter as it would go, for the operator to read before deciding. */
    public function preview(string $hash, NewsletterService $service)
    {
        $this->authorizeAdmin();

        $newsletter = Newsletter::whereNotNull('role_id')->findOrFail(UrlUtils::decodeId($hash));

        // The owner's HTML, shown to an operator: no script, nothing loaded from elsewhere (an
        // image on the owner's server would tell them who looked, and when). SecurityHeaders
        // sends this policy in place of the app's.
        request()->attributes->set('csp_override', "sandbox; default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'");

        return response($service->renderPreview($newsletter));
    }

    public function approve(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorizeAdmin();

        $newsletter = Newsletter::whereNotNull('role_id')->findOrFail(UrlUtils::decodeId($hash));

        // Claimed out of the queue, so two operators pressing at once approve it once.
        $claimed = Newsletter::whereKey($newsletter->id)
            ->where('status', 'review')
            ->update(['status' => 'draft', 'held_at' => null, 'approved_at' => now(), 'review_note' => null]);

        if ($claimed === 0) {
            return redirect()->route('admin.mail_review')->with('error', __('messages.mail_review_already_decided'));
        }

        $owner = $newsletter->role?->user;
        if ($owner && ! $owner->newsletter_trusted_at) {
            $owner->forceFill(['newsletter_trusted_at' => now()])->save();
        }

        AuditService::log(AuditService::NEWSLETTER_APPROVED, $request->user()->id, 'App\\Models\\Newsletter', $newsletter->id);

        $result = $service->send($newsletter->fresh());

        if ($result === true) {
            return redirect()->route('admin.mail_review')->with('success', __('messages.mail_review_approved'));
        }

        // Approved, and left as an approved draft its owner can send: say why it did not go.
        $reason = is_array($result) ? $result[0] : 'failed';

        return redirect()->route('admin.mail_review')->with('warning', __('messages.mail_review_approved_not_sent', [
            'reason' => __('messages.mail_review_not_sent_'.(in_array($reason, ['mail_paused', 'imported_unpaid', 'no_recipients', 'limit_exceeded', 'requires_verification'], true) ? $reason : 'failed')),
        ]));
    }

    public function reject(Request $request, string $hash)
    {
        $this->authorizeAdmin();

        $validated = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        $newsletter = Newsletter::whereNotNull('role_id')->findOrFail(UrlUtils::decodeId($hash));

        $claimed = Newsletter::whereKey($newsletter->id)
            ->where('status', 'review')
            ->update([
                'status' => 'draft',
                'held_at' => null,
                'approved_at' => null,
                'review_note' => trim((string) ($validated['note'] ?? '')) ?: __('messages.mail_review_default_note'),
            ]);

        if ($claimed === 0) {
            return redirect()->route('admin.mail_review')->with('error', __('messages.mail_review_already_decided'));
        }

        AuditService::log(AuditService::NEWSLETTER_REJECTED, $request->user()->id, 'App\\Models\\Newsletter', $newsletter->id,
            null, ['note' => $validated['note'] ?? null]);

        return redirect()->route('admin.mail_review')->with('success', __('messages.mail_review_rejected'));
    }

    public function pause(Request $request, string $hash)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail(UrlUtils::decodeId($hash));

        $done = AudienceMailGuard::pause($user, AudienceMailGuard::REASON_ADMIN, $request->user());

        return redirect()->back()->with($done ? 'success' : 'warning', $done
            ? __('messages.mail_paused_done', ['name' => $user->name ?: $user->email])
            : __('messages.mail_paused_already', ['name' => $user->name ?: $user->email]));
    }

    public function enable(Request $request, string $hash)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail(UrlUtils::decodeId($hash));
        AudienceMailGuard::enable($user, $request->user());

        return redirect()->back()->with('success', __('messages.mail_enabled_done', ['name' => $user->name ?: $user->email]));
    }

    /** A flagged account that has paid is fine: the flag goes and the checks leave it alone. */
    public function dismissFlag(Request $request, string $hash)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail(UrlUtils::decodeId($hash));
        AudienceMailGuard::dismissFlag($user, $request->user());

        return redirect()->back()->with('success', __('messages.mail_flag_dismissed', ['name' => $user->name ?: $user->email]));
    }

    public function keepPaused(Request $request, string $hash)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail(UrlUtils::decodeId($hash));
        AudienceMailGuard::keepPaused($user, $request->user());

        return redirect()->back()->with('success', __('messages.mail_kept_paused_done', ['name' => $user->name ?: $user->email]));
    }
}
