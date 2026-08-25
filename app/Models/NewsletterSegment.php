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

    public function resolveRecipients(): Collection
    {
        return match ($this->type) {
            'all_followers' => $this->resolveFollowers(),
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
     * Returned as a subquery, never a plucked list: a curator can list tens of thousands of events
     * and binding one placeholder each can exceed MySQL's prepared-statement limit.
     */
    private function mailableEventIds(): \Illuminate\Database\Query\Builder
    {
        return \Illuminate\Support\Facades\DB::table('event_role')
            ->where('event_role.role_id', $this->role->id)
            ->where('event_role.is_accepted', true)
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
