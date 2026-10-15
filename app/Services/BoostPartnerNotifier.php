<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\BoostPartnerMail;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Who hears about each step of a partner boost. Mail goes out on the platform's own mailer
 * (roleId null), never a schedule's SMTP: it is the platform writing to an owner or a member.
 */
class BoostPartnerNotifier
{
    public static function requestReceived(BoostCampaign $campaign): void
    {
        static::toPartner('request_received', $campaign);
    }

    public static function requestResent(BoostCampaign $campaign): void
    {
        static::toPartner('request_resent', $campaign);
    }

    public static function approved(BoostCampaign $campaign): void
    {
        static::toBusiness('approved', $campaign);
    }

    public static function declined(BoostCampaign $campaign): void
    {
        static::toBusiness('declined', $campaign);
    }

    public static function live(BoostCampaign $campaign): void
    {
        static::toBusiness('live', $campaign);
    }

    public static function rejected(BoostCampaign $campaign): void
    {
        static::toBusiness('rejected', $campaign);
        static::toPartner('rejected', $campaign);
    }

    /** Whoever did not press Stop hears about it. */
    public static function stopped(BoostCampaign $campaign, ?User $by): void
    {
        $partnerUserId = $campaign->partner?->user_id;

        if (! $by || (int) $by->id === (int) $partnerUserId) {
            static::toBusiness('stopped', $campaign);
        }

        if (! $by || (int) $by->id !== (int) $partnerUserId) {
            static::toPartner('stopped', $campaign);
        }
    }

    public static function accessProblem(?BoostPartner $partner, ?\Throwable $e = null): void
    {
        if (! $partner) {
            return;
        }

        $first = ! $partner->needs_attention_at;
        $partner->forceFill([
            'needs_attention_at' => $partner->needs_attention_at ?? now(),
            'connection_error' => __('messages.boost_partner_access_lost'),
            'connection_checked_at' => now(),
        ])->save();

        if ($e) {
            Log::warning('Boost partner: access problem', ['partner_id' => $partner->id, 'error' => $e->getMessage()]);
        }

        // Once a day at most, however often the sync meets the same problem.
        if ($first && $partner->user && \Illuminate\Support\Facades\Cache::add('boost-partner-access-mail-'.$partner->id, true, now()->addDay())) {
            static::send($partner->user, new BoostPartnerMail('access_problem', null, $partner, true));
        }
    }

    public static function connected(BoostPartner $partner): void
    {
        if ($partner->user) {
            static::send($partner->user, new BoostPartnerMail('connected', null, $partner, true));
        }
    }

    protected static function toPartner(string $kind, BoostCampaign $campaign): void
    {
        $user = $campaign->partner?->user;
        if ($user) {
            static::send($user, new BoostPartnerMail($kind, $campaign, null, true));
        }
    }

    protected static function toBusiness(string $kind, BoostCampaign $campaign): void
    {
        $user = $campaign->user;
        if ($user && (int) $user->id !== (int) $campaign->partner?->user_id) {
            static::send($user, new BoostPartnerMail($kind, $campaign, null, false));
        }
    }

    protected static function send(User $user, BoostPartnerMail $mail): void
    {
        if (! $user->email) {
            return;
        }

        try {
            SendQueuedEmail::dispatch($mail, $user->email, null, $user->language_code ?: null);
        } catch (\Exception $e) {
            Log::warning('Boost partner: failed to queue mail', ['kind' => $mail->kind, 'error' => $e->getMessage()]);
        }
    }
}
