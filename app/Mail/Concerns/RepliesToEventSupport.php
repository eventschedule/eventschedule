<?php

namespace App\Mail\Concerns;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Headers;

/**
 * A buyer's reply goes to the address the mail names as its "Event support contact".
 *
 * Mail to a buyer is sent From the platform address unless the schedule has its own SMTP, so
 * without a Reply-To a buyer who answered "Re: Ticket Reservation Confirmation" wrote to the
 * platform's inbox instead of the organizer the mail's footer points them to. Each mail passes the
 * same address its footer prints, so the two cannot disagree.
 */
trait RepliesToEventSupport
{
    /**
     * Reply-To is for people. An out-of-office responder answers the envelope sender or From
     * instead (RFC 3834), which is the platform's inbox, so the mail says it was sent by a
     * machine: responders that follow the RFC skip an Auto-Submitted mail, and Exchange skips one
     * that carries X-Auto-Response-Suppress.
     */
    public function headers(): Headers
    {
        return new Headers(text: [
            'Auto-Submitted' => 'auto-generated',
            'X-Auto-Response-Suppress' => 'OOF, AutoReply',
        ]);
    }

    /** @return array<int, Address> */
    protected function eventSupportReplyTo(?string $email): array
    {
        $email = trim((string) $email);

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [];
        }

        return [new Address($email)];
    }

    /**
     * The rule the feedback and installment footers print: the schedule's own sender when it has
     * one, otherwise the event owner's address.
     */
    protected function scheduleOrEventSupportEmail(?Role $role, ?Event $event): ?string
    {
        $settings = $role ? $role->getEmailSettings() : [];

        return ! empty($settings['from_address']) ? $settings['from_address'] : $event?->user?->email;
    }
}
