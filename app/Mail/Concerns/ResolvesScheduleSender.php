<?php

namespace App\Mail\Concerns;

use App\Models\Role;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Str;

/**
 * Audience mail has to say WHICH schedule it came from.
 *
 * Every mailable in this app resolves the From line the same way (see NewsletterEmail::envelope(),
 * WaitlistNotification, EventChanged): the platform address and the platform display name,
 * overridden only when the schedule has configured its own SMTP. About 1.9% of schedules have. So
 * in practice a fan who handed their address to one venue receives mail from "Event Schedule", a
 * name they have never seen, and the reflex is Report spam - which lands on the shared sending
 * reputation carrying every other schedule's ticket receipts.
 *
 * For mail somebody opted into, the display name carries the schedule: "The Blue Note via Event
 * Schedule". The ADDRESS is untouched, and DMARC alignment is evaluated on the From domain and
 * never on the display name, so this changes no deliverability mechanics.
 */
trait ResolvesScheduleSender
{
    protected function scheduleFrom(?Role $role): Address
    {
        $address = config('mail.from.address');
        $name = config('mail.from.name');

        // Its own SMTP: the schedule is already the sender in every sense. Leave it alone.
        if ($role && $role->hasEmailSettings()) {
            $settings = $role->getEmailSettings();
            if (! empty($settings['from_address'])) {
                $address = $settings['from_address'];
            }
            if (! empty($settings['from_name'])) {
                $name = $settings['from_name'];
            }

            return new Address($address, $name);
        }

        $scheduleName = $this->sanitizeSenderName($role?->name);

        if ($scheduleName !== '') {
            $name = __('messages.sender_via_platform', [
                'name' => $scheduleName,
                'platform' => $name,
            ]);
        }

        return new Address($address, $name);
    }

    /** Replies go to the schedule, not to the platform. Same shape as NewsletterEmail. */
    protected function scheduleReplyTo(?Role $role): array
    {
        $fallbackAddress = config('mail.from.address');
        $fallbackName = config('mail.from.name');

        if (empty($role?->email)) {
            return [new Address($fallbackAddress, $fallbackName)];
        }

        return [new Address($role->email, $this->sanitizeSenderName($role->name) ?: $fallbackName)];
    }

    /**
     * Strip CRLF (header injection) and cap the length.
     *
     * Deliberately done HERE rather than in validation: a schedule name is written by a dozen
     * paths including AI import and calendar sync, and a bad one reaching Symfony's Address would
     * throw inside a queue worker, where the failure is silent and takes the whole send with it.
     */
    protected function sanitizeSenderName(?string $name): string
    {
        $clean = preg_replace('/\s+/u', ' ', (string) $name);

        return Str::limit(trim((string) $clean), 60, '');
    }
}
