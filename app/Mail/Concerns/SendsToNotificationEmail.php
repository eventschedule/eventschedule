<?php

namespace App\Mail\Concerns;

use App\Models\Role;
use Illuminate\Mail\Mailables\Headers;

/**
 * An owner notification that can also be sent to the schedule's shared notification address
 * (NotificationEmailService::sendMailable()).
 *
 * That copy goes to a mailbox with no account behind it, so its unsubscribe must be the signed
 * link that removes the address, both in the List-Unsubscribe header and in the footer
 * (emails/partials/notification_email_footer). An editor's copy keeps what it had.
 */
trait SendsToNotificationEmail
{
    protected ?string $notificationEmailUnsubscribeUrl = null;

    /**
     * Mark this message as the shared notification address's copy.
     */
    public function toNotificationEmail(string $unsubscribeUrl): static
    {
        $this->notificationEmailUnsubscribeUrl = $unsubscribeUrl;

        return $this;
    }

    /**
     * The List-Unsubscribe headers: the shared address's own link on its copy, otherwise the
     * schedule's generic unsubscribe route when $withRoleFallback, otherwise none.
     */
    protected function listUnsubscribeHeaders(?Role $role, bool $withRoleFallback = true): Headers
    {
        $url = $this->notificationEmailUnsubscribeUrl;

        // Signed for the schedule's own address: the unsigned role.unsubscribe form needs a CSRF
        // token a mail client's one-click POST never has.
        if (! $url && $withRoleFallback && $role?->email) {
            $url = \App\Utils\UrlUtils::roleUnsubscribeOneClickUrl($role->email);
        }

        if (! $url) {
            return new Headers;
        }

        return new Headers(
            text: [
                'List-Unsubscribe' => '<'.$url.'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }
}
