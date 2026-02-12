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
            default => collect(),
        };
    }

    protected function resolveFollowers(): Collection
    {
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
            $query->where('created_at', '<=', $criteria['date_to']);
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

    public function recipientCount(): int
    {
        return $this->resolveRecipients()->unique('email')->count();
    }
}
