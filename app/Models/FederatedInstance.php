<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An EventSchedule install that federates its public events to this one.
 * Only populated on the nexus app (eventschedule.com), where an admin approves
 * an instance once and its events then publish automatically.
 */
class FederatedInstance extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_SUSPENDED = 'suspended';

    protected $hidden = ['secret'];

    protected $fillable = [
        'instance_id',
        'site_url',
        'name',
        'contact_email',
        'secret',
        'app_version',
        'status',
        'approved_by',
        'approved_at',
        'last_seen_at',
        'flagged_at',
    ];

    protected $casts = [
        // Encrypted rather than hashed: verifying a request HMAC needs the plaintext.
        'secret' => 'encrypted',
        'approved_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'flagged_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany(FederatedEvent::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * The host events from this instance must live on. Guards against an approved
     * instance being used to publish backlinks to somewhere else entirely.
     */
    public function host(): ?string
    {
        $host = parse_url((string) $this->site_url, PHP_URL_HOST);

        return $host ? strtolower($host) : null;
    }

    /**
     * Does this URL live on the host the instance registered? Exact host, or a
     * subdomain of it.
     *
     * The rule the event backlink has always had to pass, lifted out of the intake
     * controller so a second surface cannot drift from it. It guards every
     * instance-supplied URL this app is willing to render as a link, which is now
     * more than one: schedule_url reaches the review screen.
     *
     * parse_url returns no host for a scheme-relative or javascript: string, so
     * those fail here too.
     */
    public function ownsUrl(?string $url): bool
    {
        $parts = parse_url((string) $url) ?: [];

        if (! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        $expected = $this->host();

        return $host !== '' && $expected !== null
            && ($host === $expected || str_ends_with($host, '.'.$expected));
    }
}
