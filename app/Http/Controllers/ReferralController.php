<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $referralCode = $user->getOrCreateReferralCode();
        $referralUrl = $user->referralUrl();

        $totalReferrals = $user->referrals()->count();
        $awaitingSubscription = $user->referrals()->where('status', 'pending')->count();
        $awaitingQualification = $user->referrals()->where('status', 'subscribed')->count();
        $creditsEarned = $user->referrals()->where('status', 'credited')->count();

        $qualifiedCredits = $user->referrals()
            ->where('status', 'qualified')
            ->with('referredUser', 'referredRole')
            ->get();

        $ownedRoles = $user->owner()->get();

        $sortBy = request()->get('sort_by', 'created_at');
        $sortDir = strtolower(request()->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSortColumns = ['created_at', 'status'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $referralHistory = $user->referrals()
            ->with('referredUser', 'referredRole', 'creditedRole')
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();

        return view('referral.index', [
            'referralUrl' => $referralUrl,
            'totalReferrals' => $totalReferrals,
            'awaitingSubscription' => $awaitingSubscription,
            'awaitingQualification' => $awaitingQualification,
            'creditsEarned' => $creditsEarned,
            'qualifiedCredits' => $qualifiedCredits,
            'ownedRoles' => $ownedRoles,
            'referralHistory' => $referralHistory,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    public function applyCredit(Request $request)
    {
        $request->validate([
            'referral_id' => 'required|string',
            'role_id' => 'required|string',
        ]);

        $referralId = UrlUtils::decodeIdOrFail($request->referral_id);
        $roleId = UrlUtils::decodeIdOrFail($request->role_id);

        $user = auth()->user();

        $role = Role::where('id', $roleId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        try {
            DB::transaction(function () use ($referralId, $user, $role) {
                $referral = Referral::lockForUpdate()
                    ->where('id', $referralId)
                    ->where('referrer_user_id', $user->id)
                    ->where('status', 'qualified')
                    ->firstOrFail();

                $creditAmount = $referral->plan_type === 'enterprise' ? -1500 : -500;

                if ($role->hasActiveSubscription()) {
                    $role->applyBalance($creditAmount, __('messages.referral_credit'));
                } else {
                    $planType = $referral->plan_type ?? 'pro';
                    $role->plan_type = $planType;
                    $role->plan_expires = $role->plan_expires && $role->plan_expires > now()
                        ? \Carbon\Carbon::parse($role->plan_expires)->addDays(30)
                        : now()->addDays(30);
                    $role->save();
                }

                $referral->update([
                    'credited_role_id' => $role->id,
                    'credited_at' => now(),
                    'status' => 'credited',
                ]);
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('referrals')
                ->with('error', __('messages.referral_credit_not_found'));
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('referrals')
                ->with('error', __('messages.an_error_occurred'));
        }

        return redirect()->route('referrals')
            ->with('message', __('messages.referral_credit_applied'));
    }
}
