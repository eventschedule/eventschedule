<?php

namespace App\Services;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\Role;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Subscription;

/**
 * What a schedule may mail its audience through the platform's own sender, beyond the size and
 * verification limits Role::canSendAudienceMail() already sets.
 *
 * On 2026-11-18 a new account on a free trial imported a list of addresses and sent a phishing
 * newsletter in the name of a real conference, from the platform's own address. Three rules
 * answer it, and every send path asks them here (NewsletterService::send() for a send, a
 * scheduled send and a repeat's copy; NewsletterController for the send step, A/B tests and the
 * builder's audience step; NewsletterService::sendToRemainingRecipients() for a test's winner;
 * SendNewsletterBatch for a send already going out):
 *
 *  A. Addresses nobody gave the schedule themselves are mailed only once the schedule's owner has
 *     PAID (ownerHasPaid(): an invoice above zero that Stripe told us was paid, or a plan an
 *     operator granted). A trial is not paid. "Not given themselves" is a manual segment (an
 *     import, or typed in by hand), a sale the owner entered (an attendee import, the API, the
 *     box office) or one that is not paid, and anything a backup restore brought in
 *     (NewsletterSegment::unsolicitedEmails()). The people who did give their address (followers,
 *     confirmed subscribers, members, paying buyers and registrants, waitlists) are mailed as
 *     before; the others are left out of the send (NewsletterService::resolveRecipients()).
 *  B. A newsletter from an owner who is not yet trusted is held for an operator's review (status
 *     'review') once it, with what the owner sent in the last 30 days, reaches more than a handful
 *     of people; so is any newsletter with a watched word in what the owner wrote (SpamWatch),
 *     unless the owner has paid. Nothing sends a held newsletter but an operator's Approve
 *     (/admin/mail-review), and every runner reads 'scheduled' rows only.
 *  C. Email sending can be paused on an account (users.mail_disabled_at): when a watched word
 *     turns up for it, when its card was already used on another account, or by an operator's
 *     hand. While paused its schedules send no newsletters and no automatic announcements, and a
 *     newsletter going out stops; the owner can ask for it back once a day. An account that has
 *     paid is never paused automatically: what would have paused it is flagged for an operator
 *     instead (users.mail_flagged_at). Mail people asked for about one event (an interest list's
 *     notices, a ticket, a confirmation) is never stopped.
 *
 * Only on the hosted install and only for a schedule that sends through the platform's mail:
 * selfhost runs the operator's own server, and a schedule with its own email settings sends on
 * its own domain's name. A platform admin is never held.
 *
 * Like canSendAudienceMail(), the rules stand down under the test suite (app.is_testing) unless a
 * test turns that off, so a suite run on a hosted .env behaves as one on CI.
 */
class AudienceMailGuard
{
    /** How old an account must be, with a payment, before its newsletters go out unreviewed. */
    public const TRUSTED_ACCOUNT_DAYS = 30;

    /** The window rule B adds up an untrusted owner's recipients over. */
    public const REVIEW_WINDOW_DAYS = 30;

    /** How long a deleted account's card fingerprints are kept, as a one-way hash. */
    public const HASHED_FINGERPRINT_MONTHS = 12;

    public const REASON_SPAM_WATCH = 'spam_watch';

    public const REASON_SHARED_CARD = 'shared_card';

    public const REASON_ADMIN = 'admin';

    public const REASON_WORD = 'word';

    public const REASONS = [self::REASON_SPAM_WATCH, self::REASON_SHARED_CARD, self::REASON_ADMIN, self::REASON_WORD];

    /** Per request, so a page that asks for several schedules reads each account once. */
    private static array $pausedMemo = [];

    private static array $paidMemo = [];

    /** Whether these rules apply to this schedule at all. */
    public static function applies(?Role $role): bool
    {
        if (! config('app.hosted') || config('app.is_testing')) {
            return false;
        }

        return $role !== null && ! $role->hasEmailSettings();
    }

    // ── A. Addresses nobody gave the schedule themselves ─────────────────

    /**
     * Whether this account has paid: a subscription on any schedule it owns that is active or in
     * Stripe's dunning window AND has an invoice above zero Stripe told us was paid
     * (subscriptions.last_paid_at, stamped by SubscriptionWebhookController), or a plan an
     * operator granted that has not run out. A status alone is not a payment: a trial whose card
     * fails at its end goes to past_due without a cent. A referral's earned month is not a
     * payment, and neither is a plan from before paid subscriptions with nobody behind it.
     */
    public static function ownerHasPaid(?User $owner): bool
    {
        if (! $owner) {
            return false;
        }

        if (self::memoizing() && array_key_exists($owner->id, self::$paidMemo)) {
            return self::$paidMemo[$owner->id];
        }

        $schedules = fn ($q) => $q->select('id')->from('roles')->where('user_id', $owner->id);

        $paid = DB::table('subscriptions')
            ->whereIn('role_id', $schedules)
            ->whereIn('stripe_status', ['active', 'past_due'])
            ->whereNotNull('last_paid_at')
            ->exists()
            || Role::where('user_id', $owner->id)
                ->where('plan_source', 'admin')
                ->whereIn('plan_type', ['pro', 'enterprise'])
                ->where('plan_expires', '>=', now()->format('Y-m-d'))
                ->exists();

        if (self::memoizing()) {
            self::$paidMemo[$owner->id] = $paid;
        }

        return $paid;
    }

    /** Rule A: whether this schedule may mail addresses nobody gave it themselves. */
    public static function mayMailUnsolicited(Role $role): bool
    {
        if (! self::applies($role)) {
            return true;
        }

        $owner = $role->user;

        return $owner && ($owner->isAdmin() || self::ownerHasPaid($owner));
    }

    /**
     * The recipients rule A leaves in: everyone, except an address that reached the send only by
     * a way nobody chose themselves and that did not also give itself to this schedule.
     *
     * @param  Collection<int, object>  $recipients  as NewsletterService resolves them
     * @param  array<string, true>  $unsolicitedEmails  the addresses that came by such a way
     * @return array{0: Collection<int, object>, 1: int} the recipients kept, and how many were left out
     */
    public static function withoutUnsolicited(Role $role, Collection $recipients, array $unsolicitedEmails): array
    {
        if ($unsolicitedEmails === [] || self::mayMailUnsolicited($role)) {
            return [$recipients, 0];
        }

        $optedIn = NewsletterSegment::optedInAmong($role, array_keys($unsolicitedEmails));

        $kept = $recipients->filter(fn ($recipient) => ! isset($unsolicitedEmails[$recipient->email]) || isset($optedIn[$recipient->email]))->values();

        return [$kept, $recipients->count() - $kept->count()];
    }

    // ── B. Review of a newer sender's newsletter ─────────────────────────

    public static function reviewThreshold(): int
    {
        return max(0, (int) config('usage.newsletter_review_threshold', 10));
    }

    /**
     * Whether an account's newsletters go out without review: an operator approved one (or the
     * account had sent a large one before the review existed, which the migration recorded), or
     * it is older than TRUSTED_ACCOUNT_DAYS and has paid. Read from that column and the payment,
     * never from the account's newsletters, whose status and counts a restored backup can carry.
     */
    public static function isTrusted(?User $owner): bool
    {
        if (! $owner) {
            return false;
        }

        if ($owner->isAdmin() || $owner->newsletter_trusted_at) {
            return true;
        }

        return $owner->created_at
            && $owner->created_at->lte(now()->subDays(self::TRUSTED_ACCOUNT_DAYS))
            && self::ownerHasPaid($owner);
    }

    /**
     * How many people the owner's schedules mailed in the last REVIEW_WINDOW_DAYS: rule B adds
     * this to the send, so ten newsletters of ten each are one newsletter of a hundred.
     */
    public static function recentRecipients(?User $owner): int
    {
        if (! $owner) {
            return 0;
        }

        return self::recipientRowsSince(
            fn ($q) => $q->whereIn('newsletters.role_id', fn ($roles) => $roles->select('id')->from('roles')->where('user_id', $owner->id)),
            now()->subDays(self::REVIEW_WINDOW_DAYS),
        )->count();
    }

    /**
     * The recipient rows that count as mail sent since $since, by each row's OWN status (sent,
     * pending or failed; never a test), whatever became of its newsletter: deleting a newsletter
     * that went out (status 'cancelled') must not give its recipients back to the allowance or to
     * rule B. A sent row is dated by its own sent_at; a pending or failed one, which has none, by
     * its newsletter's last update. $newsletters narrows the newsletters (a schedule, an owner).
     */
    public static function recipientRowsSince(\Closure $newsletters, CarbonInterface $since, bool $exceptSentNewsletters = false)
    {
        return NewsletterRecipient::query()
            ->join('newsletters', 'newsletters.id', '=', 'newsletter_recipients.newsletter_id')
            ->where(fn ($q) => $newsletters($q))
            ->when($exceptSentNewsletters, fn ($q) => $q->where('newsletters.status', '!=', 'sent'))
            ->where(fn ($q) => $q
                ->where(fn ($sent) => $sent->where('newsletter_recipients.status', 'sent')->where('newsletter_recipients.sent_at', '>=', $since))
                ->orWhere(fn ($open) => $open->whereIn('newsletter_recipients.status', ['pending', 'failed'])->where('newsletters.updated_at', '>=', $since)));
    }

    /**
     * Why this newsletter would be held for review, or null when it goes. 'word' (a watched word
     * in what the owner wrote, from an owner who has not paid) or 'new_sender' (an owner not yet
     * trusted, whose recipients this send and over the last 30 days come to more than
     * reviewThreshold()). A newsletter an operator approved is never held again; an edit takes
     * the approval away (NewsletterController::update()).
     */
    public static function reviewReason(Newsletter $newsletter, int $recipients, ?User $sender = null): ?string
    {
        $role = $newsletter->role;

        if ($newsletter->isAdmin() || ! self::applies($role) || $newsletter->approved_at) {
            return null;
        }

        $owner = $role->user;

        if ($sender?->isAdmin() || $owner?->isAdmin()) {
            return null;
        }

        // A paying owner is not held for a word: NewsletterService::send() flags it instead.
        if (self::watchedWordIn($newsletter) !== null && ! self::ownerHasPaid($owner)) {
            return 'word';
        }

        if (! self::isTrusted($owner) && $recipients + self::recentRecipients($owner) > self::reviewThreshold()) {
            return 'new_sender';
        }

        return null;
    }

    /** Block keys that hold words the owner typed. Links, addresses and pictures are not read. */
    private const TEXT_KEYS = ['text', 'content', 'title', 'subtitle', 'heading', 'caption', 'label', 'buttonText', 'question', 'description'];

    /** Blocks whose words are not the owner's: copied from the schedule's links, or a picture. */
    private const NOT_TEXT_BLOCKS = ['social_links', 'video', 'image', 'header_banner', 'profile_image', 'sponsors', 'events'];

    /**
     * The first watched word in what the owner wrote (the subject, the preview text, and the text
     * of the blocks), or null. Links are left out, in a link field and inside the text alike: the
     * social links block carries the schedule's own PayPal or Venmo address into every newsletter.
     * SpamWatch's words and its comparison, which ignores case and accents.
     */
    public static function watchedWordIn(Newsletter $newsletter): ?string
    {
        $texts = [(string) $newsletter->subject, (string) (($newsletter->style_settings ?? [])['previewText'] ?? '')];

        foreach (is_array($newsletter->blocks) ? $newsletter->blocks : [] as $block) {
            if (! is_array($block) || in_array($block['type'] ?? '', self::NOT_TEXT_BLOCKS, true) || ! is_array($block['data'] ?? null)) {
                continue;
            }

            foreach (self::TEXT_KEYS as $key) {
                if (isset($block['data'][$key]) && is_string($block['data'][$key])) {
                    $texts[] = $block['data'][$key];
                }
            }
        }

        $text = implode("\n", $texts);
        // A Markdown link keeps its words and loses its address; a bare address goes whole.
        $text = (string) preg_replace('/\]\([^)]*\)/u', ']', $text);
        $text = (string) preg_replace('~\b(?:https?://|www\.)\S+~iu', ' ', $text);
        $text = (string) preg_replace('/\b[\w.+-]+@[\w-]+(?:\.[\w-]+)+\b/u', ' ', $text);

        try {
            return SpamWatch::wordInText($text);
        } catch (QueryException $e) {
            report($e);

            return null;
        }
    }

    /** Hold a claimed newsletter for review. Nothing sends it until an operator approves it. */
    public static function hold(Newsletter $newsletter, string $reason, int $recipients, ?array $mix = null): void
    {
        $newsletter->forceFill([
            'status' => 'review',
            'scheduled_at' => null,
            'send_token' => null,
            'held_at' => now(),
            'review_note' => null,
            // Who it was for when it was held, for the operator: read there, never worked out again.
            'review_mix' => $mix,
        ])->save();

        AuditService::log(AuditService::NEWSLETTER_HELD, null, 'App\\Models\\Newsletter', $newsletter->id,
            null, ['reason' => $reason, 'recipients' => $recipients]);
    }

    /** Held newsletters: any schedule's row in 'review', whatever its type column says. */
    public static function heldQuery()
    {
        return Newsletter::where('status', 'review')->whereNotNull('role_id');
    }

    // ── C. Email sending paused on an account ────────────────────────────

    /** Memos only inside a web request: a scheduler process lives for hours. */
    private static function memoizing(): bool
    {
        return ! app()->runningInConsole();
    }

    public static function forgetMemo(?int $userId = null): void
    {
        if ($userId === null) {
            self::$pausedMemo = [];
            self::$paidMemo = [];

            return;
        }

        unset(self::$pausedMemo[$userId], self::$paidMemo[$userId]);
    }

    /**
     * Whether email sending is paused on this account. A watched word that turns up for it now
     * (SpamWatch::flagsNow(), read live, so a word added today reaches an account made last
     * month) pauses it here and records why, so the owner can ask for it back. An account an
     * operator let back on is not paused again by the automatic checks, and one that has paid is
     * flagged for an operator instead.
     */
    public static function accountPaused(?User $user): bool
    {
        if (! $user || $user->isAdmin()) {
            return false;
        }

        if (self::memoizing() && array_key_exists($user->id, self::$pausedMemo)) {
            return self::$pausedMemo[$user->id];
        }

        $paused = self::readPaused($user);

        if (self::memoizing()) {
            self::$pausedMemo[$user->id] = $paused;
        }

        return $paused;
    }

    private static function readPaused(User $user): bool
    {
        if ($user->mail_disabled_at) {
            return true;
        }

        if ($user->mail_cleared_at) {
            return false;
        }

        try {
            $suspect = SpamWatch::flagsNow($user);
        } catch (QueryException $e) {
            report($e);
            $suspect = false;
        }

        if (! $suspect) {
            return false;
        }

        return self::pause($user, self::REASON_SPAM_WATCH);
    }

    /**
     * What a page says, without writing anything: paused by the column, or a watched word that
     * would pause it on the next send (an account that has not paid and no operator cleared).
     * Only a send attempt records a pause (accountPaused()); a GET never does.
     */
    public static function wouldBePaused(?User $user): bool
    {
        if (! $user || $user->isAdmin()) {
            return false;
        }

        if ($user->mail_disabled_at) {
            return true;
        }

        if ($user->mail_cleared_at || self::ownerHasPaid($user)) {
            return false;
        }

        try {
            return SpamWatch::flagsNow($user);
        } catch (QueryException $e) {
            report($e);

            return false;
        }
    }

    /** mailPaused() for a page: the same answer, nothing written. */
    public static function mailPausedForDisplay(?Role $role, ?User $sender = null): bool
    {
        if (! self::applies($role)) {
            return false;
        }

        if (self::wouldBePaused($role->user)) {
            return true;
        }

        return $sender && (int) $sender->id !== (int) $role->user_id && self::wouldBePaused($sender);
    }

    /**
     * Whether this schedule's newsletters and announcements are paused: its owner's account is,
     * or the person sending (a team member) is. Records a pause a watched word calls for: asked
     * only on a send attempt (mailPausedForDisplay() is the page's).
     */
    public static function mailPaused(?Role $role, ?User $sender = null): bool
    {
        if (! self::applies($role)) {
            return false;
        }

        if (self::accountPaused($role->user)) {
            return true;
        }

        return $sender && (int) $sender->id !== (int) $role->user_id && self::accountPaused($sender);
    }

    /**
     * The pause as the columns hold it, with no watched-word scan: what SendNewsletterBatch reads
     * for every batch of a send already going out.
     */
    public static function pausedNow(Newsletter $newsletter): bool
    {
        if ($newsletter->isAdmin() || ! self::applies($newsletter->role)) {
            return false;
        }

        $ids = array_filter([$newsletter->role?->user_id, $newsletter->user_id]);

        return $ids !== [] && User::whereIn('id', $ids)
            ->where('is_admin', false)
            ->whereNotNull('mail_disabled_at')
            ->exists();
    }

    /**
     * Pause email sending on an account, and stop what it is sending. False when it already was
     * paused, when it is an administrator, or when an automatic check ($by null) meets an account
     * an operator let back on. An automatic check never pauses an account that has paid: it flags
     * it for an operator instead.
     */
    public static function pause(User $user, string $reason, ?User $by = null): bool
    {
        if ($user->isAdmin() || ! in_array($reason, self::REASONS, true)) {
            return false;
        }

        if (! $by && self::ownerHasPaid($user)) {
            self::flag($user, $reason);

            return false;
        }

        $query = User::whereKey($user->id)->whereNull('mail_disabled_at');
        if (! $by) {
            $query->whereNull('mail_cleared_at');
        }

        $updated = $query->update([
            'mail_disabled_at' => now(),
            'mail_disabled_reason' => $reason,
            // An operator's own pause takes back an earlier "this account is fine".
            ...($by ? ['mail_cleared_at' => null] : []),
        ]);

        self::forgetMemo($user->id);

        if ($updated === 0) {
            return false;
        }

        $user->refresh();
        self::stopSending($user);

        AuditService::log(AuditService::MAIL_PAUSED, $by?->id, 'App\\Models\\User', $user->id, null, ['reason' => $reason]);

        return true;
    }

    /**
     * The newsletters this account is sending through the platform's mail stop where they are:
     * what has gone has gone, and SendNewsletterBatch reads 'cancelled' and sends no more.
     */
    private static function stopSending(User $user): void
    {
        Newsletter::where('status', 'sending')
            ->whereNotNull('role_id')
            ->where(fn ($q) => $q->where('user_id', $user->id)
                ->orWhereIn('role_id', fn ($r) => $r->select('id')->from('roles')->where('user_id', $user->id)))
            ->with('role')
            ->get()
            ->filter(fn (Newsletter $newsletter) => self::applies($newsletter->role))
            ->each(fn (Newsletter $newsletter) => $newsletter->forceFill(['status' => 'cancelled'])->save());
    }

    /** Something that would have paused an account that has paid: shown to an operator, stops nothing. */
    public static function flag(?User $user, string $reason): void
    {
        if (! $user || $user->isAdmin() || $user->mail_cleared_at) {
            return;
        }

        $updated = User::whereKey($user->id)->whereNull('mail_flagged_at')->update([
            'mail_flagged_at' => now(),
            'mail_flag_reason' => $reason,
        ]);

        if ($updated > 0) {
            $user->forceFill(['mail_flagged_at' => now(), 'mail_flag_reason' => $reason]);
            AuditService::log(AuditService::MAIL_FLAGGED, null, 'App\\Models\\User', $user->id, null, ['reason' => $reason]);
        }
    }

    /** An operator looked at a flagged account and it is fine. */
    public static function dismissFlag(User $user, User $by): void
    {
        $user->forceFill(['mail_flagged_at' => null, 'mail_flag_reason' => null, 'mail_cleared_at' => now()])->save();

        AuditService::log(AuditService::MAIL_ENABLED, $by->id, 'App\\Models\\User', $user->id, null, ['flag' => 'dismissed']);
    }

    /**
     * Let an account's email back on: an operator looked and it is fine, and the automatic checks
     * do not pause it again. It does not trust the account's newsletters: that is an approval.
     */
    public static function enable(User $user, User $by): void
    {
        $user->forceFill([
            'mail_disabled_at' => null,
            'mail_disabled_reason' => null,
            'mail_access_requested_at' => null,
            'mail_flagged_at' => null,
            'mail_flag_reason' => null,
            'mail_cleared_at' => now(),
        ])->save();
        self::forgetMemo($user->id);

        AuditService::log(AuditService::MAIL_ENABLED, $by->id, 'App\\Models\\User', $user->id);
    }

    /** "Not spam" on /admin/blocked lifts a pause the watched words made (SpamWatch::clear()). */
    public static function liftSpamWatchPause(User $user): void
    {
        $lifted = User::whereKey($user->id)
            ->where('mail_disabled_reason', self::REASON_SPAM_WATCH)
            ->update(['mail_disabled_at' => null, 'mail_disabled_reason' => null, 'mail_access_requested_at' => null]);

        if ($lifted > 0) {
            self::forgetMemo($user->id);
            $user->forceFill(['mail_disabled_at' => null, 'mail_disabled_reason' => null, 'mail_access_requested_at' => null]);
        }
    }

    /**
     * The operator looked at a request and the account stays paused. The pause is dated now, so
     * the request is behind it and the row drains; the owner can ask again tomorrow.
     */
    public static function keepPaused(User $user, User $by): void
    {
        if (! $user->mail_disabled_at) {
            return;
        }

        // Past the request even when both fall in one second (the columns hold seconds).
        $after = $user->mail_access_requested_at?->copy()->addSecond();
        $user->forceFill(['mail_disabled_at' => $after && $after->gt(now()) ? $after : now()])->save();

        AuditService::log(AuditService::MAIL_KEPT_PAUSED, $by->id, 'App\\Models\\User', $user->id);
    }

    /** An owner asks to have email sending back. Once a day at most; false when too soon. */
    public static function requestAccess(User $user): bool
    {
        if (! $user->mail_disabled_at) {
            return false;
        }

        if ($user->mail_access_requested_at && $user->mail_access_requested_at->gt(now()->subDay())) {
            return false;
        }

        $user->forceFill(['mail_access_requested_at' => now()])->save();

        AuditService::log(AuditService::MAIL_ACCESS_REQUESTED, $user->id, 'App\\Models\\User', $user->id);

        return true;
    }

    /** Whether the owner asked for it back too recently to ask again. */
    public static function requestedRecently(User $user): bool
    {
        return $user->mail_access_requested_at && $user->mail_access_requested_at->gt(now()->subDay());
    }

    /** Paused accounts that asked for their email back since they were paused. */
    public static function openRequests()
    {
        return User::whereNotNull('mail_disabled_at')
            ->whereNotNull('mail_access_requested_at')
            ->whereColumn('mail_access_requested_at', '>=', 'mail_disabled_at');
    }

    /** Accounts flagged and not paused, for an operator to look at. */
    public static function flagged()
    {
        return User::whereNotNull('mail_flagged_at')->whereNull('mail_disabled_at');
    }

    /** What a pause's reason is called, for the operator and the owner. */
    public static function reasonLabel(?string $reason): string
    {
        return in_array($reason, self::REASONS, true)
            ? __('messages.mail_paused_reason_'.$reason)
            : __('messages.mail_paused_reason_admin');
    }

    // ── Cards ────────────────────────────────────────────────────────────

    /** A deleted account's fingerprint, as it is kept: one way, keyed with the app's secret. */
    public static function hashFingerprint(string $fingerprint): string
    {
        return hash_hmac('sha256', 'card-fingerprint|'.$fingerprint, (string) config('app.key'));
    }

    /**
     * The card behind a checkout, by Stripe's fingerprint (the same card has the same one on every
     * account). Recorded for every checkout that reaches the card, then compared: inserted first,
     * so two checkouts at once each see the other. When the card was already used on another
     * account, or on one deleted in the last HASHED_FINGERPRINT_MONTHS (kept as a hash), email
     * sending on this one is paused (or, for an account that has paid, flagged). One person with
     * several accounts on one card is how the trials of 2026-11-18 were made. The checkout itself
     * goes on.
     */
    public static function recordCardFingerprint(?User $user, ?Role $role, ?string $fingerprint, bool $startsTrial = true): void
    {
        if (! config('app.hosted') || ! $user || ! is_string($fingerprint) || $fingerprint === '') {
            return;
        }

        $fingerprint = substr($fingerprint, 0, 64);

        try {
            $id = DB::table('payment_fingerprints')->insertGetId([
                'user_id' => $user->id,
                'role_id' => $role?->id,
                'fingerprint' => $fingerprint,
                'created_at' => now(),
            ]);

            $elsewhere = DB::table('payment_fingerprints')
                ->where('id', '!=', $id)
                ->where(fn ($q) => $q
                    ->where(fn ($live) => $live->where('fingerprint', $fingerprint)->where('user_id', '!=', $user->id))
                    ->orWhere(fn ($gone) => $gone->whereNull('user_id')->where('fingerprint', substr(self::hashFingerprint($fingerprint), 0, 64))))
                ->exists();
        } catch (QueryException $e) {
            report($e);

            return;
        }

        // Only a checkout that starts a trial (nothing charged) is paused for it; one that charges
        // the card today is flagged for an operator, as an account that has paid would be.
        if ($elsewhere) {
            $startsTrial ? self::pause($user, self::REASON_SHARED_CARD) : self::flag($user, self::REASON_SHARED_CARD);
        }
    }

    /**
     * A real payment arrived for a schedule (subscriptions.last_paid_at was just stamped): a pause
     * for a shared card, which only an unpaid trial gets, becomes a flag for an operator.
     */
    public static function paymentArrived(?Role $role): void
    {
        $owner = $role?->user;
        if (! $owner) {
            return;
        }

        $lifted = User::whereKey($owner->id)
            ->where('mail_disabled_reason', self::REASON_SHARED_CARD)
            ->update(['mail_disabled_at' => null, 'mail_disabled_reason' => null, 'mail_access_requested_at' => null]);

        self::forgetMemo($owner->id);

        if ($lifted > 0) {
            $owner->forceFill(['mail_disabled_at' => null, 'mail_disabled_reason' => null]);
            self::flag($owner, self::REASON_SHARED_CARD);
        }
    }

    /**
     * Stamp last_paid_at from Stripe's own answer: the subscription's latest invoice paid more than
     * zero. For checkout, so a payer is paid at once and not only when the webhook (which can race
     * the subscription row) arrives.
     */
    public static function recordPaymentFromStripe(?Subscription $subscription): void
    {
        if (! $subscription) {
            return;
        }

        try {
            $paid = (int) ($subscription->asStripeSubscription(['latest_invoice'])->latest_invoice->amount_paid ?? 0) > 0;
        } catch (\Throwable $e) {
            report($e);

            return;
        }

        if ($paid) {
            $subscription->forceFill(['last_paid_at' => now()])->save();
            self::paymentArrived($subscription->owner);
        }
    }

    /**
     * An account is deleted: its fingerprints lose the account and keep only a one-way hash, so the
     * same card on a new account is still recognised, for HASHED_FINGERPRINT_MONTHS
     * (PrunePersonalData deletes them after that).
     */
    public static function forgetFingerprints(User $user): void
    {
        DB::table('payment_fingerprints')
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->get(['id', 'fingerprint'])
            ->each(fn ($row) => DB::table('payment_fingerprints')->where('id', $row->id)->update([
                'user_id' => null,
                'role_id' => null,
                'fingerprint' => substr(self::hashFingerprint($row->fingerprint), 0, 64),
                'hashed_at' => now(),
            ]));
    }

    /** The other accounts a card of this account's was used on, for the operator. */
    public static function sharedCardAccounts(User $user): Collection
    {
        $fingerprints = DB::table('payment_fingerprints')->where('user_id', $user->id)->pluck('fingerprint');

        if ($fingerprints->isEmpty()) {
            return collect();
        }

        $ids = DB::table('payment_fingerprints')
            ->whereIn('fingerprint', $fingerprints)
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $user->id)
            ->distinct()
            ->pluck('user_id');

        return User::whereIn('id', $ids)->orderByDesc('id')->limit(10)->get();
    }
}
