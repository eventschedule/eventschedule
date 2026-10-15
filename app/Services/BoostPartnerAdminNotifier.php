<?php

namespace App\Services;

use App\Models\BoostPartner;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Tells the platform's admins that a partner shared its Page and ad account and is waiting for
 * the one-time confirmation at /admin/boost. The admin alert row says it too; this mail is so a
 * partner is not left waiting on somebody who has not opened /admin today.
 */
class BoostPartnerAdminNotifier
{
    public function awaitingConfirmation(BoostPartner $partner): void
    {
        // Once a day per partner at most: "Check again" must not mail every press.
        if (! Cache::add('boost-partner-awaiting-'.$partner->id, true, now()->addDay())) {
            return;
        }

        $admins = User::where('is_admin', true)->whereNotNull('email')->pluck('email')->all();
        $body = __('messages.boost_partner_admin_waiting_body', [
            'name' => $partner->user?->name ?? '',
            'business' => $partner->ad_account_business_name ?? '',
            'url' => route('admin.boost').'#boost-partners',
        ]);
        $subject = __('messages.boost_partner_admin_waiting_subject');

        // Queued, so the partner's Check again does not wait on the mailer.
        dispatch(function () use ($admins, $body, $subject) {
            foreach ($admins as $email) {
                try {
                    Mail::raw($body, fn ($m) => $m->to($email)->subject($subject));
                } catch (\Exception $e) {
                    Log::warning('Boost partner: could not mail admins', ['error' => $e->getMessage()]);
                }
            }
        });
    }
}
