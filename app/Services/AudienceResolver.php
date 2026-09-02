<?php

namespace App\Services;

use App\Models\NewsletterUnsubscribe;
use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Who a schedule is allowed to mail.
 *
 * This class was referenced by name in four docblocks before it existed - the migration for
 * role_subscribers, Role::subscribers(), SubscriptionConfirmation and
 * RoleSubscriberController::confirm() all cited "AudienceResolver" as the authority on which rows
 * get mailed, while the only real implementation was spread across NewsletterService and
 * NewsletterSegment. Now it is real, and it owns the two rules that must never drift:
 *
 *   1. An UNCONFIRMED subscriber is never mailed. Confirmation is the proof of mailbox
 *      possession, and without it a stranger could sign anybody up for anything.
 *   2. Suppression is a SINGLE shared list (newsletter_unsubscribes). role_subscribers has no
 *      unsubscribed_at of its own precisely so there is one source of truth, and one unsubscribe
 *      stops newsletters and announcements together.
 */
class AudienceResolver
{
    /** @var Collection<int, string>|null */
    private ?Collection $platformSuppressed = null;

    /**
     * Addresses this schedule must not mail, lowercased.
     *
     * Two lists, both of which existed before and neither of which may be skipped: the schedule's
     * own suppression list, and the platform-wide users.is_subscribed = false opt-out.
     */
    public function suppressedEmails(Role $role): array
    {
        $perSchedule = NewsletterUnsubscribe::where('role_id', $role->id)
            ->pluck('email')
            ->map(fn ($email) => strtolower($email));

        return array_flip($perSchedule->merge($this->platformSuppressedEmails())->all());
    }

    /**
     * The platform-wide users.is_subscribed = false opt-out, memoized for the life of this
     * resolver.
     *
     * users.is_subscribed carries no index, so this is a full scan of the users table. It is
     * per-PLATFORM rather than per-schedule, but it used to sit inside suppressedEmails() and so
     * ran once per role inside SendEventAnnouncements' loop - a whole-table scan per schedule, for
     * an answer that cannot change between them.
     *
     * An instance property, not a static: a static would survive RefreshDatabase between tests and
     * hand the second test the first one's opt-out list. Callers that want a fresh read take a
     * fresh resolver, which is what the container hands every command and request anyway.
     *
     * @return Collection<int, string>
     */
    private function platformSuppressedEmails(): Collection
    {
        return $this->platformSuppressed ??= User::where('is_subscribed', false)
            ->pluck('email')
            ->map(fn ($email) => strtolower($email));
    }

    /**
     * The account-less audience a new-event announcement goes to.
     *
     * Deliberately NOT the newsletter's recipient set: a newsletter is a campaign the owner wrote
     * and addressed, and it always includes the schedule's own members so they can see what went
     * out. An announcement is triggered by publishing, and mailing the owner their own event every
     * time they add one is noise, so members are not included here.
     *
     * @return Collection<int, RoleSubscriber>
     */
    public function announcementRecipients(Role $role): Collection
    {
        $excluded = $this->suppressedEmails($role);

        return RoleSubscriber::where('role_id', $role->id)
            ->confirmed()
            ->get()
            ->reject(fn ($subscriber) => isset($excluded[strtolower($subscriber->email)])
                || $this->isTestEmail($subscriber->email))
            ->values();
    }

    /**
     * Shared with NewsletterService, which has filtered these out of every send since long before
     * the audience feature existed. Seeded fixtures use @example.com addresses.
     */
    public function isTestEmail(string $email): bool
    {
        $email = strtolower($email);
        $domain = substr($email, (int) strrpos($email, '@'));

        return in_array($domain, [
            '@example.com', '@example.org', '@example.net',
            '@test.com', '@test.org', '@test.net',
            '@localhost',
        ], true);
    }
}
