<?php

namespace App\Http\Controllers;

use App\Models\BoostPartner;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BoostPartnerNotifier;
use App\Services\BoostPartnerService;
use Illuminate\Http\Request;

/**
 * /admin/boost's Boost partners card: enable an account as a partner, and confirm a partner
 * share (we cannot tell which of our accounts shared an asset with our Meta Business, so an
 * admin checks the owning Business shown beside the account before anything launches).
 */
class BoostPartnerAdminController extends Controller
{
    public function enable(Request $request)
    {
        $this->guard();
        if (! config('services.meta.partners_enabled')) {
            abort(404);
        }

        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return back()->with('error', __('messages.boost_partner_no_such_account'));
        }

        $partner = BoostPartner::firstOrNew(['user_id' => $user->id]);
        $partner->forceFill([
            'user_id' => $user->id,
            'enabled_at' => $partner->enabled_at ?? now(),
            'enabled_by' => auth()->id(),
        ])->save();

        AuditService::log(AuditService::BOOST_PARTNER_ENABLE, auth()->id(), 'BoostPartner', $partner->id, null, ['user_id' => $user->id]);

        return redirect()->to(route('admin.boost').'#boost-partners')->with('success', __('messages.boost_partner_enabled'));
    }

    public function confirm(BoostPartner $partner)
    {
        $this->guard();

        if (! $partner->hasAssets() || $partner->connection_method !== BoostPartner::METHOD_PARTNER_SHARE) {
            return back()->with('error', __('messages.boost_partner_nothing_to_confirm'));
        }

        // Confirming says the IDs are the partner's; it cannot make a failing check pass.
        if ($partner->connection_error) {
            return back()->with('error', __('messages.boost_partner_confirm_refused'));
        }

        $partner->forceFill(['confirmed_at' => now(), 'confirmed_by' => auth()->id()])->save();
        AuditService::log(AuditService::BOOST_PARTNER_CONFIRM, auth()->id(), 'BoostPartner', $partner->id);

        app(BoostPartnerService::class)->launchReady($partner);
        BoostPartnerNotifier::connected($partner);

        return redirect()->to(route('admin.boost').'#boost-partners')->with('success', __('messages.boost_partner_confirmed'));
    }

    public function disable(BoostPartner $partner)
    {
        $this->guard();

        // Requests already made keep their partner; new boosts on its schedules go back to the
        // platform's Page. Open requests are declined so nothing waits on nobody.
        $partner->forceFill(['enabled_at' => null])->save();
        \App\Services\AuditService::log(\App\Services\AuditService::BOOST_PARTNER_ENABLE, auth()->id(), 'BoostPartner', $partner->id, null, ['enabled' => false]);
        foreach ($partner->campaigns()->whereIn('status', ['pending_approval', 'awaiting_payment', 'ready'])->pluck('role_id')->unique() as $roleId) {
            if ($role = \App\Models\Role::find($roleId)) {
                app(BoostPartnerService::class)->declineOpenRequestsForRole($role);
            }
        }

        return redirect()->to(route('admin.boost').'#boost-partners')->with('success', __('messages.boost_partner_disabled'));
    }

    protected function guard(): void
    {
        if (! auth()->user()?->isAdmin() || ! config('app.hosted')) {
            abort(403);
        }
    }
}
