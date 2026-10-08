<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Everything held about one account holder, as a portable document: the "Download my data" file
 * (GDPR Arts. 15 and 20). ProfileController queues it, App\Jobs\ExportPersonalData writes it.
 *
 * Found by the account's id AND by its email address, because a good part of what is held about a
 * person was collected before, or without, the account: a ticket bought as a guest, a sign-up to a
 * schedule's emails, an interest list, a waitlist, a newsletter received.
 *
 * Never included: credentials and secrets (password hash, OAuth and API tokens, two-factor
 * secrets, ticket and unsubscribe secrets, session payloads, webhook signing secrets), and other
 * people's personal data beyond what identifies the thing the person did (the schedule's or
 * event's name). A schedule's own audience belongs in that schedule's backup, not here.
 *
 * Every table this person's rows live in is listed here. A new table that stores an email address
 * or a user_id belongs in this file; PersonalDataExportTest checks the list against the schema.
 */
class PersonalDataExportService
{
    /** Tables build() reads this person's rows from. */
    public const EXPORTED = [
        'users', 'role_user', 'role_transfers', 'subscription_cancellations', 'sales', 'gift_cards',
        'event_interests', 'ticket_waitlists', 'role_subscribers', 'newsletter_recipients',
        'newsletter_clicks', 'newsletter_unsubscribes', 'event_comments', 'event_photos',
        'event_videos', 'event_poll_votes', 'carpool_offers', 'carpool_requests', 'carpool_reviews',
        'carpool_reports', 'support_conversations', 'referrals', 'boost_campaigns', 'webhooks',
        'audit_logs', 'sessions', 'realtime_hits', 'user_active_days', 'event_feeds',
    ];

    /**
     * Tables that hold personal data by user_id or email and are deliberately left out, with why.
     * PersonalDataExportTest fails when a table appears that is in neither list.
     */
    public const NOT_EXPORTED = [
        'account_blocks' => 'An operator\'s record of a blocked account. A blocked account cannot sign in to ask for this file, and the row is deleted when the block is lifted; the fact and its date are in account.blocked_at.',
        'backup_jobs' => 'Job bookkeeping; the archives are the user\'s own downloads.',
        'calendar_syncs' => 'Which event maps to which calendar entry; covered by the calendar_connections summary.',
        'microsoft_calendar_syncs' => 'Same, for Outlook.',
        'dismissed_next_steps' => 'Interface state.',
        'dismissed_timezone_warnings' => 'Interface state.',
        'dismissed_venue_merge_suggestions' => 'Interface state.',
        'event_templates' => 'Event drafts the user saved, part of their schedules.',
        'events' => 'Published content, exported with the schedule backup.',
        'gallery_images' => 'Published content, exported with the schedule backup.',
        'newsletter_segment_users' => 'An organizer\'s list; what reached this person is in newsletters_received.',
        'newsletter_templates' => 'Content.',
        'newsletters' => 'Content the user wrote for a schedule, exported with its backup.',
        'owner_digests' => 'Bookkeeping of which weekly digest was sent.',
        'password_reset_tokens' => 'Credentials.',
        'roles' => 'Schedules; summarised under schedules, the full content is in the schedule backup.',
        'sale_refunds' => 'Refund bookkeeping, summarised on each purchase.',
        'support_messages' => 'Exported inside support_conversations.',
    ];

    /**
     * Whether this install can deliver the file. It goes out only as a link in an email, never
     * shown in the app: a selfhost sign-up proves nothing about its address (RegisteredUserController),
     * and the export includes what other people recorded against that address. An install whose
     * mailer only writes to the log would accept the request and deliver nothing.
     */
    public static function canDeliver(): bool
    {
        return (bool) config('app.hosted') || config('mail.default') !== 'log';
    }

    public function build(User $user): array
    {
        $email = strtolower((string) $user->email);

        return [
            'generated_at' => now()->toIso8601String(),
            'about' => 'Everything Event Schedule holds about this account and its email address. See the privacy policy for what each part is used for and how long it is kept.',
            'account' => $this->account($user),
            'schedules' => $this->schedules($user),
            'schedule_transfers' => $this->rows('role_transfers', fn ($q) => $q->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id)->orWhere('to_email', $email),
                ['role_id', 'status', 'keep_previous_owner', 'expires_at', 'responded_at', 'created_at']),
            'subscription_cancellations' => $this->rows('subscription_cancellations', fn ($q) => $q->where('user_id', $user->id),
                ['role_id', 'source', 'reason', 'comment', 'plan_type', 'plan_term', 'resumed_at', 'created_at']),
            'purchases' => $this->purchases($user, $email),
            'gift_cards' => $this->rows('gift_cards', fn ($q) => $q->where('purchaser_email', $email)->orWhere('recipient_email', $email),
                ['role_id', 'amount', 'remaining_amount', 'currency_code', 'status', 'purchaser_name', 'purchaser_email', 'recipient_name', 'recipient_email', 'message', 'expires_at', 'created_at']),
            'event_updates_requested' => $this->rows('event_interests', fn ($q) => $q->where('email', $email),
                ['event_id', 'event_date', 'email', 'name', 'locale', 'source', 'confirmed_at', 'ip_address', 'created_at']),
            'waitlists' => $this->rows('ticket_waitlists', fn ($q) => $q->where('email', $email),
                ['event_id', 'event_date', 'name', 'email', 'status', 'locale', 'notified_at', 'created_at']),
            'following' => DB::table('role_user')->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $user->id)->where('role_user.level', 'follower')
                ->get(['roles.name as schedule', 'roles.subdomain', 'role_user.created_at'])->all(),
            'email_signups' => DB::table('role_subscribers')->leftJoin('roles', 'roles.id', '=', 'role_subscribers.role_id')
                ->where('role_subscribers.email', $email)
                ->get(['roles.name as schedule', 'role_subscribers.email', 'role_subscribers.name', 'role_subscribers.locale', 'role_subscribers.source', 'role_subscribers.confirmed_at', 'role_subscribers.ip_address', 'role_subscribers.created_at'])->all(),
            'newsletters_received' => $this->newsletters($user, $email),
            'newsletter_unsubscribes' => $this->rows('newsletter_unsubscribes', fn ($q) => $q->where('email', $email), ['role_id', 'unsubscribed_at']),
            'comments' => $this->rows('event_comments', fn ($q) => $q->where('user_id', $user->id)->orWhere('guest_email', $email),
                ['event_id', 'event_date', 'guest_name', 'guest_email', 'comment', 'is_approved', 'created_at']),
            'photos' => $this->rows('event_photos', fn ($q) => $q->where('user_id', $user->id)->orWhere('guest_email', $email),
                ['event_id', 'event_date', 'guest_name', 'guest_email', 'photo_url', 'is_approved', 'created_at']),
            'videos' => $this->rows('event_videos', fn ($q) => $q->where('user_id', $user->id)->orWhere('guest_email', $email),
                ['event_id', 'event_date', 'guest_name', 'guest_email', 'youtube_url', 'is_approved', 'created_at']),
            'poll_votes' => $this->rows('event_poll_votes', fn ($q) => $q->where('user_id', $user->id), ['event_poll_id', 'option_index', 'event_date', 'created_at']),
            'carpool' => [
                'offers' => $this->rows('carpool_offers', fn ($q) => $q->where('user_id', $user->id),
                    ['event_id', 'event_date', 'direction', 'city', 'departure_time', 'meeting_point', 'total_spots', 'note', 'status', 'created_at']),
                'requests' => $this->rows('carpool_requests', fn ($q) => $q->where('user_id', $user->id), ['carpool_offer_id', 'message', 'status', 'created_at']),
                'reviews_written' => $this->rows('carpool_reviews', fn ($q) => $q->where('reviewer_user_id', $user->id), ['carpool_offer_id', 'rating', 'comment', 'created_at']),
                'reviews_received' => $this->rows('carpool_reviews', fn ($q) => $q->where('reviewed_user_id', $user->id), ['carpool_offer_id', 'rating', 'comment', 'created_at']),
                'reports_made' => $this->rows('carpool_reports', fn ($q) => $q->where('reporter_user_id', $user->id), ['carpool_offer_id', 'reason', 'created_at']),
            ],
            'support_conversations' => $this->support($user, $email),
            'referrals' => $this->rows('referrals', fn ($q) => $q->where('referrer_user_id', $user->id)->orWhere('referred_user_id', $user->id),
                ['plan_type', 'status', 'subscribed_at', 'qualified_at', 'credited_at', 'created_at']),
            'boost_campaigns' => $this->rows('boost_campaigns', fn ($q) => $q->where('user_id', $user->id),
                ['event_id', 'role_id', 'channel', 'name', 'status', 'currency_code', 'user_budget', 'total_charged', 'scheduled_start', 'scheduled_end', 'created_at']),
            'webhooks' => $this->rows('webhooks', fn ($q) => $q->where('user_id', $user->id), ['url', 'event_types', 'is_active', 'description', 'last_triggered_at', 'created_at']),
            // The feeds this person added to a schedule. The security log is pruned after 90
            // days, so this row is the lasting record of who did. Its name, the site it reads
            // and when: not the address, which is a credential and is stored encrypted.
            'feeds_added' => $this->rows('event_feeds', fn ($q) => $q->where('added_by', $user->id), ['role_id', 'name', 'host', 'kind', 'created_at']),
            'calendar_connections' => [
                'google' => (bool) $user->google_token,
                'microsoft' => (bool) $user->microsoft_token,
                'google_events_synced' => DB::table('calendar_syncs')->where('user_id', $user->id)->count(),
                'microsoft_events_synced' => DB::table('microsoft_calendar_syncs')->where('user_id', $user->id)->count(),
            ],
            'security_log' => $this->rows('audit_logs', fn ($q) => $q->where('user_id', $user->id), ['action', 'model_type', 'ip_address', 'user_agent', 'created_at']),
            'sessions' => DB::table('sessions')->where('user_id', $user->id)->get(['ip_address', 'user_agent', 'last_activity'])
                ->map(fn ($row) => [
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'last_activity' => date(DATE_ATOM, (int) $row->last_activity),
                ])->all(),
            'recent_page_views' => $this->rows('realtime_hits', fn ($q) => $q->where('user_id', $user->id),
                ['surface', 'path', 'title', 'country', 'device', 'browser', 'os', 'started_at', 'last_seen_at']),
            // The dates only: that is all the record holds. `counted` false means the day was
            // worked out from the security log above rather than recorded.
            'days_active' => $this->rows('user_active_days', fn ($q) => $q->where('user_id', $user->id), ['date', 'counted']),
        ];
    }

    private function account(User $user): array
    {
        $row = (array) DB::table('users')->where('id', $user->id)->first([
            'name', 'email', 'phone', 'phone_verified_at', 'email_verified_at', 'terms_accepted_at',
            'timezone', 'language_code', 'is_subscribed', 'suggestions_off_at', 'utm_source', 'utm_medium', 'utm_campaign',
            'utm_content', 'utm_term', 'referrer_url', 'landing_page', 'referral_code',
            'profile_image_url', 'created_at', 'updated_at',
        ]);

        foreach (['hero_variant', 'signup_intent', 'push_settings', 'signup_ip', 'blocked_at'] as $optional) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', $optional)) {
                $row[$optional] = DB::table('users')->where('id', $user->id)->value($optional);
            }
        }

        $row['sign_in_with_google'] = (bool) $user->google_id;
        $row['sign_in_with_facebook'] = (bool) ($user->facebook_id ?? false);
        $row['two_factor_enabled'] = (bool) $user->two_factor_confirmed_at;

        return $row;
    }

    private function schedules(User $user): array
    {
        return DB::table('role_user')->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->where('role_user.level', '!=', 'follower')
            ->get(['roles.name', 'roles.subdomain', 'roles.type', 'roles.email', 'role_user.level', 'role_user.created_at'])
            ->all();
    }

    private function purchases(User $user, string $email): array
    {
        return DB::table('sales')
            ->leftJoin('events', 'events.id', '=', 'sales.event_id')
            ->where(fn ($q) => $q->where('sales.user_id', $user->id)->orWhere('sales.email', $email))
            ->orderBy('sales.created_at')
            ->get([
                'events.name as event', 'sales.event_date', 'sales.name', 'sales.email', 'sales.phone',
                'sales.status', 'sales.payment_method', 'sales.payment_amount', 'sales.discount_amount',
                'sales.paid_at', 'sales.created_at', 'sales.custom_value1', 'sales.custom_value2',
                'sales.custom_value3', 'sales.custom_value4', 'sales.custom_value5', 'sales.custom_value6',
                'sales.custom_value7', 'sales.custom_value8', 'sales.custom_value9', 'sales.custom_value10',
            ])
            ->map(fn ($row) => array_filter((array) $row, fn ($value) => $value !== null))
            ->all();
    }

    private function newsletters(User $user, string $email): array
    {
        $recipients = DB::table('newsletter_recipients')
            ->join('newsletters', 'newsletters.id', '=', 'newsletter_recipients.newsletter_id')
            ->leftJoin('roles', 'roles.id', '=', 'newsletters.role_id')
            ->where(fn ($q) => $q->where('newsletter_recipients.user_id', $user->id)->orWhere('newsletter_recipients.email', $email))
            ->get([
                'newsletter_recipients.id', 'roles.name as schedule', 'newsletters.subject',
                'newsletter_recipients.email', 'newsletter_recipients.status', 'newsletter_recipients.sent_at',
                'newsletter_recipients.opened_at', 'newsletter_recipients.open_count',
                'newsletter_recipients.clicked_at', 'newsletter_recipients.click_count',
            ]);

        $clicks = DB::table('newsletter_clicks')
            ->whereIn('newsletter_recipient_id', $recipients->pluck('id'))
            ->get(['newsletter_recipient_id', 'url', 'clicked_at'])
            ->groupBy('newsletter_recipient_id');

        return $recipients->map(function ($row) use ($clicks) {
            $out = (array) $row;
            $out['schedule'] = $out['schedule'] ?? 'Event Schedule';
            $out['links_clicked'] = ($clicks[$row->id] ?? collect())->map(fn ($c) => ['url' => $c->url, 'clicked_at' => $c->clicked_at])->values()->all();
            unset($out['id']);

            return $out;
        })->all();
    }

    private function support(User $user, string $email): array
    {
        return DB::table('support_conversations')
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('guest_email', $email))
            ->get(['id', 'status', 'guest_name', 'guest_email', 'guest_page', 'guest_country', 'created_at'])
            ->map(function ($conversation) {
                $out = (array) $conversation;
                $out['messages'] = DB::table('support_messages')
                    ->where('support_conversation_id', $conversation->id)
                    ->orderBy('created_at')
                    ->get(['is_from_admin', 'body', 'created_at'])
                    ->map(fn ($m) => ['from' => $m->is_from_admin ? 'Event Schedule' : 'you', 'body' => $m->body, 'at' => $m->created_at])
                    ->all();
                unset($out['id']);

                return $out;
            })->all();
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function rows(string $table, \Closure $where, array $columns): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)->where($where)->get($columns)->map(fn ($row) => (array) $row)->all();
    }
}
