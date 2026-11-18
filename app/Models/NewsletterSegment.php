<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NewsletterSegment extends Model
{
    protected $fillable = [
        'role_id',
        'name',
        'type',
        'filter_criteria',
    ];

    protected $casts = [
        'filter_criteria' => 'array',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function segmentUsers()
    {
        return $this->hasMany(NewsletterSegmentUser::class);
    }

    /**
     * The one place a segment type becomes a human label.
     *
     * Previously a nested ternary duplicated in four views, every copy of which fell through to
     * "Sub-schedule" for anything it did not recognise - so adding a type rendered a subscriber
     * segment labelled "Sub-schedule" while every test still passed.
     */
    public static function typeLabel(?string $type): string
    {
        return match ($type) {
            'all_followers' => __('messages.all_followers'),
            'all_subscribers' => __('messages.all_subscribers'),
            'ticket_buyers' => __('messages.ticket_buyers'),
            'manual' => __('messages.manual'),
            'waitlist' => __('messages.waitlist'),
            'group' => __('messages.subschedule'),
            default => __('messages.subschedule'),
        };
    }

    /** Sales that are no purchase: never in a buyers segment. */
    public const NOT_A_SALE = ['cancelled', 'expired', 'refunded'];

    /** The kinds a schedule's segment may be. The rest are the platform's (no schedule). */
    public const SCHEDULE_TYPES = ['all_followers', 'all_subscribers', 'ticket_buyers', 'manual', 'waitlist', 'group'];

    public function resolveRecipients(): Collection
    {
        // A platform-wide kind on a schedule's row (a restored backup could set one) reaches nobody.
        if ($this->role_id !== null && ! in_array($this->type, self::SCHEDULE_TYPES, true)) {
            return collect();
        }

        return match ($this->type) {
            'all_followers' => $this->resolveFollowers(),
            'all_subscribers' => $this->resolveSubscribers(),
            'ticket_buyers' => $this->resolveTicketBuyers(),
            'manual' => $this->resolveManual(),
            'group' => $this->resolveGroup(),
            'waitlist' => $this->resolveWaitlist(),
            'all_users' => $this->resolveAllUsers(),
            'plan_tier' => $this->resolvePlanTier(),
            'signup_date' => $this->resolveSignupDate(),
            'admins' => $this->resolveAdmins(),
            default => collect(),
        };
    }

    /**
     * Account followers, meaning people who pressed Follow - NOT everyone holding a follower pivot.
     *
     * accountOnlyFollowers() excludes anyone with a confirmed role_subscribers row on this
     * schedule, because RoleSubscriberController::confirm() now gives a confirmed subscriber an
     * account and attaches a pivot. Without the exclusion this segment would silently widen to
     * cover both lists, which is exactly what resolveSubscribers() below says must never happen:
     * NewsletterService::send() resolves at SEND time, so a newsletter already sitting in
     * status='scheduled' would gain recipients on deploy, and on the free tier it would spend the
     * 10-recipient allowance faster.
     *
     * They are not dropped from anything: the composer's default "everyone" branch in
     * NewsletterService merges followers and confirmed subscribers and dedups by email, and an
     * all_subscribers segment names them directly.
     */
    protected function resolveFollowers(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        return $this->role->accountOnlyFollowers()
            ->select('users.id', 'users.email', 'users.name', 'users.is_subscribed')
            ->where('users.is_subscribed', true)
            ->get()
            ->map(fn ($user) => (object) [
                'user_id' => $user->id,
                'email' => strtolower($user->email),
                'name' => $user->name,
            ]);
    }

    /**
     * People who gave this schedule an email address without creating an account.
     *
     * A SEPARATE type from all_followers rather than widening it. Widening would silently change
     * the recipient set of every saved segment and every already-scheduled newsletter, and would
     * remove the owner's ability to mail only account holders.
     *
     * Only CONFIRMED rows. The subscribe endpoint is public and unauthenticated, and the repo has
     * no bounce or complaint handling anywhere, so an address nobody confirmed must never be mailed
     * more than the one confirmation itself.
     */
    protected function resolveSubscribers(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        return RoleSubscriber::where('role_id', $this->role->id)
            ->confirmed()
            ->get(['email', 'name'])
            ->map(fn ($subscriber) => (object) [
                // Nullable on newsletter_recipients, and resolveWaitlist() already emits the same
                // shape, so nothing downstream needs to know these have no account.
                'user_id' => null,
                'email' => strtolower($subscriber->email),
                'name' => $subscriber->name,
            ]);
    }

    /**
     * Event ids whose buyers this schedule may mail: its ACCEPTED attachments.
     *
     * Keyed on the event_role pivot, NOT sales.subdomain. That column is a booking-time snapshot of
     * the storefront the buyer checked out through, and RoleController::update() never rewrites it
     * on rename - so a renamed schedule silently lost its own audience, and whoever next claimed the
     * freed subdomain inherited it and could mail those people. Same hazard
     * RoleController::appointmentsTabData() documents and routes around via creator_role_id.
     *
     * The pivot is also what the docs promise: "everyone who has bought a ticket ... for one of your
     * events", not everyone who happened to check out through your page. is_accepted keeps a
     * schedule that DECLINED an event from mailing its buyers - a decline does not detach the row.
     *
     * The own-schedule arm is the same one Event::scopeManagedThrough() carries, for the same
     * reason. is_accepted is nullable, and `= true` drops NULL as well as false - which is right
     * for somebody ELSE's event and wrong for your own. An appointment booking is the live case:
     * AppointmentService attaches it with creator_role_id = this schedule and is_accepted null
     * while it awaits approval (null again on reschedule), false once cancelled - and it sets
     * sales.subdomain to this schedule, so the old subdomain rule DID reach those buyers. Without
     * this arm a cancelled booking's buyer leaves the segment permanently.
     *
     * Returned as a subquery, never a plucked list: a curator can list tens of thousands of events
     * and binding one placeholder each can exceed MySQL's prepared-statement limit.
     */
    private function mailableEventIds(): Builder
    {
        return DB::table('event_role')
            // Joined only to reach creator_role_id. Many-to-one on event_role.event_id, so this
            // cannot multiply rows, and the subquery still yields exactly one column.
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->where('event_role.role_id', $this->role->id)
            ->where(function ($q) {
                // My own schedule's event: mine whatever the pivot says.
                $q->whereColumn('event_role.role_id', 'events.creator_role_id')
                    // Somebody else's: only once this schedule accepted it. A legacy row with a
                    // null creator_role_id fails the arm above and lands here, which is the
                    // pre-existing behaviour.
                    ->orWhere('event_role.is_accepted', true);
            })
            ->select('event_role.event_id');
    }

    /**
     * Which of these addresses gave themselves to this schedule: a follower, a confirmed email
     * subscriber, a member, a buyer or registrant of one of its events, or somebody on one of its
     * waitlists. The rest came from an import or were typed in by hand, which is all a 'manual'
     * segment can tell (AudienceMailGuard::mayMailUnsolicited()).
     *
     * Asked in chunks of addresses, never by listing the whole audience: an import holds up to
     * ten thousand.
     *
     * @param  array<int, string>  $emails  lowercase
     * @return array<string, true> the opted-in addresses, lowercase, as keys
     */
    public static function optedInAmong(Role $role, array $emails): array
    {
        $found = [];
        $segment = (new self(['role_id' => $role->id]))->setRelation('role', $role);

        foreach (array_chunk(array_values(array_unique($emails)), 1000) as $chunk) {
            // Only what the person did themselves: a subscriber nobody restored, a paid sale the
            // owner did not enter, a place on a waitlist that is still open.
            $lists = [
                $role->followers()->reorder()->whereIn('users.email', $chunk)->pluck('users.email'),
                $role->members()->reorder()->whereIn('users.email', $chunk)->pluck('users.email'),
                // A checkout's tick box counts through the paid sale below, not on its own: the row
                // is made before the payment, which may never come.
                RoleSubscriber::where('role_id', $role->id)->confirmed()->whereNull('origin')
                    ->where('source', '!=', 'checkout')->whereIn('email', $chunk)->pluck('email'),
                Sale::whereIn('event_id', $segment->mailableEventIds())->where('status', 'paid')
                    ->whereNot(fn ($q) => self::ownerEntered($q))->whereIn('email', $chunk)->pluck('email'),
                TicketWaitlist::whereIn('event_id', $segment->mailableEventIds())->whereIn('status', ['waiting', 'notified'])->whereIn('email', $chunk)->pluck('email'),
            ];

            foreach ($lists as $list) {
                foreach ($list as $email) {
                    $found[strtolower((string) $email)] = true;
                }
            }
        }

        return $found;
    }

    /**
     * A sale the owner put there rather than one the buyer made: an attendee import, the API, the
     * box office, or a backup restore. Not an opt-in (AudienceMailGuard, rule A). An import from
     * before sales.origin carries payment_method 'import'.
     */
    public static function ownerEntered($query)
    {
        // COALESCE: a NULL payment_method would make the NOT of this NULL, and drop the row from
        // optedInAmong(), which asks for the sales this is not.
        return $query->whereRaw("COALESCE(sales.payment_method, '') IN ('import', 'box_office')")->orWhereNotNull('sales.origin');
    }

    /**
     * The addresses of this segment that reached it by a way nobody chose themselves (rule A):
     * all of a manual segment, the sales the owner entered, the subscribers a restore brought in.
     *
     * @return list<string> lowercase
     */
    public function unsolicitedEmails(): array
    {
        if (! $this->role && $this->type !== 'manual') {
            return [];
        }

        // A sale counts as the buyer's own only when it is paid and the owner did not enter it.
        $notOwn = fn ($q) => $q->where(fn ($w) => self::ownerEntered($w)->orWhere('sales.status', '!=', 'paid'));

        $emails = match ($this->type) {
            'manual' => $this->segmentUsers()->pluck('email'),
            'ticket_buyers' => $this->buyerSales()?->where($notOwn)->pluck('email'),
            'group' => $this->groupSales()?->where($notOwn)->pluck('email'),
            'all_subscribers' => self::unsolicitedSubscriberEmails($this->role),
            default => collect(),
        };

        return collect($emails ?? [])->map(fn ($email) => strtolower((string) $email))->unique()->values()->all();
    }

    /**
     * Confirmed subscribers who did not sign up here themselves (rule A): those a backup restore
     * brought in, and those a checkout's tick box made with no paid sale behind them (the row is
     * made before the payment). Shared with NewsletterService's "everyone" audience.
     *
     * @return list<string> lowercase
     */
    public static function unsolicitedSubscriberEmails(Role $role): array
    {
        $segment = (new self(['role_id' => $role->id]))->setRelation('role', $role);

        return RoleSubscriber::where('role_id', $role->id)
            ->confirmed()
            ->where(fn ($q) => $q->whereNotNull('origin')
                ->orWhere(fn ($checkout) => $checkout->where('source', 'checkout')
                    ->whereNotExists(fn ($sale) => $sale->selectRaw('1')
                        ->from('sales')
                        ->whereColumn('sales.email', 'role_subscribers.email')
                        ->whereIn('sales.event_id', $segment->mailableEventIds())
                        ->where('sales.status', 'paid')
                        ->whereRaw("COALESCE(sales.payment_method, '') NOT IN ('import', 'box_office')")
                        ->whereNull('sales.origin'))))
            ->pluck('email')
            ->map(fn ($email) => strtolower((string) $email))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Who bought: every sale but a cancelled, expired or refunded one. Rule A is stricter, and only
     * where it applies (unsolicitedEmails(), optedInAmong()).
     */
    private function buyerSales(): ?\Illuminate\Database\Eloquent\Builder
    {
        if (! $this->role) {
            return null;
        }

        $query = Sale::whereIn('event_id', $this->mailableEventIds())
            ->whereNotIn('status', self::NOT_A_SALE)
            ->whereNotNull('email')
            ->where('email', '!=', '');

        $criteria = $this->filter_criteria;
        if (! empty($criteria['event_id'])) {
            $query->where('event_id', $criteria['event_id']);
        }
        if (! empty($criteria['date_from'])) {
            $query->where('created_at', '>=', $criteria['date_from']);
        }
        if (! empty($criteria['date_to'])) {
            $query->where('created_at', '<=', $criteria['date_to'].' 23:59:59');
        }

        return $query;
    }

    private function groupSales(): ?\Illuminate\Database\Eloquent\Builder
    {
        $criteria = $this->filter_criteria;
        if (! $this->role || empty($criteria['group_id'])) {
            return null;
        }

        // The sub-schedule filter goes on THIS role's pivot row, not on any row carrying that
        // group_id - groups belong to one schedule, so the two agree, but pinning the role keeps a
        // hand-crafted group_id from another schedule out.
        return Sale::whereIn('event_id', $this->mailableEventIds()
            ->where('event_role.group_id', $criteria['group_id']))
            ->whereNotIn('status', self::NOT_A_SALE)
            ->whereNotNull('email')
            ->where('email', '!=', '');
    }

    protected function resolveTicketBuyers(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        $query = $this->buyerSales();

        return $query->select('user_id', 'email', 'name')
            ->distinct('email')
            ->get()
            ->map(fn ($sale) => (object) [
                'user_id' => $sale->user_id,
                'email' => strtolower($sale->email),
                'name' => $sale->name,
            ]);
    }

    protected function resolveManual(): Collection
    {
        return $this->segmentUsers()
            ->get()
            ->map(fn ($su) => (object) [
                'user_id' => $su->user_id,
                'email' => strtolower($su->email),
                'name' => $su->name,
            ]);
    }

    protected function resolveGroup(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        $query = $this->groupSales();
        if (! $query) {
            return collect();
        }

        return $query->select('user_id', 'email', 'name')
            ->distinct('email')
            ->get()
            ->map(fn ($sale) => (object) [
                'user_id' => $sale->user_id,
                'email' => strtolower($sale->email),
                'name' => $sale->name,
            ]);
    }

    protected function resolveWaitlist(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        $query = TicketWaitlist::whereIn('event_id', $this->mailableEventIds())
            ->whereIn('status', ['waiting', 'notified'])
            ->whereNotNull('email')
            ->where('email', '!=', '');

        $criteria = $this->filter_criteria;
        if (! empty($criteria['event_id'])) {
            $query->where('event_id', $criteria['event_id']);
        }

        return $query->select('email', 'name')
            ->distinct('email')
            ->get()
            ->map(fn ($entry) => (object) [
                'user_id' => null,
                'email' => strtolower($entry->email),
                'name' => $entry->name,
            ]);
    }

    protected function resolveAllUsers(): Collection
    {
        return User::whereNotNull('email_verified_at')
            ->where('is_subscribed', true)
            ->whereNull('admin_newsletter_unsubscribed_at')
            ->select('id', 'email', 'name')
            ->get()
            ->map(fn ($user) => (object) [
                'user_id' => $user->id,
                'email' => strtolower($user->email),
                'name' => $user->name,
            ]);
    }

    protected function resolvePlanTier(): Collection
    {
        $criteria = $this->filter_criteria;
        $planType = $criteria['plan_type'] ?? null;
        if (! $planType) {
            return collect();
        }

        $roleIds = Role::where('plan_type', $planType)
            ->where('is_deleted', false)
            ->pluck('id');

        $userIds = DB::table('role_user')
            ->whereIn('role_id', $roleIds)
            ->whereIn('level', ['owner', 'admin'])
            ->pluck('user_id')
            ->unique();

        return User::whereIn('id', $userIds)
            ->whereNotNull('email_verified_at')
            ->where('is_subscribed', true)
            ->whereNull('admin_newsletter_unsubscribed_at')
            ->select('id', 'email', 'name')
            ->get()
            ->map(fn ($user) => (object) [
                'user_id' => $user->id,
                'email' => strtolower($user->email),
                'name' => $user->name,
            ]);
    }

    protected function resolveSignupDate(): Collection
    {
        $criteria = $this->filter_criteria;

        $query = User::whereNotNull('email_verified_at')
            ->where('is_subscribed', true)
            ->whereNull('admin_newsletter_unsubscribed_at');

        if (! empty($criteria['date_from'])) {
            $query->where('created_at', '>=', $criteria['date_from']);
        }
        if (! empty($criteria['date_to'])) {
            $query->where('created_at', '<=', $criteria['date_to'].' 23:59:59');
        }

        return $query->select('id', 'email', 'name')
            ->get()
            ->map(fn ($user) => (object) [
                'user_id' => $user->id,
                'email' => strtolower($user->email),
                'name' => $user->name,
            ]);
    }

    protected function resolveAdmins(): Collection
    {
        return User::where('is_admin', true)
            ->whereNotNull('email_verified_at')
            ->where('is_subscribed', true)
            ->whereNull('admin_newsletter_unsubscribed_at')
            ->select('id', 'email', 'name')
            ->get()
            ->map(fn ($user) => (object) [
                'user_id' => $user->id,
                'email' => strtolower($user->email),
                'name' => $user->name,
            ]);
    }

    public function recipientCount(): int
    {
        if ($this->type === 'admins') {
            return User::where('is_admin', true)
                ->whereNotNull('email_verified_at')
                ->where('is_subscribed', true)
                ->whereNull('admin_newsletter_unsubscribed_at')
                ->count();
        }

        if ($this->type === 'all_users') {
            return User::whereNotNull('email_verified_at')
                ->where('is_subscribed', true)
                ->whereNull('admin_newsletter_unsubscribed_at')
                ->count();
        }

        return $this->resolveRecipients()->unique('email')->count();
    }
}
