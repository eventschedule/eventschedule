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

    protected function resolveTicketBuyers(): Collection
    {
        if (! $this->role) {
            return collect();
        }

        $query = Sale::where('subdomain', $this->role->subdomain)
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
            $query->where('created_at', '<=', $criteria['date_to'] . ' 23:59:59');
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

        return Sale::where('subdomain', $this->role->subdomain)
            ->whereHas('event', function ($q) use ($criteria) {
                $q->whereHas('roles', function ($q2) use ($criteria) {
                    $q2->where('event_role.group_id', $criteria['group_id']);
                });
            })
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
            $query->where('created_at', '<=', $criteria['date_to'] . ' 23:59:59');
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
