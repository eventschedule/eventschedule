<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    public function resolveRecipients(): Collection
    {
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

    protected function resolveFollowers(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        return $this->role->followers()
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

        return \App\Models\RoleSubscriber::where('role_id', $this->role->id)
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
    private function mailableEventIds(): \Illuminate\Database\Query\Builder
    {
        return \Illuminate\Support\Facades\DB::table('event_role')
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

    protected function resolveTicketBuyers(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        $query = Sale::whereIn('event_id', $this->mailableEventIds())
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

        $criteria = $this->filter_criteria;
        if (empty($criteria['group_id'])) {
            return collect();
        }

        // The sub-schedule filter goes on THIS role's pivot row, not on any row carrying that
        // group_id - groups belong to one schedule, so the two agree, but pinning the role keeps a
        // hand-crafted group_id from another schedule out.
        return Sale::whereIn('event_id', $this->mailableEventIds()
            ->where('event_role.group_id', $criteria['group_id']))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->select('user_id', 'email', 'name')
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

        $roleIds = \App\Models\Role::where('plan_type', $planType)
            ->where('is_deleted', false)
            ->pluck('id');

        $userIds = \Illuminate\Support\Facades\DB::table('role_user')
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
