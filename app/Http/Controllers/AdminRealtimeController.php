<?php

namespace App\Http\Controllers;

use App\Models\RealtimeHit;
use App\Models\Setting;
use App\Services\AuditService;
use App\Services\RealtimeDashboard;
use App\Utils\RealtimeTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * /admin/realtime: the page, its polled JSON, and the install-wide switches (Realtime itself, and
 * whether schedule owners get the part about their own pages, which RealtimeController serves).
 *
 * Inside the `admin` middleware like /admin/translations: a lapsed re-auth window answers the poll
 * with 423, which the page turns into a "confirm your password to keep it live" panel, and
 * AdminReauthUtils' fixed ceiling bounds how long polling can keep the window sliding.
 */
class AdminRealtimeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        RealtimeTracker::pruneIfDue();

        return view('admin.realtime', [
            'payload' => RealtimeDashboard::fromRequest($request)->payload(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        RealtimeTracker::pruneIfDue();

        return response()->json(RealtimeDashboard::fromRequest($request)->payload());
    }

    /**
     * Its own route, never the shared admin.settings.update: that one overwrites the custom
     * header and footer code unless the federation card's marker field is present.
     *
     * Stored as '1'/'0', never null, because Setting::get() returns the default for null and the
     * default is ON for the nexus - a null "off" could not be saved there.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (is_demo_mode()) {
            return redirect()->route('admin.settings')->with('error', __('messages.demo_mode_settings_disabled'));
        }

        $wasEnabled = RealtimeTracker::enabled();
        $enabled = $request->boolean('realtime_enabled');

        Setting::set('realtime_enabled', $enabled ? '1' : '0');

        if ($enabled && ! $wasEnabled) {
            Setting::set('realtime_enabled_at', (string) now()->getTimestamp());
        }

        // Whether schedule owners see their own pages' traffic at /realtime. Written only when the
        // form that showed the switch was the one posted: absent is not "off", and a client that
        // sends the first switch alone must not take the live view away from every organizer.
        $ownerViewBefore = RealtimeTracker::ownerViewSetting();
        $ownerView = $request->has('realtime_owner_view_submitted')
            ? $request->boolean('realtime_owner_view')
            : $ownerViewBefore;

        if ($ownerView !== $ownerViewBefore) {
            Setting::set('realtime_owner_view', $ownerView ? '1' : '0');
        }

        // Nothing to record about WHEN the view came on. The cookie notice names organizers
        // exactly while both switches are on, and each choice records whether the notice it was
        // made on did (RealtimeTracker::consentCoversOrganizers()), so a choice made while the
        // view was off never lists anyone, whatever the order of saves.

        if (! $enabled) {
            // Saved as off first, so a beacon arriving mid-purge is already refused. Batched
            // DELETE rather than TRUNCATE, which commits implicitly.
            do {
                $ids = DB::table('realtime_hits')->orderBy('id')->limit(5000)->pluck('id');
                if ($ids->isNotEmpty()) {
                    RealtimeHit::whereIn('id', $ids->all())->delete();
                }
            } while ($ids->count() === 5000);
        }

        AuditService::log(
            AuditService::ADMIN_SETTINGS_UPDATE,
            $request->user()->id,
            null,
            null,
            ['realtime_enabled' => $wasEnabled ? '1' : '0', 'realtime_owner_view' => $ownerViewBefore ? '1' : '0'],
            ['realtime_enabled' => $enabled ? '1' : '0', 'realtime_owner_view' => $ownerView ? '1' : '0'],
            'Updated realtime visitor settings',
        );

        return redirect()->to(route('admin.settings').'#realtime')->with('success', __('messages.settings_saved'));
    }
}
