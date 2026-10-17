<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\Role;
use App\Services\ReferralCreditService;
use App\Utils\PlatformPricing;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function index(ReferralCreditService $credits)
    {
        $user = auth()->user();
        $referralUrl = $user->referralUrl();

        // Every referral counted once, at the step it is on.
        $byStatus = $user->referrals()
            ->selectRaw('status, count(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status');
        $stepCounts = [
            (int) ($byStatus['pending'] ?? 0),
            (int) ($byStatus['subscribed'] ?? 0),
            (int) ($byStatus['qualified'] ?? 0),
            (int) ($byStatus['credited'] ?? 0),
        ];
        $expiredCount = (int) ($byStatus['expired'] ?? 0);
        $totalReferrals = array_sum($stepCounts) + $expiredCount;

        // The first credit still on its way: statuses move once a day, so it is due "on" the day.
        $firstSubscribed = $user->referrals()->where('status', 'subscribed')->min('subscribed_at');
        $nextCreditAt = $firstSubscribed
            ? \Carbon\Carbon::parse($firstSubscribed)->addDays(ReferralCreditService::DAYS)
            : null;

        $qualifiedCredits = $user->referrals()
            ->where('status', 'qualified')
            ->orderBy('qualified_at')
            ->get();

        // Each schedule's subscription is read once, for every credit's line.
        $ownedRoles = $user->owner()->with('subscriptions')->get()->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        // What Apply does with each credit on each schedule, said before it is pressed.
        $outcomes = [];
        foreach ($qualifiedCredits as $credit) {
            foreach ($ownedRoles as $role) {
                $outcomes[$credit->id][$role->id] = $credits->sentence($credits->outcome($credit->plan_type, $role), $credit->plan_type, $role);
            }
        }

        $sortBy = request()->get('sort_by', 'created_at');
        $sortDir = strtolower(request()->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSortColumns = ['created_at', 'status'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $referralHistory = $user->referrals()
            ->with('referredUser', 'creditedRole')
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', $sortDir)
            ->paginate(20)
            ->withQueryString();

        return view('referral.index', [
            'referralUrl' => $referralUrl,
            'totalReferrals' => $totalReferrals,
            'stepCounts' => $stepCounts,
            'expiredCount' => $expiredCount,
            'nextCreditAt' => $nextCreditAt,
            'qualifiedCredits' => $qualifiedCredits,
            'ownedRoles' => $ownedRoles,
            'outcomes' => $outcomes,
            'referralHistory' => $referralHistory,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    public function applyCredit(Request $request, ReferralCreditService $credits)
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
            $said = DB::transaction(function () use ($referralId, $user, $role, $credits) {
                $referral = Referral::lockForUpdate()
                    ->where('id', $referralId)
                    ->where('referrer_user_id', $user->id)
                    ->where('status', 'qualified')
                    ->firstOrFail();

                // In the currency's smallest unit, from the same source the referral page and
                // the credit email quote. Hardcoded, a price change moved what we advertise
                // without moving what we actually credit.
                //
                // The multiplier is looked up, not a literal 100: a zero-decimal currency
                // (JPY, KRW) has no cents, and multiplying by 100 there would credit a
                // referrer a hundred times the plan price.
                //
                // It comes from cashier.currency, NOT the platform currency: applyBalance()
                // below posts the transaction in Cashier's preferredCurrency(), so reading the
                // multiplier from anywhere else lets the two disagree and mis-credits by 100x.
                //
                // minorUnits() owns the multiply-then-round order; doing it the other way round
                // truncated 9.99 to 9 and credited 900 instead of 999.
                $creditAmount = -PlatformPricing::minorUnits(
                    $referral->plan_type === 'enterprise' ? 'enterprise' : 'pro',
                    'monthly',
                    config('cashier.currency', 'usd')
                );

                // The same rule the page used to say what this would do.
                $outcome = $credits->outcome($referral->plan_type, $role);

                if ($outcome['kind'] === 'invoice') {
                    $role->applyBalance($creditAmount, __('messages.referral_credit'));
                } else {
                    // Never a lower tier than the schedule is on: a Pro credit used to move a
                    // schedule with Enterprise time left down to Pro.
                    $role->plan_type = $outcome['plan'];
                    $role->plan_expires = $outcome['until']->format('Y-m-d');
                    // Earned, not comped: the guest-footer credit is only for plans an admin
                    // handed out, so this must not read as one. Only claim provenance when none
                    // is recorded - the block above STACKS onto an existing plan, so overwriting
                    // would let one redeemed referral strip the credit from an admin grant for
                    // good.
                    $role->plan_source ??= 'referral';
                    $role->save();
                }

                $referral->update([
                    'credited_role_id' => $role->id,
                    'credited_at' => now(),
                    'status' => 'credited',
                ]);

                return $credits->sentence($outcome, $referral->plan_type, $role);
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
            ->with('message', __('messages.referral_credit_applied').' '.$said);
    }
}
